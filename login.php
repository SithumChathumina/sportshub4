<?php
/**
 * login.php
 * Student/Admin Login Page for NSBM SportsHub
 */

session_start();

// Include database connection
require_once 'config/db.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error   = '';  // Single error message for login
$old_email = ''; // To re-fill the email field

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize inputs
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $old_email = $email; // Save for re-filling the form

    // --- Basic Validation ---
    if (empty($email) || empty($password)) {
        $error = "Please enter both your email and password.";
    } else {
        // --- Look Up User by Email ---
        // We use a prepared statement to prevent SQL Injection
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(); // Fetch the user row as an associative array

        // --- Check Password ---
        // password_verify() compares the plain text password with the stored hash
        if ($user && password_verify($password, $user['password'])) {
            // --- Login Successful! Store data in session ---
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role']; // 'student' or 'admin'

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            // Wrong email or password
            $error = "Invalid email or password. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<!-- ===== LOGIN PAGE CONTENT ===== -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Login</h4>
                    <p class="mb-0 small">Welcome back to SportsHub!</p>
                </div>
                <div class="card-body p-4">

                    <!-- Error Message -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form action="login.php" method="POST">

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="your@email.com"
                                       value="<?php echo htmlspecialchars($old_email); ?>"
                                       required autofocus>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Your password" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </div>

                    </form>

                    <hr>
                    <p class="text-center text-muted mb-0">
                        Don't have an account? <a href="register.php">Register here</a>
                    </p>

                </div><!-- end .card-body -->
            </div><!-- end .card -->

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

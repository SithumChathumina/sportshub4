<?php
/**
 * register.php
 * Student Registration Page for NSBM SportsHub
 */

// Start the session so we can store login info later
session_start();

// Include the database connection
require_once 'config/db.php';

// If user is already logged in, send them to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// --- Variables to hold messages and old form values ---
$errors   = [];      // Array to collect validation errors
$success  = '';      // Success message string
$old      = [];      // To re-fill form fields after an error

// --- Handle Form Submission ---
// $_SERVER['REQUEST_METHOD'] tells us if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 1. Collect and Sanitize Input ---
    // htmlspecialchars() prevents XSS attacks by escaping special characters
    // trim() removes leading/trailing whitespace
    $name       = trim($_POST['name']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = trim($_POST['password']   ?? '');
    $confirm_pw = trim($_POST['confirm_password'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $phone      = trim($_POST['phone']      ?? '');

    // Save old values to re-fill form if there are errors
    $old = compact('name', 'email', 'student_id', 'phone');

    // --- 2. Validate Input ---
    if (empty($name)) {
        $errors[] = "Full name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm_pw) {
        $errors[] = "Passwords do not match.";
    }
    if (empty($student_id)) {
        $errors[] = "Student ID is required.";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }

    // --- 3. If No Errors, Try to Register the User ---
    if (empty($errors)) {

        // Check if email already exists in the database
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            // Email already taken
            $errors[] = "This email address is already registered. Please login.";
        } else {
            // Hash the password securely (NEVER store plain text passwords!)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, student_id, phone, role)
                 VALUES (?, ?, ?, ?, ?, 'student')"
            );
            $stmt->execute([$name, $email, $hashed_password, $student_id, $phone]);

            // Show success message and clear old data
            $success = "Registration successful! You can now <a href='login.php'>login here</a>.";
            $old = []; // Clear the form
        }
    }
}

// Include the header (navbar etc.)
include 'includes/header.php';
?>

<!-- ===== REGISTRATION PAGE CONTENT ===== -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <!-- Card wrapping the form -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white text-center py-3">
                    <h4 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Create Your Account</h4>
                    <p class="mb-0 small">Join NSBM SportsHub today!</p>
                </div>
                <div class="card-body p-4">

                    <!-- Show Success Message -->
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Show Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form action="register.php" method="POST" novalidate>

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   placeholder="e.g. Kavindu Perera"
                                   value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>"
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="e.g. student@nsbm.ac.lk"
                                   value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                                   required>
                        </div>

                        <!-- Student ID -->
                        <div class="mb-3">
                            <label for="student_id" class="form-label fw-semibold">Student ID</label>
                            <input type="text" class="form-control" id="student_id" name="student_id"
                                   placeholder="e.g. PS/2024/123"
                                   value="<?php echo htmlspecialchars($old['student_id'] ?? ''); ?>"
                                   required>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   placeholder="e.g. 0712345678"
                                   value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>"
                                   required>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="At least 6 characters" required>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                   placeholder="Re-enter your password" required>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-person-check-fill me-2"></i>Register Now
                            </button>
                        </div>

                    </form>

                    <!-- Link to Login -->
                    <hr>
                    <p class="text-center text-muted mb-0">
                        Already have an account? <a href="login.php">Login here</a>
                    </p>

                </div><!-- end .card-body -->
            </div><!-- end .card -->

        </div><!-- end col -->
    </div><!-- end row -->
</div><!-- end container -->

<?php include 'includes/footer.php'; ?>

<?php
/**
 * clubs.php
 * Student-facing page: Browse and apply to join sports clubs.
 */

session_start();
require_once 'config/db.php';
require_once 'includes/auth.php';

$message = '';       // Feedback message after applying
$msg_type = '';      // 'success' or 'danger' for Bootstrap alert color

// --- Handle Apply to Join (POST request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['club_id'])) {

    // User must be logged in to apply
    requireLogin();

    $club_id = (int)$_POST['club_id'];           // Cast to int for safety
    $user_id = (int)$_SESSION['user_id'];

    // Check if the user has already applied or is already a member
    $stmt = $pdo->prepare(
        "SELECT id FROM club_memberships 
         WHERE user_id = ? AND club_id = ?"
    );
    $stmt->execute([$user_id, $club_id]);

    if ($stmt->fetch()) {
        // Already applied
        $message  = "You have already applied to join this club!";
        $msg_type = "warning";
    } else {
        // Insert a new membership application with status 'pending'
        $stmt = $pdo->prepare(
            "INSERT INTO club_memberships (user_id, club_id, status, applied_date)
             VALUES (?, ?, 'pending', NOW())"
        );
        $stmt->execute([$user_id, $club_id]);

        $message  = "Application submitted! Please wait for admin approval.";
        $msg_type = "success";
    }
}

// --- Fetch All Active Clubs with Member Count ---
// We use a LEFT JOIN to count approved members per club
$stmt = $pdo->query(
    "SELECT c.id, c.name, c.sport_type, c.description, c.max_members,
            COUNT(cm.id) AS member_count
     FROM clubs c
     LEFT JOIN club_memberships cm ON c.id = cm.club_id AND cm.status = 'approved'
     WHERE c.status = 'active'
     GROUP BY c.id
     ORDER BY c.name ASC"
);
$clubs = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- ===== CLUBS PAGE CONTENT ===== -->
<div class="container my-5">

    <!-- Page Title -->
    <div class="text-center mb-5">
        <h2 class="fw-bold"><i class="bi bi-people-fill text-success me-2"></i>Sports Clubs</h2>
        <p class="text-muted">Discover and join a sports club that matches your passion!</p>
    </div>

    <!-- Feedback Message (after applying) -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Clubs Grid -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

        <?php if (empty($clubs)): ?>
            <!-- No clubs found -->
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-emoji-frown me-2"></i>No clubs available at the moment. Check back later!
                </div>
            </div>
        <?php else: ?>
            <!-- Loop through each club and display a card -->
            <?php foreach ($clubs as $club): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                        <!-- Card top colored bar based on sport type -->
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-shield-fill me-2"></i>
                                <?php echo htmlspecialchars($club['name']); ?>
                            </h5>
                            <small>
                                <i class="bi bi-tag me-1"></i>
                                <?php echo htmlspecialchars($club['sport_type']); ?>
                            </small>
                        </div>

                        <div class="card-body">
                            <!-- Club Description -->
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($club['description']); ?>
                            </p>

                            <!-- Member Count Badge -->
                            <p class="mb-2">
                                <span class="badge bg-info text-dark">
                                    <i class="bi bi-person-fill me-1"></i>
                                    <?php echo $club['member_count']; ?> / <?php echo $club['max_members']; ?> Members
                                </span>
                            </p>
                        </div>

                        <div class="card-footer bg-white border-0 pb-3">
                            <?php if (isLoggedIn()): ?>
                                <!-- Apply to Join Form -->
                                <form action="clubs.php" method="POST">
                                    <!-- Hidden field sends the club ID with the form -->
                                    <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-person-check-fill me-2"></i>Apply to Join
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- If not logged in, prompt to login -->
                                <a href="login.php" class="btn btn-outline-success w-100">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login to Apply
                                </a>
                            <?php endif; ?>
                        </div>

                    </div><!-- end .card -->
                </div><!-- end .col -->
            <?php endforeach; ?>
        <?php endif; ?>

    </div><!-- end .row -->
</div><!-- end .container -->

<?php include 'includes/footer.php'; ?>

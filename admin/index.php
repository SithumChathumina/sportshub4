<?php
/**
 * admin/index.php
 * Admin Dashboard for NSBM SportsHub
 * Shows summary statistics and quick links.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

// Only admins can access this page
requireAdmin();

// --- Fetch Summary Stats using COUNT queries ---

// Total registered users (students)
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

// Total clubs in the system
$total_clubs = $pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();

// Pending membership applications
$pending_memberships = $pdo->query("SELECT COUNT(*) FROM club_memberships WHERE status = 'pending'")->fetchColumn();

// Pending facility booking requests
$pending_bookings = $pdo->query("SELECT COUNT(*) FROM facility_bookings WHERE status = 'pending'")->fetchColumn();

// Total tournaments
$total_tournaments = $pdo->query("SELECT COUNT(*) FROM tournaments")->fetchColumn();

// Total training sessions
$total_sessions = $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn();

// Include Admin Header (sidebar layout)
include 'includes/admin_header.php';
?>

<!-- ===== ADMIN DASHBOARD CONTENT ===== -->

<h4 class="fw-bold mb-4">
    <i class="bi bi-speedometer2 text-success me-2"></i>Dashboard Overview
</h4>

<!-- ===== STAT CARDS ROW ===== -->
<div class="row g-4 mb-5">

    <!-- Card: Total Students -->
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg,#4CAF50,#1a6b3c);">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <div class="fs-1 fw-bold"><?php echo $total_users; ?></div>
                    <div class="text-white-50">Registered Students</div>
                </div>
                <i class="bi bi-people-fill fs-1 opacity-50"></i>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                <small class="text-white-50">All registered student accounts</small>
            </div>
        </div>
    </div>

    <!-- Card: Total Clubs -->
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg,#2196F3,#0d47a1);">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <div class="fs-1 fw-bold"><?php echo $total_clubs; ?></div>
                    <div class="text-white-50">Sports Clubs</div>
                </div>
                <i class="bi bi-shield-fill fs-1 opacity-50"></i>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                <a href="manage_clubs.php" class="text-white-50 small text-decoration-none">
                    <i class="bi bi-arrow-right me-1"></i>Manage Clubs
                </a>
            </div>
        </div>
    </div>

    <!-- Card: Pending Memberships -->
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg,#FF9800,#e65100);">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <div class="fs-1 fw-bold"><?php echo $pending_memberships; ?></div>
                    <div class="text-white-50">Pending Memberships</div>
                </div>
                <i class="bi bi-person-clock-fill fs-1 opacity-50"></i>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                <a href="manage_memberships.php" class="text-white-50 small text-decoration-none">
                    <i class="bi bi-arrow-right me-1"></i>Review Applications
                </a>
            </div>
        </div>
    </div>

    <!-- Card: Pending Bookings -->
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg,#9C27B0,#4a148c);">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <div class="fs-1 fw-bold"><?php echo $pending_bookings; ?></div>
                    <div class="text-white-50">Pending Bookings</div>
                </div>
                <i class="bi bi-building fs-1 opacity-50"></i>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                <a href="manage_bookings.php" class="text-white-50 small text-decoration-none">
                    <i class="bi bi-arrow-right me-1"></i>Manage Bookings
                </a>
            </div>
        </div>
    </div>

    <!-- Card: Tournaments -->
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg,#F44336,#b71c1c);">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <div class="fs-1 fw-bold"><?php echo $total_tournaments; ?></div>
                    <div class="text-white-50">Tournaments</div>
                </div>
                <i class="bi bi-award-fill fs-1 opacity-50"></i>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                <a href="tournaments.php" class="text-white-50 small text-decoration-none">
                    <i class="bi bi-arrow-right me-1"></i>Manage Tournaments
                </a>
            </div>
        </div>
    </div>

    <!-- Card: Training Sessions -->
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg,#00BCD4,#006064);">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <div class="fs-1 fw-bold"><?php echo $total_sessions; ?></div>
                    <div class="text-white-50">Training Sessions</div>
                </div>
                <i class="bi bi-calendar3 fs-1 opacity-50"></i>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                <a href="schedule.php" class="text-white-50 small text-decoration-none">
                    <i class="bi bi-arrow-right me-1"></i>Manage Schedule
                </a>
            </div>
        </div>
    </div>

</div><!-- end stats row -->

<!-- ===== QUICK LINKS ===== -->
<h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions</h5>
<div class="row g-3">
    <div class="col-6 col-md-4 col-lg-2">
        <a href="manage_clubs.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-1">
            <i class="bi bi-shield-plus fs-4"></i>
            <span class="small">Add Club</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="schedule.php" class="btn btn-outline-success w-100 py-3 d-flex flex-column align-items-center gap-1">
            <i class="bi bi-calendar-plus fs-4"></i>
            <span class="small">Add Session</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="tournaments.php" class="btn btn-outline-warning w-100 py-3 d-flex flex-column align-items-center gap-1">
            <i class="bi bi-trophy fs-4"></i>
            <span class="small">Add Tournament</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="manage_memberships.php" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-1">
            <i class="bi bi-person-check fs-4"></i>
            <span class="small">Memberships</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="manage_bookings.php" class="btn btn-outline-info w-100 py-3 d-flex flex-column align-items-center gap-1">
            <i class="bi bi-building-check fs-4"></i>
            <span class="small">Bookings</span>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="match_results.php" class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center gap-1">
            <i class="bi bi-bar-chart fs-4"></i>
            <span class="small">Results</span>
        </a>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>

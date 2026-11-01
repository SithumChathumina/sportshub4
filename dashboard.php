<?php
/**
 * dashboard.php
 * Student Dashboard for NSBM SportsHub
 * Shows the student's club memberships, training sessions,
 * tournament info, and facility bookings.
 */

session_start();
require_once 'config/db.php';
require_once 'includes/auth.php';

// Only logged-in students can access this page
requireLogin();

$user_id = $_SESSION['user_id'];

// --- 1. Fetch the student's club memberships ---
// JOIN with clubs table to get the club name
$stmt = $pdo->prepare(
    "SELECT cm.status, cm.applied_date, c.name AS club_name, c.sport_type
     FROM club_memberships cm
     JOIN clubs c ON cm.club_id = c.id
     WHERE cm.user_id = ?
     ORDER BY cm.applied_date DESC"
);
$stmt->execute([$user_id]);
$memberships = $stmt->fetchAll();

// --- 2. Fetch upcoming training sessions for clubs the student is in ---
// Only show sessions for clubs where membership is approved
$stmt = $pdo->prepare(
    "SELECT s.title, s.session_date, s.session_time, s.location, s.notes, c.name AS club_name
     FROM schedules s
     JOIN clubs c ON s.club_id = c.id
     JOIN club_memberships cm ON cm.club_id = s.club_id
     WHERE cm.user_id = ? AND cm.status = 'approved'
       AND s.session_date >= CURDATE()
     ORDER BY s.session_date ASC
     LIMIT 5"
);
$stmt->execute([$user_id]);
$sessions = $stmt->fetchAll();

// --- 3. Fetch upcoming tournaments ---
// Show all active/upcoming tournaments for now
$stmt = $pdo->query(
    "SELECT name, sport_type, start_date, end_date, venue, status
     FROM tournaments
     WHERE start_date >= CURDATE()
     ORDER BY start_date ASC
     LIMIT 5"
);
$tournaments = $stmt->fetchAll();

// --- 4. Fetch the student's facility bookings ---
$stmt = $pdo->prepare(
    "SELECT fb.booking_date, fb.time_slot, fb.purpose, fb.status, f.name AS facility_name
     FROM facility_bookings fb
     JOIN facilities f ON fb.facility_id = f.id
     WHERE fb.user_id = ?
     ORDER BY fb.booking_date DESC
     LIMIT 5"
);
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- ===== STUDENT DASHBOARD CONTENT ===== -->
<div class="container my-5">

    <!-- Welcome Header -->
    <div class="mb-4">
        <h2 class="fw-bold">
            <i class="bi bi-speedometer2 text-primary me-2"></i>
            Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
        </h2>
        <p class="text-muted">Here's a summary of your activities at NSBM SportsHub.</p>
    </div>

    <div class="row g-4">

        <!-- ===== SECTION 1: Club Memberships ===== -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>My Club Memberships</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($memberships)): ?>
                        <p class="text-muted">You haven't applied to any clubs yet.
                           <a href="clubs.php">Browse clubs</a>
                        </p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($memberships as $m): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong><?php echo htmlspecialchars($m['club_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($m['sport_type']); ?></small>
                                    </div>
                                    <!-- Status badge with color coding -->
                                    <?php
                                        // Pick badge color based on status
                                        $badge = match($m['status']) {
                                            'approved' => 'success',
                                            'pending'  => 'warning text-dark',
                                            'rejected' => 'danger',
                                            default    => 'secondary'
                                        };
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        <?php echo ucfirst($m['status']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="clubs.php" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-plus-circle me-1"></i>Join Another Club
                    </a>
                </div>
            </div>
        </div>

        <!-- ===== SECTION 2: Upcoming Training Sessions ===== -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Upcoming Training Sessions</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($sessions)): ?>
                        <p class="text-muted">No upcoming training sessions for your clubs.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($sessions as $s): ?>
                                <li class="list-group-item px-0">
                                    <strong><?php echo htmlspecialchars($s['title']); ?></strong>
                                    <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($s['club_name']); ?></span>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar2 me-1"></i><?php echo $s['session_date']; ?>
                                        &nbsp;|&nbsp;
                                        <i class="bi bi-clock me-1"></i><?php echo $s['session_time']; ?>
                                        &nbsp;|&nbsp;
                                        <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($s['location']); ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="schedule.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-calendar3 me-1"></i>View Full Schedule
                    </a>
                </div>
            </div>
        </div>

        <!-- ===== SECTION 3: Upcoming Tournaments ===== -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-award-fill me-2"></i>Upcoming Tournaments</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($tournaments)): ?>
                        <p class="text-muted">No upcoming tournaments at the moment.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($tournaments as $t): ?>
                                <li class="list-group-item px-0">
                                    <strong><?php echo htmlspecialchars($t['name']); ?></strong>
                                    <span class="badge bg-warning text-dark ms-2"><?php echo htmlspecialchars($t['sport_type']); ?></span>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-range me-1"></i>
                                        <?php echo $t['start_date']; ?> &mdash; <?php echo $t['end_date']; ?>
                                        &nbsp;|&nbsp;
                                        <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($t['venue']); ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="tournaments.php" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-award me-1"></i>View All Tournaments
                    </a>
                </div>
            </div>
        </div>

        <!-- ===== SECTION 4: My Facility Bookings ===== -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>My Facility Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($bookings)): ?>
                        <p class="text-muted">You have no facility bookings yet.
                           <a href="book_facility.php">Book one now!</a>
                        </p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($bookings as $b): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong><?php echo htmlspecialchars($b['facility_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo $b['booking_date']; ?> | <?php echo htmlspecialchars($b['time_slot']); ?>
                                        </small>
                                    </div>
                                    <?php
                                        $bbadge = match($b['status']) {
                                            'approved' => 'success',
                                            'pending'  => 'warning text-dark',
                                            'rejected' => 'danger',
                                            default    => 'secondary'
                                        };
                                    ?>
                                    <span class="badge bg-<?php echo $bbadge; ?>">
                                        <?php echo ucfirst($b['status']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="book_facility.php" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-plus-circle me-1"></i>Book a Facility
                    </a>
                </div>
            </div>
        </div>

    </div><!-- end .row -->
</div><!-- end .container -->

<?php include 'includes/footer.php'; ?>

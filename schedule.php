<?php
/**
 * schedule.php
 * Student / Public Training Schedule View for NSBM SportsHub
 * Allows students to browse upcoming training sessions and schedules by club.
 */

session_start();
require_once 'config/db.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_id      = $is_logged_in ? (int)$_SESSION['user_id'] : 0;

// Fetch clubs the logged-in student has joined (approved memberships)
$my_club_ids = [];
if ($is_logged_in) {
    $stmt = $pdo->prepare(
        "SELECT club_id FROM club_memberships WHERE user_id = ? AND status = 'approved'"
    );
    $stmt->execute([$user_id]);
    $my_club_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch all active clubs for filter dropdown
$all_clubs = $pdo->query("SELECT id, name, sport_type FROM clubs WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// Handle filter parameters
$filter_club = isset($_GET['club_id']) ? (int)$_GET['club_id'] : 0;
$filter_view = isset($_GET['view']) && $_GET['view'] === 'all' ? 'all' : 'upcoming';

// Build SQL query dynamically
$query = "SELECT s.*, c.name AS club_name, c.sport_type 
          FROM schedules s 
          JOIN clubs c ON s.club_id = c.id 
          WHERE 1=1";
$params = [];

if ($filter_view === 'upcoming') {
    $query .= " AND s.session_date >= CURDATE()";
}

if ($filter_club > 0) {
    $query .= " AND s.club_id = ?";
    $params[] = $filter_club;
}

$query .= " ORDER BY s.session_date ASC, s.session_time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$schedules = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- ===== TRAINING SCHEDULE HEADER ===== -->
<div class="bg-primary text-white py-5 mb-4" style="background: linear-gradient(135deg, #1a6b3c 0%, #0d47a1 100%) !important;">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="bi bi-calendar3 me-2"></i>Training Schedule</h1>
        <p class="lead mb-0">Stay updated with practice sessions and drills across all NSBM Sports Clubs</p>
    </div>
</div>

<div class="container mb-5">

    <!-- ===== FILTER & SEARCH BAR ===== -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="schedule.php" method="GET" class="row g-2 align-items-center">
                
                <!-- Filter by Club -->
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-filter"></i></span>
                        <select name="club_id" class="form-select" onchange="this.form.submit()">
                            <option value="0">All Sports Clubs</option>
                            <?php foreach ($all_clubs as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($filter_club === (int)$c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?> (<?php echo htmlspecialchars($c['sport_type']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Time Filter: Upcoming vs All -->
                <div class="col-md-4">
                    <div class="btn-group w-100" role="group">
                        <a href="schedule.php?club_id=<?php echo $filter_club; ?>&view=upcoming" 
                           class="btn <?php echo ($filter_view === 'upcoming') ? 'btn-success' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-clock-history me-1"></i>Upcoming Only
                        </a>
                        <a href="schedule.php?club_id=<?php echo $filter_club; ?>&view=all" 
                           class="btn <?php echo ($filter_view === 'all') ? 'btn-success' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-calendar2-range me-1"></i>All Sessions
                        </a>
                    </div>
                </div>

                <!-- Reset Button -->
                <div class="col-md-3 text-md-end">
                    <?php if ($filter_club > 0 || $filter_view === 'all'): ?>
                        <a href="schedule.php" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-circle me-1"></i>Reset Filter
                        </a>
                    <?php else: ?>
                        <span class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>Showing <strong><?php echo count($schedules); ?></strong> session(s)
                        </span>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </div>

    <!-- ===== SCHEDULE TABLE / CARDS ===== -->
    <?php if (empty($schedules)): ?>
        <div class="card shadow-sm border-0 text-center py-5">
            <div class="card-body">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3.5rem;"></i>
                <h4 class="mt-3 fw-bold text-muted">No training sessions scheduled</h4>
                <p class="text-muted">
                    <?php if ($filter_club > 0): ?>
                        There are no sessions for this club currently. Try viewing another club.
                    <?php else: ?>
                        Check back soon or consult your sports club coordinator.
                    <?php endif; ?>
                </p>
                <a href="clubs.php" class="btn btn-primary mt-2">
                    <i class="bi bi-people-fill me-1"></i>Browse Sports Clubs
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-table me-2 text-success"></i>Sessions Timetable
                </h5>
                <span class="badge bg-secondary"><?php echo count($schedules); ?> Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">Date & Time</th>
                                <th style="width: 25%;">Club / Sport</th>
                                <th style="width: 25%;">Session Title</th>
                                <th style="width: 20%;">Location</th>
                                <th style="width: 15%;">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $s): ?>
                                <?php 
                                    $is_my_club = in_array((int)$s['club_id'], $my_club_ids);
                                    $is_past = (strtotime($s['session_date']) < strtotime(date('Y-m-d')));
                                    $formatted_date = date('D, M d, Y', strtotime($s['session_date']));
                                    $formatted_time = date('h:i A', strtotime($s['session_time']));
                                ?>
                                <tr class="<?php echo $is_past ? 'text-muted bg-light' : ($is_my_club ? 'table-success bg-opacity-25' : ''); ?>">
                                    <td>
                                        <div class="fw-bold <?php echo $is_past ? 'text-muted' : 'text-primary'; ?>">
                                            <i class="bi bi-calendar-event me-1"></i><?php echo $formatted_date; ?>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i><?php echo $formatted_time; ?>
                                        </small>
                                        <?php if ($is_past): ?>
                                            <div><span class="badge bg-secondary" style="font-size: 0.65rem;">Completed</span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <?php echo htmlspecialchars($s['club_name']); ?>
                                        </div>
                                        <span class="badge bg-info text-dark" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($s['sport_type']); ?>
                                        </span>
                                        <?php if ($is_my_club): ?>
                                            <span class="badge bg-success" style="font-size: 0.75rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>My Club
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($s['title']); ?></strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        <?php echo htmlspecialchars($s['location']); ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($s['notes'])): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($s['notes']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted small">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>

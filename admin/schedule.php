<?php
/**
 * admin/schedule.php
 * Admin page to add and manage training sessions (schedule).
 */

session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

requireAdmin();

$message  = '';
$msg_type = '';

// --- Handle Delete Session ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $session_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt->execute([$session_id]);
    $message  = "Training session deleted.";
    $msg_type = "success";
}

// --- Handle Add New Training Session ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $club_id      = (int)($_POST['club_id']       ?? 0);
    $title        = trim($_POST['title']           ?? '');
    $session_date = trim($_POST['session_date']    ?? '');
    $session_time = trim($_POST['session_time']    ?? '');
    $location     = trim($_POST['location']        ?? '');
    $notes        = trim($_POST['notes']           ?? '');

    if ($club_id <= 0 || empty($title) || empty($session_date) || empty($session_time) || empty($location)) {
        $message  = "Please fill in all required fields.";
        $msg_type = "danger";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO schedules (club_id, title, session_date, session_time, location, notes)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$club_id, $title, $session_date, $session_time, $location, $notes]);
        $message  = "Training session added successfully!";
        $msg_type = "success";
    }
}

// --- Fetch All Active Clubs for the dropdown ---
$clubs = $pdo->query("SELECT id, name FROM clubs WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// --- Fetch All Training Sessions ---
$sessions = $pdo->query(
    "SELECT s.*, c.name AS club_name
     FROM schedules s
     JOIN clubs c ON s.club_id = c.id
     ORDER BY s.session_date DESC, s.session_time ASC"
)->fetchAll();

include 'includes/admin_header.php';
?>

<!-- ===== SCHEDULE MANAGEMENT CONTENT ===== -->
<h4 class="fw-bold mb-4">
    <i class="bi bi-calendar3 text-primary me-2"></i>Manage Training Schedule
</h4>

<!-- Feedback Message -->
<?php if ($message): ?>
    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
        <i class="bi bi-info-circle me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- ===== ADD SESSION FORM ===== -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Add Training Session</h5>
            </div>
            <div class="card-body p-4">
                <form action="schedule.php" method="POST">

                    <!-- Select Club -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Club <span class="text-danger">*</span></label>
                        <select class="form-select" name="club_id" required>
                            <option value="">-- Select Club --</option>
                            <?php foreach ($clubs as $c): ?>
                                <option value="<?php echo $c['id']; ?>">
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Session Title -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Session Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title"
                               placeholder="e.g. Morning Batting Practice" required>
                    </div>

                    <!-- Date -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="session_date" required>
                    </div>

                    <!-- Time -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" name="session_time" required>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="location"
                               placeholder="e.g. Main Cricket Ground" required>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Notes (optional)</label>
                        <textarea class="form-control" name="notes" rows="2"
                                  placeholder="Any additional info..."></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== SESSIONS LIST TABLE ===== -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white d-flex justify-content-between">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Training Sessions</h5>
                <span class="badge bg-light text-dark"><?php echo count($sessions); ?> sessions</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Club</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sessions)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No sessions added yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sessions as $i => $s): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($s['title']); ?></strong>
                                            <?php if ($s['notes']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($s['notes']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($s['club_name']); ?></span></td>
                                        <td><?php echo $s['session_date']; ?></td>
                                        <td><?php echo $s['session_time']; ?></td>
                                        <td><?php echo htmlspecialchars($s['location']); ?></td>
                                        <td>
                                            <a href="schedule.php?action=delete&id=<?php echo $s['id']; ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this session?');">
                                                <i class="bi bi-trash-fill"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div><!-- end row -->

<?php include 'includes/admin_footer.php'; ?>

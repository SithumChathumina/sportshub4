<?php
/**
 * admin/tournaments.php
 * Admin page to add and manage tournaments.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

requireAdmin();

$message  = '';
$msg_type = '';

// --- Handle Delete ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $tid = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM tournaments WHERE id = ?")->execute([$tid]);
    $message  = "Tournament deleted.";
    $msg_type = "success";
}

// --- Handle Add New Tournament ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name       = trim($_POST['name']       ?? '');
    $sport_type = trim($_POST['sport_type'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date   = trim($_POST['end_date']   ?? '');
    $venue      = trim($_POST['venue']      ?? '');
    $max_teams  = (int)($_POST['max_teams'] ?? 0);
    $desc       = trim($_POST['description'] ?? '');

    if (empty($name) || empty($sport_type) || empty($start_date) || empty($end_date) || empty($venue)) {
        $message  = "Please fill in all required fields.";
        $msg_type = "danger";
    } elseif ($end_date < $start_date) {
        $message  = "End date cannot be before the start date.";
        $msg_type = "danger";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO tournaments (name, sport_type, start_date, end_date, venue, max_teams, description, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'upcoming')"
        );
        $stmt->execute([$name, $sport_type, $start_date, $end_date, $venue, $max_teams, $desc]);
        $message  = "Tournament added successfully!";
        $msg_type = "success";
    }
}

// --- Fetch All Tournaments ---
$tournaments = $pdo->query(
    "SELECT * FROM tournaments ORDER BY start_date DESC"
)->fetchAll();

include 'includes/admin_header.php';
?>

<!-- ===== TOURNAMENTS MANAGEMENT CONTENT ===== -->
<h4 class="fw-bold mb-4">
    <i class="bi bi-award-fill text-warning me-2"></i>Manage Tournaments
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

    <!-- ===== ADD TOURNAMENT FORM ===== -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Add Tournament</h5>
            </div>
            <div class="card-body p-4">
                <form action="tournaments.php" method="POST">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tournament Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name"
                               placeholder="e.g. Inter-Faculty Cricket Cup" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sport Type <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="sport_type"
                               placeholder="e.g. Cricket" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Venue <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="venue"
                               placeholder="e.g. Main Sports Ground" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Max Teams</label>
                        <input type="number" class="form-control" name="max_teams" value="8" min="2">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="2"
                                  placeholder="Brief description..."></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning text-dark fw-bold">
                            <i class="bi bi-plus-circle me-2"></i>Add Tournament
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== TOURNAMENTS TABLE ===== -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white d-flex justify-content-between">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Tournaments</h5>
                <span class="badge bg-light text-dark"><?php echo count($tournaments); ?> tournaments</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Sport</th>
                                <th>Dates</th>
                                <th>Venue</th>
                                <th>Teams</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tournaments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No tournaments found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tournaments as $i => $t): ?>
                                    <?php
                                        $sbadge = match($t['status']) {
                                            'upcoming'   => 'primary',
                                            'ongoing'    => 'success',
                                            'completed'  => 'secondary',
                                            default      => 'info'
                                        };
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($t['name']); ?></strong>
                                            <?php if ($t['description']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($t['description'], 0, 50)); ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($t['sport_type']); ?></td>
                                        <td>
                                            <small>
                                                <?php echo $t['start_date']; ?>
                                                <br>to<br>
                                                <?php echo $t['end_date']; ?>
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($t['venue']); ?></td>
                                        <td><?php echo $t['max_teams']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $sbadge; ?>">
                                                <?php echo ucfirst($t['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="tournaments.php?action=delete&id=<?php echo $t['id']; ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this tournament?');">
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

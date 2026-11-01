<?php
/**
 * admin/manage_clubs.php
 * Full CRUD management for Sports Clubs
 * Admin can: Add, Edit (via modal), Delete clubs
 */

session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

// Only admins may access this page
requireAdmin();

$message  = '';
$msg_type = '';

// ============================================================
// HANDLE DELETE (GET request: ?action=delete&id=X)
// ============================================================
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $club_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM clubs WHERE id = ?");
    $stmt->execute([$club_id]);
    $message  = "Club deleted successfully.";
    $msg_type = "success";
}

// ============================================================
// HANDLE ADD NEW CLUB (POST request when 'add_club' button clicked)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {

    $name        = trim($_POST['name']        ?? '');
    $sport_type  = trim($_POST['sport_type']  ?? '');
    $description = trim($_POST['description'] ?? '');
    $max_members = (int)($_POST['max_members'] ?? 20);

    if (empty($name) || empty($sport_type)) {
        $message  = "Club name and sport type are required!";
        $msg_type = "danger";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO clubs (name, sport_type, description, max_members, status)
             VALUES (?, ?, ?, ?, 'active')"
        );
        $stmt->execute([$name, $sport_type, $description, $max_members]);
        $message  = "Club added successfully!";
        $msg_type = "success";
    }
}

// ============================================================
// HANDLE EDIT CLUB (POST request when 'edit_club' button clicked)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {

    $club_id     = (int)$_POST['club_id'];
    $name        = trim($_POST['name']        ?? '');
    $sport_type  = trim($_POST['sport_type']  ?? '');
    $description = trim($_POST['description'] ?? '');
    $max_members = (int)($_POST['max_members'] ?? 20);
    $status      = trim($_POST['status']      ?? 'active');

    if (empty($name) || empty($sport_type)) {
        $message  = "Club name and sport type are required!";
        $msg_type = "danger";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE clubs SET name=?, sport_type=?, description=?, max_members=?, status=?
             WHERE id=?"
        );
        $stmt->execute([$name, $sport_type, $description, $max_members, $status, $club_id]);
        $message  = "Club updated successfully!";
        $msg_type = "success";
    }
}

// --- Fetch all clubs ---
$clubs = $pdo->query(
    "SELECT c.*, COUNT(cm.id) AS member_count
     FROM clubs c
     LEFT JOIN club_memberships cm ON c.id = cm.club_id AND cm.status = 'approved'
     GROUP BY c.id
     ORDER BY c.name ASC"
)->fetchAll();

include 'includes/admin_header.php';
?>

<!-- ===== MANAGE CLUBS CONTENT ===== -->

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-fill text-primary me-2"></i>Manage Clubs</h4>
    <!-- Button to open Add Club Modal -->
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addClubModal">
        <i class="bi bi-plus-circle-fill me-2"></i>Add New Club
    </button>
</div>

<!-- Feedback Message -->
<?php if ($message): ?>
    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
        <i class="bi bi-info-circle me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ===== CLUBS TABLE ===== -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Club Name</th>
                        <th>Sport Type</th>
                        <th>Max Members</th>
                        <th>Current Members</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clubs)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No clubs found. Add one!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clubs as $i => $c): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($c['description']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($c['sport_type']); ?></td>
                                <td><?php echo $c['max_members']; ?></td>
                                <td><?php echo $c['member_count']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($c['status'] === 'active') ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($c['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Edit button: opens modal and fills in data via JS -->
                                    <button class="btn btn-sm btn-warning me-1"
                                        onclick="openEditModal(
                                            <?php echo $c['id']; ?>,
                                            '<?php echo addslashes(htmlspecialchars($c['name'])); ?>',
                                            '<?php echo addslashes(htmlspecialchars($c['sport_type'])); ?>',
                                            '<?php echo addslashes(htmlspecialchars($c['description'])); ?>',
                                            <?php echo $c['max_members']; ?>,
                                            '<?php echo $c['status']; ?>'
                                        )">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </button>
                                    <!-- Delete button: confirm before deleting -->
                                    <a href="manage_clubs.php?action=delete&id=<?php echo $c['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this club?');">
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

<!-- ===== ADD CLUB MODAL ===== -->
<div class="modal fade" id="addClubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="manage_clubs.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Club</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Club Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Cricket Club" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sport Type</label>
                        <input type="text" class="form-control" name="sport_type" placeholder="e.g. Cricket" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Brief description..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Max Members</label>
                        <input type="number" class="form-control" name="max_members" value="20" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-2"></i>Add Club</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== EDIT CLUB MODAL ===== -->
<div class="modal fade" id="editClubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="manage_clubs.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="club_id" id="editClubId">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit Club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Club Name</label>
                        <input type="text" class="form-control" name="name" id="editClubName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sport Type</label>
                        <input type="text" class="form-control" name="sport_type" id="editClubSport" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" id="editClubDesc" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Max Members</label>
                        <input type="number" class="form-control" name="max_members" id="editClubMax" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="status" id="editClubStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-save me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript to populate the Edit Modal with existing club data -->
<script>
function openEditModal(id, name, sport, desc, max, status) {
    // Fill in the modal form fields
    document.getElementById('editClubId').value    = id;
    document.getElementById('editClubName').value  = name;
    document.getElementById('editClubSport').value = sport;
    document.getElementById('editClubDesc').value  = desc;
    document.getElementById('editClubMax').value   = max;
    document.getElementById('editClubStatus').value = status;
    // Show the modal using Bootstrap's JS API
    var modal = new bootstrap.Modal(document.getElementById('editClubModal'));
    modal.show();
}
</script>

<?php include 'includes/admin_footer.php'; ?>

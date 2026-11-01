<?php
/**
 * admin/manage_memberships.php
 * Admin page to view, approve, and reject club membership applications.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

requireAdmin();

$message  = '';
$msg_type = '';

// --- Handle Approve / Reject via GET parameter ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $membership_id = (int)$_GET['id'];
    $action        = $_GET['action'];

    if ($action === 'approve') {
        $new_status = 'approved';
        $message    = "Membership application approved!";
        $msg_type   = "success";
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
        $message    = "Membership application rejected.";
        $msg_type   = "warning";
    }

    if (isset($new_status)) {
        $stmt = $pdo->prepare("UPDATE club_memberships SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $membership_id]);
    }
}

// --- Fetch All Membership Applications ---
// JOIN with users and clubs to get readable names
$memberships = $pdo->query(
    "SELECT cm.id, cm.status, cm.applied_date,
            u.name AS student_name, u.student_id, u.email,
            c.name AS club_name, c.sport_type
     FROM club_memberships cm
     JOIN users u ON cm.user_id = u.id
     JOIN clubs c ON cm.club_id = c.id
     ORDER BY cm.applied_date DESC"
)->fetchAll();

include 'includes/admin_header.php';
?>

<!-- ===== MANAGE MEMBERSHIPS CONTENT ===== -->
<h4 class="fw-bold mb-4">
    <i class="bi bi-person-check-fill text-warning me-2"></i>Manage Club Memberships
</h4>

<!-- Feedback Message -->
<?php if ($message): ?>
    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
        <i class="bi bi-info-circle me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Summary Counts -->
<div class="mb-3">
    <span class="badge bg-warning text-dark me-2">
        Pending: <?php echo count(array_filter($memberships, fn($m) => $m['status'] === 'pending')); ?>
    </span>
    <span class="badge bg-success me-2">
        Approved: <?php echo count(array_filter($memberships, fn($m) => $m['status'] === 'approved')); ?>
    </span>
    <span class="badge bg-danger">
        Rejected: <?php echo count(array_filter($memberships, fn($m) => $m['status'] === 'rejected')); ?>
    </span>
</div>

<!-- Memberships Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Club</th>
                        <th>Sport Type</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($memberships)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No membership applications found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($memberships as $i => $m): ?>
                            <?php
                                $badge = match($m['status']) {
                                    'approved' => 'success',
                                    'pending'  => 'warning text-dark',
                                    'rejected' => 'danger',
                                    default    => 'secondary'
                                };
                            ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($m['student_name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($m['student_id']); ?></small>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($m['email']); ?></small>
                                </td>
                                <td><strong><?php echo htmlspecialchars($m['club_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($m['sport_type']); ?></td>
                                <td><?php echo $m['applied_date']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        <?php echo ucfirst($m['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($m['status'] === 'pending'): ?>
                                        <a href="manage_memberships.php?action=approve&id=<?php echo $m['id']; ?>"
                                           class="btn btn-sm btn-success me-1"
                                           onclick="return confirm('Approve this membership?');">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </a>
                                        <a href="manage_memberships.php?action=reject&id=<?php echo $m['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Reject this membership?');">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>

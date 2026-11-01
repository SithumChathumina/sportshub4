<?php
/**
 * admin/manage_bookings.php
 * Admin page to view, approve, and reject facility booking requests.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

requireAdmin();

$message  = '';
$msg_type = '';

// --- Handle Approve / Reject via GET parameter ---
// e.g. ?action=approve&id=5  or  ?action=reject&id=5
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    $action     = $_GET['action'];

    if ($action === 'approve') {
        $new_status = 'approved';
        $message    = "Booking approved successfully!";
        $msg_type   = "success";
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
        $message    = "Booking rejected.";
        $msg_type   = "warning";
    }

    // Only update if we have a valid action
    if (isset($new_status)) {
        $stmt = $pdo->prepare("UPDATE facility_bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);
    }
}

// --- Fetch All Bookings with Student Name and Facility Name ---
// We use JOINs to get the names instead of just IDs
$bookings = $pdo->query(
    "SELECT fb.id, fb.booking_date, fb.time_slot, fb.purpose, fb.status,
            u.name AS student_name, u.student_id,
            f.name AS facility_name
     FROM facility_bookings fb
     JOIN users u ON fb.user_id = u.id
     JOIN facilities f ON fb.facility_id = f.id
     ORDER BY fb.booking_date DESC"
)->fetchAll();

include 'includes/admin_header.php';
?>

<!-- ===== MANAGE BOOKINGS CONTENT ===== -->
<h4 class="fw-bold mb-4">
    <i class="bi bi-building text-info me-2"></i>Manage Facility Bookings
</h4>

<!-- Feedback Message -->
<?php if ($message): ?>
    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
        <i class="bi bi-info-circle me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filter Tabs (visual only - could add JS filter later) -->
<div class="mb-3">
    <span class="badge bg-warning text-dark me-2">
        <i class="bi bi-hourglass-split me-1"></i>
        Pending: <?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'pending')); ?>
    </span>
    <span class="badge bg-success me-2">
        <i class="bi bi-check-circle me-1"></i>
        Approved: <?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'approved')); ?>
    </span>
    <span class="badge bg-danger">
        <i class="bi bi-x-circle me-1"></i>
        Rejected: <?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'rejected')); ?>
    </span>
</div>

<!-- Bookings Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Facility</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No bookings found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $i => $b): ?>
                            <?php
                                $badge = match($b['status']) {
                                    'approved' => 'success',
                                    'pending'  => 'warning text-dark',
                                    'rejected' => 'danger',
                                    default    => 'secondary'
                                };
                            ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($b['student_name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($b['student_id']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($b['facility_name']); ?></td>
                                <td><?php echo $b['booking_date']; ?></td>
                                <td><?php echo htmlspecialchars($b['time_slot']); ?></td>
                                <td><?php echo htmlspecialchars($b['purpose']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        <?php echo ucfirst($b['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'pending'): ?>
                                        <!-- Approve Button -->
                                        <a href="manage_bookings.php?action=approve&id=<?php echo $b['id']; ?>"
                                           class="btn btn-sm btn-success me-1"
                                           onclick="return confirm('Approve this booking?');">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </a>
                                        <!-- Reject Button -->
                                        <a href="manage_bookings.php?action=reject&id=<?php echo $b['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Reject this booking?');">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">No action needed</span>
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

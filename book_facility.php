<?php
/**
 * book_facility.php
 * Facility Booking Page for NSBM SportsHub
 * Students can browse available facilities and make a booking request.
 */

session_start();
require_once 'config/db.php';
require_once 'includes/auth.php';

// Only logged-in users can book a facility
requireLogin();

$user_id = $_SESSION['user_id'];
$errors  = [];
$success = '';

// --- Handle Booking Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and sanitize inputs
    $facility_id = (int)($_POST['facility_id'] ?? 0);
    $date        = trim($_POST['booking_date'] ?? '');
    $time_slot   = trim($_POST['time_slot']    ?? '');
    $purpose     = trim($_POST['purpose']      ?? '');

    // --- Validate ---
    if ($facility_id <= 0) {
        $errors[] = "Please select a facility.";
    }
    if (empty($date)) {
        $errors[] = "Please select a date.";
    } elseif ($date < date('Y-m-d')) {
        // Can't book in the past
        $errors[] = "Booking date cannot be in the past.";
    }
    if (empty($time_slot)) {
        $errors[] = "Please select a time slot.";
    }
    if (empty($purpose)) {
        $errors[] = "Please enter the purpose of booking.";
    }

    if (empty($errors)) {
        // Check if this facility is already booked at the same time
        $stmt = $pdo->prepare(
            "SELECT id FROM facility_bookings
             WHERE facility_id = ? AND booking_date = ? AND time_slot = ? AND status != 'rejected'"
        );
        $stmt->execute([$facility_id, $date, $time_slot]);
        if ($stmt->fetch()) {
            $errors[] = "This facility is already booked for that date and time slot. Please choose another.";
        } else {
            // Insert the booking with status='pending' for admin to approve
            $stmt = $pdo->prepare(
                "INSERT INTO facility_bookings (user_id, facility_id, booking_date, time_slot, purpose, status)
                 VALUES (?, ?, ?, ?, ?, 'pending')"
            );
            $stmt->execute([$user_id, $facility_id, $date, $time_slot, $purpose]);
            $success = "Your booking request has been submitted! Awaiting admin approval.";
        }
    }
}

// --- Fetch All Available Facilities ---
$facilities = $pdo->query("SELECT * FROM facilities WHERE status = 'available' ORDER BY name ASC")->fetchAll();

// --- Fetch the User's Existing Bookings ---
$stmt = $pdo->prepare(
    "SELECT fb.booking_date, fb.time_slot, fb.purpose, fb.status, f.name AS facility_name
     FROM facility_bookings fb
     JOIN facilities f ON fb.facility_id = f.id
     WHERE fb.user_id = ?
     ORDER BY fb.booking_date DESC"
);
$stmt->execute([$user_id]);
$my_bookings = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- ===== BOOK FACILITY PAGE CONTENT ===== -->
<div class="container my-5">

    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-building text-info me-2"></i>Book a Facility</h2>
        <p class="text-muted">Reserve sports facilities for your training or events.</p>
    </div>

    <div class="row g-4">

        <!-- ===== BOOKING FORM ===== -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>New Booking Request</h5>
                </div>
                <div class="card-body p-4">

                    <!-- Success Message -->
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Booking Form -->
                    <form action="book_facility.php" method="POST">

                        <!-- Select Facility -->
                        <div class="mb-3">
                            <label for="facility_id" class="form-label fw-semibold">Select Facility</label>
                            <select class="form-select" id="facility_id" name="facility_id" required>
                                <option value="">-- Choose a facility --</option>
                                <?php foreach ($facilities as $f): ?>
                                    <option value="<?php echo $f['id']; ?>">
                                        <?php echo htmlspecialchars($f['name']); ?> (<?php echo htmlspecialchars($f['location']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="mb-3">
                            <label for="booking_date" class="form-label fw-semibold">Date</label>
                            <input type="date" class="form-control" id="booking_date" name="booking_date"
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <!-- Time Slot -->
                        <div class="mb-3">
                            <label for="time_slot" class="form-label fw-semibold">Time Slot</label>
                            <select class="form-select" id="time_slot" name="time_slot" required>
                                <option value="">-- Select a time slot --</option>
                                <option value="6:00 AM - 8:00 AM">6:00 AM – 8:00 AM</option>
                                <option value="8:00 AM - 10:00 AM">8:00 AM – 10:00 AM</option>
                                <option value="10:00 AM - 12:00 PM">10:00 AM – 12:00 PM</option>
                                <option value="12:00 PM - 2:00 PM">12:00 PM – 2:00 PM</option>
                                <option value="2:00 PM - 4:00 PM">2:00 PM – 4:00 PM</option>
                                <option value="4:00 PM - 6:00 PM">4:00 PM – 6:00 PM</option>
                                <option value="6:00 PM - 8:00 PM">6:00 PM – 8:00 PM</option>
                            </select>
                        </div>

                        <!-- Purpose -->
                        <div class="mb-4">
                            <label for="purpose" class="form-label fw-semibold">Purpose of Booking</label>
                            <input type="text" class="form-control" id="purpose" name="purpose"
                                   placeholder="e.g. Cricket practice" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-info text-white">
                                <i class="bi bi-send-fill me-2"></i>Submit Booking Request
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- ===== MY BOOKINGS TABLE ===== -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>My Booking History</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($my_bookings)): ?>
                        <p class="text-muted p-4">You have no bookings yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Facility</th>
                                        <th>Date</th>
                                        <th>Time Slot</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($my_bookings as $b): ?>
                                        <?php
                                            $bbadge = match($b['status']) {
                                                'approved' => 'success',
                                                'pending'  => 'warning text-dark',
                                                'rejected' => 'danger',
                                                default    => 'secondary'
                                            };
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($b['facility_name']); ?></td>
                                            <td><?php echo $b['booking_date']; ?></td>
                                            <td><?php echo htmlspecialchars($b['time_slot']); ?></td>
                                            <td><?php echo htmlspecialchars($b['purpose']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $bbadge; ?>">
                                                    <?php echo ucfirst($b['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- end .row -->
</div><!-- end .container -->

<?php include 'includes/footer.php'; ?>

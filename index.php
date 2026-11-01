<?php
/**
 * index.php
 * NSBM SportsHub - Public Homepage
 * Shows hero banner, featured clubs, and upcoming events
 */

session_start();
require_once 'config/db.php';

// Fetch active clubs to display on homepage
$clubs = $pdo->query(
    "SELECT c.*, COUNT(cm.id) AS member_count
     FROM clubs c
     LEFT JOIN club_memberships cm ON c.id = cm.club_id AND cm.status = 'approved'
     WHERE c.status = 'active'
     GROUP BY c.id
     ORDER BY member_count DESC
     LIMIT 6"
)->fetchAll();

// Fetch upcoming tournaments
$tournaments = $pdo->query(
    "SELECT * FROM tournaments
     WHERE start_date >= CURDATE() AND status = 'upcoming'
     ORDER BY start_date ASC
     LIMIT 3"
)->fetchAll();

include 'includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<section style="background: linear-gradient(135deg, #00693E 0%, #004d2e 100%); min-height: 40vh; display:flex; align-items:center;">
    <div class="container text-white text-center py-5">
        <h1 class="display-3 fw-bold mb-3">
            <i class="bi bi-trophy-fill me-3"></i>NSBM SportsHub
        </h1>
        <p class="lead fs-4 mb-4">
            Your all-in-one platform for sports clubs, training schedules, tournaments,<br>
            and facility bookings at NSBM Green University.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-warning btn-lg fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i>My Dashboard
                </a>
                <a href="clubs.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-people-fill me-2"></i>Browse Clubs
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-warning btn-lg fw-bold">
                    <i class="bi bi-person-plus-fill me-2"></i>Join SportsHub
                </a>
                <a href="login.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </a>
            <?php endif; ?>
        </div>
        <!-- Quick stats row -->
        <div class="row g-3 mt-5 justify-content-center">
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                    <h3 class="fw-bold mb-0"><?php echo count($clubs); ?>+</h3>
                    <small>Active Clubs</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                    <h3 class="fw-bold mb-0"><?php echo count($tournaments); ?></h3>
                    <small>Upcoming Tournaments</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                    <h3 class="fw-bold mb-0">6</h3>
                    <small>Facilities Available</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                    <h3 class="fw-bold mb-0">24/7</h3>
                    <small>Online Booking</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FEATURED CLUBS SECTION ===== -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">🏅 Featured Sports Clubs</h2>
            <p class="text-muted">Join a club that matches your passion</p>
        </div>
        <div class="row g-4">
            <?php foreach ($clubs as $club): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <!-- Color banner based on sport type -->
                        <div class="card-img-top d-flex align-items-center justify-content-center"
                             style="height:80px; background:linear-gradient(135deg,#00693E,#28a745);">
                            <i class="bi bi-people-fill fs-1 text-white"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($club['name']); ?></h5>
                            <span class="badge bg-success mb-2"><?php echo htmlspecialchars($club['sport_type']); ?></span>
                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(substr($club['description'], 0, 100)) . '...'; ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-person-fill me-1"></i>
                                <?php echo $club['member_count']; ?> / <?php echo $club['max_members']; ?> members
                            </small>
                            <a href="clubs.php" class="btn btn-sm btn-outline-success">
                                View Club <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="clubs.php" class="btn btn-success btn-lg">
                <i class="bi bi-grid-fill me-2"></i>View All Clubs
            </a>
        </div>
    </div>
</section>

<!-- ===== UPCOMING TOURNAMENTS SECTION ===== -->
<?php if (!empty($tournaments)): ?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">🏆 Upcoming Tournaments</h2>
            <p class="text-muted">Register now and compete for the championship</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php foreach ($tournaments as $t): ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 border-top border-warning border-4">
                        <div class="card-body">
                            <h5 class="fw-bold"><?php echo htmlspecialchars($t['name']); ?></h5>
                            <span class="badge bg-warning text-dark mb-2"><?php echo htmlspecialchars($t['sport_type']); ?></span>
                            <p class="small text-muted mb-1">
                                <i class="bi bi-calendar-range me-1"></i>
                                <?php echo $t['start_date']; ?> — <?php echo $t['end_date']; ?>
                            </p>
                            <p class="small text-muted mb-0">
                                <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($t['venue']); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="<?php echo isset($_SESSION['user_id']) ? 'tournaments.php' : 'login.php'; ?>"
                               class="btn btn-warning btn-sm w-100 text-dark fw-bold">
                                <i class="bi bi-trophy me-1"></i>Register Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== HOW IT WORKS SECTION ===== -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">How It Works</h2>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="p-3">
                    <div class="fs-1 text-success mb-2">①</div>
                    <h5 class="fw-bold">Register</h5>
                    <p class="text-muted small">Create your student account with your NSBM student ID.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3">
                    <div class="fs-1 text-success mb-2">②</div>
                    <h5 class="fw-bold">Join a Club</h5>
                    <p class="text-muted small">Browse sports clubs and apply for membership.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3">
                    <div class="fs-1 text-success mb-2">③</div>
                    <h5 class="fw-bold">Train & Compete</h5>
                    <p class="text-muted small">View schedules, register for tournaments, and book facilities.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3">
                    <div class="fs-1 text-success mb-2">④</div>
                    <h5 class="fw-bold">Track Progress</h5>
                    <p class="text-muted small">Monitor your participation and results on your dashboard.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<?php
/**
 * tournaments.php
 * Student / Public Tournaments & Fixtures Page for NSBM SportsHub
 * Allows students to browse upcoming/ongoing tournaments, register to participate,
 * and view match results & scores.
 */

session_start();
require_once 'config/db.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_id      = $is_logged_in ? (int)$_SESSION['user_id'] : 0;
$message      = '';
$msg_type     = '';

// --- Ensure tournament_registrations table exists gracefully ---
$pdo->exec("
    CREATE TABLE IF NOT EXISTS tournament_registrations (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        tournament_id INT NOT NULL,
        user_id       INT NOT NULL,
        team_name     VARCHAR(100) DEFAULT NULL,
        registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status        ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
        FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_tournament (tournament_id, user_id)
    )
");

// --- Handle Student Tournament Registration (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    if (!$is_logged_in) {
        header("Location: login.php");
        exit();
    }

    $tournament_id = (int)($_POST['tournament_id'] ?? 0);
    $team_name     = trim($_POST['team_name'] ?? '');

    if ($tournament_id <= 0) {
        $message  = "Invalid tournament selected.";
        $msg_type = "danger";
    } else {
        // Check if already registered
        $check_stmt = $pdo->prepare("SELECT id FROM tournament_registrations WHERE tournament_id = ? AND user_id = ?");
        $check_stmt->execute([$tournament_id, $user_id]);
        
        if ($check_stmt->fetch()) {
            $message  = "You are already registered for this tournament!";
            $msg_type = "warning";
        } else {
            // Register student
            $reg_stmt = $pdo->prepare(
                "INSERT INTO tournament_registrations (tournament_id, user_id, team_name, status) 
                 VALUES (?, ?, ?, 'confirmed')"
            );
            $reg_stmt->execute([$tournament_id, $user_id, $team_name]);
            $message  = "🎉 Registration successful! You have entered this tournament.";
            $msg_type = "success";
        }
    }
}

// --- Fetch Tournaments by status filter ---
$status_filter = $_GET['status'] ?? 'all';
$query = "SELECT t.*, 
                 COUNT(tr.id) AS registered_count
          FROM tournaments t
          LEFT JOIN tournament_registrations tr ON t.id = tr.tournament_id AND tr.status = 'confirmed'
          WHERE 1=1";
$params = [];

if (in_array($status_filter, ['upcoming', 'ongoing', 'completed'])) {
    $query .= " AND t.status = ?";
    $params[] = $status_filter;
}

$query .= " GROUP BY t.id ORDER BY t.start_date ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tournaments = $stmt->fetchAll();

// --- Fetch User's Registered Tournament IDs ---
$my_registered_tids = [];
if ($is_logged_in) {
    $user_reg_stmt = $pdo->prepare("SELECT tournament_id FROM tournament_registrations WHERE user_id = ? AND status = 'confirmed'");
    $user_reg_stmt->execute([$user_id]);
    $my_registered_tids = $user_reg_stmt->fetchAll(PDO::FETCH_COLUMN);
}

// --- Fetch Match Results for Tournaments ---
$results_stmt = $pdo->query("
    SELECT mr.*, t.name AS tournament_name, t.sport_type
    FROM match_results mr
    JOIN tournaments t ON mr.tournament_id = t.id
    ORDER BY mr.match_date DESC, mr.id DESC
    LIMIT 10
");
$match_results = $results_stmt->fetchAll();

include 'includes/header.php';
?>

<!-- ===== HERO BANNER ===== -->
<div class="bg-primary text-white py-5 mb-4" style="background: linear-gradient(135deg, #b8860b 0%, #1a6b3c 100%) !important;">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="bi bi-award-fill me-2"></i>Tournaments & Fixtures</h1>
        <p class="lead mb-0">Compete, view match fixtures, and track scores across NSBM sports events</p>
    </div>
</div>

<div class="container mb-5">

    <!-- Feedback Message -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i><?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Status Tabs / Filter -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link <?php echo ($status_filter === 'all') ? 'active bg-success' : 'text-dark'; ?>" 
                   href="tournaments.php?status=all">All Tournaments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($status_filter === 'upcoming') ? 'active bg-success' : 'text-dark'; ?>" 
                   href="tournaments.php?status=upcoming">Upcoming</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($status_filter === 'ongoing') ? 'active bg-success' : 'text-dark'; ?>" 
                   href="tournaments.php?status=ongoing">Ongoing</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($status_filter === 'completed') ? 'active bg-success' : 'text-dark'; ?>" 
                   href="tournaments.php?status=completed">Completed</a>
            </li>
        </ul>
        <div>
            <a href="#results-section" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-trophy me-1"></i>View Match Results
            </a>
        </div>
    </div>

    <!-- ===== TOURNAMENT CARDS ===== -->
    <div class="row g-4 mb-5">
        <?php if (empty($tournaments)): ?>
            <div class="col-12">
                <div class="card shadow-sm border-0 text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-award text-muted" style="font-size: 3.5rem;"></i>
                        <h4 class="mt-3 fw-bold text-muted">No tournaments found</h4>
                        <p class="text-muted">No tournaments match the selected filter at this moment.</p>
                        <a href="tournaments.php" class="btn btn-primary">View All</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($tournaments as $t): ?>
                <?php
                    $is_registered = in_array((int)$t['id'], $my_registered_tids);
                    $badge_class = match($t['status']) {
                        'upcoming'  => 'warning text-dark',
                        'ongoing'   => 'success',
                        'completed' => 'secondary',
                        default     => 'info'
                    };
                    $date_range = date('M d', strtotime($t['start_date'])) . ' &mdash; ' . date('M d, Y', strtotime($t['end_date']));
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 d-flex flex-column">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?php echo $badge_class; ?> px-2 py-1">
                                <?php echo ucfirst($t['status']); ?>
                            </span>
                            <span class="badge bg-light text-secondary border">
                                <i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($t['sport_type']); ?>
                            </span>
                        </div>
                        <div class="card-body flex-grow-1">
                            <h5 class="card-title fw-bold text-dark mt-1">
                                <?php echo htmlspecialchars($t['name']); ?>
                            </h5>
                            <p class="card-text text-muted small mb-3">
                                <?php echo !empty($t['description']) ? htmlspecialchars($t['description']) : 'Official tournament hosted by NSBM Sports Division.'; ?>
                            </p>
                            
                            <ul class="list-unstyled small text-muted mb-0">
                                <li class="mb-1">
                                    <i class="bi bi-calendar-range text-primary me-2"></i><strong>Dates:</strong> <?php echo $date_range; ?>
                                </li>
                                <li class="mb-1">
                                    <i class="bi bi-geo-alt-fill text-danger me-2"></i><strong>Venue:</strong> <?php echo htmlspecialchars($t['venue']); ?>
                                </li>
                                <li>
                                    <i class="bi bi-people-fill text-success me-2"></i><strong>Slots:</strong> 
                                    <?php echo $t['registered_count']; ?> / <?php echo $t['max_teams']; ?> registered
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                            <?php if ($is_registered): ?>
                                <button class="btn btn-outline-success w-100 disabled" disabled>
                                    <i class="bi bi-check-circle-fill me-1"></i>Registered
                                </button>
                            <?php elseif ($t['status'] === 'upcoming'): ?>
                                <?php if ($is_logged_in): ?>
                                    <button class="btn btn-success w-100" 
                                            onclick="openRegisterModal(<?php echo $t['id']; ?>, '<?php echo addslashes(htmlspecialchars($t['name'])); ?>')">
                                        <i class="bi bi-pencil-square me-1"></i>Register to Compete
                                    </button>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Login to Register
                                    </a>
                                <?php endif; ?>
                            <?php elseif ($t['status'] === 'ongoing'): ?>
                                <button class="btn btn-outline-primary w-100 disabled" disabled>
                                    <i class="bi bi-hourglass-split me-1"></i>Tournament in Progress
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary w-100 disabled" disabled>
                                    <i class="bi bi-flag-fill me-1"></i>Tournament Ended
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ===== MATCH RESULTS & SCORES SECTION ===== -->
    <div id="results-section" class="pt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-bar-chart-fill text-warning me-2"></i>Recent Match Results & Scores
                </h5>
                <span class="badge bg-warning text-dark">Live Updates</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($match_results)): ?>
                    <p class="text-muted text-center py-4 mb-0">No match results recorded yet. Check back during tournaments!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Tournament</th>
                                    <th class="text-center">Fixture / Matchup</th>
                                    <th class="text-center">Score</th>
                                    <th>Result / Stage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($match_results as $res): ?>
                                    <?php 
                                        $team1_won = $res['score_team1'] > $res['score_team2'];
                                        $team2_won = $res['score_team2'] > $res['score_team1'];
                                    ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($res['match_date'])); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($res['tournament_name']); ?></strong>
                                            <br>
                                            <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($res['sport_type']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="<?php echo $team1_won ? 'fw-bold text-success' : ''; ?>">
                                                <?php echo htmlspecialchars($res['team1']); ?>
                                            </span>
                                            <span class="text-muted mx-2">vs</span>
                                            <span class="<?php echo $team2_won ? 'fw-bold text-success' : ''; ?>">
                                                <?php echo htmlspecialchars($res['team2']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-dark fs-6 px-3">
                                                <?php echo (int)$res['score_team1']; ?> &mdash; <?php echo (int)$res['score_team2']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo !empty($res['notes']) ? htmlspecialchars($res['notes']) : 'Match Finished'; ?>
                                            </small>
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

</div>

<!-- ===== REGISTRATION MODAL ===== -->
<div class="modal fade" id="tournamentRegisterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="tournaments.php" method="POST">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="tournament_id" id="modalTournamentId">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Register for Tournament
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        You are registering for: <strong id="modalTournamentName" class="text-success"></strong>
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Team Name (Optional / Individual)</label>
                        <input type="text" class="form-control" name="team_name" placeholder="e.g. CS Strikers or Individual">
                        <small class="text-muted">If you are competing as a team, enter your team name.</small>
                    </div>
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Registration is tied to your student account: <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Confirm Registration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRegisterModal(id, name) {
    document.getElementById('modalTournamentId').value = id;
    document.getElementById('modalTournamentName').textContent = name;
    var modal = new bootstrap.Modal(document.getElementById('tournamentRegisterModal'));
    modal.show();
}
</script>

<?php include 'includes/footer.php'; ?>

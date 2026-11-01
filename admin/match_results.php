<?php
/**
 * admin/match_results.php
 * Admin page to add and view match results for tournaments.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

requireAdmin();

$message  = '';
$msg_type = '';

// --- Handle Delete Match Result ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $rid = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM match_results WHERE id = ?")->execute([$rid]);
    $message  = "Match result deleted.";
    $msg_type = "success";
}

// --- Handle Add New Match Result ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tournament_id = (int)($_POST['tournament_id'] ?? 0);
    $team1         = trim($_POST['team1']         ?? '');
    $team2         = trim($_POST['team2']         ?? '');
    $score_team1   = (int)($_POST['score_team1']  ?? 0);
    $score_team2   = (int)($_POST['score_team2']  ?? 0);
    $match_date    = trim($_POST['match_date']    ?? '');
    $notes         = trim($_POST['notes']         ?? '');

    if ($tournament_id <= 0 || empty($team1) || empty($team2) || empty($match_date)) {
        $message  = "Please fill in all required fields.";
        $msg_type = "danger";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO match_results (tournament_id, team1, team2, score_team1, score_team2, match_date, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$tournament_id, $team1, $team2, $score_team1, $score_team2, $match_date, $notes]);
        $message  = "Match result added successfully!";
        $msg_type = "success";
    }
}

// --- Fetch All Tournaments (for dropdown) ---
$tournaments = $pdo->query("SELECT id, name FROM tournaments ORDER BY start_date DESC")->fetchAll();

// --- Fetch All Match Results with Tournament Name ---
$results = $pdo->query(
    "SELECT mr.*, t.name AS tournament_name
     FROM match_results mr
     JOIN tournaments t ON mr.tournament_id = t.id
     ORDER BY mr.match_date DESC"
)->fetchAll();

include 'includes/admin_header.php';
?>

<!-- ===== MATCH RESULTS MANAGEMENT CONTENT ===== -->
<h4 class="fw-bold mb-4">
    <i class="bi bi-bar-chart-fill text-secondary me-2"></i>Match Results
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

    <!-- ===== ADD MATCH RESULT FORM ===== -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-square me-2"></i>Add Match Result</h5>
            </div>
            <div class="card-body p-4">
                <form action="match_results.php" method="POST">

                    <!-- Select Tournament -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tournament <span class="text-danger">*</span></label>
                        <select class="form-select" name="tournament_id" required>
                            <option value="">-- Select Tournament --</option>
                            <?php foreach ($tournaments as $t): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo htmlspecialchars($t['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Team 1 -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Team 1 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="team1"
                               placeholder="e.g. Faculty of Computing" required>
                    </div>

                    <!-- Team 2 -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Team 2 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="team2"
                               placeholder="e.g. Faculty of Engineering" required>
                    </div>

                    <!-- Scores side by side -->
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-semibold">Score (Team 1)</label>
                            <input type="number" class="form-control" name="score_team1" value="0" min="0" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold">Score (Team 2)</label>
                            <input type="number" class="form-control" name="score_team2" value="0" min="0" required>
                        </div>
                    </div>

                    <!-- Match Date -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Match Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="match_date" required>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Notes (optional)</label>
                        <textarea class="form-control" name="notes" rows="2"
                                  placeholder="e.g. Quarter Final"></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-plus-circle me-2"></i>Save Result
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== MATCH RESULTS TABLE ===== -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>All Match Results</h5>
                <span class="badge bg-light text-dark"><?php echo count($results); ?> results</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Tournament</th>
                                <th>Match</th>
                                <th>Score</th>
                                <th>Winner</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($results)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No results added yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($results as $i => $r): ?>
                                    <?php
                                        // Determine winner text
                                        if ($r['score_team1'] > $r['score_team2']) {
                                            $winner = $r['team1'] . " 🏆";
                                            $wclass = 'text-success fw-bold';
                                        } elseif ($r['score_team2'] > $r['score_team1']) {
                                            $winner = $r['team2'] . " 🏆";
                                            $wclass = 'text-success fw-bold';
                                        } else {
                                            $winner = "Draw";
                                            $wclass = 'text-muted';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <?php echo htmlspecialchars($r['tournament_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($r['team1']); ?>
                                            <span class="text-muted"> vs </span>
                                            <?php echo htmlspecialchars($r['team2']); ?>
                                            <?php if ($r['notes']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($r['notes']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark fs-6">
                                                <?php echo $r['score_team1']; ?> &ndash; <?php echo $r['score_team2']; ?>
                                            </span>
                                        </td>
                                        <td class="<?php echo $wclass; ?>"><?php echo $winner; ?></td>
                                        <td><?php echo $r['match_date']; ?></td>
                                        <td>
                                            <a href="match_results.php?action=delete&id=<?php echo $r['id']; ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this result?');">
                                                <i class="bi bi-trash-fill"></i>
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

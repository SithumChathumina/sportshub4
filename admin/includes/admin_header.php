
<!-- admin/includes/admin_header.php -->
<!-- 
    Admin layout header with sidebar.
    Include this at the TOP of every admin page.
    Always call requireAdmin() BEFORE including this file.
-->
<?php
// Include the auth helper if not already included
if (!function_exists('isLoggedIn')) {
    require_once dirname(__DIR__) . '/../includes/auth.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel &mdash; NSBM SportsHub</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ==== Sidebar Styles ==== */
        body {
            background-color: #f0f2f5;
        }

        /* Sidebar fixed to the left */
        #admin-sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: #1a1a2e; /* Dark navy */
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            overflow-y: auto;
        }

        /* Sidebar brand area */
        .sidebar-brand {
            background-color: #16213e;
            padding: 1.2rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* Sidebar navigation links */
        #admin-sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1.2rem;
            border-radius: 8px;
            margin: 2px 8px;
            transition: background 0.2s ease;
        }
        #admin-sidebar .nav-link:hover,
        #admin-sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        #admin-sidebar .nav-link i {
            width: 20px; /* Align icons */
        }

        /* Main content area pushed right of the sidebar */
        #admin-main {
            margin-left: 250px;
            padding: 20px 24px;
            min-height: 100vh;
        }

        /* Top bar inside main content */
        .admin-topbar {
            background: #fff;
            border-radius: 10px;
            padding: 12px 20px;
            margin-bottom: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div id="admin-sidebar">

    <!-- Brand Logo in Sidebar -->
    <div class="sidebar-brand text-center">
        <a href="index.php" class="text-decoration-none">
            <span class="text-warning fw-bold fs-5">
                <i class="bi bi-trophy-fill me-2"></i>SportsHub
            </span>
            <br>
            <small class="text-muted" style="font-size: 0.7rem;">Admin Panel</small>
        </a>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="mt-3">
        <ul class="nav flex-column">

            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>"
                   href="index.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_clubs.php') ? 'active' : ''; ?>"
                   href="manage_clubs.php">
                    <i class="bi bi-shield-fill me-2"></i> Manage Clubs
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_memberships.php') ? 'active' : ''; ?>"
                   href="manage_memberships.php">
                    <i class="bi bi-person-check-fill me-2"></i> Memberships
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'schedule.php') ? 'active' : ''; ?>"
                   href="schedule.php">
                    <i class="bi bi-calendar3 me-2"></i> Schedule
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'tournaments.php') ? 'active' : ''; ?>"
                   href="tournaments.php">
                    <i class="bi bi-award-fill me-2"></i> Tournaments
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'match_results.php') ? 'active' : ''; ?>"
                   href="match_results.php">
                    <i class="bi bi-bar-chart-fill me-2"></i> Match Results
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_bookings.php') ? 'active' : ''; ?>"
                   href="manage_bookings.php">
                    <i class="bi bi-building me-2"></i> Bookings
                </a>
            </li>

            <!-- Divider -->
            <li><hr class="border-secondary mx-3 my-2"></li>

            <!-- Back to Site -->
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="bi bi-house-fill me-2"></i> View Site
                </a>
            </li>

            <!-- Logout -->
            <li class="nav-item">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>

        </ul>
    </nav>
</div>
<!-- ===== END SIDEBAR ===== -->

<!-- ===== MAIN CONTENT AREA ===== -->
<div id="admin-main">

    <!-- Top Bar -->
    <div class="admin-topbar">
        <div>
            <strong class="text-dark">
                <i class="bi bi-grid-3x3-gap-fill text-success me-2"></i>Admin Panel
            </strong>
        </div>
        <div class="text-muted small">
            <i class="bi bi-person-circle me-1"></i>
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong>
        </div>
    </div>
    <!-- Page content goes here (injected by each admin page) -->


<!-- includes/header.php -->
<!-- 
    This file is included at the top of every page.
    It sets up the HTML head section and the Bootstrap navbar.
    Make sure session_start() has been called BEFORE including this file.
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSBM SportsHub</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons CDN (for icons like bi-person, bi-trophy, etc.) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom inline styles for the navbar branding -->
    <style>
        /* Give the navbar a sporty dark green color */
        .navbar-sportshub {
            background-color: #1a6b3c;
        }
        /* Footer link color override */
        footer a {
            color: #adb5bd;
        }
        footer a:hover {
            color: #ffffff;
        }
    </style>
</head>
<body class="bg-light">

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-sportshub shadow-sm">
    <div class="container">

        <!-- Logo / Brand Name -->
        <a class="navbar-brand fw-bold fs-4" href="index.php">
            <i class="bi bi-trophy-fill me-2 text-warning"></i>NSBM SportsHub
        </a>

        <!-- Mobile hamburger menu button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav Links -->
        <div class="collapse navbar-collapse" id="mainNavbar">

            <!-- Left side links (always visible) -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-house-fill me-1"></i>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clubs.php"><i class="bi bi-people-fill me-1"></i>Clubs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="schedule.php"><i class="bi bi-calendar3 me-1"></i>Schedule</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tournaments.php"><i class="bi bi-award-fill me-1"></i>Tournaments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="book_facility.php"><i class="bi bi-building me-1"></i>Facilities</a>
                </li>
            </ul>

            <!-- Right side links (conditional based on login status) -->
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold" href="admin/index.php">
                                <i class="bi bi-shield-lock-fill me-1"></i>Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>
                    <!-- User IS logged in: show Dashboard and Logout -->
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <!-- Show the user's name if available -->
                    <?php if (isset($_SESSION['user_name'])): ?>
                        <li class="nav-item">
                            <span class="nav-link text-warning">
                                <i class="bi bi-person-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </span>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <!-- User is NOT logged in: show Login and Register -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm ms-2 mt-1 mt-lg-0" href="register.php">
                            <i class="bi bi-person-plus-fill me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

        </div><!-- end .navbar-collapse -->
    </div><!-- end .container -->
</nav>
<!-- ===== END NAVBAR ===== -->

<!-- Page content starts here - wrapped in a container in each page -->

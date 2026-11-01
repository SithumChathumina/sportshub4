<?php
/**
 * logout.php
 * Logs the user out by destroying the session and redirecting to the login page.
 */

// Step 1: Start the session (required to access and destroy it)
session_start();

// Step 2: Unset all session variables (clear the session data)
$_SESSION = [];

// Step 3: Destroy the session itself
session_destroy();

// Step 4: Redirect the user to the login page
header("Location: login.php");
exit(); // Always exit after a redirect

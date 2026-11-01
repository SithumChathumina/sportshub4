<?php
/**
 * includes/auth.php
 * Authentication helper functions for NSBM SportsHub
 * Include this file wherever you need to check login status
 */

// Make sure sessions are started before using these functions
// (session_start() should be called before including this file)

/**
 * Check if a user is currently logged in
 * Returns true if logged in, false otherwise
 */
function isLoggedIn() {
    // If 'user_id' exists in the session, the user is logged in
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin
 * Returns true if admin, false otherwise
 */
function isAdmin() {
    // Check both that the user is logged in AND that their role is 'admin'
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require the user to be logged in
 * If not logged in, redirect to the login page
 * Works for both root pages and admin/ subdirectory in XAMPP
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Save the page URL to redirect back after login (optional improvement)
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        // Build a path-safe redirect that works in localhost/sportshub/ subdirectory
        $base = rtrim(str_replace('/admin', '', dirname($_SERVER['SCRIPT_NAME'])), '/');
        header("Location: " . $base . "/login.php");
        exit(); // Always exit after header redirect!
    }
}

/**
 * Require the user to be an admin
 * If not admin (or not logged in), redirect or block access
 * Works for pages inside the admin/ folder
 */
function requireAdmin() {
    if (!isLoggedIn()) {
        // Not logged in at all - redirect to login page
        // Remove /admin from the script path to find the root login.php
        $base = rtrim(str_replace('/admin', '', dirname($_SERVER['SCRIPT_NAME'])), '/');
        header("Location: " . $base . "/login.php");
        exit();
    }
    if (!isAdmin()) {
        // Logged in but role is not 'admin' - deny access
        http_response_code(403);
        die('<div style="font-family:sans-serif; text-align:center; margin-top:100px;">
               <h2 style="color:red;">Access Denied</h2>
               <p>You do not have admin privileges to view this page.</p>
               <a href="../dashboard.php">Return to Dashboard</a>
             </div>');
    }
}

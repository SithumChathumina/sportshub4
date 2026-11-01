<?php
/**
 * config/db.php
 * Database connection file for NSBM SportsHub
 * Uses PDO (PHP Data Objects) for safe database access
 */

// --- Database Configuration Constants ---
define('DB_HOST', 'localhost');      // Your MySQL server (usually localhost in XAMPP)
define('DB_NAME', 'sportshub4_db'); // The database name you created in phpMyAdmin
define('DB_USER', 'root');           // Default XAMPP MySQL username
define('DB_PASS', '');               // Default XAMPP MySQL password (empty)
define('DB_CHARSET', 'utf8mb4');     // Character set (supports emojis and all special chars)

// --- Build the DSN (Data Source Name) ---
// DSN tells PDO what driver and database to use
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// --- PDO Options ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Fetch rows as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
];

// --- Create the PDO Connection ---
try {
    // Try to connect to the database
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    // If successful, $pdo is now available throughout your application
} catch (PDOException $e) {
    // If connection fails, show a friendly error and stop the script
    // In production, you'd log this error instead of displaying it
    die("Database connection failed: " . $e->getMessage());
}
// $pdo is now ready to use in any file that includes this config

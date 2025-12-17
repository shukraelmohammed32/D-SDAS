<?php
// bootstrap.php - The central autoloader and application setup file

// 1. Start Session globally if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Define Root Path Constants
// This is critical for security, pointing PHP to the correct, secure folders.
define('APP_ROOT', __DIR__);
define('SRC_PATH', APP_ROOT . '/src'); // Path to all PHP logic classes

// 3. Load Configuration (Database Credentials)
// Ensure your Config.php defines DB_HOST, DB_NAME, DB_USER, DB_PASS
require_once SRC_PATH . '/Config.php';

// 4. Autoload Classes Logic
// This function automatically includes a class file whenever you try to use it
// (e.g., when you write `new Engine()` or `Database::getInstance()`)
spl_autoload_register(function ($className) {

    // Define the directories where your classes reside
    $directories = [
        SRC_PATH . '/',
    ];

    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Optional: Log an error if a required class is not found
    // error_log("Class {$className} not found.");
});

// 5. Global Error Reporting Setup (for development environment)
// You would disable or refine this on a production server.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Example: Initialize the Database connection (optional, but good practice)
// This ensures DB connection attempts happen immediately on page load.
// Database::getInstance();
?> 
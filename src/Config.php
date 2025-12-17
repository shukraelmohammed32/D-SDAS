<?php
// src/Config.php
// WARNING: DO NOT UPLOAD TO PUBLIC GIT REPOSITORIES WITHOUT REDACTING CREDENTIALS.

// =========================================================
// APPLICATION CONFIGURATION
// =========================================================

// Define the root directory name for URL paths (if needed)
//define('APP_NAME', 'DSDAS_Project');

// =========================================================
// DATABASE CONNECTION SETTINGS (PostgreSQL)
// =========================================================

// Database Hostname (usually 'localhost' or a service name)
define('DB_HOST', 'localhost');

// Database Name (from dsdas_core.sql setup)
define('DB_NAME', 'dsdas_erp');

// Database User
define('DB_USER', 'root');

// Database Password
define('DB_PASS', '');

// Allocation Cooldown Time (in minutes)
define('ALLOCATION_COOLDOWN_MINUTES', 30);
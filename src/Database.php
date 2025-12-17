<?php
// src/Database.php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // 1. Assign the Constants from Config.php to variables
        $host   = DB_HOST;
        $dbname = 'university_placement'; // ✅ Fixed: put in quotes
        $user   = DB_USER;
        $pass   = DB_PASS;
        
        // 2. Create the MySQL Connection String
        $dsn = "mysql:host={$host};dbname={university_placement};charset=utf8mb4";
        
        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            // If the database doesn't exist, this will tell you exactly why
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    // Standard Singleton Logic
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

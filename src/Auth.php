<?php
// src/Auth.php
require_once 'Database.php';

class Auth {
    private static $db = null;

    private static function initDb() {
        if (self::$db === null) {
            self::$db = Database::getInstance()->getConnection();
        }
    }

    public static function login($username, $password) {
        self::initDb();

        $stmt = self::$db->prepare("SELECT user_id, password_hash, role, admission_rank FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // PLAIN TEXT CHECK: Direct comparison instead of password_verify
        if ($user && $password === $user['password_hash']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $username;
            $_SESSION['rank'] = $user['admission_rank'];

            return true;
        }
        return false;
    }


    /**
     * Logs the current user out.
     */
    public static function logout() {
        // Clear all session variables
        $_SESSION = [];
        // Destroy the session
        session_destroy();
    }

    /**
     * Checks if a user is currently logged in.
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Enforces role-based access control.
     * If the user is not logged in or doesn't have the required role, they are redirected.
     */
    public static function checkAccess($requiredRole) {
        if (!self::isLoggedIn()) {
            // Not logged in: Redirect to login page
            header("Location: index.php");
            exit;
        }

        if ($_SESSION['user_role'] !== $requiredRole) {
            // Logged in but wrong role: Redirect to their own dashboard
            $target = ($_SESSION['user_role'] === 'admin') ? 'admin.php' : 'dashboard.php';
            header("Location: $target");
            exit;
        }
        // Access granted
    }
}
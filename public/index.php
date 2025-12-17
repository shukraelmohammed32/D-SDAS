<?php
// public/index.php (Login Page)
require_once '../bootstrap.php';
require_once '../src/Auth.php';

$error = '';

if (Auth::isLoggedIn()) {
    // If already logged in, redirect based on role
    $target = ($_SESSION['user_role'] === 'admin') ? 'admin.php' : 'dashboard.php';
    header("Location: $target");
    exit;
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (Auth::login($username, $password)) {
        // Login successful, redirect handled by the checkAccess logic in the next request
        $target = ($_SESSION['user_role'] === 'admin') ? 'admin.php' : 'dashboard.php';
        header("Location: $target");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>D-SDAS Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Minimal styles for a centered login card */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f3f2f1;
        }
        .login-card {
            background-color: white;
            padding: 30px;
            box-shadow: 0 1.6px 3.6px 0 rgba(0,0,0,0.132);
            width: 350px;
            border-radius: 2px;
        }
        .login-card h2 { color: #0078d4; margin-top: 0; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-size: 14px; }
        .ms-input { width: 100%; padding: 10px; border: 1px solid #8a8886; border-radius: 2px; box-sizing: border-box; }
        .error-message { color: #d13438; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <h2>D-SDAS Portal</h2>
        <p>Sign in to access the Allocation Engine.</p>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="ms-input" required autofocus>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="ms-input" required>
            </div>
            <button type="submit" class="ms-btn primary">Sign In</button>
        </form>
        <p style="margin-top: 20px; font-size: 12px; color: #605e5c;">Use 'admin' or 'student' credentials.</p>
    </div>
</div>
</body>
</html>
<?php
// public/dashboard.php
require_once '../bootstrap.php';
require_once '../src/Auth.php';

// Force Login
if (!Auth::isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Mock Data (In real app, fetch from User Model)
$user = $_SESSION['user_role'] ?? 'Student';
$rank = $_SESSION['rank'] ?? 1240; // Example Rank
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D-SDAS Allocation Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://static2.sharepointonline.com/files/fabric/office-ui-fabric-core/11.0.0/css/fabric.min.css">
</head>
<body>
    <header class="ms-header" style="background-color: #004578;">
        <div class="header-left">
            <span class="ms-icon">⠿</span>
            <span class="app-title">D-SDAS Student Center</span>
        </div>
        <div class="header-right" style="display:flex; align-items:center; gap:15px;">
            <span class="user-greeting">Welcome back, <strong>Student</strong></span>
            <div class="user-profile-circle" style="background:#002d50;">A</div>
            <a href="logout.php" class="ms-logout-btn">Sign out</a>
        </div>
    </header>
    <div class="layout-container">
        <aside class="ms-sidebar">
            <nav>
                <a href="#" class="nav-item active"><i class="ms-Icon ms-Icon--GridView"></i> Dashboard</a>
            </nav>
        </aside>

        <main class="ms-content">
            
            <div class="welcome-section">
                <h1>Dashboard</h1>
                <p>Manage your department allocation and academic status.</p>
            </div>

            <div class="tile-grid">
                
                <div class="tile large-tile">
                    <div class="tile-header">
                        <h3><i class="ms-Icon ms-Icon--WaitList"></i> Allocation Round 1</h3>
                        <span class="status-badge live">Live</span>
                    </div>
                    <div class="tile-body">
                        <div class="allocation-controls">
                            
                            <div class="control-group">
                                <label>Select Department Priority</label>
                                <select id="deptSelect" class="ms-select">
                                    <option value="" disabled selected>Choose a department...</option>
                                    <option value="1">Computer Science (CS)</option>
                                    <option value="2">Electrical Engineering (EE)</option>
                                    <option value="3">Mechanical Engineering (ME)</option>
                                    <option value="4">Software Engineering (SWE)</option>
                                    <option value="5">Civil Engineering (CE)</option>
                                    <option value="6">Data Science (DS)</option>
                                    <option value="7">Cyber Security (CyS)</option>
                                    <option value="8">Architecture (Arch)</option>
                                    <option value="9">Business Information Systems (BIS)</option>
                                    <option value="10">Information Technology (IT)</option>
                                </select>
                            </div>

                            <div id="predictionWidget" class="prediction-box hidden">
                                <div class="traffic-light-container">
                                    <div id="light-display" class="traffic-light gray"></div>
                                </div>
                                <div class="prediction-text">
                                    <strong id="predTitle">Analyzing...</strong>
                                    <span id="predMsg">Please wait while we check capacity.</span>
                                </div>
                            </div>

                            <div class="action-row">
                                <div class="timer">
                                    <small>Round Ends In:</small>
                                    <span id="countdown">02d 14h 30m</span>
                                </div>
                                <button id="lockBtn" class="ms-btn primary">Lock Choice</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tile">
                    <div class="tile-header"><h3>My Profile</h3></div>
                    <div class="tile-body stat-body">
                        <div class="stat-item">
                            <span class="label">Admission Rank</span>
                            <span class="value text-blue">#<?php echo $rank; ?></span>
                        </div> 
                        <div class="stat-item">
                            <span class="label">Current Status</span>
                            <span class="value">Pending Allocation</span>
                        </div>
                    </div>
                </div>

                <div class="tile">
                    <div class="tile-header"><h3>Notifications</h3></div>
                    <div class="tile-body list-body">
                        <div class="list-item">
                            <i class="ms-Icon ms-Icon--Info"></i>
                            <span>Round 1 closes on Friday.</span>
                        </div>
                        <div class="list-item">
                            <i class="ms-Icon ms-Icon--CheckMark"></i>
                            <span>Profile updated.</span>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>




,
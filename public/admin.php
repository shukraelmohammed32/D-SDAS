<?php
// public/admin.php
require_once '../src/Config.php';
require_once '../src/Database.php';
require_once '../src/Auth.php';

session_start();

// 1. SECURITY: Ensure only admins can see this
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$message = [];

// 2. HANDLE ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create_round') {
            $stmt = $db->prepare("INSERT INTO admission_rounds (round_name, start_time, end_time, status) VALUES (?, ?, ?, 'scheduled')");
            $stmt->execute([$_POST['name'], $_POST['start'], $_POST['end']]);
            $message = ['type' => 'success', 'text' => 'Round scheduled successfully.'];
            
        } elseif ($_POST['action'] === 'update_status') {
            $stmt = $db->prepare("UPDATE admission_rounds SET status = ? WHERE round_id = ?");
            $stmt->execute([$_POST['status'], $_POST['round_id']]);
            $message = ['type' => 'success', 'text' => 'Round status updated to ' . ucfirst($_POST['status']) . '.'];
            
        } elseif ($_POST['action'] === 'finalize') {
            // Mark all allocations in this round as finalized
            $stmt = $db->prepare("UPDATE allocations SET is_finalized = TRUE WHERE round_id = ?");
            $stmt->execute([$_POST['round_id']]);
            $message = ['type' => 'success', 'text' => 'Allocations finalized for this round.'];
        }
    } catch (Exception $e) {
        $message = ['type' => 'error', 'text' => 'Operation failed: ' . $e->getMessage()];
    }
}

// 3. FETCH REAL DATA FROM DATABASE
// Get Rounds
$rounds = $db->query("SELECT * FROM admission_rounds ORDER BY start_time DESC")->fetchAll();

// Get Allocation Summary (Joins departments and prediction stats)
$summary_query = "
    SELECT d.dept_name, d.base_capacity, d.current_locked, p.historical_cutoff_rank 
    FROM departments d
    LEFT JOIN prediction_stats p ON d.dept_id = p.dept_id
";
$summary = $db->query($summary_query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control | D-SDAS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="ms-office-theme">

    <header class="ms-header" style="background-color: #004578;">
        <div class="header-left">
            <span class="ms-icon">⠿</span>
            <span class="app-title">D-SDAS Admin Center</span>
        </div>
        <div class="header-right" style="display:flex; align-items:center; gap:15px;">
            <span class="user-greeting">Welcome back, <strong>Admin</strong></span>
            <div class="user-profile-circle" style="background:#002d50;">A</div>
            <a href="logout.php" class="ms-logout-btn">Sign out</a>
        </div>
    </header>

    <div class="ms-main-container">
        <aside class="ms-sidebar">
            <nav>
                <ul>
                    <li class="active"><i></i> Dashboard</li>
                </ul>
            </nav>
        </aside>

        <main class="ms-content" style="padding: 20px; flex: 1;">
            <section class="content-header">
                <h1>Allocation System Management</h1>
            </section>

            <?php if (!empty($message)): ?>
                <div class="ms-alert ms-alert-<?php echo $message['type']; ?>" style="padding:10px; margin-bottom:20px; border:1px solid; border-radius:4px;">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>

            <div class="tile-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                
                <div class="tile large-tile">
                    <div class="tile-header">
                        <h3>Round Lifecycle Control</h3>
                    </div>
                    <div class="tile-body">
                        <form method="POST" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
                            <input type="hidden" name="action" value="create_round">
                            <input type="text" name="name" placeholder="Round Name" required style="padding:5px;">
                            <input type="datetime-local" name="start" required style="padding:5px;">
                            <input type="datetime-local" name="end" required style="padding:5px;">
                            <button type="submit" class="ms-primary-btn">Schedule</button>
                        </form>

                        <table class="ms-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 2px solid #eee;">
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rounds as $r): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td><strong><?php echo htmlspecialchars($r['round_name']); ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo $r['status']; ?>">
                                            <?php echo ucfirst($r['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] === 'scheduled'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="round_id" value="<?php echo $r['round_id']; ?>">
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="ms-secondary-btn">Activate</button>
                                            </form>
                                        <?php elseif ($r['status'] === 'active'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="round_id" value="<?php echo $r['round_id']; ?>">
                                                <input type="hidden" name="status" value="sealed">
                                                <button type="submit" class="ms-secondary-btn">Seal Round</button>
                                            </form>
                                        <?php elseif ($r['status'] === 'sealed'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="finalize">
                                                <input type="hidden" name="round_id" value="<?php echo $r['round_id']; ?>">
                                                <button type="submit" class="ms-primary-btn">Finalize Allocations</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tile">
                    <div class="tile-header">
                        <h3>Real-time Seats</h3>
                    </div>
                    <div class="tile-body">
                        <table class="ms-table" style="width: 100%;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 1px solid #eee;">
                                    <th>Dept</th>
                                    <th>Cap.</th>
                                    <th>Rem.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($summary as $s): ?>
                                    <?php $rem = $s['base_capacity'] - $s['current_locked']; ?>
                                    <tr>
                                        <td><?php echo $s['dept_name']; ?></td>
                                        <td><?php echo $s['base_capacity']; ?></td>
                                        <td style="color: <?php echo ($rem < 5) ? 'red' : 'green'; ?>; font-weight: bold;">
                                            <?php echo $rem; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> 

            <!-- Finalized Allocations Summary -->
            <div class="tile" style="margin-top: 20px;">
                <div class="tile-header">
                    <h3>Finalized Allocations Summary</h3>
                </div>
                <div class="tile-body">
                    <?php
                    $final_summary = $db->query("
                        SELECT d.dept_name, COUNT(a.student_id) AS allocated_students
                        FROM allocations a
                        JOIN departments d ON a.dept_id = d.dept_id
                        WHERE a.is_finalized = TRUE
                        GROUP BY d.dept_id
                    ")->fetchAll();
                    ?>
                    <table class="ms-table" style="width: 100%;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid #eee;">
                                <th>Dept</th>
                                <th>Allocated Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($final_summary as $f): ?>
                            <tr>
                                <td><?php echo $f['dept_name']; ?></td>
                                <td><?php echo $f['allocated_students']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</body>
</html>

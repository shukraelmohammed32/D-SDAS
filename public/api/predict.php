<?php
// public/api/predict.php
require_once '../../bootstrap.php'; // Load DB and Engine
require_once '../../src/Engine.php';
require_once '../../src/Auth.php';

header('Content-Type: application/json');

// 1. Security Check
if (!Auth::isLoggedIn()) {
    echo json_encode(['color' => 'gray', 'msg' => 'Unauthorized']);
    exit;
}

// 2. Validate Input
if (!isset($_GET['dept_id'])) {
    echo json_encode(['color' => 'gray', 'msg' => 'No Department Selected']);
    exit;
}

// 3. Run Engine
$engine = new Engine();
$userId = $_SESSION['user_id'];
$deptId = (int)$_GET['dept_id'];

$result = $engine->getPrediction($userId, $deptId);

echo json_encode($result);
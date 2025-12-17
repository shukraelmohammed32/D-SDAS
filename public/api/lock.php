<?php
// public/api/lock.php
require_once '../../bootstrap.php';
require_once '../../src/Engine.php';
require_once '../../src/Auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!Auth::isLoggedIn()) {
    echo json_encode(['success' => false, 'msg' => 'Session expired. Login again.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$deptId = $input['dept_id'] ?? null;
$roundId = 1; // In real app, fetch active round from DB

if (!$deptId) {
    echo json_encode(['success' => false, 'msg' => 'Invalid Department.']);
    exit;
}

$engine = new Engine();
$result = $engine->lockChoice($_SESSION['user_id'], $deptId, $roundId);

echo json_encode($result);
<?php
// LRRS/controllers/process_request_action.php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
error_reporting(0);
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$reservation_id = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;
$action         = isset($_POST['action'])         ? trim($_POST['action'])         : '';
$reason         = isset($_POST['reason'])         ? trim($_POST['reason'])         : '';

if ($reservation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID']);
    exit;
}
if (!in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}
if ($action === 'reject' && empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
    exit;
}

if ($action === 'approve') {
    $query = "UPDATE reservation SET is_equipment_ready = 1 WHERE reservation_id = ?";
    $result = Database::search($query, 'i', [$reservation_id]);
} else {
    $query = "UPDATE reservation 
              SET is_rejected_by_technical_officer = 1, rejected_reason = ? 
              WHERE reservation_id = ?";
    $result = Database::search($query, 'si', [$reason, $reservation_id]);
}

if ($result !== false) {
    echo json_encode(['success' => true, 'message' => ucfirst($action) . 'd successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
}

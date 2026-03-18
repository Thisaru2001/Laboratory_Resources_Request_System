<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is HOD
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'hod') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$user_id = $_POST['user_id'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($user_id) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'User ID and action required']);
    exit();
}

// Validate action
if (!in_array($action, ['activate', 'deactivate'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Determine new status
$new_status = ($action === 'activate') ? 1 : 0;

// Update user status
$update_query = "UPDATE lab_user SET status = ? WHERE id = ?";
$result = Database::iud($update_query, "ii", [$new_status, $user_id]);

if ($result) {
    // Get updated user info for response
    $user_query = "SELECT id, first_name, last_name, email, status FROM lab_user WHERE id = ?";
    $user_result = Database::search($user_query, "i", [$user_id]);
    $user = $user_result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'User ' . $action . 'd successfully',
        'user' => $user
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
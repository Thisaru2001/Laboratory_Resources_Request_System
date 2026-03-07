<?php
session_start();
require_once "../config/database.php";

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

$response = ['success' => false, 'message' => ''];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Check if user is logged in as HOD
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'HOD') {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

// Get and validate inputs
$user_id = trim($_POST['user_id'] ?? '');
$action = trim($_POST['action'] ?? '');

if (empty($user_id)) {
    $response['message'] = 'User ID is required';
    echo json_encode($response);
    exit;
}

if (!in_array($action, ['activate', 'deactivate'])) {
    $response['message'] = 'Invalid action';
    echo json_encode($response);
    exit;
}

try {
    // Check if user exists
    $check_sql = "SELECT user_id, status_user, first_name, last_name FROM lab_user WHERE university_id = ?";
    $check_result = Database::search($check_sql, "s", [$user_id]);
    
    if (!$check_result || $check_result->num_rows === 0) {
        $response['message'] = 'User not found';
        echo json_encode($response);
        exit;
    }
    
    $user = $check_result->fetch_assoc();
    
    // Determine new status value (1 = active, 0 = inactive)
    $new_status = ($action === 'activate') ? 1 : 0;
    
    // Update user status
    $update_sql = "UPDATE lab_user SET status_user = ? WHERE university_id = ?";
    $update_result = Database::iud($update_sql, "is", [$new_status, $user_id]);
    
    if ($update_result) {
        // Log the action
        $action_text = ($action === 'activate') ? 'activated' : 'deactivated';
        error_log("User {$user['first_name']} {$user['last_name']} ($user_id) {$action_text} by HOD");
        
        $response['success'] = true;
        $response['message'] = "User {$action_text} successfully";
        $response['user_id'] = $user_id;
        $response['new_status'] = $new_status;
    } else {
        $response['message'] = "Failed to " . $action . " user";
    }
    
} catch (Exception $e) {
    error_log("Activate/Deactivate Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
}

echo json_encode($response);
exit;
?>
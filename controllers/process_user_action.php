<?php
// LRRS/controllers/process_user_action.php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    if (!isset($_SESSION["user"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'hod') {
        throw new Exception('Unauthorized access');
    }

    $user_id = $_POST['user_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $reason = $_POST['reason'] ?? '';
    // error_log("hello".$_POST['user_id']);

    if (!$user_id || !in_array($action, ['approve', 'reject'])) {
        throw new Exception('Invalid parameters');
    }

    // Get the HOD's ID from session
    $hod_id = $_SESSION['user']['id'] ?? 0;

    if ($action === 'approve') {
        // Approve user - set status to 1 and approved_datetime
        $query = "UPDATE lab_user SET status = 1, approved_datetime = NOW(), who_approved = ? WHERE id = ?";
        $result = Database::iud($query, "ii", [$hod_id, $user_id]);
        
        if (!$result) {
            throw new Exception('Failed to approve user');
        }
        
        $message = 'User approved successfully';
        
    } else {
        // Reject user - set status to 2 for rejected
        $query = "UPDATE lab_user SET status = 2, approved_datetime = NOW(), who_approved = ? WHERE id = ?";
        $result = Database::iud($query, "ii", [$hod_id, $user_id]);
        
        if (!$result) {
            throw new Exception('Failed to reject user');
        }
        
        $message = 'User rejected successfully';
    }

    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
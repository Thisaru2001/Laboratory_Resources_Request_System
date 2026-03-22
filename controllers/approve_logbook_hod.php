<?php
/**
 * Handle approve/reject of logbooks by HOD (Head of Department)
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Check if user is authenticated and is HOD
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'hod') {
    error_log('Unauthorized access in approve_logbook_hod.php - Role: ' . ($_SESSION['user_role'] ?? 'none'));
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Only HOD can approve logbooks.']);
    exit();
}

// Get request data
$input = file_get_contents('php://input');
error_log('approve_logbook_hod.php received: ' . $input);

$data = json_decode($input, true);
$logbook_id = $data['logbook_id'] ?? 0;
$action = $data['action'] ?? '';
$reason = $data['reason'] ?? '';
$hod_id = $_SESSION['user_id'];

if (!$logbook_id || !$action) {
    error_log('Invalid parameters: logbook_id=' . $logbook_id . ', action=' . $action);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

if (!in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

try {
    // Check if logbook exists
    $checkQuery = "SELECT id FROM practical_finished_logbook WHERE id = ?";
    $checkResult = Database::search($checkQuery, 'i', [$logbook_id]);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        error_log('Logbook not found: ' . $logbook_id);
        echo json_encode(['success' => false, 'message' => 'Logbook not found']);
        exit();
    }
    
    // Check if approval record exists for HOD
    $checkApprovalQuery = "SELECT id, is_approved FROM practical_finished_hod_notify_and_approval WHERE practical_finished_logbook_id = ?";
    $checkApprovalResult = Database::search($checkApprovalQuery, 'i', [$logbook_id]);
    
    if (!$checkApprovalResult || $checkApprovalResult->num_rows === 0) {
        error_log('HOD approval record not found for logbook: ' . $logbook_id);
        echo json_encode(['success' => false, 'message' => 'HOD approval record not found']);
        exit();
    }
    
    $approvalRecord = $checkApprovalResult->fetch_assoc();
    $approvalId = $approvalRecord['id'];
    
    if ($action === 'approve') {
        // Update approval
        $approveQuery = "UPDATE practical_finished_hod_notify_and_approval 
                         SET is_approved = 1, 
                             approved_or_rejected_datetime = NOW(),status = 'read'
                         WHERE id = ?";
        $approveResult = Database::iud($approveQuery, 'i', [$approvalId]);
        
        if ($approveResult) {
            error_log('HOD approved logbook: ' . $logbook_id);
            echo json_encode(['success' => true, 'message' => 'Logbook approved successfully!']);
        } else {
            error_log('Failed to approve logbook: ' . $logbook_id);
            echo json_encode(['success' => false, 'message' => 'Failed to approve logbook']);
        }
    } else if ($action === 'reject') {
        // Update rejection with reason
        if (!$reason) {
            echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
            exit();
        }
        
        $rejectQuery = "UPDATE practical_finished_hod_notify_and_approval 
                        SET is_approved = 0, 
                            approved_or_rejected_datetime = NOW(),status = 'read',
                            rejection_reason = ? 
                        WHERE id = ?";
        $rejectResult = Database::iud($rejectQuery, 'si', [$reason, $approvalId]);
        
        if ($rejectResult) {
            error_log('HOD rejected logbook: ' . $logbook_id . ' Reason: ' . $reason);
            echo json_encode(['success' => true, 'message' => 'Logbook rejected successfully!']);
        } else {
            error_log('Failed to reject logbook: ' . $logbook_id);
            echo json_encode(['success' => false, 'message' => 'Failed to reject logbook']);
        }
    }
    
} catch (Exception $e) {
    error_log('Exception in approve_logbook_hod.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

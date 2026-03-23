<?php
/**
 * Handle approve/reject of logbooks by Technical Officer
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Check if user is authenticated and is technical officer
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technical_officer') {
    error_log('Unauthorized access in approve_logbook_technicalOfficer.php');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get request data
$input = file_get_contents('php://input');
error_log('approve_logbook_technicalOfficer.php received: ' . $input);

$data = json_decode($input, true);
$logbook_id = $data['logbook_id'] ?? 0;
$action = $data['action'] ?? '';
$reason = $data['reason'] ?? '';
$technical_officer_id = $_SESSION['user_id'];

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
    
    if ($action === 'approve') {
        // Check if approval record exists
        $checkApprovalQuery = "SELECT id FROM practical_finished_technicalofficer_notify_and_approval WHERE practical_finished_logbook_id = ?";
        $approvalCheckResult = Database::search($checkApprovalQuery, 'i', [$logbook_id]);
        
        if ($approvalCheckResult && $approvalCheckResult->num_rows > 0) {
            // Update existing approval record
            $updateQuery = "
                UPDATE practical_finished_technicalofficer_notify_and_approval 
                SET is_approved = 1,
                    status = 'read',
                    approved_or_rejected_datetime = NOW()
                WHERE practical_finished_logbook_id = ?
            ";
            error_log('Updating existing approval record for logbook ' . $logbook_id);
            $updated = Database::iud($updateQuery, 'i', [$logbook_id]);
        } else {
            // Create new approval record
            $insertQuery = "
                INSERT INTO practical_finished_technicalofficer_notify_and_approval 
                (practical_finished_logbook_id, is_approved, status, approved_or_rejected_datetime)
                VALUES (?, 1, 'read', NOW())
            ";
            error_log('Creating new approval record for logbook ' . $logbook_id);
            $updated = Database::iud($insertQuery, 'i', [$logbook_id]);
        }
        
        // Update logbook with technical officer ID
        if ($updated) {
            $updateLogbookQuery = "
                UPDATE practical_finished_logbook 
                SET who_technicalOfficer_id = ?
                WHERE id = ?
            ";
            Database::iud($updateLogbookQuery, 'ii', [$technical_officer_id, $logbook_id]);
            error_log('Updated who_technicalOfficer_id to ' . $technical_officer_id . ' for logbook ' . $logbook_id);
        }
        
        if ($updated) {
            error_log('Successfully approved logbook ' . $logbook_id);
            echo json_encode(['success' => true, 'message' => 'Logbook approved successfully!']);
            exit();
        } else {
            error_log('Failed to approve logbook: ' . Database::getLastError());
            echo json_encode(['success' => false, 'message' => 'Failed to approve logbook: ' . Database::getLastError()]);
            exit();
        }
    } 
    else if ($action === 'reject') {
        // Check if rejection record exists
        $checkApprovalQuery = "SELECT id FROM practical_finished_technicalofficer_notify_and_approval WHERE practical_finished_logbook_id = ?";
        $approvalCheckResult = Database::search($checkApprovalQuery, 'i', [$logbook_id]);
        
        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a reason for rejection']);
            exit();
        }
        
        if ($approvalCheckResult && $approvalCheckResult->num_rows > 0) {
            // Update existing rejection record
            $queryStr = "
                UPDATE practical_finished_technicalofficer_notify_and_approval 
                SET is_approved = 0,
                    status = 'read',
                    rejection_reason = ?,
                    approved_or_rejected_datetime = NOW()
                WHERE practical_finished_logbook_id = ?
            ";
            error_log('Updating rejection record for logbook ' . $logbook_id);
            $updated = Database::iud($queryStr, 'si', [$reason, $logbook_id]);
        } else {
            // Create new rejection record
            $queryStr = "
                INSERT INTO practical_finished_technicalofficer_notify_and_approval 
                (practical_finished_logbook_id, is_approved, status, rejection_reason, approved_or_rejected_datetime)
                VALUES (?, 0, 'read', ?, NOW())
            ";
            error_log('Creating new rejection record for logbook ' . $logbook_id);
            $updated = Database::iud($queryStr, 'is', [$logbook_id, $reason]);
        }
        
        // Update logbook with 0 for rejected technical officer
        // if ($updated) {
        //     $updateLogbookQuery = "
        //         UPDATE practical_finished_logbook 
        //         SET who_technicalOfficer_id = 0
        //         WHERE id = ?
        //     ";
        //     Database::iud($updateLogbookQuery, 'i', [$logbook_id]);
        //     error_log('Set who_technicalOfficer_id to 0 for rejected logbook ' . $logbook_id);
        // }
        
        if ($updated) {
             $updateLogbookQueryy = "
                UPDATE practical_finished_logbook 
                SET who_technicalOfficer_id = ?
                WHERE id = ?
            ";
            Database::iud($updateLogbookQueryy, 'ii', [$technical_officer_id, $logbook_id]);
            error_log('Successfully rejected logbook ' . $logbook_id);
            echo json_encode(['success' => true, 'message' => 'Logbook rejected']);
            exit();
        } else {
            error_log('Failed to reject logbook: ' . Database::getLastError());
            echo json_encode(['success' => false, 'message' => 'Failed to reject logbook: ' . Database::getLastError()]);
            exit();
        }
    }
    
} catch (Exception $e) {
    error_log('Exception in approve_logbook_technicalOfficer.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
}
?>
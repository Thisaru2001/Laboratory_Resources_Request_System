<?php
/**
 * Handle approve/reject of logbooks by Supervisor
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Check if user is authenticated and is supervisor
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'supervisor') {
    error_log('Unauthorized access in approve_logbook_supervisor.php');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get request data
$input = file_get_contents('php://input');
error_log('approve_logbook_supervisor.php received: ' . $input);

$data = json_decode($input, true);
$logbook_id = $data['logbook_id'] ?? 0;
$action = $data['action'] ?? '';
$reason = $data['reason'] ?? '';
$supervisor_id = $_SESSION['user_id'];

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
        $checkApprovalQuery = "SELECT id FROM practical_finished_supervisor_notify_and_approval WHERE practical_finished_logbook_id = ?";
        $approvalCheckResult = Database::search($checkApprovalQuery, 'i', [$logbook_id]);

        if ($approvalCheckResult && $approvalCheckResult->num_rows > 0) {
            // Update existing approval record
            $updateQuery = "
                UPDATE practical_finished_supervisor_notify_and_approval
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
                INSERT INTO practical_finished_supervisor_notify_and_approval
                (practical_finished_logbook_id, is_approved, status, approved_or_rejected_datetime)
                VALUES (?, 1, 'read', NOW())
            ";
            error_log('Creating new approval record for logbook ' . $logbook_id);
            $updated = Database::iud($insertQuery, 'i', [$logbook_id]);
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

        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a reason for rejection']);
            exit();
        }

        // Check if rejection record exists
        $checkApprovalQuery = "SELECT id FROM practical_finished_supervisor_notify_and_approval WHERE practical_finished_logbook_id = ?";
        $approvalCheckResult = Database::search($checkApprovalQuery, 'i', [$logbook_id]);

        if ($approvalCheckResult && $approvalCheckResult->num_rows > 0) {
            // Update existing record
            $queryStr = "
                UPDATE practical_finished_supervisor_notify_and_approval
                SET is_approved = 0,
                    status = 'read',
                    rejection_reason = ?,
                    approved_or_rejected_datetime = NOW()
                WHERE practical_finished_logbook_id = ?
            ";
            $updated = Database::iud($queryStr, 'si', [$reason, $logbook_id]);
        } else {
            // Insert new rejection record
            $queryStr = "
                INSERT INTO practical_finished_supervisor_notify_and_approval
                (practical_finished_logbook_id, is_approved, status, rejection_reason, approved_or_rejected_datetime)
                VALUES (?, 0, 'read', ?, NOW())
            ";
            $updated = Database::iud($queryStr, 'is', [$logbook_id, $reason]);
        }

        if ($updated) {

            // Get student_id and evidence_images from logbook
            $logbookQuery = "SELECT student_id, evidence_images FROM practical_finished_logbook WHERE id = ?";
            $logbookResult = Database::search($logbookQuery, 'i', [$logbook_id]);

            if ($logbookResult && $logbookResult->num_rows > 0) {
                $logbookRow = $logbookResult->fetch_assoc();
                $student_id = $logbookRow['student_id'];
                $evidence_images = $logbookRow['evidence_images'];

                // Delete evidence images from server
                if (!empty($evidence_images)) {
                    $imagePaths = json_decode($evidence_images, true);
                    if (is_array($imagePaths)) {
                        foreach ($imagePaths as $imagePath) {
                            $fullPath = __DIR__ . '/../' . trim($imagePath);
                            if (file_exists($fullPath)) {
                                @unlink($fullPath);
                                error_log('Deleted evidence image: ' . $fullPath);
                            }
                        }
                    }
                }

                // Notify student via notification table
                $notifMessage = "Your logbook (ID: $logbook_id) has been rejected by your supervisor. Reason: $reason";
                $notifQuery = "
                    INSERT INTO notification
                    (description, created_datetime, owner_of_notification, status, need_approval)
                    VALUES (?, NOW(), ?, 'unread', 0)
                ";
                Database::iud($notifQuery, 'si', [$notifMessage, $student_id]);

                // Delete from practical_finished_supervisor_notify_and_approval
                $deleteSupQuery = "
                    DELETE FROM practical_finished_supervisor_notify_and_approval
                    WHERE practical_finished_logbook_id = ?
                ";
                Database::iud($deleteSupQuery, 'i', [$logbook_id]);

                // Delete from practical_finished_logbook
                $deleteLogQuery = "DELETE FROM practical_finished_logbook WHERE id = ?";
                Database::iud($deleteLogQuery, 'i', [$logbook_id]);
            }

            echo json_encode(['success' => true, 'message' => 'Logbook rejected and student notified']);
            exit();

        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject logbook']);
            exit();
        }
    }

} catch (Exception $e) {
    error_log('Exception in approve_logbook_supervisor.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
}
?>

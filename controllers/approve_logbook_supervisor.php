<?php
/**
 * Handle approve/reject of logbooks by Supervisor
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Auth check
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'supervisor') {
    error_log('Unauthorized access in approve_logbook_supervisor.php');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get request data
$input = file_get_contents('php://input');
error_log('approve_logbook_supervisor.php received: ' . $input);

$data       = json_decode($input, true);
$logbook_id = isset($data['logbook_id']) ? (int)$data['logbook_id'] : 0;
$action     = isset($data['action'])     ? trim($data['action'])     : '';
$reason     = isset($data['reason'])     ? trim($data['reason'])     : '';
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

    // Verify logbook exists
    $checkQuery  = "SELECT id FROM practical_finished_logbook WHERE id = ?";
    $checkResult = Database::search($checkQuery, 'i', [$logbook_id]);

    if (!$checkResult || $checkResult->num_rows === 0) {
        error_log('Logbook not found: ' . $logbook_id);
        echo json_encode(['success' => false, 'message' => 'Logbook not found']);
        exit();
    }

    // APPROVE
    if ($action === 'approve') {

        $checkApprovalQuery  = "SELECT id FROM practical_finished_supervisor_notify_and_approval 
                                WHERE practical_finished_logbook_id = ?";
        $approvalCheckResult = Database::search($checkApprovalQuery, 'i', [$logbook_id]);

        if ($approvalCheckResult && $approvalCheckResult->num_rows > 0) {
            $updateQuery = "UPDATE practical_finished_supervisor_notify_and_approval
                            SET is_approved = 1,
                                status = 'read',
                                rejection_reason = NULL,
                                approved_or_rejected_datetime = NOW()
                            WHERE practical_finished_logbook_id = ?";
            error_log('Updating existing approval record for logbook ' . $logbook_id);
            $updated = Database::iud($updateQuery, 'i', [$logbook_id]);
        } else {
            $insertQuery = "INSERT INTO practical_finished_supervisor_notify_and_approval
                            (practical_finished_logbook_id, is_approved, status, approved_or_rejected_datetime)
                            VALUES (?, 1, 'read', NOW())";
            error_log('Creating new approval record for logbook ' . $logbook_id);
            $updated = Database::iud($insertQuery, 'i', [$logbook_id]);
        }

        if ($updated) {
            error_log('Successfully approved logbook ' . $logbook_id);
            echo json_encode(['success' => true, 'message' => 'Logbook approved successfully!']);
        } else {
            error_log('Failed to approve logbook');
            echo json_encode(['success' => false, 'message' => 'Failed to approve logbook']);
        }
        exit();
    }

    // REJECT
    if ($action === 'reject') {

        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a reason for rejection']);
            exit();
        }

        // Update or insert rejection record
        $checkApprovalQuery  = "SELECT id FROM practical_finished_supervisor_notify_and_approval 
                                WHERE practical_finished_logbook_id = ?";
        $approvalCheckResult = Database::search($checkApprovalQuery, 'i', [$logbook_id]);

        if ($approvalCheckResult && $approvalCheckResult->num_rows > 0) {
            $queryStr = "UPDATE practical_finished_supervisor_notify_and_approval
                         SET is_approved = 0,
                             status = 'read',
                             rejection_reason = ?,
                             approved_or_rejected_datetime = NOW()
                         WHERE practical_finished_logbook_id = ?";
            $updated = Database::iud($queryStr, 'si', [$reason, $logbook_id]);
        } else {
            $queryStr = "INSERT INTO practical_finished_supervisor_notify_and_approval
                         (practical_finished_logbook_id, is_approved, status, rejection_reason, approved_or_rejected_datetime)
                         VALUES (?, 0, 'read', ?, NOW())";
            $updated = Database::iud($queryStr, 'is', [$logbook_id, $reason]);
        }

        if (!$updated) {
            echo json_encode(['success' => false, 'message' => 'Failed to reject logbook']);
            exit();
        }

        // Get student_id and image paths
        $logbookQuery  = "SELECT student_id, img_path1, img_path2, img_path3, img_path4
                          FROM practical_finished_logbook WHERE id = ?";
        $logbookResult = Database::search($logbookQuery, 'i', [$logbook_id]);

        if ($logbookResult && $logbookResult->num_rows > 0) {
            $logbookRow = $logbookResult->fetch_assoc();
            $student_id = $logbookRow['student_id'];

            // Delete evidence image files from server
            $imagePaths = array_filter([
                $logbookRow['img_path1'],
                $logbookRow['img_path2'],
                $logbookRow['img_path3'],
                $logbookRow['img_path4'],
            ]);

            foreach ($imagePaths as $imagePath) {
                $fullPath = __DIR__ . '/../' . trim($imagePath);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                    error_log('Deleted evidence image: ' . $fullPath);
                }
            }

            // Notify student
            $notifMessage = "Your logbook (ID: $logbook_id) has been rejected by your supervisor. Reason: $reason";
            $notifQuery   = "INSERT INTO notification
                             (description, created_datetime, owner_of_notification, status, need_approval)
                             VALUES (?, NOW(), ?, 'unread', 0)";
            Database::iud($notifQuery, 'si', [$notifMessage, $student_id]);

            // Delete child records before deleting logbook (FK order matters)

            // 1. HOD approval record
            Database::iud(
                "DELETE FROM practical_finished_hod_notify_and_approval WHERE practical_finished_logbook_id = ?",
                'i', [$logbook_id]
            );

            // 2. Technical Officer approval record
            Database::iud(
                "DELETE FROM practical_finished_technicalofficer_notify_and_approval WHERE practical_finished_logbook_id = ?",
                'i', [$logbook_id]
            );

            // 3. Supervisor approval record
            Database::iud(
                "DELETE FROM practical_finished_supervisor_notify_and_approval WHERE practical_finished_logbook_id = ?",
                'i', [$logbook_id]
            );

            // 4. Logbook itself
            $deleted = Database::iud(
                "DELETE FROM practical_finished_logbook WHERE id = ?",
                'i', [$logbook_id]
            );

            error_log('Logbook deletion result for ID ' . $logbook_id . ': ' . ($deleted ? 'SUCCESS' : 'FAILED'));
        }

        echo json_encode(['success' => true, 'message' => 'Logbook rejected and student notified']);
        exit();
    }

} catch (Exception $e) {
    error_log('Exception in approve_logbook_supervisor.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
}
?>
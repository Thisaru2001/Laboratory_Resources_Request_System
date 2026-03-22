<?php
/**
 * Handle approve/reject of logbooks by HOD
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Auth check
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'hod') {
    error_log('Unauthorized access in approve_logbook.php');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get request data
$input = file_get_contents('php://input');
error_log('approve_logbook.php received: ' . $input);

$data       = json_decode($input, true);
$logbook_id = isset($data['logbook_id']) ? (int)$data['logbook_id'] : 0;
$action     = isset($data['action'])     ? trim($data['action'])     : '';
$reason     = isset($data['reason'])     ? trim($data['reason'])     : '';
$hod_id     = $_SESSION['user_id'];

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

    // Verify logbook exists and get student_id
    $checkQuery  = "SELECT id, student_id FROM practical_finished_logbook WHERE id = ?";
    $checkResult = Database::search($checkQuery, 'i', [$logbook_id]);

    if (!$checkResult || $checkResult->num_rows === 0) {
        error_log('Logbook not found: ' . $logbook_id);
        echo json_encode(['success' => false, 'message' => 'Logbook not found']);
        exit();
    }

    $logbookRow = $checkResult->fetch_assoc();
    $student_id = $logbookRow['student_id'];

    // ── APPROVE ──────────────────────────────────────────────────────────────
    if ($action === 'approve') {

        $checkApprovalQuery  = "SELECT id FROM practical_finished_hod_notify_and_approval 
                                WHERE practical_finished_logbook_id = ?";
        $approvalCheckResult = Database::search($checkApprovalQuery, 'i', [$logbook_id]);

        if ($approvalCheckResult && $approvalCheckResult->num_rows > 0) {
            $updateQuery = "UPDATE practical_finished_hod_notify_and_approval
                            SET is_approved = 1,
                                status = 'read',
                                rejection_reason = NULL,
                                approved_or_rejected_datetime = NOW()
                            WHERE practical_finished_logbook_id = ?";
            error_log('Updating existing HOD approval record for logbook ' . $logbook_id);
            $updated = Database::iud($updateQuery, 'i', [$logbook_id]);
        } else {
            $insertQuery = "INSERT INTO practical_finished_hod_notify_and_approval
                            (practical_finished_logbook_id, is_approved, status, approved_or_rejected_datetime)
                            VALUES (?, 1, 'read', NOW())";
            error_log('Creating new HOD approval record for logbook ' . $logbook_id);
            $updated = Database::iud($insertQuery, 'i', [$logbook_id]);
        }

        if ($updated) {
            // Notify student of HOD approval
            $notifMessage = "Your logbook (ID: $logbook_id) has been approved by the HOD.";
            $notifQuery   = "INSERT INTO notification
                             (description, created_datetime, owner_of_notification, status, need_approval)
                             VALUES (?, NOW(), ?, 'unread', 0)";
            Database::iud($notifQuery, 'si', [$notifMessage, $student_id]);

            error_log('Successfully approved logbook ' . $logbook_id);
            echo json_encode(['success' => true, 'message' => 'Logbook approved successfully!']);
        } else {
            error_log('Failed to approve logbook');
            echo json_encode(['success' => false, 'message' => 'Failed to approve logbook']);
        }
        exit();
    }

    // ── REJECT ───────────────────────────────────────────────────────────────
    if ($action === 'reject') {

        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a reason for rejection']);
            exit();
        }

        $checkApprovalQuery  = "SELECT id FROM practical_finished_hod_notify_and_approval 
                                WHERE practical_finished_logbook_id = ?";
        $approvalCheckResult = Database::search($checkApprovalQuery, 'i', [$logbook_id]);

        if ($approvalCheckResult && $approvalCheckResult->num_rows > 0) {
            $queryStr = "UPDATE practical_finished_hod_notify_and_approval
                         SET is_approved = 0,
                             status = 'read',
                             rejection_reason = ?,
                             approved_or_rejected_datetime = NOW()
                         WHERE practical_finished_logbook_id = ?";
            error_log('Updating HOD rejection record for logbook ' . $logbook_id);
            $updated = Database::iud($queryStr, 'si', [$reason, $logbook_id]);
        } else {
            $queryStr = "INSERT INTO practical_finished_hod_notify_and_approval
                         (practical_finished_logbook_id, is_approved, status, rejection_reason, approved_or_rejected_datetime)
                         VALUES (?, 0, 'read', ?, NOW())";
            error_log('Creating new HOD rejection record for logbook ' . $logbook_id);
            $updated = Database::iud($queryStr, 'is', [$logbook_id, $reason]);
        }

        if ($updated) {
            // Notify student of HOD rejection
            $notifMessage = "Your logbook (ID: $logbook_id) has been rejected by the HOD. Reason: $reason";
            $notifQuery   = "INSERT INTO notification
                             (description, created_datetime, owner_of_notification, status, need_approval)
                             VALUES (?, NOW(), ?, 'unread', 0)";
            Database::iud($notifQuery, 'si', [$notifMessage, $student_id]);

            error_log('Successfully rejected logbook ' . $logbook_id);
            echo json_encode(['success' => true, 'message' => 'Logbook rejected and student notified']);
        } else {
            error_log('Failed to reject logbook');
            echo json_encode(['success' => false, 'message' => 'Failed to reject logbook']);
        }
        exit();
    }

} catch (Exception $e) {
    error_log('Exception in approve_logbook.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
}
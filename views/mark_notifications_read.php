<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$student_id = $_SESSION["user_id"];

// Get POST data
$post_student_id = $_POST['student_id'] ?? 0;

// Verify that the student ID matches the session
if ($post_student_id != $student_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
    exit();
}

// Option 1: Delete all notifications for this student
$delete_query = "DELETE FROM notification WHERE owner_of_notification = ?";
$success = Database::iud($delete_query, "i", [$student_id]);

// Option 2: If you have an 'is_read' column, you can use this instead:
// $update_query = "UPDATE notification SET is_read = 1 WHERE owner_of_notification = ? AND is_read = 0";
// $success = Database::iud($update_query, "i", [$student_id]);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Notifications marked as read']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read', 'error' => Database::getLastError()]);
}
?>
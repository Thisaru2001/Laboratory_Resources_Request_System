<?php
session_start();
include "../config/database.php";

header('Content-Type: application/json');

if ($_SESSION["user_role"] !== 'supervisor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data['student_id'] ?? null;
$reason     = $data['reason'] ?? '';

if (!$student_id || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Missing student ID or rejection reason']);
    exit;
}

try {
    // Get student details before deleting (for logging)
    $get_student = Database::search("SELECT * FROM lab_user WHERE id = ?", "i", [$student_id]);
    if (!$get_student || $get_student->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    $student = $get_student->fetch_assoc();

    // 1. Delete from lab_user_has_role first (FK constraint)
    $delete_role = Database::iud(
        "DELETE FROM lab_user_has_role WHERE lab_user_id = ?",
        "i", [$student_id]
    );

    // 2. Delete from lab_user
    $delete_user = Database::iud(
        "DELETE FROM lab_user WHERE id = ?",
        "i", [$student_id]
    );

    if (!$delete_user) {
        throw new Exception('Failed to delete student');
    }

    error_log("Student deleted - ID: {$student_id}, Email: {$student['email']}, Reason: {$reason}");

    echo json_encode([
        'success' => true,
        'message' => 'Student request rejected and account removed successfully'
    ]);

} catch (Exception $e) {
    error_log("Error rejecting student: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
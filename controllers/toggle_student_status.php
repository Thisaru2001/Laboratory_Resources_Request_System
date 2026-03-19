<?php
declare(strict_types=1);
session_start();

require_once '../config/database.php';

header('Content-Type: application/json');

// Check if supervisor is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['student_id'] ?? 0;
$new_status = $data['status'] ?? null;
$supervisor_id = $_SESSION['user_id'];

if (!$student_id || $new_status === null) {
    echo json_encode(['success' => false, 'message' => 'Student ID and status required']);
    exit;
}

// Validate status value
if (!in_array($new_status, [0, 1])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

try {
    // Verify that the supervisor is assigned to this student
    $checkQuery = "SELECT id FROM supervisor_assigned_student 
                   WHERE supervisor_id_or_hod_id = ? AND student_id = ?";
    $checkResult = Database::search($checkQuery, "ii", [$supervisor_id, $student_id]);
    
    if ($checkResult === false || $checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    // Update student status
    $updateQuery = "UPDATE lab_user SET status = ? WHERE id = ?";
    $success = Database::iud($updateQuery, "ii", [$new_status, $student_id]);
    
    if ($success) {
        // Get student details for logging
        $studentQuery = "SELECT first_name, last_name, email FROM lab_user WHERE id = ?";
        $studentResult = Database::search($studentQuery, "i", [$student_id]);
        $student = $studentResult->fetch_assoc();
        
        error_log("Student status updated: Student ID: {$student_id}, New Status: {$new_status}, Updated by Supervisor ID: {$supervisor_id}");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Student status updated successfully',
            'student_name' => htmlspecialchars($student['first_name'] . ' ' . $student['last_name'])
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }

} catch (Exception $e) {
    error_log("Error in toggle_student_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>

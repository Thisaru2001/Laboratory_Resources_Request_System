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

$student_id = $_GET['id'] ?? 0;
$supervisor_id = $_SESSION['user_id'];

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'Student ID required']);
    exit;
}

try {
    // Get student details
    $query = "SELECT id, university_id, first_name, last_name, email, mobile, 
                     join_datetime, status, img_path
              FROM lab_user 
              WHERE id = ? AND who_approved = ?";
    
    $result = Database::search($query, "ii", [$student_id, $supervisor_id]);
    
    if ($result === false) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    $student = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'student' => $student
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_student_details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
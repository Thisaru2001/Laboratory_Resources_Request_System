<?php
declare(strict_types=1);
session_start();

require_once 'Database.php';

header('Content-Type: application/json');

// Check if supervisor is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['student_id'] ?? 0;
$supervisor_id = $_SESSION['user_id'];

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'Student ID required']);
    exit;
}

try {
    // Start transaction
    Database::iud("START TRANSACTION");
    
    // Delete or mark as rejected (choose one option below)
    
    // OPTION 1: Permanently delete (if you want to remove completely)
    $query = "DELETE FROM lab_user 
              WHERE id = ? 
              AND who_approved = ? 
              AND status = 0";
    
    // OPTION 2: Mark as rejected (if you want to keep record)
    // $query = "UPDATE lab_user 
    //           SET status = 2,  -- 2 = rejected
    //               rejected_date = NOW() 
    //           WHERE id = ? 
    //           AND who_approved = ? 
    //           AND status = 0";
    
    $success = Database::iud($query, "ii", [$student_id, $supervisor_id]);
    
    // Check if any row was affected
    $checkQuery = "SELECT ROW_COUNT() as affected";
    $checkResult = Database::search($checkQuery);
    $affected = $checkResult->fetch_assoc()['affected'];
    
    if ($affected > 0) {
        // Commit transaction
        Database::iud("COMMIT");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Student request removed successfully'
        ]);
    } else {
        // Rollback if no rows affected
        Database::iud("ROLLBACK");
        echo json_encode([
            'success' => false, 
            'message' => 'No pending request found or already processed'
        ]);
    }
    
} catch (Exception $e) {
    // Rollback on error
    Database::iud("ROLLBACK");
    error_log("Error in remove_student: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
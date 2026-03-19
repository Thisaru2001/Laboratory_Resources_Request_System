<?php
declare(strict_types=1);
session_start();

require_once '../config/database.php';

header('Content-Type: application/json');

// Check if supervisor is logged in (adjust based on your session variable)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$supervisor_id = $_SESSION['user_id']; // Assuming this is the supervisor's ID

try {
    // Query for pending students where status = 0 and who_approved = supervisor_id
    $query = "SELECT id, university_id, first_name, last_name, join_datetime 
FROM lab_user 
WHERE status = 0 
AND who_approved = ?
AND approved_datetime IS NULL
ORDER BY join_datetime DESC";
    
    $result = Database::search($query, "i", [$supervisor_id]);
    
    if ($result === false) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . Database::getLastError()]);
        exit;
    }
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'count' => count($requests)
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_pending_students: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
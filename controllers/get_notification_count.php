<?php
declare(strict_types=1);

// get_notification_count.php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

try {
    if (isset($_GET['user_id'])) {
        $student_id = intval($_GET['user_id']);
        
        if ($student_id > 0) {
            $query = "SELECT COUNT(*) as unread_count 
                     FROM notification 
                     WHERE owner_of_notification = ? AND status = 'unread'";
            
            $result = Database::search($query, "i", [$student_id]);
            
            if ($result) {
                $data = $result->fetch_assoc();
                echo json_encode(['success' => true, 'count' => (int)$data['unread_count']]);
            } else {
                echo json_encode(['success' => false, 'error' => Database::getLastError()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid student ID']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Student ID not provided']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
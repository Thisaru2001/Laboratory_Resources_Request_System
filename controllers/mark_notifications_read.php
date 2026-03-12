<?php
declare(strict_types=1);

// mark_notifications_read.php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

try {
    if (isset($_POST['user_id'])) {
        $student_id = intval($_POST['user_id']);
        
        if ($student_id > 0) {
            // Update all unread notifications to read
            $query = "UPDATE notification 
                      SET status = 'read' 
                      WHERE owner_of_notification = ? AND status = 'unread'";
            
            $success = Database::iud($query, "i", [$student_id]);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Notifications marked as read']);
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
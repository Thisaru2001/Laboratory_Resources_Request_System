<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is a supervisor
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'supervisor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$supervisor_id = $_SESSION["user_id"];

// Get POST data
$reservation_id = $_POST['reservation_id'] ?? 0;
$action = $_POST['action'] ?? '';
$reason = $_POST['reason'] ?? '';

// Validate input
if (!$reservation_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

if (!in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

try {
   
    
    if ($action === 'approve') {
        // APPROVE: Update supervisor_id in reservation table
        $update_query = "UPDATE reservation 
                         SET supervisor_id = ? 
                         WHERE id = ? AND supervisor_id IS NULL";
        
        $success = Database::iud($update_query, "ii", [$supervisor_id, $reservation_id]);
        
        if (!$success) {
            throw new Exception('Failed to approve reservation: ' . Database::getLastError());
        }
        
        // Check if any row was affected
        $check_query = "SELECT ROW_COUNT() as affected";
        $check_result = Database::search($check_query);
        $affected = $check_result->fetch_assoc()['affected'];
        
        if ($affected == 0) {
            throw new Exception('Reservation not found or already processed');
        }
        
        // Get reservation details for notification
        $res_query = "SELECT r.reservation_id, r.student_id, l.location, 
                             CONCAT(st.first_name, ' ', st.last_name) as student_name
                      FROM reservation r
                      JOIN location l ON r.location_id = l.id
                      JOIN lab_user st ON r.student_id = st.id
                      WHERE r.id = ?";
        $res_result = Database::search($res_query, "i", [$reservation_id]);
        $res_data = $res_result->fetch_assoc();
        
        // Create notification for student
        $notif_message = "Your reservation " . $res_data['reservation_id'] . " has been approved by your supervisor and forwarded to Technical Officer.";
        $notif_query = "INSERT INTO notification (description, created_datetime, owner_of_notification) VALUES (?, NOW(), ?)";
        Database::iud($notif_query, "si", [$notif_message, $res_data['student_id']]);
        
        // Create notification for technical officers (role_id = 3)
        $tech_notif = "New approved reservation " . $res_data['reservation_id'] . " requires your attention.";
        $tech_query = "SELECT lab_user_id FROM lab_user_has_role WHERE role_id = 3";
        $tech_result = Database::search($tech_query);
        
        if ($tech_result && $tech_result->num_rows > 0) {
            while ($tech = $tech_result->fetch_assoc()) {
                Database::iud($notif_query, "si", [$tech_notif, $tech['lab_user_id']]);
            }
        }
        
    } else {
        // REJECT: Insert into reject_reason table
        if (empty($reason)) {
           $reason='-';
        }
        
        $reject_query = "INSERT INTO reject_reason (reason, reservation_id) VALUES (?, ?)";
        $success = Database::iud($reject_query, "si", [$reason, $reservation_id]);
        
        if (!$success) {
            throw new Exception('Failed to reject reservation: ' . Database::getLastError());
        }
        
        // Get reservation details for notification
        $res_query = "SELECT r.reservation_id, r.student_id 
                      FROM reservation r
                      WHERE r.id = ?";
        $res_result = Database::search($res_query, "i", [$reservation_id]);
        $res_data = $res_result->fetch_assoc();
        
        // Create notification for student
        $notif_message = "Your reservation " . $res_data['reservation_id'] . " has been rejected. Reason: " . $reason;
        $notif_query = "INSERT INTO notification (description, created_datetime, owner_of_notification) VALUES (?, NOW(), ?)";
        Database::iud($notif_query, "si", [$notif_message, $res_data['student_id']]);
    }
    
   
    
    echo json_encode([
        'success' => true,
        'message' => $action === 'approve' ? 'Reservation approved successfully' : 'Reservation rejected successfully'
    ]);
    
} catch (Exception $e) {
   
    error_log("Handle reservation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
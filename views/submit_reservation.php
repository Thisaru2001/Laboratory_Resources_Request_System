<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is student
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$student_id = $_SESSION["user_id"];

// Get POST data
$location_id = $_POST['location_id'] ?? 0;
$request_date = $_POST['request_date'] ?? '';
$continue_days = $_POST['continue_days'] ?? 1;
$comment = $_POST['comment'] ?? '';
$equipment_json = $_POST['equipment'] ?? '[]';

// Validate input
if (!$location_id || !$request_date || !$equipment_json) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$equipment = json_decode($equipment_json, true);
if (empty($equipment)) {
    echo json_encode(['success' => false, 'message' => 'No equipment selected']);
    exit();
}

// Get student's supervisor from supervisor_assigned_student table
$supervisor_query = "SELECT supervisor_id_or_hod_id FROM supervisor_assigned_student WHERE student_id = ? LIMIT 1";
$supervisor_result = Database::search($supervisor_query, "i", [$student_id]);
$supervisor_id = null;
if ($supervisor_result && $supervisor_result->num_rows > 0) {
    $supervisor_data = $supervisor_result->fetch_assoc();
    $supervisor_id = $supervisor_data['supervisor_id_or_hod_id'];
}

if (!$supervisor_id) {
    echo json_encode(['success' => false, 'message' => 'No supervisor assigned. Please contact HOD.']);
    exit();
}

// Get technical officer ID - using correct table name 'lab_user_has_role'
$to_query = "SELECT u.id FROM lab_user u 
             JOIN lab_user_has_role ur ON u.id = ur.lab_user_id 
             JOIN role r ON ur.role_id = r.id 
             WHERE r.role = 'Technical Officer' LIMIT 1";
$to_result = Database::search($to_query);
$technical_officer_id = null;
if ($to_result && $to_result->num_rows > 0) {
    $to_data = $to_result->fetch_assoc();
    $technical_officer_id = $to_data['id'];
} else {
    $technical_officer_id = 3; // Fallback to ID 3
}

// Generate reservation ID
$year = date('Y');
$count_query = "SELECT COUNT(*) as count FROM reservation WHERE reservation_id LIKE 'RES-$year%'";
$count_result = Database::search($count_query);
$count_row = $count_result->fetch_assoc();
$count = ($count_row['count'] ?? 0) + 1;
$reservation_id = 'RES-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

// Calculate end date for availability checking
$end_date = date('Y-m-d', strtotime($request_date . ' + ' . ($continue_days - 1) . ' days'));

// Debug log
error_log("Submitting reservation: reservation_id=$reservation_id, student_id=$student_id, supervisor_id=$supervisor_id, technical_officer_id=$technical_officer_id, location_id=$location_id, date=$request_date, days=$continue_days");

// Begin transaction
try {
    // Insert reservation with continue_days - NOTE: created_datetime is set to NOW() in query, so not included in params
    $insert_query = "INSERT INTO reservation 
                    (reservation_id, created_datetime, student_id, supervisor_id, 
                     technical_officer_id, location_id, request_date, continue_days, comment) 
                    VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
    
    // Parameters (8 params): 
    // reservation_id(s), student_id(i), supervisor_id(i), technical_officer_id(i), 
    // location_id(i), request_date(s), continue_days(i), comment(s)
    $insert_success = Database::iud($insert_query, "siiiisss", [
        $reservation_id, 
        $student_id, 
        $supervisor_id, 
        $technical_officer_id, 
        $location_id, 
        $request_date, 
        $continue_days, 
        $comment
    ]);
    
    if (!$insert_success) {
        throw new Exception('Failed to create reservation: ' . Database::getLastError());
    }
    
    // Get the inserted reservation ID
    $reservation_db_id = Database::lastInsertId();
    
    // Insert booked equipment
    foreach ($equipment as $item) {
        // Check availability for the entire date range
        $check_query = "SELECT e.total_qty,
                       COALESCE((SELECT SUM(broken_qty) FROM broken WHERE equipment_id = e.id), 0) as broken_qty,
                       COALESCE((SELECT SUM(repair_qty) FROM repair WHERE equipment_id = e.id), 0) as repair_qty,
                       COALESCE((SELECT SUM(be.book_qty) FROM book_equipment be 
                                JOIN reservation r ON be.reservation_id = r.id 
                                WHERE be.equipment_id = e.id 
                                AND r.request_date <= ? 
                                AND DATE_ADD(r.request_date, INTERVAL (r.continue_days - 1) DAY) >= ?), 0) as booked_qty
                       FROM equipment e WHERE e.id = ?";
        
        $check_result = Database::search($check_query, "ssi", [$end_date, $request_date, $item['id']]);
        $check_row = $check_result->fetch_assoc();
        
        $available = $check_row['total_qty'] - $check_row['broken_qty'] - $check_row['repair_qty'] - $check_row['booked_qty'];
        
        if ($available < $item['qty']) {
            throw new Exception("Insufficient quantity for equipment ID: " . $item['id'] . ". Only $available available for the selected dates.");
        }
        
        $book_query = "INSERT INTO book_equipment (book_qty, reservation_id, equipment_id) VALUES (?, ?, ?)";
        $book_success = Database::iud($book_query, "iii", [$item['qty'], $reservation_db_id, $item['id']]);
        
        if (!$book_success) {
            throw new Exception('Failed to book equipment: ' . Database::getLastError());
        }
    }
    
    // Create notification for supervisor
  // Create notification for supervisor - FIXED: include created_datetime
$notif_message = "New reservation submitted by student. Reservation ID: $reservation_id for " . $continue_days . " day(s) starting " . $request_date;
$notif_query = "INSERT INTO notification (description, created_datetime, owner_of_notification) VALUES (?, NOW(), ?)";
Database::iud($notif_query, "si", [$notif_message, $supervisor_id]);
    
    echo json_encode(['success' => true, 'message' => 'Reservation submitted successfully', 'reservation_id' => $reservation_id]);
    
} catch (Exception $e) {
    error_log("Reservation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
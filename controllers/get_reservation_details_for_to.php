<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is technical officer
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'technical_officer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$reservation_display_id = $_GET['id'] ?? '';

error_log("get_reservation_details_for_to.php - Received Display ID: " . $reservation_display_id);

if (empty($reservation_display_id)) {
    echo json_encode(['success' => false, 'message' => 'Reservation ID required']);
    exit();
}

try {
    // Get main reservation details
    $query = "SELECT 
                r.id,
                r.reservation_id,
                r.request_date,
                r.continue_days,
                r.comment,
                l.location as lab_location,
                CONCAT(st.first_name, ' ', st.last_name) as student_name,
                st.university_id,
                CONCAT(sup.first_name, ' ', sup.last_name) as supervisor_name
              FROM reservation r
              JOIN location l ON r.location_id = l.id
              JOIN lab_user st ON r.student_id = st.id
              LEFT JOIN lab_user sup ON r.supervisor_id = sup.id
              WHERE r.reservation_id = ?";
    
    $result = Database::search($query, "s", [$reservation_display_id]);
    
    if (!$result || $result->num_rows === 0) {
        error_log("Reservation not found with display ID: " . $reservation_display_id);
        echo json_encode(['success' => false, 'message' => 'Reservation not found with ID: ' . $reservation_display_id]);
        exit();
    }
    
    $reservation = $result->fetch_assoc();
    $numeric_id = $reservation['id'];
    
    error_log("Found reservation: Numeric ID=" . $numeric_id . ", Display ID=" . $reservation['reservation_id']);
    
    // Calculate date range
    $start_date = date('Y-m-d', strtotime($reservation['request_date']));
    $end_date = date('Y-m-d', strtotime($reservation['request_date'] . ' + ' . ($reservation['continue_days'] - 1) . ' days'));
    $date_range = $start_date;
    if ($reservation['continue_days'] > 1) {
        $date_range .= " to " . $end_date . " (" . $reservation['continue_days'] . " days)";
    }
    $reservation['date_range'] = $date_range;
    
    // Get equipment list with booking quantities - NO COMMENTS IN SQL!
    $equipment_query = "SELECT 
                        be.id as book_equipment_id,
                        e.id as equipment_id,
                        e.name as equipment_name,
                        be.book_qty as quantity
                        FROM book_equipment be
                        JOIN equipment e ON be.equipment_id = e.id
                        WHERE be.reservation_id = ?";
    
    $equipment_result = Database::search($equipment_query, "i", [$numeric_id]);
    
    $equipment_list = [];
    if ($equipment_result && $equipment_result->num_rows > 0) {
        $index = 1;
        while ($row = $equipment_result->fetch_assoc()) {
            $equipment_list[] = [
                'no' => $index++,
                'id' => $row['book_equipment_id'],
                'equipment_id' => (int)$row['equipment_id'],
                'name' => $row['equipment_name'],
                'qty' => (int)$row['quantity']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'reservation' => $reservation,
        'equipment' => $equipment_list
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching reservation details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred: ' . $e->getMessage()]);
}
?>
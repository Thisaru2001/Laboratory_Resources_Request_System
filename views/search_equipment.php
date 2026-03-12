<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$term = $_GET['term'] ?? '';

// If no search term, return empty results
if (empty($term) || strlen($term) < 2) {
    echo json_encode([]);
    exit();
}

// Calculate available quantity for future bookings
$query = "SELECT e.id, e.code, e.name, e.total_qty,
          COALESCE((SELECT SUM(broken_qty) FROM broken WHERE equipment_id = e.id), 0) as broken_qty,
          COALESCE((SELECT SUM(repair_qty) FROM repair WHERE equipment_id = e.id), 0) as repair_qty,
          COALESCE((SELECT SUM(be.book_qty) FROM book_equipment be 
                   JOIN reservation r ON be.reservation_id = r.id 
                   WHERE be.equipment_id = e.id 
                   AND r.request_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                   AND DATE_ADD(r.request_date, INTERVAL (r.continue_days - 1) DAY) >= CURDATE()), 0) as booked_qty
          FROM equipment e
          WHERE e.is_hod_checked = 1
          AND (e.name LIKE ? OR e.code LIKE ?)
          ORDER BY e.name 
          LIMIT 20";

$search_term = "%$term%";
$result = Database::search($query, "ss", [$search_term, $search_term]);

if (!$result) {
    echo json_encode([]);
    exit();
}

$equipment = [];
while ($row = $result->fetch_assoc()) {
    $available = $row['total_qty'] - $row['broken_qty'] - $row['repair_qty'] - $row['booked_qty'];
    if ($available > 0) {
        $equipment[] = [
            'id' => (int)$row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'available_qty' => (int)$available
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($equipment);
?>
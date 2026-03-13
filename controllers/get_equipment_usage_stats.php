<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Get equipment usage statistics from database
$query = "
    SELECT 
        e.id,
        e.code,
        e.name,
        COUNT(be.id) as booking_count,
        (SELECT COUNT(*) FROM reservation) as total_reservations
    FROM equipment e
    LEFT JOIN book_equipment be ON e.id = be.equipment_id
    GROUP BY e.id, e.code, e.name
    ORDER BY booking_count DESC
";

$result = Database::search($query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Database query failed'
    ]);
    exit;
}

$equipment = [];
$total_reservations = 0;

while ($row = $result->fetch_assoc()) {
    $total_reservations = $row['total_reservations'];
    $usage_percent = $total_reservations > 0 
        ? round(($row['booking_count'] / $total_reservations) * 100, 1)
        : 0;
    
    $equipment[] = [
        'id' => $row['id'],
        'code' => $row['code'],
        'name' => $row['name'],
        'bookings' => (int)$row['booking_count'],
        'usage' => $usage_percent
    ];
}

echo json_encode([
    'success' => true,
    'equipment' => $equipment,
    'total' => count($equipment)
]);
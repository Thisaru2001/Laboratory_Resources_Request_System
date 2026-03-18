<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Fix: use user_id (matches TO dashboard session)
if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$equipment_code = $_GET['code'] ?? '';

if (empty($equipment_code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid equipment code']);
    exit();
}

$query = "SELECT 
            e.id, 
            e.code, 
            e.name, 
            e.total_qty as total_qty, 
            e.simultaneous_users,
            e.description, 
            e.sterilization_required,
            e.reservation_required, 
            e.added_datatime,
            e.image_path as image_path,
            COALESCE((SELECT SUM(broken_qty) FROM broken WHERE equipment_id = e.id), 0) as broken_qty,
            COALESCE((SELECT SUM(repair_qty) FROM repair WHERE equipment_id = e.id), 0) as repair_qty,
            GROUP_CONCAT(DISTINCT l.location SEPARATOR ', ') as locations
          FROM equipment e
          LEFT JOIN equipment_has_location ehl ON e.id = ehl.equipment_id
          LEFT JOIN location l ON ehl.location_id = l.id
          WHERE e.code = ?
          GROUP BY e.id";

$result = Database::search($query, "s", [$equipment_code]);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database query failed']);
    exit();
}

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Equipment not found with code: ' . $equipment_code]);
    exit();
}

$row = $result->fetch_assoc();

$image_url = 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';

if (!empty($row['image_path'])) {
    $clean_path = str_replace('\\', '/', $row['image_path']);
    $clean_path = ltrim($clean_path, '/');
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $clean_path;
    if (file_exists($full_path)) {
        $image_url = '/' . $clean_path;
    }
}

echo json_encode([
    'success' => true,
    'equipment' => [
        'id'                     => $row['id'],
        'code'                   => $row['code'],
        'name'                   => $row['name'],
        'total_qty'              => $row['total_qty'],
        'simultaneous_users'     => $row['simultaneous_users'] ?? 1,
        'description'            => $row['description'],
        'sterilization_required' => $row['sterilization_required'] ?? 'NO',
        'reservation_required'   => $row['reservation_required'] ?? 'YES',
        'added_datetime'         => $row['added_datatime'],
        'image_path'             => $image_url,
        'locations'              => $row['locations'] ?? 'Not assigned',
        'broken_qty'             => (int)$row['broken_qty'],
        'repair_qty'             => (int)$row['repair_qty'],
    ]
]);
exit();
?>
<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$equipment_code = $_GET['code'] ?? '';
// error_log("Searching for equipment code: " . $equipment_code);

if (empty($equipment_code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid equipment code']);
    exit();
}

// Fix the query - use 'code' column instead of 'equipment_code'C:\xampp\htdocs\LRRS\controllers\get_equipment_details.php
$query = "SELECT 
    e.id, 
    e.code, 
    e.name, 
    e.total_qty as total_qty, 
    e.simultaneous_users,
    e.description, 
    e.sterilization_required,
    e.reservation_required, 
    e.image_path as image_path,
    e.added_datatime,  -- Added this field
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

// Handle image path - return relative path that works from /views/ folder
$image_url = 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png'; // Default image

if (!empty($row['image_path'])) {
    // The image_path from DB may be: "assets/equipment_images/filename" or just filename or "1"
    $path = trim($row['image_path']);
    
    // If path doesn't contain 'assets/', assume it needs that prefix
    if (strpos($path, 'assets') === false) {
        $path = 'assets/equipment_images/' . basename($path);
    }
    
    // Clean the path
    $clean_path = str_replace('\\', '/', $path);
    $clean_path = ltrim($clean_path, '/');
    
    // Try to verify file exists
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $clean_path;
    
    error_log("Equipment image DB path: " . $row['image_path']);
    error_log("Equipment image check full path: " . $full_path);
    
    if (file_exists($full_path)) {
        // Use relative path from /views/ folder
        $image_url = '../' . $clean_path;
        error_log("✓ Equipment image found: " . $image_url);
    } else {
        error_log("✗ Equipment image not found at: " . $full_path);
        error_log("  → Using relative path anyway: " . '../' . $clean_path);
        // Even if file_exists fails, use the path anyway (it might work)
        $image_url = '../' . $clean_path;
    }
}

// Return JSON data
echo json_encode([
    'success' => true,
    'equipment' => [
        'id' => $row['id'],
        'code' => $row['code'],
        'name' => $row['name'],
        'total_qty' => $row['total_qty'],
        'simultaneous_users' => $row['simultaneous_users'] ?? 1,
        'description' => $row['description'],
        'sterilization_required' => $row['sterilization_required'] ?? 'NO',
        'reservation_required' => $row['reservation_required'] ?? 'YES',
        'image_path' => $image_url,
        'locations' => $row['locations'] ?? 'Not assigned',  //
        'addedDate' => $row['added_datatime'] ?? 'Unknown'   // 
    ]
]);
exit();
?>
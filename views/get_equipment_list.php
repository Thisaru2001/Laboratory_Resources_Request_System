<?php
session_start();
require_once '../config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo '<tr><td colspan="4" class="text-center text-danger">Unauthorized</td></tr>';
    exit();
}

$student_id = $_SESSION["user_id"];
$lab_id = $_GET['lab_id'] ?? 'all';
$search_term = $_GET['term'] ?? '';

// Build query - include image_path
$query = "SELECT e.id, e.code, e.name, e.total_qty, e.image_path,
          GROUP_CONCAT(DISTINCT l.location SEPARATOR ', ') as locations,
          GROUP_CONCAT(DISTINCT l.id SEPARATOR ',') as location_ids
          FROM equipment e
          LEFT JOIN equipment_has_location ehl ON e.id = ehl.equipment_id
          LEFT JOIN location l ON ehl.location_id = l.id
          WHERE e.is_hod_checked = 1";

$params = [];
$types = "";

if ($lab_id !== 'all') {
    $query .= " AND e.id IN (SELECT equipment_id FROM equipment_has_location WHERE location_id = ?)";
    $params[] = $lab_id;
    $types .= "i";
}

if (!empty($search_term)) {
    $query .= " AND (e.name LIKE ? OR e.code LIKE ?)";
    $search_term_wildcard = "%$search_term%";
    $params[] = $search_term_wildcard;
    $params[] = $search_term_wildcard;
    $types .= "ss";
}

$query .= " GROUP BY e.id ORDER BY e.name LIMIT 50";

// Execute query
if (!empty($params)) {
    $result = Database::search($query, $types, $params);
} else {
    $result = Database::search($query);
}

if (!$result) {
    echo '<tr><td colspan="4" class="text-center text-danger">Database error: ' . Database::getLastError() . '</td></tr>';
    exit();
}

if ($result->num_rows === 0) {
    echo '<tr><td colspan="4" class="text-center text-muted py-4">';
    echo '<i class="bi bi-inbox fs-1 d-block mb-2"></i>';
    echo 'No equipment found.';
    echo '</td></tr>';
    exit();
}

while ($row = $result->fetch_assoc()) {
    // Handle image path - try to use relative path even if file check fails
    $image_url = '../assets/equipment_images/default_img.png'; // Default image
    
    if (!empty($row['image_path'])) {
        $path = trim($row['image_path']);
        
        // If path doesn't contain 'assets/', fix it
        if (strpos($path, 'assets') === false) {
            $path = 'assets/equipment_images/' . basename($path);
        }
        
        // Clean the path
        $clean_path = str_replace('\\', '/', $path);
        $clean_path = ltrim($clean_path, '/');
        
        // Use relative path from /views/ folder
        $image_url = '../' . $clean_path;
        
        error_log("Equipment list image: " . $row['image_path'] . " → " . $image_url);
    }
    
    // Display locations or "No location assigned"
    $location_display = !empty($row['locations']) ? $row['locations'] : '<span class="text-muted">Not assigned to any lab</span>';
    
    echo '<tr>';
    echo '<td data-label="Image"><img src="' . htmlspecialchars($image_url) . '" class="equipment-image" alt="' . htmlspecialchars($row['name']) . '" style="width: 50px; height: 50px; object-fit: contain;"></td>';
    echo '<td data-label="Name"><strong>' . htmlspecialchars($row['name']) . '</strong><br><small class="text-muted">Code: ' . htmlspecialchars($row['code']) . '</small></td>';
    echo '<td data-label="Location">' . $location_display . '</td>';
    echo '<td data-label="Action">';
    echo '<button class="btn-view" onclick="viewEquipmentDetails(\'' . htmlspecialchars($row['code']) . '\')">';
    echo '<i class="bi bi-eye"></i> View';
    echo '</button>';
    echo '</td>';
    echo '</tr>';
}
?>
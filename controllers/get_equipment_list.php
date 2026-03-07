<?php
session_start();
require_once "../config/database.php";

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

$response = ['success' => false, 'data' => []];

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'HOD') {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

try {
    $query = "SELECT 
                e.equipment_id,
                e.equipment_code,
                e.equipment_name,
                e.description,
                e.qty as total_qty,
                e.image_path as image,
                e.location,
                e.manufacturer,
                e.model,
                e.purchase_date,
                e.last_maintenance,
                e.next_maintenance,
                (SELECT COALESCE(SUM(qty), 0) FROM equipment_maintenance WHERE equipment_id = e.equipment_id AND status = 'pending') as maintenance
              FROM equipment e
              WHERE e.status = 'active'
              ORDER BY e.equipment_name ASC";
    
    $result = Database::search($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['available'] = $row['total_qty'] - $row['maintenance'];
            // Default usage percentage (will be overridden by AI)
            $row['usage_percentage'] = 50;
            $row['color'] = '#22c55e';
            $response['data'][] = $row;
        }
    }
    
    $response['success'] = true;
    
} catch (Exception $e) {
    error_log("Get Equipment Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
}

echo json_encode($response);
exit;
?>
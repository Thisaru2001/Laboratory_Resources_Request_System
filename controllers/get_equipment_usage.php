<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    // Get all equipment with maintenance, broken, usage statistics, and locations
    $query = "
       SELECT 
    e.id,
    e.code,
    e.name,
    e.total_qty as qty,
    e.image_path,
    e.description,
    e.added_datatime as added_datetime,
    e.is_hod_checked,
    -- Calculate maintenance pending
    COALESCE(r.repair_qty, 0) as maintenance_pending,
    -- Calculate broken quantity
    COALESCE(b.broken_qty, 0) as broken_qty,
    -- Count total bookings
    COUNT(DISTINCT be.id) as total_bookings,
    -- Get location data
    GROUP_CONCAT(DISTINCT l.location SEPARATOR ', ') as locations
FROM equipment e
LEFT JOIN (
    SELECT equipment_id, SUM(repair_qty) as repair_qty
    FROM repair 
    GROUP BY equipment_id
) r ON e.id = r.equipment_id
LEFT JOIN (
    SELECT equipment_id, SUM(broken_qty) as broken_qty
    FROM broken 
    GROUP BY equipment_id
) b ON e.id = b.equipment_id
LEFT JOIN book_equipment be ON e.id = be.equipment_id
LEFT JOIN equipment_has_location ehl ON e.id = ehl.equipment_id
LEFT JOIN location l ON ehl.location_id = l.id
WHERE e.is_hod_checked = 1
GROUP BY e.id, e.code, e.name, e.total_qty, e.image_path, e.description, 
         e.added_datatime, e.is_hod_checked, r.repair_qty, b.broken_qty
ORDER BY e.id
    ";

    $result = Database::search($query);
    
    if (!$result) {
        throw new Exception('Database query failed');
    }

    // Get total reservations for usage percentage calculation
    $total_res_query = "SELECT COUNT(*) as total FROM reservation WHERE request_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
    $total_res_result = Database::search($total_res_query);
    $total_reservations = $total_res_result ? $total_res_result->fetch_assoc()['total'] : 1;
    $total_reservations = max(1, $total_reservations); // Avoid division by zero

    $equipment_list = [];
    
    while ($row = $result->fetch_assoc()) {
        // Calculate usage percentage based on bookings vs total reservations
        $usage_percentage = round(($row['total_bookings'] / $total_reservations) * 100);
        
        // Format image path
        $image = !empty($row['image_path']) 
            ? '../' . ltrim(str_replace('\\', '/', $row['image_path']), '/')
            : 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';
        
        // Process location data
        $location = !empty($row['locations']) && $row['locations'] !== null
            ? htmlspecialchars($row['locations'])
            : 'Not assigned';
        
        $equipment_list[] = [
            'id' => (int)$row['id'],
            'code' => $row['code'] ?? 'N/A',
            'name' => $row['name'] ?? 'Unknown',
            'qty' => (int)$row['qty'],
            'image' => $image,
            'description' => $row['description'] ?? '',
            'added_datetime' => $row['added_datetime'],
            'maintenance' => (int)($row['maintenance_pending'] ?? 0),
            'broken' => (int)($row['broken_qty'] ?? 0),
            'usage' => $usage_percentage,
            'total_bookings' => (int)$row['total_bookings'],
            'location' => $location
        ];
    }

    echo json_encode([
        'success' => true,
        'count' => count($equipment_list),
        'equipment' => $equipment_list,
        'analyzed_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
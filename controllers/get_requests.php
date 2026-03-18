<?php
// LRRS/controllers/get_requests.php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
error_reporting(0);
require_once '../config/database.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'technical';

try {
    if ($type === 'technical') {
        // Get pending technical officer approval requests
        $query = "
            SELECT 
                lu.id,
                lu.university_id,
                lu.first_name,
                lu.last_name,
                lu.email,
                lu.mobile,
                lu.img_path,
                lu.join_datetime,
                lu.status
            FROM lab_user lu
            INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
            INNER JOIN role r ON uhr.role_id = r.id
            WHERE r.role = 'technical_officer' 
            AND lu.status = 0
            ORDER BY lu.join_datetime DESC
        ";
    } else {
        // Get pending supervisor approval requests
        $query = "
            SELECT 
                lu.id,
                lu.university_id,
                lu.first_name,
                lu.last_name,
                lu.email,
                lu.mobile,
                lu.img_path,
                lu.join_datetime,
                lu.status
            FROM lab_user lu
            INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
            INNER JOIN role r ON uhr.role_id = r.id
            WHERE r.role = 'supervisor' 
            AND lu.status = 0
            ORDER BY lu.join_datetime DESC
        ";
    }

    $result = Database::search($query);
    
    if (!$result) {
        throw new Exception('Query failed');
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Format image path
        $image_path = '';
        if (!empty($row['img_path'])) {
            // Clean the path and make it web-accessible
            $clean_path = str_replace('\\', '/', $row['img_path']);
            $clean_path = ltrim($clean_path, '/');
          $image_path = '../' . $clean_path;
        }
        
        $data[] = [
            'id' => (int)$row['id'],
            'university_id' => $row['university_id'] ?? '—',
            'first_name' => $row['first_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'full_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
            'email' => $row['email'] ?? '—',
            'mobile' => $row['mobile'] ?? '—',
            'image' => $image_path,
            'join_datetime' => $row['join_datetime'] ?? '—',
            'status' => (int)$row['status']
        ];
    }

    echo json_encode([
        'success' => true, 
        'data' => $data, 
        'count' => count($data)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(), 
        'data' => [], 
        'count' => 0
    ]);
}
?>
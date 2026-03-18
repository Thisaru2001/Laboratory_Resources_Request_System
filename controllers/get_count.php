<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $type = $_GET['type'] ?? '';
    
    if (!in_array($type, ['technical', 'supervisor'])) {
        throw new Exception('Invalid request type');
    }
    
    // Map the type to role
    $role = ($type === 'technical') ? 'technical_officer' : 'supervisor';
    
    // Count pending approval requests (status = 0 means pending approval)
    $query = "
        SELECT COUNT(DISTINCT lu.id) as count
        FROM lab_user lu
        INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
        INNER JOIN role r ON uhr.role_id = r.id
        WHERE r.role = ? 
        AND lu.status = 0
    ";
    
    $result = Database::search($query, "s", [$role]);
    
    if (!$result) {
        throw new Exception('Database query failed');
    }
    
    $row = $result->fetch_assoc();
    $count = (int)($row['count'] ?? 0);
    
    echo json_encode([
        'success' => true,
        'count' => $count,
        'type' => $type
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
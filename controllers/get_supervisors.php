<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is HOD
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'hod') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get all supervisors - FIXED: use correct table names and status field
$query = "
    SELECT 
        lu.id,
        lu.first_name,
        lu.last_name,
        lu.email,
        lu.university_id
    FROM lab_user lu
    INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
    INNER JOIN role r ON uhr.role_id = r.id
    WHERE r.role = 'supervisor' AND lu.status = 1
    ORDER BY lu.first_name, lu.last_name
";

$result = Database::search($query);

$supervisors = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $supervisors[] = $row;
    }
}

echo json_encode(['success' => true, 'supervisors' => $supervisors]);
?>
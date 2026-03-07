<?php
session_start();
require_once "../config/database.php";

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

$response = [
    'success' => false, 
    'message' => '', 
    'students' => ['total' => 0, 'active' => 0, 'inactive' => 0],
    'supervisors' => ['total' => 0, 'active' => 0, 'inactive' => 0],
    'technical' => ['total' => 0, 'active' => 0, 'inactive' => 0]
];

// Check if user is logged in as HOD
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'HOD') {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

try {
    // Get counts for STUDENTS
    $student_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN lu.status_user = 1 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN lu.status_user = 0 THEN 1 ELSE 0 END) as inactive
                   FROM lab_user lu
                   INNER JOIN user_has_role uhr ON lu.user_id = uhr.user_id
                   INNER JOIN role r ON uhr.role_id = r.role_id
                   WHERE r.role = 'Student' 
                   AND lu.request_status_id = 5 
                   AND lu.approved_datetime IS NOT NULL";
    
    $student_result = Database::search($student_query);
    if ($student_result && $student_result->num_rows > 0) {
        $row = $student_result->fetch_assoc();
        $response['students']['total'] = (int)$row['total'];
        $response['students']['active'] = (int)$row['active'];
        $response['students']['inactive'] = (int)$row['inactive'];
    }
    
    // Get counts for SUPERVISORS
    $supervisor_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN lu.status_user = 1 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN lu.status_user = 0 THEN 1 ELSE 0 END) as inactive
                   FROM lab_user lu
                   INNER JOIN user_has_role uhr ON lu.user_id = uhr.user_id
                   INNER JOIN role r ON uhr.role_id = r.role_id
                   WHERE r.role = 'Supervisor' 
                   AND lu.request_status_id = 5 
                   AND lu.approved_datetime IS NOT NULL";
    
    $supervisor_result = Database::search($supervisor_query);
    if ($supervisor_result && $supervisor_result->num_rows > 0) {
        $row = $supervisor_result->fetch_assoc();
        $response['supervisors']['total'] = (int)$row['total'];
        $response['supervisors']['active'] = (int)$row['active'];
        $response['supervisors']['inactive'] = (int)$row['inactive'];
    }
    
    // Get counts for TECHNICAL OFFICERS
    $technical_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN lu.status_user = 1 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN lu.status_user = 0 THEN 1 ELSE 0 END) as inactive
                   FROM lab_user lu
                   INNER JOIN user_has_role uhr ON lu.user_id = uhr.user_id
                   INNER JOIN role r ON uhr.role_id = r.role_id
                   WHERE r.role = 'Technical Officer' 
                   AND lu.request_status_id = 5 
                   AND lu.approved_datetime IS NOT NULL";
    
    $technical_result = Database::search($technical_query);
    if ($technical_result && $technical_result->num_rows > 0) {
        $row = $technical_result->fetch_assoc();
        $response['technical']['total'] = (int)$row['total'];
        $response['technical']['active'] = (int)$row['active'];
        $response['technical']['inactive'] = (int)$row['inactive'];
    }
    
    $response['success'] = true;
    
} catch (Exception $e) {
    error_log("Get User Counts Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred';
}

echo json_encode($response);
exit;
?>
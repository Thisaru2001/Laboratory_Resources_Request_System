<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in and is HOD
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'hod') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config/database.php';

try {
    // Fetch pending logbooks that HOD hasn't approved/rejected yet
    // Uses your actual schema: practical_finished_logbook + approval tables
    $query = "
        SELECT 
            pfl.id,
            pfl.reservation_id,
            pfl.any_comment,
            pfl.img_path1,
            pfl.img_path2,
            pfl.img_path3,
            pfl.img_path4,

            -- Student info
            CONCAT(student.first_name, ' ', student.last_name) AS student_name,
            student.university_id,

            -- Reservation info
            r.reservation_id AS reservation_code,
            r.request_date AS submitted_date,

            -- HOD approval status
            COALESCE(hod.is_approved, NULL) AS hod_is_approved,
            hod.status AS hod_status,

            -- Tech officer approval status  
            COALESCE(tech.is_approved, NULL) AS tech_is_approved,

            -- Supervisor approval status
            COALESCE(sup.is_approved, NULL) AS supervisor_is_approved,

            -- Count how many images were uploaded
            (
                CASE WHEN pfl.img_path1 IS NOT NULL AND pfl.img_path1 != '' THEN 1 ELSE 0 END +
                CASE WHEN pfl.img_path2 IS NOT NULL AND pfl.img_path2 != '' THEN 1 ELSE 0 END +
                CASE WHEN pfl.img_path3 IS NOT NULL AND pfl.img_path3 != '' THEN 1 ELSE 0 END +
                CASE WHEN pfl.img_path4 IS NOT NULL AND pfl.img_path4 != '' THEN 1 ELSE 0 END
            ) AS has_photos

        FROM practical_finished_logbook pfl

        -- Join student details
        LEFT JOIN lab_user student ON pfl.student_id = student.id

        -- Join reservation details
        LEFT JOIN reservation r ON pfl.reservation_id = r.id

        -- Join HOD approval table
        LEFT JOIN practical_finished_hod_notify_and_approval hod 
            ON pfl.id = hod.practical_finished_logbook_id

        -- Join tech officer approval table
        LEFT JOIN practical_finished_technicalofficer_notify_and_approval tech 
            ON pfl.id = tech.practical_finished_logbook_id

        -- Join supervisor approval table
        LEFT JOIN practical_finished_supervisor_notify_and_approval sup 
            ON pfl.id = sup.practical_finished_logbook_id

        WHERE 
            -- Only show logbooks not yet decided by HOD (pending)
            (hod.is_approved IS NOT NULL OR hod.id IS NOT NULL)

        ORDER BY r.request_date DESC
    ";

    error_log("Starting logbook query for HOD: " . $_SESSION['user_id']);
    
    $result = Database::search($query);

    if (!$result) {
        error_log("Database::search returned false - Query may have syntax error");
        error_log("Last error: " . Database::getLastError());
        echo json_encode(['error' => 'Query failed', 'details' => Database::getLastError()]);
        exit;
    }

    $logbooks = [];
    while ($row = $result->fetch_assoc()) {
        $logbooks[] = $row;
    }
    
    error_log("Logbooks fetched: " . count($logbooks) . " records");
    error_log("Logbooks data: " . json_encode($logbooks));
    
    // Return wrapped response
    echo json_encode([
        'success' => true,
        'logbooks' => $logbooks,
        'count' => count($logbooks)
    ]);
    

} catch (Exception $e) {
    error_log("Error in get_logbooks_for_hod.php: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error fetching logbooks: ' . $e->getMessage()
    ]);
}
?>
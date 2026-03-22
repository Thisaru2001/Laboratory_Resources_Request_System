<?php
/**
 * Fetch full logbook details for supervisor review modal
 */
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is supervisor
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'supervisor') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$logbook_id = $_GET['id'] ?? 0;

if (!$logbook_id) {
    echo json_encode(['error' => 'Invalid logbook ID']);
    exit;
}

// Fetch complete logbook details with student, supervisor, and reservation information
$query = "SELECT 
    l.id,
    l.reservation_id,
    l.student_id,
    l.any_comment,
    l.supervisor_id,
    l.who_technicalOfficer_id,
    l.img_path1,
    l.img_path2,
    l.img_path3,
    l.img_path4,
    u.first_name,
    u.last_name,
    u.university_id,
    u.email as student_email,
    u.mobile as student_mobile,
    sup.first_name as sup_first_name,
    sup.last_name as sup_last_name,
    sup.email as sup_email,
    r.reservation_id as reservation_code,
    r.request_date,
    r.continue_days,
    loc.location as lab_location,
    loc.is_room,
    sup_notify.is_approved,
    sup_notify.status,
    sup_notify.rejection_reason,
    sup_notify.approved_or_rejected_datetime  
FROM practical_finished_logbook l
INNER JOIN lab_user u ON l.student_id = u.id
INNER JOIN reservation r ON l.reservation_id = r.id
LEFT JOIN lab_user sup ON l.supervisor_id = sup.id
LEFT JOIN location loc ON r.location_id = loc.id
LEFT JOIN practical_finished_supervisor_notify_and_approval sup_notify 
    ON l.id = sup_notify.practical_finished_logbook_id
WHERE l.id  = ?";

$result = Database::search($query, 'i', [$logbook_id]);

if (!$result) {
    error_log('Database error in get_logbook_details_supervisor.php: ' . Database::getLastError());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . Database::getLastError()]);
    exit;
}

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Collect images
    $images = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($row["img_path$i"])) {
            $images[] = $row["img_path$i"];
        }
    }
    
    $logbookDetails = [
        'success' => true,
        'id' => $row['id'],
        'student_name' => trim($row['first_name'] . ' ' . $row['last_name']),
        'university_id' => $row['university_id'],
        'student_email' => $row['student_email'] ?? 'N/A',
        'student_mobile' => $row['student_mobile'] ?? 'N/A',
        'supervisor_name' => !empty($row['sup_first_name']) ? trim($row['sup_first_name'] . ' ' . $row['sup_last_name']) : 'Not assigned',
        'supervisor_email' => $row['sup_email'] ?? 'N/A',
        'reservation_code' => $row['reservation_code'],
        'request_date' => $row['request_date'],
        'duration' => ($row['continue_days'] ?? 0) . ' days',
        'location' => $row['lab_location'] ?? 'N/A',
        'description' => $row['any_comment'],
        'images' => $images,
        'is_approved' => $row['is_approved'],
        'status' => $row['status'],
        'rejection_reason' => $row['rejection_reason'],
        'approved_or_rejected_datetime' => $row['approved_or_rejected_datetime']
    ];
    
    echo json_encode($logbookDetails);
} else {
    echo json_encode(['success' => false, 'message' => 'Logbook not found']);
}
?>

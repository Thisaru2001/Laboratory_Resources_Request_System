<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is technical officer
if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technical_officer') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$technical_officer_id = $_SESSION['user_id'];

// Fetch pending logbooks for technical officer approval
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
    sup.first_name AS sup_first_name,
    sup.last_name AS sup_last_name,
    r.reservation_id AS reservation_code,
    r.request_date,
    loc.location AS lab_location,
    loc.is_room,
    sup_notify.is_approved AS sup_is_approved,
    sup_notify.status AS sup_status,
    sup_notify.rejection_reason AS sup_rejection_reason,
    sup_notify.approved_or_rejected_datetime AS sup_action_datetime,
    to_notify.status AS to_status,
    to_notify.is_approved AS to_is_approved,
    to_notify.id AS to_notification_id
FROM practical_finished_logbook l
INNER JOIN lab_user u ON l.student_id = u.id
INNER JOIN reservation r ON l.reservation_id = r.id
LEFT JOIN lab_user sup ON l.supervisor_id = sup.id
LEFT JOIN location loc ON r.location_id = loc.id
LEFT JOIN practical_finished_supervisor_notify_and_approval sup_notify
    ON l.id = sup_notify.practical_finished_logbook_id
LEFT JOIN practical_finished_technicalofficer_notify_and_approval to_notify
    ON l.id = to_notify.practical_finished_logbook_id
WHERE sup_notify.is_approved IS NOT NULL
  AND (to_notify.status = 'unread' OR to_notify.status IS NULL)
ORDER BY l.id DESC";

$result = Database::search($query, '');

$logbooks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Count photos
        $photoCount = 0;
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($row["img_path$i"])) $photoCount++;
        }
        
        $logbooks[] = [
            'id' => $row['id'],
            'student_name' => trim($row['first_name'] . ' ' . $row['last_name']),
            'university_id' => $row['university_id'],
            'supervisor_name' => !empty($row['sup_first_name']) ? trim($row['sup_first_name'] . ' ' . $row['sup_last_name']) : 'Not assigned',
            'reservation_code' => $row['reservation_code'],
            'submitted_date' => $row['request_date'],
            'any_comment' => $row['any_comment'],
            'has_photos' => $photoCount,
            'status' => $row['status'] ?? 'unread',
            'is_approved' => $row['is_approved'] ?? 0
        ];
    }
}

echo json_encode($logbooks);
?>
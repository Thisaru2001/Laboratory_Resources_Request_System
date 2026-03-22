<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

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
    sup_user.first_name AS sup_first_name,
    sup_user.last_name  AS sup_last_name,
    to_user.first_name  AS to_first_name,
    to_user.last_name   AS to_last_name,
    r.reservation_id    AS reservation_code,
    r.request_date,
    h.status,
    h.is_approved,
    h.id AS notification_id,
    sup_approval.is_approved  AS sup_is_approved,
    tech_approval.is_approved AS tech_is_approved
FROM practical_finished_logbook l
INNER JOIN lab_user u       ON l.student_id = u.id
INNER JOIN reservation r    ON l.reservation_id = r.id
LEFT JOIN lab_user sup_user ON l.supervisor_id = sup_user.id
LEFT JOIN lab_user to_user  ON l.who_technicalOfficer_id = to_user.id
LEFT JOIN practical_finished_supervisor_notify_and_approval sup_approval
    ON l.id = sup_approval.practical_finished_logbook_id
LEFT JOIN practical_finished_technicalofficer_notify_and_approval tech_approval
    ON l.id = tech_approval.practical_finished_logbook_id
LEFT JOIN practical_finished_hod_notify_and_approval h
    ON l.id = h.practical_finished_logbook_id
WHERE (h.is_approved IS NULL)
ORDER BY l.id DESC";

$result = Database::search($query, '');

$logbooks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // Count submitted photos
        $photoCount = 0;
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($row["img_path$i"])) $photoCount++;
        }

        // Determine approval values:
        // sup_is_approved / tech_is_approved from DB:
        //   NULL = no record yet OR row exists but not acted (pending)
        //   1    = approved
        //   0    = rejected
        // We convert to: 1=approved, 0=rejected/pending/no-record
        // so JS can do:  logbook.supervisor_approved == 1
   $supApproved  = $row['sup_is_approved']  === null ? null : (int)$row['sup_is_approved'];
$techApproved = $row['tech_is_approved'] === null ? null : (int)$row['tech_is_approved'];

        $logbooks[] = [
            'id'                         => (int)$row['id'],
            'student_name'               => trim($row['first_name'] . ' ' . $row['last_name']),
            'university_id'              => $row['university_id'],
            'supervisor_name'            => !empty($row['sup_first_name'])
                                                ? trim($row['sup_first_name'] . ' ' . $row['sup_last_name'])
                                                : 'Not assigned',
            'technical_officer_name'     => !empty($row['to_first_name'])
                                                ? trim($row['to_first_name'] . ' ' . $row['to_last_name'])
                                                : 'Not checked yet',
            'reservation_code'           => $row['reservation_code'],
            'submitted_date'             => $row['request_date'],
            'any_comment'                => $row['any_comment'],
            'has_photos'                 => $photoCount,
            'status'                     => $row['status'] ?? 'unread',
            'is_approved'                => $row['is_approved'],
            'supervisor_approved'        => $supApproved,   // 1 or 0
            'technical_officer_approved' => $techApproved,  // 1 or 0
        ];
    }
}

echo json_encode($logbooks);
?>
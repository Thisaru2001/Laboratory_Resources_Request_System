<?php
// LRRS/controllers/get_requests.php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
error_reporting(0);
require_once '../config/database.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'technical';

if ($type === 'technical') {
    // Pending reservations — join to get the Technical Officer's university_id
    $query = "
        SELECT
            r.reservation_id,
            r.reservation_id_generate,
            r.request_date,
            r.comment,
            r.any_comment,
            lu_to.university_id  AS officer_university_id,
            lu_to.first_name     AS officer_first_name,
            lu_to.last_name      AS officer_last_name,
            lu_st.university_id  AS student_university_id,
            ll.location          AS lab_location
        FROM reservation r
        LEFT JOIN lab_user lu_to ON r.technical_officer_id = lu_to.user_id
        LEFT JOIN lab_user lu_st ON r.student_id           = lu_st.user_id
        LEFT JOIN lab_location ll ON r.lab_location_id     = ll.lab_location_id
        WHERE r.is_equipment_ready            = 0
          AND r.is_rejected_by_technical_officer = 0
        ORDER BY r.request_date DESC
    ";
} else {
    // Supervisor requests — same pending reservations, joined to Supervisor role users
    $query = "
        SELECT
            r.reservation_id,
            r.reservation_id_generate,
            r.request_date,
            r.comment,
            r.any_comment,
            lu_sup.university_id AS officer_university_id,
            lu_sup.first_name    AS officer_first_name,
            lu_sup.last_name     AS officer_last_name,
            lu_st.university_id  AS student_university_id,
            ll.location          AS lab_location
        FROM reservation r
        INNER JOIN lab_user lu_st ON r.student_id = lu_st.user_id
        LEFT JOIN lab_location ll ON r.lab_location_id = ll.lab_location_id
        LEFT JOIN (
            SELECT lu.user_id, lu.university_id, lu.first_name, lu.last_name
            FROM lab_user lu
            INNER JOIN user_has_role uhr ON lu.user_id  = uhr.user_id
            INNER JOIN role ro           ON uhr.role_id = ro.role_id
            WHERE ro.role = 'Supervisor'
        ) lu_sup ON lu_sup.user_id = r.technical_officer_id
        WHERE r.is_equipment_ready               = 0
          AND r.is_rejected_by_technical_officer = 0
        ORDER BY r.request_date DESC
    ";
}

$result = Database::search($query);
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed', 'data' => [], 'count' => 0]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id'           => (int)$row['reservation_id'],
        'display_id'   => $row['reservation_id_generate'] ?? '—',
        'date'         => $row['request_date']            ?? '—',
        'officer_id'   => $row['officer_university_id']   ?? '—',
        'officer_name' => trim(($row['officer_first_name'] ?? '') . ' ' . ($row['officer_last_name'] ?? '')),
        'student_id'   => $row['student_university_id']   ?? '—',
        'lab'          => $row['lab_location']            ?? '—',
        'comment'      => $row['comment']                 ?? '',
        'any_comment'  => $row['any_comment']             ?? '',
    ];
}

echo json_encode(['success' => true, 'data' => $data, 'count' => count($data)]);

<?php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
error_reporting(0);
require_once '../config/database.php';

$query = "
    SELECT 
        r.reservation_id,
        r.reservation_id_generate,
        r.is_equipment_ready,
        r.is_rejected_by_technical_officer,
        r.request_date,
        ll.location AS lab_location,
        lu.university_id AS student_university_id
    FROM reservation r
    LEFT JOIN lab_location ll ON r.lab_location_id = ll.lab_location_id
    LEFT JOIN lab_user lu ON r.student_id = lu.user_id
    ORDER BY r.request_date DESC
";

$result = Database::search($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed']);
    exit;
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $status = 'Pending';
    if ((int)$row['is_equipment_ready'] === 1) {
        $status = 'Ready';
    } elseif ((int)$row['is_rejected_by_technical_officer'] === 1) {
        $status = 'Rejected';
    }

    $data[] = [
        'id'           => $row['reservation_id'],
        'display_id'   => $row['reservation_id_generate'],
        'lab_location' => $row['lab_location'] ?? '—',
        'student_id'   => $row['student_university_id'] ?? '—',
        'status'       => $status,
        'date'         => $row['request_date'] ?? '—',
    ];
}

echo json_encode([
    'success' => true,
    'data'    => $data,
    'count'   => count($data),
]);
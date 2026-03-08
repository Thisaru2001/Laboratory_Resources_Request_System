<?php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
error_reporting(0);
require_once '../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID']);
    exit;
}

$query = "
    SELECT
        r.reservation_id,
        r.reservation_id_generate,
        r.is_equipment_ready,
        r.is_rejected_by_technical_officer,
        r.request_date,
        r.comment,
        r.any_comment,
        r.rejected_reason,
        ll.location      AS lab_location,
        lu.university_id AS student_university_id
    FROM reservation r
    LEFT JOIN lab_location ll ON r.lab_location_id = ll.lab_location_id
    LEFT JOIN lab_user lu     ON r.student_id      = lu.user_id
    WHERE r.reservation_id = ?
    LIMIT 1
";

$result = Database::search($query, 'i', [$id]);

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit;
}

$row = $result->fetch_assoc();

$status = 'Pending';
if ((int)$row['is_equipment_ready'] === 1) {
    $status = 'Ready';
} elseif ((int)$row['is_rejected_by_technical_officer'] === 1) {
    $status = 'Rejected';
}

echo json_encode([
    'success'         => true,
    'id'              => $row['reservation_id_generate'],
    'lab_location'    => $row['lab_location']          ?? '—',
    'student_id'      => $row['student_university_id'] ?? '—',
    'supervisor_id'   => '—',
    'status'          => $status,
    'date'            => $row['request_date']          ?? '—',
    'comment'         => $row['comment']               ?? '',
    'any_comment'     => $row['any_comment']           ?? '',
    'is_rejected'     => (int)$row['is_rejected_by_technical_officer'] === 1,
    'rejected_reason' => $row['rejected_reason']       ?? '',
]);
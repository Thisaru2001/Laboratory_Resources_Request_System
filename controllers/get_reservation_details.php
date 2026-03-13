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
        r.id,
        r.reservation_id,
        r.request_date,
        r.comment,
        l.location AS lab_location,
        student.university_id AS student_university_id,
        student.first_name,
        student.last_name,
        supervisor.university_id AS supervisor_university_id,
        CASE 
            WHEN rr.id IS NOT NULL THEN 'rejected'
            WHEN r.technical_officer_id IS NOT NULL THEN 'ready'
            WHEN r.supervisor_id IS NOT NULL THEN 'to_pending'
            ELSE 'pending'
        END as status,
        rr.reason as reject_reason
    FROM reservation r
    LEFT JOIN location l ON r.location_id = l.id
    LEFT JOIN lab_user student ON r.student_id = student.id
    LEFT JOIN supervisor_assigned_student sas ON sas.student_id = r.student_id
    LEFT JOIN lab_user supervisor ON sas.supervisor_id_or_hod_id = supervisor.id
    LEFT JOIN reject_reason rr ON r.id = rr.reservation_id
    WHERE r.id = ?
";

$result = Database::search($query, 'i', [$id]);

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit;
}

$row = $result->fetch_assoc();

// Determine status for display
$display_status = 'Pending';
if ($row['status'] === 'rejected') {
    $display_status = 'Rejected';
} elseif ($row['status'] === 'ready') {
    $display_status = 'Ready';
} elseif ($row['status'] === 'to_pending') {
    $display_status = 'To Pending';
}

// Format student name
$student_name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
if (empty($student_name)) {
    $student_name = 'Unknown';
}

echo json_encode([
    'success'         => true,
    'id'              => $row['reservation_id'],
    'lab_location'    => $row['lab_location'] ?? '—',
    'student_id'      => $row['student_university_id'] ?? '—',
    'student_name'    => $student_name,
    'supervisor_id'   => $row['supervisor_university_id'] ?? '—',
    'status'          => $display_status,
    'raw_status'      => $row['status'],
    'date'            => $row['request_date'] ?? '—',
    'comment'         => $row['comment'] ?? '',
    'is_rejected'     => $row['status'] === 'rejected',
    'rejected_reason' => $row['reject_reason'] ?? ''
]);
?>
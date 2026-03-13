<?php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
error_reporting(0);
require_once '../config/database.php';

$query = "
    SELECT 
        r.id,
        r.reservation_id,
        r.request_date,
        r.comment,
        r.student_id,
        l.location AS lab_location,
        lu.university_id AS student_university_id,
        lu.first_name,
        lu.last_name,
        CASE 
            WHEN rr.id IS NOT NULL THEN 'rejected'
            WHEN r.technical_officer_id IS NOT NULL THEN 'ready'
            WHEN r.supervisor_id IS NOT NULL THEN 'to_pending'
            ELSE 'pending'
        END as status,
        rr.reason as reject_reason
    FROM reservation r
    LEFT JOIN location l ON r.location_id = l.id
    LEFT JOIN lab_user lu ON r.student_id = lu.id
    LEFT JOIN reject_reason rr ON r.id = rr.reservation_id
    ORDER BY r.request_date DESC
";

$result = Database::search($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . Database::getLastError()]);
    exit;
}

$data = [];

while ($row = $result->fetch_assoc()) {
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

    $data[] = [
        'id'             => $row['id'],
        'display_id'     => $row['reservation_id'] ?? 'N/A',
        'lab_location'   => $row['lab_location'] ?? '—',
        'student_id'     => $row['student_university_id'] ?? '—',
        'student_name'   => $student_name,
        'status'         => $display_status,
        'raw_status'     => $row['status'],
        'date'           => $row['request_date'] ?? '—',
        'comment'        => $row['comment'] ?? '',
        'reject_reason'  => $row['reject_reason'] ?? null
    ];
}

echo json_encode([
    'success' => true,
    'data'    => $data,
    'count'   => count($data)
]);
?>
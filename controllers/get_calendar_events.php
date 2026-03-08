<?php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
require_once '../config/database.php';

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

$sql = "
    SELECT
        r.reservation_id,
        r.reservation_id_generate,
        r.request_date,
        r.status,
        r.comment,
        lu.university_id,
        lu.first_name,
        lu.last_name,
        ll.location AS lab_location
    FROM reservation r
    INNER JOIN lab_user     lu ON lu.user_id         = r.student_id
    INNER JOIN lab_location ll ON ll.lab_location_id = r.lab_location_id
    WHERE r.is_equipment_ready = 1
      AND MONTH(r.request_date) = $month
      AND YEAR(r.request_date)  = $year
    ORDER BY r.request_date ASC, r.reservation_id ASC
";

$result = Database::search($sql);

$events = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $day = $row['request_date']; // YYYY-MM-DD
        if (!isset($events[$day])) $events[$day] = [];
        $events[$day][] = [
            "id"            => $row['reservation_id_generate'] ?? $row['reservation_id'],
            "university_id" => $row['university_id'],
            "student_name"  => trim($row['first_name'] . ' ' . $row['last_name']),
            "lab_location"  => $row['lab_location'],
            "status"        => $row['status'],
            "comment"       => $row['comment'] ?? ''
        ];
    }
}

echo json_encode([
    "success" => true,
    "month"   => $month,
    "year"    => $year,
    "events"  => $events
]);
?>

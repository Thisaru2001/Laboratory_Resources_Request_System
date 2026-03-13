<?php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
require_once '../config/database.php';

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

// FIXED: Use correct table and column names
$sql = "
    SELECT
        r.id,
        r.reservation_id,
        r.request_date,
        r.continue_days,
        r.comment,
        lu.university_id,
        lu.first_name,
        lu.last_name,
        l.location AS lab_location,
        CASE 
            WHEN rr.id IS NOT NULL THEN 'Rejected'
            WHEN r.technical_officer_id IS NOT NULL THEN 'Ready'
            WHEN r.supervisor_id IS NOT NULL THEN 'To Pending'
            ELSE 'Pending'
        END as status
    FROM reservation r
    INNER JOIN lab_user lu ON lu.id = r.student_id
    INNER JOIN location l ON l.id = r.location_id
    LEFT JOIN reject_reason rr ON rr.reservation_id = r.id
    WHERE MONTH(r.request_date) = ?
      AND YEAR(r.request_date) = ?
    ORDER BY r.request_date ASC, r.reservation_id ASC
";

$result = Database::search($sql, "ii", [$month, $year]);

$events = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['request_date']; // YYYY-MM-DD
        
        // Handle multi-day reservations
        $start_date = new DateTime($row['request_date']);
        $end_date = clone $start_date;
        $end_date->modify('+' . ($row['continue_days'] - 1) . ' days');
        
        $current_date = clone $start_date;
        while ($current_date <= $end_date) {
            $day = $current_date->format('Y-m-d');
            
            if (!isset($events[$day])) {
                $events[$day] = [];
            }
            
            // Only add once per reservation (for the first day)
            if ($current_date == $start_date) {
                $events[$day][] = [
                    "id"            => $row['id'],
                    "display_id"    => $row['reservation_id'],
                    "university_id" => $row['university_id'],
                    "student_name"  => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                    "lab_location"  => $row['lab_location'],
                    "status"        => $row['status'],
                    "duration"      => $row['continue_days'] . ' day(s)',
                    "comment"       => $row['comment'] ?? ''
                ];
            }
            
            $current_date->modify('+1 day');
        }
    }
}

echo json_encode([
    "success" => true,
    "month"   => $month,
    "year"    => $year,
    "events"  => $events
]);
?>
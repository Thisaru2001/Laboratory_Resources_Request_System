<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo '<tr><td colspan="5" class="text-center text-danger">Unauthorized</td></tr>';
    exit();
}

$student_id = $_SESSION["user_id"];
$filter = $_GET['filter'] ?? 'all';

// Build date filter
$date_condition = "";
switch ($filter) {
    case 'weekly':
        $date_condition = "AND r.created_datetime >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case 'monthly':
        $date_condition = "AND r.created_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
    default:
        $date_condition = "";
}

// FIXED: Use ANY_VALUE() or include all non-aggregated columns in GROUP BY
$query = "SELECT r.id, 
       r.reservation_id, 
       r.created_datetime, 
       l.location, 
       r.request_date, 
       r.continue_days,
       CASE 
           WHEN rr.id IS NOT NULL THEN 'rejected'
           WHEN r.supervisor_id IS NOT NULL AND r.technical_officer_id IS NOT NULL THEN 'approved'
           ELSE 'pending'
       END as status,
       GROUP_CONCAT(CONCAT(e.name, ' (x', be.book_qty, ')') SEPARATOR '<br>') as equipment_list
FROM reservation r
JOIN location l ON r.location_id = l.id
JOIN book_equipment be ON r.id = be.reservation_id
JOIN equipment e ON be.equipment_id = e.id
LEFT JOIN reject_reason rr ON r.id = rr.reservation_id
WHERE r.student_id = ? 
  AND r.request_date < CURDATE()  -- Only past reservations (before today)
GROUP BY r.id, r.reservation_id, r.created_datetime, l.location, 
         r.request_date, r.continue_days, r.supervisor_id, 
         r.technical_officer_id, rr.id
ORDER BY r.created_datetime DESC";

$result = Database::search($query, "i", [$student_id]);

if (!$result || $result->num_rows === 0) {
    echo '<tr><td colspan="5" class="text-center text-muted py-4">No reservation history found</td></tr>';
    exit();
}

while ($row = $result->fetch_assoc()) {
    // Determine status badge
    $status_badge = '';
    
    if ($row['status'] === 'approved') {
        $status_badge = '<span class="badge bg-success">Completed</span>';
    } elseif ($row['status'] === 'rejected') {
        $status_badge = '<span class="badge bg-danger">Rejected</span>';
    } else {
        $status_badge = '<span class="badge bg-warning">Contact HODs</span>';
    }
    
    // Format date with duration
    $date_time = date('Y-m-d h:i A', strtotime($row['created_datetime']));
    $end_date = date('Y-m-d', strtotime($row['request_date'] . ' + ' . ($row['continue_days'] - 1) . ' days'));
    $date_range = $row['request_date'];
    if ($row['continue_days'] > 1) {
        $date_range .= " to " . $end_date;
    }
    
    echo '<tr>';
    echo '<td data-label="Reservation ID"><strong>' . htmlspecialchars($row['reservation_id']) . '</strong></td>';
   echo '<td data-label="Date & Time">' . $date_time . '<br><small>(' . $row['continue_days'] . ' day(s))</small></td>';
    echo '<td data-label="Location">' . htmlspecialchars($row['location']) . '</td>';
    echo '<td data-label="Status">' . $status_badge . '</td>';
    echo '<td data-label="Action">';
    echo '<button class="btn-view" onclick="viewReservationDetails(\'' . $row['id'] . '\')" title="View Details">';
    echo '<i class="bi bi-eye"></i> View';
    echo '</button>';
    echo '</td>';
    echo '</tr>';
}
?>
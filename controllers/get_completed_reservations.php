<?php
// LRRS/controllers/get_completed_reservations.php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
require_once '../config/database.php';

$sql = "
    SELECT 
        DATE_FORMAT(request_date, '%b %Y') AS month_label,
        DATE_FORMAT(request_date, '%Y-%m') AS month_sort,
        COUNT(*) AS total
    FROM reservation
    WHERE status = 'Approved'
      AND request_date < CURDATE()
    GROUP BY month_sort, month_label
    ORDER BY month_sort ASC
";

$result = Database::search($sql);

$labels = [];
$data   = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['month_label'];
        $data[]   = (int)$row['total'];
    }
}

$avg = count($data) > 0 ? round(array_sum($data) / count($data), 1) : 0;

echo json_encode([
    "success" => true,
    "labels"  => $labels,
    "data"    => $data,
    "average" => $avg,
    "updated" => date('d M Y, h:i A')
]);
?>

<?php
// LRRS/controllers/get_session_stats.php
header('Content-Type: application/json');
require_once '../config/database.php';
date_default_timezone_set('Asia/Colombo'); // UTC+5:30

// Get monthly session counts
$sql = "
    SELECT 
        DATE_FORMAT(created_at, '%b %Y') AS month_label,
        DATE_FORMAT(created_at, '%Y-%m') AS month_sort,
        COUNT(*) AS session_count
    FROM user_sessions
    GROUP BY month_sort, month_label
    ORDER BY month_sort ASC
";

$result = Database::search($sql);

$labels = [];
$data   = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['month_label'];
        $data[]   = (int)$row['session_count'];
    }
}

// Calculate average
$avg = count($data) > 0 ? round(array_sum($data) / count($data), 1) : 0;

echo json_encode([
    "success"  => true,
    "labels"   => $labels,
    "data"     => $data,
    "average"  => $avg,
    "updated"  => date('d M Y, h:i A')   // e.g. "08 Mar 2026, 02:30 PM"
]);
?>

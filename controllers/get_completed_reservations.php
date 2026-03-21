<?php
// LRRS/controllers/get_completed_reservations.php
date_default_timezone_set('Asia/Colombo');
header('Content-Type: application/json');
require_once '../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log that the script was called
error_log("get_completed_reservations.php called - using practical_finished_logbook table");

// Check if there are any completed practicals in the logbook (approved by HOD)
$check_query = "
    SELECT COUNT(*) as total 
    FROM practical_finished_logbook p
    INNER JOIN practical_finished_hod_notify_and_approval h ON p.id = h.practical_finished_logbook_id
    WHERE h.is_approved = 1
";

$check_result = Database::search($check_query, '');
$has_data = false;
$total_count = 0;

if ($check_result && $check_result->num_rows > 0) {
    $row = $check_result->fetch_assoc();
    $total_count = $row['total'];
    $has_data = ($total_count > 0);
    error_log("Found $total_count completed practicals in logbook");
} else {
    error_log("Error checking completed practicals: " . Database::getLastError());
}

// If no real data, return empty data (not sample data)
if (!$has_data) {
    error_log("No completed practicals found, returning empty data");
    
    echo json_encode([
        "success" => true,
        "labels" => [],
        "data" => [],
        "average" => 0,
        "updated" => date('d M Y, h:i A'),
        "source" => "empty"
    ]);
    exit();
}

// Get completed practicals by month from logbook table (last 6 months)
$sql = "
    SELECT 
        DATE_FORMAT(r.request_date, '%b %Y') AS month_label,
        DATE_FORMAT(r.request_date, '%Y-%m') AS month_sort,
        COUNT(*) AS total
    FROM practical_finished_logbook p
    INNER JOIN practical_finished_hod_notify_and_approval h ON p.id = h.practical_finished_logbook_id
    JOIN reservation r ON p.reservation_id = r.id
    WHERE h.is_approved = 1 AND r.request_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_sort, month_label
    ORDER BY month_sort ASC
";

$result = Database::search($sql, '');

$labels = [];
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['month_label'];
        $data[] = (int)$row['total'];
    }
    error_log("Found " . count($data) . " months of completed practical data");
} else {
    error_log("No completed practical data found in last 6 months, checking all data");
    
    // If no data in last 6 months, try to get any data (still real data, not sample)
    $sql = "
        SELECT 
            DATE_FORMAT(r.request_date, '%b %Y') AS month_label,
            DATE_FORMAT(r.request_date, '%Y-%m') AS month_sort,
            COUNT(*) AS total
        FROM practical_finished_logbook p
        INNER JOIN practical_finished_hod_notify_and_approval h ON p.id = h.practical_finished_logbook_id
        JOIN reservation r ON p.reservation_id = r.id
        WHERE h.is_approved = 1
        GROUP BY month_sort, month_label
        ORDER BY month_sort ASC
        LIMIT 6
    ";
    
    $result = Database::search($sql, '');
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['month_label'];
            $data[] = (int)$row['total'];
        }
    }
}

// Calculate average
$avg = count($data) > 0 ? round(array_sum($data) / count($data), 1) : 0;

echo json_encode([
    "success" => true,
    "labels" => $labels,
    "data" => $data,
    "average" => $avg,
    "updated" => date('d M Y, h:i A'),
    "source" => "database"
]);
?>
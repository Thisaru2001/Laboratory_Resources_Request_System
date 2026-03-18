<?php
// LRRS/controllers/get_session_stats.php
header('Content-Type: application/json');
require_once '../config/database.php';
date_default_timezone_set('Asia/Colombo');

// Check if table exists
$check_table = "SHOW TABLES LIKE 'user_session'";
$table_result = Database::search($check_table);
if (!$table_result || $table_result->num_rows === 0) {
    // Return empty data, not sample
    echo json_encode([
        "success" => true,
        "labels" => [],
        "data" => [],
        "average" => 0,
        "updated" => date('d M Y, h:i A')
    ]);
    exit();
}

// Check if there's any data
$count_query = "SELECT COUNT(*) as total FROM user_session";
$count_result = Database::search($count_query);
$total = 0;
if ($count_result && $count_result->num_rows > 0) {
    $total = $count_result->fetch_assoc()['total'];
}

if ($total === 0) {
    // Return empty data, not sample
    echo json_encode([
        "success" => true,
        "labels" => [],
        "data" => [],
        "average" => 0,
        "updated" => date('d M Y, h:i A')
    ]);
    exit();
}

// Get monthly session counts (last 6 months)
$sql = "
    SELECT 
        DATE_FORMAT(created_at, '%b %Y') AS month_label,
        DATE_FORMAT(created_at, '%Y-%m') AS month_sort,
        COUNT(*) AS session_count
    FROM user_session
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month_sort, month_label
    ORDER BY month_sort ASC
";

$result = Database::search($sql);

$labels = [];
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['month_label'];
        $data[] = (int)$row['session_count'];
    }
}

// If no data in last 6 months, get all data
if (empty($data)) {
    $sql = "
        SELECT 
            DATE_FORMAT(created_at, '%b %Y') AS month_label,
            DATE_FORMAT(created_at, '%Y-%m') AS month_sort,
            COUNT(*) AS session_count
        FROM user_session
        GROUP BY month_sort, month_label
        ORDER BY month_sort ASC
        LIMIT 6
    ";
    
    $result = Database::search($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['month_label'];
            $data[] = (int)$row['session_count'];
        }
    }
}

// Calculate average
$avg = count($data) > 0 ? round(array_sum($data) / count($data), 1) : 0;

echo json_encode([
    "success"  => true,
    "labels"   => $labels,
    "data"     => $data,
    "average"  => $avg,
    "updated"  => date('d M Y, h:i A')
]);
?>
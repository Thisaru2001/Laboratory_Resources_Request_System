<?php
session_start();
require_once '../config/database.php';
require_once 'ExcelWriter.php';

// Check if user is logged in and is HOD
if (!isset($_SESSION["user"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'hod') {
    header('HTTP/1.0 403 Forbidden');
    echo 'Access denied';
    exit;
}

// Get all equipment with detailed information including locations
$query = "
    SELECT 
        e.id,
        e.code,
        e.name,
        e.total_qty,
        e.simultaneous_users,
        e.sterilization_required,
        e.reservation_required,
        e.added_datatime as added_datetime,
        e.description,
        e.image_path,
        COALESCE((SELECT SUM(broken_qty) FROM broken WHERE equipment_id = e.id), 0) as broken_qty,
        COALESCE((SELECT SUM(repair_qty) FROM repair WHERE equipment_id = e.id), 0) as repair_qty,
        (SELECT COUNT(DISTINCT reservation_id) FROM book_equipment WHERE equipment_id = e.id) as total_bookings,
        (SELECT COUNT(*) FROM book_equipment be 
         INNER JOIN reservation r ON be.reservation_id = r.id 
         WHERE be.equipment_id = e.id AND r.request_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_bookings,
        GROUP_CONCAT(DISTINCT l.location SEPARATOR ', ') as locations
    FROM equipment e
    LEFT JOIN equipment_has_location ehl ON e.id = ehl.equipment_id
    LEFT JOIN location l ON ehl.location_id = l.id
    GROUP BY e.id
    ORDER BY e.name ASC
";

$result = Database::search($query);

if (!$result) {
    die('Database error: ' . Database::getLastError());
}

// Process the data
$rows = [];
$counter = 1;
$total_units = 0;
$total_available = 0;
$total_broken = 0;
$total_repair = 0;

while ($row = $result->fetch_assoc()) {
    // Calculate available quantity
    $available_qty = $row['total_qty'] - ($row['broken_qty'] + $row['repair_qty']);
    
    // Calculate usage percentage (based on total bookings vs total equipment)
    $usage_percentage = 0;
    if ($row['total_qty'] > 0 && $row['total_bookings'] > 0) {
        // Simple usage calculation - you can adjust this formula based on your needs
        $usage_percentage = min(100, round(($row['total_bookings'] / ($row['total_qty'] * 10)) * 100));
    }
    
    // Format date
    $added_date = !empty($row['added_datetime']) ? date('Y-m-d', strtotime($row['added_datetime'])) : '—';
    
    // Add to rows for Excel
    $rows[] = [
        $counter++,
        $row['code'] ?? '—',
        $row['name'] ?? '—',
        $row['description'] ?? '—',
        $row['total_qty'] ?? 0,
        $available_qty,
        $row['broken_qty'] ?? 0,
        $row['repair_qty'] ?? 0,
        $usage_percentage . '%',
        $row['locations'] ?? 'Not assigned',
        $row['sterilization_required'] ?? 'NO',
        $row['reservation_required'] ?? 'YES',
        $added_date,
        $row['total_bookings'] ?? 0
    ];
    
    // Calculate totals for summary
    $total_units += $row['total_qty'] ?? 0;
    $total_available += $available_qty;
    $total_broken += $row['broken_qty'] ?? 0;
    $total_repair += $row['repair_qty'] ?? 0;
}

// Calculate overall utilization rate
$utilization_rate = 0;
if ($total_units > 0) {
    $utilization_rate = round(($total_available / $total_units) * 100, 1);
}

// Create Excel writer
$filename = 'equipment_inventory_' . date('Y-m-d') . '.xls';
$writer = new ExcelWriter($filename);

// Set title and metadata
$writer->setTitle('Microbiology Laboratory - Complete Equipment Inventory Report');
$writer->setGeneratedBy($_SESSION['user_first_name'] ?? 'HOD');

// Set headers (must match the order of your data array)
$writer->setHeaders([
    '#',
    'Code',
    'Name',
    'Description',
    'Total',
    'Available',
    'Broken',
    'Under Repair',
    'Usage %',
    'Location(s)',
    "Sterilization\nRequired",
    "Reservation\nRequired",
    'Date Added',
    'Total Bookings'
]);

// Set which columns get the quantity color treatment (0-based indices)
// Available = index 5, Broken = index 6, Under Repair = index 7, Usage % = index 8
$writer->setQtyColumns(5, 6, 7, 8);

// Set summary data
$writer->setSummary([
    'total_types'     => count($rows),
    'total_units'     => $total_units,
    'total_available' => $total_available,
    'total_broken'    => $total_broken,
    'total_repair'    => $total_repair,
    'utilization'     => $utilization_rate,
]);

// Add all rows
$writer->addRows($rows);

// Output the Excel file
$writer->output();
?>
<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in - FIXED: use user_id
if (!isset($_SESSION["user_id"])) {
    echo '<tr><td colspan="5" class="text-center text-danger">Unauthorized</td></tr>';
    exit();
}

$student_id = $_SESSION["user_id"];
$search_term = $_GET['term'] ?? '';
$lab_id = $_GET['lab_id'] ?? 'all';

// Build query with proper availability calculation
$query = "SELECT e.id, e.code, e.name, e.total_qty, e.description, 
          l.id as location_id, l.location,
          COALESCE((SELECT SUM(broken_qty) FROM broken WHERE equipment_id = e.id), 0) as broken_qty,
          COALESCE((SELECT SUM(repair_qty) FROM repair WHERE equipment_id = e.id), 0) as repair_qty,
          COALESCE((SELECT SUM(be.book_qty) FROM book_equipment be 
                   JOIN reservation r ON be.reservation_id = r.id 
                   WHERE be.equipment_id = e.id 
                   AND r.request_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                   AND DATE_ADD(r.request_date, INTERVAL (r.continue_days - 1) DAY) >= CURDATE()), 0) as booked_qty
          FROM equipment e
          JOIN location l ON e.location_id = l.id
          WHERE e.is_hod_checked = 1";

$params = [];
$types = "";

if (!empty($search_term)) {
    $query .= " AND (e.name LIKE ? OR e.code LIKE ? OR e.description LIKE ?)";
    $search_term_wildcard = "%$search_term%";
    $params[] = $search_term_wildcard;
    $params[] = $search_term_wildcard;
    $params[] = $search_term_wildcard;
    $types .= "sss";
}

if ($lab_id !== 'all') {
    $query .= " AND e.location_id = ?";
    $params[] = $lab_id;
    $types .= "i";
}

$query .= " ORDER BY e.name LIMIT 50";

$result = Database::search($query, $types, $params);

if (!$result || $result->num_rows === 0) {
    echo '<tr><td colspan="5" class="text-center text-muted py-4">No equipment found matching "' . htmlspecialchars($search_term) . '"</td></tr>';
    exit();
}

while ($row = $result->fetch_assoc()) {
    $available = $row['total_qty'] - $row['broken_qty'] - $row['repair_qty'] - $row['booked_qty'];
    $status_color = $available > 0 ? '#22c55e' : '#dc3545';
    $status_text = $available > 0 ? 'Available' : 'Not Available';
    
    // Default image
    $image_url = 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';
    
    // Highlight search term if present
    $display_name = htmlspecialchars($row['name']);
    $display_code = htmlspecialchars($row['code']);
    if (!empty($search_term)) {
        $display_name = preg_replace('/(' . preg_quote($search_term, '/') . ')/i', '<mark>$1</mark>', $display_name);
        $display_code = preg_replace('/(' . preg_quote($search_term, '/') . ')/i', '<mark>$1</mark>', $display_code);
    }
    
    echo '<tr>';
    echo '<td data-label="Image"><img src="' . $image_url . '" class="equipment-image" alt="' . htmlspecialchars($row['name']) . '" style="width: 40px; height: 40px; object-fit: contain;"></td>';
    echo '<td data-label="Name">' . $display_name . '<br><small class="text-muted">Code: ' . $display_code . '</small></td>';
    echo '<td data-label="Location">' . htmlspecialchars($row['location']) . '</td>';
    echo '<td data-label="Availability">';
    echo '<span class="availability-badge" style="background: ' . $status_color . '20; color: ' . $status_color . '; padding: 4px 8px; border-radius: 4px; font-weight: 500;">';
    echo $available . '/' . $row['total_qty'] . ' ' . $status_text;
    echo '</span>';
    echo '</td>';
    echo '<td data-label="Action">';
    echo '<button class="btn-view" onclick="viewEquipmentDetails(' . $row['id'] . ')" title="View Details">';
    echo '<i class="bi bi-eye"></i> View';
    echo '</button>';
    echo '</td>';
    echo '</tr>';
}
?>
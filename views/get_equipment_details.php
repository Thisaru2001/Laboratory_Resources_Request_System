<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    echo '<div class="text-center text-danger">Unauthorized</div>';
    exit();
}

$equipment_id = $_GET['id'] ?? 0;

if (!$equipment_id) {
    echo '<div class="text-center text-danger">Invalid equipment ID</div>';
    exit();
}

$query = "SELECT e.id, e.code, e.name, e.total_qty, e.description, e.sterilization_required,
                 e.reservation_required, e.added_datatime, l.location,
                 COALESCE((SELECT SUM(broken_qty) FROM broken WHERE equipment_id = e.id), 0) as broken_qty,
                 COALESCE((SELECT SUM(repair_qty) FROM repair WHERE equipment_id = e.id), 0) as repair_qty,
                 COALESCE((SELECT SUM(be.book_qty) FROM book_equipment be 
                          JOIN reservation r ON be.reservation_id = r.id 
                          WHERE be.equipment_id = e.id AND r.request_date >= CURDATE()), 0) as booked_qty
          FROM equipment e
          JOIN location l ON e.location_id = l.id
          WHERE e.id = ?";

$result = Database::search($query, "i", [$equipment_id]);

if ($result->num_rows === 0) {
    echo '<div class="text-center text-danger">Equipment not found</div>';
    exit();
}

$row = $result->fetch_assoc();
$available = $row['total_qty'] - $row['broken_qty'] - $row['repair_qty'] - $row['booked_qty'];
$status_color = $available > 0 ? '#22c55e' : '#dc3545';
$status_text = $available > 0 ? 'Available' : 'Not Available';

// Default image
$image_url = 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';
?>

<div class="row">
    <div class="col-md-4 text-center">
        <img src="<?php echo $image_url; ?>" style="width: 150px; height: 150px; object-fit: contain;" class="mb-3">
        <h4><?php echo htmlspecialchars($row['name']); ?></h4>
        <p><strong>Code:</strong> <?php echo htmlspecialchars($row['code']); ?></p>
    </div>
    <div class="col-md-8">
        <table class="table table-borderless">
            <tr>
                <th style="width: 150px;">Location:</th>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
            </tr>
            <tr>
                <th>Total Quantity:</th>
                <td><?php echo $row['total_qty']; ?></td>
            </tr>
            <tr>
                <th>Available Now:</th>
                <td>
                    <span class="badge" style="background: <?php echo $status_color; ?>; color: white; padding: 5px 10px;">
                        <?php echo $available; ?> units
                    </span>
                </td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <span class="badge" style="background: <?php echo $status_color; ?>20; color: <?php echo $status_color; ?>; padding: 5px 10px;">
                        <?php echo $status_text; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Broken Units:</th>
                <td><?php echo $row['broken_qty']; ?></td>
            </tr>
            <tr>
                <th>In Repair:</th>
                <td><?php echo $row['repair_qty']; ?></td>
            </tr>
            <tr>
                <th>Booked Today+:</th>
                <td><?php echo $row['booked_qty']; ?></td>
            </tr>
            <tr>
                <th>Sterilization Required:</th>
                <td><?php echo $row['sterilization_required'] ?? 'NO'; ?></td>
            </tr>
            <tr>
                <th>Reservation Required:</th>
                <td><?php echo $row['reservation_required'] ?? 'YES'; ?></td>
            </tr>
            <tr>
                <th>Description:</th>
                <td><?php echo nl2br(htmlspecialchars($row['description'] ?? 'No description available')); ?></td>
            </tr>
            <tr>
                <th>Added on:</th>
                <td><?php echo date('Y-m-d', strtotime($row['added_datatime'])); ?></td>
            </tr>
        </table>
    </div>
</div>
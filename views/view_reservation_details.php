<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    echo '<div class="text-center text-danger">Unauthorized</div>';
    exit();
}

$student_id = $_SESSION["user_id"];
$reservation_id = $_GET['id'] ?? '';

if (empty($reservation_id)) {
    echo '<div class="text-center text-danger">Invalid reservation ID</div>';
    exit();
}

$query = "SELECT r.id, r.reservation_id, r.created_datetime, r.request_date, r.continue_days,
                 r.comment, l.location, 
                 CONCAT(s.first_name, ' ', s.last_name) as supervisor_name,
                 CONCAT(t.first_name, ' ', t.last_name) as technical_officer_name,
                 rr.reason as reject_reason
          FROM reservation r
          JOIN location l ON r.location_id = l.id
          LEFT JOIN lab_user s ON r.supervisor_id = s.id
          LEFT JOIN lab_user t ON r.technical_officer_id = t.id
          LEFT JOIN reject_reason rr ON r.id = rr.reservation_id
          WHERE r.reservation_id = ? AND r.student_id = ?";

$result = Database::search($query, "si", [$reservation_id, $student_id]);

if ($result->num_rows === 0) {
    echo '<div class="text-center text-danger">Reservation not found</div>';
    exit();
}

$row = $result->fetch_assoc();

// Get equipment for this reservation
$eq_query = "SELECT e.name, e.code, be.book_qty 
             FROM book_equipment be
             JOIN equipment e ON be.equipment_id = e.id
             WHERE be.reservation_id = ?";
$eq_result = Database::search($eq_query, "i", [$row['id']]);

$equipment_list = [];
while ($eq = $eq_result->fetch_assoc()) {
    $equipment_list[] = $eq['name'] . ' (' . $eq['code'] . ') x' . $eq['book_qty'];
}

// Determine status
$status = 'Pending';
$status_class = 'warning';
if ($row['reject_reason']) {
    $status = 'Rejected';
    $status_class = 'danger';
} elseif ($row['supervisor_name'] && $row['technical_officer_name']) {
    $status = 'Approved';
    $status_class = 'success';
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><?php echo htmlspecialchars($row['reservation_id']); ?></h4>
                <span class="badge bg-<?php echo $status_class; ?> p-2"><?php echo $status; ?></span>
            </div>
            
            <table class="table table-borderless">
                <tr>
                    <th style="width: 150px;">Created Date:</th>
                    <td><?php echo date('Y-m-d h:i A', strtotime($row['created_datetime'])); ?></td>
                </tr>
                <tr>
                    <th>Request Date:</th>
                    <td><?php echo date('Y-m-d', strtotime($row['request_date'])); ?> (<?php echo $row['continue_days']; ?> day(s))</td>
                </tr>
                <tr>
                    <th>Location:</th>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                </tr>
                <tr>
                    <th>Equipment:</th>
                    <td>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($equipment_list as $item): ?>
                                <li><i class="bi bi-dot"></i> <?php echo $item; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>Supervisor:</th>
                    <td><?php echo htmlspecialchars($row['supervisor_name'] ?? 'Not assigned'); ?></td>
                </tr>
                <tr>
                    <th>Technical Officer:</th>
                    <td><?php echo htmlspecialchars($row['technical_officer_name'] ?? 'Not assigned'); ?></td>
                </tr>
                <tr>
                    <th>Comments:</th>
                    <td><?php echo nl2br(htmlspecialchars($row['comment'] ?? 'No comments')); ?></td>
                </tr>
                <?php if ($row['reject_reason']): ?>
                <tr>
                    <th>Rejection Reason:</th>
                    <td class="text-danger"><?php echo htmlspecialchars($row['reject_reason']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
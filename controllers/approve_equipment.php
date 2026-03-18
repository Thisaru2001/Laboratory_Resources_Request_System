<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'hod') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$equipment_id = intval($_POST['equipment_id'] ?? 0);
$action       = $_POST['action'] ?? '';

if ($equipment_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$hod_id = $_SESSION["user_id"];

if ($action === 'approve') {
    // Set is_hod_checked = 1
    $result = Database::iud(
        "UPDATE equipment SET is_hod_checked = 1 WHERE id = ?",
        "i", [$equipment_id]
    );

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Failed to approve equipment']);
        exit();
    }

    // Get equipment name for notification
    $eq = Database::search(
        "SELECT name, code FROM equipment WHERE id = ?",
        "i", [$equipment_id]
    );
    $eq_row  = $eq->fetch_assoc();
    $eq_name = $eq_row['name'] ?? 'Equipment';
    $eq_code = $eq_row['code'] ?? '';

    // Mark the specific notification as read (if notif_id provided)
    $notif_id = intval($_POST['notif_id'] ?? 0);
    if ($notif_id > 0) {
        Database::iud(
            "UPDATE notification SET status = 'read' 
             WHERE id = ? AND owner_of_notification = ?",
            "ii", [$notif_id, $hod_id]
        );
    }
    
    // Also mark other HOD's approval notifications for this equipment as read
    Database::iud(
        "UPDATE notification SET status = 'read' 
         WHERE owner_of_notification = ? 
         AND description LIKE ? 
         AND need_approval = 1",
        "is", [$hod_id, "%($eq_code)%"]
    );

    // Notify all Technical Officers
    $to_query = "SELECT lu.id FROM lab_user lu
                 INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
                 INNER JOIN role r ON uhr.role_id = r.id
                 WHERE r.role = 'technical_officer'";
    $to_result = Database::search($to_query);

    if ($to_result && $to_result->num_rows > 0) {
        $msg = "Equipment '{$eq_name}' (Code: {$eq_code}) has been APPROVED by HOD.";
        while ($to = $to_result->fetch_assoc()) {
            Database::iud(
                "INSERT INTO notification (description, created_datetime, owner_of_notification, status, need_approval)
                 VALUES (?, NOW(), ?, 'unread', 0)",
                "si", [$msg, $to['id']]
            );
        }
    }

    echo json_encode(['success' => true, 'message' => 'Equipment approved']);

} elseif ($action === 'reject') {
    // Get equipment details before deleting
    $eq = Database::search(
        "SELECT name, code FROM equipment WHERE id = ?",
        "i", [$equipment_id]
    );

    if (!$eq || $eq->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Equipment not found']);
        exit();
    }

    $eq_row  = $eq->fetch_assoc();
    $eq_name = $eq_row['name'] ?? 'Equipment';
    $eq_code = $eq_row['code'] ?? '';

    // Mark the specific notification as read (if notif_id provided)
    $notif_id = intval($_POST['notif_id'] ?? 0);
    if ($notif_id > 0) {
        Database::iud(
            "UPDATE notification SET status = 'read' 
             WHERE id = ? AND owner_of_notification = ?",
            "ii", [$notif_id, $hod_id]
        );
    }
    
    // Also mark other HOD's approval notifications as read
    Database::iud(
        "UPDATE notification SET status = 'read' 
         WHERE owner_of_notification = ? 
         AND description LIKE ? 
         AND need_approval = 1",
        "is", [$hod_id, "%($eq_code)%"]
    );

    // Delete equipment
    $delete_result = Database::iud(
        "DELETE FROM equipment WHERE id = ? AND is_hod_checked = 0",
        "i", [$equipment_id]
    );

    if (!$delete_result) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete equipment (may already be approved)']);
        exit();
    }

    // Notify all Technical Officers
    $to_query = "SELECT lu.id FROM lab_user lu
                 INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
                 INNER JOIN role r ON uhr.role_id = r.id
                 WHERE r.role = 'technical_officer'";
    $to_result = Database::search($to_query);

    if ($to_result && $to_result->num_rows > 0) {
        $msg = "Equipment '{$eq_name}' (Code: {$eq_code}) was REJECTED by HOD and has been removed.";
        while ($to = $to_result->fetch_assoc()) {
            Database::iud(
                "INSERT INTO notification (description, created_datetime, owner_of_notification, status, need_approval)
                 VALUES (?, NOW(), ?, 'unread', 0)",
                "si", [$msg, $to['id']]
            );
        }
    }

    echo json_encode(['success' => true, 'message' => 'Equipment rejected and deleted']);
}
?>
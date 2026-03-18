<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

// Fix session check for TO
if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = intval($_POST['id'] ?? 0);
$code = $_POST['code'] ?? '';
$name = $_POST['name'] ?? '';
$qty = intval($_POST['qty'] ?? 0);
$simultaneous_users = intval($_POST['simultaneous_users'] ?? 1);
$sterilization_required = $_POST['sterilization_required'] ?? 'NO';
$reservation_required = $_POST['reservation_required'] ?? 'YES';
$description = $_POST['description'] ?? '';
$broken_qty = intval($_POST['broken_qty'] ?? 0);
$repair_qty = intval($_POST['repair_qty'] ?? 0);

if ($id <= 0 || empty($code) || empty($name) || $qty < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$check_query = "SELECT id FROM equipment WHERE code = ? AND id != ?";
$check_result = Database::search($check_query, "si", [$code, $id]);
if ($check_result && $check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Equipment code already exists']);
    exit();
}

// Handle image upload
$image_path = null;
$update_image = false;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../assets/equipment_images/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        $image_path = 'assets/equipment_images/' . $filename;
        $update_image = true;
    }
}

// Update equipment table
if ($update_image) {
    $update_query = "UPDATE equipment SET code=?, name=?, total_qty=?, simultaneous_users=?,
        sterilization_required=?, reservation_required=?, description=?, image_path=?,
        updated_details_datetime=NOW() WHERE id=?";
    $types = "ssiissssi";
    $params = [$code, $name, $qty, $simultaneous_users, $sterilization_required, $reservation_required, $description, $image_path, $id];
} else {
    $update_query = "UPDATE equipment SET code=?, name=?, total_qty=?, simultaneous_users=?,
        sterilization_required=?, reservation_required=?, description=?,
        updated_details_datetime=NOW() WHERE id=?";
    $types = "ssiisssi";
    $params = [$code, $name, $qty, $simultaneous_users, $sterilization_required, $reservation_required, $description, $id];
}

$result = Database::iud($update_query, $types, $params);

if ($result) {
    // Update broken table
    Database::iud("DELETE FROM broken WHERE equipment_id = ?", "i", [$id]);
    if ($broken_qty > 0) {
        Database::iud("INSERT INTO broken (equipment_id, broken_qty) VALUES (?, ?)", "ii", [$id, $broken_qty]);
    }

    // Update repair table
    Database::iud("DELETE FROM repair WHERE equipment_id = ?", "i", [$id]);
    if ($repair_qty > 0) {
        Database::iud("INSERT INTO repair (equipment_id, repair_qty) VALUES (?, ?)", "ii", [$id, $repair_qty]);
    }

    echo json_encode(['success' => true, 'message' => 'Equipment updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
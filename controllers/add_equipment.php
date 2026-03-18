<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is HOD
if (!isset($_SESSION["user"]) || $_SESSION["user_role"] !== 'hod') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get form data
$code = $_POST['code'] ?? '';
$name = $_POST['name'] ?? '';
$qty = intval($_POST['qty'] ?? 0);
$description = $_POST['description'] ?? '';
$simultaneous_users = intval($_POST['simultaneous_users'] ?? 1);
$sterilization_required = $_POST['sterilization_required'] ?? 'NO';
$reservation_required = $_POST['reservation_required'] ?? 'YES';

// Validate
if (empty($code) || empty($name) || $qty < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Check if equipment code already exists
$check_query = "SELECT id, is_hod_checked FROM equipment WHERE code = ? AND is_hod_checked = 1";
$check_result = Database::search($check_query, "s", [$code]);

if ($check_result && $check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Equipment code already exists']);
    exit();
}

// Handle image upload
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../assets/equipment_images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        $image_path = 'assets/equipment_images/' . $filename;
    }
}

// Insert into database - using your actual column names
$insert_query = "INSERT INTO equipment 
    (code, name, total_qty, simultaneous_users, sterilization_required, 
     reservation_required, added_datatime, description, is_hod_checked, image_path) 
    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 1, ?)";

$types = "ssiissss";
$params = [
    $code, 
    $name, 
    $qty, 
    $simultaneous_users, 
    $sterilization_required, 
    $reservation_required, 
    $description, 
    $image_path
];

$result = Database::iud($insert_query, $types, $params);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Equipment added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
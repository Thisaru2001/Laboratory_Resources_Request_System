<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

// Allow both HOD and Technical Officer
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$allowed_roles = ['hod', 'technical_officer'];
if (!in_array($_SESSION["user_role"], $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized role']);
    exit();
}

$id                     = intval($_POST['id'] ?? 0);
$code                   = trim($_POST['code'] ?? '');
$name                   = trim($_POST['name'] ?? '');
$qty                    = intval($_POST['qty'] ?? 0);
$simultaneous_users     = intval($_POST['simultaneous_users'] ?? 1);
$sterilization_required = $_POST['sterilization_required'] ?? 'NO';
$reservation_required   = $_POST['reservation_required'] ?? 'YES';
$location_id            = intval($_POST['location_id'] ?? 0);  // Changed from lab_location to location_id
$description            = trim($_POST['description'] ?? '');

if ($id <= 0 || empty($code) || empty($name) || $qty < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Validate location if provided
if ($location_id > 0) {
    $location_check = Database::search("SELECT id FROM location WHERE id = ?", "i", [$location_id]);
    if (!$location_check || $location_check->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Selected location does not exist']);
        exit();
    }
}

// Check duplicate code excluding current equipment
$check_result = Database::search(
    "SELECT id FROM equipment WHERE code = ? AND id != ?",
    "si", [$code, $id]
);
if ($check_result && $check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Equipment code already exists']);
    exit();
}

// Handle image upload
$image_path  = null;
$update_image = false;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../assets/equipment_images/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type']);
        exit();
    }
    if ($_FILES['image']['size'] > 6 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image too large (max 6MB)']);
        exit();
    }

    $filename    = uniqid('eq_', true) . '.' . $ext;
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        $image_path   = 'assets/equipment_images/' . $filename;
        $update_image = true;
    }
}

// Start transaction for data integrity


try {
    // Build update query for equipment table
    if ($update_image) {
        $update_query = "UPDATE equipment SET 
            code = ?, name = ?, total_qty = ?, simultaneous_users = ?,
            sterilization_required = ?, reservation_required = ?,
            description = ?, image_path = ?, updated_details_datetime = NOW()
            WHERE id = ?";
        $types  = "ssiissssi";
        $params = [$code, $name, $qty, $simultaneous_users,
                   $sterilization_required, $reservation_required,
                   $description, $image_path, $id];
    } else {
        $update_query = "UPDATE equipment SET 
            code = ?, name = ?, total_qty = ?, simultaneous_users = ?,
            sterilization_required = ?, reservation_required = ?,
            description = ?, updated_details_datetime = NOW()
            WHERE id = ?";
        $types  = "ssiisssi";
        $params = [$code, $name, $qty, $simultaneous_users,
                   $sterilization_required, $reservation_required,
                   $description, $id];
    }

    $result = Database::iud($update_query, $types, $params);
    
    if (!$result) {
        throw new Exception('Failed to update equipment');
    }
    
    // Update location if location_id is provided
    if ($location_id > 0) {
        // Delete existing location associations for this equipment
        Database::iud("DELETE FROM equipment_has_location WHERE equipment_id = ?", "i", [$id]);
        
        // Insert new location association
        $insert_result = Database::iud(
            "INSERT INTO equipment_has_location (equipment_id, location_id) VALUES (?, ?)",
            "ii", [$id, $location_id]
        );
        
        if (!$insert_result) {
            throw new Exception('Failed to update equipment location');
        }
    }
    
    // Commit transaction
  
    
    echo json_encode(['success' => true, 'message' => 'Equipment updated successfully']);
    
} catch (Exception $e) {
    // Rollback on error
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
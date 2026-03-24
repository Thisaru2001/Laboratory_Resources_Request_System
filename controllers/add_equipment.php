<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = $_SESSION["user_role"];
$user_id   = $_SESSION["user_id"];

// HOD → auto approved, TO → needs HOD approval
$is_hod_checked = ($user_role === 'hod') ? 1 : 0;

$code                   = trim($_POST['code'] ?? '');
$name                   = trim($_POST['name'] ?? '');
$qty                    = intval($_POST['qty'] ?? 0);
$description            = trim($_POST['description'] ?? '');
$simultaneous_users     = intval($_POST['simultaneous_users'] ?? 1);
$sterilization_required = $_POST['sterilization_required'] ?? 'NO';
$reservation_required   = $_POST['reservation_required'] ?? 'YES';
$location_id            = intval($_POST['location_id'] ?? 0);  // Changed from lab_location to location_id

if (empty($code) || empty($name) || $qty < 1) {
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

// Check duplicate code among approved equipment
$check_result = Database::search(
    "SELECT id FROM equipment WHERE code = ? AND is_hod_checked = 1",
    "s", [$code]
);
if ($check_result && $check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Equipment code already exists']);
    exit();
}

// Handle image upload
$image_path = null;
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
        $image_path = 'assets/equipment_images/' . $filename;
    }
}

// Start transaction for data integrity


try {
    // Insert equipment
    $result = Database::iud(
        "INSERT INTO equipment 
            (code, name, total_qty, simultaneous_users, sterilization_required,
             reservation_required, added_datatime, description, is_hod_checked, image_path)
         VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
        "ssiisssis",
        [$code, $name, $qty, $simultaneous_users,
         $sterilization_required, $reservation_required,
         $description, $is_hod_checked, $image_path]
    );

    if (!$result) {
        throw new Exception('Database error while saving equipment');
    }

    // Get the new equipment ID
    $new_eq = Database::search("SELECT id FROM equipment WHERE code = ? ORDER BY id DESC LIMIT 1", "s", [$code]);
    if (!$new_eq || $new_eq->num_rows == 0) {
        throw new Exception('Failed to retrieve equipment ID');
    }
    $new_eq_row = $new_eq->fetch_assoc();
    $new_equipment_id = $new_eq_row['id'];

    // Insert location if provided
    if ($location_id > 0) {
        $location_result = Database::iud(
            "INSERT INTO equipment_has_location (equipment_id, location_id) VALUES (?, ?)",
            "ii", [$new_equipment_id, $location_id]
        );
        
        if (!$location_result) {
            throw new Exception('Failed to save equipment location');
        }
    }

    // Commit transaction
   

    // If Technical Officer → notify all HODs, need_approval = 1
    if ($user_role === 'technical_officer') {

        // Get TO's name for the notification message
        $to_result = Database::search(
            "SELECT first_name, last_name FROM lab_user WHERE id = ?",
            "i", [$user_id]
        );
        $to_name = 'Technical Officer';
        if ($to_result && $to_result->num_rows > 0) {
            $to_row  = $to_result->fetch_assoc();
            $to_name = $to_row['first_name'] . ' ' . $to_row['last_name'];
        }

        // Get location name for notification
        $location_name = '';
        if ($location_id > 0) {
            $loc_result = Database::search("SELECT location FROM location WHERE id = ?", "i", [$location_id]);
            if ($loc_result && $loc_result->num_rows > 0) {
                $loc_row = $loc_result->fetch_assoc();
                $location_name = " at location: " . $loc_row['location'];
            }
        }

        // Get all HOD user IDs
        $hod_result = Database::search(
            "SELECT u.id FROM lab_user u
             JOIN lab_user_has_role ur ON u.id = ur.lab_user_id
             JOIN role r ON ur.role_id = r.id
             WHERE r.role = 'hod' AND u.status = 1"
        );

        if ($hod_result && $hod_result->num_rows > 0) {
            while ($hod = $hod_result->fetch_assoc()) {
                Database::iud(
                    "INSERT INTO notification 
                        (description, created_datetime, owner_of_notification, status, need_approval)
                     VALUES (?, NOW(), ?, 'unread', 1)",
                    "si",
                    [
                        "New equipment '{$name}' (Code: {$code}){$location_name} added by {$to_name} requires your approval.",
                        $hod['id']
                    ]
                );
            }
        }

        echo json_encode([
            'success'          => true,
            'message'          => 'Equipment submitted for HOD approval.',
            'requires_approval' => true
        ]);

    } else {
        // HOD added — approved immediately, no notification needed
        echo json_encode([
            'success'          => true,
            'message'          => 'Equipment added successfully.',
            'requires_approval' => false
        ]);
    }

} catch (Exception $e) {
    // Rollback on error
 
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit();
?>
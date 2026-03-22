<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id                   = intval($_POST['id'] ?? 0);
$code                 = $_POST['code'] ?? '';
$name                 = $_POST['name'] ?? '';
$qty                  = intval($_POST['qty'] ?? 0);
$simultaneous_users   = intval($_POST['simultaneous_users'] ?? 1);
$sterilization_required = $_POST['sterilization_required'] ?? 'NO';
$reservation_required = $_POST['reservation_required'] ?? 'YES';
$description          = $_POST['description'] ?? '';
$broken_qty           = intval($_POST['broken_qty'] ?? 0);
$repair_qty           = intval($_POST['repair_qty'] ?? 0);

if ($id <= 0 || empty($code) || empty($name) || $qty < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Check duplicate code
$check_result = Database::search("SELECT id FROM equipment WHERE code = ? AND id != ?", "si", [$code, $id]);
if ($check_result && $check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Equipment code already exists']);
    exit();
}

// ── GET OLD DATA BEFORE UPDATE (for change comparison) ──────────────────────
$oldResult = Database::search("SELECT * FROM equipment WHERE id = ?", "i", [$id]);
$old = ($oldResult && $oldResult->num_rows > 0) ? $oldResult->fetch_assoc() : [];

$oldBrokenResult = Database::search(
    "SELECT COALESCE(SUM(broken_qty), 0) as qty FROM broken WHERE equipment_id = ?", "i", [$id]
);
$old_broken_qty = $oldBrokenResult ? (int)$oldBrokenResult->fetch_assoc()['qty'] : 0;

$oldRepairResult = Database::search(
    "SELECT COALESCE(SUM(repair_qty), 0) as qty FROM repair WHERE equipment_id = ?", "i", [$id]
);
$old_repair_qty = $oldRepairResult ? (int)$oldRepairResult->fetch_assoc()['qty'] : 0;

// ── HANDLE IMAGE UPLOAD ──────────────────────────────────────────────────────
$image_path   = null;
$update_image = false;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../assets/equipment_images/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename       = uniqid() . '.' . $file_extension;
    $target_path    = $upload_dir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        $image_path   = 'assets/equipment_images/' . $filename;
        $update_image = true;
    }
}

// ── UPDATE EQUIPMENT TABLE ───────────────────────────────────────────────────
if ($update_image) {
    $update_query = "UPDATE equipment SET code=?, name=?, total_qty=?, simultaneous_users=?,
                     sterilization_required=?, reservation_required=?, description=?, image_path=?,
                     updated_details_datetime=NOW() WHERE id=?";
    $types  = "ssiissssi";
    $params = [$code, $name, $qty, $simultaneous_users, $sterilization_required,
               $reservation_required, $description, $image_path, $id];
} else {
    $update_query = "UPDATE equipment SET code=?, name=?, total_qty=?, simultaneous_users=?,
                     sterilization_required=?, reservation_required=?, description=?,
                     updated_details_datetime=NOW() WHERE id=?";
    $types  = "ssiisssi";
    $params = [$code, $name, $qty, $simultaneous_users, $sterilization_required,
               $reservation_required, $description, $id];
}

$result = Database::iud($update_query, $types, $params);

if ($result) {
    // ── UPDATE BROKEN TABLE ──────────────────────────────────────────────────
    Database::iud("DELETE FROM broken WHERE equipment_id = ?", "i", [$id]);
    if ($broken_qty > 0) {
        Database::iud("INSERT INTO broken (equipment_id, broken_qty) VALUES (?, ?)", "ii", [$id, $broken_qty]);
    }

    // ── UPDATE REPAIR TABLE ──────────────────────────────────────────────────
    Database::iud("DELETE FROM repair WHERE equipment_id = ?", "i", [$id]);
    if ($repair_qty > 0) {
        Database::iud("INSERT INTO repair (equipment_id, repair_qty) VALUES (?, ?)", "ii", [$id, $repair_qty]);
    }

    // ── BUILD CHANGE SUMMARY ─────────────────────────────────────────────────
    $changes = [];

    if (!empty($old)) {
        if ($old['name'] !== $name) {
            $changes[] = "Name changed: '{$old['name']}' → '{$name}'";
        }
        if ($old['code'] !== $code) {
            $changes[] = "Code changed: '{$old['code']}' → '{$code}'";
        }
        if ((int)$old['total_qty'] !== $qty) {
            $diff      = $qty - (int)$old['total_qty'];
            $direction = $diff > 0 ? "increased by $diff" : "decreased by " . abs($diff);
            $changes[] = "Total quantity {$direction} (was {$old['total_qty']}, now {$qty})";
        }
        if ((int)$old['simultaneous_users'] !== $simultaneous_users) {
            $changes[] = "Simultaneous users: {$old['simultaneous_users']} → {$simultaneous_users}";
        }
        if ($old['sterilization_required'] !== $sterilization_required) {
            $changes[] = "Sterilization required: {$old['sterilization_required']} → {$sterilization_required}";
        }
        if ($old['reservation_required'] !== $reservation_required) {
            $changes[] = "Reservation required: {$old['reservation_required']} → {$reservation_required}";
        }
        if ($update_image) {
            $changes[] = "Equipment image updated";
        }
        if ($broken_qty !== $old_broken_qty) {
            $changes[] = "Broken quantity: {$old_broken_qty} → {$broken_qty}";
        }
        if ($repair_qty !== $old_repair_qty) {
            $changes[] = "Repair/maintenance quantity: {$old_repair_qty} → {$repair_qty}";
        }
    }

    // ── NOTIFY HOD IF ANYTHING CHANGED ──────────────────────────────────────
    if (!empty($changes)) {
        $changeList = implode("\n• ", $changes);
        $notifMsg   = "Equipment '{$name}' (Code: {$code}) was updated by Technical Officer:\n• " . $changeList;

        // Get HOD id
        $hodResult = Database::search(
            "SELECT lu.id FROM lab_user lu
             INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
             INNER JOIN role r ON uhr.role_id = r.id
             WHERE r.role = 'hod' AND lu.status = 1
             LIMIT 1",
            ''
        );

        if ($hodResult && $hodResult->num_rows > 0) {
            $hod_id = $hodResult->fetch_assoc()['id'];
            Database::iud(
                "INSERT INTO notification
                 (description, created_datetime, owner_of_notification, status, need_approval)
                 VALUES (?, NOW(), ?, 'unread', 1)",
                "si",
                [$notifMsg, $hod_id]
            );
        }
    }

    echo json_encode(['success' => true, 'message' => 'Equipment updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
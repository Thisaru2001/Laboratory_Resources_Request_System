<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'hod') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$notif_id = intval($_GET['notif_id'] ?? 0);

if ($notif_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    exit();
}

// Get the notification description to extract equipment code
$notif = Database::search(
    "SELECT description FROM notification WHERE id = ? AND owner_of_notification = ?",
    "ii", [$notif_id, $_SESSION["user_id"]]
);

if (!$notif || $notif->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Notification not found']);
    exit();
}

$row = $notif->fetch_assoc();
$description = $row['description'];

// Extract equipment code from description — format: "... (Code: EQ-001) ..."
preg_match('/\(Code:\s*([^)]+)\)/', $description, $matches);

if (empty($matches[1])) {
    echo json_encode(['success' => false, 'message' => 'Could not find equipment code in notification']);
    exit();
}

$code = trim($matches[1]);

// Get equipment ID - first check if already processed
$eq_processed = Database::search(
    "SELECT id FROM equipment WHERE code = ? AND is_hod_checked = 1",
    "s", [$code]
);

// If already processed, mark the notification as read and return success
if ($eq_processed && $eq_processed->num_rows > 0) {
    Database::iud(
        "UPDATE notification SET status = 'read' WHERE id = ?",
        "i", [$notif_id]
    );
    echo json_encode(['success' => true, 'message' => 'Already processed', 'already_processed' => true]);
    exit();
}

// Get equipment ID - unprocessed
$eq = Database::search(
    "SELECT id FROM equipment WHERE code = ? AND is_hod_checked = 0",
    "s", [$code]
);

if (!$eq || $eq->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Equipment not found']);
    exit();
}

$eq_row = $eq->fetch_assoc();
echo json_encode(['success' => true, 'equipment_id' => $eq_row['id']]);
exit();
?>
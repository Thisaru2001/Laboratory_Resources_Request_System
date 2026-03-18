<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION["user_id"];

// Mark single notification
if (isset($_POST['notification_id']) && !empty($_POST['notification_id'])) {
    $notif_id = intval($_POST['notification_id']);
    
    $result = Database::iud(
        "UPDATE notification SET status = 'read' WHERE id = ? AND owner_of_notification = ?",
        "ii", [$notif_id, $user_id]
    );
    
    echo json_encode(['success' => (bool)$result]);
    exit();
}

// Mark ALL as read
$result = Database::iud(
    "UPDATE notification SET status = 'read' WHERE owner_of_notification = ? AND status = 'unread'",
    "i", [$user_id]
);

echo json_encode(['success' => (bool)$result]);
?>
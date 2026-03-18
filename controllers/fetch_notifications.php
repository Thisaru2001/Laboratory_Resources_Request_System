<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION["user_id"];

$result = Database::search(
    "SELECT id, description, created_datetime, status, need_approval
     FROM notification
     WHERE owner_of_notification = ? AND need_approval = 1 AND status = 'unread'
     ORDER BY created_datetime DESC
     LIMIT 20",
    "i", [$user_id]
);

$notifications  = [];
$unread_count   = 0;
$approval_count = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if ($row['status'] === 'unread') $unread_count++;
        if ($row['need_approval'] == 1)  $approval_count++;
    }
}

echo json_encode([
    'success'        => true,
    'notifications'  => $notifications,
    'unread_count'   => $unread_count,
    'approval_count' => $approval_count
]);
exit();
?>
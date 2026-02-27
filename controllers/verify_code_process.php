<?php
session_start();
include "../config/database.php";

header('Content-Type: application/json');

$university_id = trim($_POST['u'] ?? '');
$code = trim($_POST['code'] ?? '');

if (empty($university_id) || empty($code)) {
    echo json_encode(['status'=>'error', 'msg'=>'All fields required']);
    exit;
}

try {
    // Check if code is valid and not expired
    $result = Database::search(
        "SELECT user_id FROM `lab_user` 
         WHERE university_id = ? 
         AND verification_code = ? 
         AND verification_expires > NOW() 
         LIMIT 1",
        "ss",
        [$university_id, $code]
    );

    if ($result && $result->num_rows === 1) {
        echo json_encode(['status'=>'success', 'msg'=>'Code verified successfully']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'Invalid or expired code']);
    }

} catch (Exception $e) {
    error_log("Verify code error: " . $e->getMessage());
    echo json_encode(['status'=>'error', 'msg'=>'Server error']);
}
?>
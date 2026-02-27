<?php
session_start();
include "../config/database.php";

header('Content-Type: application/json');

$university_id = trim($_POST['u'] ?? '');
$new_password = $_POST['password'] ?? '';

// Validate input
if (empty($university_id) || empty($new_password)) {
    echo json_encode(['status'=>'error', 'msg'=>'All fields required']);
    exit;
}

if (strlen($new_password) < 8) {
    echo json_encode(['status'=>'error', 'msg'=>'Password must be at least 8 characters']);
    exit;
}

try {
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password and clear verification code
    $updateResult = Database::iud(
        "UPDATE `lab_user` 
         SET password_user = ?, verification_code = NULL, verification_expires = NULL 
         WHERE university_id = ?",
        "ss",
        [$hashed_password, $university_id]
    );

    if ($updateResult) {
        echo json_encode(['status'=>'success', 'msg'=>'Password reset successfully']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'Failed to reset password']);
    }

} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    echo json_encode(['status'=>'error', 'msg'=>'Server error']);
}
?>
<?php
session_start();
include "../config/database.php"; // adjust path

header('Content-Type: application/json');

// Use $_POST since you send FormData, not JSON
$university_id = trim($_POST['u'] ?? '');
$password = $_POST['p'] ?? '';
$remember_me = !empty($_POST['r']);
$csrf_token = $_POST['csrf_token'] ?? '';

// CSRF check
if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid CSRF token']);
    exit;
}

// Basic validation
if (empty($university_id)) {
    echo json_encode(['status' => 'error', 'msg' => 'Please enter your University ID.']);
    exit;
}

if (empty($password)) {
    echo json_encode(['status' => 'error', 'msg' => 'Please enter your password.']);
    exit;
}

if (strlen($university_id) > 20) {
    echo json_encode(['status'=>'error','msg'=>'Invalid University ID']);
    exit;
}

try {
    $result = Database::search(
        "SELECT * FROM `user` WHERE `university_id` = ? LIMIT 1",
        "s",
        [$university_id]
    );

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // Set session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'university_id' => $user['university_id'],
                'name' => $user['first_name'] ?? ''
            ];

            // Remember Me token
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                Database::iud(
                    "UPDATE `user` SET `remember_token`=? WHERE `id`=?",
                    "si",
                    [$token, $user['id']]
                );

                setcookie("remember_token", $token, [
                    'expires' => time() + 60*60*24*30,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }

            echo json_encode(['status' => 'success', 'msg' => 'Login successful']);
            exit;

        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid University ID or password.']);
            exit;
        }

    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid University ID or password.']);
        exit;
    }

} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'msg' => 'Server error. Try again later.']);
}
?>

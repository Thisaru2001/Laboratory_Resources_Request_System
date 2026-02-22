<?php
session_start();
include "../config/database.php";

header('Content-Type: application/json');

/* =====================================================
   GET INPUT
===================================================== */

$university_id = trim($_POST['u'] ?? '');
$password      = $_POST['p'] ?? '';
$remember_me   = !empty($_POST['r']);
$csrf_token    = $_POST['csrf_token'] ?? '';
$recaptcha     = $_POST['recaptcha'] ?? '';

/* =====================================================
   CSRF CHECK
===================================================== */
if (!isset($_SESSION['csrf_token']) ||
    $csrf_token !== $_SESSION['csrf_token']) {

    echo json_encode([
        'status'=>'error',
        'msg'=>'Invalid CSRF token'
    ]);
    exit;
}

/* =====================================================
   BASIC VALIDATION
===================================================== */
if (empty($university_id) || empty($password)) {
    echo json_encode([
        'status'=>'error',
        'msg'=>'All fields required'
    ]);
    exit;
}

if (strlen($university_id) > 20) {
    echo json_encode([
        'status'=>'error',
        'msg'=>'Invalid University ID'
    ]);
    exit;
}

/* =====================================================
   GOOGLE RECAPTCHA VERIFY
===================================================== */

$secretKey = "6LcM0HMsAAAAANzVhD2S3a9tOPDDZS0puelYCLI3"; // 🔴 replace

$verify = file_get_contents(
    "https://www.google.com/recaptcha/api/siteverify?secret="
    .$secretKey."&response=".$recaptcha
);

$responseData = json_decode($verify);

if (!$responseData || !$responseData->success) {
    echo json_encode([
        'status'=>'error',
        'msg'=>'reCAPTCHA verification failed'
    ]);
    exit;
}

/* =====================================================
   LOGIN PROCESS
===================================================== */

try {

    $result = Database::search(
        "SELECT * FROM `lab_user`
         WHERE `university_id`=? LIMIT 1",
        "s",
        [$university_id]
    );

    if ($result && $result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            /* ---------- SESSION ---------- */
            $_SESSION['user'] = [
                'user_id'       => $user['user_id'],
                'university_id' => $user['university_id'],
                'name'          => $user['first_name']
            ];

            /* ---------- REMEMBER ME ---------- */
            if ($remember_me) {

                $token = bin2hex(random_bytes(32));

                Database::iud(
                    "UPDATE `lab_user`
                     SET `remember_token`=?
                     WHERE `user_id`=?",
                    "si",
                    [$token, $user['user_id']]
                );

                setcookie("remember_token", $token, [
                    'expires'  => time() + (86400 * 30),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Strict',
                    'secure'   => isset($_SERVER['HTTPS'])
                ]);
            }

            echo json_encode([
                'status'=>'success',
                'msg'=>'Login successful'
            ]);
            exit;
        }
    }

    echo json_encode([
        'status'=>'error',
        'msg'=>'Invalid University ID or password'
    ]);
    exit;

} catch (Exception $e) {

    error_log("Login Error: ".$e->getMessage());

    echo json_encode([
        'status'=>'error',
        'msg'=>'Server error. Try again later.'
    ]);
}
?>
<?php
session_start();
include "../config/database.php";
require '../vendor/autoload.php'; // PHPMailer autoload
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// ---------------- INPUT ----------------
$university_id = trim($_POST['u'] ?? '');
$csrf = $_POST['csrf_token'] ?? '';

// ---------------- CSRF CHECK ----------------
if (!isset($_SESSION['csrf_token']) || $csrf !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid CSRF token']);
    exit;
}

// ---------------- CLEANUP EXPIRED CODES ----------------
Database::iud(
    "UPDATE `user` SET verification_code=NULL, verification_expires=NULL WHERE verification_expires < NOW()",
    ""
);

// ---------------- GENERIC RESPONSE ----------------
$msg = "If your account exists, a verification code has been sent.";

// ---------------- PROCESS ----------------
if (!empty($university_id) && strlen($university_id) <= 20) {

    try {
        // Secure search for user
        $result = Database::search(
            "SELECT id, email FROM `user` WHERE university_id = ? LIMIT 1",
            "s",
            [$university_id]
        );

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $email = $user['email'];

            // Generate 6-digit code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date("Y-m-d H:i:s", time() + 900); // 15 min

            // Save in DB
            Database::iud(
                "UPDATE `user` SET verification_code=?, verification_expires=? WHERE id=?",
                "ssi",
                [$code, $expires, $user['id']]
            );

            // ---------------- SEND EMAIL ----------------
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'food.shop.online.store@gmail.com';  // Use env variable in production
                $mail->Password   = 'ttnv oinj vpuv agtq';          // App password, use env variable
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('food.shop.online.store@gmail.com', 'Micro Lab System');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Micro Lab Verification Code';
                $mail->Body    = "<h3 style='color:green;'>Your 6-digit verification code is: $code</h3>
                                  <p>This code expires in 15 minutes.</p>";
                $mail->AltBody = "Your 6-digit verification code is: $code. It expires in 15 minutes.";

                $mail->send();

            } catch (Exception $e) {
                error_log("Email sending failed: " . $mail->ErrorInfo);
                // Do not reveal failure to user
            }
        }

        // Always return generic success
        echo json_encode(['status'=>'success', 'msg'=>$msg]);

    } catch (Exception $e) {
        error_log("Forgot password error: " . $e->getMessage());
        echo json_encode(['status'=>'error','msg'=>'Server error. Please try again later.']);
    }

} else {
    echo json_encode(['status'=>'error','msg'=>'Invalid University ID']);
}

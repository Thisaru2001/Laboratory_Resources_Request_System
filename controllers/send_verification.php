<?php
session_start();
require_once "../config/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$university_id = strtoupper(trim($_POST['university_id'] ?? ''));

if (empty($university_id)) {
    echo json_encode(['success' => false, 'message' => 'University ID is required']);
    exit;
}

// Check if user exists and get email
$result = Database::search(
    "SELECT * FROM lab_user WHERE university_id = ?",
    "s",
    [$university_id]
);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'University ID not found']);
    exit;
}

$user = $result->fetch_assoc();

// Generate 6-digit verification code
$verification_code = sprintf("%06d", mt_rand(1, 999999));

// Store verification code in database
date_default_timezone_set('Asia/Colombo');
$expiry_time = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Code expires in 15 minutes

$update_result = Database::iud(
    "UPDATE lab_user SET verification_code = ? WHERE university_id = ?",
    "ss",
    [$verification_code, $university_id]
);

if (!$update_result) {
    echo json_encode(['success' => false, 'message' => 'Failed to generate verification code. Please try again.']);
    exit;
}

// Store in session for additional security
$_SESSION['reset_university_id'] = $university_id;
$_SESSION['reset_email'] = $user['email'];
$_SESSION['reset_time'] = time();

// Send email with verification code using PHPMailer
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'microbiologylaboratorysystem@gmail.com';
    $mail->Password   = 'cesb lydd jord elyu';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // Recipients
    $mail->setFrom('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');
    $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
    $mail->addReplyTo('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Verification Code - Microbiology Lab';
    $mail->Body = '
<table width="100%" bgcolor="#f4f4f5" cellpadding="0" cellspacing="0" style="font-family:Arial,sans-serif;">
<tr>
  <td align="center">
    <!-- Main container -->
    <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="border:1px solid #ddd;">
      <!-- Header -->
      <tr>
        <td align="center" bgcolor="#16a34a" style="padding:30px; color:#fff; font-size:24px; font-weight:bold;">
          Microbiology Lab System
        </td>
      </tr>
      <!-- Body -->
      <tr>
        <td style="padding:30px; color:#333; font-size:16px;">
          <p>Hello <strong>'.htmlspecialchars($user['first_name'].' '.$user['last_name']).'</strong>,</p>
          <p>We received a request to reset your password. Use the verification code below (valid for 15 minutes):</p>

          <table width="100%" cellpadding="15" cellspacing="0" bgcolor="#f0fdf4" style="border:2px solid #22c55e; text-align:center; margin:20px 0;">
            <tr>
              <td style="font-size:36px; font-weight:bold; color:#166534; letter-spacing:5px;">
                '.$verification_code.'
              </td>
            </tr>
          </table>

          <table width="100%" cellpadding="10" cellspacing="0" bgcolor="#fff3cd" style="border:1px solid #ffc107; margin:20px 0;">
            <tr>
              <td style="color:#856404; font-weight:bold;">&#9888; If you didn\'t make this change, contact your supervisor/HOD immediately.</td>
            </tr>
          </table>

          <table width="100%" cellpadding="5">
            <tr>
              <td><strong>University ID:</strong></td>
              <td>'.htmlspecialchars($university_id).'</td>
            </tr>
            <tr>
              <td><strong>Valid Until:</strong></td>
              <td>'.htmlspecialchars($expiry_time).'</td>
            </tr>
          </table>

          <p>Thanks,<br>
The LRR system</p>
        </td>
      </tr>
      <!-- Footer -->
      <tr>
        <td align="center" style="padding:20px; font-size:12px; color:#6b7280;">
          University of Kelaniya - Department of Microbiology<br>
          This is an automated message, please do not reply.<br>
          &copy; '.date('Y').' Microbiology Lab System
        </td>
      </tr>
    </table>
  </td>
</tr>
</table>';

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Verification code sent to your email',
        'email' => maskEmail($user['email'])
    ]);
} catch (Exception $e) {
    error_log("Password Reset Email Error: " . $mail->ErrorInfo);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send verification email. Please try again.'
    ]);
}

// Helper function to mask email for privacy
function maskEmail($email) {
    $parts = explode('@', $email);
    $name = $parts[0];
    $domain = $parts[1];
    $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
    return $maskedName . '@' . $domain;
}
?>
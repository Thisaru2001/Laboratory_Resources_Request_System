<?php
session_start();
require_once "../config/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';
 $env = parse_ini_file(__DIR__ . '/../.env');

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$university_id = strtoupper(trim($_POST['university_id'] ?? ''));
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate inputs
if (empty($university_id) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Verify session and verification status
if (!isset($_SESSION['reset_university_id']) || 
    $_SESSION['reset_university_id'] !== $university_id || 
    !isset($_SESSION['reset_verified']) || 
    $_SESSION['reset_verified'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please verify your code first']);
    exit;
}

// Check if session is expired
if (time() - $_SESSION['reset_time'] > 900) { // 15 minutes
    echo json_encode(['success' => false, 'message' => 'Session expired. Please request a new code.']);
    exit;
}

// Get user details for email
$result = Database::search(
    "SELECT * FROM lab_user WHERE university_id = ?",
    "s",
    [$university_id]
);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();

// Generate token for remember_me functionality
$token = bin2hex(random_bytes(32));

// Hash password and token
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$hashed_token = password_hash($token, PASSWORD_DEFAULT);

date_default_timezone_set('Asia/Colombo');
$now = date('Y-m-d H:i:s');

// Update password, remember_token, and clear verification_code
$update_result = Database::iud(
    "UPDATE lab_user SET password_user = ?, remember_token = ?, verification_code = NULL, details_updated_datetime = ? WHERE university_id = ?",
    "ssss",
    [$hashed_password, $hashed_token, $now, $university_id]
);

if ($update_result) {
  global $env;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $env["MAIL_HOST"];
        $mail->SMTPAuth   = true;
        $mail->Username   = $env["MAIL_USERNAME"];
        $mail->Password   = $env["MAIL_PASSWORD"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $env["MAIL_PORT"];
        $mail->CharSet    = "UTF-8";

        // Recipients
      $mail->setFrom($env["MAIL_USERNAME"], "Microbiology Lab System");
        $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
        $mail->addReplyTo('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Changed Successfully - Microbiology Lab';
        $mail->Body = '
<table width="100%" bgcolor="#f4f4f5" cellpadding="0" cellspacing="0" style="font-family:Arial,sans-serif;">
<tr>
  <td align="center">
    <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="border:1px solid #ddd;">
      <tr>
        <td align="center" bgcolor="#16a34a" style="padding:30px; color:#fff; font-size:24px; font-weight:bold;">
          Microbiology Lab System
        </td>
      </tr>
      <tr>
        <td style="padding:30px; color:#333; font-size:16px;">
          <p>Hello <strong>'.htmlspecialchars($user['first_name'].' '.$user['last_name']).'</strong>,</p>
          <p>Your password has been changed successfully.</p>

          <table width="100%" cellpadding="10" cellspacing="0" bgcolor="#f0fdf4" style="border:2px solid #22c55e; margin:20px 0;">
            <tr>
              <td style="color:#166534;">
                <strong>University ID:</strong> '.htmlspecialchars($university_id).'<br>
                <strong>Date & Time:</strong> '.htmlspecialchars($now).'<br>
              </td>
            </tr>
          </table>

          <table width="100%" cellpadding="10" cellspacing="0" bgcolor="#fff3cd" style="border:1px solid #ffc107; margin:20px 0;">
            <tr>
              <td style="color:#856404; font-weight:bold;">&#9888; If you didn\'t make this change, contact your supervisor immediately.</td>
            </tr>
          </table>

          <p style="text-align: center;">
            <a href="http://'.$_SERVER['HTTP_HOST'].'/LRRS/index.php" 
               style="background-color:#16a34a; color:#ffffff; padding:12px 30px; 
                      text-decoration:none; border-radius:5px; display:inline-block;">
              Login to System
            </a>
          </p>

          <p>Thanks,<br>The LRR system</p>
        </td>
      </tr>
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
    } catch (Exception $e) {
        error_log("Password Change Email Error: " . $mail->ErrorInfo);
    }
    
    // Clear reset session
    unset($_SESSION['reset_code']);
    unset($_SESSION['reset_university_id']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_time']);
    unset($_SESSION['reset_verified']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password changed successfully! Redirecting to login...'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again.']);
}
?>
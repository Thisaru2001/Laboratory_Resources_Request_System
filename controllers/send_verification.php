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

// Store in session with timestamp
$_SESSION['reset_code'] = $verification_code;
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
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    // Recipients
    $mail->setFrom('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');
    $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
    $mail->addReplyTo('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Verification Code - Microbiology Lab';
    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #f9fafb; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #22c55e, #16a34a); padding: 30px; text-align: center; }
            .header h2 { color: white; margin: 0; font-size: 24px; }
            .content { padding: 40px 30px; }
            .code-box { background: #f0fdf4; border: 2px dashed #22c55e; padding: 20px; text-align: center; border-radius: 10px; margin: 20px 0; }
            .code-box h1 { color: #166534; font-size: 36px; letter-spacing: 5px; margin: 0; }
            .info { color: #4b5563; line-height: 1.6; }
            .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .footer { background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 12px; border-top: 1px solid #e5e7eb; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Microbiology Lab System</h2>
            </div>
            <div class='content'>
                <h3 style='color: #166534; margin-top: 0;'>Password Reset Request</h3>
                <p class='info'>Hello <strong>" . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</strong>,</p>
                <p class='info'>We received a request to reset your password. Use the verification code below:</p>
                
                <div class='code-box'>
                    <h1>" . $verification_code . "</h1>
                </div>
                
                <div class='warning'>
                    <p style='margin: 0;'><strong>⚠️ If you didn't make this change, please contact your supervisor immediately.</strong></p>
                </div>
                
                <p class='info'>If you didn't request this, please ignore this email.</p>
                
                <table style='width: 100%; margin-top: 30px; background: #f9fafb; padding: 15px; border-radius: 8px;'>
                    <tr>
                        <td style='color: #4b5563; padding: 5px;'><strong>University ID:</strong></td>
                        <td style='color: #166534; padding: 5px;'>" . htmlspecialchars($university_id) . "</td>
                    </tr>
                </table>
            </div>
            <div class='footer'>
                <p>University of Kelaniya - Department of Microbiology</p>
                  <p>This is an automated message, please do not reply.</p>
                <p>&copy; " . date('Y') . " Microbiology Lab System</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->send();
    
    echo json_encode([
        'success' => true,
        'message' => 'Verification code sent to your email',
        'email' => $user['email']
    ]);

} catch (Exception $e) {
    error_log("Password Reset Email Error: " . $mail->ErrorInfo);
    
    // ✅ FIXED: Send response back to browser!
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send verification email. Please try again.'
    ]);
}
?>
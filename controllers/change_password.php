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

// Verify session (ensure they verified code first)
if (!isset($_SESSION['reset_university_id']) || $_SESSION['reset_university_id'] !== $university_id) {
    echo json_encode(['success' => false, 'message' => 'Please verify your code first']);
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
$token = $university_id . $new_password;

// Hash password and token
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$hashed_token = password_hash($token, PASSWORD_DEFAULT);

// Update both password and remember_token
$update_result = Database::iud(
    "UPDATE lab_user SET password_user = ?, remember_token = ? WHERE university_id = ?",
    "sss",
    [$hashed_password, $hashed_token, $university_id]
);

if ($update_result) {
    // Send confirmation email using PHPMailer
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
        $mail->Subject = 'Password Changed Successfully - Microbiology Lab';

        // HTML Email Template
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background: #f9fafb; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #22c55e, #16a34a); padding: 30px; text-align: center; }
                .header h2 { color: white; margin: 0; font-size: 24px; }
                .content { padding: 40px 30px; text-align: center; }
                .success-icon { margin-bottom: 20px; }
                .success-icon span { font-size: 64px; }
                .credentials { background: #f0fdf4; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: left; }
                .footer { background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 12px; border-top: 1px solid #e5e7eb; }
                .btn { display: inline-block; background: #22c55e; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Microbiology Lab System</h2>
                </div>
                <div class='content'>
                    <div class='success-icon'>
                        <span>✅</span>
                    </div>
                    <h3 style='color: #166534;'>Password Changed Successfully!</h3>
                    <p style='color: #4b5563;'>Your password has been updated.</p>
                    
                    <div class='credentials'>
                        <p style='margin: 10px 0;'><strong>University ID:</strong> " . htmlspecialchars($university_id) . "</p>
                        <p style='margin: 10px 0;'><strong>Name:</strong> " . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</p>
                        <p style='margin: 10px 0;'><strong>Change Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                      
                    </div>
                    
                    <p style='color: #4b5563;'>If you didn't make this change, please contact your supervisor immediately.</p>
                    
                    <a href='http://" . $_SERVER['HTTP_HOST'] . "/LRRS/index.php' class='btn'>Login to System</a>
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
    } catch (Exception $e) {
        error_log("Password Change Email Error: " . $mail->ErrorInfo);
        // Don't stop the process if email fails
    }
    
    // Clear reset session
    unset($_SESSION['reset_code']);
    unset($_SESSION['reset_university_id']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_time']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password changed successfully!'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again.']);
}
?>
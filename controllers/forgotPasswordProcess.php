<?php
session_start();
include "../config/database.php";

// Include PHPMailer files
include "SMTP.php";
include "PHPMailer.php";
include "Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

// IMPORTANT: Changed from $_GET["e"] to $_POST["u"] to match your JavaScript
if (isset($_POST["u"])) {

    $university_id = trim($_POST["u"]);
    
    error_log("Searching for university_id: " . $university_id);

    // Use prepared statement to prevent SQL injection
    $result = Database::search(
        "SELECT * FROM `lab_user` WHERE `university_id` = ?",
        "s",
        [$university_id]
    );
    
    if ($result === false) {
        error_log("Database error: " . Database::getLastError());
        // Return generic success for security (prevents email enumeration)
        echo 'success';
        exit();
    }
    
    $n = $result->num_rows;

    if ($n == 1) {
        $user_data = $result->fetch_assoc();
        $user_email = $user_data['email'];
        $first_name = $user_data['first_name'] ?? 'User';
        
        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Set expiration (15 minutes)
        $expires = date("Y-m-d H:i:s", time() + 900);
        
        // Update verification code with expiration
        $update_result = Database::iud(
            "UPDATE `lab_user` SET `verification_code` = ?, `verification_expires` = ? WHERE `university_id` = ?",
            "sss",
            [$code, $expires, $university_id]
        );
        
        if ($update_result === false) {
            error_log("Failed to update verification code");
            echo 'success'; // Return success for security
            exit();
        }

        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'microbiologylaboratorysystem@gmail.com';
            $mail->Password = 'oway suhf uzca geqp';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            
            // Recipients
            $mail->setFrom('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');
            $mail->addAddress($user_email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification Code';

            // Simple but clean email template
            $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
            </head>
            <body style="font-family: Arial, sans-serif; background-color:#f4f4f4; padding:20px;">
                <div style="max-width:600px; margin:0 auto; background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                    <div style="background: linear-gradient(135deg, #22c55e, #16a34a); padding:20px; text-align:center;">
                        <h2 style="color:#ffffff; margin:0;">Microbiology Lab System</h2>
                        <p style="color:#ffffff; margin:5px 0 0;">University of Kelaniya</p>
                    </div>
                    <div style="padding:30px;">
                        <h3 style="color:#333; margin-bottom:20px;">Hello ' . htmlspecialchars($first_name) . ',</h3>
                        <p style="color:#666; line-height:1.6;">We received a request to reset your password. Use the code below:</p>
                        <div style="background:#f0fdf4; border:2px solid #22c55e; border-radius:8px; padding:20px; text-align:center; margin:20px 0;">
                            <h1 style="color:#166534; font-size:42px; letter-spacing:5px; margin:0;">' . $code . '</h1>
                            <p style="color:#666; margin:10px 0 0;">Valid for 15 minutes</p>
                        </div>
                        <p style="color:#666; font-size:14px;">If you didn\'t request this, please ignore this email.</p>
                        <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">
                        <p style="color:#999; font-size:12px; text-align:center;">© ' . date('Y') . ' Microbiology Lab</p>
                    </div>
                </div>
            </body>
            </html>';

            $mail->AltBody = "Hello " . $first_name . ",\n\n" .
                             "Your verification code is: " . $code . "\n\n" .
                             "Valid for 15 minutes.\n\n" .
                             "Microbiology Lab System";

            $mail->send();
            echo 'success';
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            echo 'success'; // Return success even if email fails (security)
        }
    } else {
        // Return success even if user not found (prevents email enumeration)
        error_log("No user found with ID: " . $university_id);
        echo 'success';
    }
} else {
    echo 'error';
}
?>
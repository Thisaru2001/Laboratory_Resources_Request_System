<?php
include "../config/database.php";

include "SMTP.php";
include "PHPMailer.php";
include "Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

if (isset($_GET["e"])) {

    $university_id = $_GET["e"];
    
    // Debug: Check what's being searched
    error_log("Searching for university_id: " . $university_id);

    // Use the search method correctly
    $result = Database::search("SELECT * FROM `lab_user` WHERE `university_id`='" . $university_id . "'");
    
    // Check if search returned false (error) or is a valid result object
    if ($result === false) {
        // Database query failed
        $error = Database::getLastError();
        error_log("Database error: " . $error);
        echo "Database error occurred. Please try again.";
        exit();
    }
    
    $n = $result->num_rows;
    
    // Debug: Check how many rows found
    error_log("Rows found: " . $n);

    if ($n == 1) {
        // Get user data
        $user_data = $result->fetch_assoc();
        
        // Debug: Print user data
        error_log("User data: " . print_r($user_data, true));
        
        // Get the email from database - THIS IS CORRECT NOW
        $user_email = $user_data['email']; // FIXED: Use email field
        
        // Get first name
        $first_name = $user_data['first_name'] ?? 'User';
        
        // Generate verification code
        $code = random_int(100000, 999999);
        
        // Update verification code in database
        $update_result = Database::iud("UPDATE `lab_user` SET `verification_code`='" . $code . "' WHERE `university_id`='" . $university_id . "'");
        
        if ($update_result === false) {
            echo "Failed to update verification code.";
            exit();
        }

        // Create PHPMailer instance
        $mail = new PHPMailer(true); // Enable exceptions
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'microbiologylaboratorysystem@gmail.com';
            $mail->Password = 'oway suhf uzca geqp';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            
            // Recipients - FIXED: Using correct email from database
            $mail->setFrom('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');
            $mail->addAddress($user_email); // FIXED: This was the main error
            $mail->addReplyTo('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification Code - Microbiology Lab';

            // Beautiful HTML email template
            $bodyContent = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
                <div style="max-width:600px; margin:20px auto; background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <div style="background: linear-gradient(135deg, #22c55e, #16a34a); padding:30px 20px; text-align:center;">
                        <h1 style="color:#ffffff; margin:0; font-size:28px;">🔬 Microbiology Lab System</h1>
                        <p style="color:#ffffff; margin:10px 0 0; opacity:0.9;">University of Kelaniya</p>
                    </div>
                    
                    <!-- Body -->
                    <div style="padding:30px 25px;">
                        <h2 style="color:#333333; margin-bottom:20px;">Hello ' . htmlspecialchars($first_name) . ',</h2>
                        
                        <p style="color:#666666; font-size:16px; line-height:1.6; margin-bottom:25px;">
                            We received a request to reset your password for <strong>' . htmlspecialchars($university_id) . '</strong>. 
                            Please use the verification code below to complete the process:
                        </p>
                        
                        <!-- Verification Code Box -->
                        <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border:2px solid #22c55e; border-radius:12px; padding:25px; text-align:center; margin:25px 0;">
                            <p style="color:#166534; font-size:14px; margin:0 0 10px; text-transform:uppercase; letter-spacing:2px;">Verification Code</p>
                            <h1 style="color:#166534; font-size:48px; margin:0; font-family:monospace; letter-spacing:8px;">' . $code . '</h1>
                            <p style="color:#666666; font-size:14px; margin:15px 0 0;">Valid for 15 minutes</p>
                        </div>
                        
                        <p style="color:#666666; font-size:14px; line-height:1.6; margin-bottom:20px;">
                            If you didn\'t request this password reset, please ignore this email or contact support.
                        </p>
                        
                        <hr style="border:none; border-top:1px solid #e5e7eb; margin:25px 0;">
                        
                        <p style="color:#999999; font-size:13px; line-height:1.5; margin:0;">
                            <strong>Security Tip:</strong> Never share this code with anyone. Our staff will never ask for your verification code.
                        </p>
                    </div>
                    
                    <!-- Footer -->
                    <div style="background-color:#f9fafb; padding:20px 25px; text-align:center; border-top:1px solid #e5e7eb;">
                        <p style="color:#999999; font-size:12px; margin:0;">
                            © ' . date('Y') . ' Microbiology Laboratory System<br>
                            University of Kelaniya, Sri Lanka
                        </p>
                        <p style="color:#999999; font-size:11px; margin:10px 0 0;">
                            This is an automated message, please do not reply to this email.
                        </p>
                    </div>
                </div>
            </body>
            </html>';

            $mail->Body = $bodyContent;

            // Plain text alternative for non-HTML email clients
            $mail->AltBody = "Hello " . $first_name . ",\n\n" .
                             "Your Password Reset Verification Code is: " . $code . "\n\n" .
                             "If you didn't request this, please ignore this email.\n\n" .
                             "Microbiology Lab System - University of Kelaniya";

            $mail->send();
            echo 'success';
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            echo 'Verification code sending failed. Error: ' . $mail->ErrorInfo;
        }
    } else {
        echo "Invalid Email Address. No user found with ID: " . $university_id;
    }
} else {
    echo "Please enter your Email Address in Email Field.";
}
?>
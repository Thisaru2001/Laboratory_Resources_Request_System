<?php
declare(strict_types=1);
session_start();

require_once '../config/database.php';

// PHPMailer includes
require_once 'Exception.php';
require_once 'PHPMailer.php';
require_once 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$env = parse_ini_file(__DIR__ . '/../.env');
// Check if supervisor is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['student_id'] ?? 0;
$supervisor_id = $_SESSION['user_id'];

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'Student ID required']);
    exit;
}

try {
    // Start transaction
    //Database::iud("START TRANSACTION");
    
    // FIRST: Get student details BEFORE updating
    $studentQuery = "SELECT id, first_name, last_name, email, university_id 
                    FROM lab_user 
                    WHERE id = ? AND who_approved = ? AND status = 0";
    $studentResult = Database::search($studentQuery, "ii", [$student_id, $supervisor_id]);
    
    if ($studentResult === false || $studentResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found or already processed']);
        exit;
    }
    
    $student = $studentResult->fetch_assoc();
    
    // Update student status to accepted (1 = accepted)
    $updateQuery = "UPDATE lab_user 
                    SET status = 1, 
                        approved_datetime = NOW() 
                    WHERE id = ? 
                    AND who_approved = ? 
                    AND status = 0";
    
    $success = Database::iud($updateQuery, "ii", [$student_id, $supervisor_id]);
    
    if ($success) {
        // Create notification message
        $notificationMsg = $student['university_id'] . " Student Account Accepted.";
        $currentDateTime = date('Y-m-d H:i:s');
        
        // Get all HODs (role_id = 4) from lab_user_has_role table
        $hodQuery = "SELECT lab_user_id 
                     FROM lab_user_has_role 
                     WHERE role_id = 4";
        $hodResult = Database::search($hodQuery);
        
        if ($hodResult && $hodResult->num_rows > 0) {
            // Insert notification for each HOD
            while ($hod = $hodResult->fetch_assoc()) {
                $hod_id = $hod['lab_user_id'];
                
                $notificationQuery = "INSERT INTO notification 
                                      (description, created_datetime, owner_of_notification) 
                                      VALUES (?, ?, ?)";
                Database::iud($notificationQuery, "ssi", [
                    $notificationMsg, 
                    $currentDateTime, 
                    $hod_id
                ]);
            }
        }
        
        // Also insert notification for the supervisor who approved (optional)
        // $notificationQuery = "INSERT INTO notification 
        //                       (description, created_datetime, owner_of_notification) 
        //                       VALUES (?, ?, ?)";
        // Database::iud($notificationQuery, "ssi", [
        //     $notificationMsg, 
        //     $currentDateTime, 
        //     $supervisor_id
        // ]);
        
        // Commit transaction
      //  Database::iud("COMMIT");
        
        // Send email to student
        $emailSent = sendAcceptanceEmail($student);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Student accepted successfully' . ($emailSent ? '' : ' (Email notification failed)'),
            'student' => $student,
            'email_sent' => $emailSent
        ]);
    } else {
        // Rollback if no rows affected
      //  Database::iud("ROLLBACK");
        echo json_encode([
            'success' => false, 
            'message' => 'No pending request found or already processed'
        ]);
    }
    
} catch (Exception $e) {
    // Rollback on error
  //  Database::iud("ROLLBACK");
    error_log("Error in accept_student: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}

/**
 * Send acceptance email to student
 */
function sendAcceptanceEmail($student) {
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
        $mail->addAddress($student['email'], $student['first_name'] . ' ' . $student['last_name']);
       $mail->addReplyTo($env["MAIL_USERNAME"], "Microbiology Lab System");

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Student Account Has Been Approved - Microbiology Lab';

        // Get base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '100.27.246.223';
        $base_url = $protocol . $host . '/';

        $fullName = $student['first_name'] . ' ' . $student['last_name'];
        $approvedDate = date('F d, Y');

        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Account Approved</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; padding: 30px 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h2 { margin: 0; font-size: 28px; }
                .content { padding: 30px 20px; background: #f9f9f9; border-left: 1px solid #e0e0e0; border-right: 1px solid #e0e0e0; }
                .info-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .info-box table { width: 100%; border-collapse: collapse; }
                .info-box td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
                .info-box tr:last-child td { border-bottom: none; }
                .label { font-weight: 600; color: #166534; width: 120px; }
                .button { display: inline-block; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; padding: 12px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; margin: 20px 0; box-shadow: 0 4px 10px rgba(34,197,94,0.3); }
                .footer { padding: 20px; text-align: center; color: #666; font-size: 13px; border-top: 1px solid #e0e0e0; background: white; border-radius: 0 0 10px 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✓ Account Approved</h2>
                    <p>Microbiology Lab System</p>
                </div>
                
                <div class='content'>
                    <h3 style='color: #166534; margin-top: 0;'>Dear {$fullName},</h3>
                    
                    <p>Congratulations! Your student account registration has been <strong style='color: #22c55e;'>approved</strong> by your supervisor.</p>
                    
                    <div class='info-box'>
                        <h4 style='color: #166534; margin-top: 0; border-bottom: 2px solid #22c55e; padding-bottom: 10px;'>Account Details</h4>
                        <table>
                            <tr><td class='label'>Full Name:</td><td><strong>{$fullName}</strong></td></tr>
                            <tr><td class='label'>University ID:</td><td><strong>{$student['university_id']}</strong></td></tr>
                            <tr><td class='label'>Email:</td><td>{$student['email']}</td></tr>
                            <tr><td class='label'>Approved Date:</td><td>{$approvedDate}</td></tr>
                        </table>
                    </div>
                    
                    <p><strong>What happens next?</strong></p>
                    <ul style='color: #4a5568;'>
                        <li>You can now log in to the system using your credentials</li>
                        <li>Start booking lab equipment and resources</li>
                        <li>View your reservation history and status</li>
                    </ul>
                    
                    <div style='text-align: center;'>
                        <a href='{$base_url}index.php' class='button'>🔬 Login to Your Account</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p><strong>Microbiology Laboratory System</strong></p>
                    <p>Faculty of Science, University of Kelaniya</p>
                    <p>📧 microbiologylaboratorysystem@gmail.com</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Dear {$fullName},\n\n" .
                        "Congratulations! Your student account has been approved.\n\n" .
                        "Account Details:\n" .
                        "Name: {$fullName}\n" .
                        "University ID: {$student['university_id']}\n" .
                        "Email: {$student['email']}\n" .
                        "Approved Date: {$approvedDate}\n\n" .
                        "Login at: {$base_url}index.php";

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Acceptance email failed: " . $mail->ErrorInfo);
        return false;
    }
}
?>
<?php
session_start();
require_once '../config/database.php';

// Include PHPMailer
require_once 'Exception.php';
require_once 'PHPMailer.php';
require_once 'SMTP.php';
 $env = parse_ini_file(__DIR__ . '/../.env');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Check if user is logged in and is technical officer
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'technical_officer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$technical_officer_id = $_SESSION["user_id"];

// Get technical officer details for email
$to_query = "SELECT first_name, last_name, email FROM lab_user WHERE id = ?";
$to_result = Database::search($to_query, "i", [$technical_officer_id]);
$technical_officer_name = 'Technical Officer';
if ($to_result && $to_result->num_rows > 0) {
    $to_data = $to_result->fetch_assoc();
    $technical_officer_name = $to_data['first_name'] . ' ' . $to_data['last_name'];
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$reservation_display_id = $data['reservation_id'] ?? '';
$checked_equipment = $data['checked_equipment'] ?? [];

if (empty($reservation_display_id)) {
    echo json_encode(['success' => false, 'message' => 'Reservation ID required']);
    exit();
}

try {
    // Start transaction
    Database::iud("START TRANSACTION");
    
    // First, get the reservation details including numeric ID
    $res_query = "SELECT r.id, r.reservation_id, r.student_id, r.supervisor_id, r.request_date, r.continue_days, r.comment, l.location
                  FROM reservation r
                  JOIN location l ON r.location_id = l.id
                  WHERE r.reservation_id = ?";
    $res_result = Database::search($res_query, "s", [$reservation_display_id]);
    
    if (!$res_result || $res_result->num_rows === 0) {
        throw new Exception('Reservation not found');
    }
    
    $reservation = $res_result->fetch_assoc();
    $numeric_id = $reservation['id'];
    
    // Get student details
    $student_query = "SELECT first_name, last_name, email, university_id FROM lab_user WHERE id = ?";
    $student_result = Database::search($student_query, "i", [$reservation['student_id']]);
    $student_name = '';
    $student_email = '';
    $student_university_id = '';
    if ($student_result && $student_result->num_rows > 0) {
        $student_data = $student_result->fetch_assoc();
        $student_name = $student_data['first_name'] . ' ' . $student_data['last_name'];
        $student_email = $student_data['email'];
        $student_university_id = $student_data['university_id'];
    }
    
    // Get supervisor details if exists
    $supervisor_name = '';
    $supervisor_email = '';
    if ($reservation['supervisor_id']) {
        $supervisor_query = "SELECT first_name, last_name, email FROM lab_user WHERE id = ?";
        $supervisor_result = Database::search($supervisor_query, "i", [$reservation['supervisor_id']]);
        if ($supervisor_result && $supervisor_result->num_rows > 0) {
            $supervisor_data = $supervisor_result->fetch_assoc();
            $supervisor_name = $supervisor_data['first_name'] . ' ' . $supervisor_data['last_name'];
            $supervisor_email = $supervisor_data['email'];
        }
    }
    
    // Get HODs (users with role_id = 5 or role = 'hod')
    $hod_query = "SELECT lu.id, lu.first_name, lu.last_name, lu.email 
                  FROM lab_user lu
                  JOIN lab_user_has_role lur ON lu.id = lur.lab_user_id
                  JOIN role r ON lur.role_id = r.id
                  WHERE r.role = 'hod' OR r.role = 'HOD'";
    $hod_result = Database::search($hod_query);
    
    $hod_list = [];
    if ($hod_result && $hod_result->num_rows > 0) {
        while ($hod = $hod_result->fetch_assoc()) {
            $hod_list[] = [
                'id' => $hod['id'],
                'name' => $hod['first_name'] . ' ' . $hod['last_name'],
                'email' => $hod['email']
            ];
        }
    }
    
    // Get equipment list for this reservation
    $equipment_query = "SELECT e.id, e.name, e.code, be.book_qty 
                        FROM book_equipment be
                        JOIN equipment e ON be.equipment_id = e.id
                        WHERE be.reservation_id = ?";
    $equipment_result = Database::search($equipment_query, "i", [$numeric_id]);
    
    $equipment_list = [];
    $equipment_html = '';
    if ($equipment_result && $equipment_result->num_rows > 0) {
        while ($row = $equipment_result->fetch_assoc()) {
            $equipment_list[] = $row;
            $equipment_html .= "<tr>
                <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'>{$row['name']}</td>
                <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'>{$row['code']}</td>
                <td style='padding: 8px; border-bottom: 1px solid #e0e0e0; text-align: center;'>{$row['book_qty']}</td>
            </tr>";
        }
    }
    
    // Get ALL equipment for this reservation from database (for validation)
    $equipment_query = "SELECT id, equipment_id, book_qty 
                        FROM book_equipment 
                        WHERE reservation_id = ?";
    $equipment_result = Database::search($equipment_query, "i", [$numeric_id]);
    
    $db_equipment = [];
    $db_equipment_ids = [];
    if ($equipment_result && $equipment_result->num_rows > 0) {
        while ($row = $equipment_result->fetch_assoc()) {
            $db_equipment[$row['id']] = [
                'equipment_id' => $row['equipment_id'],
                'quantity' => $row['book_qty']
            ];
            $db_equipment_ids[] = $row['id'];
        }
    }
    
    // SECURITY CHECK 1: Verify all equipment items are checked
    $checked_ids = array_column($checked_equipment, 'book_equipment_id');
    
    // Check if any database equipment is missing from checked list
    $missing_items = array_diff($db_equipment_ids, $checked_ids);
    if (!empty($missing_items)) {
        throw new Exception('All equipment must be checked before submission');
    }
    
    // SECURITY CHECK 2: Verify no extra items are submitted
    $extra_items = array_diff($checked_ids, $db_equipment_ids);
    if (!empty($extra_items)) {
        throw new Exception('Invalid equipment items detected');
    }
    
    // SECURITY CHECK 3: Validate each checked item matches database
    foreach ($checked_equipment as $item) {
        $book_equipment_id = $item['book_equipment_id'];
        
        // Check if this item exists in database
        if (!isset($db_equipment[$book_equipment_id])) {
            throw new Exception('Invalid equipment item detected');
        }
        
        // Verify equipment_id matches
        if ($db_equipment[$book_equipment_id]['equipment_id'] != $item['equipment_id']) {
            throw new Exception('Equipment ID mismatch detected');
        }
        
        // Verify quantity matches
        if ($db_equipment[$book_equipment_id]['quantity'] != $item['quantity']) {
            throw new Exception('Equipment quantity mismatch detected');
        }
    }
    
    // If we get here, all validation passed
    // Update the reservation status
    $update_query = "UPDATE reservation 
                     SET technical_officer_id = ?, 
                         updated_details_by_student = NOW() 
                     WHERE id = ? AND technical_officer_id IS NULL";
    $update_success = Database::iud($update_query, "ii", [$technical_officer_id, $numeric_id]);
    
    if (!$update_success) {
        throw new Exception('Failed to update reservation status');
    }
    
    // Calculate date range
    $start_date = date('Y-m-d', strtotime($reservation['request_date']));
    $end_date = date('Y-m-d', strtotime($reservation['request_date'] . ' + ' . ($reservation['continue_days'] - 1) . ' days'));
    $date_range = $start_date;
    if ($reservation['continue_days'] > 1) {
        $date_range .= " to " . $end_date . " (" . $reservation['continue_days'] . " days)";
    }
    
    // ========== 1. CREATE NOTIFICATIONS ==========
    
    // Notification for student
    $notif_message = "Your reservation " . $reservation_display_id . " has been checked by Technical Officer " . $technical_officer_name . ".";
    $notif_query = "INSERT INTO notification (description, created_datetime, owner_of_notification) 
                    VALUES (?, NOW(), ?)";
    Database::iud($notif_query, "si", [$notif_message, $reservation['student_id']]);
    
    // Notification for supervisor (if exists)
    if ($reservation['supervisor_id']) {
        $notif_message = "Reservation " . $reservation_display_id . " for student " . $student_name . " has been checked by Technical Officer " . $technical_officer_name . ".";
        Database::iud($notif_query, "si", [$notif_message, $reservation['supervisor_id']]);
    }
    
    // Notifications for all HODs
    foreach ($hod_list as $hod) {
        $notif_message = "Reservation " . $reservation_display_id . " for student " . $student_name . " has been checked by Technical Officer " . $technical_officer_name . ".";
        Database::iud($notif_query, "si", [$notif_message, $hod['id']]);
    }
    
    // ========== 2. SEND EMAILS ==========
    
    // Get base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? '100.27.246.223';
   $base_url = $protocol . $host . '/';  // FIXED - remove /LRRS/
    
    // Send email to student
    if (!empty($student_email)) {
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
            $mail->addAddress($student_email, $student_name);
            $mail->addReplyTo('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Reservation Checked - ' . $reservation_display_id;

            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Reservation Checked</title>
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
                        <h2>✓ Reservation Checked</h2>
                        <p style='margin: 10px 0 0; opacity: 0.9;'>Microbiology Lab System</p>
                    </div>
                    
                    <div class='content'>
                        <h3 style='color: #166534; margin-top: 0;'>Dear {$student_name},</h3>
                        
                        <p>Your reservation has been <strong style='color: #22c55e;'>checked and verified</strong> by the Technical Officer.</p>
                        
                        <div class='info-box'>
                            <h4 style='color: #166534; margin-top: 0; border-bottom: 2px solid #22c55e; padding-bottom: 10px;'>Reservation Details</h4>
                            <table>
                                <tr><td class='label'>Reservation ID:</td><td><strong>{$reservation_display_id}</strong></td></tr>
                                <tr><td class='label'>Date(s):</td><td><strong>{$date_range}</strong></td></tr>
                                <tr><td class='label'>Lab Location:</td><td>{$reservation['location']}</td></tr>
                                " . ($reservation['comment'] ? "<tr><td class='label'>Comment:</td><td><em>{$reservation['comment']}</em></td></tr>" : "") . "
                                <tr><td class='label'>Technical Officer:</td><td>{$technical_officer_name}</td></tr>
                            </table>
                        </div>
                        
                        <div class='info-box'>
                            <h4 style='color: #166534; margin-top: 0; border-bottom: 2px solid #22c55e; padding-bottom: 10px;'>Equipment Used</h4>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <thead>
                                    <tr style='background: #f0f0f0;'>
                                        <th style='padding: 10px; text-align: left;'>Equipment Name</th>
                                        <th style='padding: 10px; text-align: left;'>Code</th>
                                        <th style='padding: 10px; text-align: center;'>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$equipment_html}
                                </tbody>
                            </table>
                        </div>
                        
                        <div style='text-align: center;'>
                            <a href='{$base_url}views/student.php' class='button'>
                                <i style='font-style: normal; margin-right: 8px;'>📋</i> View Your Dashboard
                            </a>
                        </div>
                        
                        <p style='color: #718096; font-size: 14px; margin-top: 20px;'>
                            If you have any questions, please contact the lab administration.
                        </p>
                    </div>
                    
                    <div class='footer'>
                        <p><strong>Microbiology Laboratory System</strong></p>
                        <p>Faculty of Science, University of Kelaniya</p>
                        <p>This is an automated notification. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            // Plain text alternative
            $equipment_list_text = "";
            foreach ($equipment_list as $eq) {
                $equipment_list_text .= "- {$eq['name']} ({$eq['code']}) x {$eq['book_qty']}\n";
            }

            $mail->AltBody = "Dear {$student_name},\n\n" .
                            "Your reservation has been checked and verified by the Technical Officer.\n\n" .
                            "Reservation Details:\n" .
                            "Reservation ID: {$reservation_display_id}\n" .
                            "Date(s): {$date_range}\n" .
                            "Lab Location: {$reservation['location']}\n" .
                            ($reservation['comment'] ? "Comment: {$reservation['comment']}\n" : "") .
                            "Technical Officer: {$technical_officer_name}\n\n" .
                            "Equipment Used:\n{$equipment_list_text}\n\n" .
                            "View your dashboard at: {$base_url}views/student.php\n\n" .
                            "Microbiology Laboratory System\n" .
                            "Faculty of Science, University of Kelaniya";

            $mail->send();
            error_log("Checked email sent to student: " . $student_email);
            
        } catch (Exception $e) {
            error_log("Failed to send email to student: " . $mail->ErrorInfo);
            // Don't fail the transaction if email fails
        }
    }
    

    echo json_encode([
        'success' => true,
        'message' => 'Equipment checked successfully. Notifications sent.',
        'checked_count' => count($checked_equipment)
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    Database::iud("ROLLBACK");
    error_log("Submit checked equipment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
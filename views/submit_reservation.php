<?php
session_start();
require_once '../config/database.php';
// Include PHPMailer
require_once '../controllers/Exception.php';
require_once '../controllers/PHPMailer.php';
require_once '../controllers/SMTP.php';
 $env = parse_ini_file(__DIR__ . '/../.env');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Check if user is logged in and is student
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$student_id = $_SESSION["user_id"];

// Get POST data
$location_id = $_POST['location_id'] ?? 0;
$request_date = $_POST['request_date'] ?? '';
$continue_days = $_POST['continue_days'] ?? 1;
$comment = $_POST['comment'] ?? '';
$equipment_json = $_POST['equipment'] ?? '[]';

// Log the received data for debugging
error_log("Equipment JSON received: " . $equipment_json);

// Validate required fields
if (!$location_id || !$request_date) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Parse equipment JSON
$equipment = json_decode($equipment_json, true);
if (empty($equipment)) {
    echo json_encode(['success' => false, 'message' => 'No equipment selected']);
    exit();
}

// Get student's supervisor
$supervisor_query = "SELECT supervisor_id_or_hod_id FROM supervisor_assigned_student WHERE student_id = ? LIMIT 1";
$supervisor_result = Database::search($supervisor_query, "i", [$student_id]);
$supervisor_id = null;
if ($supervisor_result && $supervisor_result->num_rows > 0) {
    $supervisor_data = $supervisor_result->fetch_assoc();
    $supervisor_id = $supervisor_data['supervisor_id_or_hod_id'];
}

if (!$supervisor_id) {
    echo json_encode(['success' => false, 'message' => 'No supervisor assigned. Please contact HOD.']);
    exit();
}

// Get supervisor email and name (we'll use these after reservation is created)
$supervisor_email_query = "SELECT email, first_name, last_name FROM lab_user WHERE id = ?";
$supervisor_email_result = Database::search($supervisor_email_query, "i", [$supervisor_id]);
$supervisor_email = '';
$supervisor_name = '';
if ($supervisor_email_result && $supervisor_email_result->num_rows > 0) {
    $sup_data = $supervisor_email_result->fetch_assoc();
    $supervisor_email = $sup_data['email'];
    $supervisor_name = $sup_data['first_name'] . ' ' . $sup_data['last_name'];
}

// Get student details for email
$student_query = "SELECT first_name, last_name, email, university_id FROM lab_user WHERE id = ?";
$student_result = Database::search($student_query, "i", [$student_id]);
$student_name = '';
$student_email = '';
$student_university_id = '';
if ($student_result && $student_result->num_rows > 0) {
    $stu_data = $student_result->fetch_assoc();
    $student_name = $stu_data['first_name'] . ' ' . $stu_data['last_name'];
    $student_email = $stu_data['email'];
    $student_university_id = $stu_data['university_id'];
}

// Generate unique reservation ID (format: RES-YYYY-XXXX)
$year = date('Y');
$count_query = "SELECT COUNT(*) as count FROM reservation WHERE reservation_id LIKE 'RES-$year%'";
$count_result = Database::search($count_query);
$count_row = $count_result->fetch_assoc();
$count = ($count_row['count'] ?? 0) + 1;
$reservation_id = 'RES-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

// Calculate end date for availability check
$end_date = date('Y-m-d', strtotime($request_date . ' + ' . ($continue_days - 1) . ' days'));

error_log("Submitting reservation: reservation_id=$reservation_id, student_id=$student_id, supervisor_id=$supervisor_id, location_id=$location_id, date=$request_date, days=$continue_days");

try {
   

// Insert into reservation table - 7 columns, 7 values
$insert_query = "INSERT INTO reservation 
                (reservation_id, created_datetime, student_id, 
                 location_id, request_date, continue_days, comment) 
                VALUES (?, NOW(), ?, ?, ?, ?, ?)";

$insert_success = Database::iud($insert_query, "siisis", [
    $reservation_id,  // 1st - string
    $student_id,      // 2nd - int
    $location_id,     // 3rd - int
    $request_date,    // 4th - string
    $continue_days,   // 5th - int
    $comment          // 6th - string
]);

    if (!$insert_success) {
        throw new Exception('Failed to create reservation: ' . Database::getLastError());
    }

    // Get the reservation ID from database using the generated reservation_id
    $find_query = "SELECT id FROM reservation WHERE reservation_id = ? AND student_id = ? ORDER BY created_datetime DESC LIMIT 1";
    $find_result = Database::search($find_query, "si", [$reservation_id, $student_id]);

    if (!$find_result || $find_result->num_rows === 0) {
        throw new Exception('Failed to find created reservation');
    }

    $reservation_row = $find_result->fetch_assoc();
    $reservation_db_id = $reservation_row['id'];

    error_log("Reservation created with DB ID: " . $reservation_db_id . " for reservation_id: " . $reservation_id);

    // Insert each equipment item into book_equipment table
    foreach ($equipment as $item) {
        // Check availability for the selected dates
        $check_query = "SELECT e.total_qty,
                       COALESCE((SELECT SUM(broken_qty) FROM broken WHERE equipment_id = e.id), 0) as broken_qty,
                       COALESCE((SELECT SUM(repair_qty) FROM repair WHERE equipment_id = e.id), 0) as repair_qty,
                       COALESCE((SELECT SUM(be.book_qty) FROM book_equipment be 
                                JOIN reservation r ON be.reservation_id = r.id 
                                WHERE be.equipment_id = e.id 
                                AND r.request_date <= ? 
                                AND DATE_ADD(r.request_date, INTERVAL (r.continue_days - 1) DAY) >= ?), 0) as booked_qty
                       FROM equipment e WHERE e.id = ?";

        $check_result = Database::search($check_query, "ssi", [$end_date, $request_date, $item['id']]);

        if (!$check_result) {
            throw new Exception('Failed to check availability for equipment ID: ' . $item['id']);
        }

        $check_row = $check_result->fetch_assoc();
        $available = $check_row['total_qty'] - $check_row['broken_qty'] - $check_row['repair_qty'] - $check_row['booked_qty'];

        if ($available < $item['qty']) {
            throw new Exception("Insufficient quantity for equipment: " . $item['name'] . ". Only $available available for the selected dates.");
        }

        // Insert into book_equipment table
        $book_query = "INSERT INTO book_equipment (book_qty, reservation_id, equipment_id) VALUES (?, ?, ?)";
        $book_success = Database::iud($book_query, "iii", [$item['qty'], $reservation_db_id, $item['id']]);

        if (!$book_success) {
            throw new Exception('Failed to book equipment: ' . $item['name'] . ' - ' . Database::getLastError());
        }

        error_log("Equipment booked: ID=" . $item['id'] . ", Qty=" . $item['qty'] . ", Reservation DB ID=" . $reservation_db_id);
    }

    // Create notification for supervisor
    $notif_message = "New reservation submitted by student. Reservation ID: $reservation_id for " . $continue_days . " day(s) starting " . $request_date;
    $notif_query = "INSERT INTO notification (description, created_datetime, owner_of_notification) VALUES (?, NOW(), ?)";
    $notif_success = Database::iud($notif_query, "si", [$notif_message, $supervisor_id]);

    if (!$notif_success) {
        error_log("Failed to create notification: " . Database::getLastError());
        // Don't throw exception, just log it - notification is not critical
    }

   
  

    // ============ SEND EMAIL TO SUPERVISOR ============
    if ($supervisor_email) {

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

        $mail->setFrom($env["MAIL_USERNAME"], "Microbiology Lab System");
            $mail->addAddress($supervisor_email, $supervisor_name);
            $mail->addReplyTo('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'New Reservation Approval Request - ' . $reservation_id;

            // Get base URL
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base_url = $protocol . $host . '/LRRS/';

            // Build equipment list for email
            $equipment_list_html = '';
            foreach ($equipment as $item) {
                $equipment_list_html .= "<tr>
                    <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'>{$item['name']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'>{$item['code']}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e0e0e0; text-align: center;'>{$item['qty']}</td>
                </tr>";
            }

            $start_date = date('Y-m-d', strtotime($request_date));
            $end_date = date('Y-m-d', strtotime($request_date . ' + ' . ($continue_days - 1) . ' days'));
            $date_display = $start_date;
            if ($continue_days > 1) {
                $date_display .= " to " . $end_date . " (" . $continue_days . " days)";
            }

            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Reservation Approval Request</title>
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
                        <h2>🔬 New Reservation Request</h2>
                        <p style='margin: 10px 0 0; opacity: 0.9;'>Microbiology Lab System</p>
                    </div>
                    
                    <div class='content'>
                        <h3 style='color: #166534; margin-top: 0;'>Dear {$supervisor_name},</h3>
                        
                        <p>A new reservation request requires your approval. Please review the details below:</p>
                        
                        <div class='info-box'>
                            <h4 style='color: #166534; margin-top: 0; border-bottom: 2px solid #22c55e; padding-bottom: 10px;'>Reservation Details</h4>
                            <table>
                                <tr><td class='label'>Reservation ID:</td><td><strong>{$reservation_id}</strong></td></tr>
                                <tr><td class='label'>Date(s):</td><td><strong>{$date_display}</strong></td></tr>
                                <tr><td class='label'>Student:</td><td>{$student_name} ({$student_university_id})</td></tr>
                                <tr><td class='label'>Student Email:</td><td>{$student_email}</td></tr>
                                " . ($comment ? "<tr><td class='label'>Comments:</td><td><em>{$comment}</em></td></tr>" : "") . "
                            </table>
                        </div>
                        
                        <div class='info-box'>
                            <h4 style='color: #166534; margin-top: 0; border-bottom: 2px solid #22c55e; padding-bottom: 10px;'>Requested Equipment</h4>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <thead>
                                    <tr style='background: #f0f0f0;'>
                                        <th style='padding: 10px; text-align: left;'>Equipment Name</th>
                                        <th style='padding: 10px; text-align: left;'>Code</th>
                                        <th style='padding: 10px; text-align: center;'>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$equipment_list_html}
                                </tbody>
                            </table>
                        </div>
                        
                        <div style='text-align: center;'>
                            <a href='{$base_url}views/supervisor.php' class='button'>
                                <i style='font-style: normal; margin-right: 8px;'>📋</i> Review in Dashboard
                            </a>
                        </div>
                        
                        <p style='color: #718096; font-size: 14px; margin-top: 20px;'>
                            Please log in to your dashboard to approve or reject this request.
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
            foreach ($equipment as $item) {
                $equipment_list_text .= "- {$item['name']} ({$item['code']}) x {$item['qty']}\n";
            }

            $mail->AltBody = "Dear {$supervisor_name},\n\n" .
                "A new reservation request requires your approval.\n\n" .
                "Reservation Details:\n" .
                "Reservation ID: {$reservation_id}\n" .
                "Date(s): {$date_display}\n" .
                "Student: {$student_name} ({$student_university_id})\n" .
                "Student Email: {$student_email}\n" .
                ($comment ? "Comments: {$comment}\n\n" : "\n") .
                "Requested Equipment:\n{$equipment_list_text}\n\n" .
                "Please log in to your dashboard to review and respond to this request.\n\n" .
                "Microbiology Laboratory System\n" .
                "Faculty of Science, University of Kelaniya";

            $mail->send();
            error_log("Approval request email sent to supervisor: " . $supervisor_email);
        } catch (Exception $e) {
            error_log("Failed to send email to supervisor: " . $mail->ErrorInfo);
            // Don't fail the reservation if email fails - just log it
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Reservation submitted successfully',
        'reservation_id' => $reservation_id,
        'db_id' => $reservation_db_id
    ]);
} catch (Exception $e) {
    
  
    error_log("Reservation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

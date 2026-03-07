<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Include database connection
require_once "../config/database.php";

require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

// Response array
$response = [
    'status_user' => 'error',
    'message' => '',
    'fields' => []
];

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and sanitize all inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $university_id = trim($_POST['university_id'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_email = trim($_POST['role_email'] ?? '');
    $user_type = trim($_POST['user_type'] ?? '');
    $supervisor_id = null;

    // Validate User Type
    if (empty($user_type)) {
        $response['fields'][] = ['name' => 'user_type', 'message' => 'User type is required'];
    } else {
        // Map user_type to role name for database lookup
        $role_name = '';
        switch ($user_type) {
            case 'student':
                $role_name = 'Student';
                break;
            case 'supervisor':
                $role_name = 'Supervisor';
                break;
            case 'technical_officer':
                $role_name = 'Technical Officer';
                break;
            default:
                $response['fields'][] = ['name' => 'user_type', 'message' => 'Invalid user type'];
        }
    }

    // Validate First Name
    if (empty($first_name)) {
        $response['fields'][] = ['name' => 'first_name', 'message' => 'First name is required'];
    } elseif (strlen($first_name) > 50) {
        $response['fields'][] = ['name' => 'first_name', 'message' => 'First name must be less than 50 characters'];
    } elseif (!preg_match("/^[a-zA-Z\s\-']+$/", $first_name)) {
        $response['fields'][] = ['name' => 'first_name', 'message' => 'First name contains invalid characters'];
    }

    // Validate Last Name
    if (empty($last_name)) {
        $response['fields'][] = ['name' => 'last_name', 'message' => 'Last name is required'];
    } elseif (strlen($last_name) > 50) {
        $response['fields'][] = ['name' => 'last_name', 'message' => 'Last name must be less than 50 characters'];
    } elseif (!preg_match("/^[a-zA-Z\s\-']+$/", $last_name)) {
        $response['fields'][] = ['name' => 'last_name', 'message' => 'Last name contains invalid characters'];
    }

    // Validate University ID
    if (empty($university_id)) {
        $response['fields'][] = ['name' => 'university_id', 'message' => 'University ID is required'];
    } elseif (strlen($university_id) > 15) {
        $response['fields'][] = ['name' => 'university_id', 'message' => 'University ID must be less than 15 characters'];
    } elseif (strlen($university_id) < 3) {
        $response['fields'][] = ['name' => 'university_id', 'message' => 'University ID must be at least 3 characters'];
    }

    // Validate Mobile
    if (empty($mobile)) {
        $response['fields'][] = ['name' => 'mobile', 'message' => 'Mobile number is required'];
    } elseif (!preg_match("/^(07[0-9]{8})$/", $mobile)) {
        $response['fields'][] = ['name' => 'mobile', 'message' => 'Invalid mobile number format. Use 07XXXXXXXX'];
    }

    // Validate Email
    if (empty($email)) {
        $response['fields'][] = ['name' => 'email', 'message' => 'Email address is required'];
    } elseif (strlen($email) > 100) {
        $response['fields'][] = ['name' => 'email', 'message' => 'Email must be less than 100 characters'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['fields'][] = ['name' => 'email', 'message' => 'Invalid email address format'];
    }

    // Validate Password
    if (empty($password)) {
        $response['fields'][] = ['name' => 'password', 'message' => 'Password is required'];
    } elseif (strlen($password) < 8) {
        $response['fields'][] = ['name' => 'password', 'message' => 'Password must be at least 8 characters long'];
    } elseif (strlen($password) > 20) {
        $response['fields'][] = ['name' => 'password', 'message' => 'Password must be less than 20 characters'];
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $response['fields'][] = ['name' => 'password', 'message' => 'Password must contain at least one uppercase, one lowercase, one number, and one special character'];
    }

    // Validate Supervisor Email (only for students)
    if ($user_type === 'student') {
        if (empty($role_email)) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'Supervisor email is required'
            ];
        } elseif (strlen($role_email) > 100) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'Supervisor email must be less than 100 characters'
            ];
        } elseif (!filter_var($role_email, FILTER_VALIDATE_EMAIL)) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'Invalid supervisor email format'
            ];
        } else {
            // Check if supervisor exists in lab_user
            $check_supervisor_sql = "SELECT user_id FROM lab_user WHERE email = ?";
            $check_supervisor_result = Database::search($check_supervisor_sql, "s", [$role_email]);

            if ($check_supervisor_result && $check_supervisor_result->num_rows === 0) {
                $response['fields'][] = [
                    'name' => 'role_email',
                    'message' => 'Supervisor email not found in the system.'
                ];
            } else if ($check_supervisor_result) {
                // Get supervisor ID
                $supervisor_row = $check_supervisor_result->fetch_assoc();
                $supervisor_id = $supervisor_row['user_id'];

                // Check if user has supervisor role (role_id = 7)
                $check_role_sql = "SELECT 1 FROM user_has_role WHERE user_id = ? AND role_id = 7";
                $check_role_result = Database::search($check_role_sql, "i", [$supervisor_id]);

                if ($check_role_result && $check_role_result->num_rows === 0) {
                    $response['fields'][] = [
                        'name' => 'role_email',
                        'message' => 'This user is not registered as a supervisor.'
                    ];
                }
            }
        }
    }

    // Validate HOD Email for supervisors and technical officers
    if ($user_type === 'supervisor' || $user_type === 'technical_officer') {
        if (empty($role_email)) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'HOD email is required'
            ];
        } elseif (strlen($role_email) > 100) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'HOD email must be less than 100 characters'
            ];
        } elseif (!filter_var($role_email, FILTER_VALIDATE_EMAIL)) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'Invalid HOD email format'
            ];
        } else {
            // Check if HOD exists in lab_user
            $check_hod_sql = "SELECT user_id FROM lab_user WHERE email = ?";
            $check_hod_result = Database::search($check_hod_sql, "s", [$role_email]);

            if ($check_hod_result && $check_hod_result->num_rows === 0) {
                $response['fields'][] = [
                    'name' => 'role_email',
                    'message' => 'HOD email not found in the system.'
                ];
            } else if ($check_hod_result) {
                // Get HOD ID
                $hod_row = $check_hod_result->fetch_assoc();
                $hod_id = $hod_row['user_id'];

                // Check if user has HOD role (role_id = 6)
                $check_role_sql = "SELECT 1 FROM user_has_role WHERE user_id = ? AND role_id = 6";
                $check_role_result = Database::search($check_role_sql, "i", [$hod_id]);

                if ($check_role_result && $check_role_result->num_rows === 0) {
                    $response['fields'][] = [
                        'name' => 'role_email',
                        'message' => 'This user is not registered as an HOD.'
                    ];
                } else {
                    // Store HOD ID in supervisor_id for the approved_id field
                    $supervisor_id = $hod_id;
                }
            }
        }
    }

    // TEMPORARY DEBUG - Add this
    error_log("User Type: " . $user_type);
    error_log("Role Email: " . $role_email);
    error_log("Validation Fields: " . print_r($response['fields'], true));

    if (!empty($response['fields'])) {
        $response['message'] = 'Please correct the errors';
        echo json_encode($response);
        exit;
    }

    // Check if user already exists
    $check_sql = "SELECT user_id FROM lab_user WHERE email = ? OR mobile = ? OR university_id = ?";
    $check_result = Database::search($check_sql, "sss", [$email, $mobile, $university_id]);

    if ($check_result && $check_result->num_rows > 0) {
        // Check which field(s) already exist
        $check_sql2 = "SELECT email, mobile, university_id FROM lab_user WHERE email = ? OR mobile = ? OR university_id = ?";
        $check_result2 = Database::search($check_sql2, "sss", [$email, $mobile, $university_id]);

        if ($check_result2) {
            while ($row = $check_result2->fetch_assoc()) {
                if ($row['email'] === $email) {
                    $response['fields'][] = ['name' => 'email', 'message' => 'Email address already registered'];
                }
                if ($row['mobile'] === $mobile) {
                    $response['fields'][] = ['name' => 'mobile', 'message' => 'Mobile number already registered'];
                }
                if ($row['university_id'] === $university_id) {
                    $response['fields'][] = ['name' => 'university_id', 'message' => 'University ID already registered'];
                }
            }
        }

        $response['message'] = 'User already exists';
        echo json_encode($response);
        exit;
    }

    // Handle profile image upload
    $profile_image_path = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = handleImageUpload($_FILES['profile_image']);
        if ($upload_result['success']) {
            $profile_image_path = $upload_result['path'];
        } else {
            $response['fields'][] = ['name' => 'profile_image', 'message' => $upload_result['message']];
            $response['message'] = 'Image upload failed';
            echo json_encode($response);
            exit;
        }
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $remember_token = password_hash($university_id . $password, PASSWORD_BCRYPT);
    $verification_code = '000000';

    // Get current date and time
    date_default_timezone_set('Asia/Colombo');
    $joined_date = date('Y-m-d H:i:s');

    // Insert into lab_user
    try {
        $insert_sql_table1 = "INSERT INTO lab_user (
            first_name, last_name, university_id, email, mobile, 
            password_user, img_path, request_status_id, 
            join_datetime, approved_id, verification_code, remember_token, status_user
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $request_status_id = 4; // 4 = pending
        $status_user = 1; // 1 = active

        $insert_success = Database::iud($insert_sql_table1, "sssssssissssi", [
            $first_name,
            $last_name,
            $university_id,
            $email,
            $mobile,
            $hashed_password,
            $profile_image_path,
            $request_status_id,
            $joined_date,
            $supervisor_id,
            $verification_code,
            $remember_token,
            $status_user
        ]);

        if (!$insert_success) {
            throw new Exception('Failed to insert into lab_user');
        }

        $user_id = Database::lastInsertId();

        // Get the correct role_id from database based on user_type
        $role_sql = "SELECT role_id FROM role WHERE role = ?";
        $role_result = Database::search($role_sql, "s", [$role_name]);

        if (!$role_result || $role_result->num_rows === 0) {
            throw new Exception('Invalid role type');
        }

        $role_row = $role_result->fetch_assoc();
        $role_id = $role_row['role_id'];

        // Insert into user_has_role
        $insert_sql_table2 = "INSERT INTO user_has_role (user_id, role_id) VALUES (?, ?)";
        $role_success = Database::iud($insert_sql_table2, "ii", [$user_id, $role_id]);

        if (!$role_success) {
            throw new Exception('Failed to assign user role');
        }

        // ========== FIXED: Send notifications for ALL user types ==========
        
        // For Students: Assign to supervisor and notify
        if ($user_type === 'student' && isset($supervisor_id)) {
            $assign_sql = "INSERT INTO supervisor_assigned (student_id, supervisor_id) VALUES (?, ?)";
            $assign_success = Database::iud($assign_sql, "ii", [$user_id, $supervisor_id]);

            if (!$assign_success) {
                error_log("Failed to assign supervisor to student ID: " . $user_id);
            } else {
                error_log("📧 Sending student notification to supervisor: " . $role_email);
                sendRoleNotification($role_email, $first_name, $last_name, $university_id, $user_type, 'supervisor');
            }
        }
        
        // For Supervisors and Technical Officers: Notify HOD
        elseif (($user_type === 'supervisor' || $user_type === 'technical_officer') && isset($supervisor_id)) {
            error_log("📧 Sending " . $user_type . " notification to HOD: " . $role_email);
            sendRoleNotification($role_email, $first_name, $last_name, $university_id, $user_type, 'hod');
        }

        $response['status_user'] = 'success';
        $response['message'] = 'Account created successfully! Your request has been sent for approval.';
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log("Signup Error: " . $e->getMessage());
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Signup Error: " . $e->getMessage());
}

echo json_encode($response);
exit;

function handleImageUpload($file)
{
    $response = ['success' => false, 'message' => '', 'path' => ''];

    // Allowed file types
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 6 * 1024 * 1024; // 6MB

    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        $response['message'] = 'Only JPG, PNG, GIF, and WEBP images are allowed';
        return $response;
    }

    // Check file size
    if ($file['size'] > $max_size) {
        $response['message'] = 'Image size must be less than 6MB';
        return $response;
    }

    // Create upload directory if it doesn't exist
    $upload_dir = '../assets/profile_images/';

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $response['success'] = true;
        $response['path'] = 'assets/profile_images/' . $filename;
    } else {
        $response['message'] = 'Failed to upload image';
    }

    return $response;
}

// Unified notification function for all roles
function sendRoleNotification($recipient_email, $first_name, $last_name, $user_id, $user_type, $recipient_role)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'microbiologylaboratorysystem@gmail.com';
        $mail->Password   = 'cesb lydd jord elyu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');
        $mail->addAddress($recipient_email);
        $mail->addReplyTo('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');

        $mail->isHTML(true);
        
        // Set subject and content based on recipient role
        if ($recipient_role === 'supervisor') {
            $mail->Subject = 'Student Registration Pending Approval - Microbiology Lab';
            $greeting = 'Dear Supervisor,';
            $role_text = 'student';
            $dashboard_link = 'index.php';
        } else {
            $mail->Subject = ucfirst($user_type) . ' Registration Pending Approval - Microbiology Lab';
            $greeting = 'Dear HOD,';
            $role_text = $user_type;
            $dashboard_link = 'index.php';
        }

        $mail->Body = "
        <html>
        <head>
            <title>Registration Approval Required</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #22c55e; color: white; padding: 15px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { text-align: center; padding: 15px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Microbiology Lab System</h2>
                </div>
                <div class='content'>
                    <h3>{$greeting}</h3>
                    <p>A {$role_text} has registered and requires your approval:</p>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px; background: #e5e7eb;'><strong>Name:</strong></td>
                            <td style='padding: 8px;'>{$first_name} {$last_name}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background: #e5e7eb;'><strong>University ID:</strong></td>
                            <td style='padding: 8px;'>{$user_id}</td>
                        </tr>
                    </table>
                    <p style='margin-top: 20px;'>Please log in to the system to review and approve this registration.</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='http://" . $_SERVER['HTTP_HOST'] . "/LRRS/{$dashboard_link}' 
                           style='background: #22c55e; color: white; padding: 10px 20px; 
                                  text-decoration: none; border-radius: 5px;'>Go to Dashboard</a>
                    </p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Microbiology Lab System<br>University of Kelaniya, Faculty of Science</p>
                    <p>This is an automated notification. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        if ($mail->send()) {
            error_log("✅ Email sent successfully to: " . $recipient_email);
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            error_log("❌ Email failed: " . $mail->ErrorInfo);
            return ['success' => false, 'message' => 'Email sending failed'];
        }
    } catch (Exception $e) {
        error_log("❌ Email exception: " . $e->getMessage());
        return ['success' => false, 'message' => "Mailer Error: " . $mail->ErrorInfo];
    }
}
?>
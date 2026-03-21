<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
  $env = parse_ini_file(__DIR__ . '/../.env');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 for production

// Include database connection
require_once "../config/database.php";

// Check if PHPMailer files exist
$phpmailer_path = __DIR__ . '/PHPMailer.php';
if (!file_exists($phpmailer_path)) {
    die(json_encode(['status_user' => 'error', 'message' => 'PHPMailer.php not found']));
}

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
        $role_name = '';
        switch ($user_type) {
            case 'student':
                $role_name = 'student';
                break;
            case 'supervisor':
                $role_name = 'supervisor';
                break;
            case 'technical_officer':
                $role_name = 'technical_officer';
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

    // If there are validation errors, return them
    if (!empty($response['fields'])) {
        $error_count = count($response['fields']);
        $first_error = $response['fields'][0]['message'] ?? 'Validation failed';
        $response['message'] = "Validation failed (" . $error_count . " error" . ($error_count > 1 ? "s" : "") . "): " . $first_error;
        echo json_encode($response);
        exit;
    }

    // Check if user already exists
    $check_sql = "SELECT id, email, mobile, university_id FROM lab_user WHERE email = ? OR mobile = ? OR university_id = ?";
    $check_result = Database::search($check_sql, "sss", [$email, $mobile, $university_id]);

    if ($check_result && $check_result->num_rows > 0) {
        while ($row = $check_result->fetch_assoc()) {
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

        $response['message'] = 'User already exists';
        echo json_encode($response);
        exit;
    }

    // Get lists of valid emails for better error messages
    $supervisor_emails = [];
    $hod_emails = [];
    
    // Get all supervisors
    $sup_query = "SELECT l.email, l.first_name, l.last_name FROM lab_user l 
                  JOIN lab_user_has_role r ON l.id = r.lab_user_id 
                  WHERE r.role_id = 2";
    $sup_result = Database::search($sup_query, "");
    if ($sup_result) {
        while ($sup = $sup_result->fetch_assoc()) {
            $supervisor_emails[] = $sup['email'] . ' (' . $sup['first_name'] . ' ' . $sup['last_name'] . ')';
        }
    }

    // Get all HODs
    $hod_query = "SELECT l.email, l.first_name, l.last_name FROM lab_user l 
                  JOIN lab_user_has_role r ON l.id = r.lab_user_id 
                  WHERE r.role_id = 4";
    $hod_result = Database::search($hod_query, "");
    if ($hod_result) {
        while ($hod = $hod_result->fetch_assoc()) {
            $hod_emails[] = $hod['email'] . ' (' . $hod['first_name'] . ' ' . $hod['last_name'] . ')';
        }
    }

    // Validate Role Email based on user type with clear user-friendly messages
    if ($user_type === 'student') {
        if (empty($role_email)) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'Supervisor email is required. Please enter your supervisor\'s email address.'
            ];
        } elseif (!filter_var($role_email, FILTER_VALIDATE_EMAIL)) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'Invalid email format. Please enter a valid supervisor email address.'
            ];
        } else {
            // Check if the email belongs to a supervisor
            $check_supervisor_sql = "SELECT l.id FROM lab_user l 
                                    JOIN lab_user_has_role r ON l.id = r.lab_user_id 
                                    WHERE l.email = ? AND r.role_id = 2";
            $check_supervisor_result = Database::search($check_supervisor_sql, "s", [$role_email]);

            if (!$check_supervisor_result || $check_supervisor_result->num_rows === 0) {
                // Check if it exists but is not a supervisor
                $check_any_user = Database::search("SELECT id FROM lab_user WHERE email = ?", "s", [$role_email]);
                if ($check_any_user && $check_any_user->num_rows > 0) {
                    $response['fields'][] = [
                        'name' => 'role_email',
                        'message' => 'This email exists but is not registered as a supervisor. Students must select a valid supervisor email.'
                    ];
                } else {
                    $sup_list = !empty($supervisor_emails) ? ' Available supervisors: ' . implode(', ', $supervisor_emails) : '';
                    $response['fields'][] = [
                        'name' => 'role_email',
                        'message' => 'Supervisor email not found in the system.' . $sup_list
                    ];
                }
            } else {
                $supervisor_row = $check_supervisor_result->fetch_assoc();
                $supervisor_id = $supervisor_row['id'];
            }
        }
    }

    // Validate HOD Email for supervisors and technical officers
    if ($user_type === 'supervisor' || $user_type === 'technical_officer') {
        if (empty($role_email)) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'HOD email is required. Please enter the Head of Department\'s email address.'
            ];
        } elseif (!filter_var($role_email, FILTER_VALIDATE_EMAIL)) {
            $response['fields'][] = [
                'name' => 'role_email',
                'message' => 'Invalid email format. Please enter a valid HOD email address.'
            ];
        } else {
            // Check if the email belongs to an HOD
            $check_hod_sql = "SELECT l.id FROM lab_user l 
                            JOIN lab_user_has_role r ON l.id = r.lab_user_id 
                            WHERE l.email = ? AND r.role_id = 4";
            $check_hod_result = Database::search($check_hod_sql, "s", [$role_email]);

            if (!$check_hod_result || $check_hod_result->num_rows === 0) {
                // Check if it exists but is not an HOD
                $check_any_user = Database::search("SELECT id FROM lab_user WHERE email = ?", "s", [$role_email]);
                if ($check_any_user && $check_any_user->num_rows > 0) {
                    $response['fields'][] = [
                        'name' => 'role_email',
                        'message' => 'This email exists but is not registered as an HOD. ' . ucfirst($user_type) . 's must be approved by the HOD.'
                    ];
                } else {
                    $hod_list = !empty($hod_emails) ? ' Available HODs: ' . implode(', ', $hod_emails) : '';
                    $response['fields'][] = [
                        'name' => 'role_email',
                        'message' => 'HOD email not found in the system.' . $hod_list
                    ];
                }
            } else {
                $hod_row = $check_hod_result->fetch_assoc();
                $supervisor_id = $hod_row['id'];
            }
        }
    }

    // If there are validation errors from role validation, return them
    if (!empty($response['fields'])) {
        $error_count = count($response['fields']);
        $first_error = $response['fields'][0]['message'] ?? 'Validation failed';
        $response['message'] = "Validation error (" . $error_count . " issue" . ($error_count > 1 ? "s" : "") . "): " . $first_error;
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
    if ($hashed_password === false) {
        throw new Exception('Password hashing failed');
    }
    
    // Generate and hash the remember token (university_id + password)
    $token_data = $university_id . $password;
    $remember_token = password_hash($token_data, PASSWORD_BCRYPT);
    if ($remember_token === false) {
        throw new Exception('Token hashing failed');
    }
    
    $verification_code = '000000';

    // Get current date and time
    date_default_timezone_set('Asia/Colombo');
    $joined_date = date('Y-m-d H:i:s');

    // If supervisor_id is still null (should not happen at this point), use a default
    if ($supervisor_id === null) {
        // Get first HOD as default
        $default_hod = Database::search("SELECT id FROM lab_user WHERE id IN (SELECT lab_user_id FROM lab_user_has_role WHERE role_id = 4) LIMIT 1", "");
        if ($default_hod && $default_hod->num_rows > 0) {
            $hod_row = $default_hod->fetch_assoc();
            $supervisor_id = $hod_row['id'];
        } else {
            $supervisor_id = 1; // Fallback to ID 1
        }
    }

    // Insert into lab_user
    try {
        $insert_sql_table1 = "INSERT INTO lab_user (
            who_approved, first_name, last_name, university_id, email, mobile,
            password_user, img_path, join_datetime, verification_code, 
            remember_token, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $status = 0; 

        $insert_success = Database::iud($insert_sql_table1, "issssssssssi", [
            $supervisor_id,
            $first_name,
            $last_name,
            $university_id,
            $email,
            $mobile,
            $hashed_password,
            $profile_image_path,
            $joined_date,
            $verification_code,
            $remember_token,
            $status
        ]);

        if (!$insert_success) {
            throw new Exception('Failed to insert into lab_user');
        }

        // Get the last insert ID
        $last_id_sql = "SELECT LAST_INSERT_ID() as id";
        $last_id_result = Database::search($last_id_sql, "");
        if (!$last_id_result || $last_id_result->num_rows === 0) {
            throw new Exception('Could not retrieve last insert ID');
        }
        $last_id_row = $last_id_result->fetch_assoc();
        $user_id = $last_id_row['id'];

        // Get role ID
        $role_sql = "SELECT id FROM role WHERE role = ?";
        $role_result = Database::search($role_sql, "s", [$role_name]);

        if (!$role_result || $role_result->num_rows === 0) {
            throw new Exception('Invalid role type: ' . $role_name);
        }

        $role_row = $role_result->fetch_assoc();
        $role_id = $role_row['id'];

        // Assign role to user
        $insert_sql_table2 = "INSERT INTO lab_user_has_role (lab_user_id, role_id) VALUES (?, ?)";
        $role_success = Database::iud($insert_sql_table2, "ii", [$user_id, $role_id]);

        if (!$role_success) {
            throw new Exception('Failed to assign user role');
        }

        // For Students: Assign to supervisor
        if ($user_type === 'student' && $supervisor_id > 0) {
            $assign_sql = "INSERT INTO supervisor_assigned_student (student_id, supervisor_id_or_hod_id) VALUES (?, ?)";
            $assign_success = Database::iud($assign_sql, "ii", [$user_id, $supervisor_id]);

            if (!$assign_success) {
                error_log("Failed to assign supervisor to student ID: " . $user_id);
            }
        }

        // Send email notifications
        if ($user_type === 'student' && !empty($role_email)) {
            sendRoleNotification($role_email, $first_name, $last_name, $university_id, $user_type, 'supervisor');
        } elseif (($user_type === 'supervisor' || $user_type === 'technical_officer') && !empty($role_email)) {
            sendRoleNotification($role_email, $first_name, $last_name, $university_id, $user_type, 'hod');
        }

        $response['status_user'] = 'success';
        $response['message'] = 'Account created successfully! Your request has been sent for approval.';
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Signup Database Error: " . $e->getMessage());
    }
} catch (Exception $e) {
    $response['message'] = 'System error: ' . $e->getMessage();
    error_log("Signup System Error: " . $e->getMessage());
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
    $upload_dir = __DIR__ . '/../assets/profile_images/';
    
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            $response['message'] = 'Failed to create upload directory';
            return $response;
        }
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
        $error = error_get_last();
        $response['message'] = 'Failed to upload image: ' . ($error['message'] ?? 'Unknown error');
    }

    return $response;
}

// Unified notification function for all roles
function sendRoleNotification($recipient_email, $first_name, $last_name, $user_id, $user_type, $recipient_role)
{
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
        $mail->addAddress($recipient_email);
        $mail->addReplyTo($env["MAIL_USERNAME"], "Microbiology Lab System");
        
        // Content
        $mail->isHTML(true);

        // Set subject and content based on recipient role
        if ($recipient_role === 'supervisor') {
            $mail->Subject = 'Student Registration Pending Approval - Microbiology Lab';
            $greeting = 'Dear Supervisor,';
            $role_text = 'student';
        } else {
            $mail->Subject = ucfirst($user_type) . ' Registration Pending Approval - Microbiology Lab';
            $greeting = 'Dear HOD,';
            $role_text = $user_type;
        }

        // Get base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '100.27.246.223';
       $base_url = $protocol . $host . '/';  // FIXED - remove /LRRS/

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
                        <a href='{$base_url}login.php' 
                           style='background: #22c55e; color: white; padding: 10px 20px; 
                                  text-decoration: none; border-radius: 5px;'>Login to Dashboard</a>
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

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}
?>
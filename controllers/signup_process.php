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
    $supervisor_email = trim($_POST['supervisor_email'] ?? '');

    // Validate reCAPTCHA if enabled
    if (isset($_POST['recaptcha_token'])) {
        if (!verifyRecaptcha($_POST['recaptcha_token'])) {
            throw new Exception('reCAPTCHA verification failed');
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
    } elseif (strlen($university_id) > 20) {
        $response['fields'][] = ['name' => 'university_id', 'message' => 'University ID must be less than 20 characters'];
    } elseif (strlen($university_id) < 5) {
        $response['fields'][] = ['name' => 'university_id', 'message' => 'University ID must be at least 5 characters'];
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

    // Validate Supervisor Email (Required Field)
    if (empty($supervisor_email)) {
        $response['fields'][] = [
            'name' => 'supervisor_email',
            'message' => 'Supervisor email is required'
        ];
    } elseif (strlen($supervisor_email) > 100) {
        $response['fields'][] = [
            'name' => 'supervisor_email',
            'message' => 'Supervisor email must be less than 100 characters'
        ];
    } elseif (!filter_var($supervisor_email, FILTER_VALIDATE_EMAIL)) {
        $response['fields'][] = [
            'name' => 'supervisor_email',
            'message' => 'Invalid supervisor email format'
        ];
    } else {
        // ✅ STEP 1: Check email exists in lab_user table
        $check_supervisor_sql = "SELECT user_id FROM lab_user WHERE email = ?";
        $check_supervisor_result = Database::search($check_supervisor_sql, "s", [$supervisor_email]);

        if ($check_supervisor_result && $check_supervisor_result->num_rows === 0) {
            $response['fields'][] = [
                'name' => 'supervisor_email',
                'message' => 'Supervisor email not found in the system.'
            ];
        } else if ($check_supervisor_result) {
            // ✅ Get supervisor ID
            $supervisor_row = $check_supervisor_result->fetch_assoc();
            $supervisor_id = $supervisor_row['user_id'];

            // ✅ STEP 2: Check role_id = 7 using Database class
            $check_role_sql = "SELECT 1 FROM user_has_role WHERE user_id = ? AND role_id = 7";
            $check_role_result = Database::search($check_role_sql, "i", [$supervisor_id]);

            if ($check_role_result && $check_role_result->num_rows === 0) {
                $response['fields'][] = [
                    'name' => 'supervisor_email',
                    'message' => 'This user is not registered as a supervisor.'
                ];
            }
        }
    }

    // If there are validation errors, return them
    if (!empty($response['fields'])) {
        $response['message'] = 'Please correct the errors below';
        echo json_encode($response);
        exit;
    }

    // ✅ FIXED: Check if user already exists using Database class
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

    // Get current date and time
    $joined_date = date('Y-m-d H:i:s');

    // ✅ FIXED: Insert using Database class
    try {
        // Insert into Table 1: lab_user
        $insert_sql_table1 = "INSERT INTO lab_user (
            first_name, last_name, university_id, email, mobile, 
            password_user, img_path,request_status_request_status_id, 
            join_datetime, status_user
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $request_status_id = 4; // 4 = pending
        $status_user = 1; // 1 = active

        $insert_success = Database::iud($insert_sql_table1, "sssssssisi", [
            $first_name,
            $last_name,
            $university_id,
            $email,
            $mobile,
            $hashed_password,
            $profile_image_path,
            $request_status_id,
            $joined_date,
            $status_user
        ]);

        if (!$insert_success) {
            throw new Exception('Failed to insert into lab_user');
        }

        $user_id = Database::lastInsertId();

        // Insert into Table 2: user_has_role
        $insert_sql_table2 = "INSERT INTO user_has_role (user_id, role_id) VALUES (?, ?)";
        $role_id = 8; // Role ID 2 for student/user

        $role_success = Database::iud($insert_sql_table2, "ii", [$user_id, $role_id]);

        if (!$role_success) {
            throw new Exception('Failed to assign user role');
        }

        // After successfully inserting the student and getting $user_id
        // And after validating supervisor email and getting $supervisor_id

        // Insert into supervisor_assigned table
        $assign_sql = "INSERT INTO supervisor_assigned (student_id, supervisor_id) VALUES (?, ?)";
        $assign_success = Database::iud($assign_sql, "ii", [$user_id, $supervisor_id]);

        if (!$assign_success) {
            // Log error but don't stop the process
            error_log("Failed to assign supervisor to student ID: " . $user_id);
        }
        // Send notification to supervisor
        sendSupervisorNotification($supervisor_email, $first_name, $last_name, $university_id);

        $response['status_user'] = 'success';
        $response['message'] = 'Account created successfully! Your request has been sent to the supervisor for approval.';
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

// Helper Functions

function verifyRecaptcha($token)
{
    $secret_key = '6LcM0HMsAAAAANzVhD2S3a9tOPDDZS0puelYCLI3';

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secret_key,
        'response' => $token
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        return false;
    }

    $result = json_decode($result, true);
    return $result['success'] ?? false;
}

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

    // Create directory if it doesn't exist
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

function sendSupervisorNotification($supervisor_email, $student_fname, $student_lname, $student_id)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'microbiologylaboratorysystem@gmail.com';
        $mail->Password   = 'oway suhf uzca geqp';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');
        $mail->addAddress($supervisor_email);
        $mail->addReplyTo('microbiologylaboratorysystem@gmail.com', 'Microbiology Lab System');

        $mail->isHTML(true);
        $mail->Subject = 'Student Registration Pending Approval - Microbiology Lab';

        $mail->Body = "
        <html>
        <head>
            <title>Student Registration Approval Required</title>
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
                    <h3>Dear Supervisor,</h3>
                    <p>A student has registered with you as their supervisor:</p>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px; background: #e5e7eb;'><strong>Student Name:</strong></td>
                            <td style='padding: 8px;'>{$student_fname} {$student_lname}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background: #e5e7eb;'><strong>Student ID:</strong></td>
                            <td style='padding: 8px;'>{$student_id}</td>
                        </tr>
                    </table>
                    <p style='margin-top: 20px;'>Please log in to the system to review and approve this registration.</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='http://" . $_SERVER['HTTP_HOST'] . "/supervisor_dashboard.php' 
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
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            return ['success' => false, 'message' => 'Email sending failed'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Mailer Error: " . $mail->ErrorInfo];
    }
}

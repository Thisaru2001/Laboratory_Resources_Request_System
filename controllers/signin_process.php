<?php
session_start();
include "../config/database.php";

  
$university_id = $_POST["university_id"] ?? '';
$password = $_POST["password"] ?? '';
$remember_me = $_POST["remember_me"] ?? '';


$university_id = strtoupper(trim($university_id));


$token = $university_id . $password;

if (empty($university_id)) {
    echo "Please Enter Your University ID.";
    exit;
}

if (empty($password)) {
    echo "Please Enter Your Password.";
    exit;
}


$max_attempts = 4;
$lockout_time = 15 * 60;


$attempts_file = sys_get_temp_dir() . '/login_attempts_' . md5($university_id . $_SERVER['REMOTE_ADDR']);


if (file_exists($attempts_file)) {
    $attempt_data = json_decode(file_get_contents($attempts_file), true);
    $attempts = $attempt_data['attempts'] ?? 0;
    $first_attempt = $attempt_data['first_attempt'] ?? time();

    if ($attempts >= $max_attempts) {
        $time_elapsed = time() - $first_attempt;
        if ($time_elapsed < $lockout_time) {
            $minutes_remaining = ceil(($lockout_time - $time_elapsed) / 60);
            echo "Too many failed login attempts. Please try again in " . $minutes_remaining . " minutes.";
            exit;
        } else {

            $attempts = 0;
            $first_attempt = time();
        }
    }
} else {
    $attempts = 0;
    $first_attempt = time();
}

$result = Database::search("
    SELECT u.*, r.role 
    FROM `lab_user` u
    LEFT JOIN `user_has_role` ur ON u.user_id = ur.user_id
    LEFT JOIN `role` r ON ur.role_id = r.role_id
    WHERE u.`university_id`='" . $university_id . "'
");
$count = $result->num_rows;

if ($count == 1) {
    $user = $result->fetch_assoc();


    if (isset($user['status_user']) && $user['status_user'] == 0) {
        echo "Your account has been deactivated. Please contact Supervisor or HODs.";
        exit;
    }

 
    if (password_verify($password, $user['password_user'])) {

        if (password_verify($token, $user['remember_token'])) {

            if (file_exists($attempts_file)) {
                unlink($attempts_file);
            }

            // Get user role from database
            $user_role = $user['role'] ?? 'Student';

            // Store role in session (keep original case)
            $_SESSION["user"] = $user;
            $_SESSION["user_id"] = $user['user_id'];
            $_SESSION["user_role"] = $user_role;
            $_SESSION["last_activity"] = time();
            $_SESSION["ip_address"] = $_SERVER['REMOTE_ADDR'];
            $_SESSION["user_agent"] = $_SERVER['HTTP_USER_AGENT'];

            // Set redirect path based on role (NO strtolower)
            switch ($user_role) {
                case 'Student':
                    $redirect = "/LRRS/views/student.php";
                    break;
                case 'Technical Officer':
                    $redirect = "/LRRS/views/tec_officer.php";
                    break;
                case 'Supervisor':
                    $redirect = "/LRRS/views/supervisor.php";
                    break;
                case 'HOD':
                    $redirect = "/LRRS/views/hod.php";
                    break;
                default:
                    $redirect = "/LRRS/views/student.php";
            }

            session_regenerate_id(true);

            if ($remember_me == "true" || $remember_me == "1") {

                $expiry = time() + (60 * 60 * 24 * 30);

                setcookie("university_id", $university_id, [
                    'expires' => $expiry,
                    'path' => '/',
                    'domain' => '',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                setcookie("token_hash", password_hash($token, PASSWORD_DEFAULT), [
                    'expires' => $expiry,
                    'path' => '/',
                    'domain' => '',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            } else {

                setcookie("university_id", "", time() - 3600, "/", "", true, true);
                setcookie("token_hash", "", time() - 3600, "/", "", true, true);
            }

            // Return success with redirect path
            echo "success|$redirect";
       
        } else {

            $attempts++;
            $attempt_data = [
                'attempts' => $attempts,
                'first_attempt' => $first_attempt,
                'ip' => $_SERVER['REMOTE_ADDR']
            ];
            file_put_contents($attempts_file, json_encode($attempt_data));

            echo "Security verification failed. Please contact Supervisor or HODs.";

            error_log("Security Warning: Suspicious login attempt detected for University ID: " . $university_id . ". Access denied.");
        }
    } else {

        $attempts++;
        $attempt_data = [
            'attempts' => $attempts,
            'first_attempt' => $first_attempt,
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        file_put_contents($attempts_file, json_encode($attempt_data));

        $remaining_attempts = $max_attempts - $attempts;
        if ($remaining_attempts > 0) {
            echo "Invalid University ID or Password. " . $remaining_attempts . " attempts remaining.";
        } else {
            echo "Maximum login attempts exceeded. Please try again after 15 minutes.";
        }
    }
} else {

    $attempts++;
    $attempt_data = [
        'attempts' => $attempts,
        'first_attempt' => $first_attempt,
        'ip' => $_SERVER['REMOTE_ADDR']
    ];
    file_put_contents($attempts_file, json_encode($attempt_data));

    $remaining_attempts = $max_attempts - $attempts;
    if ($remaining_attempts > 0) {
        echo "Invalid University ID or Password. " . $remaining_attempts . " attempts remaining.";
    } else {
        echo "Maximum login attempts exceeded. Please try again after 15 minutes.";
    }
}


$temp_dir = sys_get_temp_dir();
foreach (glob($temp_dir . '/login_attempts_*') as $file) {
    if (time() - filemtime($file) > 3600) {
        unlink($file);
    }
}
?>
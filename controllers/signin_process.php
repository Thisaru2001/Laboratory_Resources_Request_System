<?php
session_start();
include "../config/database.php";

$university_id = $_POST["university_id"] ?? '';
$password = $_POST["password"] ?? '';
$remember_me = $_POST["remember_me"] ?? '';
 
$university_id = strtoupper(trim($university_id));
error_log("Processed University ID: " . $university_id);
// Generate the same token as in signup
$token = $university_id . $password;
  
if (empty($university_id)) {
    echo "Please Enter Your University ID.";
    exit;
}

if (empty($password)) {
    echo "Please Enter Your Password.";
    exit;
}

// Login attempt limiting
$max_attempts = 4;
$lockout_time = 15 * 60;

$attempts_file = sys_get_temp_dir() . '/login_attempts_' . md5($university_id . $_SERVER['REMOTE_ADDR']);
error_log("Attempts file: " . $attempts_file);

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
            // Reset attempts after lockout period
            $attempts = 0;
            $first_attempt = time();
        }
    }
} else {
    $attempts = 0;
    $first_attempt = time();
}
 
$result = Database::search(
    "SELECT u.*, r.role 
     FROM `lab_user` u
     LEFT JOIN `lab_user_has_role` ur ON u.id = ur.lab_user_id
     LEFT JOIN `role` r ON ur.role_id = r.id
     WHERE u.`university_id` = ?",
    "s",
    [$university_id]
);

// Check if result is valid
if (!$result) {
    error_log("Database error: " . Database::getLastError());
    echo "Database error occurred. Please try again later.";
    exit;
}

$count = $result->num_rows;

if ($count == 1) {
    $user = $result->fetch_assoc();

    // Check if account is deactivated
    if (isset($user['status']) && $user['status'] == 0) {
      echo "Your account is not activated. Please contact the supervisor.";
        exit;
    }

    // First verify password
    if (password_verify($password, $user['password_user'])) {
       
        // Then verify the token
        if (password_verify($token, $user['remember_token'])) {
            
            // Clear login attempts on successful login
            if (file_exists($attempts_file)) {
                unlink($attempts_file);
            }

            // Get user role from database
            $user_role = $user['role'] ?? 'student';
 
            // Store user data in session
            $_SESSION["user"] = $user;
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["user_first_name"] = $user['first_name'] ?? '';
            $_SESSION["user_last_name"] = $user['last_name'] ?? '';
            $_SESSION["user_role"] = $user_role;
            $_SESSION["last_activity"] = time();
            $_SESSION["ip_address"] = $_SERVER['REMOTE_ADDR'];
            $_SESSION["user_agent"] = $_SERVER['HTTP_USER_AGENT'];

            // Set redirect path based on role
            $role_lower = strtolower($user_role);
            switch ($role_lower) {
                case 'student':
                    $redirect = "views/student.php";
                    break;
                case 'technical_officer':
                    $redirect = "views/tec_officer.php";
                    break;
                case 'supervisor':
                    $redirect = "views/supervisor.php";
                    break;
                case 'hod':
                    $redirect = "views/hod.php";
                    break;
                default:
                    $redirect = "views/student.php";
            }

            // error_log("Redirect URL: " . $redirect);

            session_regenerate_id(true);

            // Handle "Remember Me" functionality
        if ($remember_me == "true" || $remember_me == "1") {
    $expiry = time() + (60 * 60 * 24 * 30);

    setcookie("university_id", $university_id, [
        'expires' => $expiry,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => false,  // ← must be false so JS can read it
        'samesite' => 'Strict'
    ]);

    // ADD THIS - store plain password for auto-fill
    setcookie("saved_password", $password, [
        'expires' => $expiry,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => false,  // ← must be false so JS can read it
        'samesite' => 'Strict'
    ]);
} else {
    setcookie("university_id", "", time() - 3600, "/");
    setcookie("saved_password", "", time() - 3600, "/");
    setcookie("token_hash", "", time() - 3600, "/");
}

            // Log user session
            try {
                $insert_sql_user_sessions = "INSERT INTO user_session (created_at, lab_user_id) VALUES (NOW(), ?)";
                Database::iud($insert_sql_user_sessions, "i", [$user['id']]);
            } catch (Exception $e) {
                error_log("Session logging error: " . $e->getMessage());
            }

            // IMPORTANT: Return success with redirect path
            // Make sure there's NO whitespace before or after
            
            echo "success|$redirect";
            error_log("About to redirect to: " . $redirect);
error_log("Session user_id: " . ($_SESSION["user_id"] ?? 'not set'));
error_log("Session user_role: " . ($_SESSION["user_role"] ?? 'not set'));
            exit();
            
        } else {
            // Token verification failed
            $attempts++;
            $attempt_data = [
                'attempts' => $attempts,
                'first_attempt' => $first_attempt,
                'ip' => $_SERVER['REMOTE_ADDR']
            ];
            file_put_contents($attempts_file, json_encode($attempt_data));

            echo "Security verification failed. Please contact Supervisor or HOD.";
            error_log("Security Warning: Token verification failed for University ID: " . $university_id);
            exit();
        }
    } else {
        // Wrong password
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
        exit();
    }
} else {
    // User not found
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
    exit();
}

// Clean up old attempt files
$temp_dir = sys_get_temp_dir();
foreach (glob($temp_dir . '/login_attempts_*') as $file) {
    if (time() - filemtime($file) > 3600) {
        unlink($file);
    }
}
?>
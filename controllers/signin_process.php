<?php
session_start();
include "../config/database.php";

header('Content-Type: application/json');

// Get form data - using the correct field names from JavaScript
$university_id = trim($_POST['u'] ?? '');  // 'u' from JavaScript
$password      = $_POST['p'] ?? '';        // 'p' from JavaScript
$remember_me   = !empty($_POST['r']);      // 'r' from JavaScript

// REMOVE or COMMENT OUT line 36 - this line is causing the warning
// $responseData = json_decode($verify);

// Validate required fields
if (empty($university_id) || empty($password)) {
    error_log("ERROR: Required fields missing");
    echo json_encode([
        'status'=>'error',
        'msg'=>'All fields required'
    ]);
    exit;
}

// Validate University ID length
if (strlen($university_id) > 20) {
    error_log("ERROR: University ID too long - " . strlen($university_id) . " characters");
    echo json_encode([
        'status'=>'error',
        'msg'=>'Invalid University ID'
    ]);
    exit;
}

try {
    error_log("Searching for user with University ID: " . $university_id);
    
    $result = Database::search(
        "SELECT * FROM `lab_user`
         WHERE `university_id`=? LIMIT 1",
        "s",
        [$university_id]
    );

    // Check if query executed properly
    if ($result === false) {
        $dbError = Database::getLastError();
        error_log("DATABASE ERROR: Query failed - " . ($dbError ?: 'Unknown error'));
        echo json_encode([
            'status'=>'error',
            'msg'=>'Database query failed'
        ]);
        exit;
    }

    if ($result && $result->num_rows > 0) {
        
        $user = $result->fetch_assoc();
     
        // Check if password field exists
        if (!isset($user['password_user'])) {
            error_log("ERROR: Password field missing from database result!");
            echo json_encode([
                'status'=>'error',
                'msg'=>'Database configuration error'
            ]);
            exit;
        }
        
        // Verify password
        error_log("Attempting password verification...");
        $passwordValid = password_verify($password, $user['password_user']);
        error_log("Password verification result: " . ($passwordValid ? 'SUCCESS' : 'FAILED'));
        
        if ($passwordValid) {
            error_log("PASSWORD VERIFICATION SUCCESS for user: " . $university_id);

            /* ---------- SESSION ---------- */
            $_SESSION['user'] = [
                'user_id'       => $user['user_id'],
                'university_id' => $user['university_id'],
                'name'          => $user['first_name']
            ];
            error_log("Session data set: " . print_r($_SESSION['user'], true));

            /* ---------- REMEMBER ME ---------- */
            if ($remember_me) {
                error_log("Processing 'Remember Me' option");
                
                $token = bin2hex(random_bytes(32));
                error_log("Generated remember token: " . $token);

                $updateResult = Database::iud(
                    "UPDATE `lab_user`
                     SET `remember_token`=?
                     WHERE `user_id`=?",
                    "si",
                    [$token, $user['user_id']]
                );

                if ($updateResult) {
                    error_log("Remember token saved to database");
                    
                    setcookie("remember_token", $token, [
                        'expires'  => time() + (86400 * 30),
                        'path'     => '/',
                        'httponly' => true,
                        'samesite' => 'Strict',
                        'secure'   => isset($_SERVER['HTTPS'])
                    ]);
                    error_log("Remember cookie set");
                } else {
                    error_log("FAILED to save remember token: " . Database::getLastError());
                }
            }

            error_log("LOGIN SUCCESSFUL - Sending success response");
            echo json_encode([
                'status'=>'success',
                'msg'=>'Login successful'
            ]);
            exit;
        } else {
            error_log("PASSWORD VERIFICATION FAILED for user: " . $university_id);
            error_log("Submitted password length: " . strlen($password));
        }
    } else {
        error_log("USER NOT FOUND with University ID: " . $university_id);
        if ($result) {
            error_log("num_rows: " . $result->num_rows);
        }
    }

    error_log("LOGIN FAILED - Invalid credentials response");
    echo json_encode([
        'status'=>'error',
        'msg'=>'Invalid University ID or password'
    ]);
    exit;

} catch (Exception $e) {
    error_log("EXCEPTION CAUGHT: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());

    echo json_encode([
        'status'=>'error',
        'msg'=>'Server error. Try again later.'
    ]);
    exit;
} finally {
    error_log("========== LOGIN ATTEMPT ENDED ==========");
}
?>
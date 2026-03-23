<?php
session_start();

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if user is logged in and is a HOD
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'hod') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config/database.php';

$hod_id = $_SESSION["user_id"];

try {
    // Log received data
    error_log("Profile update - User ID: $hod_id");
    error_log("POST: " . print_r($_POST, true));
    error_log("FILES: " . print_r($_FILES, true));

    // Validate POST data
    if (empty($_POST)) {
        throw new Exception('No data received');
    }

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $university_id = trim($_POST['university_id'] ?? '');

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Check if email already exists for another user
    $check_query = "SELECT id FROM lab_user WHERE email = ? AND id != ?";
    $check_result = Database::search($check_query, "si", [$email, $hod_id]);
    
    if ($check_result && $check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists for another user']);
        exit;
    }

    // Handle password change if provided
    $password_updated = false;
    $hashed_password = null;
    
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        // Get current user's password hash
        $pwd_query = "SELECT password_user FROM lab_user WHERE id = ?";
        $pwd_result = Database::search($pwd_query, "i", [$hod_id]);
        
        if (!$pwd_result || $pwd_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        $user_pwd = $pwd_result->fetch_assoc();
        
        // Verify current password
        if (!password_verify($current_password, $user_pwd['password_user'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }

        // Validate new password length
        if (strlen($new_password) < 8) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long']);
            exit;
        }

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $password_updated = true;
    } else if (!empty($_POST['current_password']) || !empty($_POST['new_password'])) {
        // Check if only one password field is filled
        echo json_encode(['success' => false, 'message' => 'Please provide both current and new password']);
        exit;
    }

    // Get current user data to check existing image
    $current_query = "SELECT img_path FROM lab_user WHERE id = ?";
    $current_result = Database::search($current_query, "i", [$hod_id]);
    $current_user = $current_result ? $current_result->fetch_assoc() : null;
    $old_image_path = $current_user['img_path'] ?? null;

    // Initialize image path
    $img_path = null;

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        
        // Get actual MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($mime_type, $allowed_types) || !in_array($extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Only image files are allowed (JPEG, PNG, GIF, WebP)']);
            exit;
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
            exit;
        }

        // Create upload directory if it doesn't exist
        $upload_dir = dirname(__DIR__) . '/assets/profile_images/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Check if directory is writable
        if (!is_writable($upload_dir)) {
            throw new Exception('Upload directory is not writable');
        }

        // Generate unique filename
        $filename = 'hod_' . $hod_id . '_' . time() . '.' . $extension;
        $upload_path = $upload_dir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload file');
        }

        // Delete old image if it exists
        if (!empty($old_image_path)) {
            $old_path = dirname(__DIR__) . '/assets/profile_images/' . basename($old_image_path);
            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }

        $img_path = $filename;
    }

    // Prepare update query
    $update_fields = [];
    $update_params = [];
    $update_types = "";

    $update_fields[] = "first_name = ?";
    $update_params[] = $first_name;
    $update_types .= "s";

    $update_fields[] = "last_name = ?";
    $update_params[] = $last_name;
    $update_types .= "s";

    $update_fields[] = "email = ?";
    $update_params[] = $email;
    $update_types .= "s";

    if (!empty($mobile)) {
        $update_fields[] = "mobile = ?";
        $update_params[] = $mobile;
        $update_types .= "s";
    }

    if (!empty($university_id)) {
        $update_fields[] = "university_id = ?";
        $update_params[] = $university_id;
        $update_types .= "s";
    }

    if (!empty($img_path)) {
        $update_fields[] = "img_path = ?";
        $update_params[] = $img_path;
        $update_types .= "s";
    }

    if ($password_updated && $hashed_password) {
        $update_fields[] = "password_user = ?";
        $update_params[] = $hashed_password;
        $update_types .= "s";
    }

    $update_params[] = $hod_id;
    $update_types .= "i";

    $update_query = "UPDATE lab_user SET " . implode(", ", $update_fields) . " WHERE id = ?";

    // Execute update
    Database::iud($update_query, $update_types, $update_params);

    // Update session data
    $_SESSION["user_first_name"] = $first_name;
    $_SESSION["user_last_name"] = $last_name;
    $_SESSION["user"]["first_name"] = $first_name;
    $_SESSION["user"]["last_name"] = $last_name;
    $_SESSION["user"]["email"] = $email;
    $_SESSION["user"]["mobile"] = $mobile;
    $_SESSION["user"]["university_id"] = $university_id;

    if (!empty($img_path)) {
        $_SESSION["user"]["img_path"] = $img_path;
        $_SESSION["img_path"] = $img_path;
    }

    error_log("Profile updated successfully for user: $hod_id");

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully' . ($password_updated ? ' and password changed' : ''),
        'img_path' => $img_path ? ('assets/profile_images/' . $img_path) : null,
        'full_name' => $first_name . ' ' . $last_name,
        'password_changed' => $password_updated
    ]);

} catch (Exception $e) {
    error_log("Error updating profile: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()]);
}
?>

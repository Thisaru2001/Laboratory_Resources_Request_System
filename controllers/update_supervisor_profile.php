<?php
session_start();

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if user is logged in and is a supervisor
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'supervisor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config/database.php';

$supervisor_id = $_SESSION["user_id"];

try {
    // Log received data
    error_log("Profile update - User ID: $supervisor_id");
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
    $check_result = Database::search($check_query, "si", [$email, $supervisor_id]);
    
    if ($check_result && $check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists for another user']);
        exit;
    }

    // Get current user data to check existing image
    $current_query = "SELECT img_path FROM lab_user WHERE id = ?";
    $current_result = Database::search($current_query, "i", [$supervisor_id]);
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
        $filename = 'supervisor_' . $supervisor_id . '_' . time() . '_' . uniqid() . '.' . $extension;
        $file_path = $upload_dir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            $error = error_get_last();
            throw new Exception('Failed to upload image: ' . ($error['message'] ?? 'Unknown error'));
        }

        // Set permissions
        chmod($file_path, 0644);

        $img_path = 'assets/profile_images/' . $filename;

        // Delete old image if exists
        if ($old_image_path) {
            $old_file_path = dirname(__DIR__) . '/' . $old_image_path;
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }
    }

    // Update profile using your Database::iud() method
    if ($img_path) {
        // With image update
        $update_query = "UPDATE lab_user SET first_name = ?, last_name = ?, email = ?, mobile = ?, university_id = ?, img_path = ? WHERE id = ?";
        $success = Database::iud($update_query, "ssssssi", [
            $first_name,
            $last_name,
            $email,
            $mobile,
            $university_id,
            $img_path,
            $supervisor_id
        ]);
    } else {
        // Without image update
        $update_query = "UPDATE lab_user SET first_name = ?, last_name = ?, email = ?, mobile = ?, university_id = ? WHERE id = ?";
        $success = Database::iud($update_query, "sssssi", [
            $first_name,
            $last_name,
            $email,
            $mobile,
            $university_id,
            $supervisor_id
        ]);
    }

    if (!$success) {
        $error = Database::getLastError();
        throw new Exception('Database update failed: ' . ($error ?? 'Unknown error'));
    }

    // Get updated user data
    $user_query = "SELECT first_name, last_name, email, mobile, university_id, img_path FROM lab_user WHERE id = ?";
    $user_result = Database::search($user_query, "i", [$supervisor_id]);
    $updated_user = $user_result ? $user_result->fetch_assoc() : null;
    error_log('Profile update error: ' . $updated_user['img_path']);
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'profile_image' => $img_path ?: ($updated_user['img_path'] ?? null),
        'user_data' => $updated_user
    ]);

} catch (Exception $e) {
    error_log('Profile update error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
} finally {
    // Close database connection
    Database::close();
}
?>
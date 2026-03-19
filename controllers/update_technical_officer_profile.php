<?php
declare(strict_types=1);
session_start();

require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$technical_officer_id = $_SESSION['user_id'];

// Get POST data
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$mobile = $_POST['mobile'] ?? '';

// Validation
if (empty($first_name) || empty($last_name) || empty($email) || empty($mobile)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check if email is already taken by another user
$email_check = "SELECT id FROM lab_user WHERE email = ? AND id != ?";
$email_result = Database::search($email_check, "si", [$email, $technical_officer_id]);

if ($email_result && $email_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email is already in use']);
    exit;
}

$profile_image_path = null;

try {
    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../assets/profile_images/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = $_FILES['profile_image']['name'];
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_size = $_FILES['profile_image']['size'];
        $file_type = $_FILES['profile_image']['type'];

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed']);
            exit;
        }

        // Validate file size (max 5MB)
        if ($file_size > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
            exit;
        }

        // Generate unique filename
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_filename = 'technical_officer_' . $technical_officer_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        // Move uploaded file
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $profile_image_path = 'assets/profile_images/' . $new_filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload profile image']);
            exit;
        }
    }

    // Build update query
    if ($profile_image_path) {
        $update_query = "UPDATE lab_user SET first_name = ?, last_name = ?, email = ?, mobile = ?, img_path = ? WHERE id = ?";
        $success = Database::iud($update_query, "sssssi", [$first_name, $last_name, $email, $mobile, $profile_image_path, $technical_officer_id]);
    } else {
        $update_query = "UPDATE lab_user SET first_name = ?, last_name = ?, email = ?, mobile = ? WHERE id = ?";
        $success = Database::iud($update_query, "ssssi", [$first_name, $last_name, $email, $mobile, $technical_officer_id]);
    }
    
    if ($success) {
        // Update session if name changed
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

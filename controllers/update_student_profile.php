<?php
declare(strict_types=1);
session_start();

require_once '../config/database.php';

header('Content-Type: application/json');

// Check if student is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$student_id = $_SESSION['user_id'];

// Get POST data
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$mobile = $_POST['mobile'] ?? '';
$university_id = $_POST['university_id'] ?? '';
$supervisor_id = $_POST['supervisor_id'] ?? '';

// Validation
if (empty($first_name) || empty($last_name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check if email is already taken by another user
$email_check = "SELECT id FROM lab_user WHERE email = ? AND id != ?";
$email_result = Database::search($email_check, "ii", [$email, $student_id]);

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
        $new_filename = 'student_' . $student_id . '_' . time() . '.' . $file_extension;
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
        $update_query = "UPDATE lab_user SET first_name = ?, last_name = ?, email = ?, mobile = ?, university_id = ?, img_path = ? WHERE id = ?";
        $success = Database::iud($update_query, "ssssssi", [$first_name, $last_name, $email, $mobile, $university_id, $profile_image_path, $student_id]);
    } else {
        $update_query = "UPDATE lab_user SET first_name = ?, last_name = ?, email = ?, mobile = ?, university_id = ? WHERE id = ?";
        $success = Database::iud($update_query, "sssssi", [$first_name, $last_name, $email, $mobile, $university_id, $student_id]);
    }
    
    if ($success) {
        // Update supervisor assignment if provided
        if (!empty($supervisor_id)) {
            // First, check if supervisor assignment exists
            $check_query = "SELECT id FROM supervisor_assigned_student WHERE student_id = ?";
            $check_result = Database::search($check_query, "i", [$student_id]);
            
            if ($check_result && $check_result->num_rows > 0) {
                // Update existing assignment
                $update_sup = "UPDATE supervisor_assigned_student SET supervisor_id_or_hod_id = ? WHERE student_id = ?";
                Database::iud($update_sup, "ii", [$supervisor_id, $student_id]);
            } else {
                // Create new assignment
                $insert_sup = "INSERT INTO supervisor_assigned_student (student_id, supervisor_id_or_hod_id) VALUES (?, ?)";
                Database::iud($insert_sup, "ii", [$student_id, $supervisor_id]);
            }
        }

        error_log("Student profile updated - ID: {$student_id}, Name: {$first_name} {$last_name}");
        
        $response = [
            'success' => true,
            'message' => 'Profile updated successfully',
            'first_name' => htmlspecialchars($first_name),
            'last_name' => htmlspecialchars($last_name)
        ];
        
        // Include image path in response if updated
        if ($profile_image_path) {
            $response['profile_image'] = $profile_image_path;
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }

} catch (Exception $e) {
    error_log("Error in update_student_profile.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>

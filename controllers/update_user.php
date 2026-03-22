<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');



// Check if user is logged in and is HOD
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'hod') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// ========== IMAGE UPLOAD FUNCTION ==========
function handleImageUpload($file) {
    $response = ['success' => false, 'message' => '', 'path' => ''];
    
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'File upload error: ' . $file['error'];
        return $response;
    }
    
    // Validate file size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $response['message'] = 'Image size must be less than 2MB';
        return $response;
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        $response['message'] = 'Only JPG, PNG and GIF images are allowed';
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
        $response['message'] = 'Image uploaded successfully';
    } else {
        $response['message'] = 'Failed to move uploaded file';
    }
    
    return $response;
}

// Get POST data
$user_id = $_POST['user_id'] ?? '';
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$role = $_POST['role'] ?? '';
$supervisor_id = $_POST['supervisor_id'] ?? null;
$original_email = $_POST['original_email'] ?? '';
$original_role = $_POST['original_role'] ?? '';

// Validate inputs
$errors = [];

if (empty($user_id)) {
    $errors['user_id'] = 'User ID is required';
}

if (empty($first_name)) {
    $errors['first_name'] = 'First name is required';
} elseif (strlen($first_name) > 50) {
    $errors['first_name'] = 'First name must be less than 50 characters';
} elseif (!preg_match("/^[a-zA-Z\s\-']+$/", $first_name)) {
    $errors['first_name'] = 'First name contains invalid characters';
}

if (empty($last_name)) {
    $errors['last_name'] = 'Last name is required';
} elseif (strlen($last_name) > 50) {
    $errors['last_name'] = 'Last name must be less than 50 characters';
} elseif (!preg_match("/^[a-zA-Z\s\-']+$/", $last_name)) {
    $errors['last_name'] = 'Last name contains invalid characters';
}

if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (strlen($email) > 100) {
    $errors['email'] = 'Email must be less than 100 characters';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
}

if (empty($mobile)) {
    $errors['mobile'] = 'Mobile number is required';
} elseif (!preg_match("/^(07[0-9]{8})$/", $mobile)) {
    $errors['mobile'] = 'Invalid mobile number. Use 07XXXXXXXX';
}

$valid_roles = ['student', 'supervisor', 'technical_officer', 'hod'];
if (!in_array($role, $valid_roles)) {
    $errors['role'] = 'Invalid role selected';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

// Check if email already exists (if changed)
if ($email !== $original_email) {
    $check_email = Database::search("SELECT id FROM lab_user WHERE email = ? AND id != ?", "si", [$email, $user_id]);
    if ($check_email && $check_email->num_rows > 0) {
        $errors['email'] = 'Email already registered';
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }
}

// Handle image upload
$profile_image_path = null;
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $upload_result = handleImageUpload($_FILES['profile_image']);
    if ($upload_result['success']) {
        $profile_image_path = $upload_result['path'];
    } else {
        $errors['profile_image'] = $upload_result['message'];
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }
}

try {
    // Update user basic info
    if ($profile_image_path) {
        $update_query = "UPDATE lab_user SET first_name = ?, last_name = ?, email = ?, mobile = ?, img_path = ? WHERE id = ?";
        $params = [$first_name, $last_name, $email, $mobile, $profile_image_path, $user_id];
        $types = "sssssi";
    } else {
        $update_query = "UPDATE lab_user SET first_name = ?, last_name = ?, email = ?, mobile = ? WHERE id = ?";
        $params = [$first_name, $last_name, $email, $mobile, $user_id];
        $types = "ssssi";
    }
    
    $result = Database::iud($update_query, $types, $params);
    
    if (!$result) {
        throw new Exception('Failed to update user');
    }
    
    // Update role if changed
    if ($role !== $original_role) {
        
        // Get new role ID
        $role_query = "SELECT id FROM role WHERE role = ?";
        $role_result = Database::search($role_query, "s", [$role]);
        if (!$role_result || $role_result->num_rows === 0) {
            throw new Exception('Invalid role: ' . $role);
        }
        $role_row = $role_result->fetch_assoc();
        $new_role_id = $role_row['id'];
        
        // Delete old role assignments
        $delete_roles = Database::iud("DELETE FROM lab_user_has_role WHERE lab_user_id = ?", "i", [$user_id]);
        if (!$delete_roles) {
            throw new Exception('Failed to update role assignments');
        }
        
        // Insert new role
        $insert_role = Database::iud("INSERT INTO lab_user_has_role (lab_user_id, role_id) VALUES (?, ?)", "ii", [$user_id, $new_role_id]);
        if (!$insert_role) {
            throw new Exception('Failed to assign new role');
        }
        
        // Handle role-specific assignments
        if ($role === 'student') {
            // For students, handle supervisor assignment
            if ($supervisor_id && $supervisor_id !== '') {
                
                // Check if assignment exists
                $check_assign = Database::search("SELECT id FROM supervisor_assigned_student WHERE student_id = ?", "i", [$user_id]);
                if ($check_assign && $check_assign->num_rows > 0) {
                    // Update existing assignment
                    $assign = Database::iud("UPDATE supervisor_assigned_student SET supervisor_id_or_hod_id = ? WHERE student_id = ?", "ii", [$supervisor_id, $user_id]);
                } else {
                    // Create new assignment
                    $assign = Database::iud("INSERT INTO supervisor_assigned_student (student_id, supervisor_id_or_hod_id) VALUES (?, ?)", "ii", [$user_id, $supervisor_id]);
                }
                if (!$assign) {
                    throw new Exception('Failed to assign supervisor');
                }
            }
        } else {
            // For non-students, remove any supervisor assignments
            Database::iud("DELETE FROM supervisor_assigned_student WHERE student_id = ?", "i", [$user_id]);
        }
    } else if ($role === 'student' && $supervisor_id && $supervisor_id !== '') {
        // Role unchanged but supervisor might have changed
        
        $check_assign = Database::search("SELECT id FROM supervisor_assigned_student WHERE student_id = ?", "i", [$user_id]);
        if ($check_assign && $check_assign->num_rows > 0) {
            // Update existing assignment
            $assign = Database::iud("UPDATE supervisor_assigned_student SET supervisor_id_or_hod_id = ? WHERE student_id = ?", "ii", [$supervisor_id, $user_id]);
        } else {
            // Create new assignment
            $assign = Database::iud("INSERT INTO supervisor_assigned_student (student_id, supervisor_id_or_hod_id) VALUES (?, ?)", "ii", [$user_id, $supervisor_id]);
        }
        if (!$assign) {
            throw new Exception('Failed to update supervisor assignment');
        }
    }
    
    // Get updated user data for response
    $user_query = "
        SELECT 
            lu.id,
            lu.first_name, 
            lu.last_name, 
            lu.email, 
            lu.mobile, 
            lu.img_path, 
            lu.status,
            lu.university_id,
            r.role as role_name
        FROM lab_user lu
        INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
        INNER JOIN role r ON uhr.role_id = r.id
        WHERE lu.id = ?
    ";
    $user_result = Database::search($user_query, "i", [$user_id]);
    
    if (!$user_result || $user_result->num_rows === 0) {
        throw new Exception('Failed to retrieve updated user data');
    }
    
    $updated_user = $user_result->fetch_assoc();
    
    // Format image URL
    if (!empty($updated_user['img_path'])) {
        $clean_path = str_replace('\\', '/', $updated_user['img_path']);
        $clean_path = ltrim($clean_path, '/');
        $updated_user['image_url'] = '/' . $clean_path;
    } else {
        $full_name = $updated_user['first_name'] . ' ' . $updated_user['last_name'];
        $updated_user['image_url'] = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=22c55e&color=fff&size=100";
    }
    
    // Format mobile for display
    if (!empty($updated_user['mobile']) && strlen($updated_user['mobile']) == 10) {
        $updated_user['mobile_formatted'] = substr($updated_user['mobile'], 0, 3) . '-' . substr($updated_user['mobile'], 3, 3) . '-' . substr($updated_user['mobile'], 6, 4);
    }
    error_log( $updated_user['image_url']);
    echo json_encode([
        'success' => true, 
        'message' => 'User updated successfully',
        'user' => $updated_user
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is HOD
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'hod') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Accept either user_id or university_id
$user_id = $_GET['user_id'] ?? 0;
$university_id = $_GET['university_id'] ?? '';

// Log the request for debugging
error_log("get_user_details.php called - user_id: $user_id, university_id: $university_id");

if (!$user_id && empty($university_id)) {
    echo json_encode(['success' => false, 'message' => 'User ID or University ID is required']);
    exit();
}

try {
    // Build query based on what was provided
    if ($user_id && $user_id != 0) {
        $query_param = $user_id;
        $query_type = "i";
        $query_field = "lu.id";
        error_log("Searching by database ID: $user_id");
    } else {
        $query_param = $university_id;
        $query_type = "s";
        $query_field = "lu.university_id";
        error_log("Searching by university ID: $university_id");
    }

    // Get user details with role
    $query = "
        SELECT 
            lu.id,
            lu.first_name,
            lu.last_name,
            lu.university_id,
            lu.email,
            lu.mobile,
            lu.img_path,
            lu.status,
            lu.join_datetime,
            lu.approved_datetime
        FROM lab_user lu
        WHERE $query_field = ?
    ";

    $result = Database::search($query, $query_type, [$query_param]);
    
    error_log("Query executed. Result rows: " . ($result ? $result->num_rows : '0'));

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        error_log("User found with ID: " . $user['id']);
        
        // Get user role
        $role_query = "
            SELECT r.role 
            FROM lab_user_has_role uhr 
            INNER JOIN role r ON uhr.role_id = r.id 
            WHERE uhr.lab_user_id = ?
        ";
        $role_result = Database::search($role_query, "i", [$user['id']]);
        
        if ($role_result && $role_result->num_rows > 0) {
            $role_row = $role_result->fetch_assoc();
            $user['role_name'] = $role_row['role'];
        } else {
            // Default role if not found
            $user['role_name'] = 'student';
            error_log("No role found for user ID: " . $user['id'] . ", using default 'student'");
        }
        
        // Format mobile number for display
        if (!empty($user['mobile'])) {
            if (strlen($user['mobile']) == 10) {
                $user['mobile_formatted'] = substr($user['mobile'], 0, 3) . '-' . 
                                           substr($user['mobile'], 3, 3) . '-' . 
                                           substr($user['mobile'], 6, 4);
            } else {
                $user['mobile_formatted'] = $user['mobile'];
            }
        } else {
            $user['mobile_formatted'] = '';
        }
        
        // Get full image URL
        if (!empty($user['img_path'])) {
            // Clean up the path
            $clean_path = str_replace('\\', '/', $user['img_path']);
            $clean_path = ltrim($clean_path, '/');
            // Remove any duplicate slashes
            $clean_path = preg_replace('#/+#', '/', $clean_path);
            $user['image_url'] = '/' . $clean_path;
            error_log("Image path: " . $user['image_url']);
        } else {
            // Generate avatar using UI Avatars
            $full_name = trim($user['first_name'] . ' ' . $user['last_name']);
            $user['image_url'] = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . 
                                "&background=22c55e&color=fff&size=100&bold=true";
        }
        
        // Status text
        $user['status_text'] = ($user['status'] == 1) ? 'Active' : 'Inactive';
        
        // Get supervisor info if user is a student
        if ($user['role_name'] === 'student') {
            error_log("User is a student, fetching supervisor info");
            
            $sup_query = "
                SELECT 
                    sas.supervisor_id_or_hod_id,
                    lu.first_name,
                    lu.last_name,
                    lu.email,
                    lu.university_id
                FROM supervisor_assigned_student sas
                INNER JOIN lab_user lu ON sas.supervisor_id_or_hod_id = lu.id
                WHERE sas.student_id = ?
            ";
            $sup_result = Database::search($sup_query, "i", [$user['id']]);
            
            if ($sup_result && $sup_result->num_rows > 0) {
                $user['supervisor'] = $sup_result->fetch_assoc();
                error_log("Supervisor found: " . json_encode($user['supervisor']));
            } else {
                $user['supervisor'] = null;
                error_log("No supervisor found for student ID: " . $user['id']);
            }
        } else {
            $user['supervisor'] = null;
        }
        
        // Add timestamps for display
        if (!empty($user['join_datetime'])) {
            $user['join_date_formatted'] = date('Y-m-d H:i', strtotime($user['join_datetime']));
        }
        if (!empty($user['approved_datetime'])) {
            $user['approved_date_formatted'] = date('Y-m-d H:i', strtotime($user['approved_datetime']));
        }
        
        echo json_encode(['success' => true, 'user' => $user]);
        error_log("User data sent successfully for ID: " . $user['id']);
        
    } else {
        error_log("User not found with provided identifier");
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    
} catch (Exception $e) {
    error_log("Exception in get_user_details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../index.php");
    exit();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Get user role (keep original for display, but use lowercase for comparison)
$user_role_original = $_SESSION['user_role'];
$user_role_lower = strtolower($user_role_original);

// Define allowed roles for each page (using lowercase for comparison)
$allowed_pages = [
    'student.php' => ['student'],
    'supervisor.php' => ['supervisor'],
    'hod.php' => ['hod'],
    'tec_officer.php' => ['technical officer']
];

// Check if current page is in allowed pages
if (isset($allowed_pages[$current_page])) {
    $allowed_roles = $allowed_pages[$current_page];
    
    // If user's role is not allowed for this page
    if (!in_array($user_role_lower, $allowed_roles)) {
        // Redirect to appropriate page based on role (using original case for paths)
        switch($user_role_original) {
            case 'Student':
                header("Location: /LRRS/views/student.php");
                break;
            case 'Supervisor':
                header("Location: /LRRS/views/supervisor.php");
                break;
            case 'HOD':
                header("Location: /LRRS/views/hod.php");
                break;
            case 'Technical Officer':
                header("Location: /LRRS/views/tec_officer.php");
                break;
            default:
                header("Location: /LRRS/views/student.php");
        }
        exit();
    }
} else {
    // If page not in allowed list, check if it's a valid role page
    $valid_pages = ['student.php', 'supervisor.php', 'hod.php', 'tec_officer.php'];
    if (in_array($current_page, $valid_pages)) {
        // This is a valid page but not in allowed_pages? Something's wrong
        error_log("auth_check.php: Page $current_page not found in allowed_pages");
    }
    header("Location: ../index.php");
    exit();
}

// Update last activity
$_SESSION['last_activity'] = time();
?>
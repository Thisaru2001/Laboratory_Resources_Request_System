<?php
session_start();
require_once "../config/database.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$code = trim($_POST['code'] ?? '');
$university_id = strtoupper(trim($_POST['university_id'] ?? ''));

if (empty($code) || empty($university_id)) {
    echo json_encode(['success' => false, 'message' => 'Code and University ID are required']);
    exit;
}

// Check session
if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_university_id']) || !isset($_SESSION['reset_time'])) {
    echo json_encode(['success' => false, 'message' => 'No verification session found. Please request again.']);
    exit;
}

// Verify university ID matches
if ($_SESSION['reset_university_id'] !== $university_id) {
    echo json_encode(['success' => false, 'message' => 'University ID mismatch']);
    exit;
}

// Check if code expired (10 minutes)
if (time() - $_SESSION['reset_time'] > 600) {
    // Clear expired session
    unset($_SESSION['reset_code']);
    unset($_SESSION['reset_university_id']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_time']);
    
    echo json_encode(['success' => false, 'message' => 'Verification code expired. Please request again.']);
    exit;
}

// Verify code
if ($_SESSION['reset_code'] !== $code) {
    echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
    exit;
}

// Code verified successfully
echo json_encode([
    'success' => true,
    'message' => 'Code verified successfully'
]);
?>
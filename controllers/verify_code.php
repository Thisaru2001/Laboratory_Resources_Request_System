<?php
session_start();
require_once "../config/database.php";

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$university_id = strtoupper(trim($_POST['university_id'] ?? ''));
$code = trim($_POST['code'] ?? '');

if (empty($university_id) || empty($code)) {
    echo json_encode(['success' => false, 'message' => 'University ID and verification code are required']);
    exit;
}

// Verify session
if (!isset($_SESSION['reset_university_id']) || $_SESSION['reset_university_id'] !== $university_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid session. Please request a new code.']);
    exit;
}

// Check if code is expired (15 minutes)
if (time() - $_SESSION['reset_time'] > 900) {
    echo json_encode(['success' => false, 'message' => 'Verification code has expired. Please request a new one.']);
    exit;
}

// Get user and verify code from database
$result = Database::search(
    "SELECT verification_code FROM lab_user WHERE university_id = ?",
    "s",
    [$university_id]
);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();

// Verify the code
if ($user['verification_code'] !== $code) {
    echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
    exit;
}

// Code is valid - store verification status in session
$_SESSION['reset_verified'] = true;

echo json_encode([
    'success' => true,
    'message' => 'Code verified successfully'
]);
?>
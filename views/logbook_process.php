<?php
// ═══════════════════════════════════════════════════════════════════════════
//  logbook_process.php — AJAX POST handler for Practical Finish Logbook
//  Called by: logbook_form.php (fetch POST)
//  Returns:   JSON { ok: bool, message: string, logbook_id?: int }
// ═══════════════════════════════════════════════════════════════════════════
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';  // Removed session_start() - not needed for QR code

// ── JSON response helper ──────────────────────────────────────────────────
function jsonOut(bool $ok, string $msg, array $extra = []): never {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['ok' => $ok, 'message' => $msg], $extra));
    exit;
}

// ── Only accept POST ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(false, 'Invalid request method.');
}

// ── Session guard - REMOVED for QR code access ────────────────────────────
// if (empty($_SESSION['user_id'])) {
//     jsonOut(false, 'Session expired. Please log in again.');
// }

// ── Upload directory ──────────────────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/../uploads/logbook/');
define('UPLOAD_URL', 'uploads/logbook/');
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ════════════════════════════════════════════════════════════════════════════
//  1. Collect & validate fields
// ════════════════════════════════════════════════════════════════════════════
$uni_id          = trim($_POST['university_id'] ?? '');
$reservation_no  = trim($_POST['reservation_id'] ?? ''); // This is the reservation_id (VARCHAR) from QR code
$any_comment     = trim($_POST['comment'] ?? '');
$declaration     = !empty($_POST['declaration']);

// Validation
if (empty($uni_id)) {
    jsonOut(false, 'University ID is required.');
}

if (empty($reservation_no)) {
    jsonOut(false, 'Invalid Reservation ID. Please scan the QR code again.');
}

if (empty($any_comment)) {
    jsonOut(false, 'You must add a comment about the practical session. Describe equipment used and any issues encountered.');
}

if (!$declaration) {
    jsonOut(false, 'You must confirm the declaration before submitting.');
}

// ════════════════════════════════════════════════════════════════════════════
//  2. Find student by university_id FIRST (before processing photos)
// ════════════════════════════════════════════════════════════════════════════
$studentResult = Database::search(
    'SELECT id, first_name, last_name FROM lab_user WHERE university_id = ? LIMIT 1',
    's',
    [$uni_id]
);

if (!$studentResult || $studentResult->num_rows === 0) {
    jsonOut(false, 'Student not found with University ID: ' . $uni_id);
}

$student = $studentResult->fetch_assoc();
$student_id = (int)$student['id'];

// ════════════════════════════════════════════════════════════════════════════
//  3. Process up to 4 base64 images → save to disk
// ════════════════════════════════════════════════════════════════════════════
$photoData  = $_POST['photo_data']  ?? [];
$photoNames = $_POST['photo_names'] ?? [];

if (!is_array($photoData)) {
    jsonOut(false, 'Invalid photo data.');
}

if (count($photoData) < 1) {
    jsonOut(false, 'Please upload at least 1 photo as evidence.');
}

if (count($photoData) > 4) {
    jsonOut(false, 'Maximum 4 photos allowed.');
}

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

$imgPaths   = [null, null, null, null];
$savedFiles = [];

foreach ($photoData as $idx => $dataUrl) {
    if (empty($dataUrl) || $idx >= 4) continue;

    // Parse "data:image/jpeg;base64,XXXX"
    if (!preg_match('#^data:(image/[a-z]+);base64,(.+)$#s', $dataUrl, $m)) {
        jsonOut(false, 'Photo ' . ($idx + 1) . ': invalid data format.');
    }

    $rawData = base64_decode($m[2], true);
    if ($rawData === false) {
        jsonOut(false, 'Photo ' . ($idx + 1) . ': could not decode image.');
    }

    // Real MIME check on raw bytes
    $finfo        = new finfo(FILEINFO_MIME_TYPE);
    $detectedMime = $finfo->buffer($rawData);

    if (!isset($allowedMimes[$detectedMime])) {
        jsonOut(false, 'Photo ' . ($idx + 1) . ': unsupported format. Use JPG, PNG, or WEBP.');
    }

    if (strlen($rawData) > 8 * 1024 * 1024) {
        jsonOut(false, 'Photo ' . ($idx + 1) . ': exceeds the 8 MB limit.');
    }

    $ext      = $allowedMimes[$detectedMime];
    $filename = 'logbook_' . $student_id . '_' . time() . '_' . uniqid() . '.' . $ext;
    $dest     = UPLOAD_DIR . $filename;

    // Ensure the directory exists
    if (!is_dir(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true)) {
            jsonOut(false, 'Photo ' . ($idx + 1) . ': could not create upload directory. Check folder permissions.');
        }
    }

    if (file_put_contents($dest, $rawData) === false) {
        jsonOut(false, 'Photo ' . ($idx + 1) . ': could not save file. Check folder permissions.');
    }

    $savedFiles[]   = $dest;
    $imgPaths[$idx] = UPLOAD_URL . $filename;
}

// ════════════════════════════════════════════════════════════════════════════
//  4. Verify reservation exists and belongs to this student
// ════════════════════════════════════════════════════════════════════════════
$resResult = Database::search(
    "SELECT id, 
            student_id,
            technical_officer_id
     FROM reservation 
     WHERE reservation_id = ? 
     LIMIT 1",
    's',
    [$reservation_no]
);

if (!$resResult || $resResult->num_rows === 0) {
    foreach ($savedFiles as $f) @unlink($f);
    jsonOut(false, 'Reservation not found. Please check your Reservation ID.');
}

$reservation = $resResult->fetch_assoc();
$reservation_db_id = (int)($reservation['id'] ?? 0);
$res_student_id = (int)($reservation['student_id'] ?? 0);
$technical_officer_id = (int)($reservation['technical_officer_id'] ?? 0);

// Verify this reservation belongs to the student
if ($res_student_id !== $student_id) {
    foreach ($savedFiles as $f) @unlink($f);
    jsonOut(false, 'This reservation does not belong to you. Please check your University ID and Reservation ID.');
}

// ════════════════════════════════════════════════════════════════════════════
//  5. Get supervisor from supervisor_assigned_student table
// ════════════════════════════════════════════════════════════════════════════
$supResult = Database::search(
    'SELECT supervisor_id_or_hod_id as supervisor_id 
     FROM supervisor_assigned_student 
     WHERE student_id = ? 
     LIMIT 1',
    'i',
    [$student_id]
);

$supervisor_id = 0;
if ($supResult && $supResult->num_rows > 0) {
    $supRow = $supResult->fetch_assoc();
    $supervisor_id = (int)($supRow['supervisor_id'] ?? 0);
}

// ════════════════════════════════════════════════════════════════════════════
//  6. Get HOD from role table
// ════════════════════════════════════════════════════════════════════════════
$hodResult = Database::search(
    "SELECT lu.id 
     FROM lab_user lu
     INNER JOIN lab_user_has_role lur ON lur.lab_user_id = lu.id
     INNER JOIN role ro ON ro.id = lur.role_id
     WHERE ro.role = 'hod'
     LIMIT 1"
);

$hod_id = 0;
if ($hodResult && $hodResult->num_rows > 0) {
    $hodRow = $hodResult->fetch_assoc();
    $hod_id = (int)($hodRow['id'] ?? 0);
}

// ════════════════════════════════════════════════════════════════════════════
//  7. Check if logbook already exists for this reservation
// ════════════════════════════════════════════════════════════════════════════
$dupResult = Database::search(
    'SELECT id FROM practical_finished_logbook WHERE reservation_id = ? LIMIT 1',
    'i',
    [$reservation_db_id]
);

if ($dupResult && $dupResult->num_rows > 0) {
    foreach ($savedFiles as $f) @unlink($f);
    jsonOut(false, 'A logbook entry has already been submitted for this reservation.');
}

// ════════════════════════════════════════════════════════════════════════════
//  8. INSERT into practical_finished_logbook
// ════════════════════════════════════════════════════════════════════════════
$inserted = Database::iud(
    "INSERT INTO practical_finished_logbook
        (reservation_id, student_id, supervisor_id, any_comment,
         img_path1, img_path2, img_path3, img_path4)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
    'iiisssss',
    [
        $reservation_db_id,
        $student_id,
        $supervisor_id,
        $any_comment,
       
       
       
        $imgPaths[0],
        $imgPaths[1],
        $imgPaths[2],
        $imgPaths[3]
    ]
);

if (!$inserted) {
    foreach ($savedFiles as $f) @unlink($f);
    jsonOut(false, 'Failed to save logbook entry: ' . (Database::getLastError() ?? 'Unknown error'));
}

$logbook_id = Database::lastInsertId();

// ════════════════════════════════════════════════════════════════════════════
//  9. Create notifications - Notify ALL technical officers, HOD, and Supervisor
// ════════════════════════════════════════════════════════════════════════════
$stuName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?: 'Unknown Student';

$notifMsg = "Practical Finish Logbook #$logbook_id has been submitted by "
          . "$stuName ($uni_id) for Reservation #$reservation_no. "
          . "Please review the evidence.";

$notifSql = "INSERT INTO notification
             (description, created_datetime, owner_of_notification, status, need_approval)
             VALUES (?, NOW(), ?, 'unread', 1)";

$notifSqltechof = "INSERT INTO practical_finished_technicalOfficer_notify_and_approval
             (description, create_datetime, status, practical_finished_logbook_id)
             VALUES (?, NOW(), 'unread', ?)";
             
$notifSqlhod = "INSERT INTO practical_finished_hod_notify_and_approval
             (description, create_datetime, status, practical_finished_logbook_id)
             VALUES (?, NOW(), 'unread', ?)";

$notifSqlsupervisor = "INSERT INTO practical_finished_supervisor_notify_and_approval
             (description, create_datetime, status, practical_finished_logbook_id)
             VALUES (?, NOW(), 'unread', ?)";



// Search for logbook entry by reservation_id
$logbookResult = Database::search(
    'SELECT id
     FROM practical_finished_logbook 
     WHERE reservation_id = ? 
     LIMIT 1',
    'i',
    [$reservation_db_id]  // The reservation ID (INT from reservation table)
);


    $logbook = $logbookResult->fetch_assoc();
    $logbook_id = $logbook['id'];
   


// Get ALL technical officers (users with technical_officer role)
// $techOfficersResult = Database::search(
//     "SELECT lu.id 
//      FROM lab_user lu
//      INNER JOIN lab_user_has_role lur ON lur.lab_user_id = lu.id
//      INNER JOIN role ro ON ro.id = lur.role_id
//      WHERE ro.role = 'technical_officer'"
// );

// Notify ALL technical officers
// if ($techOfficersResult && $techOfficersResult->num_rows > 0) {
//     while ($to = $techOfficersResult->fetch_assoc()) {
//         Database::iud($notifSql, 'si', [$notifMsg, $to['id']]);
//     }
// } else {
//     // If no technical officers found with role, log it
//     error_log("No technical officers found with role 'technical_officer'");
// }
if ($technical_officer_id > 0) {
    Database::iud($notifSqltechof, 'si', [$notifMsg, $logbook_id]);
}
// Notify HOD
if ($hod_id > 0) {
    Database::iud($notifSqlhod, 'si', [$notifMsg, $logbook_id]);
}

// Notify Supervisor
if ($supervisor_id > 0) {
    Database::iud($notifSqlsupervisor, 'si', [$notifMsg, $logbook_id]);
}

// ════════════════════════════════════════════════════════════════════════════
//  10. Success response
// ════════════════════════════════════════════════════════════════════════════
jsonOut(true, 'Evidence submitted successfully! Your logbook entry is pending review.', [
    'logbook_id' => $logbook_id
]);
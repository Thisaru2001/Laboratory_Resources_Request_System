<?php
// ═══════════════════════════════════════════════════════════════════════════
//  logbook_process.php — AJAX POST handler for Practical Finish Logbook
//  Called by: logbook_form.php (fetch POST)
//  Returns:   JSON { ok: bool, message: string, logbook_id?: int }
// ═══════════════════════════════════════════════════════════════════════════
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config/Database.php';   // adjust path if needed

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

// ── Session guard ─────────────────────────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    jsonOut(false, 'Session expired. Please log in again.');
}

// ── Upload directory ──────────────────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/../uploads/logbook/');
define('UPLOAD_URL', 'uploads/logbook/');
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ════════════════════════════════════════════════════════════════════════════
//  1. Collect & validate fields
// ════════════════════════════════════════════════════════════════════════════
$student_id  = (int)$_SESSION['user_id'];
$uni_id      = trim($_POST['university_id'] ?? '');
$reservation_id       = trim($_POST['reservation_id']         ?? '');
$comment     = trim($_POST['comment']       ?? '');
$declaration = !empty($_POST['declaration']);

if (!$student_id)
    jsonOut(false, 'Session expired. Please log in again.');
if ($uni_id === '')
    jsonOut(false, 'University ID is required.');
if (!$reservation_id)
    jsonOut(false, 'Please enter a valid Reservation ID.');
if (!$declaration)
    jsonOut(false, 'You must confirm the declaration before submitting.');

// ════════════════════════════════════════════════════════════════════════════
//  2. Process up to 4 base64 images → save to disk
// ════════════════════════════════════════════════════════════════════════════
$photoData  = $_POST['photo_data']  ?? [];
$photoNames = $_POST['photo_names'] ?? [];

if (!is_array($photoData) || count($photoData) > 4)
    jsonOut(false, 'Invalid photo data. Maximum 4 photos allowed.');

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

$imgPaths   = [null, null, null, null];  // img_path1 … img_path4
$savedFiles = [];                        // track for rollback on DB failure

foreach ($photoData as $idx => $dataUrl) {
    if (empty($dataUrl) || $idx >= 4) continue;

    // Parse "data:image/jpeg;base64,XXXX"
    if (!preg_match('#^data:(image/[a-z]+);base64,(.+)$#s', $dataUrl, $m))
        jsonOut(false, 'Photo ' . ($idx + 1) . ': invalid data format.');

    $rawData = base64_decode($m[2], true);
    if ($rawData === false)
        jsonOut(false, 'Photo ' . ($idx + 1) . ': could not decode image.');

    // Real MIME check on raw bytes
    $finfo        = new finfo(FILEINFO_MIME_TYPE);
    $detectedMime = $finfo->buffer($rawData);

    if (!isset($allowedMimes[$detectedMime]))
        jsonOut(false, 'Photo ' . ($idx + 1) . ': unsupported format. Use JPG, PNG, or WEBP.');

    if (strlen($rawData) > 8 * 1024 * 1024)
        jsonOut(false, 'Photo ' . ($idx + 1) . ': exceeds the 8 MB limit.');

    $ext      = $allowedMimes[$detectedMime];
    $filename = 'logbook_' . $student_id . '_' . uniqid('', true) . '.' . $ext;
    $dest     = UPLOAD_DIR . $filename;

    if (file_put_contents($dest, $rawData) === false)
        jsonOut(false, 'Photo ' . ($idx + 1) . ': could not save file. Check folder permissions.');

    $savedFiles[]   = $dest;
    $imgPaths[$idx] = UPLOAD_URL . $filename;
}

// ════════════════════════════════════════════════════════════════════════════
//  3. Verify university_id matches the logged-in student
// ════════════════════════════════════════════════════════════════════════════
$chkResult = Database::search(
    'SELECT id FROM lab_user WHERE id = ? AND university_id = ? LIMIT 1',
    'is',
    [$student_id, $uni_id]
);
if (!$chkResult || $chkResult->num_rows === 0) {
    foreach ($savedFiles as $f) @unlink($f);
    jsonOut(false, 'University ID does not match your account.');
}


$resResult = Database::search(
    "SELECT r.id               AS res_id,
            r.supervisor_id,
            r.technical_officer_id,
            (SELECT lu.id
             FROM   lab_user lu
             INNER  JOIN lab_user_has_role lur ON lur.lab_user_id = lu.id
             INNER  JOIN role ro               ON ro.id = lur.role_id
             WHERE  ro.role = 'hod'
             LIMIT  1)         AS hod_id
     FROM   reservation r
     WHERE  r.student_id = ?
     ORDER  BY r.id DESC
     LIMIT  1",
    'i',
    [$student_id]
);

if (!$resResult || $resResult->num_rows === 0) {
    foreach ($savedFiles as $f) @unlink($f);
    jsonOut(false, 'No active reservation found for your account. Please make a reservation first.');
}

$res                           = $resResult->fetch_assoc();
$reservation_id                = (int)($res['res_id']              ?? 0);
$supervisor_id                 = (int)($res['supervisor_id']        ?? 0);
$hod_id                        = (int)($res['hod_id']               ?? 0);
$who_technical_officer_checked = (int)($res['technical_officer_id'] ?? 0);

// ════════════════════════════════════════════════════════════════════════════
//  5. Duplicate check — one logbook per reservation per student
// ════════════════════════════════════════════════════════════════════════════
$dupResult = Database::search(
    'SELECT id FROM practical_finished_logbook WHERE reservation_id = ? AND student_id = ? LIMIT 1',
    'ii',
    [$reservation_id, $student_id]
);
if ($dupResult && $dupResult->num_rows > 0) {
    foreach ($savedFiles as $f) @unlink($f);
    jsonOut(false, 'You have already submitted a logbook entry for your current reservation.');
}

// ════════════════════════════════════════════════════════════════════════════
//  6. INSERT into practical_finished_logbook
// ════════════════════════════════════════════════════════════════════════════
$inserted = Database::iud(
    "INSERT INTO practical_finished_logbook
         (
          reservation_id, student_id,
          
          img_path1, img_path2, img_path3, img_path4)
     VALUES (?, ?, ?, ?, ?, ?)",
    'iissss',
    [
      
        $reservation_id,
        $student_id,
      
       
        $imgPaths[0],
        $imgPaths[1],
        $imgPaths[2],
        $imgPaths[3],
    ]
);

if (!$inserted) {
    foreach ($savedFiles as $f) @unlink($f);
    jsonOut(false, 'Failed to save logbook entry: ' . (Database::getLastError() ?? 'Unknown error'));
}

$newId = Database::lastInsertId();

// ════════════════════════════════════════════════════════════════════════════
//  7. Notify technical officer AND HOD
//     Fetch student name for a readable notification message
// ════════════════════════════════════════════════════════════════════════════
$stuResult = Database::search(
    'SELECT first_name, last_name, university_id FROM lab_user WHERE id = ? LIMIT 1',
    'i',
    [$student_id]
);
$stuRow   = $stuResult ? $stuResult->fetch_assoc() : [];
$stuName  = trim(($stuRow['first_name'] ?? '') . ' ' . ($stuRow['last_name'] ?? '')) ?: 'Unknown Student';
$stuUniId = $stuRow['university_id'] ?? $uni_id;

$notifMsg = "Practical Finish Logbook #$newId has been submitted by "
          . "$stuName ($stuUniId) for Reservation #$reservation_id. "
          . "Please review the evidence and comments.";

$notifSql = "INSERT INTO notification
                 (description, created_datetime, owner_of_notification, status, need_approval)
             VALUES (?, NOW(), ?, 'unread', 1)";

if ($who_technical_officer_checked > 0) {
    Database::iud($notifSql, 'si', [$notifMsg, $who_technical_officer_checked]);
}

if ($hod_id > 0) {
    Database::iud($notifSql, 'si', [$notifMsg, $hod_id]);
}

// ════════════════════════════════════════════════════════════════════════════
//  8. Success response
// ════════════════════════════════════════════════════════════════════════════
jsonOut(true, 'Evidence submitted successfully! Your logbook entry is pending technical officer review.', [
    'logbook_id' => $newId,
]);
<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in and is HOD
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'hod') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid logbook ID']);
    exit;
}

$logbook_id = intval($_GET['id']);

// Fetch complete logbook details
$query = "
    SELECT 
        pfl.id,
        pfl.reservation_id,
        pfl.any_comment,
        pfl.img_path1,
        pfl.img_path2,
        pfl.img_path3,
        pfl.img_path4,
        pfl.datetime,

        -- Student info
        CONCAT(student.first_name, ' ', student.last_name) AS student_name,
        student.university_id,
        student.email AS student_email,
        student.mobile AS student_mobile,

        -- Supervisor info
        CONCAT(supervisor.first_name, ' ', supervisor.last_name) AS supervisor_name,

        -- Technical Officer info
        CONCAT(tech_officer.first_name, ' ', tech_officer.last_name) AS tech_officer_name,

        -- Reservation info
        r.reservation_id      AS reservation_code,
        r.request_date        AS submitted_date,
        r.continue_days,
        loc.location          AS lab_location,

        -- HOD approval
        hod.is_approved       AS hod_is_approved,
        hod.rejection_reason  AS hod_rejection_reason,
        hod.approved_or_rejected_datetime AS hod_approved_datetime,

        -- Tech officer approval
        tech.is_approved      AS tech_is_approved,
        tech.rejection_reason AS tech_rejection_reason,

        -- Supervisor approval
        sup.is_approved       AS sup_is_approved,
        sup.rejection_reason  AS sup_rejection_reason

    FROM practical_finished_logbook pfl

    LEFT JOIN lab_user student    ON pfl.student_id    = student.id
    LEFT JOIN lab_user supervisor ON pfl.supervisor_id = supervisor.id
    LEFT JOIN lab_user tech_officer ON pfl.who_technicalOfficer_id = tech_officer.id
    LEFT JOIN reservation r       ON pfl.reservation_id = r.id
    LEFT JOIN location loc        ON r.location_id      = loc.id

    LEFT JOIN practical_finished_hod_notify_and_approval hod
        ON pfl.id = hod.practical_finished_logbook_id

    LEFT JOIN practical_finished_technicalofficer_notify_and_approval tech
        ON pfl.id = tech.practical_finished_logbook_id

    LEFT JOIN practical_finished_supervisor_notify_and_approval sup
        ON pfl.id = sup.practical_finished_logbook_id

    WHERE pfl.id = ?
    LIMIT 1
";

error_log("Fetching logbook details for ID: " . $logbook_id);

$result = Database::search($query, "i", [$logbook_id]);

if (!$result) {
    error_log("Database query failed: " . Database::getLastError());
    echo json_encode([
        'success' => false, 
        'message' => 'Database query failed: ' . Database::getLastError()
    ]);
    exit;
}

if ($result->num_rows === 0) {
    error_log("Logbook ID $logbook_id not found");
    echo json_encode(['success' => false, 'message' => 'Logbook not found']);
    exit;
}

$logbook = $result->fetch_assoc();

error_log("Logbook retrieved: " . json_encode($logbook));

// Fetch equipment list for this reservation
$equipment_query = "
    SELECT 
        e.id,
        e.name,
        e.code,
        be.book_qty as quantity
    FROM book_equipment be
    JOIN equipment e ON be.equipment_id = e.id
    WHERE be.reservation_id = ?
    ORDER BY e.name ASC
";

$equipment_result = Database::search($equipment_query, "i", [$logbook['reservation_id']]);
$equipment_list = [];

if ($equipment_result) {
    while ($eq = $equipment_result->fetch_assoc()) {
        $equipment_list[] = $eq;
    }
}

// ===== IMAGE PATH RECONSTRUCTION =====
// Images are stored in nested folders like: uploads/logbook/logbook_BS/2023/filename.jpg
// Database stores only: uploads/logbook/filename.jpg
// We need to reconstruct the full path using the university_id and year

$images = [];
$raw_paths = [
    $logbook['img_path1'] ?? '',
    $logbook['img_path2'] ?? '',
    $logbook['img_path3'] ?? '',
    $logbook['img_path4'] ?? '',
];

// Extract university ID and create the expected folder structure
$university_id = $logbook['university_id'] ?? '';
$sanitized_id = str_replace(['/', '\\', ' '], '_', $university_id);
$year = date('Y', strtotime($logbook['submitted_date']));

foreach ($raw_paths as $path) {
    if (empty(trim($path))) continue;
    
    // Clean path: convert backslashes and remove 'uploads/logbook/' prefix if present
    $clean = str_replace('\\', '/', $path);
    $clean = preg_replace('|^uploads/logbook/|', '', $clean);
    $filename = basename($clean);
    
    // Construct full path for both possible locations
    $full_path = "uploads/logbook/logbook_{$sanitized_id}/{$year}/{$filename}";
    $fallback_path = "uploads/logbook/{$filename}";
    
    // Check which path exists on filesystem
    $filesystem_full = __DIR__ . '/../' . $full_path;
    $filesystem_fallback = __DIR__ . '/../' . $fallback_path;
    
    $found = false;
    
    if (file_exists($filesystem_full)) {
        error_log("Found image at: {$full_path}");
        $images[] = $full_path;
        $found = true;
    } elseif (file_exists($filesystem_fallback)) {
        error_log("Found image at fallback: {$fallback_path}");
        $images[] = $fallback_path;
        $found = true;
    } else {
        // Try other possible year folders (in case year mismatch)
        $uploaded_dir = __DIR__ . '/../uploads/logbook/logbook_' . $sanitized_id;
        if (is_dir($uploaded_dir)) {
            // Scan year folders
            $year_folders = array_diff(scandir($uploaded_dir), ['.', '..']);
            foreach ($year_folders as $year_folder) {
                $year_path = $uploaded_dir . '/' . $year_folder . '/' . $filename;
                if (file_exists($year_path)) {
                    $web_path = "uploads/logbook/logbook_{$sanitized_id}/{$year_folder}/{$filename}";
                    error_log("Found image in year folder: {$web_path}");
                    $images[] = $web_path;
                    $found = true;
                    break;
                }
            }
        }
    }
    
    if (!$found) {
        error_log("⚠️ Image not found: {$filename} (searched for: {$full_path})");
    }
}

error_log("Evidence images processed: " . json_encode($images));

$logbook['equipment_list'] = $equipment_list;
$logbook['evidence_images'] = $images;

echo json_encode([
    'success' => true,
    'logbook' => $logbook
]);
?>

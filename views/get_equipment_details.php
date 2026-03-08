<?php
// ─── LRRS/views/get_equipment_details.php ────────────────────────────────────
header('Content-Type: application/json');

$DEBUG = false;

require_once '../config/database.php';

$code = trim($_GET['code'] ?? '');
if (!$code) {
    echo json_encode(["error" => "No equipment code provided"]);
    exit;
}

// ── Fetch equipment + lab location + grant ────────────────────────────────────
$sql = "
    SELECT
        e.equipment_id,
        e.equipment_code,
        e.name,
        e.qty,
        DATE_FORMAT(e.added_datetime, '%d %b %Y') AS added_datetime,
        e.image_path,
        e.description,
        e.lab_id,
        e.grant_id,
        ll.location                            AS lab_location,
        gd.name                                AS grant_name,
        gd.project_name                        AS grant_project,
        DATE_FORMAT(gd.start_date, '%d %b %Y') AS grant_start,
        DATE_FORMAT(gd.end_date,   '%d %b %Y') AS grant_end
    FROM equipment e
    LEFT JOIN lab_location  ll ON ll.lab_location_id = e.lab_id
    LEFT JOIN grant_details gd ON gd.grant_id        = e.grant_id
    WHERE e.equipment_code = ?
    LIMIT 1
";

$result = Database::search($sql, "s", [$code]);

if (!$result || $result->num_rows === 0) {
    echo json_encode(["error" => "Equipment not found: $code"]);
    exit;
}

$eq = $result->fetch_assoc();

// ── Run Python AI ─────────────────────────────────────────────────────────────
$python_exe    = 'C:\\Users\\CYBORG\\AppData\\Local\\Programs\\Python\\Python39\\python.exe';
$python_script = __DIR__ . '\\analyze_equipment_comment.py';
$safe_code     = escapeshellarg($code);
$command       = '"' . $python_exe . '" "' . $python_script . '" ' . $safe_code . ' 2>&1';
$raw_output    = shell_exec($command);

if ($DEBUG) {
    echo json_encode([
        "debug"              => true,
        "command"            => $command,
        "raw_output"         => $raw_output,
        "shell_exec_enabled" => function_exists('shell_exec') ? "YES" : "NO",
        "python_exe_exists"  => file_exists($python_exe) ? "YES" : "NO",
        "script_exists"      => file_exists($python_script) ? "YES" : "NO",
        "equipment_db_row"   => $eq,
        "json_decode_result" => json_decode(trim((string)$raw_output), true),
        "json_last_error"    => json_last_error_msg(),
    ], JSON_PRETTY_PRINT);
    exit;
}

$ai = $raw_output ? json_decode(trim($raw_output), true) : null;

if (!$ai || isset($ai['error'])) {
    $ai = [
        "student_id"     => null,
        "university_id"  => null,
        "full_name"      => null,
        "reservation_id" => null,
        "confidence"     => 0,
        "sentiment"      => "Unknown",
        "keywords"       => [],
        "analysis"       => isset($ai['error']) ? "AI error: ".$ai['error'] : "AI analysis unavailable.",
        "raw_comment"    => "",
        "mention_found"  => false,
    ];
}

$eq['ai'] = $ai;
echo json_encode($eq);
?>

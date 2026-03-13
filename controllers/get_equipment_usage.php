<?php
// ============================================================
//  EXACT PATH : LRRS\controllers\get_equipment_usage.php
// ============================================================

header('Content-Type: application/json');

require_once '../config/database.php';

define('PYTHON_EXE', '/usr/bin/python3');

try {

    // ── STEP 1: Get equipment from DB ──────────────────────
    $query = "
        SELECT
            e.equipment_id,
            e.equipment_code,
            e.name,
            e.qty,
            e.image_path,
            e.description,
            COALESCE(SUM(em.qty), 0) AS maintenance_pending
        FROM equipment e
        LEFT JOIN equipment_maintenance em
            ON e.equipment_id = em.equipment_id
            AND em.status_of_maintenance_id IN (
                SELECT status_of_maintenance_id
                FROM status_of_maintenance
                WHERE status = 'In Progress'
            )
        GROUP BY
            e.equipment_id, e.equipment_code, e.name,
            e.qty, e.image_path, e.description
        ORDER BY e.equipment_id
    ";

    $result = Database::search($query);
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database query failed']);
        exit;
    }

    $equipment_list = [];
    while ($row = $result->fetch_assoc()) {
        $equipment_list[] = $row;
    }

    // ── STEP 2: Paths ──────────────────────────────────────
    $ai_folder     = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'ai');
    $python_script = $ai_folder . DIRECTORY_SEPARATOR . 'equipment_analyzer.py';
    $json_output   = $ai_folder . DIRECTORY_SEPARATOR . 'equipment_usage_report.json';

    // ── STEP 3: Auto-run Python if JSON missing or > 1 hour old ──
    $json_missing = !file_exists($json_output);
    $json_old     = file_exists($json_output) && (time() - filemtime($json_output)) > 3600;

    $python_ran = false;
    $run_output = [];
    $run_error  = '';

    if (($json_missing || $json_old) && $ai_folder && file_exists($python_script)) {

        // Use hardcoded full path — avoids Apache PATH issues on Windows
        $python_exe = PYTHON_EXE;

        // If hardcoded path doesn't exist, fallback to 'python'
        if (!file_exists($python_exe)) {
            $python_exe = 'python';
        }

        $cmd = '"' . $python_exe . '" "' . $python_script . '" 2>&1';
        exec($cmd, $run_output, $return_code);
        $python_ran = ($return_code === 0);
        $run_error  = implode("\n", $run_output);
    }

    // ── STEP 4: Read JSON output from Python ──────────────
    $usage_map = [];

    if (file_exists($json_output)) {
        $json_raw  = file_get_contents($json_output);
        $json_data = json_decode($json_raw, true);

        if ($json_data && isset($json_data['equipment_usage'])) {
            foreach ($json_data['equipment_usage'] as $item) {
                $key             = strtolower(trim($item['equipment_name']));
                $usage_map[$key] = floatval($item['usage_percent']);
            }
        }
    }

    // ── STEP 5: Merge usage % into equipment list ──────────
    $final = [];
    foreach ($equipment_list as $eq) {

        $name_key = strtolower(trim($eq['name'] ?? ''));
        $usage    = $usage_map[$name_key] ?? 0.0;

        if (!empty($eq['image_path'])) {
            $image = '../' . ltrim(str_replace('\\', '/', $eq['image_path']), '/');
        } else {
            $image = 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';
        }

        $final[] = [
            'id'          => (int)($eq['equipment_id'] ?? 0),
            'code'        => $eq['equipment_code'] ?? 'N/A',
            'name'        => $eq['name'] ?? 'Unknown',
            'qty'         => (int)($eq['qty'] ?? 0),
            'image'       => $image,
            'description' => $eq['description'] ?? '',
            'maintenance' => (int)($eq['maintenance_pending'] ?? 0),
            'usage'       => $usage,
        ];
    }

    echo json_encode([
        'success'      => true,
        'count'        => count($final),
        'equipment'    => $final,
        'analyzed_at'  => date('Y-m-d H:i:s'),
        // ── debug fields (remove these after confirming it works) ──
        'python_ran'   => $python_ran,
        'python_exe'   => PYTHON_EXE,
        'json_exists'  => file_exists($json_output),
        'json_age_sec' => file_exists($json_output) ? (time() - filemtime($json_output)) : null,
        'triggered'    => ($json_missing || $json_old),
        'run_error'    => $run_error,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

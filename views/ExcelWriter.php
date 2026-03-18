<?php
/**
 * ExcelWriter.php
 * Generates a professional .xlsx-compatible equipment inventory report.
 *
 * Usage:
 *   $writer = new ExcelWriter('equipment_inventory.xls');
 *   $writer->setTitle('Microbiology Lab – Equipment Inventory');
 *   $writer->setGeneratedBy('Dr. Perera');
 *   $writer->setHeaders(['#','Code','Name','Description',
 *                        'Total','Available','Broken','Repair','Usage %',
 *                        'Sterilization','Reservation','Date Added']);
 *   $writer->setSummary([
 *       'total_types'     => 12,
 *       'total_units'     => 87,
 *       'total_available' => 72,
 *       'total_broken'    => 5,
 *       'total_repair'    => 10,
 *       'utilization'     => '82.8',
 *   ]);
 *   $writer->addRows($rows);   // each row is an associative or indexed array
 *   $writer->output();
 */
class ExcelWriter
{
    // ── Configuration ───────────────────────────────────────────
    private string  $filename      = 'equipment_inventory.xls';
    private string  $title         = 'Equipment Inventory';
    private string  $generatedBy   = 'HOD';
    private array   $headers       = [];
    private array   $data          = [];
    private array   $summary       = [];

    // Column indices (0-based) that get special colour treatment
    // Set these to match your header order if you customise headers.
    private int $colAvailable = 5;   // green
    private int $colBroken    = 6;   // red
    private int $colRepair    = 7;   // amber
    private int $colUsage     = 8;   // bar

    // ── Colour palette ───────────────────────────────────────────
    private const C = [
        'green_dark'   => '#166534',
        'green_mid'    => '#22c55e',
        'green_light'  => '#dcfce7',
        'green_header' => '#16a34a',
        'amber'        => '#f59e0b',
        'amber_light'  => '#fffbeb',
        'red'          => '#ef4444',
        'red_light'    => '#fef2f2',
        'grey_light'   => '#f8fafc',
        'grey_mid'     => '#e2e8f0',
        'white'        => '#FFFFFF',
        'text_dark'    => '#0f172a',
        'text_muted'   => '#64748b',
    ];

    // ── Public API ───────────────────────────────────────────────
    public function __construct(string $filename = 'equipment_inventory.xls')
    {
        $this->filename = $filename;
    }

    public function setTitle(string $title): void          { $this->title = $title; }
    public function setGeneratedBy(string $by): void       { $this->generatedBy = $by; }
    public function setHeaders(array $headers): void       { $this->headers = $headers; }
    public function setSummary(array $summary): void       { $this->summary = $summary; }

    /** Set which 0-based column indices get the qty colour treatment. */
    public function setQtyColumns(int $available, int $broken, int $repair, int $usage): void
    {
        $this->colAvailable = $available;
        $this->colBroken    = $broken;
        $this->colRepair    = $repair;
        $this->colUsage     = $usage;
    }

    public function addRow(array $row): void  { $this->data[] = $row; }
    public function addRows(array $rows): void { foreach ($rows as $r) { $this->addRow($r); } }

    // ── Output ───────────────────────────────────────────────────
    public function output(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="'
                . preg_replace('/[^a-zA-Z0-9._\-]/', '_', $this->filename) . '"');
            header('Cache-Control: max-age=0, no-cache, no-store');
            header('Pragma: no-cache');
        }

        echo $this->buildHtml();
        exit;
    }

    /** Return the HTML string (useful for testing without headers). */
    public function buildHtml(): string
    {
        $generatedDate = date('Y-m-d  H:i:s');
        $by            = htmlspecialchars($this->generatedBy);
        $title         = htmlspecialchars($this->title);
        $totalRows     = count($this->data);

        ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/* ── Reset ── */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size:10pt; color:#0f172a; background:#fff; }

/* ── Title banner ── */
.title-banner {
    background: <?= self::C['green_dark'] ?>;
    color: #fff;
    font-size: 15pt;
    font-weight: bold;
    text-align: center;
    padding: 12px 16px;
    letter-spacing: 0.5px;
}

/* ── Meta row ── */
.meta-row {
    background: <?= self::C['grey_light'] ?>;
    border-bottom: 1px solid <?= self::C['grey_mid'] ?>;
    display: flex;
    justify-content: space-between;
    padding: 4px 12px;
    font-size: 8.5pt;
    color: <?= self::C['text_muted'] ?>;
    font-style: italic;
}

/* ── Section heading ── */
.section-heading {
    background: <?= self::C['green_dark'] ?>;
    color: #fff;
    font-weight: bold;
    font-size: 10pt;
    padding: 6px 10px;
    margin-top: 18px;
    margin-bottom: 0;
    border-radius: 4px 4px 0 0;
}

/* ── Main table ── */
table.main {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
    font-size: 9.5pt;
}
table.main thead tr {
    background: <?= self::C['green_header'] ?>;
    color: #fff;
}
table.main thead th {
    padding: 7px 8px;
    border: 1px solid <?= self::C['green_dark'] ?>;
    text-align: center;
    font-size: 9pt;
    font-weight: bold;
    vertical-align: middle;
    white-space: pre-line;   /* allows \n line breaks in header strings */
}
table.main tbody tr.row-even { background: <?= self::C['grey_light'] ?>; }
table.main tbody tr.row-odd  { background: #fff; }
table.main tbody tr:hover    { background: <?= self::C['green_light'] ?>; }
table.main td {
    padding: 5px 8px;
    border: 1px solid <?= self::C['grey_mid'] ?>;
    vertical-align: middle;
}
table.main td.center { text-align: center; }
table.main td.right  { text-align: right; }
table.main td.left   { text-align: left; }

/* ── Quantity badges ── */
.qty-available { color: <?= self::C['green_mid'] ?>; font-weight: bold; }
.qty-zero-av   { color: <?= self::C['red'] ?>;       font-weight: bold; }
.qty-broken    { color: <?= self::C['red'] ?>;        font-weight: bold; }
.qty-repair    { color: <?= self::C['amber'] ?>;      font-weight: bold; }
.qty-zero      { color: <?= self::C['text_muted'] ?>; }

/* ── Usage bar ── */
.usage-wrap {
    display: flex; align-items: center; gap: 6px; min-width: 90px;
}
.usage-bar-bg {
    flex: 1; height: 8px; background: <?= self::C['grey_mid'] ?>;
    border-radius: 4px; overflow: hidden; min-width: 50px;
}
.usage-bar-fill { height: 8px; border-radius: 4px; }
.usage-label    { font-weight: bold; font-size: 9pt; min-width: 36px; text-align:right; }

/* ── YES / NO pills ── */
.pill-yes {
    display:inline-block; padding:2px 8px; border-radius:12px;
    background:<?= self::C['green_light'] ?>; color:<?= self::C['green_dark'] ?>;
    font-size:8.5pt; font-weight:bold;
}
.pill-no {
    display:inline-block; padding:2px 8px; border-radius:12px;
    background:<?= self::C['grey_mid'] ?>; color:<?= self::C['text_muted'] ?>;
    font-size:8.5pt;
}

/* ── Summary table ── */
table.summary {
    width: 340px;
    border-collapse: collapse;
    font-size: 9.5pt;
    margin-bottom: 20px;
}
table.summary td {
    padding: 5px 10px;
    border: 1px solid <?= self::C['grey_mid'] ?>;
}
table.summary tr.sum-head td {
    background: <?= self::C['green_dark'] ?>;
    color: #fff;
    font-weight: bold;
    font-size: 10pt;
    text-align: center;
    padding: 6px 10px;
}
table.summary .sum-label {
    background: <?= self::C['green_light'] ?>;
    font-weight: bold;
    color: <?= self::C['text_dark'] ?>;
}
table.summary .sum-value {
    text-align: center;
    font-weight: bold;
    color: <?= self::C['green_dark'] ?>;
}

/* ── Legend ── */
table.legend { border-collapse:collapse; font-size:9pt; margin-bottom:20px; }
table.legend td { padding:4px 10px; border:1px solid <?= self::C['grey_mid'] ?>; }
.leg-dot { font-size:14pt; font-weight:bold; }

/* ── Footer ── */
.footer {
    margin-top: 10px;
    font-size: 8pt;
    color: <?= self::C['text_muted'] ?>;
    text-align: center;
    border-top: 1px solid <?= self::C['grey_mid'] ?>;
    padding-top: 6px;
}
</style>
</head>
<body>

<!-- ══ Title banner ══════════════════════════════════════════════ -->
<div class="title-banner">🔬 &nbsp; <?= $title ?></div>

<!-- ══ Meta row ════════════════════════════════════════════════ -->
<div class="meta-row">
    <span>Generated: <?= htmlspecialchars($generatedDate) ?></span>
    <span>Generated by: <?= $by ?> &nbsp;|&nbsp; Report: Full Equipment Inventory &nbsp;|&nbsp; Total items: <?= $totalRows ?></span>
</div>

<!-- ══ Main equipment table ═════════════════════════════════════ -->
<div class="section-heading">📋 &nbsp; Equipment List</div>
<table class="main">
    <thead>
        <tr>
            <?php foreach ($this->headers as $h): ?>
            <th><?= htmlspecialchars($h) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($this->data)): ?>
        <tr>
            <td colspan="<?= max(1, count($this->headers)) ?>"
                style="text-align:center; padding:20px; color:<?= self::C['text_muted'] ?>; font-style:italic;">
                No equipment data available.
            </td>
        </tr>
        <?php else: ?>
            <?php foreach ($this->data as $rowIdx => $row): ?>
            <?php
                $rowClass   = ($rowIdx % 2 === 0) ? 'row-even' : 'row-odd';
                $rowValues  = array_values($row);   // support both indexed & associative
            ?>
            <tr class="<?= $rowClass ?>">
                <?php foreach ($rowValues as $colIdx => $cell): ?>
                <?php
                    $val     = ($cell !== null && $cell !== '') ? htmlspecialchars((string)$cell) : '—';
                    $tdClass = 'center';
                    $inner   = $val;

                    // ── Column-specific rendering ────────────────
                    if ($colIdx === $this->colAvailable) {
                        $num     = (int)$cell;
                        $cls     = $num > 0 ? 'qty-available' : 'qty-zero-av';
                        $inner   = "<span class=\"{$cls}\">{$val}</span>";
                        $tdClass = 'center';
                    } elseif ($colIdx === $this->colBroken) {
                        $num     = (int)$cell;
                        $cls     = $num > 0 ? 'qty-broken' : 'qty-zero';
                        $inner   = "<span class=\"{$cls}\">{$val}</span>";
                        $tdClass = 'center';
                    } elseif ($colIdx === $this->colRepair) {
                        $num     = (int)$cell;
                        $cls     = $num > 0 ? 'qty-repair' : 'qty-zero';
                        $inner   = "<span class=\"{$cls}\">{$val}</span>";
                        $tdClass = 'center';
                    } elseif ($colIdx === $this->colUsage) {
                        // Usage % – render a mini progress bar
                        $pct     = min(100, max(0, (int)$cell));
                        if ($pct >= 70)      $barColor = self::C['green_mid'];
                        elseif ($pct >= 40)  $barColor = self::C['amber'];
                        else                 $barColor = self::C['red'];
                        $inner = "
                            <div class=\"usage-wrap\">
                                <div class=\"usage-bar-bg\">
                                    <div class=\"usage-bar-fill\"
                                         style=\"width:{$pct}%;background:{$barColor};\"></div>
                                </div>
                                <span class=\"usage-label\" style=\"color:{$barColor};\">{$pct}%</span>
                            </div>";
                        $tdClass = 'left';
                    } elseif (strtoupper((string)$cell) === 'YES') {
                        $inner   = '<span class="pill-yes">YES</span>';
                        $tdClass = 'center';
                    } elseif (strtoupper((string)$cell) === 'NO') {
                        $inner   = '<span class="pill-no">NO</span>';
                        $tdClass = 'center';
                    } elseif ($colIdx <= 1) {
                        // # and code columns → center
                        $tdClass = 'center';
                    } elseif ($colIdx <= 3) {
                        // name / description → left
                        $tdClass = 'left';
                    }
                ?>
                <td class="<?= $tdClass ?>"><?= $inner ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- ══ Summary ══════════════════════════════════════════════════ -->
<?php if (!empty($this->summary)): ?>
<?php
$s           = $this->summary;
$sumRows     = [
    ['Total Equipment Types',    htmlspecialchars((string)($s['total_types']     ?? '—'))],
    ['Total Units',              htmlspecialchars((string)($s['total_units']     ?? '—'))],
    ['Total Available Units',    htmlspecialchars((string)($s['total_available'] ?? '—'))],
    ['Total Broken Units',       htmlspecialchars((string)($s['total_broken']    ?? '—'))],
    ['Total Under Repair',       htmlspecialchars((string)($s['total_repair']    ?? '—'))],
    ['Overall Utilisation Rate', htmlspecialchars((string)($s['utilization']     ?? '—')) . '%'],
];
?>
<table class="summary">
    <tr class="sum-head"><td colspan="2">📊 &nbsp; Summary</td></tr>
    <?php foreach ($sumRows as $sr): ?>
    <tr>
        <td class="sum-label"><?= $sr[0] ?></td>
        <td class="sum-value"><?= $sr[1] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- ══ Legend ═══════════════════════════════════════════════════ -->
<table class="legend">
    <tr>
        <td style="background:<?= self::C['green_dark'] ?>;color:#fff;font-weight:bold;padding:5px 10px;"
            colspan="2">🎨 &nbsp; Legend</td>
    </tr>
    <tr>
        <td class="leg-dot" style="color:<?= self::C['green_mid'] ?>;background:#fff;">●</td>
        <td style="background:#fff;">Available quantity — good condition</td>
    </tr>
    <tr>
        <td class="leg-dot" style="color:<?= self::C['amber'] ?>;background:#fff;">●</td>
        <td style="background:#fff;">Units currently under repair / maintenance</td>
    </tr>
    <tr>
        <td class="leg-dot" style="color:<?= self::C['red'] ?>;background:#fff;">●</td>
        <td style="background:#fff;">Broken / damaged units</td>
    </tr>
    <tr>
        <td colspan="2" style="background:#fff;">
            <span class="pill-yes">YES</span>&nbsp; Feature required &nbsp;&nbsp;
            <span class="pill-no">NO</span>&nbsp; Feature not required
        </td>
    </tr>
</table>

<!-- ══ Footer ═══════════════════════════════════════════════════ -->
<div class="footer">
    Microbiology Laboratory &nbsp;|&nbsp; University of Kelaniya &nbsp;|&nbsp;
    Report generated: <?= htmlspecialchars($generatedDate) ?> &nbsp;|&nbsp;
    This document is confidential and intended for internal use only.
</div>

</body>
</html>
<?php
        return ob_get_clean();
    }
}

/* ══════════════════════════════════════════════════════════════════
 * EXAMPLE USAGE — DELETE THIS BLOCK IN PRODUCTION
 * ══════════════════════════════════════════════════════════════════
 *
 * // In your controller / download endpoint:
 * session_start();
 * require_once '../classes/ExcelWriter.php';
 * require_once '../config/database.php';
 *
 * // Fetch data
 * $query = "
 *     SELECT
 *         e.id,
 *         e.code,
 *         e.name,
 *         COALESCE(e.description,'') as description,
 *         e.total_qty,
 *         (e.total_qty - COALESCE(b.broken,0) - COALESCE(r.repair,0)) as available,
 *         COALESCE(b.broken,0)  as broken,
 *         COALESCE(r.repair,0)  as repair,
 *         CONCAT(ROUND((COUNT(bk.id) / GREATEST(total_res.cnt,1)) * 100), '%') as usage_pct,
 *         e.sterilization_required,
 *         e.reservation_required,
 *         DATE_FORMAT(e.added_datatime, '%Y-%m-%d') as date_added
 *     FROM equipment e
 *     LEFT JOIN (SELECT equipment_id, SUM(broken_qty) broken FROM broken GROUP BY equipment_id) b
 *               ON e.id = b.equipment_id
 *     LEFT JOIN (SELECT equipment_id, SUM(repair_qty)  repair FROM repair  GROUP BY equipment_id) r
 *               ON e.id = r.equipment_id
 *     LEFT JOIN book_equipment bk ON e.id = bk.equipment_id
 *     CROSS JOIN (SELECT COUNT(*) cnt FROM reservation) total_res
 *     GROUP BY e.id
 *     ORDER BY e.name
 * ";
 * $result = Database::search($query);
 *
 * $rows    = [];
 * $counter = 1;
 * while ($row = $result->fetch_assoc()) {
 *     $rows[] = [
 *         $counter++,
 *         $row['code'],
 *         $row['name'],
 *         $row['description'],
 *         $row['total_qty'],
 *         $row['available'],
 *         $row['broken'],
 *         $row['repair'],
 *         $row['usage_pct'],
 *         $row['sterilization_required'],
 *         $row['reservation_required'],
 *         $row['date_added'],
 *     ];
 * }
 *
 * $writer = new ExcelWriter('equipment_inventory_' . date('Ymd') . '.xls');
 * $writer->setTitle('Microbiology Lab – Equipment Inventory');
 * $writer->setGeneratedBy($_SESSION['user_first_name'] ?? 'HOD');
 * $writer->setHeaders([
 *     '#', 'Code', 'Name', 'Description',
 *     'Total', 'Available', 'Broken', 'Under Repair', 'Usage %',
 *     "Sterilization\nRequired", "Reservation\nRequired", 'Date Added'
 * ]);
 * $writer->setSummary([
 *     'total_types'     => count($rows),
 *     'total_units'     => array_sum(array_column($rows, 4)),
 *     'total_available' => array_sum(array_column($rows, 5)),
 *     'total_broken'    => array_sum(array_column($rows, 6)),
 *     'total_repair'    => array_sum(array_column($rows, 7)),
 *     'utilization'     => '—',   // compute as needed
 * ]);
 * $writer->addRows($rows);
 * $writer->output();
 *
 * ══════════════════════════════════════════════════════════════════ */

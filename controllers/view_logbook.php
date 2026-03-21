<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$logbook_id = $_GET['id'] ?? 0;

if (!$logbook_id) {
    die('Invalid logbook ID');
}

// Fetch logbook details
$logbookResult = Database::search(
    "SELECT 
        l.*,
        u.first_name,
        u.last_name,
        u.university_id,
        u.email,
        u.mobile,
        sup.first_name as sup_first_name,
        sup.last_name as sup_last_name,
        to_user.first_name as to_first_name,
        to_user.last_name as to_last_name,
        r.reservation_id as reservation_code,
        r.request_date,
        r.comment as reservation_comment
    FROM practical_finished_logbook l
    INNER JOIN lab_user u ON l.student_id = u.id
    INNER JOIN reservation r ON l.reservation_id = r.id
    LEFT JOIN lab_user sup ON l.supervisor_id = sup.id
    LEFT JOIN lab_user to_user ON l.who_technical_officer_checked = to_user.id
    WHERE l.id = ?",
    'i',
    [$logbook_id]
);

if (!$logbookResult || $logbookResult->num_rows === 0) {
    die('Logbook not found');
}

$logbook = $logbookResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook Details #<?= $logbook_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Practical Finish Logbook Details</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Student Name:</strong> <?= htmlspecialchars($logbook['first_name'] . ' ' . $logbook['last_name']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>University ID:</strong> <?= htmlspecialchars($logbook['university_id']) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Reservation ID:</strong> <?= htmlspecialchars($logbook['reservation_code']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Request Date:</strong> <?= htmlspecialchars($logbook['request_date']) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Supervisor Checked:</strong> 
                        <?php 
                        if (!empty($logbook['sup_first_name'])) {
                            echo htmlspecialchars($logbook['sup_first_name'] . ' ' . $logbook['sup_last_name']);
                        } else {
                            echo '<span class="text-muted">Not checked yet</span>';
                        }
                        ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Technical Officer Checked:</strong> 
                        <?php 
                        if (!empty($logbook['to_first_name'])) {
                            echo htmlspecialchars($logbook['to_first_name'] . ' ' . $logbook['to_last_name']);
                        } else {
                            echo '<span class="text-muted">Not checked yet</span>';
                        }
                        ?>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Student's Comment:</strong>
                    <p class="border p-2 bg-light rounded"><?= nl2br(htmlspecialchars($logbook['any_comment'])) ?></p>
                </div>
                <div class="mb-3">
                    <strong>Evidence Photos:</strong>
                    <div class="row mt-2">
                        <?php
                        for ($i = 1; $i <= 4; $i++) {
                            $imgPath = $logbook["img_path$i"];
                            if (!empty($imgPath)) {
                                echo '<div class="col-md-3 mb-2">
                                        <img src="/LRRS/' . htmlspecialchars($imgPath) . '" 
                                             class="img-fluid rounded border" 
                                             style="cursor: pointer; max-height: 150px; object-fit: cover;"
                                             onclick="window.open(this.src)">
                                      </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-secondary" onclick="window.close()">Close</button>
            </div>
        </div>
    </div>
</body>
</html>
<?php
session_start();

// Enable error logging
error_log("=== SUPERVISOR.PHP LOADED ===");
error_log("Session user_id: " . ($_SESSION["user_id"] ?? 'NOT SET'));
error_log("Session user_role: " . ($_SESSION["user_role"] ?? 'NOT SET'));

require_once '../config/database.php';

// Check if user is logged in and is a supervisor
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'supervisor') {
    header("Location: ../index.php");
    exit();
}

$supervisor_id = $_SESSION["user_id"];

// Get supervisor details
$user_query = "SELECT first_name, last_name, img_path FROM lab_user WHERE id = ?";
$user_result = Database::search($user_query, "i", [$supervisor_id]);

if (!$user_result) {
    error_log("User query failed: " . Database::getLastError());
    $first_name = 'Supervisor';
    $last_name = '';
    $profile_image = '';
    $user_data = ['img_path' => '']; // Initialize user_data
} else {
    $user_data = $user_result->fetch_assoc();
    $first_name = $user_data['first_name'] ?? 'Supervisor';
    $last_name = $user_data['last_name'] ?? '';
    $profile_image = $user_data['img_path'] ?? '';
}
$full_name = trim($first_name . ' ' . $last_name);

// ---------- DASHBOARD STATS ----------
// Students assigned to this supervisor
$students_count_q = "SELECT COUNT(DISTINCT student_id) as cnt 
                     FROM supervisor_assigned_student 
                     WHERE supervisor_id_or_hod_id = ?";
$sc = Database::search($students_count_q, "i", [$supervisor_id]);
$students_count = 0;
if ($sc && $sc->num_rows > 0) {
    $students_count = $sc->fetch_assoc()['cnt'] ?? 0;
}

// Pending reservations (waiting for supervisor approval)
$pending_q = "SELECT COUNT(DISTINCT r.id) as cnt 
              FROM reservation r
              INNER JOIN supervisor_assigned_student sas 
                ON r.student_id = sas.student_id 
                AND sas.supervisor_id_or_hod_id = ?
              WHERE r.supervisor_id IS NULL
                AND NOT EXISTS (SELECT 1 FROM reject_reason rr WHERE rr.reservation_id = r.id)";
$pq = Database::search($pending_q, "i", [$supervisor_id]);
$pending_count = 0;
if ($pq && $pq->num_rows > 0) {
    $pending_count = $pq->fetch_assoc()['cnt'] ?? 0;
}

// Today's practicals
$today = date('Y-m-d');
$today_q = "SELECT COUNT(DISTINCT r.id) as cnt 
            FROM reservation r
            INNER JOIN supervisor_assigned_student sas 
              ON r.student_id = sas.student_id 
              AND sas.supervisor_id_or_hod_id = ?
            WHERE ? BETWEEN r.request_date 
              AND DATE_ADD(r.request_date, INTERVAL (r.continue_days - 1) DAY)
              AND r.supervisor_id IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM reject_reason rr WHERE rr.reservation_id = r.id)";
$tq = Database::search($today_q, "is", [$supervisor_id, $today]);
$today_count = 0;
if ($tq && $tq->num_rows > 0) {
    $today_count = $tq->fetch_assoc()['cnt'] ?? 0;
}

// Total reservations
$total_res_q = "SELECT COUNT(DISTINCT r.id) as cnt 
                FROM reservation r
                INNER JOIN supervisor_assigned_student sas 
                  ON r.student_id = sas.student_id 
                  AND sas.supervisor_id_or_hod_id = ?";
$trq = Database::search($total_res_q, "i", [$supervisor_id]);
$total_reservations = 0;
if ($trq && $trq->num_rows > 0) {
    $total_reservations = $trq->fetch_assoc()['cnt'] ?? 0;
}

// ---------- CALENDAR EVENTS ----------
$cal_q = "SELECT r.id, r.reservation_id, r.request_date, r.continue_days,
                 l.location,
                 CONCAT(st.first_name,' ',st.last_name) as student_name,
                 GROUP_CONCAT(DISTINCT CONCAT(e.name,' (x',be.book_qty,')') SEPARATOR '|') as equipment_list
          FROM reservation r
          JOIN location l ON r.location_id = l.id
          JOIN lab_user st ON r.student_id = st.id
          LEFT JOIN book_equipment be ON r.id = be.reservation_id
          LEFT JOIN equipment e ON be.equipment_id = e.id
          INNER JOIN supervisor_assigned_student sas 
            ON r.student_id = sas.student_id 
            AND sas.supervisor_id_or_hod_id = ?
          WHERE NOT EXISTS (SELECT 1 FROM reject_reason rr WHERE rr.reservation_id = r.id)
          GROUP BY r.id, r.reservation_id, r.request_date, r.continue_days, l.location, st.first_name, st.last_name";
$cal_result = Database::search($cal_q, "i", [$supervisor_id]);

$calendar_events = [];
if ($cal_result && $cal_result->num_rows > 0) {
    while ($row = $cal_result->fetch_assoc()) {
        $start = new DateTime($row['request_date']);
        $end   = clone $start;
        $end->modify('+' . ($row['continue_days'] - 1) . ' days');
        $calendar_events[] = [
            'day'       => (int)$start->format('j'),
            'month'     => (int)$start->format('n'),
            'year'      => (int)$start->format('Y'),
            'end_day'   => (int)$end->format('j'),
            'end_month' => (int)$end->format('n'),
            'end_year'  => (int)$end->format('Y'),
            'title'     => $row['reservation_id'],
            'student'   => $row['student_name'],
            'equipment' => $row['equipment_list'] ?? 'No equipment',
            'location'  => $row['location'],
            'duration'  => $row['continue_days'] . ' day(s)',
        ];
    }
}
$calendar_events_json = json_encode($calendar_events);

// ---------- ALL RESERVATIONS ----------
$reservations_q = "SELECT r.id, r.reservation_id, r.request_date, r.continue_days,
                          r.comment, r.created_datetime,
                          l.location,
                          CONCAT(st.first_name,' ',st.last_name) as student_name,
                          st.university_id,
                          st.email as student_email,
                          GROUP_CONCAT(DISTINCT CONCAT(e.name,' (x',be.book_qty,')') ORDER BY e.name SEPARATOR '<br>') as equipment_list,
                          CASE 
                            WHEN rr.id IS NOT NULL THEN 'rejected'
                            WHEN r.supervisor_id IS NOT NULL THEN 'approved'
                            ELSE 'pending'
                          END as status,
                          rr.reason as reject_reason
                   FROM reservation r
                   JOIN location l ON r.location_id = l.id
                   JOIN lab_user st ON r.student_id = st.id
                   LEFT JOIN book_equipment be ON r.id = be.reservation_id
                   LEFT JOIN equipment e ON be.equipment_id = e.id
                   LEFT JOIN reject_reason rr ON r.id = rr.reservation_id
                   INNER JOIN supervisor_assigned_student sas 
                     ON r.student_id = sas.student_id 
                     AND sas.supervisor_id_or_hod_id = ?
                   GROUP BY r.id, r.reservation_id, r.request_date, r.continue_days, r.comment, 
                            r.created_datetime, l.location, st.first_name, st.last_name, 
                            st.university_id, st.email, rr.id, rr.reason
                   ORDER BY r.created_datetime DESC";
$reservations_result = Database::search($reservations_q, "i", [$supervisor_id]);

$all_reservations = [];
if ($reservations_result && $reservations_result->num_rows > 0) {
    while ($row = $reservations_result->fetch_assoc()) {
        $all_reservations[] = $row;
    }
}

// ---------- ASSIGNED STUDENTS ----------
$students_q = "SELECT lu.id, lu.first_name, lu.last_name, lu.university_id, lu.email,
                      lu.mobile, lu.img_path, lu.join_datetime, lu.status,
                      (SELECT COUNT(*) FROM reservation r2 WHERE r2.student_id = lu.id) as request_count
               FROM lab_user lu
               INNER JOIN supervisor_assigned_student sas ON lu.id = sas.student_id
               WHERE sas.supervisor_id_or_hod_id = ?
               GROUP BY lu.id, lu.first_name, lu.last_name, lu.university_id, lu.email, 
                        lu.mobile, lu.img_path, lu.join_datetime, lu.status
               ORDER BY lu.first_name";
$students_result = Database::search($students_q, "i", [$supervisor_id]);

$all_students = [];
if ($students_result && $students_result->num_rows > 0) {
    while ($row = $students_result->fetch_assoc()) {
        $all_students[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Supervisor Dashboard - Microbiology Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="../assets/resources/flask.svg">
    <style>
        /* Notification Modal Styles */
        .request-card {
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .request-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(34, 197, 94, 0.15);
            border-color: #22c55e;
        }

        .request-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .request-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 15px;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);
        }

        .request-info {
            flex: 1;
        }

        .request-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #166534;
            margin-bottom: 3px;
        }

        .request-university {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .request-details {
            margin: 15px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 12px;
            font-size: 0.95rem;
            color: #495057;
            border-left: 4px solid #22c55e;
        }

        .request-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-accept {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
        }

        /* Student Details Modal Styles */
        .info-card {
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .student-avatar-large {
            transition: all 0.3s ease;
        }

        .student-avatar-large:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(34, 197, 94, 0.4) !important;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-accepted {
            background: #d1e7dd;
            color: #0a3622;
        }

        #modalAcceptStudentBtn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border: none;
            padding: 8px 24px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }

        #modalAcceptStudentBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .btn-view {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        /* Notification Bell */
        .notification-bell {
            position: relative;
            cursor: pointer;
            margin-right: 15px;
        }

        .notification-bell i {
            font-size: 1.5rem;
            color: #166534;
            transition: all 0.3s;
        }

        .notification-bell:hover i {
            transform: rotate(15deg);
            color: #22c55e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            overflow-x: hidden;
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
            animation: fadeIn 0.3s;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            min-height: 100vh;
            background: linear-gradient(180deg, #166534 0%, #14532d 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
            border-radius: 0 30px 30px 0;
            overflow-y: auto;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
            pointer-events: none;
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            text-decoration: none;
            transition: all 0.3s;
            border-radius: 12px;
            margin: 4px 16px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .sidebar a i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(8px);
            color: white;
        }

        .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 3px solid #ffd700;
        }

        .sidebar h4 {
            padding: 28px 24px;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 600;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            letter-spacing: 1px;
        }

        .sidebar h4 i {
            margin-right: 10px;
            color: #ffd700;
        }

        .sidebar-footer {
            padding: 20px;
            margin-top: 30px;
            text-align: center;
            background: rgba(0, 0, 0, 0.2);
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Main */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        /* Topbar */
        .topbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 12px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 15px;
            z-index: 999;
            border-radius: 50px;
            margin: 15px 25px 0 25px;
            width: calc(100% - 50px);
        }

        .profile-img {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid #22c55e;
            cursor: pointer;
            transition: all 0.3s;
        }

        .profile-img:hover {
            transform: scale(1.1);
            border-color: #ffd700;
        }

        /* Content */
        .content-area {
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            min-height: calc(100vh - 80px);
        }

        /* Cards */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        /* Stat cards */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 20px;
            padding: 25px;
            color: white;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(34, 197, 94, 0.4);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
            transition: all 0.3s;
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        /* Tables */
        .table thead th {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px;
        }

        .table tbody tr {
            transition: all 0.3s;
        }

        .table tbody tr:hover {
            background: rgba(34, 197, 94, 0.05);
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .user-table thead {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
        }

        .user-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .user-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .user-table tbody tr:hover {
            background: #f9f9f9;
        }

        /* Badges */
        .badge {
            padding: 8px 14px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge.bg-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22) !important;
            color: white;
        }

        .badge.bg-success {
            background: linear-gradient(135deg, #22c55e, #16a34a) !important;
        }

        .badge.bg-danger {
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
        }

        .badge.bg-info {
            background: linear-gradient(135deg, #0dcaf0, #0aa2c0) !important;
            color: white;
        }

        /* Status badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d1e7dd;
            color: #0a3622;
        }

        .status-rejected {
            background: #f8d7da;
            color: #842029;
        }

        /* Buttons */
        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.6);
        }

        .btn-view {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-view:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-approve {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-approve:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .btn-reject {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-reject:hover {
            transform: scale(1.05);
        }

        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1)
            }

            50% {
                transform: scale(1.1)
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        /* Tabs */
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
            border: none;
            padding: 10px 20px;
            position: relative;
        }

        .nav-tabs .nav-link.active {
            color: #28a745;
            background: transparent;
            border-bottom: 3px solid #28a745;
        }

        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #28a745;
        }

        /* Search */
        .search-add-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-container {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 500px;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #22c55e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        .search-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .filter-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            outline: none;
            transition: all 0.3s;
            min-width: 180px;
        }

        .filter-select:focus {
            border-color: #22c55e;
        }

        /* Calendar */
        .calendar-container {
            background: linear-gradient(135deg, #166534 0%, #14532d 100%);
            border-radius: 32px;
            padding: 24px;
            margin: 30px 0;
            color: white;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .calendar-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .calendar-left {
            flex: 1.5;
            min-width: 300px;
            background: white;
            padding: 25px;
        }

        .calendar-right {
            flex: 1;
            min-width: 280px;
            background: linear-gradient(135deg, #166534 0%, #14532d 100%);
            padding: 30px;
            color: #fff;
        }

        .calendar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding: 0 5px;
        }

        .calendar-header .month {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .calendar-header i {
            font-size: 1.3rem;
            cursor: pointer;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .calendar-header i:hover {
            transform: scale(1.1);
        }

        .weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 600;
            color: #666;
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 2px solid #f0f0f0;
        }

        .days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }

        .day-cell {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 12px;
            transition: all 0.3s;
            font-size: 0.95rem;
            color: #333;
            position: relative;
            font-weight: 500;
        }

        .day-cell:hover:not(.prev-date):not(.next-date) {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .day-cell.prev-date,
        .day-cell.next-date {
            color: #ccc;
        }

        .day-cell.active {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            font-weight: 600;
        }

        .day-cell.today {
            font-weight: 700;
            border: 2px solid #22c55e;
            color: #22c55e;
        }

        .day-cell.event::after {
            content: '•';
            position: absolute;
            bottom: 2px;
            font-size: 1.2rem;
            color: #ffd700;
        }

        .goto-section {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .goto-input {
            flex: 1;
            min-width: 120px;
            padding: 12px 16px;
            border: 2px solid #f0f0f0;
            border-radius: 16px;
            outline: none;
            transition: all 0.3s;
        }

        .goto-input:focus {
            border-color: #22c55e;
        }

        .goto-btn,
        .today-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .goto-btn:hover,
        .today-btn:hover {
            transform: scale(1.05);
        }

        .event-day {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .event-date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .events-list {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .event-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 18px;
            border-radius: 16px;
            margin-bottom: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s;
        }

        .event-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(6px);
        }

        .event-item .event-title {
            font-size: 1rem;
            font-weight: 600;
        }

        .event-item .event-meta {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 4px;
        }

        .no-event {
            text-align: center;
            padding: 40px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Reject reason input */
        .reject-reason-wrap {
            display: none;
            margin-top: 10px;
        }

        /* Responsive */
        @media(max-width:991px) {
            .sidebar {
                left: -280px;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                margin: 10px;
                width: calc(100% - 20px);
            }

            .content-area {
                padding: 20px;
            }
        }

        @media(max-width:768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .filter-section {
                flex-direction: column;
            }

            .filter-select {
                width: 100%;
            }

            .calendar-wrapper {
                flex-direction: column;
            }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <h4><i class="bi bi-flask"></i> MicroLab</h4>
        <a onclick="showSection('dashboard')" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a onclick="showSection('students')"><i class="bi bi-people"></i> My Students</a>
        <a onclick="showSection('requests')"><i class="bi bi-clipboard-check"></i> Reservation
            <?php if ($pending_count > 0): ?>
                <span class="badge bg-danger ms-auto"><?= $pending_count ?></span>
            <?php endif; ?>
        </a>
        <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        <div class="sidebar-footer">
            <i class="bi bi-building"></i><br>
            Microbiology Lab<br>
            University of Kelaniya
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn d-lg-none text-dark" onclick="toggleSidebar()">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="fw-bold mb-0" style="background:linear-gradient(135deg,#22c55e,#16a34a);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                    Supervisor Dashboard
                </h5>
            </div>
            <div class="d-flex align-items-center gap-3">

                <!-- Notification Bell -->
                <div class="notification-bell" onclick="openNotificationModal()">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge">0</span>
                </div>

                <span class="fw-semibold d-none d-sm-block" style="color:#166534;">
                    <?= htmlspecialchars($full_name) ?>
                </span>

                <div class="dropdown">
                    <?php


                   $profile_image = $user_data['img_path'] ?? '';

if (!empty($profile_image)) {
    // Get just the filename from the path
    $filename = basename($profile_image);
    
    // Construct the correct path - images are in assets/profile_images/
    $clean_path = 'assets/profile_images/' . $filename;
    
    // Full system path for file checking
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $clean_path;
    
    error_log("Profile image - Filename: " . $filename);
    error_log("Profile image - Clean path: " . $clean_path);
    error_log("Profile image - Full path: " . $full_path);
    
    if (file_exists($full_path)) {
        // From views folder, need to go up one level to root
        $profile_image = '../' . $clean_path;
        error_log("Profile image - FOUND! Using: " . $profile_image);
    } else {
        // Try without the assets/ prefix if the path in DB already includes it
        $alt_path = ltrim($profile_image, '/');
        $full_alt_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $alt_path;
        
        if (file_exists($full_alt_path)) {
            $profile_image = '../' . $alt_path;
            error_log("Profile image - Found at alternative path: " . $profile_image);
        } else {
            error_log("Profile image - NOT FOUND, using avatar");
            $profile_image = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=100';
        }
    }
} else {
    error_log("Profile image - No image in database");
    $profile_image = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=100';
}
                    ?>

                   <img src="<?php echo $profile_image; ?>" class="profile-img dropdown-toggle" data-bs-toggle="dropdown">
<ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;border:none;box-shadow:0 10px 30px rgba(0,0,0,0.1);">
    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
</ul>
                </div>
            </div>
        </div>

        <!-- Notification Modal -->
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content" style="border-radius: 24px; border: none;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border-radius: 24px 24px 0 0;">
                        <h5 class="modal-title" id="notificationModalLabel">
                            <i class="bi bi-bell me-2"></i>Student Account Requests
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="min-height: 300px;">
                        <!-- Loading Spinner -->
                        <div id="notificationLoading" class="text-center py-5">
                            <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading requests...</p>
                        </div>

                        <!-- Requests Container -->
                        <div id="requestsContainer" style="display: none;"></div>

                        <!-- No Requests Message -->
                        <div id="noRequestsMessage" class="text-center py-5" style="display: none;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            <p class="mt-3 text-muted fs-5">No pending student requests</p>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e0e0e0;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Details Modal -->
        <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 24px; border: none;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border-radius: 24px 24px 0 0;">
                        <h5 class="modal-title" id="studentDetailsModalLabel">
                            <i class="bi bi-person-badge me-2"></i>Student Details
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" id="studentDetailsContent">
                        <!-- Loading spinner -->
                        <div class="text-center py-4" id="studentDetailsLoading">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading student details...</p>
                        </div>

                        <!-- Student details will be loaded here -->
                        <div id="studentDetailsDisplay" style="display: none;"></div>

                        <!-- Error message -->
                        <div id="studentDetailsError" style="display: none;" class="text-center py-4">
                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-danger">Error loading student details</p>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e0e0e0;">
                        <div id="studentModalActions">
                            <button class="btn-accept me-2" id="modalAcceptStudentBtn" onclick="acceptStudentFromModal()" style="display: none;">
                                <i class="bi bi-check-circle me-2"></i>Accept Request
                            </button>

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">

            <!-- ===== DASHBOARD SECTION ===== -->
            <div id="dashboardSection">
                <h3 class="mb-4" style="color:white;text-shadow:2px 2px 4px rgba(0,0,0,0.2);">Dashboard Overview</h3>

                <!-- Stats -->
                <div class="analytics-grid">
                    <div class="stat-card" onclick="showSection('students')">
                        <i class="bi bi-mortarboard-fill"></i>
                        <h3><?= $students_count ?></h3>
                        <p>My Students</p>
                    </div>
                    <div class="stat-card" onclick="showSection('requests')">
                        <i class="bi bi-hourglass-split"></i>
                        <h3><?= $pending_count ?></h3>
                        <p>Reseravtion Pending Approvals</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-calendar-check"></i>
                        <h3><?= $today_count ?></h3>
                        <p>Today's Practicals</p>
                    </div>
                    <div class="stat-card" onclick="showSection('requests')">
                        <i class="bi bi-journals"></i>
                        <h3><?= $total_reservations ?></h3>
                        <p>Total Reservations</p>
                    </div>
                </div>

                <!-- Recent Pending Requests Quick View -->
                <h4 class="mb-3" style="color:white;"><i class="bi bi-clock-history me-2"></i>Pending Approval Requests</h4>
                <div class="card p-4 mb-4">
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Student</th>
                                    <th>University ID</th>
                                    <th>Request Date</th>
                                    <th>Location</th>

                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $shown = 0;
                                foreach ($all_reservations as $res) {
                                    if ($res['status'] !== 'pending') continue;
                                    if ($shown >= 5) break;
                                    $shown++;
                                    $start = date('M d, Y', strtotime($res['request_date']));
                                    $end = ($res['continue_days'] > 1)
                                        ? ' – ' . date('M d, Y', strtotime($res['request_date'] . ' +' . ($res['continue_days'] - 1) . ' days'))
                                        : '';
                                ?>
                                    <tr>
                                        <td><small class="text-muted"><?= htmlspecialchars($res['reservation_id']) ?></small></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($res['student_name']) ?></small></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($res['university_id']) ?></small></td>
                                        <td><small class="text-muted"><?= $start . $end ?></small></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($res['location']) ?></small></td>

                                        <td>
                                            <button class="btn-view me-1" onclick="openRequestModal(<?= $res['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-approve me-1" onclick="quickAction(<?= $res['id'] ?>,'approve')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button class="btn-reject" onclick="quickAction(<?= $res['id'] ?>,'reject')">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if ($shown === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3"><i class="bi bi-check-circle text-success me-2"></i>No pending requests</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($pending_count > 5): ?>
                        <div class="text-end mt-3">
                            <button class="btn btn-success btn-sm px-4" onclick="showSection('requests')">
                                View All <?= $pending_count ?> Pending Requests <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Calendar -->
                <h3 class="mb-3" style="color:white;">Lab Booking Calendar</h3>
                <div class="calendar-container">
                    <div class="calendar-wrapper">
                        <div class="calendar-left">
                            <div class="calendar-header">
                                <i class="fas fa-angle-left prev"></i>
                                <div class="month" id="displayMonth"></div>
                                <i class="fas fa-angle-right next"></i>
                            </div>
                            <div class="weekdays">
                                <div>Sun</div>
                                <div>Mon</div>
                                <div>Tue</div>
                                <div>Wed</div>
                                <div>Thu</div>
                                <div>Fri</div>
                                <div>Sat</div>
                            </div>
                            <div class="days-grid" id="daysGrid"></div>
                            <div class="goto-section">
                                <input type="text" placeholder="MM/YYYY" class="goto-input" id="gotoInput" maxlength="7">
                                <button class="goto-btn" id="gotoBtn">Go</button>
                                <button class="today-btn" id="todayBtn">Today</button>
                            </div>
                        </div>
                        <div class="calendar-right">
                            <div class="event-day" id="eventDay"></div>
                            <div class="event-date" id="eventDate"></div>
                            <div class="events-list" id="eventsList">
                                <div class="no-event">Select a date to view bookings</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== STUDENTS SECTION ===== -->
            <div id="studentsSection" style="display:none;">
                <h3 class="mb-4" style="color:white;">My Students</h3>
                <div class="card p-4">
                    <div class="search-add-row">
                        <div class="search-container">
                            <input type="text" id="studentSearchInput" class="search-input" placeholder="Search by name, ID or email..." oninput="filterStudents()">
                            <button class="search-btn" onclick="filterStudents()"><i class="bi bi-search"></i> Search</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>University ID</th>
                                    <th>Email</th>
                                    <th>Reservations</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                <?php foreach ($all_students as $st): ?>
                                    <tr data-name="<?= strtolower($st['first_name'] . ' ' . $st['last_name']) ?>"
                                        data-uid="<?= strtolower($st['university_id']) ?>"
                                        data-email="<?= strtolower($st['email']) ?>">
                                        <td>
                                            <?php
                                            $simg = (!empty($st['img_path']) && file_exists($st['img_path']))
                                                ? $st['img_path']
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($st['first_name'] . ' ' . $st['last_name']) . '&background=22c55e&color=fff&size=50';
                                            ?>
                                            <img src="<?= $simg ?>" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #22c55e;">
                                        </td>
                                        <td><strong><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($st['university_id']) ?></td>
                                        <td><?= htmlspecialchars($st['email']) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= $st['request_count'] ?? 0 ?> reservations</span>
                                        </td>
                                        <td>
                                            <button class="btn-view" onclick="viewStudentReservations(<?= $st['id'] ?>, '<?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?>')">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($all_students)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No students assigned</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ===== REQUESTS SECTION ===== -->
            <div id="requestsSection" style="display:none;">
                <h3 class="mb-4" style="color:white;"><i class="bi bi-clipboard-check me-2"></i>Reservation Requests</h3>
                <div class="card p-4">
                    <!-- Filters -->
                    <div class="row mb-3 g-2">
                        <div class="col-md-4">
                            <input type="text" id="reqSearchInput" class="form-control" placeholder="Search by ID, student, location..." oninput="filterRequests()">
                        </div>
                        <div class="col-md-3">
                            <select id="reqStatusFilter" class="form-select" onchange="filterRequests()">
                                <option value="all">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="reqTimeFilter" class="form-select" onchange="filterRequests()">
                                <option value="all">All Time</option>
                                <option value="weekly">This Week</option>
                                <option value="monthly">This Month</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Student</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="requestsTableBody">
                                <?php foreach ($all_reservations as $res):
                                    $start = date('M d', strtotime($res['request_date']));
                                    $end   = ($res['continue_days'] > 1)
                                        ? '–' . date('M d', strtotime($res['request_date'] . ' +' . ($res['continue_days'] - 1) . ' days'))
                                        : '';
                                    $created = date('M d, Y', strtotime($res['created_datetime']));
                                ?>
                                    <tr data-status="<?= $res['status'] ?>"
                                        data-created="<?= $res['created_datetime'] ?>"
                                        data-search="<?= strtolower($res['reservation_id'] . ' ' . $res['student_name'] . ' ' . $res['location']) ?>">
                                        <td><small class="text-muted"><?= htmlspecialchars($res['reservation_id']) ?></small></td>

                                        <td>
                                            <?= htmlspecialchars($res['student_name']) ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($res['university_id']) ?></small>
                                        </td>



                                        <td><span class="status-badge status-<?= $res['status'] ?>"><?= ucfirst($res['status']) ?></span></td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <button class="btn-view" onclick="openRequestModal(<?= $res['id'] ?>)" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <?php if ($res['status'] === 'pending'): ?>
                                                    <button class="btn-approve" onclick="quickAction(<?= $res['id'] ?>,'approve')" title="Approve">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                    <button class="btn-reject" onclick="quickAction(<?= $res['id'] ?>,'reject')" title="Reject">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($all_reservations)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">No reservation requests found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /content-area -->
    </div><!-- /main-content -->

    <!-- ===== REQUEST DETAILS MODAL ===== -->
    <div class="modal fade" id="requestDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius:24px;border:none;">
                <div class="modal-header" style="background:linear-gradient(135deg,#22c55e,#16a34a);color:white;border-radius:24px 24px 0 0;">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Reservation Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="requestDetailsContent"></div>
                <div class="modal-footer" id="requestModalFooter" style="border-top:1px solid #f0f0f0;">
                    <div id="rejectReasonWrap" class="reject-reason-wrap w-100">
                        <label class="form-label fw-semibold text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Rejection Reason</label>
                        <textarea id="rejectReasonText" class="form-control" rows="2" placeholder="Enter reason for rejection..."></textarea>
                        <div class="d-flex gap-2 mt-2">
                            <button class="btn btn-danger" onclick="confirmReject()">Confirm Reject</button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="cancelReject()">Cancel</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success" id="modalApproveBtn" onclick="modalAction('approve')">
                        <i class="bi bi-check-circle me-2"></i>Approve
                    </button>
                    <button type="button" class="btn btn-danger" id="modalRejectBtn" onclick="modalAction('reject')">
                        <i class="bi bi-x-circle me-2"></i>Reject
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== STUDENT RESERVATIONS MODAL ===== -->
    <div class="modal fade" id="studentReservationsModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content" style="border-radius:24px;border:none;">
                <div class="modal-header" style="background:linear-gradient(135deg,#22c55e,#16a34a);color:white;border-radius:24px 24px 0 0;">
                    <h5 class="modal-title" id="studentReservationsTitle"><i class="bi bi-journal-text me-2"></i>Student Reservations</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="studentReservationsContent"></div>
                <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>

    <!-- Quick Reject Confirm (inline quick actions) -->
    <div class="modal fade" id="quickRejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:20px;border:none;">
                <div class="modal-header" style="background:linear-gradient(135deg,#dc3545,#c82333);color:white;border-radius:20px 20px 0 0;">
                    <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Reservation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-semibold">Reason for rejection <span class="text-danger">*</span></label>
                    <textarea id="quickRejectReason" class="form-control" rows="3" placeholder="Enter reason..."></textarea>
                    <input type="hidden" id="quickRejectResId">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" onclick="submitQuickReject()"><i class="bi bi-x-circle me-2"></i>Confirm Reject</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update your openNotificationModal function
        function openNotificationModal() {
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
            modal.show();

            // Fetch pending requests
            fetchPendingRequests();

            // Optional: Mark notification as read when opened
            // You can add this if you want to clear the badge when modal is opened
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                badge.style.display = 'none';
            }
        }

        // Update your fetchPendingRequests function
        function fetchPendingRequests() {
            // Show loading, hide others
            document.getElementById('notificationLoading').style.display = 'block';
            document.getElementById('requestsContainer').style.display = 'none';
            document.getElementById('noRequestsMessage').style.display = 'none';

            // AJAX request to get pending students
            fetch('../controllers/get_pending_students.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('notificationLoading').style.display = 'none';

                    if (data.success && data.requests.length > 0) {
                        displayRequests(data.requests);
                        document.getElementById('requestsContainer').style.display = 'block';

                        // Update notification badge ONLY if there are requests
                        const badge = document.getElementById('notificationBadge');
                        if (badge) {
                            badge.textContent = data.requests.length;
                            badge.style.display = 'flex'; // Show badge
                            badge.style.backgroundColor = '#dc3545'; // Red color for pending requests
                        }
                    } else {
                        document.getElementById('noRequestsMessage').style.display = 'block';

                        // Hide the badge completely when there are no requests
                        const badge = document.getElementById('notificationBadge');
                        if (badge) {
                            badge.style.display = 'none'; // Hide badge when 0
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('notificationLoading').style.display = 'none';
                    document.getElementById('noRequestsMessage').style.display = 'block';
                    document.getElementById('noRequestsMessage').innerHTML = `
                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                <p class="mt-2">Error loading requests</p>
            `;

                    // Hide badge on error
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        badge.style.display = 'none';
                    }
                });
        }

        // Function to display requests
        function displayRequests(requests) {
            const container = document.getElementById('requestsContainer');
            container.innerHTML = '';

            requests.forEach(request => {
                const initials = (request.first_name?.[0] || '') + (request.last_name?.[0] || '');
                const fullName = `${request.first_name} ${request.last_name}`.trim();
                const createdDate = new Date(request.join_datetime).toLocaleString();

                const card = document.createElement('div');
                card.className = 'request-card';
                card.id = `request-${request.id}`;
                card.innerHTML = `
            <div class="request-header">
                <div class="request-avatar">${initials || '?'}</div>
                <div class="request-info">
                    <div class="request-name">${fullName || 'Unknown'}</div>
                    <div class="request-university">${request.university_id || 'No University ID'}</div>
                </div>
            </div>
            <div class="request-details">
                <i class="bi bi-person-plus me-1"></i>
                Student account creation request
                <br>
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    Requested: ${createdDate}
                </small>
            </div>
            <div class="request-actions">
               <button class="btn-view" onclick="viewStudent(${request.id})">
    <i class="bi bi-eye me-1"></i>View
</button>
              <button class="btn-accept" onclick="acceptStudent(${request.id}, 'modal', event)">
                    <i class="bi bi-check-circle me-1"></i>Accept
                </button>
            </div>
        `;
                container.appendChild(card);
            });
        }

        // Function to accept student
        // Function to accept student
        function acceptStudent(studentId, source = 'modal', event) {

            if (!confirm('Accept this student account request?')) return;


            // Prevent default and stop propagation to avoid any issues
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            // Find and hide the button FIRST (before removing card)
            if (event && event.target) {
                const clickedBtn = event.target.closest('.btn-accept');
                if (clickedBtn) {
                    clickedBtn.style.display = 'none'; // Hide the button
                    console.log('Button hidden successfully'); // Debug log
                }
            }

            fetch('../controllers/accept_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        student_id: studentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        //  alert('Student accepted successfully!');



                        if (source === 'modal') {
                            // Remove card from modal
                            const card = document.getElementById(`request-${studentId}`);
                            if (card) {
                                // Add a small delay before removing so user can see button disappear
                                setTimeout(() => {
                                    card.remove();

                                    // Update badge
                                    const badge = document.getElementById('notificationBadge');
                                    if (badge) {
                                        badge.textContent = Math.max(0, parseInt(badge.textContent) - 1);
                                    }

                                    // If no more requests, show empty message
                                    if (document.querySelectorAll('.request-card').length === 0) {
                                        const container = document.getElementById('requestsContainer');
                                        const noRequests = document.getElementById('noRequestsMessage');
                                        if (container) container.style.display = 'none';
                                        if (noRequests) noRequests.style.display = 'block';
                                    }
                                }, 300); // Small delay to see button hide before card removes
                            }

                            // Update badge immediately
                            const badge = document.getElementById('notificationBadge');
                            if (badge) {
                                badge.textContent = Math.max(0, parseInt(badge.textContent) - 1);
                            }

                        } else if (source === 'view') {
                            // If on view page, show accepted message and disable buttons
                            const acceptBtn = document.querySelector('button[onclick*="acceptStudent"]');
                            const viewBtns = document.querySelectorAll('.btn-view, .btn-secondary');

                            if (acceptBtn) {
                                acceptBtn.disabled = true;
                                acceptBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Accepted';
                                acceptBtn.classList.remove('btn-success');
                                acceptBtn.classList.add('btn-secondary');
                            }

                            // Disable any other action buttons
                            viewBtns.forEach(btn => {
                                if (btn.onclick && btn.onclick.toString().includes('window.close')) {
                                    // Keep close button enabled
                                } else {
                                    btn.disabled = true;
                                }
                            });

                            // Show status message on page
                            const statusDiv = document.createElement('div');
                            statusDiv.className = 'alert alert-success mt-3';
                            statusDiv.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> This student has been accepted.';

                            const card = document.querySelector('.card-body');
                            if (card) {
                                card.insertBefore(statusDiv, card.querySelector('hr'));
                            }
                        }

                        // Refresh parent window if it exists (for view page opened from modal)
                        if (window.opener && !window.opener.closed) {
                            window.opener.refreshNotifications();
                        }
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error accepting student');
                });
        }

        // Global variable to store current student ID
        let currentStudentId = null;

        // Function to view student details in modal
        function viewStudent(studentId) {
            currentStudentId = studentId;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
            modal.show();

            // Show loading, hide content and error
            document.getElementById('studentDetailsLoading').style.display = 'block';
            document.getElementById('studentDetailsDisplay').style.display = 'none';
            document.getElementById('studentDetailsError').style.display = 'none';
            document.getElementById('modalAcceptStudentBtn').style.display = 'none';

            // Fetch student details via AJAX
            fetch(`../controllers/get_student_details.php?id=${studentId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('studentDetailsLoading').style.display = 'none';

                    if (data.success) {
                        displayStudentDetails(data.student);
                        document.getElementById('studentDetailsDisplay').style.display = 'block';

                        // Show accept button only if status is pending (0)
                        if (data.student.status == 0) {
                            document.getElementById('modalAcceptStudentBtn').style.display = 'inline-block';
                        }
                    } else {
                        document.getElementById('studentDetailsError').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('studentDetailsLoading').style.display = 'none';
                    document.getElementById('studentDetailsError').style.display = 'block';
                });
        }

        // Function to display student details in modal
        function displayStudentDetails(student) {
            const fullName = `${student.first_name || ''} ${student.last_name || ''}`.trim();
            const joinDate = new Date(student.join_datetime).toLocaleString();
            const statusText = student.status == 0 ? 'Pending Approval' : 'Accepted';
            const statusClass = student.status == 0 ? 'status-pending' : 'status-accepted';

            const html = `
        <div class="row">
            <div class="col-12 text-center mb-4">
                <div class="student-avatar-large" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto; box-shadow: 0 10px 20px rgba(34,197,94,0.3);">
                    ${(student.first_name?.[0] || '') + (student.last_name?.[0] || '') || '?'}
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-card p-3 mb-3" style="background: #f8f9fa; border-radius: 16px;">
                    <h6 class="text-success fw-bold mb-3"><i class="bi bi-person me-2"></i>Personal Info</h6>
                    <table class="table table-borderless mb-0" style="font-size: 0.95rem;">
                        <tr>
                            <th style="width: 120px; color: #6c757d;">Full Name</th>
                            <td class="fw-semibold">${fullName}</td>
                        </tr>
                        <tr>
                            <th style="color: #6c757d;">University ID</th>
                            <td><span class="badge bg-info text-white p-2">${student.university_id || 'N/A'}</span></td>
                        </tr>
                        <tr>
                            <th style="color: #6c757d;">Status</th>
                            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-card p-3 mb-3" style="background: #f8f9fa; border-radius: 16px;">
                    <h6 class="text-success fw-bold mb-3"><i class="bi bi-envelope me-2"></i>Contact Info</h6>
                    <table class="table table-borderless mb-0" style="font-size: 0.95rem;">
                        <tr>
                            <th style="width: 80px; color: #6c757d;">Email</th>
                            <td><a href="mailto:${student.email}" class="text-success">${student.email}</a></td>
                        </tr>
                        <tr>
                            <th style="color: #6c757d;">Mobile</th>
                            <td>${student.mobile || 'Not provided'}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="col-12">
                <div class="info-card p-3" style="background: #f0fdf4; border-radius: 16px; border-left: 4px solid #22c55e;">
                    <h6 class="text-success fw-bold mb-3"><i class="bi bi-calendar me-2"></i>Account Info</h6>
                    <table class="table table-borderless mb-0" style="font-size: 0.95rem;">
                        <tr>
                            <th style="width: 120px; color: #6c757d;">Request Date</th>
                            <td>${joinDate}</td>
                        </tr>
                       
                    </table>
                </div>
            </div>
        </div>
    `;

            document.getElementById('studentDetailsDisplay').innerHTML = html;
        }

        // Function to accept student from modal
        // Function to accept student from modal
        function acceptStudentFromModal() {
            if (!currentStudentId) return;

            if (!confirm('Accept this student account request?')) return;

            // Get the accept button
            const modalAcceptBtn = document.getElementById('modalAcceptStudentBtn');

            // Store original button text and disable it
            if (modalAcceptBtn) {
                modalAcceptBtn.disabled = true;
                modalAcceptBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Waiting...';
                console.log('Modal accept button - waiting state');
            }

            fetch('../controllers/accept_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        student_id: currentStudentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Change button to "Notify Student" before hiding
                        if (modalAcceptBtn) {
                            modalAcceptBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Notify Student';
                            modalAcceptBtn.style.background = '#28a745';
                            modalAcceptBtn.disabled = false;
                        }

                        // Update the status badge in the modal to show "Accepted"
                        const statusBadge = document.querySelector('#studentDetailsDisplay .status-badge');
                        if (statusBadge) {
                            statusBadge.className = 'status-badge status-accepted';
                            statusBadge.textContent = 'Accepted';
                            statusBadge.style.background = '#d1e7dd';
                            statusBadge.style.color = '#0a3622';
                        }

                        // Close modal after a short delay
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('studentDetailsModal'));
                            if (modal) {
                                modal.hide();
                            }
                        }, 1500); // Longer delay to show "Notify Student" message

                        // Remove card from notification modal if it exists
                        const card = document.getElementById(`request-${currentStudentId}`);
                        if (card) {
                            setTimeout(() => {
                                card.remove();
                            }, 1500);
                        }

                        // Update badge
                        const badge = document.getElementById('notificationBadge');
                        if (badge) {
                            const currentCount = parseInt(badge.textContent) || 0;
                            badge.textContent = Math.max(0, currentCount - 1);

                            if (badge.textContent === '0') {
                                badge.style.backgroundColor = '#6c757d';
                            }
                        }

                        // Refresh pending requests if modal is open
                        const notificationModal = document.getElementById('notificationModal');
                        if (notificationModal && notificationModal.classList.contains('show')) {
                            fetchPendingRequests();
                        }

                        // Refresh parent window if it exists
                        if (window.opener && !window.opener.closed) {
                            window.opener.refreshNotifications();
                        }

                    } else {
                        alert('Error: ' + data.message);
                        // If error, restore original button
                        if (modalAcceptBtn) {
                            modalAcceptBtn.disabled = false;
                            modalAcceptBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Accept Request';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error accepting student');
                    // If error, restore original button
                    if (modalAcceptBtn) {
                        modalAcceptBtn.disabled = false;
                        modalAcceptBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Accept Request';
                    }
                });
        }

        // Function to refresh notifications (called from child window)
        function refreshNotifications() {
            fetch('../controllers/get_pending_students.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('notificationBadge').textContent = data.requests.length;

                        // If modal is open, refresh the display
                        const modal = document.getElementById('notificationModal');
                        if (modal.classList.contains('show')) {
                            if (data.requests.length > 0) {
                                displayRequests(data.requests);
                                document.getElementById('requestsContainer').style.display = 'block';
                                document.getElementById('noRequestsMessage').style.display = 'none';
                            } else {
                                document.getElementById('requestsContainer').style.display = 'none';
                                document.getElementById('noRequestsMessage').style.display = 'block';
                            }
                        }
                    }
                })
                .catch(error => console.error('Error refreshing:', error));
        }

        // Auto-refresh notifications every 30 seconds
        setInterval(() => {
            const modal = document.getElementById('notificationModal');
            if (!modal.classList.contains('show')) {
                refreshNotifications();
            }
        }, 30000);








        // ===== RESERVATION DATA (from PHP) =====
        const allReservations = <?php echo json_encode($all_reservations); ?>;
        const allStudents = <?php echo json_encode($all_students); ?>;
        let currentModalResId = null;

        // ===== SIDEBAR =====
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        function showSection(section) {
            ['dashboard', 'students', 'requests'].forEach(s => {
                const el = document.getElementById(s + 'Section');
                if (el) el.style.display = 'none';
            });
            const sectionEl = document.getElementById(section + 'Section');
            if (sectionEl) sectionEl.style.display = 'block';

            document.querySelectorAll('.sidebar a').forEach(a => {
                a.classList.remove('active');
                if (a.getAttribute('onclick') && a.getAttribute('onclick').includes(section)) {
                    a.classList.add('active');
                }
            });
        }

        // ===== STUDENT FILTER =====
        function filterStudents() {
            const term = document.getElementById('studentSearchInput').value.toLowerCase();
            document.querySelectorAll('#studentTableBody tr').forEach(row => {
                if (!row.dataset.name) return;
                const match = row.dataset.name.includes(term) ||
                    row.dataset.uid.includes(term) ||
                    row.dataset.email.includes(term);
                row.style.display = match ? '' : 'none';
            });
        }

        // ===== REQUEST FILTERS =====
        function filterRequests() {
            const term = document.getElementById('reqSearchInput').value.toLowerCase();
            const status = document.getElementById('reqStatusFilter').value;
            const time = document.getElementById('reqTimeFilter').value;
            const now = new Date();

            document.querySelectorAll('#requestsTableBody tr').forEach(row => {
                if (!row.dataset.status) return;
                let show = true;

                if (term && !row.dataset.search.includes(term)) show = false;
                if (status !== 'all' && row.dataset.status !== status) show = false;

                if (time !== 'all') {
                    const created = new Date(row.dataset.created);
                    const diff = (now - created) / (1000 * 60 * 60 * 24);
                    if (time === 'weekly' && diff > 7) show = false;
                    if (time === 'monthly' && diff > 30) show = false;
                }

                row.style.display = show ? '' : 'none';
            });
        }

        // ===== REQUEST DETAILS MODAL =====
        function openRequestModal(resId) {
            const res = allReservations.find(r => r.id == resId);
            if (!res) return;
            currentModalResId = resId;

            const isPending = res.status === 'pending';
            const approveBtn = document.getElementById('modalApproveBtn');
            const rejectBtn = document.getElementById('modalRejectBtn');
            if (approveBtn) approveBtn.style.display = isPending ? 'inline-block' : 'none';
            if (rejectBtn) rejectBtn.style.display = isPending ? 'inline-block' : 'none';
            document.getElementById('rejectReasonWrap').style.display = 'none';

            const start = new Date(res.request_date);
            const end = new Date(res.request_date);
            end.setDate(end.getDate() + parseInt(res.continue_days) - 1);
            const dateStr = res.continue_days > 1 ?
                start.toDateString() + ' → ' + end.toDateString() + ' (' + res.continue_days + ' days)' :
                start.toDateString();

            const statusColors = {
                pending: '#856404',
                approved: '#0a3622',
                rejected: '#842029'
            };
            const statusBg = {
                pending: '#fff3cd',
                approved: '#d1e7dd',
                rejected: '#f8d7da'
            };

            document.getElementById('requestDetailsContent').innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 rounded-3" style="background:#f8f9fa;">
                    <h6 class="text-success fw-bold mb-3"><i class="bi bi-clipboard me-2"></i>Reservation Info</h6>
                    <table class="table table-borderless mb-0" style="font-size:0.9rem;">
                        <tr><th style="width:130px;">Res. ID</th><td><strong>${res.reservation_id}</strong></td></tr>
                        <tr><th>Created</th><td>${new Date(res.created_datetime).toLocaleString()}</td></tr>
                        <tr><th>Date(s)</th><td>${dateStr}</td></tr>
                        <tr><th>Location</th><td>${res.location}</td></tr>
                        <tr><th>Status</th><td><span style="padding:4px 10px;border-radius:12px;background:${statusBg[res.status]};color:${statusColors[res.status]};font-weight:600;font-size:0.82rem;">${res.status.toUpperCase()}</span></td></tr>
                        ${res.comment ? `<tr><th>Comment</th><td><em>${res.comment}</em></td></tr>` : ''}
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded-3" style="background:#f0fdf4;">
                    <h6 class="text-success fw-bold mb-3"><i class="bi bi-person me-2"></i>Student Info</h6>
                    <table class="table table-borderless mb-0" style="font-size:0.9rem;">
                        <tr><th style="width:130px;">Name</th><td>${res.student_name}</td></tr>
                        <tr><th>Reg. No.</th><td>${res.university_id}</td></tr>
                        <tr><th>Email</th><td>${res.student_email}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-12">
                <div class="p-3 rounded-3" style="background:#fff8f0;">
                    <h6 class="text-success fw-bold mb-2"><i class="bi bi-tools me-2"></i>Equipment Requested</h6>
                    <div style="font-size:0.9rem;line-height:1.8;">${res.equipment_list || 'N/A'}</div>
                </div>
            </div>
            ${res.reject_reason ? `
            <div class="col-12">
                <div class="p-3 rounded-3" style="background:#fff5f5;border-left:4px solid #dc3545;">
                    <h6 class="text-danger fw-bold mb-1"><i class="bi bi-exclamation-triangle me-2"></i>Rejection Reason</h6>
                    <p class="mb-0">${res.reject_reason}</p>
                </div>
            </div>` : ''}
        </div>
    `;

            new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
        }

        function modalAction(action) {
            if (action === 'approve') {
                submitAction(currentModalResId, 'approve', '');
            } else {
                document.getElementById('rejectReasonWrap').style.display = 'block';
                document.getElementById('modalApproveBtn').style.display = 'none';
                document.getElementById('modalRejectBtn').style.display = 'none';
            }
        }

        function confirmReject() {
            const reason = document.getElementById('rejectReasonText').value.trim();
            if (!reason) {
                alert('Please enter a rejection reason.');
                return;
            }
            submitAction(currentModalResId, 'reject', reason);
            bootstrap.Modal.getInstance(document.getElementById('requestDetailsModal')).hide();
        }

        function cancelReject() {
            document.getElementById('rejectReasonWrap').style.display = 'none';
            document.getElementById('modalApproveBtn').style.display = 'inline-block';
            document.getElementById('modalRejectBtn').style.display = 'inline-block';
        }

        // ===== QUICK ACTION (from table buttons) =====
        function quickAction(resId, action) {
            if (action === 'approve') {
                if (confirm('Approve this reservation? It will be forwarded to the Technical Officer.')) {
                    submitAction(resId, 'approve', '');
                }
            } else {
                document.getElementById('quickRejectResId').value = resId;
                document.getElementById('quickRejectReason').value = '';
                new bootstrap.Modal(document.getElementById('quickRejectModal')).show();
            }
        }

        function submitQuickReject() {
            const reason = document.getElementById('quickRejectReason').value.trim();
            const resId = document.getElementById('quickRejectResId').value;
            if (!reason) {
                alert('Please enter a rejection reason.');
                return;
            }
            bootstrap.Modal.getInstance(document.getElementById('quickRejectModal')).hide();
            submitAction(resId, 'reject', reason);
        }

        // ===== SUBMIT APPROVE / REJECT via AJAX =====
        function submitAction(resId, action, reason) {
            const formData = new FormData();
            formData.append('reservation_id', resId);
            formData.append('action', action);
            formData.append('reason', reason);

            fetch('../controllers/handle_reservation.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(action === 'approve' ?
                            '✅ Reservation approved! Forwarded to Technical Officer.' :
                            '❌ Reservation rejected.');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(() => alert('Network error. Please try again.'));
        }

        // ===== VIEW STUDENT RESERVATIONS =====
        function viewStudentReservations(studentId, studentName) {
            document.getElementById('studentReservationsTitle').innerHTML =
                `<i class="bi bi-journal-text me-2"></i>${studentName} — Reservations`;

            fetch(`../controllers/get_student_reservations.php?student_id=${studentId}`)
                .then(r => r.text())
                .then(html => {
                    document.getElementById('studentReservationsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('studentReservationsModal')).show();
                })
                .catch(() => {
                    document.getElementById('studentReservationsContent').innerHTML =
                        '<p class="text-muted text-center py-4">Unable to load reservations.</p>';
                    new bootstrap.Modal(document.getElementById('studentReservationsModal')).show();
                });
        }

        // ===== CALENDAR =====
        let month = new Date().getMonth();
        let year = new Date().getFullYear();
        let activeDay = new Date().getDate();
        const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const eventsArr = <?= $calendar_events_json ?: '[]' ?>;

        function initCalendar() {
            const firstDay = new Date(year, month, 1).getDay();
            const lastDate = new Date(year, month + 1, 0).getDate();
            const prevLast = new Date(year, month, 0).getDate();
            const nextDays = 7 - new Date(year, month + 1, 0).getDay() - 1;

            const displayMonth = document.getElementById('displayMonth');
            if (displayMonth) displayMonth.textContent = months[month] + ' ' + year;

            let days = '';
            for (let x = firstDay; x > 0; x--)
                days += `<div class="day-cell prev-date">${prevLast - x + 1}</div>`;

            for (let i = 1; i <= lastDate; i++) {
                let hasEvent = eventsArr.some(e => {
                    if (e.year !== year || e.month !== month + 1) return false;
                    return i >= e.day && i <= e.end_day;
                });
                let cls = 'day-cell';
                const today = new Date();
                if (i === today.getDate() && year === today.getFullYear() && month === today.getMonth()) cls += ' today';
                if (hasEvent) cls += ' event';
                if (i === activeDay) cls += ' active';
                days += `<div class="${cls}" data-day="${i}">${i}</div>`;
            }
            for (let j = 1; j <= nextDays; j++)
                days += `<div class="day-cell next-date">${j}</div>`;

            const daysGrid = document.getElementById('daysGrid');
            if (daysGrid) daysGrid.innerHTML = days;

            document.querySelectorAll('.day-cell:not(.prev-date):not(.next-date)').forEach(cell => {
                cell.addEventListener('click', function() {
                    document.querySelectorAll('.day-cell').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    activeDay = parseInt(this.dataset.day);
                    updateEventDisplay(activeDay);
                });
            });
            updateEventDisplay(activeDay);
        }

        function updateEventDisplay(day) {
            const d = new Date(year, month, day);
            const eventDay = document.getElementById('eventDay');
            const eventDate = document.getElementById('eventDate');
            if (eventDay) eventDay.textContent = d.toString().split(' ')[0];
            if (eventDate) eventDate.textContent = `${day} ${months[month]} ${year}`;

            const dayEvents = eventsArr.filter(e => {
                if (e.year !== year || e.month !== month + 1) return false;
                return day >= e.day && day <= e.end_day;
            });

            const eventsList = document.getElementById('eventsList');
            if (!eventsList) return;

            if (!dayEvents.length) {
                eventsList.innerHTML = '<div class="no-event">No bookings on this day</div>';
                return;
            }

            eventsList.innerHTML = dayEvents.map(e => {
                const eqList = e.equipment ? e.equipment.split('|').map(eq => `<div style="font-size:0.78rem;">• ${eq}</div>`).join('') : '';
                return `
        <div class="event-item">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="event-title"><i class="fas fa-circle me-2" style="color:#ffd700;font-size:0.6rem;"></i>${e.title}</span>
                <small style="background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:10px;">${e.duration}</small>
            </div>
            <div class="event-meta"><i class="bi bi-person me-1"></i>${e.student}</div>
            <div class="event-meta"><i class="bi bi-pin-map-fill me-1"></i>${e.location}</div>
            <div class="event-meta mt-1">${eqList}</div>
        </div>`;
            }).join('');
        }

        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');
        const todayBtn = document.getElementById('todayBtn');
        const gotoBtn = document.getElementById('gotoBtn');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                month--;
                if (month < 0) {
                    month = 11;
                    year--;
                }
                initCalendar();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                month++;
                if (month > 11) {
                    month = 0;
                    year++;
                }
                initCalendar();
            });
        }

        if (todayBtn) {
            todayBtn.addEventListener('click', () => {
                const t = new Date();
                month = t.getMonth();
                year = t.getFullYear();
                activeDay = t.getDate();
                initCalendar();
            });
        }

        if (gotoBtn) {
            gotoBtn.addEventListener('click', () => {
                const input = document.getElementById('gotoInput');
                if (!input) return;
                const parts = input.value.split('/');
                if (parts.length === 2) {
                    const m = parseInt(parts[0]) - 1;
                    const y = parseInt(parts[1]);
                    if (m >= 0 && m < 12 && y > 2000) {
                        month = m;
                        year = y;
                        initCalendar();
                    } else alert('Use MM/YYYY');
                } else alert('Use MM/YYYY');
            });
        }

        // Function to check pending students and update badge
        function checkPendingStudents() {
            fetch('../controllers/get_pending_students.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        if (data.success && data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'inline-block';
                            badge.style.backgroundColor = '#dc3545'; // Red for notifications

                            // Optional: Add animation
                            badge.style.animation = 'pulse 1s';
                            setTimeout(() => {
                                badge.style.animation = '';
                            }, 1000);
                        } else {
                            badge.style.display = 'none';
                            //  badge.textContent = '0';
                            // badge.style.display = 'inline-block'; 
                            //  badge.style.backgroundColor = '#6c757d'; // Gray for zero
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking pending students:', error);
                    // Don't hide badge on error, just keep current value
                });
        }


        document.addEventListener('DOMContentLoaded', () => {
            showSection('dashboard');
            initCalendar();
            checkPendingStudents();

            // Set up interval to check every 30 seconds
            setInterval(checkPendingStudents, 30000);
        });
    </script>
</body>

</html>
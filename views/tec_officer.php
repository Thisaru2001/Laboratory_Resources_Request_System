<?php
session_start();
require_once '../config/database.php';


// Check if user is logged in and is a technical officer
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'technical_officer') {
    header("Location: ../index.php");
    exit();
}

$technical_officer_id = $_SESSION["user_id"];

// Get technical officer details
$user_query = "SELECT first_name, last_name, email, mobile, university_id, img_path, join_datetime FROM lab_user WHERE id = ?";
$user_result = Database::search($user_query, "i", [$technical_officer_id]);
$user_data = [];
$first_name = 'Technical';
$last_name = 'Officer';
$profile_image = '';
if ($user_result && $user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $first_name   = $user_data['first_name'] ?? 'Technical';
    $last_name    = $user_data['last_name']  ?? 'Officer';
    $profile_image = $user_data['img_path']   ?? '';
}
$full_name = trim($first_name . ' ' . $last_name);

// ---------- DASHBOARD STATS ----------
// Total students count
$students_count_q = "SELECT COUNT(DISTINCT u.id) as cnt 
                     FROM lab_user u
                     JOIN lab_user_has_role ur ON u.id = ur.lab_user_id
                     JOIN role r ON ur.role_id = r.id
                     WHERE r.role = 'student' AND u.status = 1";
$sc = Database::search($students_count_q);
$students_count = ($sc && $sc->num_rows > 0) ? $sc->fetch_assoc()['cnt'] : 0;

// Total equipment count
$equipment_count_q = "SELECT COUNT(*) as cnt FROM equipment WHERE is_hod_checked = 1";
$ec = Database::search($equipment_count_q);
$equipment_count = ($ec && $ec->num_rows > 0) ? $ec->fetch_assoc()['cnt'] : 0;

// Calculate Equipment Utilization Rate
$utilization_query = "
    SELECT 
        (SELECT COALESCE(SUM(total_qty), 0) FROM equipment) as total_qty,
        (SELECT COALESCE(SUM(broken_qty), 0) FROM broken) as broken_qty,
        (SELECT COALESCE(SUM(repair_qty), 0) FROM repair) as repair_qty
";

$utilization_result = Database::search($utilization_query);
$utilization_rate = 0;

if ($utilization_result && $utilization_result->num_rows > 0) {
    $row = $utilization_result->fetch_assoc();
    $total_qty = (int)$row['total_qty'];
    $broken_qty = (int)$row['broken_qty'];
    $repair_qty = (int)$row['repair_qty'];

    $available_qty = $total_qty - ($broken_qty + $repair_qty);

    if ($total_qty > 0) {
        $utilization_rate = round(($available_qty / $total_qty) * 100);
    }
}

// Equipment in maintenance (from broken table)
$maintenance_q = "SELECT COUNT(DISTINCT equipment_id) as cnt FROM broken";
$mq = Database::search($maintenance_q);
$maintenance_count = ($mq && $mq->num_rows > 0) ? $mq->fetch_assoc()['cnt'] : 0;

// Pending equipment requests (reservations waiting for technical officer approval)
$pending_q = "SELECT COUNT(DISTINCT r.id) as cnt 
              FROM reservation r
              WHERE r.supervisor_id IS NOT NULL 
                AND r.technical_officer_id IS NULL
                AND NOT EXISTS (SELECT 1 FROM reject_reason rr WHERE rr.reservation_id = r.id)";
$pq = Database::search($pending_q);
$pending_count = ($pq && $pq->num_rows > 0) ? $pq->fetch_assoc()['cnt'] : 0;

// Today's practicals
$today = date('Y-m-d');
$today_q = "SELECT COUNT(DISTINCT r.id) as cnt 
            FROM reservation r
            WHERE ? BETWEEN r.request_date 
              AND DATE_ADD(r.request_date, INTERVAL (r.continue_days - 1) DAY)
              AND r.technical_officer_id IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM reject_reason rr WHERE rr.reservation_id = r.id)";
$tq = Database::search($today_q, "s", [$today]);
$today_count = ($tq && $tq->num_rows > 0) ? $tq->fetch_assoc()['cnt'] : 0;

// ---------- CALENDAR EVENTS (all approved reservations) ----------
$cal_q = "SELECT r.id, r.reservation_id, r.request_date, r.continue_days,
                 l.location,
                 CONCAT(st.first_name,' ',st.last_name) as student_name,
                 GROUP_CONCAT(CONCAT(e.name,' (x',be.book_qty,')') SEPARATOR '|') as equipment_list
          FROM reservation r
          JOIN location l ON r.location_id = l.id
          JOIN lab_user st ON r.student_id = st.id
          LEFT JOIN book_equipment be ON r.id = be.reservation_id
          LEFT JOIN equipment e ON be.equipment_id = e.id
          WHERE r.technical_officer_id IS NOT NULL
            AND NOT EXISTS (SELECT 1 FROM reject_reason rr WHERE rr.reservation_id = r.id)
          GROUP BY r.id";
$cal_result = Database::search($cal_q);

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

// ---------- EQUIPMENT LIST ----------
// ---------- EQUIPMENT LIST ----------
$equipment_q = "SELECT e.id, e.code, e.name, e.total_qty, e.description,
                       e.sterilization_required, e.reservation_required,
                       GROUP_CONCAT(DISTINCT l.location SEPARATOR ', ') as location,
                       COALESCE((SELECT SUM(broken_qty) FROM broken WHERE equipment_id = e.id), 0) as broken_qty,
                       COALESCE((SELECT SUM(repair_qty) FROM repair WHERE equipment_id = e.id), 0) as repair_qty,
                       COALESCE((SELECT SUM(be.book_qty) FROM book_equipment be 
                                JOIN reservation r ON be.reservation_id = r.id 
                                WHERE be.equipment_id = e.id 
                                AND r.request_date <= CURDATE() 
                                AND DATE_ADD(r.request_date, INTERVAL (r.continue_days - 1) DAY) >= CURDATE()), 0) as booked_qty
                FROM equipment e
                LEFT JOIN equipment_has_location ehl ON e.id = ehl.equipment_id
                LEFT JOIN location l ON ehl.location_id = l.id
                WHERE e.is_hod_checked = 1
                GROUP BY e.id
                ORDER BY e.name";
$equipment_result = Database::search($equipment_q);

$equipmentDataTable = [];
if ($equipment_result && $equipment_result->num_rows > 0) {
    while ($row = $equipment_result->fetch_assoc()) {
        $available = $row['total_qty'] - $row['broken_qty'] - $row['repair_qty'] - $row['booked_qty'];
        $usage = $row['total_qty'] > 0 ? round((($row['total_qty'] - $available) / $row['total_qty']) * 100) : 0;

        $equipmentDataTable[] = [
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'image' => 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png',
            'available' => $available,
            'total' => $row['total_qty'],
            'maintenance' => $row['broken_qty'] + $row['repair_qty'],
            'usage' => $usage,
            'location' => $row['location'],
            'manufacturer' => 'Various',
            'model' => 'Standard',
            'purchaseDate' => '2024-01-01',
            'lastMaintenance' => date('Y-m-d', strtotime('-30 days')),
            'nextMaintenance' => date('Y-m-d', strtotime('+30 days')),
            'description' => $row['description'] ?? 'No description available',
            'broken_qty' => $row['broken_qty'],
            'repair_qty' => $row['repair_qty']
        ];
    }
}

// ---------- REQUESTS LIST ----------
$requests_q = "SELECT r.id, r.reservation_id, r.created_datetime, r.request_date, r.continue_days,
                      r.comment, l.location,
                      CONCAT(st.first_name,' ',st.last_name) as student_name,
                      st.university_id as studentId,
                      CASE 
                        WHEN rr.id IS NOT NULL THEN 'rejected'
                        WHEN r.technical_officer_id IS NOT NULL THEN 'approved'
                        ELSE 'pending'
                      END as status,
                      CONCAT(sup.first_name,' ',sup.last_name) as supervisor,
                      rr.reason as notes
               FROM reservation r
               JOIN location l ON r.location_id = l.id
               JOIN lab_user st ON r.student_id = st.id
               LEFT JOIN lab_user sup ON r.supervisor_id = sup.id
               LEFT JOIN reject_reason rr ON r.id = rr.reservation_id
               WHERE r.supervisor_id IS NOT NULL
               ORDER BY r.created_datetime DESC";
$requests_result = Database::search($requests_q);

$requests = [];
if ($requests_result && $requests_result->num_rows > 0) {
    while ($row = $requests_result->fetch_assoc()) {
        $dateTime = date('Y-m-d h:i A', strtotime($row['created_datetime']));
        $timestamp = strtotime($row['created_datetime']);

        $requests[] = [

            'id' => $row['reservation_id'],
            'dateTime' => $dateTime,
            'timestamp' => $timestamp,
            'studentName' => $row['student_name'],
            'studentId' => $row['studentId'],
            'lab' => $row['location'],
            'duration' => $row['continue_days'] . ' days',
            'purpose' => $row['comment'] ?? 'No purpose specified',
            'status' => $row['status'],
            'supervisor' => $row['supervisor'] ?? 'Not assigned',
            'notes' => $row['notes'] ?? ($row['status'] === 'pending' ? 'Awaiting technical officer approval' : '')
        ];
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Microbiology Lab System - TO Dashboard</title>
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
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

        #rejectReasonModal.show .modal-dialog,
        #confirmModal.show .modal-dialog {
            z-index: 9001 !important;
            position: relative !important;
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
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 12px 16px;
            margin-top: 0;
            text-align: center;
            background: rgba(0, 0, 0, 0.25);
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.8);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar {
            padding-bottom: 70px; /* important to avoid content overlap with footer */
        }

        /* Main content */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        /* Modern Topbar with Rounded Navbar */
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            margin: 15px 25px 0 25px;
            width: calc(100% - 50px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
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

        .content-area {
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            min-height: calc(100vh - 80px);
        }

        /* Modern Cards */
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

        /* Analytics Cards */
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

        .stat-card:hover::before {
            transform: rotate(45deg) translate(10%, 10%);
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

        /* Filter Section */
        .filter-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 12px 20px;
            border: 2px solid #f0f0f0;
            border-radius: 16px;
            outline: none;
            transition: all 0.3s;
            min-width: 200px;
        }

        .filter-select:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }

        /* Equipment Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table th {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            padding: 15px;
            font-weight: 600;
            text-align: left;
        }

        .details-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .details-table tr:hover td {
            background: rgba(34, 197, 94, 0.05);
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 4px;
            transition: width 0.3s;
        }

        /* Buttons */
        .btn-view {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 2px;
            white-space: nowrap;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.6);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-danger {
            border-radius: 12px !important;
            padding: 8px 12px !important;
            transition: all 0.3s !important;
        }

        .btn-danger:hover {
            transform: scale(1.1);
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
        }

        /* Badges */
        .badge {
            padding: 8px 14px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.3px;
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

        /* Table Styles */
        .table {
            border-radius: 18px;
            overflow: hidden;
        }

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
            transform: scale(1.01);
        }

        /* Form Elements */
        .form-control,
        .form-select {
            border: 2px solid #f0f0f0;
            border-radius: 16px;
            padding: 12px 16px;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }

        /* Calendar Section */
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
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.6);
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
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .day-cell.today {
            font-weight: 700;
            border: 2px solid #22c55e;
            color: #22c55e;
        }

        .day-cell.event {
            background-color: rgba(255, 215, 0, 0.15) !important;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        .day-cell.event::after {
            content: '';
            display: none;
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
            font-size: 0.95rem;
        }

        .goto-input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
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
            font-size: 0.95rem;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .goto-btn:hover,
        .today-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.6);
        }

        .right-header {
            margin-bottom: 25px;
        }

        .event-day {
            font-size: 2.2rem;
            font-weight: 700;
            text-transform: capitalize;
            margin-bottom: 5px;
        }

        .event-date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }

        .events-list {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .event-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 18px 20px;
            border-radius: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .event-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(8px);
        }

        .event-item .title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .event-item i {
            color: #ffd700;
            font-size: 0.8rem;
        }

        .event-item .event-title {
            font-size: 1.1rem;
            font-weight: 600;
            word-break: break-word;
        }

        .event-item .event-time {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-left: 28px;
        }

        .event-item .event-details {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }

        .event-item .event-details i {
            color: #ffd700;
            margin-right: 6px;
            font-size: 0.9rem;
        }

        .event-date-range {
            font-size: 0.8rem;
            color: #ffd700;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .no-event {
            text-align: center;
            padding: 50px 20px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 1.1rem;
        }

        /* Scrollbar */
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

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        /* Search and Add Row */
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

        .add-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* User Table */
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
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .user-table tbody tr:hover {
            background: #f9f9f9;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 4px;
            flex-wrap: nowrap;
            align-items: center;
        }

        .btn-edit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 2px;
            white-space: nowrap;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }

        .btn-remove {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 2px;
            white-space: nowrap;
        }

        .btn-remove:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Status Badge */
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-approved {
            background-color: #28a745;
            color: #fff;
        }

        .status-rejected {
            background-color: #dc3545;
            color: #fff;
        }

        .status-in_progress {
            background-color: #17a2b8;
            color: #fff;
        }

        /* Request Rate Badge */
        .request-rate {
            background: #e6f7e6;
            color: #22c55e;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .calendar-wrapper {
                flex-direction: row;
            }
        }

        @media (max-width: 992px) {
            .calendar-wrapper {
                flex-direction: column;
            }
        }

        @media (max-width: 991px) {
            .sidebar {
                left: -280px;
                border-radius: 0 20px 20px 0;
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

        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .event-day {
                font-size: 1.8rem;
            }

            .filter-section {
                flex-direction: column;
            }

            .filter-select {
                width: 100%;
            }

            .search-add-row {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                max-width: 100%;
            }

            .user-table {
                font-size: 14px;
            }

            .user-table td {
                padding: 10px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-edit,
            .btn-remove {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .content-area {
                padding: 15px;
            }

            .card {
                padding: 15px !important;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        /* Animation for removing items */
        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        /* Shake animation for validation */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .is-invalid {
            border-color: #dc2626 !important;
        }

        .invalid-feedback {
            display: block !important;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            color: #dc2626;
        }

        /* Notification Bell and Dropdown Styles - ADD THIS */
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

        /* Request Badge - only visible when count > 0 */
        .request-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dd1818;
            color: white;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0 5px;
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
            animation: pulse 2s infinite;
        }

        .request-badge.visible {
            display: flex;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 45px;
            right: 0;
            width: 380px;
            max-height: 500px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.1);
            display: none;
        }

        .notification-dropdown.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-header {
            padding: 12px 16px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dropdown-header h6 {
            margin: 0;
            font-weight: 600;
            color: white;
        }

        .btn-close-sm {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1.2rem;
        }

        .btn-close-sm:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .dropdown-body {
            max-height: 450px;
            overflow-y: auto;
        }

        /* Modal Styles */
        .modal {
            z-index: 1050 !important;
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: none;
        }

        /* Ensure dropdowns are above modals */
        .dropdown-menu {
            z-index: 1060 !important;
        }

        /* Fix for notification dropdown z-index */
        .notification-dropdown {
            z-index: 1070 !important;
        }

        /* Animation for modal */
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
            cursor: pointer;
        }

        .notification-item:hover {
            background: #f9fafb;
        }

        .notification-item.unread {
            background: #f0fdf4;
            border-left: 3px solid #22c55e;
        }

        .notification-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .notification-title strong {
            color: #166534;
        }

        .notification-meta {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .notification-meta i {
            margin-right: 4px;
        }

        .notification-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .btn-approve {
            background: #22c55e;
            color: white;
            border: none;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-approve:hover {
            background: #16a34a;
            transform: translateY(-1px);
        }

        .btn-reject {
            background: #ef4444;
            color: white;
            border: none;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-reject:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        /* Toast Notification Styles */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
            font-size: 14px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .toast-success {
            background: #22c55e;
            color: white;
            border-left: 4px solid #166534;
        }

        .toast-error {
            background: #ef4444;
            color: white;
            border-left: 4px solid #b91c1c;
        }

        .toast-warning {
            background: #f59e0b;
            color: white;
            border-left: 4px solid #d97706;
        }

        .toast-info {
            background: #3b82f6;
            color: white;
            border-left: 4px solid #1e40af;
        }
    </style>

    <!-- Keep original favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/resources/flask.svg">
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <h4><i class="bi bi-flask"></i> MicroLab</h4>
        <a onclick="showSection('dashboard')" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a onclick="showSection('equipment')"><i class="bi bi-tools"></i> Equipment Manage</a>
        <a onclick="showSection('activity')"><i class="bi bi-activity"></i> Requests
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

    <!-- MAIN -->
    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn d-lg-none text-dark" onclick="toggleSidebar()">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, #22c55e, #16a34a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Welcome, <?= htmlspecialchars($first_name ?? '') ?></h5>
            </div>
            <div class="d-flex align-items-center gap-3">

                <!-- Notification Bell with Dropdown -->
                <div class="notification-bell" style="position:relative;">
                    <i class="bi bi-journal-check fs-5" style="color: #166534; cursor: pointer;" onclick="toggleNotificationDropdown()"></i>
                    <span class="request-badge" id="requestBadge">0</span>

                    <!-- Dropdown Menu -->
                    <div id="notificationDropdown" class="notification-dropdown" style="display: none;">
                        <div class="dropdown-header">
                            <h6>Pending Logbook Reviews</h6>
                            <button class="btn-close-sm" onclick="toggleNotificationDropdown()">×</button>
                        </div>
                        <div class="dropdown-body" id="notificationList">
                            <div class="text-center p-3">
                                <div class="spinner-border text-success spinner-border-sm" role="status"></div>
                                <p class="mt-2 mb-0">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="fw-semibold d-none d-sm-block" style="color: #166534;"><?= htmlspecialchars($full_name) ?></span>
                <div class="dropdown">
                    <?php
                    $img_path = $user_data['img_path'] ?? '';

                    if (!empty($img_path)) {
                        $filename = basename($img_path);
                        $app_root = dirname(__DIR__);
                        $image_file_path = $app_root . '/assets/profile_images/' . $filename;

                        if (file_exists($image_file_path)) {
                            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                            $host = $_SERVER['HTTP_HOST'];
                            $profile_image = $protocol . $host . '/assets/profile_images/' . $filename;
                        } else {
                            $profile_image = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=100';
                        }
                    } else {
                        $profile_image = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=100';
                    }
                    ?>


                    <img src="<?php echo htmlspecialchars($profile_image); ?>" class="profile-img dropdown-toggle" data-bs-toggle="dropdown">
                    <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <li><a class="dropdown-item" href="#" onclick="openProfileModal(event)"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">

            <!-- Dashboard Section -->
            <div id="dashboardSection">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Dashboard Overview</h3>

                <div class="analytics-grid">
                    <!-- <div class="stat-card">
                        <i class="bi bi-mortarboard-fill"></i>
                        <h3><?= $students_count ?></h3>
                        <p>Students</p>
                    </div> -->
                    <div class="stat-card">
                        <i class="bi bi-flask"></i>
                        <h3><?= $equipment_count ?></h3>
                        <p>Total Equipment</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-graph-up"></i>
                        <h3><?= $utilization_rate ?>%</h3>
                        <p>Utilization Rate</p>
                    </div>
                    <div class="stat-card" onclick="viewPendingRequests()">
                        <i class="bi bi-tools"></i>
                        <h3 class="text-warning"><?= $pending_count ?></h3>
                        <!-- <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                            <i class="bi bi-eye"></i>
                        </button> -->
                        <p> Request Pending</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-flask"></i>
                        <h3><?= $today_count ?></h3>
                        <p> Today's Practicals</p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <!-- <div class="row mb-4 justify-content-center">
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted d-flex justify-content-center align-items-center gap-2">

                                <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </h6>
                            <h3 class="text-warning"><?= $pending_count ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted d-flex justify-content-center align-items-center gap-2">
                                Today's Practicals

                            </h6>
                            <h3 class="text-info"><?= $today_count ?></h3>
                        </div>
                    </div>
                </div> -->

                <!-- Calendar Section -->
                <h3 class="mb-4 mt-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment & Lab Schedule</h3>
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
                            <div class="right-header">
                                <div class="event-day" id="eventDay"></div>
                                <div class="event-date" id="eventDate"></div>
                            </div>
                            <div class="events-list" id="eventsList">
                                <div class="no-event">Select a date to view bookings</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Management Section -->
            <div id="equipmentSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment Management</h3>

                <div class="card p-4">
                    <!-- Search and Filter Row -->
                    <div class="search-add-row" style="display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <div class="search-container" style="display: flex; gap: 10px; flex: 2; min-width: 300px;">
                            <input type="text" id="equipmentSearch" class="search-input"
                                placeholder="Search by name or code..."
                                onkeyup="searchEquipment()"
                                style="flex: 1;">
                        </div>

                        <!-- FILTER DROPDOWN - SINGLE ONLY -->
                        <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 200px;">
                            <select id="statusFilterequipment" class="filter-select" onchange="searchEquipmentStatus()"
                                style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #e0e0e0; background: white;">
                                <option value="all">All Equipment</option>
                                <option value="maintenance">Maintenance Pending</option>
                                <option value="broken">Broken</option>
                            </select>
                        </div>

                        <button class="add-btn" onclick="addEquipment()" style="white-space: nowrap;">
                            <i class="bi bi-plus-circle"></i> Add Equipment
                        </button>
                    </div>

                    <!-- Equipment Table -->
                    <div class="table-responsive mt-3">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Equipment Name</th>
                                    <th>Total Qty</th>
                                    <th>Maintenance qty</th>
                                    <th>Broken qty</th>
                                    <th>Location</th>
                                    <th>Usage %</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="equipmentTableBody">
                                <?php
                                // Define the equipment data array
                                $equipmentDataTable = [];

                                // Fetch equipment data from database
                                $equipment_query = "SELECT 
            e.id,
            e.code,
            e.name,
            e.description,
            e.total_qty,
            e.image_path,
            (SELECT COALESCE(SUM(broken_qty), 0) FROM broken WHERE equipment_id = e.id) as broken_qty,
            (SELECT COALESCE(SUM(repair_qty), 0) FROM repair WHERE equipment_id = e.id) as repair_qty,
            GROUP_CONCAT(DISTINCT l.location SEPARATOR ', ') as locations
        FROM equipment e
        LEFT JOIN equipment_has_location ehl ON e.id = ehl.equipment_id
        LEFT JOIN location l ON ehl.location_id = l.id
        WHERE e.is_hod_checked = 1
        GROUP BY e.id
        ORDER BY e.name ASC";

                                $equipment_result = Database::search($equipment_query);

                                if ($equipment_result && $equipment_result->num_rows > 0) {
                                    while ($equipment_row = $equipment_result->fetch_assoc()) { // Changed variable name
                                        $equipment_code = htmlspecialchars($equipment_row['code']);
                                        $name = htmlspecialchars($equipment_row['name']);
                                        $total_qty = (int)$equipment_row['total_qty'];
                                        $broken_qty = (int)$equipment_row['broken_qty'];
                                        $repair_qty = (int)$equipment_row['repair_qty'];
                                        $equipment_location = !empty($equipment_row['locations']) && $equipment_row['locations'] !== null 
                                            ? htmlspecialchars($equipment_row['locations']) 
                                            : 'Not assigned';

                                        // Calculate available quantity
                                        $available_qty = $total_qty - ($broken_qty + $repair_qty);

                                        // Calculate usage percentage (based on bookings)
                                        $usage_query = "SELECT COUNT(*) as booking_count FROM book_equipment WHERE equipment_id = ?";
                                        $usage_result = Database::search($usage_query, "i", [$equipment_row['id']]);
                                        $usage_count = 0;
                                        if ($usage_result && $usage_result->num_rows > 0) {
                                            $usage_row = $usage_result->fetch_assoc();
                                            $usage_count = $usage_row['booking_count'];
                                        }

                                        // FIXED: Calculate percentage based on total quantity or usage pattern
                                        // Option 1: Percentage of times this equipment is used vs others
                                        $total_bookings_query = "SELECT COUNT(*) as total FROM reservation";
                                        $total_bookings_result = Database::search($total_bookings_query);
                                        $total_bookings = 0;

                                        if ($total_bookings_result && $total_bookings_result->num_rows > 0) {
                                            $total_bookings_row = $total_bookings_result->fetch_assoc(); // Different variable name
                                            $total_bookings = $total_bookings_row['total'];
                                        }

                                        // Calculate usage percentage based on ALL bookings
                                        $usage_percentage = $total_bookings > 0 ? round(($usage_count / $total_bookings) * 100) : 0;
                                        $usage_percentage = min(100, $usage_percentage); // Cap at 100%

                                        // Set image path
                                        $image_path = !empty($equipment_row['img_path'])
                                            ? '/' . ltrim(str_replace('\\', '/', $equipment_row['img_path']), '/')
                                            : 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';

                                        // Add to data array for JavaScript
                                        $equipmentDataTable[] = [
                                            'code' => $equipment_code,
                                            'name' => $name,
                                            'image' => $image_path,
                                            'qty' => $total_qty,
                                            'broken' => $broken_qty,
                                            'maintenance' => $repair_qty,
                                            'location' => $equipment_location,
                                            'usage' => $usage_percentage,
                                            'id' => $equipment_row['id']
                                        ];

                                        // Determine badge color for available/total ratio
                                        $ratio = $total_qty > 0 ? $available_qty / $total_qty : 0;
                                        $badgeColor = '#22c55e'; // green
                                        if ($ratio < 0.3) $badgeColor = '#ef4444'; // red
                                        else if ($ratio < 0.6) $badgeColor = '#f59e0b'; // orange

                                        // Bar color based on usage
                                        $barColor = '#22c55e';
                                        if ($usage_percentage < 30) $barColor = '#ef4444';
                                        else if ($usage_percentage < 60) $barColor = '#f59e0b';
                                ?>
                                        <tr data-equipment-id="<?php echo $equipment_code; ?>"
                                            data-equipment-id-numeric="<?php echo $equipment_row['id']; ?>"
                                            data-maintenance="<?php echo $repair_qty; ?>"
                                            data-broken="<?php echo $broken_qty; ?>">
                                            <td>
                                                <img src="<?php echo $image_path; ?>"
                                                    style="width:50px;height:50px;object-fit:contain;"
                                                    onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'"
                                                    alt="<?php echo $name; ?>">
                                            </td>
                                            <td><strong><?php echo $name; ?></strong></td>
                                            <td>
                                                <?php if ($repair_qty > 0): ?>
                                                    <span class="badge bg-warning"><?php echo $repair_qty; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">------</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($broken_qty > 0): ?>
                                                    <span class="badge bg-danger"><?php echo $broken_qty; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">------</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div style="width:90px;height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;">
                                                        <div style="width:<?php echo $usage_percentage; ?>%;height:8px;background:<?php echo $barColor; ?>;border-radius:4px;transition:width 0.6s ease;"></div>
                                                    </div>
                                                    <span style="font-weight:600;color:<?php echo $barColor; ?>;min-width:45px;">
                                                        <?php echo $usage_percentage; ?>%
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn-view" onclick="viewEquipmentByCode('<?php echo $equipment_code; ?>')" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn-edit" onclick="editEquipment('<?php echo $equipment_code; ?>')" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn-remove" onclick="removeEquipment('<?php echo $equipment_code; ?>')" title="Remove">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No equipment found in database</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Equipment Details Modal -->
            <!-- <div class="modal fade" id="equipmentDetailsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-info-circle me-2"></i>
                                Equipment Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="equipmentDetailsContent">
                          
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Requests Section - Simplified for Technical Officer -->
            <div id="activitySection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Requests</h3>

                <div class="card p-4">
                    <!-- Filter Section with Time Range and Status -->
                    <div class="filter-section" style="margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap;">
                        <!-- Time Range Filter -->
                        <select class="filter-select" id="timeRangeFilter" onchange="filterRequestsByTime()" style="min-width: 200px;">
                            <option value="all">All Time</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>

                        <!-- Status Filter -->
                        <select class="filter-select" id="statusFilter" onchange="filterRequestsByStatus()" style="min-width: 200px;">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Requests Table - Simplified Columns -->
                    <div class="table-responsive mt-3">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Date & Time</th>
                                    <th>Student ID</th>
                                    <th>Lab</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="requestListBody">
                                <?php foreach ($requests as $req):
                                    $statusClass = '';
                                    $statusText = '';

                                    switch ($req['status']) {
                                        case 'pending':
                                            $statusClass = 'bg-warning';
                                            $statusText = 'Pending';
                                            break;
                                        case 'approved':
                                            $statusClass = 'bg-success';
                                            $statusText = 'Approved';
                                            break;
                                        case 'rejected':
                                            $statusClass = 'bg-danger';
                                            $statusText = 'Rejected';
                                            break;
                                    }

                                    // Create a JSON string of the request data for JavaScript
                                    $requestJson = json_encode($req, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($req['id']) ?></td>
                                        <td><?= htmlspecialchars($req['dateTime']) ?></td>
                                        <td><?= htmlspecialchars($req['studentId']) ?></td>
                                        <td><?= htmlspecialchars($req['lab']) ?></td>
                                        <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-view" onclick="viewRequest('<?= htmlspecialchars($req['id'], ENT_QUOTES) ?>')" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <?php if ($req['status'] === 'pending'): ?>
                                                    <button class="btn-remove" onclick="rejectRequest('<?= htmlspecialchars($req['id'], ENT_QUOTES) ?>')" title="Reject">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> <!-- End content-area -->

        <!-- Profile Modal -->
        <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                    <div class="modal-header" style="border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, #22c55e, #16a34a);">
                        <h5 class="modal-title text-white fw-bold">My Profile</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Profile Image Upload -->
                        <div class="text-center mb-4">
                            <div style="width: 120px; height: 120px; margin: 0 auto; border-radius: 50%; overflow: hidden; border: 3px solid #22c55e; background: #f3f4f6;">
                                <img id="profileImagePreview" src="" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-sm btn-outline-success mt-3" onclick="document.getElementById('profileImageInput').click()">
                                <i class="bi bi-cloud-upload me-2"></i>Upload Photo
                            </button>
                        </div>

                        <!-- Form -->
                        <form id="profileForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">First Name</label>
                                    <input type="text" class="form-control" id="firstName" placeholder="First name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" placeholder="Last name">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="email" placeholder="Email address">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mobile</label>
                                    <input type="text" class="form-control" id="mobile" placeholder="Mobile number">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">University ID</label>
                                    <input type="text" class="form-control" id="universityId" placeholder="University ID" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Join Date</label>
                                <input type="text" class="form-control" id="joinDate" readonly style="background-color: #f3f4f6;">
                            </div>

                            <div class="modal-footer border-top mt-4">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-success" onclick="saveProfile()">
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- End main-content -->

    <!-- Request Details Modal -->
    <div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Reservation Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="requestDetailsContent" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="submitCheckedEquipment()" id="submitModalBtn">
                        <i class="bi bi-check-circle me-2"></i>Submit
                    </button>
                    <button type="button" class="btn btn-danger" onclick="showRejectRequestModal(currentRequestId)" id="rejectModalBtn" style="display:none;">
                        <i class="bi bi-x-circle me-2"></i>Reject
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let allEquipmentData = [];


        // ========== SECTION PERSISTENCE ==========
        // Store current section before reload
        function saveCurrentSection() {
            if (document.getElementById('activitySection').style.display === 'block') {
                sessionStorage.setItem('lastSection', 'activity');
            } else if (document.getElementById('equipmentSection').style.display === 'block') {
                sessionStorage.setItem('lastSection', 'equipment');
            } else {
                sessionStorage.setItem('lastSection', 'dashboard');
            }
        }

        // Restore last section on page load
        function restoreLastSection() {
            const lastSection = sessionStorage.getItem('lastSection');
            if (lastSection) {
                showSection(lastSection);
            } else {
                showSection('dashboard');
            }
        }

        function rejectRequestFromModal() {
            if (!currentRequestId) return;

            // Save current section before reload
            saveCurrentSection();

            // Close the reservation details modal
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('requestDetailsModal'));
            if (detailsModal) detailsModal.hide();

            // Show the rejection reason modal
            showRejectRequestModal(currentRequestId);
        }

        // Submit checked equipment
        function submitCheckedEquipment() {
            if (!currentRequestId) {
                showToast('warning', 'No reservation selected');
                return;
            }

            // Save current section before reload
            saveCurrentSection();

            // Get ALL checkboxes
            const allCheckboxes = document.querySelectorAll('#requestDetailsContent input[type="checkbox"]');
            const checkedBoxes = document.querySelectorAll('#requestDetailsContent input[type="checkbox"]:checked');

            // Check if ALL checkboxes are checked
            if (allCheckboxes.length === 0) {
                showToast('warning', 'No equipment found to check');
                return;
            }

            if (checkedBoxes.length !== allCheckboxes.length) {
                showToast('warning',
                    `⚠️ Please Ready ALL equipment items (${checkedBoxes.length}/${allCheckboxes.length} checked). ` +
                    `Note: Reject with reason if any equipment not ready or count insufficient - student can resubmit.`
                );
                return;
            }

            // Prepare data
            const checkedEquipment = [];
            checkedBoxes.forEach(checkbox => {
                checkedEquipment.push({
                    book_equipment_id: checkbox.value,
                    equipment_id: checkbox.dataset.equipmentId,
                    quantity: checkbox.dataset.quantity
                });
            });

            // Show loading state
            const submitBtn = document.getElementById('submitModalBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

            // Send to server
            fetch('../controllers/submit_checked_equipment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        reservation_id: currentRequestId,
                        checked_equipment: checkedEquipment
                    })
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    if (data.success) {
                        showToast('success', 'Equipment checked successfully!');

                        // Hide modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('requestDetailsModal'));
                        if (modal) modal.hide();

                        // FIND AND REMOVE THE ROW FROM THE TABLE
                        const tableBody = document.getElementById('requestListBody');
                        if (tableBody) {
                            // Find all rows
                            const rows = tableBody.getElementsByTagName('tr');

                            // Loop through rows to find the one with matching ID
                            for (let i = 0; i < rows.length; i++) {
                                const firstCell = rows[i].getElementsByTagName('td')[0];
                                if (firstCell && firstCell.textContent === currentRequestId) {
                                    // Add fade-out animation
                                    rows[i].style.transition = 'opacity 0.3s';
                                    rows[i].style.opacity = '0';

                                    // Remove after animation
                                    setTimeout(() => {
                                        rows[i].remove();

                                        // Update pending count in sidebar
                                        updatePendingCount();

                                        // Check if table is empty
                                        if (tableBody.getElementsByTagName('tr').length === 0) {
                                            const emptyRow = document.createElement('tr');
                                            emptyRow.innerHTML = '<td colspan="6" class="text-center">No requests found</td>';
                                            tableBody.appendChild(emptyRow);
                                        }
                                    }, 300);
                                    break;
                                }
                            }
                        }

                        // Update the requests array (remove the processed request)
                        const index = requests.findIndex(req => req.id === currentRequestId);
                        if (index !== -1) {
                            requests.splice(index, 1);
                        }

                        // Refresh the display
                        filterRequestsByStatus();

                    } else {
                        showToast('error', 'Error: ' + (data.message || 'Failed to submit'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    showToast('error', 'Network error. Please try again.');
                });
        }

        // Update pending count in sidebar
        function updatePendingCount() {
            const pendingCount = requests.filter(req => req.status === 'pending').length;
            const badge = document.querySelector('.sidebar a[onclick="showSection(\'activity\')"] .badge');
            if (badge) {
                if (pendingCount > 0) {
                    badge.textContent = pendingCount;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }

        function viewRequest(reservationId) {
            //alert(reservationId);
            // Show loading in modal
            const modalElement = document.getElementById('requestDetailsModal');
            const detailsContent = document.getElementById('requestDetailsContent');

            detailsContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading reservation details...</p>
        </div>
    `;

            // Show modal
            const existing = bootstrap.Modal.getInstance(modalElement);
            if (existing) existing.dispose();
            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Fetch reservation details
            fetch(`../controllers/get_reservation_details_for_to.php?id=${reservationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayReservationDetails(data.reservation, data.equipment);
                        currentRequestId = reservationId;
                    } else {
                        detailsContent.innerHTML = `
                    <div class="alert alert-danger m-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Failed to load reservation details'}
                    </div>
                `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    detailsContent.innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Network error. Please try again.
                </div>
            `;
                });
        }


        // Reject request with reason modal
        function rejectRequest(id) {
            saveCurrentSection();
            currentRequestId = id;
            showRejectRequestModal(id);
        }

        // Show rejection reason modal for request
        function showRejectRequestModal(id) {
            try {
                const modalElement = document.getElementById('rejectRequestReasonModal');
                const textarea = document.getElementById('requestRejectionReason');

                if (!modalElement) {
                    console.error('Modal element "rejectRequestReasonModal" not found in DOM');
                    return;
                }

                if (!textarea) {
                    console.error('Textarea "requestRejectionReason" not found in DOM');
                    return;
                }

                // Clear textarea
                textarea.value = '';
                textarea.classList.remove('is-invalid');

                // Remove any existing error message
                const errorDiv = document.getElementById('requestReasonError');
                if (errorDiv) {
                    errorDiv.remove();
                }

                // Show modal
                const modal = new bootstrap.Modal(modalElement);
                modal.show();

            } catch (error) {
                console.error('Error showing request rejection modal:', error);
            }
        }

        // Confirm rejection of request
        function confirmRejectRequest() {
            const textarea = document.getElementById('requestRejectionReason');
            if (!textarea) return;

            const reason = textarea.value.trim();
            if (!reason) {
                textarea.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.id = 'requestReasonError';
                errorDiv.className = 'invalid-feedback d-block';
                errorDiv.textContent = 'Please provide a reason for rejection';
                if (!document.getElementById('requestReasonError')) {
                    textarea.parentNode.appendChild(errorDiv);
                }
                return;
            }

            closeModalManually('rejectRequestReasonModal');
            executeRejectRequest(currentRequestId, reason);
        }

        // Execute the request rejection
        function executeRejectRequest(id, reason) {
            const formData = new FormData();
            formData.append('reservation_id', id);
            formData.append('action', 'reject');
            formData.append('reason', reason);

            fetch('../controllers/handle_to_approval.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', 'Rejected!');

                        // FIND AND REMOVE THE ROW FROM THE TABLE
                        const tableBody = document.getElementById('requestListBody');
                        if (tableBody) {
                            const rows = tableBody.getElementsByTagName('tr');

                            for (let i = 0; i < rows.length; i++) {
                                const firstCell = rows[i].getElementsByTagName('td')[0];
                                if (firstCell && firstCell.textContent === id) {
                                    rows[i].style.transition = 'opacity 0.3s';
                                    rows[i].style.opacity = '0';

                                    setTimeout(() => {
                                        rows[i].remove();
                                        updatePendingCount();

                                        if (tableBody.getElementsByTagName('tr').length === 0) {
                                            const emptyRow = document.createElement('tr');
                                            emptyRow.innerHTML = '<td colspan="6" class="text-center">No requests found</td>';
                                            tableBody.appendChild(emptyRow);
                                        }
                                    }, 300);
                                    break;
                                }
                            }
                        }

                        // Update requests array
                        const index = requests.findIndex(req => req.id === id);
                        if (index !== -1) {
                            requests.splice(index, 1);
                        }

                        filterRequestsByStatus();
                    } else showToast('error', 'Error: ' + data.message);
                });
        }

        // Display reservation details in modal
        function displayReservationDetails(reservation, equipment) {
            const detailsContent = document.getElementById('requestDetailsContent');

            // Build equipment table HTML
            let equipmentHtml = '';
            if (equipment.length > 0) {
                equipment.forEach(item => {
                    equipmentHtml += `
                <tr>
                    <td class="text-center">${item.no}</td>
                    <td>${item.name}</td>
                    <td class="text-center">${item.qty}</td>
                    <td class="text-center">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" 
                                   id="eq_${item.id}" value="${item.id}" 
                                   data-equipment-id="${item.equipment_id}"
                                   data-quantity="${item.qty}">
                            <label class="form-check-label" for="eq_${item.id}">Ready</label>
                        </div>
                    </td>
                </tr>
            `;
                });
            } else {
                equipmentHtml = `
            <tr>
                <td colspan="4" class="text-center text-muted">No equipment found</td>
            </tr>
        `;
            }

            detailsContent.innerHTML = `
        <div class="container-fluid">
            <!-- Reservation Info Card -->
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Reservation Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th style="width: 120px;">Reservation ID:</th>
                                    <td><strong>${reservation.reservation_id}</strong></td>
                                </tr>
                                <tr>
                                    <th>University ID:</th>
                                    <td>${reservation.university_id}</td>
                                </tr>
                                <tr>
                                    <th>Student Name:</th>
                                    <td>${reservation.student_name}</td>
                                </tr>
                                <tr>
                                    <th>Supervisor:</th>
                                    <td>${reservation.supervisor_name || 'Not assigned'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th style="width: 120px;">Request Date(s):</th>
                                    <td>${reservation.date_range}</td>
                                </tr>
                                <tr>
                                    <th>Lab Location:</th>
                                    <td>${reservation.lab_location}</td>
                                </tr>
                                <tr>
                                    <th>Comment:</th>
                                    <td>${reservation.comment || 'No comments'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Equipment List Card -->
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Equipment List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                              
                            </thead>
                            <tbody>
                                ${equipmentHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Show/hide reject button
            const rejectBtn = document.getElementById('rejectModalBtn');
            if (rejectBtn) {
                // You can check status here if needed
                rejectBtn.style.display = 'inline-block';
            }
        }





        // ========== DATA FROM PHP ==========
        const equipmentDataTable = <?php echo json_encode($equipmentDataTable, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const requests = <?php echo json_encode($requests, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

        // ========== CALENDAR VARIABLES ==========
        let activeDay;
        let month = new Date().getMonth();
        let year = new Date().getFullYear();
        const months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        let eventsArr = <?= $calendar_events_json ?: '[]' ?>;
        let currentRequestId = null;

        // ========== DASHBOARD FUNCTIONS ==========
        function viewPendingRequests() {
            showSection('activity');
            document.getElementById('statusFilter').value = 'pending';
            filterRequestsByStatus();
        }

        function viewTodayPracticals() {
            showSection('activity');
            document.getElementById('timeRangeFilter').value = 'daily';
            filterRequestsByStatus();
        }

        // ========== SIDEBAR FUNCTIONS ==========
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
            document.getElementById("sidebarOverlay").classList.toggle("active");
        }

        // ========== SECTION NAVIGATION ==========
        function showSection(section) {
            // Hide all sections
            document.getElementById('dashboardSection').style.display = 'none';
            document.getElementById('equipmentSection').style.display = 'none';
            document.getElementById('activitySection').style.display = 'none';

            // Show selected section
            const sectionElement = document.getElementById(section + 'Section');
            if (sectionElement) {
                sectionElement.style.display = 'block';
            }

            // Update active state in sidebar
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(section)) {
                    link.classList.add('active');
                }
            });
        }

        // ========== EQUIPMENT MANAGEMENT FUNCTIONS ==========
        function searchEquipment() {
            filterAndDisplayEquipment();
        }

        // function displayEquipmentTable(equipment) {
        //     const tableBody = document.getElementById('equipmentTableBody');
        //     if (!tableBody) return;

        //     tableBody.innerHTML = '';

        //     equipment.forEach(item => {
        //         const row = document.createElement('tr');

        //         const ratio = item.available / item.total;
        //         let badgeColor = '#22c55e';
        //         if (ratio < 0.3) badgeColor = '#ef4444';
        //         else if (ratio < 0.6) badgeColor = '#f59e0b';

        //         row.innerHTML = `
        //     <td><img src="${item.image}" style="width: 50px; height: 50px; object-fit: contain;"></td>
        //     <td>${item.code}</td>
        //     <td>${item.name}</td>
        //     <td><span class="badge" style="background: ${badgeColor}; color: white;">${item.available}/${item.total}</span></td>
        //     <td><span class="badge bg-warning">${item.maintenance}</span></td>
        //     <td>
        //         <div class="progress-bar" style="width: 100px; display: inline-block; margin-right: 10px;">
        //             <div class="progress-fill" style="width: ${item.usage}%"></div>
        //         </div>
        //         ${item.usage}%
        //     </td>
        //     <td>
        //         <div class="action-buttons">
        //             <button class="btn-view" onclick='viewEquipment(${JSON.stringify(item)})' title="View Details">
        //                 <i class="bi bi-eye"></i>
        //             </button>
        //             <button class="btn-edit" onclick="editEquipment('${item.code}')" title="Edit">
        //                 <i class="bi bi-pencil-square"></i>
        //             </button>
        //             <button class="btn-remove" onclick="removeEquipment('${item.code}')" title="Remove">
        //                 <i class="bi bi-trash"></i>
        //             </button>
        //         </div>
        //     </td>
        // `;
        //         tableBody.appendChild(row);
        //     });
        // }

        function editEquipment(code) {
            isEditMode = true;

            document.getElementById('equipmentModalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Equipment';
            document.getElementById('modalSaveButtonText').textContent = 'Update Equipment';
            document.getElementById('maintenanceSection').style.display = 'block';
            clearEquipmentErrors();

            const modal = new bootstrap.Modal(document.getElementById('addEquipmentModal'));
            modal.show();

            document.getElementById('eqCode').value = 'Loading...';
            document.getElementById('eqName').value = 'Loading...';
            document.getElementById('eqCode').disabled = true;
            document.getElementById('eqName').disabled = true;

            fetch(`../controllers/tech_get_equi.php?code=${encodeURIComponent(code)}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('eqCode').disabled = false;
                    document.getElementById('eqName').disabled = false;

                    if (data.success) {
                        currentEquipmentId = data.equipment.id;
                        document.getElementById('eqId').value = data.equipment.id;

                        document.getElementById('eqCode').value = data.equipment.code || '';
                        document.getElementById('eqName').value = data.equipment.name || '';
                        document.getElementById('eqQty').value = data.equipment.total_qty || 1;
                        document.getElementById('eqSimultaneousUsers').value = data.equipment.simultaneous_users || 1;
                        document.getElementById('eqSterilization').value = data.equipment.sterilization_required || 'NO';
                        document.getElementById('eqReservation').value = data.equipment.reservation_required || 'YES';
                        document.getElementById('eqLabLocation').value = data.equipment.location_id || '';
                        document.getElementById('eqDescription').value = data.equipment.description || '';
                        document.getElementById('eqMaintenanceQty').value = data.equipment.repair_qty || 0;
                        document.getElementById('eqBrokenQty').value = data.equipment.broken_qty || 0;
                        recalcAvailable();

                        if (data.equipment.image_path) {
                            const imagePath = data.equipment.image_path;
                            document.getElementById('eqImagePreview').src = imagePath;
                            document.getElementById('eqImagePreview').style.display = 'block';
                            document.getElementById('eqImagePlaceholder').style.display = 'none';
                            document.getElementById('currentImageInfo').innerHTML = 'Current image: <img src="' + imagePath + '" style="height: 30px; width: 30px; object-fit: cover; border-radius: 4px;">';
                            document.getElementById('currentImageInfo').style.display = 'block';
                        }
                    } else {
                        showToast('error', 'Error loading equipment details: ' + (data.message || 'Unknown error'));
                        modal.hide();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('eqCode').disabled = false;
                    document.getElementById('eqName').disabled = false;
                    showToast('error', 'Network error. Please try again.');
                    modal.hide();
                });
        }

        function viewEquipmentByCode(code) {
            const contentDiv = document.getElementById('equipmentDetailsContent');
            const modalEl = document.getElementById('equipmentDetailsModal');

            // Show loading
            contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status" style="width:2rem;height:2rem;"></div>
            <p class="mt-3 text-muted small fw-semibold">Loading equipment details...</p>
        </div>`;

            // Clean up any existing modal instance and backdrop
            const existing = bootstrap.Modal.getInstance(modalEl);
            if (existing) existing.dispose();
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            // Show modal FIRST, then fetch
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            fetch(`../controllers/get_equipment_details.php?code=${encodeURIComponent(code)}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        contentDiv.innerHTML = `<div class="alert alert-danger m-3">${data.message || 'Failed to load'}</div>`;
                        return;
                    }

                    const eq = data.equipment;
                    const addedDate = eq.added_datetime ? new Date(eq.added_datetime).toLocaleDateString() : '—';

                    contentDiv.innerHTML = `
                <div class="row g-0">
                    <div class="col-md-4 text-center border-end p-4">
                        <img src="${eq.image_path || 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png'}"
                             style="width:140px;height:140px;object-fit:contain;"
                             class="img-fluid rounded border p-2 bg-light mb-3"
                             onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'">
                        <h5 class="fw-bold mb-1" style="color:#166534;">${eq.name || 'Unknown'}</h5>
                        <span class="badge bg-secondary"><i class="bi bi-upc-scan me-1"></i>${eq.code || 'N/A'}</span>
                        <div class="mt-3">
                            <span class="badge bg-info text-dark">
                                <i class="bi bi-box-seam me-1"></i>Total Qty: ${eq.total_qty || 0}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-8 p-3">
                        <table class="table table-sm table-borderless">
                            <tr><th class="text-muted fw-normal" style="width:160px">Date Added</th><td>${addedDate}</td></tr>
                            <tr><th class="text-muted fw-normal">Simultaneous Users</th><td>${eq.simultaneous_users || 1}</td></tr>
                            <tr>
                                <th class="text-muted fw-normal">Sterilization Required</th>
                                <td><span class="badge ${eq.sterilization_required === 'YES' ? 'bg-warning' : 'bg-secondary'}">${eq.sterilization_required || 'NO'}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Reservation Required</th>
                                <td><span class="badge ${eq.reservation_required === 'YES' ? 'bg-success' : 'bg-secondary'}">${eq.reservation_required || 'YES'}</span></td>
                            </tr>
                            ${eq.description ? `<tr><th class="text-muted fw-normal">Description</th><td><small>${eq.description}</small></td></tr>` : ''}
                        </table>
                    </div>
                </div>`;
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = `<div class="alert alert-danger m-3">Network error. Please try again.</div>`;
                });
        }

        function recalcAvailable() {
            const total = parseInt(document.getElementById('eqQty').value) || 0;
            const maintenance = parseInt(document.getElementById('eqMaintenanceQty').value) || 0;
            const broken = parseInt(document.getElementById('eqBrokenQty').value) || 0;
            const available = Math.max(0, total - maintenance - broken);

            document.getElementById('availableQtyDisplay').textContent = available;

            const warning = document.getElementById('qtyValidationWarning');
            if (maintenance + broken > total) {
                warning.classList.remove('d-none');
                document.getElementById('qtyWarningMessage').textContent =
                    `Maintenance (${maintenance}) + Broken (${broken}) = ${maintenance + broken} exceeds Total (${total})`;
            } else {
                warning.classList.add('d-none');
            }
        }

        function loadEquipmentWithUsage() {
            const tableBody = document.getElementById('equipmentTableBody');
            if (!tableBody) return;

            tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-success me-2" role="status"
                     style="width:1.5rem;height:1.5rem;"></div>
                <span style="color:#166534;font-weight:600;">
                    Loading equipment data...
                </span>
            </td>
        </tr>`;

            fetch('../controllers/get_equipment_usage.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Equipment data received:', data);

                    if (data.success) {
                        allEquipmentData = data.equipment;
                        filterAndDisplayEquipment();

                        const filterSelect = document.getElementById('statusFilterequipment');
                        if (filterSelect) filterSelect.value = 'all';
                    } else {
                        tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            ❌ ${data.message || 'Failed to load equipment data'}
                        </td>
                    </tr>`;
                    }
                })
                .catch(error => {
                    console.error('Equipment load error:', error);
                    tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        ❌ Connection error
                    </td>
                </tr>`;
                });
        }


        function displayEquipmentTable(equipment) {
            const tableBody = document.getElementById('equipmentTableBody');
            if (!tableBody) return;

            tableBody.innerHTML = '';

            if (!equipment || equipment.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No equipment found</td></tr>';
                return;
            }

            equipment.sort((a, b) => b.usage - a.usage);

            equipment.forEach(item => {
                const code = item.code || 'N/A';
                const name = item.name || 'Unknown';
                const image = item.image || 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';
                const qty = item.qty || 0;
                const maintenance = item.maintenance || 0;
                const broken = item.broken || 0;
                const location = (item.location && item.location.trim() !== '') ? item.location.trim() : null;
                const usage = Math.round(parseFloat(item.usage) || 0);

                let barColor = '#22c55e';
                if (usage < 30) barColor = '#ef4444';
                else if (usage < 60) barColor = '#f59e0b';

                const row = document.createElement('tr');
                row.setAttribute('data-equipment-id', code);
                row.setAttribute('data-equipment-id-numeric', item.id || '');

                row.innerHTML = `
            <td>
                <img src="${image}"
                     style="width:50px;height:50px;object-fit:contain;"
                     onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'"
                     alt="${name}">
            </td>
            <td>${name}</td>
            <td style="width: 80px; padding: 8px 4px; text-align: center;">
                <span style="font-weight: 600;">${qty}</span>
            </td>
            <td>
                ${maintenance > 0 
                    ? `<span class="badge bg-warning">${maintenance}</span>` 
                    : '<span class="text-muted">------</span>'}
            </td>
            <td>
                ${broken > 0 
                    ? `<span class="badge bg-danger">${broken}</span>` 
                    : '<span class="text-muted">------</span>'}
            </td>
            <td style="width: 150px; padding: 8px 4px;">
                ${!location
                    ? `<span class="badge bg-secondary" style="background-color: #9ca3af !important;">
                        <i class="bi bi-geo-alt"></i> Not Assigned
                       </span>`
                    : `<span class="badge bg-info" style="background-color: #0ea5e9 !important;">
                        <i class="bi bi-geo-alt"></i> ${location}
                       </span>`}
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:100px;height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;">
                        <div style="width:${usage}%;height:8px;background:${barColor};border-radius:4px;"></div>
                    </div>
                    <span style="font-weight:600;color:${barColor};min-width:45px;">${usage}%</span>
                </div>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view" onclick="viewEquipmentByCode('${code}')" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-edit" onclick="editEquipment('${code}')" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn-remove" onclick="removeEquipment('${code}')" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
                tableBody.appendChild(row);
            });
        }

        function updateEquipmentCount(searchTerm) {
            // This function is now handled by searchEquipmentStatus
            // Keep it for backward compatibility
            const equipmentTable = document.getElementById('equipmentTableBody');
            if (!equipmentTable) return;

            const visibleEquipment = Array.from(equipmentTable.getElementsByTagName('tr'))
                .filter(row => row.style.display !== 'none').length;
            const totalEquipment = allEquipmentData.length;

            document.getElementById('equipmentCount').textContent =
                (visibleEquipment > 0 || searchTerm === '') ?
                '(' + totalEquipment + ')' : '(0)';
        }

        function removeEquipment(code) {
            // if (!confirm(`Are you sure you want to remove equipment "${code}"?\n\nThis action cannot be undone.`)) {
            //     return;
            // }

            // Find button by traversing the DOM — no event needed
            const allBtns = document.querySelectorAll('.btn-remove');
            let btn = null;
            allBtns.forEach(b => {
                if (b.getAttribute('onclick') && b.getAttribute('onclick').includes(code)) {
                    btn = b;
                }
            });

            const originalHTML = btn ? btn.innerHTML : '<i class="bi bi-trash"></i>';

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }

            fetch('../controllers/delete_equipment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        code: code
                    })
                })
                .then(r => r.json())
                .then(data => {
                    console.log('Delete response:', data);
                    if (data.success) {
                        showSuccess(`Equipment "${code}" removed successfully!`);
                        loadEquipmentWithUsage();
                    } else {
                        showError(data.message || 'Failed to remove equipment.');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = originalHTML;
                        }
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    showError('Network error. Please try again.');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    }
                });
        }

        function filterAndDisplayEquipment() {
            const filterValue = document.getElementById('statusFilterequipment').value;
            const searchTerm = document.getElementById('equipmentSearch').value.toLowerCase().trim();

            if (!allEquipmentData || allEquipmentData.length === 0) {
                return;
            }

            let filteredData = [...allEquipmentData];

            // Apply status filter
            if (filterValue === 'maintenance') {
                filteredData = filteredData.filter(item => item.maintenance > 0);
            } else if (filterValue === 'broken') {
                filteredData = filteredData.filter(item => item.broken > 0);
            }

            // Apply search filter
            if (searchTerm !== '') {
                filteredData = filteredData.filter(item => {
                    const nameMatch = item.name && item.name.toLowerCase().includes(searchTerm);
                    const codeMatch = item.code && item.code.toLowerCase().includes(searchTerm);
                    return nameMatch || codeMatch;
                });
            }

            displayEquipmentTable(filteredData);
        }

        function showError(message) {
            // Check if message div exists
            let msgDiv = document.getElementById('messageDiv');

            if (!msgDiv) {
                // Create message div if it doesn't exist
                msgDiv = document.createElement('div');
                msgDiv.id = 'messageDiv';
                msgDiv.className = 'position-fixed top-0 end-0 m-3 p-3 rounded shadow';
                msgDiv.style.zIndex = '9999';
                document.body.appendChild(msgDiv);
            }

            msgDiv.className = 'position-fixed top-0 end-0 m-3 p-3 rounded shadow bg-danger text-white';
            msgDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${message}`;
            msgDiv.style.display = 'block';
        }

        function viewEquipment(equipment) {
            const detailsContent = document.getElementById('equipmentDetailsContent');

            const today = new Date();
            const nextDate = new Date(equipment.nextMaintenance);
            const isOverdue = nextDate < today;

            detailsContent.innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center">
                <img src="${equipment.image}" style="width: 150px; height: 150px; object-fit: contain;" class="mb-3">
                <h4>${equipment.name}</h4>
                <p class="text-muted">${equipment.code}</p>
                <span class="badge ${equipment.available > 0 ? 'bg-success' : 'bg-danger'}" style="font-size: 1rem;">
                    ${equipment.available > 0 ? 'Available' : 'Unavailable'}
                </span>
            </div>
            <div class="col-md-8">
                <table class="table table-borderless">
                    <tr><th style="width: 150px;">Location:</th><td>${equipment.location}</td></tr>
                    <tr><th>Manufacturer:</th><td>${equipment.manufacturer}</td></tr>
                    <tr><th>Model:</th><td>${equipment.model}</td></tr>
                    <tr><th>Purchase Date:</th><td>${equipment.purchaseDate}</td></tr>
                    <tr><th>Last Maintenance:</th><td>${equipment.lastMaintenance}</td></tr>
                    <tr>
                        <th>Next Maintenance:</th>
                        <td>
                            ${equipment.nextMaintenance}
                            ${isOverdue ? '<span class="badge bg-danger ms-2">⚠️ Overdue</span>' : ''}
                        </td>
                    </tr>
                    <tr><th>Availability:</th><td><span class="badge" style="background: #22c55e;">${equipment.available}/${equipment.total} units</span></td></tr>
                    <tr><th>Broken:</th><td><span class="badge bg-warning">${equipment.broken_qty}</span></td></tr>
                    <tr><th>In Repair:</th><td><span class="badge bg-info">${equipment.repair_qty}</span></td></tr>
                    <tr><th>Maintenance:</th><td><span class="badge bg-warning">${equipment.maintenance} pending</span></td></tr>
                    <tr>
                        <th>Usage Rate:</th>
                        <td>
                            <div class="progress-bar" style="width: 100px;">
                                <div class="progress-fill" style="width: ${equipment.usage}%"></div>
                            </div>
                            ${equipment.usage}%
                        </td>
                    </tr>
                    <tr><th>Description:</th><td>${equipment.description}</td></tr>
                </table>
            </div>
        </div>
    `;

            const eqModal = document.getElementById('equipmentDetailsModal');
            const eqExisting = bootstrap.Modal.getInstance(eqModal);
            if (eqExisting) eqExisting.dispose();
            new bootstrap.Modal(eqModal).show();
        }

        function showSuccess(message) {
            // Check if message div exists
            let msgDiv = document.getElementById('messageDiv');

            if (!msgDiv) {
                // Create message div if it doesn't exist
                msgDiv = document.createElement('div');
                msgDiv.id = 'messageDiv';
                msgDiv.className = 'position-fixed top-0 end-0 m-3 p-3 rounded shadow';
                msgDiv.style.zIndex = '9999';
                document.body.appendChild(msgDiv);
            }

            msgDiv.className = 'position-fixed top-0 end-0 m-3 p-3 rounded shadow bg-success text-white';
            msgDiv.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>${message}`;

            setTimeout(() => {
                msgDiv.style.display = 'none';
            }, 3000);
        }

        function addEquipment() {
            isEditMode = false;
            currentEquipmentId = null;

            document.getElementById('addEquipmentForm').reset();
            document.getElementById('eqId').value = '';

            document.getElementById('eqImagePreview').style.display = 'none';
            document.getElementById('eqImagePlaceholder').style.display = 'block';
            document.getElementById('currentImageInfo').style.display = 'none';
            document.getElementById('maintenanceSection').style.display = 'none';
            document.getElementById('eqQty').value = '1';
            document.getElementById('eqSimultaneousUsers').value = '1';
            document.getElementById('eqSterilization').value = 'NO';
            document.getElementById('eqReservation').value = 'YES';
            document.getElementById('eqLabLocation').value = '';

            document.getElementById('equipmentModalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Equipment';
            document.getElementById('modalSaveButtonText').textContent = 'Save Equipment';

            clearEquipmentErrors();

            const modal = new bootstrap.Modal(document.getElementById('addEquipmentModal'));
            modal.show();
        }

        // Clear form errors
        function clearEquipmentErrors() {
            const errorFields = ['eqCode', 'eqName', 'eqQty', 'eqLabLocation'];
            errorFields.forEach(field => {
                const element = document.getElementById(field);
                if (element) element.classList.remove('is-invalid');
                const errorElement = document.getElementById(field + 'Error');
                if (errorElement) errorElement.textContent = '';
            });
        }

        // Preview image
        function previewEquipmentImage(input) {
            const preview = document.getElementById('eqImagePreview');
            const placeholder = document.getElementById('eqImagePlaceholder');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Save equipment (add or update)
        function saveEquipment() {
            const id = document.getElementById('eqId').value;
            const code = document.getElementById('eqCode').value.trim();
            const name = document.getElementById('eqName').value.trim();
            const qty = document.getElementById('eqQty').value;
            const simultaneous_users = document.getElementById('eqSimultaneousUsers').value || 1;
            const sterilization = document.getElementById('eqSterilization').value;
            const reservation = document.getElementById('eqReservation').value;
            const labLocation = document.getElementById('eqLabLocation').value;
            const description = document.getElementById('eqDescription').value.trim();
            const imageFile = document.getElementById('eqImage').files[0];

            // Validate
            let isValid = true;

            if (!code) {
                document.getElementById('eqCode').classList.add('is-invalid');
                document.getElementById('eqCodeError').textContent = 'Equipment code is required';
                isValid = false;
            } else {
                document.getElementById('eqCode').classList.remove('is-invalid');
            }

            if (!name) {
                document.getElementById('eqName').classList.add('is-invalid');
                document.getElementById('eqNameError').textContent = 'Equipment name is required';
                isValid = false;
            } else {
                document.getElementById('eqName').classList.remove('is-invalid');
            }

            if (!qty || qty < 1) {
                document.getElementById('eqQty').classList.add('is-invalid');
                document.getElementById('eqQtyError').textContent = 'Quantity must be at least 1';
                isValid = false;
            } else {
                document.getElementById('eqQty').classList.remove('is-invalid');
            }

            if (!labLocation) {
                document.getElementById('eqLabLocation').classList.add('is-invalid');
                document.getElementById('eqLabLocationError').textContent = 'Lab location is required';
                isValid = false;
            } else {
                document.getElementById('eqLabLocation').classList.remove('is-invalid');
            }

            if (!isValid) return;

            const saveBtn = document.querySelector('#addEquipmentModal .btn-success');
            const originalText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            const formData = new FormData();

            if (id) formData.append('id', id);

            formData.append('code', code);
            formData.append('name', name);
            formData.append('qty', qty);
            formData.append('simultaneous_users', simultaneous_users);
            formData.append('sterilization_required', sterilization);
            formData.append('reservation_required', reservation);
            formData.append('location_id', labLocation);
            formData.append('description', description);
            formData.append('broken_qty', document.getElementById('eqBrokenQty').value);
            formData.append('repair_qty', document.getElementById('eqMaintenanceQty').value);
            if (imageFile) formData.append('image', imageFile);

            const url = id ? '../controllers/save_update_eqdetails.php' : '../controllers/add_equipment.php';

            fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;

                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addEquipmentModal')).hide();
                        showSuccess(id ? 'Equipment updated successfully!' : 'Equipment added successfully!');
                        loadEquipmentWithUsage();
                    } else {
                        showToast('error', 'Error: ' + (data.message || 'Operation failed'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                    showToast('error', 'Network error. Please try again.');
                });
        }

        function searchEquipmentStatus() {
            filterAndDisplayEquipment();
        }

        // function editEquipment(code) {
        //     alert('Edit equipment: ' + code);
        // }

        // function removeEquipment(code) {
        //     if (confirm(`Are you sure you want to remove equipment ${code}?`)) {
        //         alert('Equipment removed successfully! (Note: This would update the database)');
        //     }
        // }

        // ========== REQUEST FILTER FUNCTIONS ==========

        // Filter by status for requests
        function filterRequestsByStatus() {
            const statusFilter = document.getElementById('statusFilter').value;
            const timeRange = document.getElementById('timeRangeFilter').value;
            const today = new Date();

            let filtered = [];

            switch (timeRange) {
                case 'daily':
                    filtered = requests.filter(item => {
                        const itemDate = new Date(item.timestamp * 1000);
                        return itemDate.toDateString() === today.toDateString();
                    });
                    break;
                case 'weekly':
                    const weekAgo = new Date();
                    weekAgo.setDate(today.getDate() - 7);
                    filtered = requests.filter(item => (item.timestamp * 1000) >= weekAgo.getTime());
                    break;
                case 'monthly':
                    const monthAgo = new Date();
                    monthAgo.setDate(today.getDate() - 30);
                    filtered = requests.filter(item => (item.timestamp * 1000) >= monthAgo.getTime());
                    break;
                case 'all':
                default:
                    filtered = [...requests];
                    break;
            }

            if (statusFilter !== 'all') {
                filtered = filtered.filter(item => item.status === statusFilter);
            }

            displayRequestTable(filtered);
        }

        // Update existing filterRequestsByTime function
        function filterRequestsByTime() {
            filterRequestsByStatus();
        }

        // Display request table with simplified columns
        // Display request table with simplified columns
        function displayRequestTable(requestsList) {
            const tableBody = document.getElementById('requestListBody');
            if (!tableBody) return;
            tableBody.innerHTML = '';
            requestsList.sort((a, b) => b.timestamp - a.timestamp);
            requestsList.forEach((item, idx) => {
                const row = document.createElement('tr');
                let statusClass = item.status === 'pending' ? 'bg-warning' : item.status === 'approved' ? 'bg-success' : 'bg-danger';
                let statusText = item.status === 'pending' ? 'Pending' : item.status === 'approved' ? 'Approved' : 'Rejected';
                row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.dateTime}</td>
            <td>${item.studentId}</td>
            <td>${item.lab}</td>
            <td><span class="badge ${statusClass}">${statusText}</span></td>
            <td>
                <div class="action-buttons">
                  ${item.status === 'pending' ? ` <button class="btn-view" onclick="viewRequest('${item.id}')" title="View Details"><i class="bi bi-eye"></i></button>` : ''}
                   
                    ${item.status === 'pending' ? `<button class="btn-remove" onclick="rejectRequest('${item.id}')" title="Reject"><i class="bi bi-x-circle"></i></button>` : ''}
                </div>
            </td>
        `;
                tableBody.appendChild(row);
            });
            if (requestsList.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center">No requests found</td></tr>`;
            }
        }

        // View request details



        // ========== CALENDAR FUNCTIONS ==========
        function initCalendar() {
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const prevLastDay = new Date(year, month, 0);
            const prevDays = prevLastDay.getDate();
            const lastDate = lastDay.getDate();
            const day = firstDay.getDay();
            const nextDays = 7 - lastDay.getDay() - 1;

            const displayMonth = document.getElementById('displayMonth');
            if (displayMonth) {
                displayMonth.innerHTML = months[month] + " " + year;
            }

            let days = "";

            for (let x = day; x > 0; x--) {
                days += `<div class="day-cell prev-date">${prevDays - x + 1}</div>`;
            }

            for (let i = 1; i <= lastDate; i++) {
                let hasEvent = false;
                eventsArr.forEach(event => {
                    if (event.day === i && event.month === month + 1 && event.year === year) {
                        hasEvent = true;
                    }
                });

                let classes = "day-cell";
                if (i === new Date().getDate() && year === new Date().getFullYear() && month === new Date().getMonth()) {
                    classes += " today";
                }
                if (hasEvent) {
                    classes += " event";
                }

                days += `<div class="${classes}" data-day="${i}">${i}</div>`;
            }

            for (let j = 1; j <= nextDays; j++) {
                days += `<div class="day-cell next-date">${j}</div>`;
            }

            const daysGrid = document.getElementById('daysGrid');
            if (daysGrid) {
                daysGrid.innerHTML = days;
            }
            updateActiveDay();

            if (activeDay) {
                updateEventDisplay(activeDay);
            } else {
                updateEventDisplay(new Date().getDate());
            }
        }

        function updateActiveDay() {
            const dayCells = document.querySelectorAll('.day-cell');
            dayCells.forEach(cell => {
                cell.addEventListener('click', function(e) {
                    if (!this.classList.contains('prev-date') && !this.classList.contains('next-date')) {
                        dayCells.forEach(c => c.classList.remove('active'));
                        this.classList.add('active');

                        const day = parseInt(this.textContent);
                        activeDay = day;
                        updateEventDisplay(day);
                    }
                });
            });
        }

        function showSection(section) {
            console.log('Showing section:', section);
            const sections = ['dashboard', 'userManagement', 'equipment', 'history', 'activity', 'analytics'];
            sections.forEach(s => {
                const el = document.getElementById(s + 'Section');
                if (el) el.style.display = 'none';
            });

            const sectionElement = document.getElementById(section + 'Section');
            if (sectionElement) {
                sectionElement.style.display = 'block';
                console.log('Section displayed:', section);
            } else {
                console.error('Section not found:', section + 'Section');
            }

            document.querySelectorAll('.sidebar a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('onclick')?.includes(section)) {
                    link.classList.add('active');
                }
            });

            if (section === 'equipment') {
                loadEquipmentWithUsage();
            }
            if (section === 'dashboard' || section === 'analytics') {
                setTimeout(() => {
                    if (typeof initCharts === 'function') initCharts();
                    if (typeof initAnalyticsCharts === 'function') initAnalyticsCharts();
                    if (typeof initCalendar === 'function') initCalendar();
                    if (typeof initCalendarListeners === 'function') initCalendarListeners();
                }, 100);
            }
            if (section === 'history') {
                const searchInput = document.getElementById('reservationSearch');
                if (searchInput) searchInput.value = '';
                const statusFilter = document.getElementById('statusFilter');
                if (statusFilter) statusFilter.value = 'all';
                if (typeof loadReservations === 'function') loadReservations();
            }
            if (section === 'activity') {
                if (typeof initRequestSection === 'function') initRequestSection();
            }
        }

        function updateEventDisplay(day) {
            const date = new Date(year, month, day);
            const dayName = date.toString().split(' ')[0];

            const eventDayEl = document.getElementById('eventDay');
            const eventDateEl = document.getElementById('eventDate');

            if (eventDayEl) {
                eventDayEl.innerHTML = dayName;
            }
            if (eventDateEl) {
                eventDateEl.innerHTML = `${day} ${months[month]} ${year}`;
            }

            let dayEvents = [];
            eventsArr.forEach(event => {
                if (event.day === day && event.month === month + 1 && event.year === year) {
                    dayEvents.push(event);
                }
                if (event.year === year && event.month === month + 1) {
                    if (day >= event.day && day <= event.end_day && !dayEvents.includes(event)) {
                        dayEvents.push(event);
                    }
                }
            });

            let eventsHtml = "";
            if (dayEvents.length > 0) {
                dayEvents.forEach(event => {
                    let dateRange = '';
                    if (event.day !== event.end_day || event.month !== event.end_month) {
                        dateRange = `<div class="event-date-range">
                    ${event.day} ${months[event.month-1]} - ${event.end_day} ${months[event.end_month-1]} ${event.end_year}
                </div>`;
                    }

                    let equipmentHtml = '';
                    if (event.equipment) {
                        equipmentHtml = `<div><i class="bi bi-tools" style="color: #ffd700;"></i> ${event.equipment}</div>`;
                    }

                    eventsHtml += `
                <div class="event-item">
                    <div class="title">
                        <i class="fas fa-circle"></i>
                        <span class="event-title">${event.title}</span>
                    </div>
                    ${dateRange}
                    <div class="event-time"><strong>Duration:</strong> ${event.duration}</div>
                    <div class="event-details">
                        <div><i class="bi bi-person-fill" style="color: #ffd700;"></i> Student: ${event.student}</div>
                        <div><i class="bi bi-pin-map-fill" style="color: #ffd700;"></i> ${event.location}</div>
                        ${equipmentHtml}
                    </div>
                </div>
            `;
                });
            } else {
                eventsHtml = '<div class="no-event">No bookings scheduled for this day</div>';
            }

            const eventsList = document.getElementById('eventsList');
            if (eventsList) {
                eventsList.innerHTML = eventsHtml;
            }
        }

        // ========== CALENDAR NAVIGATION ==========
        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');

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

        const todayBtn = document.getElementById('todayBtn');
        if (todayBtn) {
            todayBtn.addEventListener('click', () => {
                const today = new Date();
                month = today.getMonth();
                year = today.getFullYear();
                initCalendar();
                updateEventDisplay(today.getDate());
            });
        }

        const gotoBtn = document.getElementById('gotoBtn');
        if (gotoBtn) {
            gotoBtn.addEventListener('click', () => {
                const input = document.getElementById('gotoInput').value;
                const parts = input.split('/');
                if (parts.length === 2) {
                    const m = parseInt(parts[0]) - 1;
                    const y = parseInt(parts[1]);
                    if (m >= 0 && m < 12 && y > 0) {
                        month = m;
                        year = y;
                        initCalendar();
                    } else {
                        showToast('warning', 'Invalid date format. Use MM/YYYY');
                    }
                } else {
                    showToast('warning', 'Invalid date format. Use MM/YYYY');
                }
            });
        }

        // ========== INITIALIZATION ==========
        document.addEventListener('DOMContentLoaded', function() {
            initCalendar();
            loadPendingLogbooks();
            startNotificationPolling();

            // Check if this is a fresh login (you can set a flag in your PHP session)
            const isFreshLogin = <?php echo isset($_SESSION['fresh_login']) ? 'true' : 'false'; ?>;

            if (isFreshLogin) {
                // Clear saved section on fresh login
                sessionStorage.removeItem('lastSection');
                <?php unset($_SESSION['fresh_login']); // Clear the flag 
                ?>
            }

            const lastSection = sessionStorage.getItem('lastSection');

            if (!lastSection) {
                showSection('dashboard');
            } else {
                showSection(lastSection);
            }

            if (document.getElementById('requestListBody')) {
                filterRequestsByStatus();
            }

            // Initialize profile modal
            const profileModal = document.getElementById('profileModal');
            if (profileModal) {
                profileModal.addEventListener('hidden.bs.modal', () => {
                    location.reload();
                });
            }
        });

        // Profile Functions
        function openProfileModal(event) {
            event.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('profileModal'));

            // Load user data
            const firstName = '<?= htmlspecialchars($user_data['first_name'] ?? '') ?>';
            const lastName = '<?= htmlspecialchars($user_data['last_name'] ?? '') ?>';
            const email = '<?= htmlspecialchars($user_data['email'] ?? '') ?>';
            const mobile = '<?= htmlspecialchars($user_data['mobile'] ?? '') ?>';
            const universityId = '<?= htmlspecialchars($user_data['university_id'] ?? '') ?>';
            const joinDate = '<?= htmlspecialchars($user_data['join_datetime'] ?? '') ?>';

            // Populate form
            document.getElementById('firstName').value = firstName;
            document.getElementById('lastName').value = lastName;
            document.getElementById('email').value = email;
            document.getElementById('mobile').value = mobile;
            document.getElementById('universityId').value = universityId;
            document.getElementById('joinDate').value = joinDate ? new Date(joinDate).toLocaleDateString() : '';

            // Set profile image
            const profileImg = document.querySelector('.profile-img');
            document.getElementById('profileImagePreview').src = profileImg.src;

            modal.show();
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showToast('error', 'Please select a valid image file (JPEG, PNG, GIF, or WebP)');
                return;
            }

            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                showToast('error', 'Image size must be less than 5MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('profileImagePreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        document.getElementById('profileImageInput')?.addEventListener('change', previewImage);

        function saveProfile() {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const mobile = document.getElementById('mobile').value.trim();

            if (!firstName || !lastName || !email || !mobile) {
                showToast('error', 'Please fill in all required fields');
                return;
            }

            const formData = new FormData();
            formData.append('first_name', firstName);
            formData.append('last_name', lastName);
            formData.append('email', email);
            formData.append('mobile', mobile);

            const fileInput = document.getElementById('profileImageInput');
            if (fileInput.files.length > 0) {
                formData.append('profile_image', fileInput.files[0]);
            }

            const saveBtn = event.target;
            const originalText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            fetch('../controllers/update_technical_officer_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', 'Profile updated successfully!');
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('profileModal')).hide();
                        }, 1000);
                    } else {
                        showToast('error', data.message || 'Failed to update profile');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Network error. Please try again.');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                });
        }

        function showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = `
                <i class="bi ${type === 'success' ? 'bi-check-circle' : type === 'error' ? 'bi-exclamation-circle' : 'bi-info-circle'} me-2"></i>
                ${message}
            `;

            const colors = {
                success: '#22c55e',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            Object.assign(toast.style, {
                position: 'fixed',
                bottom: '20px',
                right: '20px',
                backgroundColor: colors[type],
                color: 'white',
                padding: '12px 20px',
                borderRadius: '8px',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                zIndex: '9999',
                display: 'flex',
                alignItems: 'center',
                fontSize: '14px',
                fontWeight: '500',
                animation: 'slideIn 0.3s ease-out'
            });

            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ========== LOGBOOK APPROVAL SYSTEM FOR TECHNICAL OFFICER ==========
        let notificationPollingInterval = null;
        let currentLogbookId = null;

        function loadPendingLogbooks() {
            const container = document.getElementById('notificationList');
            if (!container) {
                console.error('notificationList container not found');
                return;
            }

            container.innerHTML = `
        <div class="text-center p-3">
            <div class="spinner-border text-success spinner-border-sm" role="status"></div>
            <p class="mt-2 mb-0">Loading pending logbooks...</p>
        </div>
    `;

            fetch('../controllers/fetch_pending_logbooks_technicalOfficer.php')
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Logbooks data received:', data);

                    if (data.error) {
                        console.error('Error from server:', data.error);
                        container.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-exclamation-circle"></i>
                        <p>${data.error}</p>
                    </div>
                `;
                        updateBadgeCount(0);
                        return;
                    }

                    // Ensure data is an array
                    const logbooksArray = Array.isArray(data) ? data : [];
                    console.log('Processing logbooks:', logbooksArray.length);

                    updateNotificationList(logbooksArray);
                    updateBadgeCount(logbooksArray.length);
                })
                .catch(error => {
                    console.error('Error loading pending logbooks:', error);
                    container.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-exclamation-circle"></i>
                    <p>Error loading notifications</p>
                    <small>${error.message}</small>
                </div>
            `;
                    updateBadgeCount(0);
                });
        }

        function updateNotificationList(logbooks) {
            const container = document.getElementById('notificationList');

            if (!logbooks || logbooks.length === 0) {
                container.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-check-circle"></i>
                <p>No pending logbooks to review</p>
            </div>
        `;
                return;
            }

            let html = '';
            logbooks.forEach(logbook => {
                // Format the date if available
                const submittedDate = logbook.submitted_date && logbook.submitted_date !== 'Awaiting review' ?
                    new Date(logbook.submitted_date).toLocaleString() :
                    'Awaiting review';

                html += `
            <div class="notification-item" id="logbook-${logbook.id}">
                <div class="notification-title">
                    <strong>Logbook #${logbook.id}</strong> - ${escapeHtml(logbook.student_name)}
                </div>
                <div class="notification-meta">
                    <i class="bi bi-person-badge"></i> ${escapeHtml(logbook.university_id)}<br>
                    <i class="bi bi-receipt"></i> Reservation: ${escapeHtml(logbook.reservation_code)}<br>
                    <i class="bi bi-calendar"></i> Submitted: ${submittedDate}<br>
                    <i class="bi bi-image"></i> Photos: ${logbook.has_photos || 0} image(s)
                </div>
               
                <div class="notification-actions justify-content-center">
                  
                    <button class="btn-view" onclick="viewLogbookDetails(${logbook.id})">
                        <i class="bi bi-eye"></i> View Details
                    </button>
                </div>
            </div>
        `;
            });
            container.innerHTML = html;
        }

        function updateBadgeCount(count) {
            const badge = document.getElementById('requestBadge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.add('visible');
                } else {
                    badge.textContent = '0';
                    badge.classList.remove('visible');
                }
            }
        }

        // Approve logbook with modal confirmation
        function approveLogbook(logbookId) {
            showConfirmModal(
                'Approve Logbook',
                '<div class="text-center">' +
                '<i class="bi bi-check-circle-fill text-success" style="font-size: 48px;"></i>' +
                '<p class="mt-3">Are you sure you want to approve this logbook?</p>' +
                '<p class="text-muted small">This will mark the logbook as reviewed and send it to HOD for final approval.</p>' +
                '</div>',
                'Yes, Approve',
                'btn-success',
                () => {
                    executeApprove(logbookId);
                }
            );
        }

        // Reject logbook - shows rejection reason modal first
        function rejectLogbook(logbookId) {
            showRejectModal(logbookId);
        }

        // Show rejection reason modal
        function showRejectModal(logbookId) {
            currentLogbookId = logbookId;

            const modalElement = document.getElementById('rejectReasonModal');
            const textarea = document.getElementById('rejectionReason');

            if (!modalElement || !textarea) {
                console.error('Modal or textarea not found');
                const reason = prompt('Enter rejection reason:');
                if (reason && reason.trim()) executeReject(logbookId, reason);
                return;
            }

            // Clear textarea
            textarea.value = '';
            textarea.classList.remove('is-invalid');

            // Remove any error div
            const errorDiv = document.getElementById('reasonError');
            if (errorDiv) errorDiv.remove();

            // Show modal using Bootstrap
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }




















        // Confirm rejection after entering reason
        // function confirmReject() {
        //     const reason = document.getElementById('rejectionReason').value.trim();

        //     if (!reason) {
        //         const textarea = document.getElementById('rejectionReason');
        //         textarea.classList.add('is-invalid');

        //         let errorDiv = document.getElementById('reasonError');
        //         if (!errorDiv) {
        //             errorDiv = document.createElement('div');
        //             errorDiv.id = 'reasonError';
        //             errorDiv.className = 'invalid-feedback';
        //             errorDiv.innerHTML = '<i class="bi bi-exclamation-circle"></i> Please enter a reason for rejection';
        //             textarea.parentNode.appendChild(errorDiv);
        //         }

        //         textarea.style.animation = 'shake 0.5s';
        //         setTimeout(() => {
        //             textarea.style.animation = '';
        //         }, 500);

        //         return;
        //     }

        //     // Close rejection modal
        //     const rejectModal = document.getElementById('rejectReasonModal');
        //     const modal = bootstrap.Modal.getInstance(rejectModal);
        //     if (modal) modal.hide();

        //     // Show confirmation modal
        //     showConfirmModal(
        //         'Reject Logbook',
        //         '<div class="text-center">' +
        //         '<i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 48px;"></i>' +
        //         '<p class="mt-3"><strong>Are you sure you want to reject this logbook?</strong></p>' +
        //         '<div class="alert alert-danger mt-3">' +
        //         '<i class="bi bi-info-circle me-2"></i>' +
        //         '<strong>Reason:</strong> ' + escapeHtml(reason) +
        //         '</div>' +
        //         '<p class="text-danger small mt-2">⚠️ This action cannot be undone. The student will be notified.</p>' +
        //         '</div>',
        //         'Yes, Reject',
        //         'btn-danger',
        //         () => {
        //             executeReject(currentLogbookId, reason);
        //         }
        //     );
        // }

        // Execute approve action
        function executeApprove(logbookId) {
            //showToast('info', 'Processing approval...');
            closeModalManually('confirmModal');

            fetch('../controllers/approve_logbook_technicalOfficer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        logbook_id: logbookId,
                        action: 'approve'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', data.message || 'Logbook approved successfully!');
                        const item = document.getElementById(`logbook-${logbookId}`);
                        if (item) {
                            item.style.transition = 'all 0.3s ease';
                            item.style.opacity = '0';
                            item.style.transform = 'translateX(-100%)';
                            setTimeout(() => {
                                if (item.parentNode) item.remove();
                                const remainingItems = document.querySelectorAll('.notification-item').length;
                                updateBadgeCount(remainingItems);

                                if (remainingItems === 0) {
                                    document.getElementById('notificationList').innerHTML = `
                            <div class="empty-state">
                                <i class="bi bi-check-circle"></i>
                                <p>No pending logbooks to review</p>
                            </div>
                        `;
                                }
                            }, 300);
                        }


                        setTimeout(function() {
                            location.reload();
                        }, 2000); // 2000ms = 2 seconds

                    } else {
                        showToast('error', data.message || 'Error approving logbook');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Network error. Please try again.');
                });
        }

        // Execute reject action
        function executeReject(logbookId, reason) {
            // showToast('info', 'Processing rejection...');

            const rejectBtn = document.querySelector(`#logbook-${logbookId} .btn-reject`);
            if (rejectBtn) {
                rejectBtn.disabled = true;
                rejectBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Rejecting...';
            }

            fetch('../controllers/approve_logbook_technicalOfficer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        logbook_id: logbookId,
                        action: 'reject',
                        reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                           showToast('warning', data.message || 'Logbook rejected successfully');
                        const item = document.getElementById(`logbook-${logbookId}`);
                        if (item) {
                            item.style.transition = 'all 0.3s ease';
                            item.style.opacity = '0';
                            item.style.transform = 'translateX(100%)';
                            setTimeout(() => {
                                if (item.parentNode) item.remove();
                                const remainingItems = document.querySelectorAll('.notification-item').length;
                                updateBadgeCount(remainingItems);

                                if (remainingItems === 0) {
                                    document.getElementById('notificationList').innerHTML = `
                            <div class="empty-state">
                                <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                                <p class="mt-3">No pending logbooks to review</p>
                            </div>
                        `;
                                }
                            }, 300);
                        }
                     
                        setTimeout(function() {
                            location.reload();
                        }, 2000); // refresh after 3 seconds
                    } else {
                        showToast('error', data.message || 'Error rejecting logbook');
                        if (rejectBtn) {
                            rejectBtn.disabled = false;
                            rejectBtn.innerHTML = '<i class="bi bi-x-circle"></i> Reject';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Network error. Please try again.');
                    if (rejectBtn) {
                        rejectBtn.disabled = false;
                        rejectBtn.innerHTML = '<i class="bi bi-x-circle"></i> Reject';
                    }
                });
        }

        // View logbook details
        // function viewLogbookDetails(logbookId) {
        //     showToast('info', 'Loading logbook details...');
        //     fetch(`../controllers/get_logbook_details_technicalOfficer.php?id=${logbookId}`)
        //         .then(response => response.json())
        //         .then(data => {
        //             if (data.success) {

        //                 let imagesHtml = '';
        //                 if (data.images && data.images.length > 0) {
        //                     imagesHtml = '<div style="margin-top: 15px;"><strong>Images:</strong><div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 5px;">';
        //                     data.images.forEach(img => {
        //                         imagesHtml += `<img src="../${img}" style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 1px solid #ddd;" onerror="this.style.display=\'none\'">`;
        //                     });
        //                     imagesHtml += '</div></div>';
        //                 }


        //                 const details = `Logbook #${logbookId}\n\n` +
        //                     `Student: ${data.student_name}\n` +
        //                     `University ID: ${data.university_id}\n` +
        //                     `Email: ${data.student_email}\n` +
        //                     `Mobile: ${data.student_mobile}\n` +
        //                     `Reservation: ${data.reservation_code}\n` +
        //                     `Request Date: ${data.request_date}\n` +
        //                     `Duration: ${data.duration}\n` +
        //                     `Lab Location: ${data.location}\n\n` +
        //                     `Student Comment:\n${data.description}\n`;


        //                 if (data.images && data.images.length > 0) {
        //                     alert(details + '\n\nImages available. Check console for image URLs.');
        //                     console.log('Images:', data.images);
        //                 } else {
        //                     alert(details);
        //                 }
        //                 showToast('Logbook details loaded', 'success');
        //             } else {
        //                 showToast(data.message || 'Failed to load logbook details', 'error');
        //             }
        //         })
        //         .catch(error => {
        //             console.error('Error:', error);
        //             showToast('Error loading details', 'error');
        //         });
        // }

        // Show confirmation modal
        function showConfirmModal(title, message, confirmText, confirmClass, onConfirm) {
            const modalElement = document.getElementById('confirmModal');
            const modalHeader = document.getElementById('confirmModalHeader');
            const modalTitle = document.getElementById('confirmModalTitle');
            const modalBody = document.getElementById('confirmModalBody');
            const confirmBtn = document.getElementById('confirmModalBtn');

            // Reset and style header
            modalHeader.className = 'modal-header';
            if (confirmClass === 'btn-success') {
                modalHeader.classList.add('bg-success', 'text-white');
            } else if (confirmClass === 'btn-danger') {
                modalHeader.classList.add('bg-danger', 'text-white');
            } else {
                modalHeader.classList.add('bg-warning', 'text-white');
            }

            // Set title and message
            modalTitle.innerHTML = `<i class="bi bi-question-circle me-2"></i>${title}`;
            modalBody.innerHTML = message;

            // Style the confirm button
            confirmBtn.className = 'btn ' + confirmClass;
            confirmBtn.innerHTML = `<i class="bi bi-check-circle me-2"></i>${confirmText}`;

            // Remove previous event listeners by cloning
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

            // Add click handler
            newConfirmBtn.addEventListener('click', () => {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
                if (onConfirm && typeof onConfirm === 'function') {
                    onConfirm();
                }
            });

            // Show modal
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }

        // Helper function to close modals manually
        function closeModalManually(modalId) {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                try {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        modalElement.classList.remove('show');
                        modalElement.style.display = 'none';
                    }
                } catch (e) {
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                }

                // Remove backdrop if any
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());

                // Restore body scroll
                document.body.style.overflow = '';
                document.body.classList.remove('modal-open');
            }
        }

        // Confirm rejection from modal
        function confirmReject() {
            const textarea = document.getElementById('rejectionReason');
            if (!textarea) return;

            const reason = textarea.value.trim();
            if (!reason) {
                textarea.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.id = 'reasonError';
                errorDiv.className = 'invalid-feedback d-block';
                errorDiv.textContent = 'Please provide a reason for rejection';
                if (!document.getElementById('reasonError')) {
                    textarea.parentNode.appendChild(errorDiv);
                }
                return;
            }

            closeModalManually('rejectReasonModal');
            if (currentLogbookId) {
                executeReject(currentLogbookId, reason);
            }
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Start polling for new logbooks every 30 seconds
        function startNotificationPolling() {
            if (notificationPollingInterval) clearInterval(notificationPollingInterval);
            notificationPollingInterval = setInterval(loadPendingLogbooks, 30000);
        }

        function stopNotificationPolling() {
            if (notificationPollingInterval) clearInterval(notificationPollingInterval);
        }

        // Toggle notification dropdown
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown && (dropdown.style.display === 'none' || dropdown.style.display === '')) {
                dropdown.style.display = 'block';
                loadPendingLogbooks();
            } else if (dropdown) {
                dropdown.style.display = 'none';
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const logbookBell = document.querySelector('[onclick="toggleNotificationDropdown()"]');
            const logbookDropdown = document.getElementById('notificationDropdown');
            if (logbookBell && logbookDropdown) {
                const bellParent = logbookBell.closest('.notification-bell');
                if (bellParent && !bellParent.contains(event.target)) {
                    logbookDropdown.style.display = 'none';
                }
            }
        });

        function viewLogbookDetails(logbookId) {
            //  showToast('info', 'Loading logbook details...');
            // Store the logbook ID for approve/reject buttons
            window.currentViewingLogbookId = logbookId;

            fetch(`../controllers/get_logbook_details_technicalOfficer.php?id=${logbookId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Logbook details response:', data);
                    if (data.success) {
                        // Build images HTML
                        let imagesHtml = '';
                        if (data.images && data.images.length > 0) {
                            imagesHtml = '<div class="mt-4"><strong class="d-block mb-3"><i class="bi bi-image me-2"></i>Submitted Photos:</strong><div style="display: flex; flex-wrap: nowrap; gap: 15px; overflow-x: auto; padding-bottom: 10px;">';
                            data.images.forEach((img, idx) => {
                                const fileName = img.split('/').pop();
                                imagesHtml += `<div style="text-align: center; flex-shrink: 0;"><img src="../${img}" style="width: 180px; height: 180px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1); cursor: pointer;" onerror="this.style.display='none'" title="Photo ${idx + 1}"><p class="small text-muted mt-2">Photo ${idx + 1}</p><a href="../${img}" download="${fileName}" class="btn btn-sm btn-outline-primary mt-1" style="font-size: 0.75rem; padding: 3px 8px;"><i class="bi bi-download"></i></a></div>`;
                            });
                            imagesHtml += '</div></div>';
                        }

                        const detailsHtml = `
                    <div style="max-height: 600px; overflow-y: auto;">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="color: #166534;"><i class="bi bi-person me-2"></i>Student Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Name:</strong> ${escapeHtml(data.student_name)}</p>
                                        <p class="mb-2"><strong>University ID:</strong> ${escapeHtml(data.university_id)}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Email:</strong> ${escapeHtml(data.student_email)}</p>
                                        <p class="mb-2"><strong>Mobile:</strong> ${escapeHtml(data.student_mobile)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="color: #166534;"><i class="bi bi-receipt me-2"></i>Reservation Details</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Reservation Code:</strong> ${escapeHtml(data.reservation_code)}</p>
                                        <p class="mb-2"><strong>Request Date:</strong> ${escapeHtml(data.request_date)}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Duration:</strong> ${escapeHtml(data.duration || 'N/A')}</p>
                                        <p class="mb-2"><strong>Lab Location:</strong> ${escapeHtml(data.location || 'N/A')}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="color: #166534;"><i class="bi bi-chat-left-text me-2"></i>Student's Practical Session Comment</h6>
                                <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; border-left: 3px solid #22c55e;">
                                    <p class="mb-0">${escapeHtml(data.description || 'No comment provided')}</p>
                                </div>
                            </div>
                        </div>
                        
                        ${imagesHtml}
                    </div>
                `;

                        // Create modal content
                        const modalElement = document.getElementById('logbookDetailsModal');
                        if (modalElement) {
                            const body = modalElement.querySelector('.modal-body');
                            if (body) {
                                body.innerHTML = detailsHtml;
                                const modal = new bootstrap.Modal(modalElement);
                                modal.show();
                            }
                        } else {
                            alert(`Logbook #${logbookId}\nStudent: ${data.student_name}\nUniversity ID: ${data.university_id}`);
                        }
                        showToast('success', 'Logbook details loaded');
                    } else {
                        console.error('Server response error:', data);
                        showToast('error', data.message || 'Failed to load logbook details');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showToast('error', 'Error loading details: ' + error.message);
                });
        }

        // Approve logbook from modal
        function approveLogbookFromModal() {
            const logbookId = window.currentViewingLogbookId;
            if (!logbookId) {
                showToast('error', 'No logbook selected');
                return;
            }

            showConfirmModal(
                'Approve Logbook',
                '<div class="text-center">' +
                '<i class="bi bi-check-circle-fill text-success" style="font-size: 48px;"></i>' +
                '<p class="mt-3">Are you sure you want to approve this logbook?</p>' +
                '<p class="text-muted small">This will mark the logbook as reviewed and send it to HOD for final approval.</p>' +
                '</div>',
                'Yes, Approve',
                'btn-success',
                () => {
                    executeApprove(logbookId);
                }
            );
        }

        // Reject logbook from modal
        function rejectLogbookFromModal() {
            const logbookId = window.currentViewingLogbookId;
            if (!logbookId) {
                showToast('error', 'No logbook selected');
                return;
            }

            showRejectModal(logbookId);
        }

        // function startNotificationPolling() {
        //     if (notificationPollingInterval) clearInterval(notificationPollingInterval);
        //     notificationPollingInterval = setInterval(loadPendingLogbooks, 30000);
        // }

        function stopNotificationPolling() {
            if (notificationPollingInterval) clearInterval(notificationPollingInterval);
        }

        // function toggleNotificationDropdown() {
        //     const dropdown = document.getElementById('notificationDropdown');
        //     if (dropdown && (dropdown.style.display === 'none' || dropdown.style.display === '')) {
        //         dropdown.style.display = 'block';
        //         if (typeof loadPendingLogbooks === 'function') {
        //             loadPendingLogbooks();
        //         }
        //     } else if (dropdown) {
        //         dropdown.style.display = 'none';
        //     }
        // }



        // Show rejection reason modal
        // function showRejectModal(logbookId) {
        //     console.log('showRejectModal called:', logbookId);
        //     currentLogbookId = logbookId;

        //     try {
        //         const modalElement = document.getElementById('rejectReasonModal');
        //         const textarea = document.getElementById('rejectionReason');

        //         console.log('Modal element exists:', !!modalElement);
        //         console.log('Textarea exists:', !!textarea);

        //         if (!modalElement || !textarea) {
        //             console.error('Modal or textarea not found');
        //             const reason = prompt('Enter rejection reason:');
        //             if (reason && reason.trim()) executeReject(logbookId, reason);
        //             return;
        //         }


        //         textarea.value = '';
        //         textarea.classList.remove('is-invalid');


        //         const errorDiv = document.getElementById('reasonError');
        //         if (errorDiv) errorDiv.remove();


        //         const existingBackdrop = document.querySelector('.modal-backdrop');
        //         if (existingBackdrop) existingBackdrop.remove();


        //         modalElement.classList.add('show');
        //         modalElement.style.display = 'block';

        //         console.log('Modal element classes after manual add:', modalElement.className);
        //         console.log('Modal display style:', window.getComputedStyle(modalElement).display);


        //         const backdrop = document.createElement('div');
        //         backdrop.className = 'modal-backdrop fade show';
        //         backdrop.style.zIndex = '8999';
        //         document.body.appendChild(backdrop);

        //         console.log('Backdrop created and appended');


        //         document.body.style.overflow = 'hidden';
        //         document.body.classList.add('modal-open');


        //         if (typeof bootstrap !== 'undefined') {
        //             try {
        //                 const modal = new bootstrap.Modal(modalElement, {
        //                     backdrop: false,
        //                     keyboard: false
        //                 });
        //                 modal.show();
        //                 console.log('Bootstrap modal also showed (fallback)');
        //             } catch (e) {
        //                 console.log('Bootstrap modal show failed, but manual show succeeded:', e.message);
        //             }
        //         }

        //     } catch (error) {
        //         console.error('Error in showRejectModal:', error);
        //         const reason = prompt('Enter rejection reason:');
        //         if (reason && reason.trim()) executeReject(logbookId, reason);
        //     }
        // }

        // Confirm rejection
        function confirmReject() {
            const reason = document.getElementById('rejectionReason').value.trim();

            if (!reason) {
                // Show error in modal
                const textarea = document.getElementById('rejectionReason');
                textarea.classList.add('is-invalid');

                // Add error message if not exists
                let errorDiv = document.getElementById('reasonError');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.id = 'reasonError';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.innerHTML = '<i class="bi bi-exclamation-circle"></i> Please enter a reason for rejection';
                    textarea.parentNode.appendChild(errorDiv);
                }

                // Focus and shake animation
                textarea.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    textarea.style.animation = '';
                }, 500);

                return;
            }

            // Close rejection modal manually
            closeModalManually('rejectReasonModal');

            // Show confirmation modal
            showConfirmModal(
                'Reject Logbook',
                '<div class="text-center"><i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 48px;"></i><p class="mt-3">Are you sure you want to reject this logbook?</p><p class="text-muted small">Reason: <strong>' + escapeHtml(reason) + '</strong></p><p class="text-danger small">This action cannot be undone.</p></div>',
                'Yes, Reject',
                'btn-danger',
                () => {
                    executeReject(currentLogbookId, reason);
                }
            );
        }

        // Helper function to close modals manually
        // function closeModalManually(modalId) {
        //     const modalElement = document.getElementById(modalId);
        //     if (modalElement) {
        //         // Remove show class
        //         modalElement.classList.remove('show');
        //         modalElement.style.display = 'none';

        //         console.log('Modal closed manually:', modalId);

        //         // Try Bootstrap method as fallback
        //         if (typeof bootstrap !== 'undefined') {
        //             try {
        //                 const instance = bootstrap.Modal.getInstance(modalElement);
        //                 if (instance) {
        //                     instance.hide();
        //                 }
        //             } catch (e) {
        //                 console.log('Bootstrap modal hide not available, but manual close done');
        //             }
        //         }

        //         // Remove backdrop
        //         const backdrop = document.querySelector('.modal-backdrop');
        //         if (backdrop) {
        //             backdrop.remove();
        //         }

        //         // Restore body scroll
        //         document.body.style.overflow = '';
        //         document.body.classList.remove('modal-open');
        //     }
        // }

        // Execute reject
        // function executeReject(logbookId, reason) {
        //     // Show loading state
        //     showToast('Processing rejection...', 'info');

        //     fetch('../controllers/approve_logbook_technicalOfficer.php', {
        //             method: 'POST',
        //             headers: {
        //                 'Content-Type': 'application/json',
        //             },
        //             body: JSON.stringify({
        //                 logbook_id: logbookId,
        //                 action: 'reject',
        //                 reason: reason
        //             })
        //         })
        //         .then(response => response.json())
        //         .then(data => {
        //             if (data.success) {
        //                 // Remove the item from list
        //                 const item = document.getElementById(`logbook-${logbookId}`);
        //                 if (item) {
        //                     item.style.animation = 'slideOut 0.3s ease';
        //                     setTimeout(() => item.remove(), 300);
        //                 }

        //                 // Update badge count
        //                 const remainingItems = document.querySelectorAll('.notification-item').length;
        //                 updateBadgeCount(remainingItems);

        //                 // Show success message
        //                 showToast(data.message || 'Logbook rejected successfully', 'warning');

        //                 // Refresh list if no items left
        //                 if (remainingItems === 0) {
        //                     setTimeout(() => {
        //                         document.getElementById('notificationList').innerHTML = `
        //             <div class="empty-state">
        //                 <i class="bi bi-check-circle"></i>
        //                 <p>No pending logbooks to review</p>
        //             </div>
        //         `;
        //                     }, 300);
        //                 }
        //             } else {
        //                 showToast(data.message || 'Error rejecting logbook', 'error');
        //             }
        //         })
        //         .catch(error => {
        //             console.error('Error:', error);
        //             showToast('Network error. Please try again.', 'error');
        //         });
        // }



        // Execute approve
        function executeApprove(logbookId) {
            // Show loading state
            //  showToast('Processing approval...', 'info');

            fetch('../controllers/approve_logbook_technicalOfficer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        logbook_id: logbookId,
                        action: 'approve'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                          // Show success message
                        showToast(data.message || 'Logbook approved successfully!', 'success');
                        // Remove the item from list with animation
                        const item = document.getElementById(`logbook-${logbookId}`);
                        if (item) {
                            item.style.animation = 'slideOut 0.3s ease';
                            setTimeout(() => item.remove(), 300);
                        }

                        // Update badge count
                        const remainingItems = document.querySelectorAll('.notification-item').length;
                        updateBadgeCount(remainingItems);

                      

                        // Refresh list if no items left
                        if (remainingItems === 0) {
                            setTimeout(() => {
                                document.getElementById('notificationList').innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-check-circle"></i>
                            <p>No pending logbooks to review</p>
                        </div>
                    `;
                            }, 300);
                        }
                        setTimeout(function () {
    location.reload();
}, 1500); // 2000ms = 2 seconds
                    } else {
                        showToast(data.message || 'Error approving logbook', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Network error. Please try again.', 'error');
                });
        }

        // Helper function to escape HTML
        // function escapeHtml(text) {
        //     const div = document.createElement('div');
        //     div.textContent = text;
        //     return div.innerHTML;
        // }



        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .is-invalid {
        border-color: #dc2626 !important;
    }
    
    .invalid-feedback {
        display: block !important;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
`;
        document.head.appendChild(style);

        // Optional: Clear rejection reason error when typing
        document.addEventListener('DOMContentLoaded', function() {
            const reasonTextarea = document.getElementById('rejectionReason');
            if (reasonTextarea) {
                reasonTextarea.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const errorDiv = document.getElementById('reasonError');
                    if (errorDiv) errorDiv.remove();
                });
            }
        });

        // View logbook details
        // function viewLogbookDetails(logbookId) {
        //     window.open(`../controllers/view_logbook.php?id=${logbookId}`, '_blank', 'width=900,height=700');
        // }
    </script>

    <div class="modal fade" id="equipmentDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>Equipment Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="equipmentDetailsContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Equipment Modal (same as before, but add this hidden input inside the form) -->
    <!-- <div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">

                 
                    <div class="modal-header py-3" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                        <h5 class="modal-title text-white fw-semibold" id="equipmentModalTitle">
                            <i class="bi bi-plus-circle me-2"></i>Add New Equipment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                
                    <div class="modal-body p-4" style="background: #f8fafc;">
                        <form id="addEquipmentForm" enctype="multipart/form-data">
                           
                            <input type="hidden" id="eqId" value="">

                            <div class="row g-3">
                              
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-body p-3">
                                            <h6 class="fw-semibold mb-3" style="color: #22c55e;">
                                                <i class="bi bi-info-circle me-1"></i>Basic Information
                                            </h6>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Equipment Code <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="eqCode"
                                                    placeholder="e.g., MIC-001" required>
                                                <div class="invalid-feedback" id="eqCodeError"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Equipment Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="eqName"
                                                    placeholder="e.g., Microscope" required>
                                                <div class="invalid-feedback" id="eqNameError"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Total Quantity <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="eqQty"
                                                    min="1" value="1" required>
                                                <div class="invalid-feedback" id="eqQtyError"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Simultaneous Users</label>
                                                <input type="number" class="form-control" id="eqSimultaneousUsers"
                                                    min="1" value="1">
                                                <small class="text-muted">Number of users that can use this at once</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                             
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-body p-3">
                                            <h6 class="fw-semibold mb-3" style="color: #22c55e;">
                                                <i class="bi bi-image me-1"></i>Equipment Image
                                            </h6>

                                            <div class="text-center mb-3">
                                                <div class="image-preview-container"
                                                    style="width: 150px; height: 150px; margin: 0 auto; 
                                                    border: 2px dashed #22c55e; border-radius: 10px; 
                                                    display: flex; align-items: center; justify-content: center;
                                                    overflow: hidden; background: #f8f9fa;">
                                                    <img id="eqImagePreview" src="#" alt="Preview"
                                                        style="max-width: 100%; max-height: 100%; display: none;">
                                                    <i id="eqImagePlaceholder" class="bi bi-image"
                                                        style="font-size: 3rem; color: #22c55e;"></i>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <input type="file" class="form-control" id="eqImage"
                                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                                    onchange="previewEquipmentImage(this)">
                                                <small class="text-muted">Max size: 6MB (JPG, PNG, GIF, WEBP)</small>
                                                <div id="currentImageInfo" class="mt-2 small text-muted" style="display: none;"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Sterilization Required</label>
                                                <select class="form-select" id="eqSterilization">
                                                    <option value="NO">No</option>
                                                    <option value="YES">Yes</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Reservation Required</label>
                                                <select class="form-select" id="eqReservation">
                                                    <option value="YES">Yes</option>
                                                    <option value="NO">No</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                              
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <h6 class="fw-semibold mb-3" style="color: #22c55e;">
                                                <i class="bi bi-card-text me-1"></i>Description
                                            </h6>
                                            <textarea class="form-control" id="eqDescription" rows="3"
                                                placeholder="Enter equipment description..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                   
                    <div class="modal-footer py-3 px-4 border-0" style="background: #f8fafc;">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-success px-4" onclick="saveEquipment()"
                            style="background: linear-gradient(135deg, #22c55e, #16a34a); border: none;">
                            <i class="bi bi-check-circle me-1"></i><span id="modalSaveButtonText">Save Equipment</span>
                        </button>
                    </div>
                </div>
            </div>
        </div> -->

    <div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">

                <!-- Header - we'll change the title dynamically -->
                <div class="modal-header py-3" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                    <h5 class="modal-title text-white fw-semibold" id="equipmentModalTitle">
                        <i class="bi bi-plus-circle me-2"></i>Add New Equipment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4" style="background: #f8fafc;">
                    <form id="addEquipmentForm" enctype="multipart/form-data">
                        <!-- Hidden field to store equipment ID for edit mode -->
                        <input type="hidden" id="eqId" value="">

                        <div class="row g-3">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body p-3">
                                        <h6 class="fw-semibold mb-3" style="color: #22c55e;">
                                            <i class="bi bi-info-circle me-1"></i>Basic Information
                                        </h6>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Equipment Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="eqCode"
                                                placeholder="e.g., MIC-001" required>
                                            <div class="invalid-feedback" id="eqCodeError"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Equipment Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="eqName"
                                                placeholder="e.g., Microscope" required>
                                            <div class="invalid-feedback" id="eqNameError"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Total Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="eqQty"
                                                min="1" value="1" required oninput="recalcAvailable()">
                                            <div class="invalid-feedback" id="eqQtyError"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Simultaneous Users</label>
                                            <input type="number" class="form-control" id="eqSimultaneousUsers"
                                                min="1" value="1">
                                            <small class="text-muted">Number of users that can use this at once</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- NEW: Maintenance & Broken Section -->
                                <div class="card border-0 shadow-sm" id="maintenanceSection" style="display:none;">
                                    <div class="card-body p-3">
                                        <h6 class="fw-semibold mb-3" style="color: #f59e0b;">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Status & Maintenance
                                        </h6>

                                        <div class="row g-3">
                                            <!-- Maintenance Quantity -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-tools text-warning me-1"></i>Maintenance Qty
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="eqMaintenanceQty"
                                                        min="0" value="0" placeholder="0" oninput="recalcAvailable()">
                                                    <span class="input-group-text bg-light text-muted">units</span>
                                                </div>
                                                <small class="text-muted">Equipment currently in maintenance</small>
                                            </div>

                                            <!-- Broken Quantity -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-bug text-danger me-1"></i>Broken Qty
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="eqBrokenQty"
                                                        min="0" value="0" placeholder="0" oninput="recalcAvailable()">
                                                    <span class="input-group-text bg-light text-muted">units</span>
                                                </div>
                                                <small class="text-muted">Equipment reported as broken</small>
                                            </div>
                                        </div>

                                        <!-- Available Quantity (Auto-calculated) -->
                                        <div class="mt-3 p-3 bg-light rounded-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-semibold">
                                                    <i class="bi bi-check-circle-fill text-success me-1"></i>Available Quantity:
                                                </span>
                                                <span class="fs-5 fw-bold text-success" id="availableQtyDisplay">1</span>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                Available = Total - Maintenance - Broken
                                            </small>
                                        </div>

                                        <!-- Validation warning -->
                                        <div id="qtyValidationWarning" class="alert alert-warning py-2 px-3 mt-3 small d-none">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            <span id="qtyWarningMessage">Maintenance + Broken cannot exceed Total Quantity</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body p-3">
                                        <h6 class="fw-semibold mb-3" style="color: #22c55e;">
                                            <i class="bi bi-image me-1"></i>Equipment Image
                                        </h6>

                                        <div class="text-center mb-3">
                                            <div class="image-preview-container"
                                                style="width: 150px; height: 150px; margin: 0 auto; 
                                        border: 2px dashed #22c55e; border-radius: 10px; 
                                        display: flex; align-items: center; justify-content: center;
                                        overflow: hidden; background: #f8f9fa;">
                                                <img id="eqImagePreview" src="#" alt="Preview"
                                                    style="max-width: 100%; max-height: 100%; display: none;">
                                                <i id="eqImagePlaceholder" class="bi bi-image"
                                                    style="font-size: 3rem; color: #22c55e;"></i>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <input type="file" class="form-control" id="eqImage"
                                                accept="image/jpeg,image/png,image/gif,image/webp"
                                                onchange="previewEquipmentImage(this)">
                                            <small class="text-muted">Max size: 6MB (JPG, PNG, GIF, WEBP)</small>
                                            <div id="currentImageInfo" class="mt-2 small text-muted" style="display: none;"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Sterilization Required</label>
                                            <select class="form-select" id="eqSterilization">
                                                <option value="NO">No</option>
                                                <option value="YES">Yes</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Reservation Required</label>
                                            <select class="form-select" id="eqReservation">
                                                <option value="YES">Yes</option>
                                                <option value="NO">No</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Lab Location <span class="text-danger">*</span></label>
                                            <select class="form-select" id="eqLabLocation">
                                                <option value="">-- Select Lab Location --</option>
                                                <option value="13">A12-001</option>
                                                <option value="14">A12-004</option>
                                                <option value="15">A12-006</option>
                                                <option value="16">A11-101 (Special Student Lab)</option>
                                                <option value="17">A11-108 (Instrument Lab)</option>
                                                <option value="18">A05-002 (Teaching Lab)</option>
                                            </select>
                                            <div class="invalid-feedback" id="eqLabLocationError"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Full Width Row - Description -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <h6 class="fw-semibold mb-3" style="color: #22c55e;">
                                            <i class="bi bi-card-text me-1"></i>Description
                                        </h6>
                                        <textarea class="form-control" id="eqDescription" rows="3"
                                            placeholder="Enter equipment description..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer - change button text dynamically -->
                <div class="modal-footer py-3 px-4 border-0" style="background: #f8fafc;">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-success px-4" onclick="saveEquipment()"
                        style="background: linear-gradient(135deg, #22c55e, #16a34a); border: none;">
                        <i class="bi bi-check-circle me-1"></i><span id="modalSaveButtonText">Save Equipment</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logbook Details Modal -->
    <div class="modal fade" id="logbookDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-journal-check me-2"></i>Logbook Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-danger" onclick="rejectLogbookFromModal()" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-success" onclick="approveLogbookFromModal()">
                        <i class="bi bi-check-circle me-1"></i>Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectReasonModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle me-2"></i>Reject Logbook
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModalManually('rejectReasonModal')"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Please provide reason for rejection:</label>
                        <textarea id="rejectionReason" class="form-control" rows="4"
                            placeholder="Enter detailed reason why this logbook is being rejected..."></textarea>
                        <small class="text-muted">This reason will be sent to the student.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModalManually('rejectReasonModal')">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmReject()">
                        <i class="bi bi-check-circle"></i> Confirm Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Rejection Reason Modal -->
    <div class="modal fade" id="rejectRequestReasonModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle me-2"></i>Reject Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModalManually('rejectRequestReasonModal')"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Please provide reason for rejection:</label>
                        <textarea id="requestRejectionReason" class="form-control" rows="4"
                            placeholder="Enter detailed reason why this request is being rejected..."></textarea>
                        <small class="text-muted">This reason will be sent to the student.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModalManually('rejectRequestReasonModal')">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmRejectRequest()">
                        <i class="bi bi-check-circle"></i> Confirm Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" id="confirmModalHeader">
                    <h5 class="modal-title" id="confirmModalTitle">
                        <i class="bi bi-question-circle me-2"></i>Confirm Action
                    </h5>
                    <button type="button" class="btn-close" onclick="closeModalManually('confirmModal')"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    Are you sure you want to perform this action?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModalManually('confirmModal')">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn" id="confirmModalBtn">
                        <i class="bi bi-check-circle"></i> Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
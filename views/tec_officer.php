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
$user_query = "SELECT first_name, last_name, img_path FROM lab_user WHERE id = ?";
$user_result = Database::search($user_query, "i", [$technical_officer_id]);
$first_name = 'Technical';
$last_name = 'Officer';
$profile_image = '';
if ($user_result && $user_result->num_rows > 0) {
    $u = $user_result->fetch_assoc();
    $first_name   = $u['first_name'] ?? 'Technical';
    $last_name    = $u['last_name']  ?? 'Officer';
    $profile_image = $u['img_path']   ?? '';
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

// Equipment utilization rate (average usage)
$utilization_q = "SELECT AVG(
                    CASE 
                      WHEN total_qty > 0 
                      THEN ((total_qty - COALESCE((SELECT SUM(book_qty) FROM book_equipment be 
                                                    JOIN reservation r ON be.reservation_id = r.id 
                                                    WHERE be.equipment_id = e.id 
                                                    AND r.request_date <= CURDATE() 
                                                    AND DATE_ADD(r.request_date, INTERVAL (r.continue_days - 1) DAY) >= CURDATE()), 0)) / total_qty) * 100
                      ELSE 0
                    END) as avg_util
                  FROM equipment e";
$util_result = Database::search($utilization_q);
$utilization_rate = ($util_result && $util_result->num_rows > 0) ? round($util_result->fetch_assoc()['avg_util']) : 85;

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
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .btn-view:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
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
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-edit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }

        .btn-remove {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
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
                <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, #22c55e, #16a34a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Technical Officer Dashboard</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-semibold d-none d-sm-block" style="color: #166534;"><?= htmlspecialchars($full_name) ?></span>
                <div class="dropdown">
                    <?php
                    if (empty($profile_image) || !file_exists($profile_image)) {
                        $profile_image = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=100';
                    }
                    ?>
                    <img src="<?= $profile_image ?>" class="profile-img dropdown-toggle" data-bs-toggle="dropdown">
                    <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
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
                    <div class="stat-card">
                        <i class="bi bi-mortarboard-fill"></i>
                        <h3><?= $students_count ?></h3>
                        <p>Students</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-flask"></i>
                        <h3><?= $equipment_count ?></h3>
                        <p>Total Equipment</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-graph-up"></i>
                        <h3><?= $utilization_rate ?>%</h3>
                        <p>Equipment Utilization Rate</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-tools"></i>
                        <h3><?= $maintenance_count ?></h3>
                        <p>Maintenance</p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4 justify-content-center">
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted d-flex justify-content-center align-items-center gap-2">
                                Request Pending
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
                                <button class="btn btn-sm btn-outline-info p-1" onclick="viewTodayPracticals()" style="line-height: 1;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </h6>
                            <h3 class="text-info"><?= $today_count ?></h3>
                        </div>
                    </div>
                </div>

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
                            <div class="events-list" id="eventsList"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Management Section -->
            <div id="equipmentSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment Management</h3>

                <div class="card p-4">
                    <!-- Search and Add Row -->
                    <div class="search-add-row">
                        <div class="search-container">
                            <input type="text" id="equipmentSearch" class="search-input" placeholder="Search by code, name or location...">
                            <button class="search-btn" onclick="searchEquipment()">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                        <button class="add-btn" onclick="addEquipment()">
                            <i class="bi bi-plus-circle"></i> Add Equipment
                        </button>
                    </div>

                    <!-- Equipment Table -->
                    <div class="table-responsive mt-3">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Equipment Code</th>
                                    <th>Name</th>
                                    <th>Active (Available/Total)</th>
                                    <th>Maintenance Pending</th>
                                    <th>Usage %</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="equipmentTableBody">
                                <?php foreach ($equipmentDataTable as $item):
                                    $ratio = $item['available'] / $item['total'];
                                    $badgeColor = '#22c55e';
                                    if ($ratio < 0.3) $badgeColor = '#ef4444';
                                    else if ($ratio < 0.6) $badgeColor = '#f59e0b';
                                ?>
                                    <tr>
                                        <td><img src="<?= $item['image'] ?>" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                        <td><?= htmlspecialchars($item['code']) ?></td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><span class="badge" style="background: <?= $badgeColor ?>; color: white;"><?= $item['available'] ?>/<?= $item['total'] ?></span></td>
                                        <td><span class="badge bg-warning"><?= $item['maintenance'] ?></span></td>
                                        <td>
                                            <div class="progress-bar" style="width: 100px; display: inline-block; margin-right: 10px;">
                                                <div class="progress-fill" style="width: <?= $item['usage'] ?>%"></div>
                                            </div>
                                            <?= $item['usage'] ?>%
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-view" onclick='viewEquipment(<?= json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn-edit" onclick="editEquipment('<?= $item['code'] ?>')" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-remove" onclick="removeEquipment('<?= $item['code'] ?>')" title="Remove">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Equipment Details Modal -->
            <div class="modal fade" id="equipmentDetailsModal" tabindex="-1" aria-hidden="true">
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
                            <!-- Content will be populated by JavaScript -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

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
                            <option value="approved">Checked</option>
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
                                            $statusText = 'Checked';
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
                    <i class="bi bi-check-circle me-2"></i>Submit Checked
                </button>
                <button type="button" class="btn btn-danger" onclick="rejectRequestFromModal()" id="rejectModalBtn" style="display:none;">
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
    
    const reason = prompt('Please enter rejection reason:');
    if (!reason) return;
    
    if (confirm(`Are you sure you want to reject this request?`)) {
        const formData = new FormData();
        formData.append('reservation_id', currentRequestId);
        formData.append('action', 'reject');
        formData.append('reason', reason);
        
        fetch('../controllers/handle_to_approval.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Request rejected successfully!');
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('requestDetailsModal'));
                if (modal) modal.hide();
                
                // Reload and restore section
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error rejecting request');
        });
    }
}

// Submit checked equipment
// Submit checked equipment
// Submit checked equipment
function submitCheckedEquipment() {
    if (!currentRequestId) {
        alert('No reservation selected');
        return;
    }
    
    // Save current section before reload
    saveCurrentSection();
    
    // Get ALL checkboxes
    const allCheckboxes = document.querySelectorAll('#requestDetailsContent input[type="checkbox"]');
    const checkedBoxes = document.querySelectorAll('#requestDetailsContent input[type="checkbox"]:checked');
    
    // Check if ALL checkboxes are checked
    if (allCheckboxes.length === 0) {
        alert('No equipment found to check');
        return;
    }
    
    if (checkedBoxes.length !== allCheckboxes.length) {
        alert(`Please check ALL equipment items (${checkedBoxes.length}/${allCheckboxes.length} checked)`);
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
            alert('Equipment checked successfully!');
            
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
            alert('Error: ' + (data.message || 'Failed to submit'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        alert('Network error. Please try again.');
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


// Add this function
function rejectRequest(id) {
    // Save current section before reload
    saveCurrentSection();
    
    currentRequestId = id;
    const reason = prompt('Please enter rejection reason:');
    if (!reason) return;
    
    if (confirm(`Reject request ${id}?`)) {
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
                alert('Rejected!'); 
                
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
            }
            else alert('Error: ' + data.message);
        });
    }
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
                            <label class="form-check-label" for="eq_${item.id}">Check</label>
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
            const searchTerm = document.getElementById('equipmentSearch').value.toLowerCase();
            const filtered = equipmentDataTable.filter(item =>
                item.code.toLowerCase().includes(searchTerm) ||
                item.name.toLowerCase().includes(searchTerm) ||
                item.location.toLowerCase().includes(searchTerm)
            );
            displayEquipmentTable(filtered);
        }

        function displayEquipmentTable(equipment) {
            const tableBody = document.getElementById('equipmentTableBody');
            if (!tableBody) return;

            tableBody.innerHTML = '';

            equipment.forEach(item => {
                const row = document.createElement('tr');

                const ratio = item.available / item.total;
                let badgeColor = '#22c55e';
                if (ratio < 0.3) badgeColor = '#ef4444';
                else if (ratio < 0.6) badgeColor = '#f59e0b';

                row.innerHTML = `
            <td><img src="${item.image}" style="width: 50px; height: 50px; object-fit: contain;"></td>
            <td>${item.code}</td>
            <td>${item.name}</td>
            <td><span class="badge" style="background: ${badgeColor}; color: white;">${item.available}/${item.total}</span></td>
            <td><span class="badge bg-warning">${item.maintenance}</span></td>
            <td>
                <div class="progress-bar" style="width: 100px; display: inline-block; margin-right: 10px;">
                    <div class="progress-fill" style="width: ${item.usage}%"></div>
                </div>
                ${item.usage}%
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view" onclick='viewEquipment(${JSON.stringify(item)})' title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-edit" onclick="editEquipment('${item.code}')" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn-remove" onclick="removeEquipment('${item.code}')" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
                tableBody.appendChild(row);
            });
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
                            <div class="progress-bar" style="width: 200px;">
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

        function addEquipment() {
            alert('Add Equipment functionality would open a form modal');
        }

        function editEquipment(code) {
            alert('Edit equipment: ' + code);
        }

        function removeEquipment(code) {
            if (confirm(`Are you sure you want to remove equipment ${code}?`)) {
                alert('Equipment removed successfully! (Note: This would update the database)');
            }
        }

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
        let statusText = item.status === 'pending' ? 'Pending' : item.status === 'approved' ? 'Checked' : 'Rejected';
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

            let eventsHtml = "";
            eventsArr.forEach(event => {
                if (event.day === day && event.month === month + 1 && event.year === year) {
                    eventsHtml += `
                <div class="event-item">
                    <div class="title">
                        <i class="fas fa-circle"></i>
                        <span class="event-title">${event.title}</span>
                    </div>
                    <div class="event-time">${event.student} - ${event.location}</div>
                    <div class="event-time" style="margin-left: 28px; font-size: 0.8rem;">${event.duration}</div>
                </div>
            `;
                }
            });

            if (eventsHtml === "") {
                eventsHtml = '<div class="no-event">No events scheduled</div>';
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
                        alert('Invalid date format. Use MM/YYYY');
                    }
                } else {
                    alert('Invalid date format. Use MM/YYYY');
                }
            });
        }

        // ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    initCalendar();
    
    // Check if this is a fresh login (you can set a flag in your PHP session)
    const isFreshLogin = <?php echo isset($_SESSION['fresh_login']) ? 'true' : 'false'; ?>;
    
    if (isFreshLogin) {
        // Clear saved section on fresh login
        sessionStorage.removeItem('lastSection');
        <?php unset($_SESSION['fresh_login']); // Clear the flag ?>
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
});
    </script>
</body>

</html>
<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Get student ID from session
$student_id = $_SESSION["user_id"];

// Get user details
$user_query = "SELECT first_name, last_name, img_path FROM lab_user WHERE id = ?";
$user_result = Database::search($user_query, "i", [$student_id]);

if (!$user_result) {
    error_log("User query failed: " . Database::getLastError());
    $first_name = 'Student';
    $last_name = '';
    $profile_image = '';
} else {
    $user_data = $user_result->fetch_assoc();
    $first_name = $user_data['first_name'] ?? 'Student';
    $last_name = $user_data['last_name'] ?? '';
    $profile_image = $user_data['img_path'] ?? '';
}
$full_name = trim($first_name . ' ' . $last_name);

// Get notification count
$notif_query = "SELECT COUNT(*) as count FROM notification WHERE owner_of_notification = ?";
$notif_result = Database::search($notif_query, "i", [$student_id]);
$notif_count = 0;
if ($notif_result) {
    $notif_data = $notif_result->fetch_assoc();
    $notif_count = $notif_data['count'] ?? 0;
}

// Get recent notifications
// Get recent notifications - FIXED: use created_datetime instead of created_at
$notif_list_query = "SELECT description, created_datetime 
                     FROM notification 
                     WHERE owner_of_notification = ? 
                     ORDER BY created_datetime DESC LIMIT 5";
$notif_list_result = Database::search($notif_list_query, "i", [$student_id]);

// Get student's supervisor
$supervisor_query = "SELECT supervisor_id_or_hod_id FROM supervisor_assigned_student WHERE student_id = ? LIMIT 1";
$supervisor_result = Database::search($supervisor_query, "i", [$student_id]);
$supervisor_id = null;
if ($supervisor_result && $supervisor_result->num_rows > 0) {
    $supervisor_data = $supervisor_result->fetch_assoc();
    $supervisor_id = $supervisor_data['supervisor_id_or_hod_id'];
}

// Get locations for dropdown
$location_query = "SELECT id, location FROM location ORDER BY location";
$location_result = Database::search($location_query);

// Get student's reservations - INCLUDING continue_days
$res_query = "SELECT r.id, r.reservation_id, r.request_date, r.continue_days, l.location, 
                     r.comment, r.created_datetime,
                     CONCAT(s.first_name, ' ', s.last_name) as supervisor_name
              FROM reservation r
              JOIN location l ON r.location_id = l.id
              LEFT JOIN lab_user s ON r.supervisor_id = s.id
              WHERE r.student_id = ?
              ORDER BY r.created_datetime DESC
              LIMIT 5";
$res_result = Database::search($res_query, "i", [$student_id]);

// Fetch student's reservations for calendar - WITH continue_days (now that column exists)
$calendar_events = [];
$calendar_query = "SELECT r.id, r.reservation_id, r.request_date, r.continue_days,
                          l.location,
                          GROUP_CONCAT(CONCAT(e.name, ' (x', be.book_qty, ')') SEPARATOR '<br>') as equipment_list
                   FROM reservation r
                   JOIN location l ON r.location_id = l.id
                   JOIN book_equipment be ON r.id = be.reservation_id
                   JOIN equipment e ON be.equipment_id = e.id
                   WHERE r.student_id = ?
                   GROUP BY r.id
                   ORDER BY r.request_date DESC";

$calendar_result = Database::search($calendar_query, "i", [$student_id]);

if ($calendar_result && $calendar_result->num_rows > 0) {
    while ($row = $calendar_result->fetch_assoc()) {
        $request_date = new DateTime($row['request_date']);
        $end_date = clone $request_date;
        $end_date->modify('+' . ($row['continue_days'] - 1) . ' days');
        
        $calendar_events[] = [
            'day' => (int)$request_date->format('j'),
            'month' => (int)$request_date->format('n'),
            'year' => (int)$request_date->format('Y'),
            'end_day' => (int)$end_date->format('j'),
            'end_month' => (int)$end_date->format('n'),
            'end_year' => (int)$end_date->format('Y'),
            'title' => $row['reservation_id'],
            'equipment' => $row['equipment_list'],
            'location' => $row['location'],
            'duration' => $row['continue_days'] . ' day(s)',
            'time' => 'Full Day Booking'
        ];
    }
}

// Convert to JSON for JavaScript
$calendar_events_json = json_encode($calendar_events);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard - Microbiology Lab</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <style>
        /* Keep ALL your existing CSS styles - they are perfect */
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
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
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

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #206106;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 60px;
            right: 20px;
            width: 350px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1000;
            overflow: hidden;
            animation: slideIn 0.3s ease;
        }

        .notification-dropdown.show {
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-header {
            padding: 20px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h6 {
            margin: 0;
            font-weight: 600;
        }

        .notification-header span {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s;
            cursor: pointer;
        }

        .notification-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .notification-item.unread {
            background: rgba(34, 197, 94, 0.1);
        }

        .notification-item .time {
            font-size: 0.75rem;
            color: #999;
            margin-top: 5px;
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

        /* Equipment Table */
        .equipment-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .equipment-table thead {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
        }

        .equipment-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .equipment-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .equipment-table tbody tr:hover {
            background: #f9f9f9;
        }

        .equipment-image {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .availability-badge {
            background: #e6f7e6;
            color: #22c55e;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
        }

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

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.6);
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

        .badge.bg-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7) !important;
        }

        .badge.bg-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268) !important;
        }

        .badge.bg-info {
            background: linear-gradient(135deg, #0dcaf0, #0aa2c0) !important;
            color: white;
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

        /* Equipment dropdown */
        .position-relative {
            position: relative !important;
        }

        #equipmentDropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
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

        /* Modal */
        .add-event-wrapper {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 450px;
            background: white;
            border-radius: 32px;
            padding: 30px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            z-index: 2000;
            display: none;
        }

        .add-event-wrapper.active {
            display: block;
            animation: modalPop 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalPop {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }

            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .add-event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .add-event-header .title {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .add-event-header .close {
            cursor: pointer;
            font-size: 1.3rem;
            color: #999;
            transition: all 0.3s;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #f5f5f5;
        }

        .add-event-header .close:hover {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            transform: rotate(90deg);
        }

        .add-event-body {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .add-event-body input,
        .add-event-body textarea {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid #f0f0f0;
            border-radius: 18px;
            outline: none;
            transition: all 0.3s;
            font-size: 1rem;
            font-family: inherit;
        }

        .add-event-body textarea {
            min-height: 100px;
            resize: vertical;
        }

        .add-event-body input:focus,
        .add-event-body textarea:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }

        .add-event-footer {
            margin-top: 20px;
        }

        .add-event-footer button {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 18px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3);
        }

        .add-event-footer button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(34, 197, 94, 0.5);
        }

        .no-event {
            text-align: center;
            padding: 50px 20px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 1.1rem;
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

            .equipment-table td {
                display: block;
            }

            .equipment-table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #166534;
                display: block;
                margin-bottom: 5px;
            }
        }

        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .notification-dropdown {
                width: 300px;
                right: 10px;
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

            .equipment-table,
            .equipment-table thead,
            .equipment-table tbody,
            .equipment-table th,
            .equipment-table td,
            .equipment-table tr {
                display: block;
            }

            .equipment-table thead {
                display: none;
            }

            .equipment-table td {
                padding: 10px;
                border: none;
                position: relative;
                padding-left: 50%;
            }

            .equipment-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: 45%;
                font-weight: 600;
                color: #166534;
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
    </style>
    <link rel="icon" type="image/svg+xml" href="../assets/resources/flask.svg">
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <h4><i class="bi bi-flask"></i> MicroLab</h4>
        <a onclick="showSection('dashboard')" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a onclick="showSection('equipment')"><i class="bi bi-tools"></i> Equipment</a>
        <a onclick="showSection('history')"><i class="bi bi-clock-history"></i> Reservation History</a>
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
                <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, #22c55e, #16a34a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Welcome, <span id="userName"><?php echo htmlspecialchars($first_name); ?></span>
                </h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notification Bell -->
                <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge"><?php echo $notif_count; ?></span>
                </div>

                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h6>Notifications</h6>
                        <span><?php echo $notif_count; ?> new</span>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <?php
                      if ($notif_list_result && $notif_list_result->num_rows > 0) {
    while ($notif = $notif_list_result->fetch_assoc()) {
        $time_ago = '';
        $notif_time = strtotime($notif['created_datetime']); // FIXED: use created_datetime
        $time_diff = time() - $notif_time;

        if ($time_diff < 60) {
            $time_ago = 'Just now';
        } elseif ($time_diff < 3600) {
            $minutes = floor($time_diff / 60);
            $time_ago = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($time_diff < 86400) {
            $hours = floor($time_diff / 3600);
            $time_ago = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $days = floor($time_diff / 86400);
            $time_ago = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }

        echo '<div class="notification-item unread">';
        echo '<div><i class="bi bi-info-circle-fill text-success me-2"></i> ' . htmlspecialchars($notif['description']) . '</div>';
        echo '<div class="time">' . $time_ago . '</div>';
        echo '</div>';
    }
} else {
                            echo '<div class="text-center text-muted p-3">No new notifications</div>';
                        }
                        ?>
                    </div>
                </div>

                <span class="fw-semibold d-none d-sm-block" style="color: #166534;" id="userNameDisplay">
                    <?php echo htmlspecialchars($full_name); ?>
                </span>

                <div class="dropdown">
                    <?php
                    if (empty($profile_image) || !file_exists($profile_image)) {
                        $profile_image = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=100';
                    }
                    ?>
                    <img src="<?php echo $profile_image; ?>" class="profile-img dropdown-toggle" data-bs-toggle="dropdown">
                    <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">
            <!-- Dashboard Section -->
            <div id="dashboardSection">
                <!-- Create Reservation Form -->
                <h4 class="mb-3" style="color: white;">Create Equipment Reservation</h4>
                <div class="card p-4 mb-4">
                    <form id="equipmentRequestForm">
                        <!-- Step 1: Basic Information - REMOVED RETURN DATE FIELD -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Step 1:</strong> Select lab location to view available equipment
                                </div>
                            </div>

                            <!-- Lab Location -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-pin-map-fill text-success me-1"></i>Lab Location
                                </label>
                                <select id="labLocation" class="form-select" required onchange="loadEquipmentByLocation()">
                                    <option value="" disabled selected>-- Select Lab Location --</option>
                                    <?php
                                    if ($location_result && $location_result->num_rows > 0) {
                                        while ($row = $location_result->fetch_assoc()) {
                                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['location']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <small class="text-muted">Equipment available only in selected lab</small>
                            </div>

                            <!-- Request Date -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-date text-success me-1"></i>Request Date
                                </label>
                                <input type="date" id="requestDate" class="form-control"
                                    value="<?php echo date('Y-m-d'); ?>"
                                    min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <!-- Duration -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-week text-success me-1"></i>Duration
                                </label>
                                <select id="continueDays" class="form-select" required>
                                    <option value="1" selected>1 Day (Full Day Booking)</option>
                                    <option value="2">2 Days</option>
                                    <option value="3">3 Days</option>
                                </select>
                                <small class="text-muted">Equipment booked for the entire selected day(s)</small>
                            </div>
                        </div>

                        <!-- Step 2: Equipment Selection (unchanged) -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-success">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Step 2:</strong> Search and add equipment to your reservation
                                </div>
                            </div>

                            <!-- Equipment Search -->
                            <div class="col-md-6 position-relative">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-search text-success me-1"></i>Search Equipment
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-microscope"></i>
                                    </span>
                                    <input type="text" id="equipmentSearch" class="form-control"
                                        placeholder="Type to search equipment..."
                                        autocomplete="off" disabled>
                                </div>
                                <div id="equipmentDropdown" class="dropdown-menu w-100 p-2"
                                    style="display: none; max-height: 300px; overflow-y: auto;"></div>
                                <small class="text-muted" id="searchHint">Select a lab location first</small>
                            </div>

                            <!-- Available Quantity -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-box-seam text-success me-1"></i>Available
                                </label>
                                <input type="text" id="availableQty" class="form-control bg-light" readonly placeholder="0">
                            </div>

                            <!-- Book Quantity -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-sort-numeric-up text-success me-1"></i>Book Qty
                                </label>
                                <input type="number" id="bookQty" class="form-control" min="1" value="1" disabled>
                            </div>

                            <!-- Add Button -->
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-success w-100" onclick="addEquipment()" id="addEquipmentBtn" disabled>
                                    <i class="bi bi-plus-circle me-1"></i>Add Item
                                </button>
                            </div>
                        </div>

                        <!-- Selected Equipment Table (unchanged) -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-list-check text-success me-1"></i>Selected Equipment
                            </label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="equipmentTable">
                                    <thead class="table-success">
                                        <tr>
                                            <th width="50%">Equipment Name</th>
                                            <th width="15%">Code</th>
                                            <th width="15%">Quantity</th>
                                            <th width="20%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selectedEquipmentBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                <i class="bi bi-inbox me-2"></i>No equipment added yet
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Additional Comments (unchanged) -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-chat-text text-success me-1"></i>Additional Comments
                            </label>
                            <textarea id="requestComment" class="form-control" rows="2"
                                placeholder="Enter any special requirements..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </button>
                            <button type="button" class="btn btn-success px-4" onclick="submitReservation()" id="submitBtn">
                                <i class="bi bi-send me-1"></i>Submit Reservation
                            </button>
                        </div>
                        
                        <div class="alert alert-warning mt-3 mb-0" id="formWarning" style="display: none;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <span id="warningMessage"></span>
                        </div>
                    </form>
                </div>

           <!-- My Reservation Status Section -->
<h4 class="mb-3" style="color: white;">
    <i class="bi bi-clock-history me-2"></i>My Reservation Status
</h4>
<div class="card p-4 mb-4">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Reservation ID</th>
                    <th>Date(s)</th>
                    <th>Lab Location</th>
                    <th>Equipment</th>
                    <th>Status</th>
                    <th>Supervisor</th>
                    <th>Reason (if rejected)</th>
                </tr>
            </thead>
            <tbody id="reservationStatusBody">
                <?php
                // Update the reservation query to include continue_days
                $res_query = "SELECT r.id, r.reservation_id, r.request_date, r.continue_days, l.location, 
                                     r.comment, r.created_datetime,
                                     CONCAT(s.first_name, ' ', s.last_name) as supervisor_name
                              FROM reservation r
                              JOIN location l ON r.location_id = l.id
                              LEFT JOIN lab_user s ON r.supervisor_id = s.id
                              WHERE r.student_id = ?
                              ORDER BY r.created_datetime DESC
                              LIMIT 5";
                $res_result = Database::search($res_query, "i", [$student_id]);
                
                if ($res_result && $res_result->num_rows > 0) {
                    while ($row = $res_result->fetch_assoc()) {
                        // Get equipment for this reservation
                        $eq_query = "SELECT e.name, be.book_qty 
                                     FROM book_equipment be
                                     JOIN equipment e ON be.equipment_id = e.id
                                     WHERE be.reservation_id = ?";
                        $eq_result = Database::search($eq_query, "i", [$row['id']]);

                        $equipment_list = [];
                        if ($eq_result) {
                            while ($eq = $eq_result->fetch_assoc()) {
                                $equipment_list[] = $eq['name'] . " (x" . $eq['book_qty'] . ")";
                            }
                        }
                        $equipment_display = implode("<br>", $equipment_list);

                        // Calculate date range
                        $start_date = date('Y-m-d', strtotime($row['request_date']));
                        $end_date = date('Y-m-d', strtotime($row['request_date'] . ' + ' . ($row['continue_days'] - 1) . ' days'));
                        $date_display = $start_date;
                        if ($row['continue_days'] > 1) {
                            $date_display .= " to " . $end_date . " (" . $row['continue_days'] . " days)";
                        }

                        // Check if rejected
                        $reject_query = "SELECT reason FROM reject_reason WHERE reservation_id = ?";
                        $reject_result = Database::search($reject_query, "i", [$row['id']]);
                        $reject_reason = '-';
                        if ($reject_result && $reject_result->num_rows > 0) {
                            $reject_data = $reject_result->fetch_assoc();
                            $reject_reason = $reject_data['reason'];
                        }

                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($row['reservation_id']) . "</strong></td>";
                        echo "<td>" . $date_display . "</td>";
                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                        echo "<td>" . $equipment_display . "</td>";
                        echo "<td><span class='badge bg-warning'>Pending</span></td>";
                        echo "<td>" . htmlspecialchars($row['supervisor_name'] ?? 'Not Assigned') . "</td>";
                        echo "<td><small class='text-danger'>" . htmlspecialchars($reject_reason) . "</small></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center text-muted py-3'>No reservations found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

                <!-- Booking Calendar -->
                <h4 class="mb-3" style="color: white;">My Booking Calendar</h4>
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

            <!-- Equipment Section (unchanged) -->
            <div id="equipmentSection" style="display: none;">
                <h3 class="mb-4" style="color: white;">Equipment</h3>
                <div class="card p-4">
                    <div class="search-add-row">
                        <div class="search-container">
                            <input type="text" id="equipmentSearch" class="search-input" placeholder="Search by equipment name...">
                            <button class="search-btn" onclick="searchEquipmentTable()">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="filter-section">
                        <select class="filter-select" id="labFilter" onchange="filterEquipmentTable()">
                            <option value="all">All Labs</option>
                            <?php
                            $labs_for_filter = Database::search("SELECT id, location FROM location ORDER BY location");
                            while ($lab = $labs_for_filter->fetch_assoc()) {
                                echo "<option value='" . $lab['id'] . "'>" . htmlspecialchars($lab['location']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="equipment-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Available</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="equipmentTableBody">
                                <!-- Dynamic content will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reservation History Section (unchanged) -->
            <div id="historySection" style="display: none;">
                <h3 class="mb-4" style="color: white;">Reservation History</h3>
                <div class="card p-4">
                    <div class="filter-section">
                        <select class="filter-select" id="timeFilter" onchange="filterReservations()">
                            <option value="all">All Time</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="equipment-table">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="reservationHistoryBody">
                                <!-- Dynamic content will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals (unchanged) -->
    <div class="modal fade" id="equipmentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Equipment Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="equipmentDetailsContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ============ GLOBAL VARIABLES ============
        let selectedEquipment = [];
        let currentEquipmentId = null;
        let currentEquipmentName = '';
        let currentEquipmentCode = '';
        let currentAvailableQty = 0;

        // Calendar variables
        let activeDay = new Date().getDate();
        let month = new Date().getMonth();
        let year = new Date().getFullYear();

        const months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        // Real events from database (now includes duration)
        let eventsArr = <?php echo $calendar_events_json ?: '[]'; ?>;

        // ============ REMOVED RETURN DATE FUNCTIONS ============
        // The updateReturnDate function and event listeners have been removed

        // ============ EQUIPMENT SEARCH ============
        function loadEquipmentByLocation() {
            const locationId = document.getElementById('labLocation').value;
            const searchInput = document.getElementById('equipmentSearch');
            const bookQty = document.getElementById('bookQty');
            const addBtn = document.getElementById('addEquipmentBtn');
            const searchHint = document.getElementById('searchHint');

            if (!locationId) {
                searchInput.disabled = true;
                bookQty.disabled = true;
                addBtn.disabled = true;
                searchHint.innerText = 'Select a lab location first';
                document.getElementById('equipmentDropdown').style.display = 'none';
                return;
            }

            searchInput.disabled = false;
            searchInput.value = '';
            searchInput.placeholder = 'Type to search equipment...';
            searchHint.innerText = 'Start typing to search equipment in this lab';
        }

        document.getElementById('equipmentSearch')?.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            const locationId = document.getElementById('labLocation').value;
            const dropdown = document.getElementById('equipmentDropdown');

            if (!locationId) {
                alert('Please select a lab location first');
                this.value = '';
                return;
            }

            if (searchTerm.length < 2) {
                dropdown.style.display = 'none';
                return;
            }

            // AJAX call to search equipment
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `search_equipment.php?location_id=${locationId}&term=${encodeURIComponent(searchTerm)}`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const equipment = JSON.parse(xhr.responseText);
                        displayEquipmentDropdown(equipment);
                    } catch (e) {
                        console.error('Parse error:', e);
                    }
                }
            };
            xhr.send();
        });

        function displayEquipmentDropdown(equipment) {
            const dropdown = document.getElementById('equipmentDropdown');
            dropdown.innerHTML = '';

            if (equipment.length === 0) {
                dropdown.innerHTML = '<div class="text-muted p-2">No equipment found</div>';
                dropdown.style.display = 'block';
                return;
            }

            equipment.forEach(item => {
                const div = document.createElement('div');
                div.className = 'dropdown-item p-2 border-bottom';
                div.style.cursor = 'pointer';
                div.onclick = () => selectEquipment(item);
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small class="text-muted">Code: ${item.code}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success">Available: ${item.available_qty}</span>
                        </div>
                    </div>
                `;
                dropdown.appendChild(div);
            });

            dropdown.style.display = 'block';
        }

        function selectEquipment(item) {
            currentEquipmentId = item.id;
            currentEquipmentName = item.name;
            currentEquipmentCode = item.code;
            currentAvailableQty = item.available_qty;

            document.getElementById('equipmentSearch').value = item.name + ' (' + item.code + ')';
            document.getElementById('availableQty').value = item.available_qty;
            document.getElementById('bookQty').disabled = false;
            document.getElementById('bookQty').max = item.available_qty;
            document.getElementById('bookQty').value = 1;
            document.getElementById('addEquipmentBtn').disabled = false;
            document.getElementById('equipmentDropdown').style.display = 'none';
        }

        // ============ ADD/REMOVE EQUIPMENT ============
        function addEquipment() {
            const qty = parseInt(document.getElementById('bookQty').value);

            if (!currentEquipmentId) {
                alert('Please select equipment first');
                return;
            }

            if (qty < 1 || qty > currentAvailableQty) {
                alert(`Quantity must be between 1 and ${currentAvailableQty}`);
                return;
            }

            const existing = selectedEquipment.find(e => e.id === currentEquipmentId);
            if (existing) {
                if (existing.qty + qty > currentAvailableQty) {
                    alert(`Total quantity would exceed available (${currentAvailableQty})`);
                    return;
                }
                existing.qty += qty;
            } else {
                selectedEquipment.push({
                    id: currentEquipmentId,
                    name: currentEquipmentName,
                    code: currentEquipmentCode,
                    qty: qty
                });
            }

            updateEquipmentTable();
            resetSelection();
        }

        function updateEquipmentTable() {
            const tbody = document.getElementById('selectedEquipmentBody');

            if (selectedEquipment.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3"><i class="bi bi-inbox me-2"></i>No equipment added yet</td></tr>';
                return;
            }

            let html = '';
            selectedEquipment.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.code || '-'}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm" 
                                   value="${item.qty}" min="1" 
                                   onchange="updateQuantity(${index}, this.value)"
                                   style="width: 80px;">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEquipment(${index})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function updateQuantity(index, newQty) {
            if (newQty < 1) {
                alert('Quantity must be at least 1');
                updateEquipmentTable();
                return;
            }
            selectedEquipment[index].qty = parseInt(newQty);
        }

        function removeEquipment(index) {
            selectedEquipment.splice(index, 1);
            updateEquipmentTable();
        }

        function resetSelection() {
            currentEquipmentId = null;
            currentEquipmentName = '';
            currentEquipmentCode = '';
            currentAvailableQty = 0;
            document.getElementById('equipmentSearch').value = '';
            document.getElementById('availableQty').value = '';
            document.getElementById('bookQty').disabled = true;
            document.getElementById('bookQty').value = 1;
            document.getElementById('addEquipmentBtn').disabled = true;
        }

        function resetForm() {
            if (selectedEquipment.length > 0 && !confirm('Are you sure you want to reset? All selected equipment will be cleared.')) {
                return;
            }

            document.getElementById('labLocation').value = '';
            document.getElementById('requestDate').value = '<?php echo date('Y-m-d'); ?>';
            document.getElementById('continueDays').value = '1';
            document.getElementById('requestComment').value = '';

            selectedEquipment = [];
            updateEquipmentTable();
            resetSelection();
            document.getElementById('equipmentSearch').disabled = true;
            document.getElementById('formWarning').style.display = 'none';
        }

        function submitReservation() {
            const locationId = document.getElementById('labLocation').value;
            const requestDate = document.getElementById('requestDate').value;

            if (!locationId) {
                showWarning('Please select a lab location');
                return;
            }

            if (!requestDate) {
                showWarning('Please select a request date');
                return;
            }

            if (selectedEquipment.length === 0) {
                showWarning('Please add at least one equipment item');
                return;
            }

            const formData = new FormData();
            formData.append('location_id', locationId);
            formData.append('request_date', requestDate);
            formData.append('continue_days', document.getElementById('continueDays').value);
            formData.append('comment', document.getElementById('requestComment').value);
            formData.append('equipment', JSON.stringify(selectedEquipment));

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_reservation.php', true);
            xhr.onload = function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send me-1"></i>Submit Reservation';

                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert('Reservation submitted successfully!');
                            resetForm();
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } catch (e) {
                        alert('Server error occurred');
                    }
                }
            };
            xhr.send(formData);
        }

        function showWarning(message) {
            document.getElementById('warningMessage').innerText = message;
            document.getElementById('formWarning').style.display = 'block';
            setTimeout(() => {
                document.getElementById('formWarning').style.display = 'none';
            }, 5000);
        }

        // ============ NOTIFICATION FUNCTIONS ============
        function toggleNotifications() {
            document.getElementById("notificationDropdown")?.classList.toggle("show");
            if (document.getElementById("notificationDropdown").classList.contains("show")) {
                markNotificationsAsRead();
            }
        }

        function markNotificationsAsRead() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'mark_notifications_read.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('notificationBadge').textContent = '0';
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.remove('unread');
                    });
                }
            };
            xhr.send('student_id=<?php echo $student_id; ?>');
        }

        // ============ CALENDAR FUNCTIONS ============
        function initCalendar() {
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const prevLastDay = new Date(year, month, 0);
            const prevDays = prevLastDay.getDate();
            const lastDate = lastDay.getDate();
            const day = firstDay.getDay();
            const nextDays = 7 - lastDay.getDay() - 1;

            document.getElementById('displayMonth').innerHTML = months[month] + " " + year;

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
                    if (event.year === year && event.month === month + 1) {
                        if (i >= event.day && i <= event.end_day) {
                            hasEvent = true;
                        }
                    }
                });

                let classes = "day-cell";
                if (i === new Date().getDate() && year === new Date().getFullYear() && month === new Date().getMonth()) {
                    classes += " today";
                }
                if (hasEvent) {
                    classes += " event";
                }
                if (i === activeDay) {
                    classes += " active";
                }

                days += `<div class="${classes}" data-day="${i}">${i}</div>`;
            }

            for (let j = 1; j <= nextDays; j++) {
                days += `<div class="day-cell next-date">${j}</div>`;
            }

            document.getElementById('daysGrid').innerHTML = days;
            attachDayClickHandlers();
            updateEventDisplay(activeDay);
        }

        function attachDayClickHandlers() {
            document.querySelectorAll('.day-cell').forEach(cell => {
                cell.addEventListener('click', function() {
                    if (!this.classList.contains('prev-date') && !this.classList.contains('next-date')) {
                        document.querySelectorAll('.day-cell').forEach(c => c.classList.remove('active'));
                        this.classList.add('active');
                        activeDay = parseInt(this.textContent);
                        updateEventDisplay(activeDay);
                    }
                });
            });
        }

      function updateEventDisplay(day) {
    const date = new Date(year, month, day);
    document.getElementById('eventDay').innerHTML = date.toString().split(' ')[0];
    document.getElementById('eventDate').innerHTML = `${day} ${months[month]} ${year}`;

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
                dateRange = `<div class="event-date-range" style="font-size:0.8rem; color: #ffd700;">
                    ${event.day} ${months[event.month-1]} - ${event.end_day} ${months[event.end_month-1]} ${event.end_year}
                </div>`;
            }
            
            eventsHtml += `
                <div class="event-item" onclick="viewBookingDetails('${event.title}')">
                    <div class="title">
                        <i class="fas fa-circle"></i>
                        <span class="event-title">${event.title}</span>
                    </div>
                    ${dateRange}
                    <div class="event-time">${event.time} (${event.duration})</div>
                    <div class="event-details">
                        <i class="bi bi-pin-map-fill" style="color: #ffd700;"></i> ${event.location}<br>
                        <i class="bi bi-tools" style="color: #ffd700;"></i> ${event.equipment}
                    </div>
                </div>
            `;
        });
    } else {
        eventsHtml = '<div class="no-event">No bookings scheduled for this day</div>';
    }

    document.getElementById('eventsList').innerHTML = eventsHtml;
}

        function viewBookingDetails(reservationId) {
            alert('Viewing details for: ' + reservationId);
        }

        // ============ CALENDAR NAVIGATION ============
        document.querySelector('.prev')?.addEventListener('click', () => {
            month--;
            if (month < 0) {
                month = 11;
                year--;
            }
            initCalendar();
        });

        document.querySelector('.next')?.addEventListener('click', () => {
            month++;
            if (month > 11) {
                month = 0;
                year++;
            }
            initCalendar();
        });

        document.getElementById('todayBtn')?.addEventListener('click', () => {
            const today = new Date();
            month = today.getMonth();
            year = today.getFullYear();
            activeDay = today.getDate();
            initCalendar();
        });

        document.getElementById('gotoBtn')?.addEventListener('click', () => {
            const input = document.getElementById('gotoInput').value;
            const parts = input.split('/');
            if (parts.length === 2) {
                const m = parseInt(parts[0]) - 1;
                const y = parseInt(parts[1]);
                if (m >= 0 && m < 12 && y >= 2000 && y <= 2100) {
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

        // ============ SIDEBAR FUNCTIONS ============
        function toggleSidebar() {
            document.getElementById("sidebar")?.classList.toggle("active");
            document.getElementById("sidebarOverlay")?.classList.toggle("active");
        }

        function showSection(section) {
            document.getElementById('dashboardSection').style.display = 'none';
            document.getElementById('equipmentSection').style.display = 'none';
            document.getElementById('historySection').style.display = 'none';
            document.getElementById(section + 'Section').style.display = 'block';

            document.querySelectorAll('.sidebar a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('onclick')?.includes(section)) {
                    link.classList.add('active');
                }
            });

            if (section === 'history') {
                loadReservationHistory();
            } else if (section === 'equipment') {
                loadEquipmentList();
            }
        }

        function loadEquipmentList() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_equipment_list.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('equipmentTableBody').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        function loadReservationHistory() {
            const filter = document.getElementById('timeFilter').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_reservation_history.php?filter=' + filter, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('reservationHistoryBody').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        function filterReservations() {
            loadReservationHistory();
        }

        function filterEquipmentTable() {
            const labId = document.getElementById('labFilter').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_equipment_list.php?lab_id=' + labId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('equipmentTableBody').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

      function searchEquipmentTable() {
    const searchTerm = document.getElementById('equipmentSearch').value;
    const labId = document.getElementById('labFilter').value;
    
    // Add loading indicator
    document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-success"></div></td></tr>';
    
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'search_equipment_table.php?term=' + encodeURIComponent(searchTerm) + '&lab_id=' + labId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('equipmentTableBody').innerHTML = xhr.responseText;
        } else {
            document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading equipment</td></tr>';
        }
    };
    xhr.onerror = function() {
        document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Network error</td></tr>';
    };
    xhr.send();
}

function filterEquipmentTable() {
    const labId = document.getElementById('labFilter').value;
    
    // Clear search input when filtering
    document.getElementById('equipmentSearch').value = '';
    
    // Add loading indicator
    document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-success"></div></td></tr>';
    
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_equipment_list.php?lab_id=' + labId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('equipmentTableBody').innerHTML = xhr.responseText;
        } else {
            document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading equipment</td></tr>';
        }
    };
    xhr.send();
}

        function viewEquipmentDetails(id) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_equipment_details.php?id=' + id, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('equipmentDetailsContent').innerHTML = xhr.responseText;
                    new bootstrap.Modal(document.getElementById('equipmentDetailsModal')).show();
                }
            };
            xhr.send();
        }

        // Close notifications when clicking outside
        document.addEventListener('click', function(event) {
            const bell = document.querySelector('.notification-bell');
            const dropdown = document.getElementById('notificationDropdown');
            if (bell && dropdown && !bell.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }

            if (!event.target.closest('#equipmentSearch') && !event.target.closest('#equipmentDropdown')) {
                document.getElementById('equipmentDropdown').style.display = 'none';
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initCalendar();
            showSection('dashboard');
        });
    </script>
</body>
</html>
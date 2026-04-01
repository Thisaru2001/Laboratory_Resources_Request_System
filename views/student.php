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
$user_query = "SELECT id, first_name, last_name, email, mobile, university_id, img_path, join_datetime FROM lab_user WHERE id = ?";
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

// Load .env file and get OpenRouter API key
$env_file_path = __DIR__ . '/../.env';
$env = @parse_ini_file($env_file_path);
$openrouter_api_key = '';

if ($env === false) {
    error_log("WARNING: .env file not found at: " . $env_file_path);
} else if (!isset($env['OPENROUTER_API_KEY']) || empty($env['OPENROUTER_API_KEY'])) {
    error_log("WARNING: OPENROUTER_API_KEY not found in .env file");
} else {
    $openrouter_api_key = $env['OPENROUTER_API_KEY'];
    error_log("API KEY LOADED: " . substr($openrouter_api_key, 0, 10) . "...");
}


// Get notification count - only unread
$notif_query = "SELECT COUNT(*) as count FROM notification WHERE owner_of_notification = ? AND status = 'unread'";
$notif_result = Database::search($notif_query, "i", [$student_id]);
$notif_count = 0;
if ($notif_result) {
    $notif_data = $notif_result->fetch_assoc();
    $notif_count = $notif_data['count'] ?? 0;
}

// Get recent notifications
// Get recent notifications - FIXED: use created_datetime instead of created_at, AND status = 'unread' to show only unread
$notif_list_query = "SELECT description, created_datetime 
                     FROM notification 
                     WHERE owner_of_notification = ? AND status = 'unread'
                     ORDER BY created_datetime DESC LIMIT 5";
$notif_list_result = Database::search($notif_list_query, "i", [$student_id]);

// Get student's supervisor  
$supervisor_query = "SELECT supervisor_id_or_hod_id FROM supervisor_assigned_student WHERE student_id = ? LIMIT 1";
$supervisor_result = Database::search($supervisor_query, "i", [$student_id]);
$supervisor_id = null;
$supervisor_name = '';
if ($supervisor_result && $supervisor_result->num_rows > 0) {
    $supervisor_data = $supervisor_result->fetch_assoc();
    $supervisor_id = $supervisor_data['supervisor_id_or_hod_id'];
    
    // Get supervisor's name
    $sup_name_query = "SELECT first_name, last_name FROM lab_user WHERE id = ?";
    $sup_name_result = Database::search($sup_name_query, "i", [$supervisor_id]);
    if ($sup_name_result && $sup_name_result->num_rows > 0) {
        $sup_name_data = $sup_name_result->fetch_assoc();
        $supervisor_name = trim($sup_name_data['first_name'] . ' ' . $sup_name_data['last_name']);
    }
}

// Get locations for dropdown
$location_query = "SELECT id, location 
FROM location 
WHERE is_room = 0 
ORDER BY location;";
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
            display: flex;
            flex-direction: column;
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
            margin-top: auto;
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
            background: #e10101;
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
            border-radius: 8px;
            background: #f8f9fa;
            padding: 5px;
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

        /* LabBot Chatbot Styles */
        #chatMessages {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        .chat-message-user {
            display: flex;
            justify-content: flex-end;
            animation: slideIn 0.3s ease-out;
        }

        .chat-message-user > div {
            background: #10b981;
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            max-width: 70%;
            word-wrap: break-word;
        }

        .chat-message-bot {
            display: flex;
            justify-content: flex-start;
            animation: slideIn 0.3s ease-out;
        }

        .chat-message-bot > div {
            background: #334155;
            color: #e2e8f0;
            padding: 12px 16px;
            border-radius: 12px;
            max-width: 70%;
            word-wrap: break-word;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-loading {
            display: flex;
            gap: 6px;
            padding: 12px 16px;
        }

        .chat-loading span {
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            animation: pulse 1.4s infinite;
        }

        .chat-loading span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .chat-loading span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes pulse {
            0%, 60%, 100% {
                opacity: 0.5;
            }
            30% {
                opacity: 1;
            }
        }

        @keyframes blink {
            0%, 48%, 100% {
                opacity: 1;
            }
            50%, 98% {
                opacity: 0;
            }
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

        @keyframes lbdot {
    0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
    30% { opacity: 1; transform: translateY(-3px); }
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
        <a onclick="showSection('labbot')"> <i class="bi bi-robot" style="color: white; font-size: 16px;"></i> Lab AI</a>
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
                <div class="notification-bell" onclick="toggleNotificationDropdown()">
                    <i class="fas fa-bell"></i>
                    <?php
                    // Count unread notifications using the existing $student_id variable
                    $unread_query = "SELECT COUNT(*) as unread_count 
                     FROM notification 
                     WHERE owner_of_notification = ? AND status = 'unread'";
                    $unread_result = Database::search($unread_query, "i", [$student_id]);

                    if ($unread_result) {
                        $unread_data = $unread_result->fetch_assoc();
                        $notif_count = $unread_data['unread_count'] ?? 0;
                    } else {
                        $notif_count = 0;
                    }

                    // Only show badge if count is greater than 0
                    if ($notif_count > 0):
                    ?>
                        <span class="notification-badge" id="notificationBadge">
                            <?php echo $notif_count; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h6>Notifications</h6>
                        <!-- <span><?php echo $notif_count; ?> new</span> -->
                    </div>
                    <div class="notification-list" id="notificationList">
                        <?php
                        // Get notifications for specific student - only unread
                        $notif_query = "SELECT id, description, created_datetime, status 
                        FROM notification 
                        WHERE owner_of_notification = ? AND status = 'unread'
                        ORDER BY created_datetime DESC 
                        LIMIT 5";

                        $notif_list_result = Database::search($notif_query, "i", [$student_id]);

                        if ($notif_list_result && $notif_list_result->num_rows > 0) {
                            while ($notif = $notif_list_result->fetch_assoc()) {
                                // Calculate time ago
                                $time_ago = '';
                                $notif_time = strtotime($notif['created_datetime']);
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

                                // Add unread class only if status is unread
                                $unread_class = ($notif['status'] == 'unread') ? 'unread' : '';

                                echo '<div class="notification-item ' . $unread_class . '" onclick="markNotificationRead(' . $notif['id'] . ')">';
                                echo '<div><i class="bi bi-info-circle-fill text-success me-2"></i> ' . htmlspecialchars($notif['description']) . '</div>';
                                echo '<div class="time">' . $time_ago . '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="text-center text-muted p-3">No notifications</div>';
                        }
                        ?>
                    </div>
                </div>


                <span class="fw-semibold d-none d-sm-block" style="color: #166534;" id="userNameDisplay">
                    <?php echo htmlspecialchars($full_name); ?>
                </span>

                <div class="dropdown">
                    <?php
                    // $profile_image = $_SESSION['user']['img_path'] ?? $_SESSION['img_path'] ?? '';
error_log("Profile image path: " . $profile_image);
                    if (!empty($profile_image)) {
                         // Clean the path (remove any leading slashes and fix backslashes)
                            $clean_path = str_replace('\\', '/', $profile_image);
                            $clean_path = ltrim($clean_path, '/');

                            // Remove any 'LRRS/' prefix if it exists (just in case)
                            $clean_path = preg_replace('/^LRRS\//', '', $clean_path);

                            // Use relative path from /views/ to /assets/
                            // This works on both localhost (/LRRS/...) and online server (/...)
                            $image_url = '../' . $clean_path;

                            // Try multiple possible locations for the file
                            $possible_paths = [
                                $_SERVER['DOCUMENT_ROOT'] . '/LRRS/' . $clean_path,  // Localhost
                                $_SERVER['DOCUMENT_ROOT'] . '/' . $clean_path,       // Online server
                            ];

                            $file_found = false;
                            foreach ($possible_paths as $path) {
                                if (file_exists($path)) {
                                    $file_found = true;
                                    error_log("Image found at: " . $path);
                                    break;
                                }
                            }
                        
                            // If not found, try alternative path (without assets/ prefix)
                            if (!$file_found) {
                                $filename = basename($clean_path);
                                $alt_path = 'assets/profile_images/' . $filename;
                                
                                $alt_possible_paths = [
                                    $_SERVER['DOCUMENT_ROOT'] . '/LRRS/' . $alt_path,  // Localhost
                                    $_SERVER['DOCUMENT_ROOT'] . '/' . $alt_path,       // Online server
                                ];

                                foreach ($alt_possible_paths as $path) {
                                    if (file_exists($path)) {
                                        $image_url = '../' . $alt_path;
                                        $file_found = true;
                                        error_log("Image found at alternative path: " . $path);
                                        break;
                                    }
                                }
                            }

                            // If still not found, use avatar
                            if (!$file_found) {
                                error_log("Image not found at any location. Checked: " . implode(", ", $possible_paths));
                                $image_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=22c55e&color=fff&size=100";
                            }
                    } else {
                        $image_url = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=100';
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($image_url); ?>" class="profile-img dropdown-toggle" data-bs-toggle="dropdown">
                    <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <li><a class="dropdown-item" href="#" onclick="openProfileModal(event)" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Profile Modal -->
        <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; border: none;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border: none; border-radius: 20px 20px 0 0;">
                        <h5 class="modal-title fw-bold" id="profileModalLabel"><i class="bi bi-person-circle me-2"></i>My Profile</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="profileForm">
                            <div class="row g-3">
                                <!-- Profile Image -->
                                <div class="col-12 text-center mb-3">
                                    <div style="position: relative; width: 120px; margin: 0 auto;">
                                        <img id="profileImagePreview" src="<?php echo htmlspecialchars($image_url); ?>" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #22c55e; cursor: pointer;" onclick="document.getElementById('profileImageInput').click();">
                                        <input type="file" id="profileImageInput" style="display: none;" accept="image/*" onchange="previewImage(event)">
                                        <small class="text-muted d-block mt-2">Click image to change</small>
                                    </div>
                                </div>

                                <!-- First Name -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold"><i class="bi bi-person text-success me-1"></i>First Name</label>
                                    <input type="text" id="firstName" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
                                </div>

                                <!-- Last Name -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold"><i class="bi bi-person text-success me-1"></i>Last Name</label>
                                    <input type="text" id="lastName" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
                                </div>

                                <!-- University ID -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold"><i class="bi bi-card-text text-success me-1"></i>University ID</label>
                                    <input type="text" id="universityId" class="form-control" value="<?php echo htmlspecialchars($user_data['university_id'] ?? ''); ?>">
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold"><i class="bi bi-envelope text-success me-1"></i>Email</label>
                                    <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                                </div>

                                <!-- Mobile -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold"><i class="bi bi-telephone text-success me-1"></i>Mobile</label>
                                    <input type="tel" id="mobile" class="form-control" value="<?php echo htmlspecialchars($user_data['mobile'] ?? ''); ?>">
                                </div>

                                <!-- Join Date (Read-only) -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold"><i class="bi bi-calendar text-success me-1"></i>Joined Date</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo date('Y-m-d', strtotime($user_data['join_datetime'] ?? '')); ?>" disabled>
                                </div>

                                <!-- Supervisor -->
                                <!-- <div class="col-md-6">
                                    <label class="form-label fw-semibold"><i class="bi bi-person-badge text-success me-1"></i>Supervisor</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($supervisor_name ?: 'Not assigned'); ?>" disabled>
                                </div> -->

                                <!-- Supervisor Name (Read-only) -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold"><i class="bi bi-person-check text-success me-1"></i>Supervisor Name</label>
                                    <input type="text" id="supervisorName" class="form-control bg-light" value="<?php 
                                        if ($supervisor_id) {
                                            $sup_query = "SELECT CONCAT(first_name, ' ', last_name) as name FROM lab_user WHERE id = ?";
                                            $sup_result = Database::search($sup_query, "i", [$supervisor_id]);
                                            if ($sup_result && $sup_result->num_rows > 0) {
                                                echo htmlspecialchars($sup_result->fetch_assoc()['name']);
                                            }
                                        }
                                    ?>" disabled>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e0e0e0;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="saveProfile()" style="background: linear-gradient(135deg, #22c55e, #16a34a); border: none;">
                            <i class="bi bi-check-circle me-1"></i>Save Changes
                        </button>
                    </div>
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
                                <select id="labLocation" class="form-select" required onchange="loadEquipment()">
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

                            <!-- Equipment Search Input -->
                            <div class="col-md-6 position-relative">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-search text-success me-1"></i>Search Equipment
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-microscope"></i>
                                    </span>
                                    <input type="text" id="equipmentSearch" class="form-control"
                                        placeholder="Select a lab location first"
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

                            <!-- Book Quantity   assets\equipment\1.jpg C:\xampp\htdocs\LRRS\assets\equipment\1.jpg-->
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

                                    <th>Status</th>

                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="reservationStatusBody">
                                <?php
                                // Update the reservation query to include continue_days and status
                                $res_query = "SELECT r.id, r.reservation_id, r.request_date, r.continue_days, l.location, 
                         r.comment, r.created_datetime,
                         CONCAT(s.first_name, ' ', s.last_name) as supervisor_name,
                         r.supervisor_id,
                         r.technical_officer_id,
                         CASE 
                            WHEN rr.id IS NOT NULL THEN 'rejected'
                            WHEN r.technical_officer_id IS NOT NULL THEN 'to_checked'
                            WHEN r.supervisor_id IS NOT NULL THEN 'to_pending'
                            ELSE 'pending'
                         END as status,
                         rr.reason as reject_reason
                  FROM reservation r
                  JOIN location l ON r.location_id = l.id
                  LEFT JOIN lab_user s ON r.supervisor_id = s.id
                  LEFT JOIN reject_reason rr ON r.id = rr.reservation_id
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

                                        // Determine status badge based on status
                                        $status_badge = '';
                                        $status_text = '';

                                        switch ($row['status']) {
                                            case 'pending':
                                                $status_badge = 'bg-warning';
                                                $status_text = 'Pending';
                                                break;
                                            case 'to_pending':
                                                $status_badge = 'bg-info';
                                                $status_text = 'To Pending';
                                                break;
                                            case 'to_checked':
                                                $status_badge = 'bg-success';
                                                $status_text = 'Approve';
                                                break;
                                            case 'rejected':
                                                $status_badge = 'bg-danger';
                                                $status_text = 'Rejected';
                                                break;
                                            default:
                                                $status_badge = 'bg-secondary';
                                                $status_text = 'Unknown';
                                        }

                                        echo "<tr>";
                                        echo "<td><strong>" . htmlspecialchars($row['reservation_id']) . "</strong></td>";
                                        echo "<td>" . $date_display . "</td>";
                                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                        echo "<td><span class='badge " . $status_badge . "'>" . $status_text . "</span></td>";

                                        // Action buttons column
                                        echo "<td>";
                                        echo "<div class='action-buttons'>";

                                        // View button for all statuses
                                        echo "<button class='btn-view me-2' onclick='viewReservation(\"" . htmlspecialchars($row['id']) . "\")' title='View Details'>";
                                        echo "<i class='bi bi-eye'></i>";
                                        echo "</button>";

                                        // Remove button only for Pending status
                                        if ($row['status'] === 'pending') {
                                            echo "<button class='btn btn-danger btn-sm' onclick='removeReservation(\"" . htmlspecialchars($row['reservation_id']) . "\")' title='Remove Reservation' style='padding: 2px 8px; font-size: 0.7rem; background: linear-gradient(135deg, #dc3545, #c82333); border: none;'>";
                                            echo "<i class='bi bi-trash'></i>";
                                            echo "</button>";
                                        }

                                        echo "</div>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center text-muted py-3'>No reservations found</td></tr>";
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
                            <input type="text" id="equipmentSearch1" class="search-input" placeholder="Search by equipment name...">

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
                    </div>



                    <div class="table-responsive">
                        <table class="equipment-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <!-- <th>Available</th> -->
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
















          <!-- LabBot AI Chatbot Section -->
<div id="labbotSection" style="display: none;">
   <h5 class="mb-3" style="color: white; font-style: italic;">AI Assistant</h5>
<div style="display: flex; flex-direction: column; height: 560px; background: #0f172a; border-radius: 20px; overflow: hidden; border: 1px solid #1e293b; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">

        <!-- Header -->
        <div style="background: #1e293b; padding: 14px 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #334155; flex-shrink: 0;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="bi bi-robot" style="color: white; font-size: 16px;"></i>
            </div>
            <div>
                <div style="color: #f1f5f9; font-weight: 600; font-size: 14px;">LabBot</div>
                <div style="color: #10b981; font-size: 11px; display: flex; align-items: center; gap: 4px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                    Online · AI Assistant
                </div>
            </div>
            <div style="margin-left: auto; color: #475569; font-size: 11px;">Microbiology Lab</div>
        </div>

        <!-- Messages -->
        <div id="chatMessages" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; scroll-behavior: smooth; scrollbar-width: thin; scrollbar-color: #334155 transparent;">
            <!-- Welcome message -->
            <div style="display: flex; gap: 8px; align-items: flex-end;">
                <div style="width: 28px; height: 28px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; flex-shrink: 0;"><i class="bi bi-robot" style="color: white; font-size: 16px;"></i></div>
                <div style="max-width: 72%; background: #1e293b; color: #cbd5e1; padding: 10px 14px; border-radius: 16px; border-bottom-left-radius: 4px; font-size: 13px; line-height: 1.5;">
                    <div style="font-weight: 600; font-size: 11px; margin-bottom: 4px; opacity: 0.6;">LabBot</div>
                    Hello! I'm LabBot, your AI assistant for the Microbiology Department. How can I help you today?
                </div>
            </div>
        </div>

        <!-- Quick chips -->
        <!-- <div id="labbotChips" style="display: flex; gap: 6px; flex-wrap: wrap; padding: 0 16px 10px; flex-shrink: 0;">
            <span onclick="sendChip('How do I make a reservation?')" style="background: #1e293b; border: 1px solid #334155; color: #94a3b8; font-size: 11px; padding: 5px 10px; border-radius: 20px; cursor: pointer;">How do I make a reservation?</span>
            <span onclick="sendChip('What equipment is available?')" style="background: #1e293b; border: 1px solid #334155; color: #94a3b8; font-size: 11px; padding: 5px 10px; border-radius: 20px; cursor: pointer;">Available equipment</span>
            <span onclick="sendChip('Lab safety protocols')" style="background: #1e293b; border: 1px solid #334155; color: #94a3b8; font-size: 11px; padding: 5px 10px; border-radius: 20px; cursor: pointer;">Safety protocols</span>
        </div> -->

        <!-- Input -->
        <div style="display: flex; gap: 8px; padding: 12px 16px; background: #1e293b; border-top: 1px solid #334155; flex-shrink: 0; align-items: center;">
            <input type="text" id="labbotInput" placeholder="Type a message..."
                style="flex: 1; background: #0f172a; border: 1px solid #334155; color: #f1f5f9; border-radius: 24px; padding: 9px 16px; font-size: 13px; outline: none;"
                onkeypress="if(event.key==='Enter') sendLabbotMessage()"
                onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#334155'">
            <button onclick="sendLabbotMessage()" id="labbotSendBtn"
                style="width: 36px; height: 36px; border-radius: 50%; background: #10b981; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;">
                <i class="bi bi-send-fill" style="color: white; font-size: 13px;"></i>
            </button>
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
    <!-- <div class="modal fade" id="equipmentDetailsModal" tabindex="-1">
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
    </div> -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>



// ========== LabBot AI Chatbot Functions ==========
const OPENROUTER_API_KEY = '<?php echo htmlspecialchars($openrouter_api_key, ENT_QUOTES, 'UTF-8'); ?>';
const OPENROUTER_API_URL = 'https://openrouter.ai/api/v1/chat/completions';
const STUDENT_NAME = '<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>';
const STUDENT_IMAGE = '<?php echo htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'); ?>';

let labBotConversationHistory = [
    {
        role: 'system',
        content: 'You are LabBot, an AI assistant for the Microbiology Department, Faculty of Science, University of Kelaniya.You assist students with questions related to microbiology, practical lab work, research, general biology topics, laboratory equipment, and safety protocols.Provide helpful, concise, and professional responses at all times.If you are unsure about specific lab-related details, advise the student to contact the Technical Officer.'
    }
];

function sendChip(text) {
    document.getElementById('labbotChips').style.display = 'none';
    document.getElementById('labbotInput').value = text;
    sendLabbotMessage();
}

function sendLabbotMessage() {
    const input = document.getElementById('labbotInput');
    const message = input.value.trim();
    
    if (!message) return;

    // Check if API key is configured
    if (!OPENROUTER_API_KEY || OPENROUTER_API_KEY.trim() === '') {
        addChatMessageWithTyping('I apologize, but the AI assistant is not properly configured on this server. The API key is missing. Please contact your lab administrator to set up the OPENROUTER_API_KEY in the .env file.', 'bot');
        return;
    }

    console.log('LabBot: Sending message:', message);
    console.log('LabBot: API Key loaded:', !!OPENROUTER_API_KEY);
    console.log('LabBot: API Key length:', OPENROUTER_API_KEY.length);

    addChatMessage(message, 'user');
    input.value = '';

    // Loading bubble
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'labbotLoading';
    loadingDiv.style.cssText = 'display:flex;gap:8px;align-items:flex-end;';
    loadingDiv.innerHTML = `
        <div style="width:28px;height:28px;border-radius:50%;background:#10b981;color:white;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;"> <i class="bi bi-robot" style="color: white; font-size: 16px;"></i></div>
        <div style="background:#1e293b;padding:10px 14px;border-radius:16px;border-bottom-left-radius:4px;display:flex;gap:5px;align-items:center;">
            <span style="width:7px;height:7px;background:#64748b;border-radius:50%;animation:lbdot 1.4s infinite;display:inline-block;"></span>
            <span style="width:7px;height:7px;background:#64748b;border-radius:50%;animation:lbdot 1.4s infinite 0.2s;display:inline-block;"></span>
            <span style="width:7px;height:7px;background:#64748b;border-radius:50%;animation:lbdot 1.4s infinite 0.4s;display:inline-block;"></span>
        </div>`;
    document.getElementById('chatMessages').appendChild(loadingDiv);
    document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;

    labBotConversationHistory.push({
        role: 'user',
        content: message
    });

    fetch(OPENROUTER_API_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${OPENROUTER_API_KEY}`,
            'HTTP-Referer': window.location.origin,
            'X-Title': 'LabBot'
        },
        body: JSON.stringify({
         model: 'nvidia/nemotron-3-super-120b-a12b:free',
            messages: labBotConversationHistory,
            temperature: 0.7,
            max_tokens: 300
        })
    })
    .then(response => {
        console.log('LabBot: Response received, status:', response.status);
        
        // Check for HTTP errors first
        if (response.status === 401) {
            throw new Error('API_UNAUTHORIZED: Your API key is invalid or expired.');
        }
        if (response.status === 429) {
            throw new Error('API_RATE_LIMIT: Too many requests. Please wait a moment.');
        }
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json().catch(err => {
            console.error('LabBot: JSON parse error:', err);
            throw new Error('Failed to parse API response');
        });
    })
    .then(data => {
        console.log('LabBot: Full data received:', JSON.stringify(data, null, 2));
        
        const loading = document.getElementById('labbotLoading');
        if (loading) loading.remove();

        if (data.choices && data.choices[0]) {
            console.log('LabBot: choices[0]:', JSON.stringify(data.choices[0], null, 2));
            
            let botResponse = null;
            
            // Try different paths to get the message content
            if (data.choices[0].message && data.choices[0].message.content) {
                botResponse = data.choices[0].message.content;
                console.log('LabBot: Found response at choices[0].message.content');
            } else if (data.choices[0].text) {
                botResponse = data.choices[0].text;
                console.log('LabBot: Found response at choices[0].text');
            } else if (data.result && data.result.completions && data.result.completions[0]) {
                botResponse = data.result.completions[0].text || data.result.completions[0].content;
                console.log('LabBot: Found response at data.result.completions');
            }
            
            if (botResponse) {
                console.log('LabBot: Bot response received:', botResponse.substring(0, 50) + '...');
                
                labBotConversationHistory.push({
                    role: 'assistant',
                    content: botResponse
                });
                addChatMessageWithTyping(botResponse, 'bot');
            } else {
                console.error('LabBot: No response content found in choices[0]');
                addChatMessageWithTyping('Sorry, I received an incomplete response from the API. Please try again.', 'bot');
            }
        } else if (data.error) {
            console.error('LabBot API Error:', data.error.message);
            
            let errorMsg = 'Sorry, I encountered an error. ';
            if (data.error.message === 'Incorrect API key provided' || data.error.message?.includes('authentication')) {
                errorMsg += 'The API key is invalid or not configured. Please check with the lab administrator.';
            } else {
                errorMsg += data.error.message || 'Please try again.';
            }
            addChatMessageWithTyping(errorMsg, 'bot');
        } else {
            console.error('LabBot: Unexpected response format:', JSON.stringify(data, null, 2));
            addChatMessageWithTyping('Sorry, something went wrong. Please try again.', 'bot');
        }
    })
    .catch(error => {
        console.error('LabBot Error caught:', error.message, error);
        
        const loading = document.getElementById('labbotLoading');
        if (loading) loading.remove();
        
        let errorMsg = 'Sorry, I\'m having trouble connecting. ';
        if (error.message?.includes('API_UNAUTHORIZED')) {
            errorMsg = 'The API key appears to be invalid. Please contact your lab administrator.';
        } else if (error.message?.includes('API_RATE_LIMIT')) {
            errorMsg = 'Too many requests. Please wait a moment and try again.';
        } else if (error.message?.includes('401')) {
            errorMsg = 'Authentication failed. The API key may be invalid or expired.';
        } else if (error.message?.includes('timeout')) {
            errorMsg = 'Request timed out. Please try again.';
        } else if (error.message?.includes('parse')) {
            errorMsg = 'Got an invalid response from the API. Please try again.';
        } else {
            errorMsg = 'Network error. Please check your connection and try again.';
        }
        addChatMessageWithTyping(errorMsg, 'bot');
    });
}

function addChatMessage(text, sender) {
    const chatMessages = document.getElementById('chatMessages');
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:8px;align-items:flex-end;' + (sender === 'user' ? 'flex-direction:row-reverse;' : '');

    let avatar = document.createElement('div');
    let label = 'LabBot';
    const bubbleBg  = sender === 'user' ? '#10b981' : '#1e293b';
    const textColor = sender === 'user' ? 'white' : '#cbd5e1';
    const radius    = sender === 'user' ? '16px 16px 4px 16px' : '16px 16px 16px 4px';

    if (sender === 'user') {
        // Student message - show profile image
        label = STUDENT_NAME;
        avatar.innerHTML = `<img src="${STUDENT_IMAGE}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">`;
    } else {
        // Bot message - show robot icon
        avatar.style.cssText = 'width:28px;height:28px;border-radius:50%;background:#10b981;color:white;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;';
        avatar.innerHTML = '<i class="bi bi-robot" style="color: white; font-size: 16px;"></i>';
    }

    const messageContent = document.createElement('div');
    messageContent.style.cssText = `max-width:72%;background:${bubbleBg};color:${textColor};padding:10px 14px;border-radius:${radius};font-size:13px;line-height:1.5;word-break:break-word;`;
    messageContent.innerHTML = `
        <div style="font-weight:600;font-size:11px;margin-bottom:4px;opacity:0.6;">${label}</div>
        ${escapeHtml(text)}`;

    if (sender === 'user') {
        row.appendChild(messageContent);
        row.appendChild(avatar);
    } else {
        row.appendChild(avatar);
        row.appendChild(messageContent);
    }

    chatMessages.appendChild(row);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function addChatMessageWithTyping(text, sender) {
    // Ensure text is a string
    if (!text || typeof text !== 'string') {
        text = String(text || '');
    }
    
    const chatMessages = document.getElementById('chatMessages');
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:8px;align-items:flex-end;' + (sender === 'user' ? 'flex-direction:row-reverse;' : '');

    let avatar = document.createElement('div');
    let label = 'LabBot';
    const bubbleBg  = sender === 'user' ? '#10b981' : '#1e293b';
    const textColor = sender === 'user' ? 'white' : '#cbd5e1';
    const radius    = sender === 'user' ? '16px 16px 4px 16px' : '16px 16px 16px 4px';

    if (sender === 'user') {
        // Student message - show profile image, display instantly (no typing animation)
        label = STUDENT_NAME;
        avatar.innerHTML = `<img src="${STUDENT_IMAGE}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">`;
        
        const messageContent = document.createElement('div');
        messageContent.style.cssText = `max-width:72%;background:${bubbleBg};color:${textColor};padding:10px 14px;border-radius:${radius};font-size:13px;line-height:1.5;word-break:break-word;`;
        messageContent.innerHTML = `<div style="font-weight:600;font-size:11px;margin-bottom:4px;opacity:0.6;">${label}</div>${escapeHtml(text)}`;
        
        row.appendChild(messageContent);
        row.appendChild(avatar);
        chatMessages.appendChild(row);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    } else {
        // Bot message - show robot icon with typing animation
        avatar.style.cssText = 'width:28px;height:28px;border-radius:50%;background:#10b981;color:white;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;';
        avatar.innerHTML = '<i class="bi bi-robot" style="color: white; font-size: 16px;"></i>';

        const messageContent = document.createElement('div');
        messageContent.style.cssText = `max-width:72%;background:${bubbleBg};color:${textColor};padding:10px 14px;border-radius:${radius};font-size:13px;line-height:1.5;word-break:break-word;`;
        
        const labelDiv = document.createElement('div');
        labelDiv.style.cssText = 'font-weight:600;font-size:11px;margin-bottom:4px;opacity:0.6;';
        labelDiv.textContent = label;
        
        const textSpan = document.createElement('span');
        const cursorSpan = document.createElement('span');
        cursorSpan.style.animation = 'blink 0.7s infinite';
        cursorSpan.textContent = '|';
        
        messageContent.appendChild(labelDiv);
        messageContent.appendChild(textSpan);
        messageContent.appendChild(cursorSpan);
        
        row.appendChild(avatar);
        row.appendChild(messageContent);
        chatMessages.appendChild(row);
        
        // Animate typing
        let charIndex = 0;
        const typeChar = () => {
            if (charIndex < text.length) {
                const char = text[charIndex];
                if (char === '\n') {
                    textSpan.innerHTML += '<br>';
                } else {
                    textSpan.textContent += char;
                }
                charIndex++;
                chatMessages.scrollTop = chatMessages.scrollHeight;
                setTimeout(typeChar, 30);
            } else {
                // Remove cursor when done
                if (cursorSpan && cursorSpan.parentNode) {
                    cursorSpan.remove();
                }
            }
        };
        
        typeChar();
    }
}





        // View reservation details
        function viewReservation(reservationId) {
            viewReservationDetails(reservationId);
        }

        // Remove reservation (for pending only)
        function removeReservation(reservationId) {
            // alert(reservationId);
            ConfirmModal.show({
                title: 'Remove Reservation',
                heading: 'Remove this reservation?',
                message: 'This will permanently remove your pending reservation.<br><span style="color:#dc2626;font-weight:600;">This action cannot be undone.</span>',
                type: 'danger',
                confirmText: 'Yes, Remove It',
                confirmIcon: 'bi-trash3',
                onConfirm: () => {
                    ConfirmModal.setLoading('Removing...');
                    fetch('../controllers/remove_reservation.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                reservation_id: reservationId
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            ConfirmModal.hide();
                            if (data.success) {
                                showToast('success', 'Reservation removed successfully!');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showToast('error', data.message || 'Failed to remove reservation.');
                            }
                        })
                        .catch(() => {
                            ConfirmModal.hide();
                            showToast('error', 'Network error. Please try again.');
                        });
                }
            });
        }
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
        function loadEquipment() {
            const locationId = document.getElementById('labLocation').value;
            const searchInput = document.getElementById('equipmentSearch');
            const bookQty = document.getElementById('bookQty');
            const addBtn = document.getElementById('addEquipmentBtn');
            const searchHint = document.getElementById('searchHint');

            if (!locationId) {
                searchInput.disabled = true;
                searchInput.value = '';
                searchInput.placeholder = 'Select a lab location first';
                bookQty.disabled = true;
                addBtn.disabled = true;
                searchHint.innerText = 'Select a lab location first';
                document.getElementById('availableQty').value = '';
                document.getElementById('equipmentDropdown').style.display = 'none';
                return;
            }

            searchInput.disabled = false;
            searchInput.value = '';
            searchInput.placeholder = 'Type to search equipment by name or code...';
            bookQty.disabled = true;
            addBtn.disabled = true;
            searchHint.innerText = 'Start typing to search equipment in this lab (min 2 characters)';
            document.getElementById('availableQty').value = '';
            document.getElementById('equipmentDropdown').style.display = 'none';
        }

        // Add input event listener for equipment search
        document.getElementById('equipmentSearch')?.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            const locationId = document.getElementById('labLocation').value;
            const dropdown = document.getElementById('equipmentDropdown');

            // Validate location selected
            if (!locationId) {
                alert('Please select a lab location first');
                this.value = '';
                dropdown.style.display = 'none';
                return;
            }

            // Require at least 2 characters for search
            if (searchTerm.length < 2) {
                dropdown.style.display = 'none';
                return;
            }

            // Show loading indicator
            dropdown.innerHTML = '<div class="text-center p-3"><span class="spinner-border spinner-border-sm text-success" role="status"></span> Searching equipment...</div>';
            dropdown.style.display = 'block';

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
                        dropdown.innerHTML = '<div class="text-danger p-3">Error processing response</div>';
                    }
                } else if (xhr.status === 401) {
                    dropdown.innerHTML = '<div class="text-danger p-3">Session expired. Please login again.</div>';
                    setTimeout(() => {
                        window.location.href = '../index.php';
                    }, 2000);
                } else {
                    dropdown.innerHTML = `<div class="text-danger p-3">Server error (Status: ${xhr.status}). Please try again.</div>`;
                }
            };

            xhr.onerror = function() {
                dropdown.innerHTML = '<div class="text-danger p-3">Network error. Please check your connection.</div>';
            };

            xhr.send();
        });

        function displayEquipmentDropdown(equipment) {
            const dropdown = document.getElementById('equipmentDropdown');
            dropdown.innerHTML = '';

            if (!equipment || equipment.length === 0) {
                dropdown.innerHTML = '<div class="text-muted p-3 text-center"><i class="bi bi-search me-2"></i>No available equipment found matching your search</div>';
                dropdown.style.display = 'block';
                return;
            }

            equipment.forEach(item => {
                const div = document.createElement('div');
                div.className = 'dropdown-item p-3 border-bottom';
                div.style.cursor = 'pointer';
                div.onmouseover = () => div.style.backgroundColor = '#f0fdf4';
                div.onmouseout = () => div.style.backgroundColor = '';
                div.onclick = () => selectEquipment(item);

                div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong class="fs-6">${item.name}</strong>
                    <div class="mt-1">
                        <small class="text-muted"><i class="bi bi-upc-scan me-1"></i>Code: ${item.code}</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-success p-2">Available: ${item.available_qty}</span>
                </div>
            </div>
        `;
                dropdown.appendChild(div);
            });

            dropdown.style.display = 'block';
        }

        function selectEquipment(item) {
            // Store current equipment details
            currentEquipmentId = item.id;
            currentEquipmentName = item.name;
            currentEquipmentCode = item.code;
            currentAvailableQty = item.available_qty;

            // Update form fields
            document.getElementById('equipmentSearch').value = `${item.name} (${item.code})`;
            document.getElementById('availableQty').value = item.available_qty;

            const bookQty = document.getElementById('bookQty');
            bookQty.disabled = false;
            bookQty.max = item.available_qty;
            bookQty.value = 1;
            bookQty.min = 1;

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
       readonly
       style="width: 80px; background-color: #f8f9fa;">
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
            // if (selectedEquipment.length > 0 && !confirm('Are you sure you want to reset? All selected equipment will be cleared.')) {
            //     return;
            // }

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

    // Validation with toast
    if (!locationId) {
        showToast('error', 'Please select a lab location.');
        return;
    }

    if (!requestDate) {
        showToast('error', 'Please select a request date.');
        return;
    }

    if (selectedEquipment.length === 0) {
        showToast('error', 'Please add at least one equipment item');
        return;
    }

    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const originalContent = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

    // Prepare form data
    const formData = new FormData();
    formData.append('location_id', locationId);
    formData.append('request_date', requestDate);
    formData.append('continue_days', document.getElementById('continueDays').value);
    formData.append('comment', document.getElementById('requestComment').value);
    formData.append('equipment', JSON.stringify(selectedEquipment));

    // Send request
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'submit_reservation.php', true);
    
    xhr.onload = function() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;

        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    // Success toast
                    showToast('success', '✅ Reservation submitted successfully!');
                    
                    // Reset form and reload after a short delay
                    resetForm();
                    setTimeout(() => {
                        location.reload();
                    }, 1500); // Wait 1.5 seconds to show the success message
                    
                } else {
                    // Error toast with server message
                    showToast('error', '❌ ' + (response.message || 'Failed to submit reservation'));
                }
                
            } catch (e) {
                console.error('Parse error:', e);
                showToast('error', '❌ Server error occurred. Please try again.');
            }
        } else {
            // HTTP error toast
            showToast('error', '❌ Server error (Status: ' + xhr.status + '). Please try again.');
        }
    };

    xhr.onerror = function() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
        showToast('error', '❌ Network error. Please check your connection.');
    };

    xhr.ontimeout = function() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
        showToast('error', '❌ Request timed out. Please try again.');
    };

    // Optional: Set timeout (30 seconds)
    xhr.timeout = 30000;
    
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
        // ============ NOTIFICATION FUNCTIONS ============
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById("notificationDropdown");
            dropdown.classList.toggle("show");

            // Mark as read when opened
            if (dropdown.classList.contains("show")) {
                markNotificationsAsRead();
            }
        }

        function markNotificationRead(notificationId) {
            // Optional: Mark individual notification as read
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../controllers/mark_notifications_read.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Remove unread class from this notification
                    const notifItem = event.currentTarget;
                    if (notifItem) notifItem.classList.remove('unread');

                    // Update badge count
                    updateNotificationCount();
                }
            };
            xhr.send('notification_id=' + notificationId);
        }

        function markNotificationsAsRead() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../controllers/mark_notifications_read.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Hide the badge
                            const badge = document.getElementById('notificationBadge');
                            if (badge) {
                                badge.style.display = 'none';
                            }

                            // Remove unread class from all notification items
                            document.querySelectorAll('.notification-item').forEach(item => {
                                item.classList.remove('unread');
                            });

                            // Update the header "new" count
                            const headerSpan = document.querySelector('.notification-header span');
                            if (headerSpan) {
                                headerSpan.textContent = '0 new';
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };

            // Send student_id
            xhr.send('user_id=' + encodeURIComponent('<?php echo $student_id; ?>'));
        }

        function updateNotificationCount() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '../controllers/get_notification_count.php?user_id=<?php echo $student_id; ?>', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        const badge = document.getElementById('notificationBadge');
                        const headerSpan = document.querySelector('.notification-header span');

                        if (response.count > 0) {
                            if (badge) {
                                badge.textContent = response.count;
                                badge.style.display = 'flex';
                            }
                            if (headerSpan) {
                                headerSpan.textContent = response.count + ' new';
                            }
                        } else {
                            if (badge) {
                                badge.style.display = 'none';
                            }
                            if (headerSpan) {
                                headerSpan.textContent = '0 new';
                            }
                        }
                    } catch (e) {
                        console.error('Error updating notification count:', e);
                    }
                }
            };
            xhr.send();
        }

        // Auto-refresh notification count periodically
        setInterval(function() {
            if (!document.getElementById("notificationDropdown").classList.contains("show")) {
                updateNotificationCount();
            }
        }, 30000); // Refresh every 30 seconds

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const bell = document.querySelector('.notification-bell');

            if (dropdown && bell && !dropdown.contains(event.target) && !bell.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Prevent dropdown from closing when clicking inside it
        document.getElementById('notificationDropdown')?.addEventListener('click', function(event) {
            event.stopPropagation();
        });

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
            viewReservationDetails(reservationId);
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
            document.getElementById('labbotSection').style.display = 'none';
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
            console.log('Loading equipment list...');

            // Show loading indicator - CHANGED colspan from 5 to 4
            document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-success"></div> Loading equipment...</td></tr>';

            // Get the current filter value (default to 'all')
            const labFilter = document.getElementById('labFilter')?.value || 'all';

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_equipment_list.php?lab_id=' + labFilter, true);

            xhr.onload = function() {
                console.log('Equipment list response status:', xhr.status);

                if (xhr.status === 200) {
                    document.getElementById('equipmentTableBody').innerHTML = xhr.responseText;
                } else if (xhr.status === 404) {
                    console.error('get_equipment_list.php not found');
                    // CHANGED colspan from 5 to 4
                    document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Equipment list file not found (404). Please check if get_equipment_list.php exists in the student folder.</td></tr>';
                } else {
                    console.error('Error loading equipment. Status:', xhr.status);
                    // CHANGED colspan from 5 to 4
                    document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading equipment (Status: ' + xhr.status + '). Please try again.</td></tr>';
                }
            };

            xhr.onerror = function() {
                console.error('Network error loading equipment');
                // CHANGED colspan from 5 to 4
                document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Network error. Please check your connection.</td></tr>';
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


        function viewReservationDetails(reservationId) {
            const modalEl = document.getElementById('reservationDetailsModal');
            const contentDiv = document.getElementById('reservationDetailsContent');

            contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status" style="width:2rem;height:2rem;"></div>
            <p class="mt-3 text-muted small fw-semibold">Loading reservation details...</p>
        </div>`;

            const existing = bootstrap.Modal.getInstance(modalEl);
            if (existing) existing.dispose();
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            fetch(`../controllers/get_reservation_details.php?id=${encodeURIComponent(reservationId)}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        contentDiv.innerHTML = `<div class="alert alert-danger m-3">${data.message || 'Failed to load'}</div>`;
                        return;
                    }

                    const res = data;

                    // Status badge
                    let badgeColor = '#f59e0b';
                    let statusText = 'Pending';
                    if (res.status === 'Ready' || res.status === 'to_checked') {
                        badgeColor = '#22c55e';
                        statusText = 'Approved';
                    } else if (res.status === 'Rejected' || res.status === 'rejected') {
                        badgeColor = '#ef4444';
                        statusText = 'Rejected';
                    } else if (res.status === 'to_pending' || res.status === 'TO_Pending') {
                        badgeColor = '#3b82f6';
                        statusText = 'Under Review';
                    }

                    // Equipment list
                    let equipmentHtml = '<p class="text-muted fst-italic">No equipment found</p>';
                    if (res.equipment && res.equipment.length > 0) {
                        equipmentHtml = `
                    <table class="table table-sm table-bordered mt-2">
                        <thead style="background:linear-gradient(135deg,#22c55e,#16a34a);color:white;">
                            <tr><th>Equipment</th><th>Code</th><th class="text-center">Qty</th></tr>
                        </thead>
                        <tbody>
                            ${res.equipment.map(eq => `
                                <tr>
                                    <td>${eq.name}</td>
                                    <td><small class="text-muted">${eq.code}</small></td>
                                    <td class="text-center"><span class="badge bg-success">${eq.booked_qty}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>`;
                    }

                    // Rejection reason
                    let rejectionHtml = '';
                    if ((res.status === 'rejected' || res.status === 'Rejected') && res.rejected_reason) {
                        rejectionHtml = `
                    <div style="background:#fff0f0;border-left:4px solid #ef4444;
                                padding:12px 16px;border-radius:8px;margin-top:12px;">
                        <strong style="color:#dc2626;">
                            <i class="bi bi-exclamation-triangle me-2"></i>Rejection Reason:
                        </strong>
                        <p class="mb-0 mt-1" style="color:#78350f;">${res.rejected_reason}</p>
                    </div>`;
                    }

                    // Format date
                    let formattedDate = res.date || '—';
                    try {
                        if (res.date && res.date !== '—') {
                            formattedDate = new Date(res.date).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                        }
                    } catch (e) {}

                    contentDiv.innerHTML = `
                <div class="p-2">
                    <!-- Header info -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Reservation ID</small>
                            <strong style="color:#166534;font-size:1.1rem;">${res.id || reservationId}</strong>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted d-block">Status</small>
                            <span style="background:${badgeColor};color:white;padding:4px 14px;
                                         border-radius:20px;font-weight:600;font-size:0.85rem;">
                                ${statusText}
                            </span>
                        </div>
                    </div>

                    <hr>

                    <!-- Details -->
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td style="color:#166534;font-weight:600;width:140px;">Lab Location</td>
                            <td><i class="bi bi-geo-alt-fill text-danger me-1"></i>${res.lab_location || '—'}</td>
                        </tr>
                        <tr>
                            <td style="color:#166534;font-weight:600;">Request Date</td>
                            <td><i class="bi bi-calendar3 me-1"></i>${formattedDate}</td>
                        </tr>
                        <tr>
                            <td style="color:#166534;font-weight:600;">Supervisor</td>
                            <td>${res.supervisor_id || '—'}</td>
                        </tr>
                        ${res.comment ? `
                        <tr>
                            <td style="color:#166534;font-weight:600;">Comment</td>
                            <td><em class="text-muted">"${res.comment}"</em></td>
                        </tr>` : ''}
                    </table>

                    ${rejectionHtml}

                    <!-- Equipment -->
                    <div class="mt-3">
                        <strong style="color:#166534;">
                            <i class="bi bi-tools me-1"></i>Equipment Requested
                        </strong>
                        ${equipmentHtml}
                    </div>
                </div>`;
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = `<div class="alert alert-danger m-3">Network error. Please try again.</div>`;
                });
        }



        function searchEquipmentTable() {
            const searchTerm = document.getElementById('equipmentSearch').value.trim();
            const labId = document.getElementById('labFilter').value;

            if (searchTerm.length < 2) {
                alert('Please enter at least 2 characters to search');
                loadEquipmentList();
                return;
            }

            // Add loading indicator - CHANGED colspan from 5 to 4
            document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-success"></div> Searching equipment...</td></tr>';

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_equipment_list.php?lab_id=' + labId + '&term=' + encodeURIComponent(searchTerm), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('equipmentTableBody').innerHTML = xhr.responseText;
                } else {
                    // CHANGED colspan from 5 to 4
                    document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error searching equipment</td></tr>';
                }
            };
            xhr.onerror = function() {
                // CHANGED colspan from 5 to 4
                document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Network error</td></tr>';
            };
            xhr.send();
        }

        function filterEquipmentTable() {
            const labId = document.getElementById('labFilter').value;

            // Clear search input when filtering
            document.getElementById('equipmentSearch1').value = '';

            // Add loading indicator - CHANGED colspan from 5 to 4
            document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-success"></div> Loading equipment...</td></tr>';

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_equipment_list.php?lab_id=' + labId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('equipmentTableBody').innerHTML = xhr.responseText;
                } else {
                    // CHANGED colspan from 5 to 4
                    document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading equipment</td></tr>';
                }
            };
            xhr.onerror = function() {
                // CHANGED colspan from 5 to 4
                document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Network error</td></tr>';
            };
            xhr.send();
        }





        function viewEquipmentDetails(code) {
            // alert("ok");
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
                            <!-- <tr><th class="text-muted fw-normal" style="width:160px">Date Added</th><td>${addedDate}</td></tr> -->
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


        // ============ AUTO SEARCH FUNCTIONALITY ============
        let searchTimeout;

        // Auto-search for equipment
        const searchInput = document.getElementById('equipmentSearch1');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim();
                const labId = document.getElementById('labFilter')?.value || 'all';

                // Clear previous timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Show loading indicator for short searches
                if (searchTerm.length > 0 && searchTerm.length < 2) {
                    document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-search fs-1 d-block mb-2"></i>Type at least 2 characters to search</td></tr>';
                    return;
                }

                // Set new timeout (300ms delay)
                searchTimeout = setTimeout(() => {
                    if (searchTerm.length === 0) {
                        // If search is empty, load all equipment
                        loadEquipmentList();
                    } else if (searchTerm.length >= 2) {
                        // Show loading indicator
                        document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-success" style="width: 2rem; height: 2rem;"></div><p class="mt-2">Searching...</p></td></tr>';

                        // Perform search
                        const xhr = new XMLHttpRequest();
                        xhr.open('GET', 'get_equipment_list.php?lab_id=' + labId + '&term=' + encodeURIComponent(searchTerm), true);

                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                document.getElementById('equipmentTableBody').innerHTML = xhr.responseText;
                            } else {
                                document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error searching equipment</td></tr>';
                            }
                        };

                        xhr.onerror = function() {
                            document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Network error</td></tr>';
                        };

                        xhr.send();
                    }
                }, 300); // Wait 300ms after user stops typing
            });

            // Add clear button functionality (optional)
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    loadEquipmentList();
                }
            });
        }

        // Also update filterEquipmentTable to work with search
        function filterEquipmentTable() {
            const labId = document.getElementById('labFilter').value;

            // Clear search input when filtering
            const searchInput = document.getElementById('equipmentSearch1');
            if (searchInput) {
                searchInput.value = '';
            }

            // Add loading indicator
            document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-success"></div> Loading equipment...</td></tr>';

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_equipment_list.php?lab_id=' + labId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('equipmentTableBody').innerHTML = xhr.responseText;
                } else {
                    document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading equipment</td></tr>';
                }
            };
            xhr.onerror = function() {
                document.getElementById('equipmentTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Network error</td></tr>';
            };
            xhr.send();
        }



        // =============================================
        // REUSABLE CONFIRM MODAL
        // =============================================
        const ConfirmModal = {
            _callback: null,

            show({
                title = 'Are you sure?',
                heading = 'Are you sure?',
                message = 'This action cannot be undone.',
                type = 'danger', // danger | warning | success | info
                confirmText = 'Confirm',
                confirmIcon = 'bi-check-circle',
                onConfirm = null
            }) {
                const colors = {
                    danger: {
                        header: 'linear-gradient(135deg,#dc2626,#b91c1c)',
                        circle: '#fee2e2',
                        icon: '#dc2626'
                    },
                    warning: {
                        header: 'linear-gradient(135deg,#f59e0b,#d97706)',
                        circle: '#fef3c7',
                        icon: '#f59e0b'
                    },
                    success: {
                        header: 'linear-gradient(135deg,#22c55e,#16a34a)',
                        circle: '#dcfce7',
                        icon: '#22c55e'
                    },
                    info: {
                        header: 'linear-gradient(135deg,#3b82f6,#2563eb)',
                        circle: '#dbeafe',
                        icon: '#3b82f6'
                    },
                };

                const c = colors[type] || colors.danger;

                // Set header
                document.getElementById('confirmModalHeader').style.background = c.header;
                document.getElementById('confirmModalTitleText').textContent = title;

                // Set icon circle
                document.getElementById('confirmModalIconCircle').style.background = c.circle;
                document.getElementById('confirmModalBodyIcon').style.color = c.icon;
                document.getElementById('confirmModalBodyIcon').className = `bi ${confirmIcon}`;

                // Set text
                document.getElementById('confirmModalHeading').textContent = heading;
                document.getElementById('confirmModalMessage').innerHTML = message;

                // Set confirm button
                const btn = document.getElementById('confirmModalBtn');
                btn.style.background = c.header;
                btn.innerHTML = `<i class="bi ${confirmIcon} me-2"></i>${confirmText}`;
                btn.disabled = false;

                this._callback = onConfirm;

                const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                modal.show();
            },

            setLoading(text = 'Processing...') {
                const btn = document.getElementById('confirmModalBtn');
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${text}`;
            },

            hide() {
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
                if (modal) modal.hide();
            }
        };

        // Confirm button click
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('confirmModalBtn').addEventListener('click', function() {
                if (typeof ConfirmModal._callback === 'function') {
                    ConfirmModal._callback();
                }
            });
        });

        // =============================================
        // REUSABLE TOAST
        // =============================================
        function showToast(type, message) {
            const colors = {
                success: {
                    color: '#22c55e',
                    icon: 'bi-check-circle-fill'
                },
                error: {
                    color: '#dc2626',
                    icon: 'bi-x-circle-fill'
                },
                warning: {
                    color: '#f59e0b',
                    icon: 'bi-exclamation-triangle-fill'
                },
                info: {
                    color: '#3b82f6',
                    icon: 'bi-info-circle-fill'
                },
            };

            const c = colors[type] || colors.info;
            const toast = document.getElementById('appToast');

            document.getElementById('appToastIcon').className = `bi ${c.icon}`;
            document.getElementById('appToastIcon').style.color = c.color;
            document.getElementById('appToastMsg').textContent = message;

            toast.style.borderLeft = `5px solid ${c.color}`;
            toast.style.display = 'flex';
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
            toast.style.transition = 'all 0.3s ease';

            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                setTimeout(() => toast.style.display = 'none', 300);
            }, 3000);
        }

        // =============================================
        // PROFILE MODAL FUNCTIONS
        // =============================================
        function openProfileModal(event) {
            event.preventDefault();
            const profileModal = document.getElementById('profileModal');
            const modal = new bootstrap.Modal(profileModal);
            
            // Add listener for when modal is hidden (closed)
            profileModal.addEventListener('hidden.bs.modal', function() {
                location.reload();
            }, { once: true }); // Only trigger once
            
            modal.show();
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileImagePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        function saveProfile() {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const mobile = document.getElementById('mobile').value.trim();
            const universityId = document.getElementById('universityId').value.trim();
            const imageFile = document.getElementById('profileImageInput').files[0];

            // Validation
            if (!firstName) {
                showToast('warning', 'First name is required');
                return;
            }
            if (!lastName) {
                showToast('warning', 'Last name is required');
                return;
            }
            if (!email) {
                showToast('warning', 'Email is required');
                return;
            }

            // Prepare FormData
            const formData = new FormData();
            formData.append('first_name', firstName);
            formData.append('last_name', lastName);
            formData.append('email', email);
            formData.append('mobile', mobile);
            formData.append('university_id', universityId);
            
            // Add image only if a new file was selected
            if (imageFile) {
                formData.append('profile_image', imageFile);
            }

            // Send to server
            fetch('../controllers/update_student_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', '✅ Profile updated successfully!');
                    // Update the page name display
                    document.getElementById('userName').textContent = firstName;
                    document.getElementById('userNameDisplay').textContent = firstName + ' ' + lastName;
                    
                    // Update profile image if changed
                    if (data.profile_image) {
                        document.getElementById('profileImagePreview').src = data.profile_image;
                    }
                    
                    // Close modal
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('profileModal')).hide();
                    }, 500);
                } else {
                    showToast('error', data.message || 'Failed to update profile');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Error saving profile');
            });
        }

       
    </script>




    <!-- PUT THIS just before </body>, after all other modals -->
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

    <!-- Reservation Details Modal -->
    <div class="modal fade" id="reservationDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-white"
                    style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-check me-2"></i>Reservation Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reservationDetailsContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Reusable Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:24px;border:none;box-shadow:0 20px 50px rgba(0,0,0,0.3);overflow:hidden;">
                <div class="modal-header" id="confirmModalHeader" style="border:none;padding:20px 25px;">
                    <h5 class="modal-title text-white fw-bold" id="confirmModalTitle">
                        <i id="confirmModalIcon" class="me-2"></i>
                        <span id="confirmModalTitleText"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div id="confirmModalIconCircle"
                        style="width:70px;height:70px;border-radius:50%;
                            display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                        <i id="confirmModalBodyIcon" style="font-size:2rem;"></i>
                    </div>
                    <h5 id="confirmModalHeading" style="color:#111827;font-weight:700;margin-bottom:10px;"></h5>
                    <p id="confirmModalMessage" style="color:#6b7280;font-size:0.95rem;margin-bottom:0;"></p>
                </div>
                <div class="modal-footer" style="border:none;padding:10px 25px 25px;justify-content:center;gap:12px;">
                    <button type="button" data-bs-dismiss="modal"
                        style="background:#f3f4f6;color:#374151;border:none;
                               padding:10px 30px;border-radius:50px;font-weight:600;cursor:pointer;">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" id="confirmModalBtn"
                        style="color:white;border:none;padding:10px 30px;
                               border-radius:50px;font-weight:600;cursor:pointer;">
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="appToast"
        style="position:fixed;top:20px;right:20px;z-index:99999;
            background:white;border-radius:16px;
            box-shadow:0 10px 30px rgba(0,0,0,0.15);
            padding:16px 20px;display:none;align-items:center;
            gap:12px;min-width:280px;max-width:380px;">
        <i id="appToastIcon" style="font-size:1.4rem;"></i>
        <span id="appToastMsg" style="color:#111827;font-weight:500;font-size:0.95rem;"></span>
    </div>
</body>

</html>
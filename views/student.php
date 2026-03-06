
<?php
require_once '../config/database.php';
require_once 'auth_check.php';
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
            from { opacity: 0; }
            to { opacity: 1; }
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
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
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
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
      <!-- Keep original favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/resources/flask.svg">
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR - Student Version -->
    <div class="sidebar" id="sidebar">
        <h4><i class="bi bi-flask"></i> MicroLab</h4>
        <a onclick="showSection('dashboard')" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a onclick="showSection('equipment')"><i class="bi bi-tools"></i> Equipment</a>
        <a onclick="showSection('history')"><i class="bi bi-clock-history"></i> Reservation History</a>
        <a href="#" onclick="logout()"><i class="bi bi-box-arrow-right"></i> Logout</a>

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
                <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, #22c55e, #16a34a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Welcome, <span id="userName">John</span>
                </h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notification Bell -->
                <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge">3</span>
                </div>

                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h6>Notifications</h6>
                        <span>3 new</span>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-item unread">
                            <div><i class="bi bi-check-circle-fill text-success me-2"></i> Your booking #REQ004 has been approved</div>
                            <div class="time">2 minutes ago</div>
                        </div>
                        <div class="notification-item unread">
                            <div><i class="bi bi-clock-fill text-warning me-2"></i> Equipment ready for pickup</div>
                            <div class="time">15 minutes ago</div>
                        </div>
                        <div class="notification-item unread">
                            <div><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Return reminder: Microscope due today</div>
                            <div class="time">1 hour ago</div>
                        </div>
                    </div>
                </div>

                <span class="fw-semibold d-none d-sm-block" style="color: #166534;" id="userNameDisplay">John Doe</span>
                <div class="dropdown">
                    <img src="https://ui-avatars.com/api/?name=John+Doe&background=22c55e&color=fff&size=100" class="profile-img dropdown-toggle" data-bs-toggle="dropdown">
                    <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="logout()"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">

            <!-- Dashboard Section -->
            <div id="dashboardSection">
           

                <!-- Create Request Section - UPDATED -->
<h4 class="mb-3" style="color: white;">Create Equipment Request</h4>
<div class="card p-4 mb-4">
    <form id="equipmentRequestForm">
        <!-- Lab Location, Requested Date, Continue Days - ALL IN ONE ROW -->
        <div class="row g-3 mb-3">




     <div class="col-md-4">
    <label class="form-label fw-semibold">Lab Location</label>
    <select id="labLocation" class="form-select" required onchange="loadEquipmentByLab()">
        <option value="" disabled selected>Select Lab</option>
        <?php
        // Enable error reporting for debugging (remove in production)
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        try {
            // First, check if the table exists
            $check_table_query = "SHOW TABLES LIKE 'lab_location'";
            $table_result = Database::search($check_table_query);
            
            if ($table_result && $table_result->num_rows > 0) {
                // Table exists, now query locations
                $location_query = "SELECT DISTINCT location FROM lab_location WHERE location IS NOT NULL AND location != '' ORDER BY location";
                $location_result = Database::search($location_query);
                
                if ($location_result && $location_result->num_rows > 0) {
                    while ($row = $location_result->fetch_assoc()) {
                        $location = htmlspecialchars($row['location']);
                        echo "<option value=\"$location\">$location</option>";
                    }
                } else {
                    // Table exists but no data
                    echo '<option value="" disabled>No lab locations found in database</option>';
                    // Still show fallback options
                    echo '<option value="Microbiology Lab 01">Microbiology Lab 01</option>';
                    echo '<option value="Microbiology Lab 02">Microbiology Lab 02</option>';
                    echo '<option value="Research Laboratory">Research Laboratory</option>';
                }
            } else {
                // Table doesn't exist
                echo '<option value="" disabled>Lab location table not found</option>';
                // Show fallback options
                echo '<option value="Microbiology Lab 01">Microbiology Lab 01</option>';
                echo '<option value="Microbiology Lab 02">Microbiology Lab 02</option>';
                echo '<option value="Research Laboratory">Research Laboratory</option>';
            }
        } catch (Exception $e) {
            // Log error and show fallback
            error_log("Database error in lab location dropdown: " . $e->getMessage());
            echo '<option value="" disabled>Error loading locations</option>';
            echo '<option value="Microbiology Lab 01">Microbiology Lab 01</option>';
            echo '<option value="Microbiology Lab 02">Microbiology Lab 02</option>';
            echo '<option value="Research Laboratory">Research Laboratory</option>';
        }
        ?>
    </select>
</div>





            <div class="col-md-4">
                <label class="form-label fw-semibold">Requested Date</label>
                <input type="date" id="requestDate" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Continue Days</label>
                <select id="continueDays" class="form-select" required>
                    <option value="" disabled selected>Select Days</option>
                    <option value="1">1 Day</option>
                    <option value="2">2 Days</option>
                    <option value="3">3 Days</option>
                </select>
            </div>
        </div>

        <!-- Equipment Search - Second (only relevant to selected lab) -->
        <div class="row g-3 mb-3">
            <div class="col-md-6 position-relative">
                <label class="form-label fw-semibold">Search Equipment (by Lab)</label>
                <input type="text" id="equipmentName" class="form-control" placeholder="Select lab location first" autocomplete="off" disabled>
                <div id="equipmentDropdown" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto;"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Quantity</label>
                <input type="number" id="equipmentQty" class="form-control" min="1" value="1" disabled>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-success w-100" onclick="addEquipment()" id="addEquipmentBtn" disabled>
                    <i class="bi bi-plus-circle me-1"></i>Add
                </button>
            </div>
        </div>

        <!-- Equipment Table -->
        <div class="table-responsive">
            <table class="table table-bordered mb-4" id="equipmentTable">
                <thead class="table-success">
                    <tr>
                        <th>Equipment</th>
                        <th>Qty</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Additional Comments -->
        <div class="mb-4">
            <label class="form-label fw-semibold">Additional Comments</label>
            <textarea id="requestComment" class="form-control" rows="3" placeholder="Enter any special requirements or notes..."></textarea>
        </div>

        <!-- Buttons - Only Submit and Reset -->
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" onclick="submitRequest()">
                <i class="bi bi-send me-1"></i>Submit Request
            </button>
            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </button>
        </div>
    </form>
</div>

                <!-- Request Status Section -->
                <h4 class="mb-3" style="color: white;">My Request Status</h4>
                <div class="card p-4 mb-4">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Request ID</th>
                                  
                                    <th>Date</th>
                                    <th>Lab</th>
                                    <th>Status</th>
                                    <th>Reason (if rejected)</th>
                                </tr>
                            </thead>
                            <tbody id="requestStatusBody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Booking Calendar Section -->
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
                            <div class="events-list" id="eventsList"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Section (Table View) - UPDATED with Search -->
            <div id="equipmentSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment</h3>

                <div class="card p-4">
                    <!-- Search Bar -->
                    <div class="search-add-row">
                        <div class="search-container">
                            <input type="text" id="equipmentSearch" class="search-input" placeholder="Search by equipment name...">
                            <button class="search-btn" onclick="searchEquipmentTable()">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="filter-section">
                        <select class="filter-select" id="labFilter" onchange="filterEquipmentTable()">
                            <option value="all">All Labs</option>
                            <option value="lab1">Microbiology Lab 01</option>
                            <option value="lab2">Microbiology Lab 02</option>
                            <option value="research">Research Laboratory</option>
                        </select>

                        <!-- <select class="filter-select" id="statusFilter" onchange="filterEquipmentTable()">
                            <option value="all">All Status</option>
                            <option value="available">Available</option>
                            <option value="in-use">In Use</option>
                            <option value="maintenance">Maintenance</option>
                        </select> -->
                    </div>

                    <div class="table-responsive">
                        <table class="equipment-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Today Availability</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="equipmentTableBody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

           <!-- Reservation History Section -->
<div id="historySection" style="display: none;">
    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Reservation History</h3>

    <div class="card p-4">
        <!-- Time Filter -->
        <div class="filter-section" style="margin-bottom: 20px;">
            <select class="filter-select" id="timeFilter" onchange="filterReservations()" style="min-width: 200px;">
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
                        <th>Action</th> <!-- New column for View button -->
                    </tr>
                </thead>
                <tbody id="reservationHistoryBody">
                    <!-- Dynamic content will be loaded here -->
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

            <!-- Success/Error Modal -->
            <div class="modal fade" id="submissionModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header" id="submissionModalHeader">
                            <h5 class="modal-title" id="submissionModalTitle">Success!</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="submissionModalBody">
                            <!-- Content will be dynamically inserted -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Event Modal (Keep if needed) -->
            <div class="add-event-wrapper" id="addEventWrapper">
                <div class="add-event-header">
                    <div class="title">Equipment Request Details</div>
                    <i class="fas fa-times close" id="closeEventBtn"></i>
                </div>
                <div class="add-event-body">
                    <input type="text" placeholder="Equipment Request Title" id="eventName" maxlength="60">
                    <textarea placeholder="Request Details" id="eventDetails" rows="3"></textarea>
                    <input type="text" placeholder="Start Time (HH:MM)" id="eventTimeFrom" readonly>
                    <input type="text" placeholder="End Time (HH:MM)" id="eventTimeTo" readonly>
                </div>
                <div class="add-event-footer">
                    <button id="addEventSubmit">Confirm Request</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

   <script>
// ============ GLOBAL VARIABLES ============
let selectedEquipment = [];
let currentUser = { name: "John", id: 1 };

// Equipment data by lab (detailed version with all fields)
const equipmentByLab = {
    'Microbiology Lab 01': [
        { equipment_id: 1, name: 'Microscope', equipment_code: 'MIC001', available_units: 5, total_units: 8, manufacturer: 'Olympus', model: 'CX23' },
        { equipment_id: 4, name: 'Autoclave', equipment_code: 'AUT004', available_units: 6, total_units: 6, manufacturer: 'Hirayama', model: 'HVE-50' },
        { equipment_id: 8, name: 'Microcentrifuge', equipment_code: 'MIC008', available_units: 4, total_units: 6, manufacturer: 'Eppendorf', model: '5424' }
    ],
    'Microbiology Lab 02': [
        { equipment_id: 3, name: 'Incubator', equipment_code: 'INC003', available_units: 2, total_units: 4, manufacturer: 'Thermo Scientific', model: 'Heratherm' },
        { equipment_id: 6, name: 'Water Bath', equipment_code: 'WAT006', available_units: 5, total_units: 7, manufacturer: 'Memmert', model: 'WNB 14' }
    ],
    'Research Laboratory': [
        { equipment_id: 2, name: 'Centrifuge', equipment_code: 'CEN002', available_units: 3, total_units: 5, manufacturer: 'Eppendorf', model: '5424R' },
        { equipment_id: 5, name: 'pH Meter', equipment_code: 'PHM005', available_units: 3, total_units: 3, manufacturer: 'Mettler Toledo', model: 'FiveEasy' },
        { equipment_id: 7, name: 'Spectrophotometer', equipment_code: 'SPE007', available_units: 2, total_units: 3, manufacturer: 'Thermo Scientific', model: 'Genesys 150' }
    ]
};

// Equipment data for table display
const equipmentData = [
    { 
        id: 1,
        name: 'Microscope', 
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png', 
        location: 'Microbiology Lab 01', 
        status: 'available', 
        lab: 'lab1',
        labName: 'Microbiology Lab 01',
        available: 5,
        total: 8,
        manufacturer: 'Olympus',
        model: 'CX23',
        description: 'Binocular microscope with LED illumination'
    },
    { 
        id: 2,
        name: 'Centrifuge', 
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941543.png', 
        location: 'Research Laboratory', 
        status: 'in-use', 
        lab: 'research',
        labName: 'Research Laboratory',
        available: 3,
        total: 5,
        manufacturer: 'Eppendorf',
        model: '5424R',
        description: 'Refrigerated microcentrifuge'
    },
    { 
        id: 3,
        name: 'Incubator', 
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941538.png', 
        location: 'Microbiology Lab 02', 
        status: 'maintenance', 
        lab: 'lab2',
        labName: 'Microbiology Lab 02',
        available: 2,
        total: 4,
        manufacturer: 'Thermo Scientific',
        model: 'Heratherm',
        description: 'Microbiological incubator'
    },
    { 
        id: 4,
        name: 'Autoclave', 
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941521.png', 
        location: 'Microbiology Lab 01', 
        status: 'available', 
        lab: 'lab1',
        labName: 'Microbiology Lab 01',
        available: 6,
        total: 6,
        manufacturer: 'Hirayama',
        model: 'HVE-50',
        description: 'Vertical sterilization autoclave'
    },
    { 
        id: 5,
        name: 'pH Meter', 
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941556.png', 
        location: 'Research Laboratory', 
        status: 'available', 
        lab: 'research',
        labName: 'Research Laboratory',
        available: 3,
        total: 3,
        manufacturer: 'Mettler Toledo',
        model: 'FiveEasy',
        description: 'Digital pH meter'
    },
    { 
        id: 6,
        name: 'Water Bath', 
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941578.png', 
        location: 'Microbiology Lab 02', 
        status: 'in-use', 
        lab: 'lab2',
        labName: 'Microbiology Lab 02',
        available: 5,
        total: 7,
        manufacturer: 'Memmert',
        model: 'WNB 14',
        description: 'Digital water bath'
    },
    { 
        id: 7,
        name: 'Spectrophotometer', 
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941563.png', 
        location: 'Research Laboratory', 
        status: 'available', 
        lab: 'research',
        labName: 'Research Laboratory',
        available: 2,
        total: 3,
        manufacturer: 'Thermo Scientific',
        model: 'Genesys 150',
        description: 'UV-Vis spectrophotometer'
    },
    { 
        id: 8,
        name: 'Microcentrifuge', 
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941543.png', 
        location: 'Microbiology Lab 01', 
        status: 'available', 
        lab: 'lab1',
        labName: 'Microbiology Lab 01',
        available: 4,
        total: 6,
        manufacturer: 'Eppendorf',
        model: '5424',
        description: 'Personal microcentrifuge'
    }
];

// Calendar variables
let activeDay;
let month = new Date().getMonth();
let year = new Date().getFullYear();

const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

let eventsArr = [
    { day: 20, month: 2, year: 2026, events: [{ title: "Microscope Booking", time: "10:00 AM - 12:00 PM", details: "Lab 01" }] },
    { day: 21, month: 2, year: 2026, events: [{ title: "Centrifuge Booking", time: "2:00 PM - 4:00 PM", details: "Research Lab" }] },
    { day: 22, month: 2, year: 2026, events: [{ title: "Autoclave Booking", time: "1:00 PM - 3:00 PM", details: "Lab 01" }] }
];

// Enhanced Reservation History Data with more details
const reservationHistoryData = [
    { id: 'RES001', dateTime: '2026-03-01 10:30 AM', timestamp: new Date('2026-03-01T10:30:00'), location: 'Microbiology Lab 01', status: 'completed', equipment: 'Microscope (2), Centrifuge (1)', duration: '2 hours', purpose: 'Research Project' },
    { id: 'RES002', dateTime: '2026-02-28 02:00 PM', timestamp: new Date('2026-02-28T14:00:00'), location: 'Research Laboratory', status: 'completed', equipment: 'Spectrophotometer (1)', duration: '1.5 hours', purpose: 'Sample Analysis' },
    { id: 'RES003', dateTime: '2026-02-25 09:00 AM', timestamp: new Date('2026-02-25T09:00:00'), location: 'Microbiology Lab 02', status: 'pending', equipment: 'Incubator (1), Water Bath (1)', duration: '3 hours', purpose: 'Culture Growth' },
    { id: 'RES004', dateTime: '2026-02-20 01:00 PM', timestamp: new Date('2026-02-20T13:00:00'), location: 'Microbiology Lab 01', status: 'completed', equipment: 'Autoclave (1)', duration: '1 hour', purpose: 'Sterilization' },
    { id: 'RES005', dateTime: '2026-02-15 11:00 AM', timestamp: new Date('2026-02-15T11:00:00'), location: 'Research Laboratory', status: 'completed', equipment: 'pH Meter (1), Balance (1)', duration: '2 hours', purpose: 'Solution Preparation' },
    { id: 'RES006', dateTime: '2026-02-10 03:30 PM', timestamp: new Date('2026-02-10T15:30:00'), location: 'Microbiology Lab 02', status: 'completed', equipment: 'Microscope (1)', duration: '2 hours', purpose: 'Cell Observation' },
    { id: 'RES007', dateTime: '2026-02-05 10:00 AM', timestamp: new Date('2026-02-05T10:00:00'), location: 'Microbiology Lab 01', status: 'completed', equipment: 'Centrifuge (1)', duration: '1.5 hours', purpose: 'Sample Preparation' },
    { id: 'RES008', dateTime: '2026-02-01 02:00 PM', timestamp: new Date('2026-02-01T14:00:00'), location: 'Research Laboratory', status: 'pending', equipment: 'Spectrophotometer (1), pH Meter (1)', duration: '3 hours', purpose: 'Experiment' }
];

// ============ INITIALIZATION ============
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for request
    const today = new Date().toISOString().split('T')[0];
    const requestDate = document.getElementById('requestDate');
    if (requestDate) {
        requestDate.min = today;
        requestDate.value = today;
    }
    
    // Load initial data
    loadLabLocations();
    loadMyRequests();
    showSection('dashboard');
    
    // Display equipment table
    const equipmentTableBody = document.getElementById('equipmentTableBody');
    if (equipmentTableBody) displayEquipmentTable(equipmentData);
    
    // Display reservation history with slight delay to ensure functions are loaded
    setTimeout(() => {
        const historyBody = document.getElementById('reservationHistoryBody');
        if (historyBody) {
            filterReservations(); // This will call the correct display function
        }
    }, 100);
    
    initCalendar();
    loadEvents();
});

// ============ LOGOUT ============
function logout() {
    alert('Logged out successfully');
}

// ============ LAB LOCATIONS ============
function loadLabLocations() {
    const select = document.getElementById('labLocation');
    if (!select) return;
    
    select.innerHTML = '<option value="" disabled selected>Select Lab</option>';
    const labs = ['Microbiology Lab 01', 'Microbiology Lab 02', 'Research Laboratory'];
    labs.forEach(lab => {
        const option = document.createElement('option');
        option.value = lab;
        option.textContent = lab;
        select.appendChild(option);
    });
}

// ============ LOAD EQUIPMENT BY LAB ============
let searchTimeout;
function loadEquipmentByLab() {
    const selectedLab = document.getElementById('labLocation').value;
    const equipmentInput = document.getElementById('equipmentName');
    const qtyInput = document.getElementById('equipmentQty');
    const addBtn = document.getElementById('addEquipmentBtn');
    
    if (!equipmentInput || !qtyInput || !addBtn) return;
    
    if (selectedLab) {
        equipmentInput.disabled = false;
        equipmentInput.placeholder = `Search equipment in ${selectedLab}`;
        qtyInput.disabled = false;
        addBtn.disabled = false;
        equipmentInput.value = '';
        
        // Add event listener for search
        equipmentInput.removeEventListener('input', searchHandler);
        equipmentInput.addEventListener('input', searchHandler);
    } else {
        equipmentInput.disabled = true;
        equipmentInput.placeholder = 'Select lab location first';
        qtyInput.disabled = true;
        addBtn.disabled = true;
    }
}

// Search handler function
function searchHandler() {
    const lab = document.getElementById('labLocation').value;
    searchEquipmentByLab(lab);
}

function searchEquipmentByLab(labName) {
    clearTimeout(searchTimeout);
    const term = document.getElementById('equipmentName').value;
    
    if (term.length < 2) return; // Only search after 2 characters
    
    searchTimeout = setTimeout(() => {
        const labEquipment = equipmentByLab[labName] || [];
        const filtered = labEquipment.filter(item => 
            item.name.toLowerCase().includes(term.toLowerCase()) ||
            item.equipment_code.toLowerCase().includes(term.toLowerCase())
        );
        
        showEquipmentDropdown(filtered);
    }, 300);
}

// Show equipment dropdown
function showEquipmentDropdown(equipment) {
    let dropdown = document.getElementById('equipmentDropdown');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = 'equipmentDropdown';
        dropdown.className = 'dropdown-menu show position-absolute w-100';
        dropdown.style.maxHeight = '200px';
        dropdown.style.overflowY = 'auto';
        dropdown.style.zIndex = '1000';
        
        const parent = document.getElementById('equipmentName').parentNode;
        parent.style.position = 'relative';
        parent.appendChild(dropdown);
    }
    
    dropdown.innerHTML = '';
    
    if (equipment.length === 0) {
        const div = document.createElement('div');
        div.className = 'dropdown-item text-muted';
        div.textContent = 'No equipment found';
        dropdown.appendChild(div);
        return;
    }
    
    equipment.forEach(item => {
        const div = document.createElement('div');
        div.className = 'dropdown-item';
        div.style.cursor = 'pointer';
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span><strong>${item.name}</strong> (${item.equipment_code})</span>
                <span class="badge bg-success">Available: ${item.available_units || 0}/${item.total_units || 0}</span>
            </div>
        `;
        div.onclick = () => selectEquipment(item);
        dropdown.appendChild(div);
    });
}

// Select equipment
function selectEquipment(item) {
    document.getElementById('equipmentName').value = item.name;
    const qtyInput = document.getElementById('equipmentQty');
    qtyInput.max = item.available_units || 1;
    qtyInput.value = 1;
    document.getElementById('equipmentDropdown')?.remove();
}

// ============ ADD EQUIPMENT ============
function addEquipment() {
    const name = document.getElementById('equipmentName').value;
    const qty = parseInt(document.getElementById('equipmentQty').value);
    const selectedLab = document.getElementById('labLocation').value;
    
    if (!selectedLab) {
        showNotification('Please select a lab location first', 'warning');
        return;
    }
    
    if (!name || !qty) {
        showNotification('Please search and select equipment', 'warning');
        return;
    }
    
    // Get equipment from the current lab's list
    const labEquipment = equipmentByLab[selectedLab] || [];
    const equipment = labEquipment.find(e => e.name.toLowerCase() === name.toLowerCase());
    
    if (equipment) {
        // Check if quantity exceeds available units
        const existing = selectedEquipment.find(e => e.equipment_id === equipment.equipment_id);
        const currentQty = existing ? existing.qty : 0;
        
        if (currentQty + qty > equipment.available_units) {
            showNotification(`Only ${equipment.available_units} units available`, 'warning');
            return;
        }
        
        if (existing) {
            existing.qty += qty;
        } else {
            selectedEquipment.push({
                equipment_id: equipment.equipment_id,
                name: equipment.name,
                qty: qty,
                available: equipment.available_units || 0
            });
        }
        
        updateEquipmentTable();
        document.getElementById('equipmentName').value = '';
        document.getElementById('equipmentQty').value = 1;
        document.getElementById('equipmentDropdown')?.remove();
    } else {
        showNotification('Equipment not found in this lab', 'warning');
    }
}

// Update equipment table
function updateEquipmentTable() {
    const tbody = document.querySelector('#equipmentTable tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    selectedEquipment.forEach((item, index) => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${item.name}</td>
            <td>${item.qty}</td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="removeEquipment(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
    });
}

// Remove equipment
function removeEquipment(index) {
    selectedEquipment.splice(index, 1);
    updateEquipmentTable();
}

// Reset form
function resetForm() {
    const form = document.getElementById('equipmentRequestForm');
    if (form) form.reset();
    
    selectedEquipment = [];
    updateEquipmentTable();
    
    const today = new Date().toISOString().split('T')[0];
    const requestDate = document.getElementById('requestDate');
    if (requestDate) requestDate.value = today;
    
    // Disable equipment search until lab is selected
    const equipmentInput = document.getElementById('equipmentName');
    const qtyInput = document.getElementById('equipmentQty');
    const addBtn = document.getElementById('addEquipmentBtn');
    
    if (equipmentInput) {
        equipmentInput.disabled = true;
        equipmentInput.placeholder = 'Select lab location first';
    }
    if (qtyInput) qtyInput.disabled = true;
    if (addBtn) addBtn.disabled = true;
}

// Submit request
function submitRequest() {
    // Validate form
    if (selectedEquipment.length === 0) {
        showNotification('Please add at least one equipment', 'warning');
        return;
    }
    
    const required = ['labLocation', 'requestDate', 'continueDays'];
    for (const field of required) {
        if (!document.getElementById(field)?.value) {
            showNotification(`Please fill in all required fields`, 'warning');
            return;
        }
    }
    
    // Show success modal
    const modalTitle = document.getElementById('submissionModalTitle');
    const modalBody = document.getElementById('submissionModalBody');
    const modalHeader = document.getElementById('submissionModalHeader');
    
    if (modalTitle) modalTitle.textContent = '✅ Request Submitted!';
    if (modalHeader) modalHeader.className = 'modal-header bg-success text-white';
    if (modalBody) {
        modalBody.innerHTML = `
            <div class="text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Your equipment request has been submitted successfully!</h5>
                <p class="text-muted">Request ID: #REQ${Math.floor(Math.random() * 1000)}</p>
                <p>You will be notified once your request is processed.</p>
            </div>
        `;
    }
    
    const modalElement = document.getElementById('submissionModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Auto close after 3 seconds
        setTimeout(() => modal.hide(), 3000);
    }
    
    // Add to request status (mock)
    addToRequestStatus();
    
    resetForm();
}

// Add to request status (mock function)
function addToRequestStatus() {
    console.log('Request added to status');
    // In a real app, this would add to database
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}

// Load my requests
function loadMyRequests() {
    const tbody = document.getElementById('requestStatusBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    const mockRequests = [
        { reservation_id: 1, total_equipment: 2, request_date: '2026-02-20', lab_name: 'Lab 01', status: 'Pending', rejected_reason: null },
        { reservation_id: 2,  total_equipment: 1, request_date: '2026-02-21', lab_name: 'Research Lab', status: 'Pending', rejected_reason: null },
        { reservation_id: 3,  total_equipment: 1, request_date: '2026-02-19', lab_name: 'Lab 02', status: 'Rejected', rejected_reason: 'Equipment under maintenance' }
    ];
    
    mockRequests.forEach(req => {
        const row = tbody.insertRow();
        
        let statusBadge = '';
        if (req.status === 'Approved') {
            statusBadge = '<span class="badge bg-success">Approved</span>';
        } else if (req.status === 'Pending') {
            statusBadge = '<span class="badge bg-warning">Pending</span>';
        } else if (req.status === 'Rejected') {
            statusBadge = '<span class="badge bg-danger">Rejected</span>';
        }
        
        row.innerHTML = `
            <td><span class="fw-semibold">#REQ${String(req.reservation_id).padStart(3, '0')}</span></td>
         
            <td>${req.request_date}</td>
            <td>${req.lab_name}</td>
            <td>${statusBadge}</td>
            <td class="text-danger">${req.rejected_reason || '-'}</td>
        `;
    });
}

// ============ SIDEBAR FUNCTIONS ============
function toggleSidebar() {
    document.getElementById("sidebar")?.classList.toggle("active");
    document.getElementById("sidebarOverlay")?.classList.toggle("active");
}

function toggleNotifications() {
    document.getElementById("notificationDropdown")?.classList.toggle("show");
}

// Close notifications when clicking outside
document.addEventListener('click', function(event) {
    const bell = document.querySelector('.notification-bell');
    const dropdown = document.getElementById('notificationDropdown');
    if (bell && dropdown && !bell.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
    }
    
    // Close equipment dropdown when clicking outside
    if (!event.target.closest('#equipmentName') && !event.target.closest('#equipmentDropdown')) {
        const dropdown = document.getElementById('equipmentDropdown');
        if (dropdown) dropdown.remove();
    }
});

// Show different sections
function showSection(section) {
    const sections = ['dashboardSection', 'equipmentSection', 'historySection'];
    sections.forEach(s => {
        const el = document.getElementById(s);
        if (el) el.style.display = 'none';
    });

    const sectionEl = document.getElementById(section + 'Section');
    if (sectionEl) sectionEl.style.display = 'block';

    document.querySelectorAll('.sidebar a').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('onclick')?.includes(section)) {
            link.classList.add('active');
        }
    });

    if (section === 'history') {
        filterReservations();
    }
}

// ============ EQUIPMENT TABLE FUNCTIONS ============
let filteredEquipment = [...equipmentData];

function displayEquipmentTable(equipment) {
    const tableBody = document.getElementById('equipmentTableBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    equipment.forEach(item => {
        const row = document.createElement('tr');
        
        // All badges are green - no conditional logic needed
        const badgeColor = '#22c55e';
        
        row.innerHTML = `
            <td data-label="Image"><img src="${item.image}" class="equipment-image" alt="${item.name}" style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px;"></td>
            <td data-label="Name">${item.name}</td>
            <td data-label="Location">${item.location}</td>
            <td data-label="Availability">
                <span class="availability-badge" style="background: ${badgeColor}20; color: ${badgeColor}; padding: 4px 8px; border-radius: 4px; font-weight: 500;">
                    ${item.available}/${item.total}
                </span>
            </td>
            <td data-label="Action">
                <button class="btn-view" onclick="viewEquipmentDetails(${item.id})" title="View Details">
                    <i class="bi bi-eye"></i> View
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function viewEquipmentDetails(id) {
    const equipment = equipmentData.find(item => item.id === id);
    if (!equipment) return;
    
    const detailsContent = document.getElementById('equipmentDetailsContent');
    if (!detailsContent) return;
    
    // Determine status badge
    let statusBadge = '';
    if (equipment.status === 'available') {
        statusBadge = '<span class="badge bg-success">Available</span>';
    } else if (equipment.status === 'in-use') {
        statusBadge = '<span class="badge bg-warning">In Use</span>';
    } else {
        statusBadge = '<span class="badge bg-danger">Maintenance</span>';
    }
    
    detailsContent.innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center">
                <img src="${equipment.image}" style="width: 150px; height: 150px; object-fit: contain;" class="mb-3">
                <h4>${equipment.name}</h4>
                ${statusBadge}
            </div>
            <div class="col-md-8">
                <table class="table table-borderless">
                    <tr><th style="width: 150px;">Location:</th><td>${equipment.location}</td></tr>
                    <tr><th>Manufacturer:</th><td>${equipment.manufacturer}</td></tr>
                    <tr><th>Model:</th><td>${equipment.model}</td></tr>
                    <tr><th>Today Availability:</th><td><span class="availability-badge" style="background: #22c55e20; color: #22c55e; padding: 4px 8px; border-radius: 4px; font-weight: 500;">${equipment.available}/${equipment.total}</span></td></tr>
                    <tr><th>Description:</th><td>${equipment.description}</td></tr>
                </table>
            </div>
        </div>
    `;
    
    const modalElement = document.getElementById('equipmentDetailsModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

// ============ RESERVATION HISTORY FUNCTIONS ============
function displayReservationHistory(reservations) {
    const tableBody = document.getElementById('reservationHistoryBody');
    if (!tableBody) return;
    
    // Sort by date (newest first)
    reservations.sort((a, b) => b.timestamp - a.timestamp);
    
    tableBody.innerHTML = '';
    
    reservations.forEach(res => {
        const row = document.createElement('tr');
        
        // Determine status badge
        let statusBadge = '';
        if (res.status === 'completed') {
            statusBadge = '<span class="badge bg-success">Completed</span>';
        } else {
            statusBadge = '<span class="badge bg-warning">Pending</span>';
        }
        
        row.innerHTML = `
            <td data-label="Reservation ID">${res.id}</td>
            <td data-label="Date & Time">${res.dateTime}</td>
            <td data-label="Location">${res.location}</td>
            <td data-label="Status">${statusBadge}</td>
            <td data-label="Action">
                <button class="btn-view" onclick="viewReservationDetails('${res.id}')" title="View Details">
                    <i class="bi bi-eye"></i> View
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// View reservation details function
function viewReservationDetails(reservationId) {
    const reservation = reservationHistoryData.find(item => item.id === reservationId);
    if (!reservation) return;
    
    // Check if modal exists, if not create it
    let modalElement = document.getElementById('reservationDetailsModal');
    
    if (!modalElement) {
        // Create modal dynamically
        modalElement = document.createElement('div');
        modalElement.className = 'modal fade';
        modalElement.id = 'reservationDetailsModal';
        modalElement.setAttribute('tabindex', '-1');
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-calendar-check me-2"></i>
                            Reservation Details
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="reservationDetailsContent">
                        <!-- Content will be populated here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalElement);
    }
    
    // Determine status badge for modal
    let statusBadge = '';
    if (reservation.status === 'completed') {
        statusBadge = '<span class="badge bg-success">Completed</span>';
    } else {
        statusBadge = '<span class="badge bg-warning">Pending</span>';
    }
    
    // Populate modal content with all details
    const detailsContent = document.getElementById('reservationDetailsContent');
    if (detailsContent) {
        detailsContent.innerHTML = `
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Reservation: ${reservation.id}</h4>
                        ${statusBadge}
                    </div>
                    
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">Date & Time:</th>
                            <td>${reservation.dateTime}</td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td>${reservation.location}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>${statusBadge}</td>
                        </tr>
                        <tr>
                            <th>Equipment:</th>
                            <td>${reservation.equipment || 'Not specified'}</td>
                        </tr>
                        <tr>
                            <th>Duration:</th>
                            <td>${reservation.duration || 'Not specified'}</td>
                        </tr>
                        <tr>
                            <th>Purpose:</th>
                            <td>${reservation.purpose || 'Not specified'}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
    }
    
    // Show modal
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function filterReservations() {
    const filter = document.getElementById('timeFilter')?.value || 'all';
    const today = new Date();
    let filtered = [];
    
    if (filter === 'weekly') {
        const weekAgo = new Date();
        weekAgo.setDate(today.getDate() - 7);
        filtered = reservationHistoryData.filter(item => item.timestamp >= weekAgo);
    } else if (filter === 'monthly') {
        const monthAgo = new Date();
        monthAgo.setDate(today.getDate() - 30);
        filtered = reservationHistoryData.filter(item => item.timestamp >= monthAgo);
    } else {
        filtered = reservationHistoryData;
    }
    
    displayReservationHistory(filtered);
}

// ============ CALENDAR FUNCTIONS ============
function loadEvents() {
    const saved = localStorage.getItem("studentCalendarEvents");
    if (saved) {
        eventsArr = JSON.parse(saved);
    }
}

function saveEvents() {
    localStorage.setItem("studentCalendarEvents", JSON.stringify(eventsArr));
}

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

    // Previous month days
    for (let x = day; x > 0; x--) {
        days += `<div class="day-cell prev-date">${prevDays - x + 1}</div>`;
    }

    // Current month days
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

    // Next month days
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

    if (eventDayEl) eventDayEl.innerHTML = dayName;
    if (eventDateEl) eventDateEl.innerHTML = `${day} ${months[month]} ${year}`;

    let eventsHtml = "";
    eventsArr.forEach(event => {
        if (event.day === day && event.month === month + 1 && event.year === year) {
            event.events.forEach(ev => {
                eventsHtml += `
                    <div class="event-item">
                        <div class="title">
                            <i class="fas fa-circle"></i>
                            <span class="event-title">${ev.title}</span>
                        </div>
                        <div class="event-time">${ev.time}</div>
                        <div class="event-time" style="margin-left: 28px; font-size: 0.8rem;">${ev.details || ''}</div>
                    </div>
                `;
            });
        }
    });

    if (eventsHtml === "") {
        eventsHtml = '<div class="no-event">No bookings scheduled</div>';
    }

    const eventsList = document.getElementById('eventsList');
    if (eventsList) {
        eventsList.innerHTML = eventsHtml;
    }
}

// Calendar Navigation
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

// ============ ADD EVENT MODAL ============
const closeEventBtn = document.getElementById('closeEventBtn');
if (closeEventBtn) {
    closeEventBtn.addEventListener('click', () => {
        document.getElementById('addEventWrapper')?.classList.remove('active');
    });
}

window.addEventListener('click', (e) => {
    if (e.target === document.getElementById('addEventWrapper')) {
        document.getElementById('addEventWrapper')?.classList.remove('active');
    }
});

const addEventSubmit = document.getElementById('addEventSubmit');
if (addEventSubmit) {
    addEventSubmit.addEventListener('click', () => {
        const title = document.getElementById('eventName')?.value.trim();
        const details = document.getElementById('eventDetails')?.value.trim();
        const from = document.getElementById('eventTimeFrom')?.value;
        const to = document.getElementById('eventTimeTo')?.value;

        if (!title || !from || !to) {
            alert('Please fill all fields');
            return;
        }

        const requestDate = new Date(year, month, activeDay || new Date().getDate());
        const eventDay = requestDate.getDate();
        const eventMonth = requestDate.getMonth() + 1;
        const eventYear = requestDate.getFullYear();

        const newEvent = {
            title: title,
            time: `${from} - ${to}`,
            details: details
        };

        let added = false;
        eventsArr.forEach(event => {
            if (event.day === eventDay && event.month === eventMonth && event.year === eventYear) {
                event.events.push(newEvent);
                added = true;
            }
        });

        if (!added) {
            eventsArr.push({
                day: eventDay,
                month: eventMonth,
                year: eventYear,
                events: [newEvent]
            });
        }

        saveEvents();
        initCalendar();

        // Add notification
        addNotification('New equipment request submitted', 'info');

        document.getElementById('addEventWrapper')?.classList.remove('active');
        alert('Equipment request submitted successfully!');
    });
}

// Add notification function
function addNotification(message, type) {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        const currentCount = parseInt(badge.textContent);
        badge.textContent = currentCount + 1;
    }

    const list = document.getElementById('notificationList');
    if (list) {
        const newNotif = document.createElement('div');
        newNotif.className = 'notification-item unread';
        newNotif.innerHTML = `
            <div><i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'info-circle-fill'} me-2"></i> ${message}</div>
            <div class="time">Just now</div>
        `;
        list.insertBefore(newNotif, list.firstChild);
    }
}
</script>
</body>
</html>
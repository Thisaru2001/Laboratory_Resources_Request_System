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
            background: rgba(0,0,0,0.5);
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
            box-shadow: 4px 0 20px rgba(0,0,0,0.2);
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
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            pointer-events: none;
        }

        .sidebar a {
            color: rgba(255,255,255,0.9);
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
            background: rgba(255,255,255,0.15);
            transform: translateX(8px);
            color: white;
        }

        .sidebar a.active {
            background: rgba(255,255,255,0.2);
            border-left: 3px solid #ffd700;
        }

        .sidebar h4 {
            padding: 28px 24px;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 600;
            border-bottom: 2px solid rgba(255,255,255,0.2);
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
            background: rgba(0,0,0,0.2);
            font-size: 0.85rem;
            color: rgba(255,255,255,0.7);
        }

        /* Main content */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        /* Modern Topbar with Rounded Navbar */
        .topbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 12px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 15px;
            z-index: 999;
            border-bottom: 1px solid rgba(255,255,255,0.3);
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

        /* Notification Bell - Only one bell for student */
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
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
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
            background: rgba(255,255,255,0.2);
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
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            min-height: calc(100vh - 80px);
        }

        /* Modern Cards */
        .card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }

        /* Analytics Cards - Simplified for student */
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
            background: rgba(255,255,255,0.1);
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

        /* Equipment Browser */
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

        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .equipment-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s;
            cursor: pointer;
        }

        .equipment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(34, 197, 94, 0.2);
        }

        .equipment-image {
            height: 180px;
            background: linear-gradient(135deg, #22c55e20, #16a34a20);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .equipment-image img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }

        .status-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: blink 2s infinite;
        }

        .status-indicator.available {
            background: #22c55e;
            box-shadow: 0 0 10px #22c55e;
        }

        .status-indicator.in-use {
            background: #f59e0b;
            box-shadow: 0 0 10px #f59e0b;
        }

        .status-indicator.maintenance {
            background: #ef4444;
            box-shadow: 0 0 10px #ef4444;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .equipment-info {
            padding: 20px;
        }

        .equipment-info h5 {
            font-weight: 600;
            margin-bottom: 8px;
            color: #166534;
        }

        .equipment-info p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .equipment-info .location {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #22c55e;
            font-weight: 500;
        }

        /* Comment Textarea */
        .comment-textarea {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid #f0f0f0;
            border-radius: 18px;
            outline: none;
            transition: all 0.3s;
            font-size: 1rem;
            font-family: inherit;
            min-height: 100px;
            resize: vertical;
        }

        .comment-textarea:focus {
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
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .calendar-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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

        .goto-btn, .today-btn {
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

        .goto-btn:hover, .today-btn:hover {
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
            color: rgba(255,255,255,0.7);
            font-size: 1.1rem;
        }

        .events-list {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .event-item {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 18px 20px;
            border-radius: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .event-item:hover {
            background: rgba(255,255,255,0.2);
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
            color: rgba(255,255,255,0.7);
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
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
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
            color: rgba(255,255,255,0.5);
            font-size: 1.1rem;
        }

        /* Buttons */
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
        .form-control, .form-select {
            border: 2px solid #f0f0f0;
            border-radius: 16px;
            padding: 12px 16px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
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
            
            .equipment-grid {
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
    </style>
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR - Student Version -->
    <div class="sidebar" id="sidebar">
        <h4><i class="bi bi-flask"></i> MicroLab Student</h4>
        <a onclick="showSection('dashboard')" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a onclick="showSection('equipment')"><i class="bi bi-tools"></i> Equipment Browser</a>
        <a onclick="showSection('history')"><i class="bi bi-clock-history"></i> History</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        
        <div class="sidebar-footer">
            <i class="bi bi-building"></i><br>
            Microbiology Lab<br>
            University of Kelaniya
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <!-- TOPBAR with Notification Bell (Only one bell) -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn d-lg-none text-dark" onclick="toggleSidebar()">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, #22c55e, #16a34a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Student Dashboard</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Single Notification Bell -->
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
                        <div class="notification-item">
                            <div><i class="bi bi-check-circle-fill text-success me-2"></i> Your booking #REQ003 completed</div>
                            <div class="time">2 hours ago</div>
                        </div>
                    </div>
                </div>

                <span class="fw-semibold d-none d-sm-block" style="color: #166534;">John Doe</span>
                <div class="dropdown">
                    <img src="https://ui-avatars.com/api/?name=John+Doe&background=22c55e&color=fff&size=100" class="profile-img dropdown-toggle" data-bs-toggle="dropdown">
                    <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">

            <!-- Dashboard Section (Contains Create Request, Request Status, and Calendar) -->
            <div id="dashboardSection">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Student Dashboard</h3>
                
                <!-- Student Stats -->
                <div class="analytics-grid mb-4">
                    <div class="stat-card">
                        <i class="bi bi-calendar-check"></i>
                        <h3>5</h3>
                        <p>My Bookings</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-clock-history"></i>
                        <h3>12.5</h3>
                        <p>Hours Used</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-tools"></i>
                        <h3>3</h3>
                        <p>Equipment Used</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-hourglass-split"></i>
                        <h3>2</h3>
                        <p>Pending Requests</p>
                    </div>
                </div>

                <!-- Create Request Section -->
                <h4 class="mb-3" style="color: white;">Create Equipment Request</h4>
                <div class="card p-4 mb-4">
                    <form id="equipmentRequestForm" onsubmit="return submitEquipmentRequest(event)">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Search Equipment</label>
                                <input type="text" id="equipmentName" class="form-control" placeholder="Enter equipment name">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Quantity</label>
                                <input type="number" id="equipmentQty" class="form-control" min="1" value="1">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-success w-100" onclick="addEquipment()">
                                    <i class="bi bi-plus-circle me-1"></i>Add
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered mb-4" id="equipmentTable">
                                <thead class="table-success">
                                    <tr>
                                        <th>Equipment</th>
                                        <th>Qty</th>
                                        <th>Status</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lab Location</label>
                                <select id="labLocation" class="form-select" required>
                                    <option value="" disabled selected>Select Lab</option>
                                    <option value="Microbiology Lab 01">Microbiology Lab 01</option>
                                    <option value="Microbiology Lab 02">Microbiology Lab 02</option>
                                    <option value="Research Laboratory">Research Laboratory</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Requested Day</label>
                                <input type="date" id="requestDate" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Time</label>
                                <input type="time" id="startTime" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">End Time</label>
                                <input type="time" id="endTime" class="form-control" required>
                            </div>
                        </div>

                        <!-- Comment Textarea -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Additional Comments</label>
                            <textarea id="requestComment" class="comment-textarea" placeholder="Enter any special requirements or notes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send me-1"></i>Submit Request
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
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
                                    <th>Equipment</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Lab</th>
                                    <th>Status</th>
                                    <th>Reason (if rejected)</th>
                                </tr>
                            </thead>
                            <tbody id="requestStatusBody">
                                <tr>
                                    <td><span class="fw-semibold">#REQ001</span></td>
                                    <td>Microscope (2)</td>
                                    <td>2026-02-20</td>
                                    <td>10:00 - 12:00</td>
                                    <td>Lab 01</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">#REQ002</span></td>
                                    <td>Centrifuge (1)</td>
                                    <td>2026-02-21</td>
                                    <td>14:00 - 16:00</td>
                                    <td>Research Lab</td>
                                    <td><span class="badge bg-success">Approved</span></td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">#REQ003</span></td>
                                    <td>Incubator (1)</td>
                                    <td>2026-02-19</td>
                                    <td>09:00 - 11:00</td>
                                    <td>Lab 02</td>
                                    <td><span class="badge bg-danger">Rejected</span></td>
                                    <td class="text-danger">Equipment under maintenance</td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">#REQ004</span></td>
                                    <td>Autoclave (1)</td>
                                    <td>2026-02-22</td>
                                    <td>13:00 - 15:00</td>
                                    <td>Lab 01</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                    <td>-</td>
                                </tr>
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

            <!-- Equipment Browser Section -->
            <div id="equipmentSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment Browser</h3>
                
                <div class="card p-4">
                    <div class="filter-section">
                        <select class="filter-select" id="labFilter" onchange="filterEquipment()">
                            <option value="all">All Labs</option>
                            <option value="lab1">Microbiology Lab 01</option>
                            <option value="lab2">Microbiology Lab 02</option>
                            <option value="research">Research Laboratory</option>
                        </select>
                        
                        <select class="filter-select" id="statusFilter" onchange="filterEquipment()">
                            <option value="all">All Status</option>
                            <option value="available">Available</option>
                            <option value="in-use">In Use</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div class="equipment-grid" id="equipmentGrid">
                        <!-- Equipment cards will be populated here -->
                    </div>
                </div>
            </div>

            <!-- History Section (Student's Booking History) -->
            <div id="historySection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">My Booking History</h3>
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Lab</th>
                                    <th>Equipment</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="bookingHistoryBody">
                                <tr>
                                    <td>#REQ001</td>
                                    <td>2026-02-10</td>
                                    <td>Lab 01</td>
                                    <td>Microscope (2)</td>
                                    <td>3 hours</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#REQ002</td>
                                    <td>2026-02-15</td>
                                    <td>Research Lab</td>
                                    <td>Centrifuge (1)</td>
                                    <td>4 hours</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>#REQ003</td>
                                    <td>2026-02-18</td>
                                    <td>Lab 02</td>
                                    <td>Incubator (1)</td>
                                    <td>2 hours</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#REQ004</td>
                                    <td>2026-02-19</td>
                                    <td>Lab 01</td>
                                    <td>Autoclave (1)</td>
                                    <td>2 hours</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#REQ005</td>
                                    <td>2026-02-05</td>
                                    <td>Research Lab</td>
                                    <td>pH Meter (1)</td>
                                    <td>1.5 hours</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Event Modal -->
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
        // Toggle Sidebar
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
            document.getElementById("sidebarOverlay").classList.toggle("active");
        }

        // Toggle Notifications
        function toggleNotifications() {
            document.getElementById("notificationDropdown").classList.toggle("show");
        }

        // Close notifications when clicking outside
        document.addEventListener('click', function(event) {
            const bell = document.querySelector('.notification-bell');
            const dropdown = document.getElementById('notificationDropdown');
            if (bell && dropdown && !bell.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Show different sections
        function showSection(section) {
            // Hide all sections
            document.getElementById('dashboardSection').style.display = 'none';
            document.getElementById('equipmentSection').style.display = 'none';
            document.getElementById('historySection').style.display = 'none';
            
            // Show selected section
            document.getElementById(section + 'Section').style.display = 'block';
            
            // Update active state in sidebar
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(section)) {
                    link.classList.add('active');
                }
            });
            
            // Initialize equipment grid if showing equipment section
            if (section === 'equipment') {
                initEquipmentGrid();
            }
        }

        // Equipment Browser Functions
        const equipmentData = [
            {
                name: 'Microscope',
                image: 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png',
                location: 'Microbiology Lab 01',
                status: 'available',
                lab: 'lab1'
            },
            {
                name: 'Centrifuge',
                image: 'https://cdn-icons-png.flaticon.com/512/2941/2941543.png',
                location: 'Research Laboratory',
                status: 'in-use',
                lab: 'research'
            },
            {
                name: 'Incubator',
                image: 'https://cdn-icons-png.flaticon.com/512/2941/2941538.png',
                location: 'Microbiology Lab 02',
                status: 'maintenance',
                lab: 'lab2'
            },
            {
                name: 'Autoclave',
                image: 'https://cdn-icons-png.flaticon.com/512/2941/2941521.png',
                location: 'Microbiology Lab 01',
                status: 'available',
                lab: 'lab1'
            },
            {
                name: 'pH Meter',
                image: 'https://cdn-icons-png.flaticon.com/512/2941/2941556.png',
                location: 'Research Laboratory',
                status: 'available',
                lab: 'research'
            },
            {
                name: 'Water Bath',
                image: 'https://cdn-icons-png.flaticon.com/512/2941/2941578.png',
                location: 'Microbiology Lab 02',
                status: 'in-use',
                lab: 'lab2'
            }
        ];

        function initEquipmentGrid() {
            const grid = document.getElementById('equipmentGrid');
            if (!grid) return;
            
            grid.innerHTML = '';
            
            const labFilter = document.getElementById('labFilter')?.value || 'all';
            const statusFilter = document.getElementById('statusFilter')?.value || 'all';
            
            equipmentData.forEach(item => {
                if ((labFilter === 'all' || item.lab === labFilter) && 
                    (statusFilter === 'all' || item.status === statusFilter)) {
                    
                    const card = document.createElement('div');
                    card.className = 'equipment-card';
                    card.innerHTML = `
                        <div class="equipment-image">
                            <img src="${item.image}" alt="${item.name}">
                            <div class="status-indicator ${item.status === 'available' ? 'available' : item.status === 'in-use' ? 'in-use' : 'maintenance'}"></div>
                        </div>
                        <div class="equipment-info">
                            <h5>${item.name}</h5>
                            <p><i class="bi bi-geo-alt"></i> ${item.location}</p>
                            <div class="location">
                                <span class="badge bg-${item.status === 'available' ? 'success' : item.status === 'in-use' ? 'warning' : 'danger'}">${item.status}</span>
                            </div>
                        </div>
                    `;
                    grid.appendChild(card);
                }
            });
        }

        function filterEquipment() {
            initEquipmentGrid();
        }

        // Request Management
        function getStatusBadge() {
            const statuses = [
                ["Available", "success"],
                ["In Use", "warning"],
                ["Pending", "warning"],
                ["Damaged", "danger"],
                ["Out of Service", "secondary"]
            ];
            let s = statuses[Math.floor(Math.random() * statuses.length)];
            return `<span class="badge bg-${s[1]}">${s[0]}</span>`;
        }

        function addEquipment() {
            let name = document.getElementById("equipmentName").value.trim();
            let qty = document.getElementById("equipmentQty").value;

            if (!name) {
                alert("Please enter equipment name");
                return;
            }
            if (!qty || qty < 1) {
                alert("Please enter valid quantity");
                return;
            }

            let table = document.querySelector("#equipmentTable tbody");
            let row = table.insertRow();
            row.innerHTML = `
                <td>${name}</td>
                <td>${qty}</td>
                <td>${getStatusBadge()}</td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
            `;

            document.getElementById("equipmentName").value = "";
            document.getElementById("equipmentQty").value = "1";
        }

        function submitEquipmentRequest(event) {
            event.preventDefault();
            
            const date = document.getElementById('requestDate').value;
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;
            const labLocation = document.getElementById('labLocation').value;
            const comment = document.getElementById('requestComment').value;

            const equipmentRows = document.querySelectorAll("#equipmentTable tbody tr");
            if (equipmentRows.length === 0) {
                alert("Please add at least one equipment");
                return false;
            }

            const today = new Date().toISOString().split('T')[0];
            if (date < today) {
                alert("Past dates are not allowed");
                return false;
            }

            if (!startTime || !endTime) {
                alert("Please select start and end times");
                return false;
            }

            if (endTime <= startTime) {
                alert("End time must be after start time");
                return false;
            }

            const s = new Date(`1970-01-01T${startTime}`);
            const e = new Date(`1970-01-01T${endTime}`);
            if ((e - s) / (1000 * 60 * 60) > 8) {
                alert("Maximum booking duration is 8 hours");
                return false;
            }

            let equipmentList = [];
            equipmentRows.forEach(row => {
                const cells = row.cells;
                equipmentList.push(`${cells[0].textContent} (${cells[1].textContent})`);
            });

            const eventTitle = `Equipment Request: ${labLocation}`;
            
            document.getElementById('eventName').value = eventTitle;
            document.getElementById('eventDetails').value = 
                `Equipment: ${equipmentList.join(', ')}\nComment: ${comment || 'No comments'}`;
            document.getElementById('eventTimeFrom').value = startTime;
            document.getElementById('eventTimeTo').value = endTime;

            window.currentRequestData = {
                date: date,
                labLocation: labLocation,
                equipmentList: equipmentList,
                comment: comment,
                startTime: startTime,
                endTime: endTime
            };

            document.getElementById('addEventWrapper').classList.add('active');
            
            return false;
        }

        // Calendar Implementation
        let activeDay;
        let month = new Date().getMonth();
        let year = new Date().getFullYear();

        const months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        let eventsArr = [
            {
                day: 20,
                month: 2,
                year: 2026,
                events: [
                    {
                        title: "Microscope Booking",
                        time: "10:00 AM - 12:00 PM",
                        details: "Lab 01"
                    }
                ]
            },
            {
                day: 21,
                month: 2,
                year: 2026,
                events: [
                    {
                        title: "Centrifuge Booking",
                        time: "2:00 PM - 4:00 PM",
                        details: "Research Lab"
                    }
                ]
            },
            {
                day: 22,
                month: 2,
                year: 2026,
                events: [
                    {
                        title: "Autoclave Booking",
                        time: "1:00 PM - 3:00 PM",
                        details: "Lab 01"
                    }
                ]
            }
        ];

        // Load events from localStorage
        function loadEvents() {
            const saved = localStorage.getItem("studentCalendarEvents");
            if (saved) {
                eventsArr = JSON.parse(saved);
            }
        }
        loadEvents();

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
            
            // Update event display for current active day or today
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
                        // Remove active class from all cells
                        dayCells.forEach(c => c.classList.remove('active'));
                        // Add active class to clicked cell
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

            // Show events for this day
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

        // Navigation
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

        // Modal close functionality
        const addEventWrapper = document.getElementById('addEventWrapper');
        const closeEventBtn = document.getElementById('closeEventBtn');

        if (closeEventBtn) {
            closeEventBtn.addEventListener('click', () => {
                addEventWrapper.classList.remove('active');
            });
        }

        window.addEventListener('click', (e) => {
            if (e.target === addEventWrapper) {
                addEventWrapper.classList.remove('active');
            }
        });

        // Confirm Request Submit
        const addEventSubmit = document.getElementById('addEventSubmit');
        if (addEventSubmit) {
            addEventSubmit.addEventListener('click', () => {
                const title = document.getElementById('eventName').value.trim();
                const details = document.getElementById('eventDetails').value.trim();
                const from = document.getElementById('eventTimeFrom').value;
                const to = document.getElementById('eventTimeTo').value;

                if (!title || !from || !to) {
                    alert('Please fill all fields');
                    return;
                }

                // Add event to calendar
                const requestDate = window.currentRequestData ? new Date(window.currentRequestData.date) : new Date(year, month, activeDay || new Date().getDate());
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
                
                // Clear form
                document.getElementById('equipmentRequestForm').reset();
                const tableBody = document.querySelector("#equipmentTable tbody");
                if (tableBody) tableBody.innerHTML = '';
                
                // Close modal
                addEventWrapper.classList.remove('active');
                
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

        // Set min date for request
        const today = new Date().toISOString().split('T')[0];
        const requestDate = document.getElementById('requestDate');
        if (requestDate) {
            requestDate.min = today;
        }

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            showSection('dashboard');
            initEquipmentGrid();
            initCalendar();
        });
    </script>
</body>
</html>
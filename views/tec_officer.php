<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Technical Officer Dashboard - Microbiology Lab</title>

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

        /* Equipment Management Table */
        .management-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .management-table th {
            padding: 15px 20px;
            text-align: left;
            color: #166534;
            font-weight: 600;
        }

        .management-table td {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .management-table tr:hover td {
            transform: scale(1.01);
            box-shadow: 0 5px 20px rgba(34, 197, 94, 0.2);
            transition: all 0.3s;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-badge.available { background: #e8f5e9; color: #2e7d32; }
        .status-badge.in-use { background: #fff3e0; color: #ef6c00; }
        .status-badge.maintenance { background: #ffebee; color: #c62828; }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-maintenance {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-maintenance:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(245, 158, 11, 0.4);
        }

        .btn-repair {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-repair:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        /* Request List Table */
        .request-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .request-table th {
            padding: 15px 20px;
            text-align: left;
            color: #166534;
            font-weight: 600;
        }

        .request-table td {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .request-table tr:hover td {
            transform: scale(1.01);
            box-shadow: 0 5px 20px rgba(34, 197, 94, 0.2);
            transition: all 0.3s;
        }

        .btn-approve {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-approve:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .btn-reject {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-reject:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
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
            
            .request-table td {
                display: block;
            }
            
            .request-table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #166534;
                display: block;
                margin-bottom: 5px;
            }
            
            .management-table td {
                display: block;
            }
            
            .management-table td::before {
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
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-maintenance, .btn-repair, .btn-approve, .btn-reject {
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

    <!-- SIDEBAR - Technical Officer Version -->
    <div class="sidebar" id="sidebar">
        <h4><i class="bi bi-flask"></i> MicroLab Tech</h4>
        <a onclick="showSection('dashboard')" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a onclick="showSection('requests')"><i class="bi bi-list-check"></i> Requests List</a>
        <a onclick="showSection('management')"><i class="bi bi-gear"></i> Equipment Management</a>
        <a onclick="showSection('equipment')"><i class="bi bi-tools"></i> Equipment Browser</a>
        <a onclick="showSection('history')"><i class="bi bi-clock-history"></i> Booking History</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        
        <div class="sidebar-footer">
            <i class="bi bi-building"></i><br>
            Microbiology Lab<br>
            University of Kelaniya
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <!-- TOPBAR with Notification Bell -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn d-lg-none text-dark" onclick="toggleSidebar()">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, #22c55e, #16a34a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Technical Officer Dashboard</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Single Notification Bell -->
                <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge">5</span>
                </div>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h6>Notifications</h6>
                        <span>5 new</span>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-item unread">
                            <div><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Microscope #MIC-001 needs maintenance</div>
                            <div class="time">10 minutes ago</div>
                        </div>
                        <div class="notification-item unread">
                            <div><i class="bi bi-clock-fill text-warning me-2"></i> New request #REQ005 pending</div>
                            <div class="time">25 minutes ago</div>
                        </div>
                        <div class="notification-item unread">
                            <div><i class="bi bi-check-circle-fill text-success me-2"></i> Equipment returned: Centrifuge</div>
                            <div class="time">1 hour ago</div>
                        </div>
                        <div class="notification-item unread">
                            <div><i class="bi bi-info-circle-fill text-info me-2"></i> Maintenance scheduled for tomorrow</div>
                            <div class="time">2 hours ago</div>
                        </div>
                        <div class="notification-item unread">
                            <div><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Broken equipment report: Incubator</div>
                            <div class="time">3 hours ago</div>
                        </div>
                    </div>
                </div>

                <span class="fw-semibold d-none d-sm-block" style="color: #166534;">Tech Officer</span>
                <div class="dropdown">
                    <img src="https://ui-avatars.com/api/?name=Tech+Officer&background=22c55e&color=fff&size=100" class="profile-img dropdown-toggle" data-bs-toggle="dropdown">
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

            <!-- Dashboard Section -->
            <div id="dashboardSection">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Dashboard Overview</h3>
                
                <!-- Equipment Status Cards -->
                <div class="analytics-grid">
                    <div class="stat-card">
                        <i class="bi bi-tools"></i>
                        <h3>24</h3>
                        <p>Total Available Equipment</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h3>3</h3>
                        <p>Broken Equipment</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-gear"></i>
                        <h3>5</h3>
                        <p>Maintenance Pending</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-calendar-check"></i>
                        <h3>12</h3>
                        <p>Today's Bookings</p>
                    </div>
                </div>

                <!-- Quick Stats Row -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">In Use Equipment</h6>
                            <h3 class="text-warning">8</h3>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Pending Requests</h6>
                            <h3 class="text-info">6</h3>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Completed Today</h6>
                            <h3 class="text-success">4</h3>
                        </div>
                    </div>
                </div>

                <!-- Booked Calendar Section -->
                <h4 class="mb-3" style="color: white;">Equipment Booking Calendar</h4>
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

            <!-- Requests List Section -->
            <div id="requestsSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Student Requests</h3>
                
                <div class="card p-4">
                    <div class="filter-section">
                        <select class="filter-select" id="requestFilter" onchange="filterRequests()">
                            <option value="all">All Requests</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="request-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Equipment</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Lab</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="requestListBody">
                                <tr>
                                    <td data-label="ID">#REQ001</td>
                                    <td data-label="Student">John Doe</td>
                                    <td data-label="Equipment">Microscope (2)</td>
                                    <td data-label="Date">2026-02-20</td>
                                    <td data-label="Time">10:00 - 12:00</td>
                                    <td data-label="Lab">Lab 01</td>
                                    <td data-label="Status"><span class="badge bg-warning">Pending</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-approve" onclick="approveRequest('REQ001')">Approve</button>
                                            <button class="btn-reject" onclick="rejectRequest('REQ001')">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">#REQ002</td>
                                    <td data-label="Student">Jane Smith</td>
                                    <td data-label="Equipment">Centrifuge (1)</td>
                                    <td data-label="Date">2026-02-21</td>
                                    <td data-label="Time">14:00 - 16:00</td>
                                    <td data-label="Lab">Research Lab</td>
                                    <td data-label="Status"><span class="badge bg-success">Approved</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <span class="text-success">Ready for pickup</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">#REQ003</td>
                                    <td data-label="Student">Mike Johnson</td>
                                    <td data-label="Equipment">Incubator (1)</td>
                                    <td data-label="Date">2026-02-19</td>
                                    <td data-label="Time">09:00 - 11:00</td>
                                    <td data-label="Lab">Lab 02</td>
                                    <td data-label="Status"><span class="badge bg-danger">Rejected</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <span class="text-danger">Under maintenance</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">#REQ004</td>
                                    <td data-label="Student">Sarah Wilson</td>
                                    <td data-label="Equipment">Autoclave (1)</td>
                                    <td data-label="Date">2026-02-22</td>
                                    <td data-label="Time">13:00 - 15:00</td>
                                    <td data-label="Lab">Lab 01</td>
                                    <td data-label="Status"><span class="badge bg-warning">Pending</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-approve" onclick="approveRequest('REQ004')">Approve</button>
                                            <button class="btn-reject" onclick="rejectRequest('REQ004')">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">#REQ005</td>
                                    <td data-label="Student">Alex Chen</td>
                                    <td data-label="Equipment">pH Meter (1)</td>
                                    <td data-label="Date">2026-02-23</td>
                                    <td data-label="Time">11:00 - 13:00</td>
                                    <td data-label="Lab">Research Lab</td>
                                    <td data-label="Status"><span class="badge bg-warning">Pending</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-approve" onclick="approveRequest('REQ005')">Approve</button>
                                            <button class="btn-reject" onclick="rejectRequest('REQ005')">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Equipment Management Section -->
            <div id="managementSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment Management</h3>
                
                <div class="card p-4">
                    <div class="filter-section">
                        <select class="filter-select" id="statusFilter" onchange="filterManagement()">
                            <option value="all">All Equipment</option>
                            <option value="available">Available</option>
                            <option value="in-use">In Use</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="broken">Broken</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="management-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Equipment</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Last Maintained</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="managementBody">
                                <tr>
                                    <td data-label="Code">MIC-001</td>
                                    <td data-label="Equipment">Microscope</td>
                                    <td data-label="Location">Lab 01</td>
                                    <td data-label="Status"><span class="status-badge available">Available</span></td>
                                    <td data-label="Last Maintained">2026-02-15</td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-maintenance" onclick="scheduleMaintenance('MIC-001')">Maintenance</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="Code">CEN-002</td>
                                    <td data-label="Equipment">Centrifuge</td>
                                    <td data-label="Location">Research Lab</td>
                                    <td data-label="Status"><span class="status-badge in-use">In Use</span></td>
                                    <td data-label="Last Maintained">2026-02-10</td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-maintenance" onclick="scheduleMaintenance('CEN-002')">Maintenance</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="Code">INC-003</td>
                                    <td data-label="Equipment">Incubator</td>
                                    <td data-label="Location">Lab 02</td>
                                    <td data-label="Status"><span class="status-badge maintenance">Maintenance</span></td>
                                    <td data-label="Last Maintained">2026-02-01</td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-repair" onclick="markRepaired('INC-003')">Mark Repaired</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="Code">AUT-004</td>
                                    <td data-label="Equipment">Autoclave</td>
                                    <td data-label="Location">Lab 01</td>
                                    <td data-label="Status"><span class="status-badge broken" style="background:#ffebee; color:#c62828;">Broken</span></td>
                                    <td data-label="Last Maintained">2026-01-28</td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-repair" onclick="markRepaired('AUT-004')">Mark Repaired</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="Code">PHM-005</td>
                                    <td data-label="Equipment">pH Meter</td>
                                    <td data-label="Location">Research Lab</td>
                                    <td data-label="Status"><span class="status-badge available">Available</span></td>
                                    <td data-label="Last Maintained">2026-02-18</td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-maintenance" onclick="scheduleMaintenance('PHM-005')">Maintenance</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="Code">WAT-006</td>
                                    <td data-label="Equipment">Water Bath</td>
                                    <td data-label="Location">Lab 02</td>
                                    <td data-label="Status"><span class="status-badge in-use">In Use</span></td>
                                    <td data-label="Last Maintained">2026-02-12</td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-maintenance" onclick="scheduleMaintenance('WAT-006')">Maintenance</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                        
                        <select class="filter-select" id="equipStatusFilter" onchange="filterEquipment()">
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

            <!-- Booking History Section -->
            <div id="historySection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">All Booking History</h3>
                <div class="card p-4">
                    <div class="filter-section">
                        <input type="date" class="filter-select" id="historyDate" onchange="filterHistory()">
                        <select class="filter-select" id="historyStatus" onchange="filterHistory()">
                            <option value="all">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
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
                                    <td>John Doe</td>
                                    <td>2026-02-10</td>
                                    <td>Lab 01</td>
                                    <td>Microscope (2)</td>
                                    <td>3 hours</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#REQ002</td>
                                    <td>Jane Smith</td>
                                    <td>2026-02-15</td>
                                    <td>Research Lab</td>
                                    <td>Centrifuge (1)</td>
                                    <td>4 hours</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>#REQ003</td>
                                    <td>Mike Johnson</td>
                                    <td>2026-02-18</td>
                                    <td>Lab 02</td>
                                    <td>Incubator (1)</td>
                                    <td>2 hours</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#REQ004</td>
                                    <td>Sarah Wilson</td>
                                    <td>2026-02-19</td>
                                    <td>Lab 01</td>
                                    <td>Autoclave (1)</td>
                                    <td>2 hours</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#REQ005</td>
                                    <td>Alex Chen</td>
                                    <td>2026-02-20</td>
                                    <td>Research Lab</td>
                                    <td>pH Meter (1)</td>
                                    <td>2 hours</td>
                                    <td><span class="badge bg-primary">Approved</span></td>
                                </tr>
                                <tr>
                                    <td>#REQ006</td>
                                    <td>Emma Davis</td>
                                    <td>2026-02-21</td>
                                    <td>Lab 02</td>
                                    <td>Water Bath (1)</td>
                                    <td>1.5 hours</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
            document.getElementById('requestsSection').style.display = 'none';
            document.getElementById('managementSection').style.display = 'none';
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
            const statusFilter = document.getElementById('equipStatusFilter')?.value || 'all';
            
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

        // Filter functions
        function filterRequests() {
            const filter = document.getElementById('requestFilter').value;
            console.log('Filtering requests:', filter);
            // In real app, this would filter the table
        }

        function filterManagement() {
            const filter = document.getElementById('statusFilter').value;
            console.log('Filtering management:', filter);
            // In real app, this would filter the table
        }

        function filterHistory() {
            const date = document.getElementById('historyDate').value;
            const status = document.getElementById('historyStatus').value;
            console.log('Filtering history:', date, status);
            // In real app, this would filter the table
        }

        // Action functions
        function approveRequest(requestId) {
            if (confirm(`Approve request ${requestId}?`)) {
                alert(`Request ${requestId} approved successfully!`);
                // In real app, this would update the database
            }
        }

        function rejectRequest(requestId) {
            const reason = prompt(`Enter rejection reason for ${requestId}:`);
            if (reason) {
                alert(`Request ${requestId} rejected. Reason: ${reason}`);
                // In real app, this would update the database
            }
        }

        function scheduleMaintenance(equipmentCode) {
            const date = prompt(`Enter maintenance date for ${equipmentCode} (YYYY-MM-DD):`);
            if (date) {
                alert(`Maintenance scheduled for ${equipmentCode} on ${date}`);
                // In real app, this would update the database
            }
        }

        function markRepaired(equipmentCode) {
            if (confirm(`Mark ${equipmentCode} as repaired?`)) {
                alert(`${equipmentCode} marked as repaired and available`);
                // In real app, this would update the database
            }
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
                        title: "Microscope Booking - John Doe",
                        time: "10:00 AM - 12:00 PM",
                        details: "Lab 01"
                    },
                    {
                        title: "Centrifuge Booking - Jane Smith",
                        time: "2:00 PM - 4:00 PM",
                        details: "Research Lab"
                    }
                ]
            },
            {
                day: 21,
                month: 2,
                year: 2026,
                events: [
                    {
                        title: "Autoclave Booking - Sarah Wilson",
                        time: "1:00 PM - 3:00 PM",
                        details: "Lab 01"
                    }
                ]
            },
            {
                day: 22,
                month: 2,
                year: 2026,
                events: [
                    {
                        title: "pH Meter Booking - Alex Chen",
                        time: "11:00 AM - 1:00 PM",
                        details: "Research Lab"
                    }
                ]
            }
        ];

        function saveEvents() {
            localStorage.setItem("techCalendarEvents", JSON.stringify(eventsArr));
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
                    <div><i class="bi bi-${type === 'success' ? 'check-circle-fill text-success' : 'info-circle-fill text-info'} me-2"></i> ${message}</div>
                    <div class="time">Just now</div>
                `;
                list.insertBefore(newNotif, list.firstChild);
            }
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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Microbiology Lab System - Admin Dashboard</title>
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

        .request-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dd1818;
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
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
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

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .request-table tr:hover td {
            transform: scale(1.01);
            box-shadow: 0 5px 20px rgba(34, 197, 94, 0.2);
            transition: all 0.3s;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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

        /* Activity Timeline */
        .activity-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .activity-table td {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .activity-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .activity-badge.created {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .activity-badge.approved {
            background: #e3f2fd;
            color: #1565c0;
        }

        .activity-badge.rejected {
            background: #ffebee;
            color: #c62828;
        }

        .activity-badge.returned {
            background: #fff3e0;
            color: #ef6c00;
        }

        .activity-badge.maintenance {
            background: #f3e5f5;
            color: #7b1fa2;
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

        /* Report Card */
        .report-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(34, 197, 94, 0.2);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .report-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #166534;
        }

        .btn-generate {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-generate:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
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

        /* Chart Container */
        .chart-container {
            height: 300px;
            margin-top: 20px;
            position: relative;
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

            .action-buttons {
                flex-direction: column;
            }

            .btn-approve,
            .btn-reject {
                width: 100%;
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

        /* User Management Styles */
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

        .table-heading {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-heading i {
            color: #22c55e;
            font-size: 1.4rem;
        }

        .table-count {
            background: #e0e0e0;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #666;
            margin-left: auto;
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

        /* Booking Badge */
        .booking-badge {
            background: #e6f7e6;
            color: #22c55e;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
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

        /* Responsive */
        @media (max-width: 768px) {
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


        /* View Button Style */
        .btn-view {
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

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }

        /* Update action buttons container for 3 buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .action-buttons button {
            min-width: 36px;
            justify-content: center;
        }


        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            padding: 20px;
            margin: 8% auto;
            width: 60%;
            border-radius: 8px;
        }

        .close {
            float: right;
            font-size: 20px;
            cursor: pointer;
        }

        .filter-buttons {
            margin-bottom: 15px;
        }

        .filter-buttons button {
            margin-right: 5px;
            padding: 5px 10px;
        }

        /* Profile image in tables */
        .user-table td img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #22c55e;
            transition: all 0.3s ease;
        }

        .user-table td img:hover {
            transform: scale(1.1);
            border-color: #ffd700;
        }

        /* Add this to your CSS file - place it near other button styles */

        /* Deactivate Button (Red) */
        .btn-deactivate {
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

        .btn-deactivate:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }

        /* Activate Button (Green) */
        .btn-activate {
            background: linear-gradient(135deg, #22c55e, #16a34a);
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

        /* Request Count Badges */
        .request-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #dc3545;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            padding: 0 5px;
            margin-left: 8px;
            animation: pulse 2s infinite;
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
        }

        .request-tab.active .request-count-badge {
            background-color: white;
            color: #dc3545;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .btn-activate:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
        }

        /* Update responsive section */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .btn-edit,
            .btn-remove,
            .btn-deactivate,
            .btn-activate {
                width: 100%;
                justify-content: center;
            }
        }

        /* Request Tabs */
        .request-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .request-tab {
            padding: 10px 20px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            color: #166534;
        }

        .request-tab:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.2);
        }

        .request-tab.active {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
        }


        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .btn-edit,
            .btn-remove,
            .btn-deactivate,
            .btn-activate,
            .btn-view,
            .btn-approve,
            .btn-reject {
                width: 100%;
                justify-content: center;
            }

            .request-tabs {
                flex-direction: column;
            }

            .request-tab {
                width: 100%;
            }
        }
    </style>


    <!-- Keep original favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/resources/flask.svg">
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR - Updated for Admin -->
    <div class="sidebar" id="sidebar">
        <h4><i class="bi bi-flask"></i> MicroLab</h4>
        <a onclick="showSection('dashboard')" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a onclick="showSection('userManagement')"><i class="bi bi-people"></i> User Manage</a>
        <!-- <a onclick="showSection('requestList')"><i class="bi bi-list-check"></i> Request List</a> -->
        <a onclick="showSection('equipment')"><i class="bi bi-tools"></i> Equipment Manage</a>
        <a onclick="showSection('history')"><i class="bi bi-clock-history"></i> Rservation Details</a>
        <a onclick="showSection('activity')"><i class="bi bi-activity"></i> Requests</a>
        <a onclick="showSection('analytics')"><i class="bi bi-graph-up"></i> Analytics</a>
        <!-- <a onclick="showSection('reports')"><i class="bi bi-file-text"></i> Reports</a> -->
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
                <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, #22c55e, #16a34a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Admin Dashboard</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- equipment management -->
                <!-- <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="bi bi-database-add"></i>
                </div> -->

                <!-- create account -->
                <!-- <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="bi bi-person-add"></i>
                </div> -->

                <!-- request badge -->
                <div class="notification-bell" onclick="showSection('activity')">
                    <i class="bi bi-journal-check"></i>
                    <span class="request-badge" id="requestBadge">4</span>
                </div>


                <!-- Notification Bell -->
                <div class="notification-bell" onclick="openNotificationModal()">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge">3</span>
                </div>












                <span class="fw-semibold d-none d-sm-block" style="color: #166534;">Surangi</span>
                <div class="dropdown">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=22c55e&color=fff&size=100" class="profile-img dropdown-toggle" data-bs-toggle="dropdown">
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

                <div class="analytics-grid">
                    <div class="stat-card">
                        <i class="bi bi-mortarboard-fill"></i>
                        <h3>56</h3>
                        <p>Students</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-person-badge-fill"></i>
                        <h3>5</h3>
                        <p>Supervisors/Lectures</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-person-gear"></i>
                        <h3>3</h3>
                        <p>Technical Officers</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-graph-up"></i>
                        <h3>85%</h3>
                        <p>Equipment Utilization Rate</p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4 justify-content-center">
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Technical officer Pending
                                <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </h6>
                            <h3 class="text-warning">8</h3>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Today's Practicals
                                <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </h6>
                            <h3 class="text-info">15</h3>
                        </div>
                    </div>
                    <!-- <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Total Equipment</h6>
                            <h3 class="text-success">3</h3>
                        </div>
                    </div> -->
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Maintenance
                                <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </h6>

                            <h3 class="text-danger">2</h3>
                        </div>
                    </div>
                </div>

                <!-- Most Used Equipment -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-3" style="color: #166534;">Completed Practicals/Research</h5>
                            <div class="chart-container">
                                <canvas id="usageChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Activity Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-3" style="color: #166534;">System Progress</h5>
                            <div class="chart-container">
                                <canvas id="monthlyChart"></canvas>
                            </div>
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

            <!-- User Management Section -->
            <div id="userManagementSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">User Management</h3>
                <!-- Search and Add Row (Outside cards - common for all tables) -->
                <div class="search-add-row" style="margin-bottom: 20px;">
                    <div class="search-container">
                        <input type="text"
                            id="userSearch"
                            class="search-input"
                            placeholder="Search by ID, name or email..."
                            oninput="searchUsers()"> <!-- This triggers on EVERY keystroke, including backspace -->
                        <!-- <button class="search-btn" onclick="searchUsers()">
            <i class="bi bi-search"></i> Search
        </button> -->
                    </div>
                    <button class="add-btn" onclick="addNewUser()">
                        <i class="bi bi-plus-circle"></i> Add User
                    </button>
                </div>

                <!-- Student Table Card -->
                <div id="studentTableCard" class="card p-4 mb-4"> <!-- Added ID -->
                    <h4 class="table-heading mt-0">
                        <i class="bi bi-person-badge"></i> Students
                        <span class="table-count" id="studentCount">(24)</span>
                    </h4>
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Profile Image</th>
                                    <th>University ID</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                <tr data-user-id="SCI001" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=John+Doe&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>SCI001</td>
                                    <td>John Doe</td>
                                    <td>077-1234567</td>
                                    <td>john.doe@science.lk</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI001')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('SCI001')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="SCI002" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Jane+Smith&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>SCI002</td>
                                    <td>Jane Smith</td>
                                    <td>078-2345678</td>
                                    <td>jane.smith@science.lk</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI002')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('SCI002')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="SCI003" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Mike+Johnson&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>SCI003</td>
                                    <td>Mike Johnson</td>
                                    <td>071-3456789</td>
                                    <td>mike.j@science.lk</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI003')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('SCI003')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="SCI004" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Sarah+Wilson&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>SCI004</td>
                                    <td>Sarah Wilson</td>
                                    <td>072-4567890</td>
                                    <td>sarah.w@science.lk</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI004')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('SCI004')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="SCI005" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Pathum+Perera&background=6c757d&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>SCI005</td>
                                    <td>Pathum Perera</td>
                                    <td>077-5678901</td>
                                    <td>pathum.p@science.lk</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI005')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('SCI005')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="SCI006" data-status="active">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Nimali+Fernando&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>SCI006</td>
                                    <td>Nimali Fernando</td>
                                    <td>071-6789012</td>
                                    <td>nimali.f@science.lk</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI006')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-deactivate" onclick="toggleUserStatus('SCI006')">
                                                <i class="bi bi-person-x"></i> Deactivate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Supervisor/Lecturer Table Card -->
                <div id="supervisorTableCard" class="card p-4 mb-4"> <!-- Added ID -->
                    <h4 class="table-heading mt-0">
                        <i class="bi bi-person-workspace"></i> Supervisors & Lecturers
                        <span class="table-count" id="supervisorCount">(8)</span>
                    </h4>
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Profile Image</th>
                                    <th>University ID</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>

                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="supervisorTableBody">
                                <tr data-user-id="STF001" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Kamal+Perera&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>STF001</td>
                                    <td>Dr. Kamal Perera</td>
                                    <td>077-7890123</td>
                                    <td>kamal.p@micro.lk</td>

                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('STF001')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('STF001')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="STF002" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Malini+Silva&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>STF002</td>
                                    <td>Prof. Malini Silva</td>
                                    <td>078-8901234</td>
                                    <td>malini.s@micro.lk</td>

                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('STF002')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('STF002')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="STF003" data-status="active">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Nuwan+Jayawardena&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>STF003</td>
                                    <td>Dr. Nuwan Jayawardena</td>
                                    <td>071-9012345</td>
                                    <td>nuwan.j@micro.lk</td>

                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('STF003')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-deactivate" onclick="toggleUserStatus('STF003')">
                                                <i class="bi bi-person-x"></i> Deactivate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="STF004" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Sirimal+Fernando&background=6c757d&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>STF004</td>
                                    <td>Prof. Sirimal Fernando</td>
                                    <td>072-0123456</td>
                                    <td>sirimal.f@micro.lk</td>

                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('STF004')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('STF004')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Technical Officer Table Card -->
                <div id="techOfficerTableCard" class="card p-4"> <!-- Added ID -->
                    <h4 class="table-heading mt-0">
                        <i class="bi bi-person-gear"></i> Technical Officers
                        <span class="table-count" id="techOfficerCount">(4)</span>
                    </h4>
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Profile Image</th>
                                    <th>University ID</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>

                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="techOfficerTableBody">
                                <tr data-user-id="TEC001" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Sunil+Rathnayake&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>TEC001</td>
                                    <td>Sunil Rathnayake</td>
                                    <td>077-1237890</td>
                                    <td>sunil.r@lab.lk</td>

                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('TEC001')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('TEC001')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="TEC002" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Chamari+Weerasinghe&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>TEC002</td>
                                    <td>Chamari Weerasinghe</td>
                                    <td>078-2348901</td>
                                    <td>chamari.w@lab.lk</td>

                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('TEC002')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('TEC002')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="TEC003" data-status="active">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Prasanna+Kumara&background=22c55e&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>TEC003</td>
                                    <td>Prasanna Kumara</td>
                                    <td>071-3459012</td>
                                    <td>prasanna.k@lab.lk</td>

                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('TEC003')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-deactivate" onclick="toggleUserStatus('TEC003')">
                                                <i class="bi bi-person-x"></i> Deactivate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-user-id="TEC004" data-status="inactive">
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=Mahesh+Gunasekara&background=6c757d&color=fff&size=50"
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td>TEC004</td>
                                    <td>Mahesh Gunasekara</td>
                                    <td>072-4560123</td>
                                    <td>mahesh.g@lab.lk</td>

                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('TEC004')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-activate" onclick="toggleUserStatus('TEC004')">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Equipment Management Section -->
            <div id="equipmentSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment Management</h3>

                <!-- Search and Add Row (Outside card) -->
                <div class="search-add-row" style="margin-bottom: 20px;">
                    <div class="search-container">
                        <input type="text"
                            id="equipmentSearch"
                            class="search-input"
                            placeholder="Search by code, name or location..."
                            oninput="searchEquipment()"> <!-- Real-time search -->
                        <!-- <button class="search-btn" onclick="searchEquipment()">
                <i class="bi bi-search"></i> Search
            </button> -->
                    </div>
                    <button class="add-btn" onclick="addEquipment()">
                        <i class="bi bi-plus-circle"></i> Add Equipment
                    </button>
                </div>

                <!-- Equipment Table Card -->
                <div id="equipmentTableCard" class="card p-4"> <!-- Added ID for hiding -->
                    <h4 class="table-heading mt-0">
                        <i class="bi bi-tools"></i> Equipment List
                        <span class="table-count" id="equipmentCount">(6)</span>
                    </h4>
                    <div class="table-responsive">
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
                                <tr data-equipment-id="MIC-001">
                                    <td><img src="https://cdn-icons-png.flaticon.com/512/2941/2941514.png" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                    <td>MIC-001</td>
                                    <td>Microscope</td>
                                    <td><span class="badge" style="background: #22c55e; color: white;">4/8</span></td>
                                    <td><span class="badge bg-warning">2</span></td>
                                    <td>
                                        <div class="progress-bar" style="width: 100px; display: inline-block;">
                                            <div class="progress-fill" style="width: 75%"></div>
                                        </div>
                                        75%
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewEquipment('MIC-001')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-edit" onclick="editEquipment('MIC-001')" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeEquipment('MIC-001')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-equipment-id="CEN-002">
                                    <td><img src="https://cdn-icons-png.flaticon.com/512/2941/2941543.png" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                    <td>CEN-002</td>
                                    <td>Centrifuge</td>
                                    <td><span class="badge" style="background: #22c55e; color: white;">3/5</span></td>
                                    <td><span class="badge bg-warning">1</span></td>
                                    <td>
                                        <div class="progress-bar" style="width: 100px; display: inline-block;">
                                            <div class="progress-fill" style="width: 60%"></div>
                                        </div>
                                        60%
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewEquipment('CEN-002')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-edit" onclick="editEquipment('CEN-002')" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeEquipment('CEN-002')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-equipment-id="INC-003">
                                    <td><img src="https://cdn-icons-png.flaticon.com/512/2941/2941538.png" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                    <td>INC-003</td>
                                    <td>Incubator</td>
                                    <td><span class="badge" style="background: #22c55e; color: white;">2/4</span></td>
                                    <td><span class="badge bg-warning">3</span></td>
                                    <td>
                                        <div class="progress-bar" style="width: 100px; display: inline-block;">
                                            <div class="progress-fill" style="width: 50%"></div>
                                        </div>
                                        50%
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewEquipment('INC-003')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-edit" onclick="editEquipment('INC-003')" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeEquipment('INC-003')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-equipment-id="AUT-004">
                                    <td><img src="https://cdn-icons-png.flaticon.com/512/2941/2941521.png" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                    <td>AUT-004</td>
                                    <td>Autoclave</td>
                                    <td><span class="badge" style="background: #22c55e; color: white;">6/6</span></td>
                                    <td><span class="badge bg-warning">0</span></td>
                                    <td>
                                        <div class="progress-bar" style="width: 100px; display: inline-block;">
                                            <div class="progress-fill" style="width: 90%"></div>
                                        </div>
                                        90%
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewEquipment('AUT-004')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-edit" onclick="editEquipment('AUT-004')" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeEquipment('AUT-004')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-equipment-id="PHM-005">
                                    <td><img src="https://cdn-icons-png.flaticon.com/512/2941/2941556.png" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                    <td>PHM-005</td>
                                    <td>pH Meter</td>
                                    <td><span class="badge" style="background: #22c55e; color: white;">3/3</span></td>
                                    <td><span class="badge bg-warning">1</span></td>
                                    <td>
                                        <div class="progress-bar" style="width: 100px; display: inline-block;">
                                            <div class="progress-fill" style="width: 35%"></div>
                                        </div>
                                        35%
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewEquipment('PHM-005')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-edit" onclick="editEquipment('PHM-005')" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeEquipment('PHM-005')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-equipment-id="WAT-006">
                                    <td><img src="https://cdn-icons-png.flaticon.com/512/2941/2941578.png" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                    <td>WAT-006</td>
                                    <td>Water Bath</td>
                                    <td><span class="badge" style="background: #22c55e; color: white;">5/7</span></td>
                                    <td><span class="badge bg-warning">2</span></td>
                                    <td>
                                        <div class="progress-bar" style="width: 100px; display: inline-block;">
                                            <div class="progress-fill" style="width: 70%"></div>
                                        </div>
                                        70%
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewEquipment('WAT-006')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-edit" onclick="editEquipment('WAT-006')" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeEquipment('WAT-006')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
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




            <!-- Reservation Details Section -->
<div id="historySection" style="display: none;">
    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Reservation Details</h3>

    <!-- Search and Filter Row (Outside card) -->
    <div class="search-add-row" style="margin-bottom: 20px;">
        <div class="search-container">
            <input type="text"
                id="reservationSearch"
                class="search-input"
                placeholder="Search by ID, student or lab..."
                oninput="searchReservations()"> <!-- Real-time search -->
            <button class="search-btn" onclick="searchReservations()">
                <i class="bi bi-search"></i> Search
            </button>
        </div>
        <div class="filter-section" style="margin-bottom: 0;">
            <select class="filter-select" id="statusFilter" onchange="searchReservations()" style="min-width: 150px;">
                <option value="all">All Status</option>
                <option value="ready">Ready</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <!-- Add Button -->
        <button class="add-btn" onclick="addReservation()" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
            <i class="bi bi-plus-circle"></i> Add Reservation
        </button>
    </div>

    <!-- Reservation Table Card -->
    <div id="reservationTableCard" class="card p-4">
        <h4 class="table-heading mt-0">
            <i class="bi bi-calendar-check"></i> Reservations
            <span class="table-count" id="reservationCount">(3)</span>
        </h4>
        <div class="table-responsive">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Lab Location</th>
                        <th>Student ID</th>
                        <th>Status</th>
                        <th>Reservation Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="reservationTableBody">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reservation Details Modal -->
<div class="modal fade" id="reservationDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-check me-2"></i>
                    Reservation Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reservationDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



            <!-- Request Section -->
            <div id="activitySection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Requests</h3>

                <div class="card p-4">
                    <!-- Request Type Tabs with Count Badges -->
                    <div class="request-tabs" style="margin-bottom: 20px;">
                        <button class="request-tab active" onclick="switchRequestType('technical')">
                            Technical Officer Requests
                            <span class="request-count-badge" id="technicalRequestCount">3</span>
                        </button>
                        <button class="request-tab" onclick="switchRequestType('supervisor')">
                            Supervisor Requests
                            <span class="request-count-badge" id="supervisorRequestCount">2</span>
                        </button>
                    </div>

                    <!-- Time Range Filter -->
                    <div class="filter-section" style="margin-bottom: 20px;">
                        <select class="filter-select" id="timeRangeFilter" onchange="filterRequestsByTime()" style="min-width: 200px;">
                            <option value="all">All Time</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>

                    <!-- Requests Table -->
                    <div class="table-responsive mt-3">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Date & Time</th>
                                    <th>University ID</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="requestListBody">
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Request Details Modal -->
            <div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-info-circle me-2"></i>
                                Request Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="requestDetailsContent">
                            <!-- Content will be populated by JavaScript -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>





            <!-- Analytics Section -->
<div id="analyticsSection" style="display: none;">
    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Analytics Dashboard</h3>

    <!-- Summary Statistics Cards -->
    <div class="analytics-grid">
        <!-- Students Card -->
        <div class="stat-card">
            <i class="bi bi-mortarboard-fill"></i>
            <h3>56</h3>
            <p>Students</p>
        </div>

        <!-- Supervisors/Lecturers Card -->
        <div class="stat-card">
            <i class="bi bi-person-badge-fill"></i>
            <h3>5</h3>
            <p>Supervisors/Lecturers</p>
        </div>

        <!-- Technical Officers Card -->
        <div class="stat-card">
            <i class="bi bi-person-gear"></i>
            <h3>3</h3>
            <p>Technical Officers</p>
        </div>

        <!-- Active Equipment Card -->
        <div class="stat-card">
            <i class="bi bi-tools"></i>
            <h3>85%</h3>
            <p>Equipment Utilization Rate</p>
        </div>

        <!-- Maintenance Card -->
        <div class="stat-card">
            <i class="bi bi-gear-wide-connected"></i>
            <h3>3</h3>
            <p>Maintenance</p>
        </div>
    </div>

    <!-- First Row: Rejected Requests Report & Equipment Usage Chart -->
    <div class="row">
        <!-- Rejected Requests Report -->
        <div class="col-md-6 mb-4">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold" style="color: #166534;">
                        <i class="bi bi-x-circle-fill text-danger me-2"></i>
                        Rejected Requests (Technical Officer)
                    </h5>
                    <button class="btn-generate" onclick="generateReport('rejected')">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generate Report
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="details-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Student ID</th>
                                <th>Reason</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#REQ003</td>
                                <td>SCI003</td>
                                <td>
                                    <button class="btn-view" onclick="viewRejectionReason('REQ003')" title="View Reason">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                                <td>2026-02-19 09:15 AM</td>
                            </tr>
                            <tr>
                                <td>#REQ007</td>
                                <td>SCI007</td>
                                <td>
                                    <button class="btn-view" onclick="viewRejectionReason('REQ007')" title="View Reason">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                                <td>2026-02-17 02:30 PM</td>
                            </tr>
                            <tr>
                                <td>#REQ012</td>
                                <td>SCI012</td>
                                <td>
                                    <button class="btn-view" onclick="viewRejectionReason('REQ012')" title="View Reason">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                                <td>2026-02-15 11:45 AM</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Equipment Usage Report with Search -->
        <div class="col-md-6 mb-4">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold" style="color: #166534;">
                        <i class="bi bi-bar-chart-fill text-success me-2"></i>
                        Equipment Usage Report
                    </h5>
                    <button class="btn-generate" onclick="generateReport('usage')">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generate Report
                    </button>
                </div>

                <!-- Search Input for Equipment Name (Real-time) -->
                <div class="mb-3">
                    <input type="text" 
                           id="equipmentUsageSearch" 
                           class="form-control" 
                           placeholder="Search equipment name..."
                           oninput="filterEquipmentUsage()">
                </div>

                <!-- Equipment Usage Table -->
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th>Equipment Name</th>
                                <th>Usage Percentage</th>
                            </tr>
                        </thead>
                        <tbody id="equipmentUsageTableBody">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Download Inventory Button -->
    <div class="row">
        <div class="col-12">
            <div class="card p-4 text-center">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0" style="color: #166534;">
                        <i class="bi bi-download me-2"></i>
                        Full Equipment Inventory
                    </h5>
                    <button class="btn-generate" onclick="downloadInventory()">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Download Full Inventory List
                    </button>
                </div>
                <p class="text-muted mt-2 mb-0 small">Download complete inventory of all equipment with details</p>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectionReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Rejection Reason
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="rejectionReasonContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

                <!-- Rejection Reason Modal -->
                <div class="modal fade" id="rejectionReasonModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Rejection Reason
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="rejectionReasonContent">
                                <!-- Content will be populated by JavaScript -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
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
// ========== REQUEST SECTION FUNCTIONS ==========
let currentRequestType = 'technical';
let currentRequestId = null;

// Request Data with status tracking
const technicalRequests = [
    {
        id: 'TEC-REQ-001',
        dateTime: '2026-02-20 10:30 AM',
        timestamp: new Date('2026-02-20T10:30:00'),
        universityId: 'SCI001',
        name: 'John Doe',
        status: 'pending',
        details: {
            equipment: 'Microscope (2 units)',
            lab: 'Lab 01',
            duration: '2 hours',
            purpose: 'Final Year Research Project'
        }
    },
    {
        id: 'TEC-REQ-002',
        dateTime: '2026-02-20 11:00 AM',
        timestamp: new Date('2026-02-20T11:00:00'),
        universityId: 'SCI002',
        name: 'Jane Smith',
        status: 'pending',
        details: {
            equipment: 'Centrifuge (1 unit)',
            lab: 'Research Lab',
            duration: '3 hours',
            purpose: 'DNA Extraction'
        }
    },
    {
        id: 'TEC-REQ-003',
        dateTime: '2026-02-19 09:15 AM',
        timestamp: new Date('2026-02-19T09:15:00'),
        universityId: 'SCI003',
        name: 'Mike Johnson',
        status: 'pending',
        details: {
            equipment: 'Incubator (1 unit)',
            lab: 'Lab 02',
            duration: '4 hours',
            purpose: 'Bacterial Culture'
        }
    }
];

const supervisorRequests = [
    {
        id: 'SUP-REQ-001',
        dateTime: '2026-02-20 09:30 AM',
        timestamp: new Date('2026-02-20T09:30:00'),
        universityId: 'SCI004',
        name: 'Sarah Wilson',
        status: 'pending',
        details: {
            equipment: 'Autoclave (1 unit)',
            lab: 'Lab 01',
            duration: '1.5 hours',
            purpose: 'Media Sterilization',
            supervisor: 'Dr. Kamal Perera'
        }
    },
    {
        id: 'SUP-REQ-002',
        dateTime: '2026-02-19 02:00 PM',
        timestamp: new Date('2026-02-19T14:00:00'),
        universityId: 'SCI005',
        name: 'Pathum Perera',
        status: 'pending',
        details: {
            equipment: 'pH Meter (1 unit)',
            lab: 'Research Lab',
            duration: '2 hours',
            purpose: 'Solution Preparation',
            supervisor: 'Prof. Malini Silva'
        }
    }
];

// Function to update request counts
function updateRequestCounts() {
    const technicalCount = technicalRequests.filter(req => req.status === 'pending').length;
    const supervisorCount = supervisorRequests.filter(req => req.status === 'pending').length;

    document.getElementById('technicalRequestCount').textContent = technicalCount;
    document.getElementById('supervisorRequestCount').textContent = supervisorCount;
    document.getElementById('requestBadge').textContent = technicalCount + supervisorCount;
}

function switchRequestType(type) {
    currentRequestType = type;
    const tabs = document.querySelectorAll('.request-tab');
    tabs.forEach(tab => tab.classList.remove('active'));

    if (type === 'technical') {
        tabs[0].classList.add('active');
    } else {
        tabs[1].classList.add('active');
    }
    filterRequestsByTime();
}

function filterRequestsByTime() {
    const timeRange = document.getElementById('timeRangeFilter').value;
    const today = new Date();
    let requests = currentRequestType === 'technical' ? technicalRequests : supervisorRequests;
    let filtered = [];

    switch (timeRange) {
        case 'daily':
            filtered = requests.filter(item =>
                item.timestamp.toDateString() === today.toDateString()
            );
            break;
        case 'weekly':
            const weekAgo = new Date();
            weekAgo.setDate(today.getDate() - 7);
            filtered = requests.filter(item => item.timestamp >= weekAgo);
            break;
        case 'monthly':
            const monthAgo = new Date();
            monthAgo.setDate(today.getDate() - 30);
            filtered = requests.filter(item => item.timestamp >= monthAgo);
            break;
        case 'all':
        default:
            filtered = requests;
            break;
    }
    displayRequestTable(filtered);
}

function displayRequestTable(requests) {
    const tableBody = document.getElementById('requestListBody');
    if (!tableBody) return;
    tableBody.innerHTML = '';

    requests.sort((a, b) => b.timestamp - a.timestamp);
    requests.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.dateTime}</td>
            <td>${item.universityId}</td>
            <td>${item.name}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view" onclick="viewRequest('${item.id}')" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-approve" onclick="approveRequest('${item.id}')" title="Approve">
                        <i class="bi bi-check-circle"></i>
                    </button>
                    <button class="btn-reject" onclick="rejectRequest('${item.id}')" title="Reject">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });

    if (requests.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="5" class="text-center">No requests found for this time period</td>`;
        tableBody.appendChild(row);
    }
}

function viewRequest(id) {
    const requests = currentRequestType === 'technical' ? technicalRequests : supervisorRequests;
    const request = requests.find(item => item.id === id);
    if (!request) return;

    currentRequestId = id;
    const detailsContent = document.getElementById('requestDetailsContent');
    let detailsHtml = `
        <div class="row">
            <div class="col-md-12">
                <table class="table table-borderless">
                    <tr><th style="width: 150px;">Request ID:</th><td><strong>${request.id}</strong></td></tr>
                    <tr><th>Date & Time:</th><td>${request.dateTime}</td></tr>
                    <tr><th>University ID:</th><td>${request.universityId}</td></tr>
                    <tr><th>Name:</th><td>${request.name}</td></tr>
                    <tr><th>Equipment:</th><td>${request.details.equipment}</td></tr>
                    <tr><th>Lab:</th><td>${request.details.lab}</td></tr>
                    <tr><th>Duration:</th><td>${request.details.duration}</td></tr>
                    <tr><th>Purpose:</th><td>${request.details.purpose}</td></tr>
    `;
    if (currentRequestType === 'supervisor' && request.details.supervisor) {
        detailsHtml += `<tr><th>Supervisor:</th><td>${request.details.supervisor}</td></tr>`;
    }
    detailsHtml += `</table></div></div>`;
    detailsContent.innerHTML = detailsHtml;
    new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
}

function approveRequest(id) {
    const requestId = id || currentRequestId;
    if (confirm(`Are you sure you want to approve request ${requestId}?`)) {
        const requests = currentRequestType === 'technical' ? technicalRequests : supervisorRequests;
        const request = requests.find(r => r.id === requestId);
        if (request) request.status = 'approved';

        alert(`Request ${requestId} has been approved successfully!`);
        const modal = bootstrap.Modal.getInstance(document.getElementById('requestDetailsModal'));
        if (modal) modal.hide();

        updateRequestCounts();
        filterRequestsByTime();
    }
}

function rejectRequest(id) {
    const requestId = id || currentRequestId;
    const reason = prompt(`Enter rejection reason for request ${requestId}:`);
    if (reason) {
        const requests = currentRequestType === 'technical' ? technicalRequests : supervisorRequests;
        const request = requests.find(r => r.id === requestId);
        if (request) request.status = 'rejected';

        alert(`Request ${requestId} has been rejected. Reason: ${reason}`);
        const modal = bootstrap.Modal.getInstance(document.getElementById('requestDetailsModal'));
        if (modal) modal.hide();

        updateRequestCounts();
        filterRequestsByTime();
    }
}

// ========== EQUIPMENT SEARCH FUNCTIONS ==========
function searchEquipment() {
    const searchTerm = document.getElementById('equipmentSearch').value.toLowerCase().trim();
    const equipmentVisible = filterEquipmentTable('equipmentTableBody', searchTerm);
    toggleEquipmentVisibility('equipmentTableCard', equipmentVisible, searchTerm);
    updateEquipmentCount(searchTerm);
}

function filterEquipmentTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    if (!table) return 0;
    let visibleCount = 0;

    for (let row of table.getElementsByTagName('tr')) {
        if (searchTerm === '' || row.textContent.toLowerCase().includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    }
    return visibleCount;
}

function toggleEquipmentVisibility(cardId, visibleCount, searchTerm) {
    const card = document.getElementById(cardId);
    if (!card) return;
    card.style.display = (visibleCount === 0 && searchTerm !== '') ? 'none' : 'block';
}

function updateEquipmentCount(searchTerm) {
    const equipmentTable = document.getElementById('equipmentTableBody');
    if (!equipmentTable) return;

    const visibleEquipment = Array.from(equipmentTable.getElementsByTagName('tr'))
        .filter(row => row.style.display !== 'none').length;
    const totalEquipment = equipmentTable.children.length;

    document.getElementById('equipmentCount').textContent =
        (visibleEquipment > 0 || searchTerm === '') ?
        '(' + visibleEquipment + '/' + totalEquipment + ')' : '(0)';
}

// ========== USER MANAGEMENT FUNCTIONS ==========
function toggleUserStatus(userId) {
    const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (!userRow) return;

    const currentStatus = userRow.getAttribute('data-status');
    const actionCell = userRow.querySelector('.action-buttons');
    const button = actionCell.querySelector(currentStatus === 'active' ? '.btn-deactivate' : '.btn-activate');

    if (currentStatus === 'active') {
        if (confirm(`Are you sure you want to deactivate user ${userId}?`)) {
            userRow.setAttribute('data-status', 'inactive');
            button.className = 'btn-activate';
            button.innerHTML = '<i class="bi bi-person-check"></i> Activate';
            alert(`User ${userId} has been deactivated.`);
        }
    } else {
        if (confirm(`Are you sure you want to activate user ${userId}?`)) {
            userRow.setAttribute('data-status', 'active');
            button.className = 'btn-deactivate';
            button.innerHTML = '<i class="bi bi-person-x"></i> Deactivate';
            alert(`User ${userId} has been activated.`);
        }
    }
}

function viewPendingRequests() {
    showSection('activity');
    document.getElementById('timeRangeFilter').value = 'all';
    filterRequestsByTime();
}

function openNotificationModal() {
    toggleNotifications();
}

function searchUsers() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase().trim();

    const studentVisible = filterTable('studentTableBody', searchTerm);
    const supervisorVisible = filterTable('supervisorTableBody', searchTerm);
    const techVisible = filterTable('techOfficerTableBody', searchTerm);

    toggleTableVisibility('studentTableCard', studentVisible, searchTerm);
    toggleTableVisibility('supervisorTableCard', supervisorVisible, searchTerm);
    toggleTableVisibility('techOfficerTableCard', techVisible, searchTerm);

    updateVisibleCounts(searchTerm);
}

function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    if (!table) return 0;
    let visibleCount = 0;

    for (let row of table.getElementsByTagName('tr')) {
        if (searchTerm === '' || row.textContent.toLowerCase().includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    }
    return visibleCount;
}

function toggleTableVisibility(cardId, visibleCount, searchTerm) {
    const card = document.getElementById(cardId);
    if (!card) return;
    card.style.display = (visibleCount === 0 && searchTerm !== '') ? 'none' : 'block';
}

function updateVisibleCounts(searchTerm) {
    const tables = [
        { id: 'studentTableBody', countId: 'studentCount' },
        { id: 'supervisorTableBody', countId: 'supervisorCount' },
        { id: 'techOfficerTableBody', countId: 'techOfficerCount' }
    ];

    tables.forEach(table => {
        const tbody = document.getElementById(table.id);
        if (!tbody) return;

        const visible = Array.from(tbody.getElementsByTagName('tr'))
            .filter(row => row.style.display !== 'none').length;
        const total = tbody.children.length;

        document.getElementById(table.countId).textContent =
            (visible > 0 || searchTerm === '') ? '(' + visible + '/' + total + ')' : '(0)';
    });
}

function addNewUser() {
    alert('Add User modal would open here');
}

function editUser(userId) {
    alert('Edit user: ' + userId);
}

// ========== SIDEBAR & NAVIGATION ==========
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("active");
    document.getElementById("sidebarOverlay").classList.toggle("active");
}

function toggleNotifications() {
    document.getElementById("notificationDropdown").classList.toggle("show");
}

document.addEventListener('click', function(event) {
    const bell = document.querySelector('.notification-bell:last-child');
    const dropdown = document.getElementById('notificationDropdown');
    if (bell && dropdown && !bell.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

function showSection(section) {
    const sections = ['dashboard', 'userManagement', 'equipment', 'history', 'activity', 'analytics'];
    sections.forEach(s => document.getElementById(s + 'Section').style.display = 'none');

    const sectionElement = document.getElementById(section + 'Section');
    if (sectionElement) sectionElement.style.display = 'block';

    document.querySelectorAll('.sidebar a').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('onclick')?.includes(section)) link.classList.add('active');
    });

    if (section === 'equipment') {
        displayEquipmentTable(equipmentDataTable);
    }
    if (section === 'dashboard' || section === 'analytics') {
        setTimeout(() => {
            initCharts();
            initAnalyticsCharts();
        }, 100);
    }
    if (section === 'history') {
        // Reset filters when showing section
        document.getElementById('reservationSearch').value = '';
        document.getElementById('statusFilter').value = 'all';
        searchReservations();
    }
    if (section === 'activity') filterRequestsByTime();
}

// ========== EQUIPMENT FUNCTIONS ==========
const equipmentGridData = [
    { name: 'Microscope', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png', location: 'Microbiology Lab 01', status: 'available', lab: 'lab1' },
    { name: 'Centrifuge', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941543.png', location: 'Research Laboratory', status: 'in-use', lab: 'research' },
    { name: 'Incubator', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941538.png', location: 'Microbiology Lab 02', status: 'maintenance', lab: 'lab2' },
    { name: 'Autoclave', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941521.png', location: 'Microbiology Lab 01', status: 'available', lab: 'lab1' },
    { name: 'pH Meter', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941556.png', location: 'Research Laboratory', status: 'available', lab: 'research' },
    { name: 'Water Bath', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941578.png', location: 'Microbiology Lab 02', status: 'in-use', lab: 'lab2' }
];

const equipmentDataTable = [
    { code: 'MIC-001', name: 'Microscope', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png', available: 4, total: 8, maintenance: 2, usage: 75, location: 'Microbiology Lab 01', manufacturer: 'Olympus', model: 'CX23', purchaseDate: '2024-01-15', lastMaintenance: '2026-02-01', nextMaintenance: '2026-05-01', description: 'Binocular microscope with LED illumination, 4 objective lenses (4x, 10x, 40x, 100x)' },
    { code: 'CEN-002', name: 'Centrifuge', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941543.png', available: 3, total: 5, maintenance: 1, usage: 60, location: 'Research Laboratory', manufacturer: 'Eppendorf', model: '5424R', purchaseDate: '2023-11-20', lastMaintenance: '2026-01-15', nextMaintenance: '2026-04-15', description: 'Refrigerated microcentrifuge, max speed 15,000 rpm' },
    { code: 'INC-003', name: 'Incubator', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941538.png', available: 2, total: 4, maintenance: 3, usage: 50, location: 'Microbiology Lab 02', manufacturer: 'Thermo Scientific', model: 'Heratherm', purchaseDate: '2023-09-10', lastMaintenance: '2026-02-10', nextMaintenance: '2026-03-10', description: 'Microbiological incubator, 100L capacity' },
    { code: 'AUT-004', name: 'Autoclave', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941521.png', available: 6, total: 6, maintenance: 0, usage: 90, location: 'Microbiology Lab 01', manufacturer: 'Hirayama', model: 'HVE-50', purchaseDate: '2024-02-01', lastMaintenance: '2026-01-20', nextMaintenance: '2026-04-20', description: 'Vertical sterilization autoclave, 50L capacity' },
    { code: 'PHM-005', name: 'pH Meter', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941556.png', available: 3, total: 3, maintenance: 1, usage: 35, location: 'Research Laboratory', manufacturer: 'Mettler Toledo', model: 'FiveEasy', purchaseDate: '2024-03-05', lastMaintenance: '2026-02-05', nextMaintenance: '2026-05-05', description: 'Digital pH meter with automatic temperature compensation' },
    { code: 'WAT-006', name: 'Water Bath', image: 'https://cdn-icons-png.flaticon.com/512/2941/2941578.png', available: 5, total: 7, maintenance: 2, usage: 70, location: 'Microbiology Lab 02', manufacturer: 'Memmert', model: 'WNB 14', purchaseDate: '2023-10-12', lastMaintenance: '2026-01-25', nextMaintenance: '2026-02-25', description: 'Digital water bath, 20L capacity' }
];

function displayEquipmentTable(equipment) {
    const tableBody = document.getElementById('equipmentTableBody');
    if (!tableBody) return;
    tableBody.innerHTML = '';

    equipment.forEach(item => {
        const ratio = item.available / item.total;
        let badgeColor = '#22c55e';
        if (ratio < 0.3) badgeColor = '#ef4444';
        else if (ratio < 0.6) badgeColor = '#f59e0b';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><img src="${item.image}" style="width: 50px; height: 50px; object-fit: contain;"></td>
            <td>${item.code}</td>
            <td>${item.name}</td>
            <td><span class="badge" style="background: ${badgeColor}; color: white;">${item.available}/${item.total}</span></td>
            <td><span class="badge bg-warning">${item.maintenance}</span></td>
            <td>
                <div class="progress-bar" style="width: 100px; display: inline-block;">
                    <div class="progress-fill" style="width: ${item.usage}%"></div>
                </div> ${item.usage}%
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view" onclick="viewEquipment('${item.code}')" title="View Details"><i class="bi bi-eye"></i></button>
                    <button class="btn-edit" onclick="editEquipment('${item.code}')" title="Edit"><i class="bi bi-pencil-square"></i></button>
                    <button class="btn-remove" onclick="removeEquipment('${item.code}')" title="Remove"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function viewEquipment(code) {
    const equipment = equipmentDataTable.find(item => item.code === code);
    if (!equipment) return;

    const formatDate = (date) => new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    const isOverdue = new Date(equipment.nextMaintenance) < new Date();

    document.getElementById('equipmentDetailsContent').innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center">
                <img src="${equipment.image}" style="width: 150px; height: 150px; object-fit: contain;" class="mb-3">
                <h4>${equipment.name}</h4>
                <p class="text-muted">${equipment.code}</p>
            </div>
            <div class="col-md-8">
                <table class="table table-borderless">
                    <tr><th>Location:</th><td>${equipment.location}</td></tr>
                    <tr><th>Manufacturer:</th><td>${equipment.manufacturer}</td></tr>
                    <tr><th>Model:</th><td>${equipment.model}</td></tr>
                    <tr><th>Purchase Date:</th><td>${formatDate(equipment.purchaseDate)}</td></tr>
                    <tr><th>Last Maintenance:</th><td>${formatDate(equipment.lastMaintenance)}</td></tr>
                    <tr><th>Next Maintenance:</th><td>${formatDate(equipment.nextMaintenance)} ${isOverdue ? '<span class="badge bg-danger ms-2">⚠️ Overdue</span>' : ''}</td></tr>
                    <tr><th>Availability:</th><td>${equipment.available}/${equipment.total} units</td></tr>
                    <tr><th>Maintenance:</th><td>${equipment.maintenance} pending</td></tr>
                    <tr><th>Usage Rate:</th><td>${equipment.usage}%</td></tr>
                    <tr><th>Description:</th><td>${equipment.description}</td></tr>
                </table>
            </div>
        </div>
    `;
    new bootstrap.Modal(document.getElementById('equipmentDetailsModal')).show();
}

function addEquipment() {
    alert('Add Equipment modal would open here');
}

function editEquipment(code) {
    alert('Edit equipment: ' + code);
}

function removeEquipment(code) {
    if (confirm(`Remove equipment ${code}?`)) {
        const index = equipmentDataTable.findIndex(item => item.code === code);
        if (index !== -1) equipmentDataTable.splice(index, 1);
        displayEquipmentTable(equipmentDataTable);
        alert('Equipment removed!');
    }
}

// ========== RESERVATION DATA ==========
const reservationData = [
    {
        id: 'RES-001',
        lab: 'Microbiology Lab 01',
        studentId: 'SCI001',
        studentName: 'John Doe',
        status: 'ready',
        date: '2026-02-25',
        time: '10:00 - 12:00',
        equipment: 'Microscope (2), Slides (10)',
        purpose: 'Final Year Research Project',
        technicalOfficer: 'Mr. Sunil Rathnayake',
        notes: 'All equipment verified and ready'
    },
    {
        id: 'RES-002',
        lab: 'Research Laboratory',
        studentId: 'SCI002',
        studentName: 'Jane Smith',
        status: 'pending',
        date: '2026-02-26',
        time: '14:00 - 16:00',
        equipment: 'Centrifuge (1), Test Tubes (5)',
        purpose: 'DNA Extraction Practical',
        technicalOfficer: 'Mrs. Chamari Weerasinghe',
        notes: 'Waiting for equipment availability'
    },
    {
        id: 'RES-003',
        lab: 'Microbiology Lab 02',
        studentId: 'SCI003',
        studentName: 'Mike Johnson',
        status: 'rejected',
        date: '2026-02-24',
        time: '09:00 - 11:00',
        equipment: 'Incubator (1), Culture Media (2)',
        purpose: 'Bacterial Culture Experiment',
        technicalOfficer: 'Mr. Prasanna Kumara',
        notes: 'Equipment under maintenance'
    }
];

// ========== RESERVATION FUNCTIONS ==========
function searchReservations() {
    const searchTerm = document.getElementById('reservationSearch').value.toLowerCase().trim();
    const statusFilter = document.getElementById('statusFilter').value;
    
    console.log('Searching reservations:', { searchTerm, statusFilter });
    
    // Filter reservations based on search term and status
    const filtered = reservationData.filter(item => {
        // Check status filter
        if (statusFilter !== 'all' && item.status !== statusFilter) {
            return false;
        }
        
        // Check search term (if not empty)
        if (searchTerm !== '') {
            return (
                item.id.toLowerCase().includes(searchTerm) ||
                item.studentId.toLowerCase().includes(searchTerm) ||
                item.studentName.toLowerCase().includes(searchTerm) ||
                item.lab.toLowerCase().includes(searchTerm)
            );
        }
        
        return true; // Passes all filters
    });
    
    // Display filtered results
    displayReservationTable(filtered);
    
    // Update visibility and count
    updateReservationVisibility(filtered.length, searchTerm, statusFilter);
}

// This function is called by the select onchange
function filterReservations() {
    searchReservations();
}

function displayReservationTable(reservations) {
    const tableBody = document.getElementById('reservationTableBody');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (reservations.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="6" class="text-center py-4">No reservations found</td>`;
        tableBody.appendChild(row);
        return;
    }

    reservations.forEach(item => {
        let statusClass = '';
        switch (item.status) {
            case 'ready':
                statusClass = 'bg-success';
                break;
            case 'pending':
                statusClass = 'bg-warning';
                break;
            case 'rejected':
                statusClass = 'bg-danger';
                break;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.lab}</td>
            <td>${item.studentId}</td>
            <td><span class="badge ${statusClass}">${item.status}</span></td>
            <td>${item.date}</td>
            <td>
                <button class="btn-view" onclick="viewReservation('${item.id}')" title="View Details">
                    <i class="bi bi-eye"></i> View
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function updateReservationVisibility(visibleCount, searchTerm, statusFilter) {
    const card = document.getElementById('reservationTableCard');
    const countElement = document.getElementById('reservationCount');
    
    if (!card || !countElement) return;
    
    const totalReservations = reservationData.length;
    
    // Update count display
    if (visibleCount > 0 || (searchTerm === '' && statusFilter === 'all')) {
        countElement.textContent = '(' + visibleCount + '/' + totalReservations + ')';
    } else {
        countElement.textContent = '(0)';
    }
    
    // Hide card if no results AND (search is active OR filter is active)
    if (visibleCount === 0 && (searchTerm !== '' || statusFilter !== 'all')) {
        card.style.display = 'none';
    } else {
        card.style.display = 'block';
    }
}

function viewReservation(id) {
    const reservation = reservationData.find(item => item.id === id);
    if (!reservation) return;

    const detailsContent = document.getElementById('reservationDetailsContent');

    let statusBadge = '';
    switch (reservation.status) {
        case 'ready':
            statusBadge = '<span class="badge bg-success">Ready</span>';
            break;
        case 'pending':
            statusBadge = '<span class="badge bg-warning">Pending</span>';
            break;
        case 'rejected':
            statusBadge = '<span class="badge bg-danger">Rejected</span>';
            break;
    }

    detailsContent.innerHTML = `
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Reservation: ${reservation.id}</h4>
                    ${statusBadge}
                </div>
                
                <table class="table table-borderless">
                    <tr><th style="width: 200px;">Lab Location:</th><td>${reservation.lab}</td></tr>
                    <tr><th>Student ID:</th><td>${reservation.studentId} (${reservation.studentName})</td></tr>
                    <tr><th>Technical Officer:</th><td>${reservation.technicalOfficer}</td></tr>
                    <tr><th>Reservation Date:</th><td>${reservation.date}</td></tr>
                    <tr><th>Time Slot:</th><td>${reservation.time}</td></tr>
                    <tr><th>Purpose:</th><td>${reservation.purpose}</td></tr>
                    <tr><th>Requested Equipment:</th><td>${reservation.equipment}</td></tr>
                    <tr><th>Notes:</th><td>${reservation.notes}</td></tr>
                </table>
            </div>
        </div>
    `;

    new bootstrap.Modal(document.getElementById('reservationDetailsModal')).show();
}

function addReservation() {
    alert('Add Reservation modal would open here');
}

// ========== ANALYTICS FUNCTIONS ==========
let usageChart, monthlyChart, equipmentUsageChart;
let equipmentUsageData = [
    { name: 'Microscope', usage: 80 },
    { name: 'Centrifuge', usage: 65 },
    { name: 'Incubator', usage: 45 },
    { name: 'Autoclave', usage: 70 },
    { name: 'pH Meter', usage: 35 },
    { name: 'Water Bath', usage: 20 },
    { name: 'Shaker', usage: 55 },
    { name: 'Hot Plate', usage: 30 },
    { name: 'Balance', usage: 25 }
];

function initCharts() {
    const usageCtx = document.getElementById('usageChart')?.getContext('2d');
    if (usageCtx) {
        if (usageChart) usageChart.destroy();
        usageChart = new Chart(usageCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Completed Practicals',
                    data: [45, 32, 28, 20, 15, 10, 5],
                    backgroundColor: '#22c55e',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
    if (monthlyCtx) {
        if (monthlyChart) monthlyChart.destroy();
        monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'System Usage',
                    data: [12, 19, 15, 25, 22, 30],
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    
    // Initialize equipment usage table
    displayEquipmentUsageTable(equipmentUsageData);
}

function initAnalyticsCharts() {
    const ctx = document.getElementById('equipmentUsageChart')?.getContext('2d');
    if (!ctx) return;
    if (equipmentUsageChart) equipmentUsageChart.destroy();
    equipmentUsageChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Microscope', 'Centrifuge', 'Incubator', 'Autoclave', 'pH Meter', 'Water Bath'],
            datasets: [{
                label: 'Usage Percentage',
                data: [80, 65, 45, 70, 35, 20],
                backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#ef4444'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, max: 100, title: { display: true, text: 'Usage %' } } }
        }
    });
}

// Filter equipment usage table (real-time search)
function filterEquipmentUsage() {
    const searchTerm = document.getElementById('equipmentUsageSearch').value.toLowerCase().trim();
    
    const filtered = equipmentUsageData.filter(item => 
        item.name.toLowerCase().includes(searchTerm)
    );
    
    displayEquipmentUsageTable(filtered);
}

// Display equipment usage table
function displayEquipmentUsageTable(data) {
    const tableBody = document.getElementById('equipmentUsageTableBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="2" class="text-center py-4">No equipment found</td></tr>';
        return;
    }
    
    data.forEach(item => {
        // Determine color based on usage percentage
        let color = '#22c55e'; // green
        if (item.usage < 30) color = '#ef4444'; // red
        else if (item.usage < 60) color = '#f59e0b'; // orange
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="progress-bar" style="width: 150px;">
                        <div class="progress-fill" style="width: ${item.usage}%; background: ${color};"></div>
                    </div>
                    <span style="color: ${color}; font-weight: 600;">${item.usage}%</span>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Download full inventory
function downloadInventory() {
    // Create CSV content
    let csv = "Equipment Code,Equipment Name,Available/Total,Maintenance Pending,Usage %,Location,Manufacturer,Model\n";
    
    equipmentDataTable.forEach(item => {
        csv += `${item.code},${item.name},${item.available}/${item.total},${item.maintenance},${item.usage}%,${item.location},${item.manufacturer},${item.model}\n`;
    });
    
    // Create download link
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'equipment_inventory.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    alert('Inventory list downloaded successfully!');
}

const rejectionReasons = {
    'REQ003': { studentName: 'Mike Johnson', studentId: 'SCI003', reason: 'Equipment under maintenance - Scheduled for repair on 2026-02-25', rejectedBy: 'Mr. Prasanna Kumara', dateTime: '2026-02-19 09:15 AM' },
    'REQ007': { studentName: 'Alice Brown', studentId: 'SCI007', reason: 'Technical issue reported - Motor malfunction, awaiting spare parts', rejectedBy: 'Mrs. Chamari Weerasinghe', dateTime: '2026-02-17 02:30 PM' },
    'REQ012': { studentName: 'Tharindu Silva', studentId: 'SCI012', reason: 'Calibration required - Device giving inaccurate readings', rejectedBy: 'Mr. Sunil Rathnayake', dateTime: '2026-02-15 11:45 AM' }
};

function viewRejectionReason(requestId) {
    const r = rejectionReasons[requestId];
    if (!r) return;
    document.getElementById('rejectionReasonContent').innerHTML = `
        <p><strong>Request ID:</strong> ${requestId}</p>
        <p><strong>Student:</strong> ${r.studentName} (${r.studentId})</p>
        <p><strong>Rejected By:</strong> ${r.rejectedBy}</p>
        <p><strong>Date & Time:</strong> ${r.dateTime}</p>
        <div class="alert alert-danger mt-3">
            <i class="bi bi-info-circle-fill me-2"></i> ${r.reason}
        </div>
    `;
    new bootstrap.Modal(document.getElementById('rejectionReasonModal')).show();
}

function generateReport(type) {
    if (type === 'rejected') {
        // Generate rejected requests report
        let report = "REJECTED REQUESTS REPORT\n";
        report += "=======================\n\n";
        report += "Request ID | Student ID | Reason | Date & Time\n";
        report += "----------------------------------------\n";
        
        Object.keys(rejectionReasons).forEach(reqId => {
            const r = rejectionReasons[reqId];
            report += `${reqId} | ${r.studentId} | ${r.reason} | ${r.dateTime}\n`;
        });
        
        console.log(report);
        alert('Rejected requests report generated! Check console for preview.');
        
    } else if (type === 'usage') {
        // Generate equipment usage report
        let report = "EQUIPMENT USAGE REPORT\n";
        report += "======================\n\n";
        report += "Equipment Name | Usage Percentage\n";
        report += "--------------------------------\n";
        
        equipmentUsageData.forEach(item => {
            report += `${item.name} | ${item.usage}%\n`;
        });
        
        console.log(report);
        alert('Equipment usage report generated! Check console for preview.');
    }
    
    // Show success message
    const msg = document.createElement('div');
    msg.className = 'alert alert-success position-fixed top-0 end-0 m-3';
    msg.style.zIndex = '9999';
    msg.innerHTML = `${type.charAt(0).toUpperCase() + type.slice(1)} report generated!`;
    document.body.appendChild(msg);
    setTimeout(() => msg.remove(), 3000);
}

// ========== CALENDAR FUNCTIONS ==========
let month = new Date().getMonth();
let year = new Date().getFullYear();
const months = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

function initCalendar() {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const lastDate = lastDay.getDate();
    const day = firstDay.getDay();

    document.getElementById('displayMonth').innerHTML = months[month] + " " + year;
    let days = "";
    for (let i = 0; i < day; i++) days += `<div class="day-cell prev-date"></div>`;
    for (let i = 1; i <= lastDate; i++) {
        let classes = "day-cell";
        if (i === new Date().getDate() && year === new Date().getFullYear() && month === new Date().getMonth()) classes += " today";
        days += `<div class="${classes}">${i}</div>`;
    }
    document.getElementById('daysGrid').innerHTML = days;
    document.getElementById('eventDay').innerHTML = 'Monday';
    document.getElementById('eventDate').innerHTML = '15 February 2026';
    document.getElementById('eventsList').innerHTML = '<div class="no-event">No events scheduled</div>';
}

document.querySelector('.prev')?.addEventListener('click', () => {
    month--;
    if (month < 0) { month = 11; year--; }
    initCalendar();
});

document.querySelector('.next')?.addEventListener('click', () => {
    month++;
    if (month > 11) { month = 0; year++; }
    initCalendar();
});

document.getElementById('todayBtn')?.addEventListener('click', () => {
    const d = new Date();
    month = d.getMonth();
    year = d.getFullYear();
    initCalendar();
});

document.getElementById('gotoBtn')?.addEventListener('click', () => {
    const parts = document.getElementById('gotoInput').value.split('/');
    if (parts.length === 2) {
        const m = parseInt(parts[0]) - 1, y = parseInt(parts[1]);
        if (m >= 0 && m < 12 && y > 0) {
            month = m;
            year = y;
            initCalendar();
        } else alert('Invalid date. Use MM/YYYY');
    } else alert('Invalid format. Use MM/YYYY');
});

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    updateRequestCounts();
    updateVisibleCounts('');
    initCharts();
    showSection('dashboard');
    initCalendar();
    
    // Initialize reservation display
    displayReservationTable(reservationData);
    document.getElementById('reservationCount').textContent = '(' + reservationData.length + ')';
    
    if (document.getElementById('equipmentSection')) displayEquipmentTable(equipmentDataTable);
    if (document.getElementById('analyticsSection')) setTimeout(initAnalyticsCharts, 500);
});
</script>
</body>

</html>
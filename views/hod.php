<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Microbiology Lab</title>

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
    </style>
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
        <a onclick="showSection('activity')"><i class="bi bi-activity"></i> Supervisor Requests</a>
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
                <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="bi bi-database-add"></i>
                </div>

                <!-- create account -->
                <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="bi bi-person-add"></i>
                </div>

              <!-- request badge -->
<div class="notification-bell" onclick="showSection('activity')">
    <i class="bi bi-journal-check"></i>
    <span class="request-badge" id="requestBadge">4</span>
</div>

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
                            <div><i class="bi bi-check-circle-fill text-success me-2"></i> Booking #REQ004 approved</div>
                            <div class="time">2 minutes ago</div>
                        </div>
                        <div class="notification-item unread">
                            <div><i class="bi bi-clock-fill text-warning me-2"></i> Equipment ready for pickup</div>
                            <div class="time">15 minutes ago</div>
                        </div>
                        <div class="notification-item unread">
                            <div><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Return reminder: Microscope</div>
                            <div class="time">1 hour ago</div>
                        </div>
                        <div class="notification-item">
                            <div><i class="bi bi-check-circle-fill text-success me-2"></i> Booking #REQ003 completed</div>
                            <div class="time">2 hours ago</div>
                        </div>
                        <div class="notification-item">
                            <div><i class="bi bi-info-circle-fill text-info me-2"></i> Lab maintenance scheduled</div>
                            <div class="time">Yesterday</div>
                        </div>
                    </div>
                </div>

                <span class="fw-semibold d-none d-sm-block" style="color: #166534;">Admin User</span>
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
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Supervisor Pending</h6>
                            <h3 class="text-warning">8</h3>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Supervisor Rejected</h6>
                            <h3 class="text-danger">15</h3>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Active Equipment</h6>
                            <h3 class="text-success">3</h3>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted">Maintenance</h6>
                            <h3 class="text-info">2</h3>
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

                <div class="card p-4">
                    <!-- Search and Add Row -->
                    <div class="search-add-row">
                        <div class="search-container">
                            <input type="text" id="userSearch" class="search-input" placeholder="Search by ID, name or email...">
                            <button class="search-btn" onclick="searchUsers()">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                        <button class="add-btn" onclick="addNewUser()">
                            <i class="bi bi-plus-circle"></i> Add User
                        </button>
                    </div>

                    <!-- Student Table -->
                    <h4 class="table-heading mt-4">
                        <i class="bi bi-person-badge"></i> Students
                        <span class="table-count" id="studentCount">(24)</span>
                    </h4>
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>University ID</th>
                                    <th>First Name</th>
                                    <th>Email</th>
                                    <th>Bookings</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                <tr>
                                    <td data-label="ID">SCI001</td>
                                    <td data-label="Name">John Doe</td>
                                    <td data-label="Email">john.doe@science.lk</td>
                                    <td data-label="Bookings"><span class="booking-badge">12</span></td>
                                    <td data-label="Status"><span class="badge bg-success">Active</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI001')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('SCI001')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">SCI002</td>
                                    <td data-label="Name">Jane Smith</td>
                                    <td data-label="Email">jane.smith@science.lk</td>
                                    <td data-label="Bookings"><span class="booking-badge">8</span></td>
                                    <td data-label="Status"><span class="badge bg-success">Active</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI002')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('SCI002')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">SCI005</td>
                                    <td data-label="Name">Pathum Perera</td>
                                    <td data-label="Email">pathum.p@science.lk</td>
                                    <td data-label="Bookings"><span class="booking-badge">3</span></td>
                                    <td data-label="Status"><span class="badge bg-warning">Inactive</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI005')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('SCI005')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">SCI008</td>
                                    <td data-label="Name">Nimali Fernando</td>
                                    <td data-label="Email">nimali.f@science.lk</td>
                                    <td data-label="Bookings"><span class="booking-badge">15</span></td>
                                    <td data-label="Status"><span class="badge bg-success">Active</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('SCI008')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('SCI008')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Supervisor/Lecturer Table -->
                    <h4 class="table-heading mt-5">
                        <i class="bi bi-person-workspace"></i> Supervisors & Lecturers
                        <span class="table-count" id="supervisorCount">(8)</span>
                    </h4>
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>University ID</th>
                                    <th>First Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="supervisorTableBody">
                                <tr>
                                    <td data-label="ID">STF001</td>
                                    <td data-label="Name">Dr. Kamal Perera</td>
                                    <td data-label="Email">kamal.p@micro.lk</td>
                                    <td data-label="Dept">Microbiology</td>
                                    <td data-label="Status"><span class="badge bg-success">Active</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('STF001')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('STF001')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">STF002</td>
                                    <td data-label="Name">Prof. Malini Silva</td>
                                    <td data-label="Email">malini.s@micro.lk</td>
                                    <td data-label="Dept">Molecular Biology</td>
                                    <td data-label="Status"><span class="badge bg-success">Active</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('STF002')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('STF002')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">STF004</td>
                                    <td data-label="Name">Dr. Nuwan Jayawardena</td>
                                    <td data-label="Email">nuwan.j@micro.lk</td>
                                    <td data-label="Dept">Biochemistry</td>
                                    <td data-label="Status"><span class="badge bg-warning">Inactive</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('STF004')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('STF004')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Technical Officer Table -->
                    <h4 class="table-heading mt-5">
                        <i class="bi bi-person-gear"></i> Technical Officers
                        <span class="table-count" id="techOfficerCount">(4)</span>
                    </h4>
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>University ID</th>
                                    <th>First Name</th>
                                    <th>Email</th>
                                    <th>Specialization</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="techOfficerTableBody">
                                <tr>
                                    <td data-label="ID">TEC001</td>
                                    <td data-label="Name">Sunil Rathnayake</td>
                                    <td data-label="Email">sunil.r@lab.lk</td>
                                    <td data-label="Specialization">Lab Equipment</td>
                                    <td data-label="Status"><span class="badge bg-success">Active</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('TEC001')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('TEC001')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">TEC002</td>
                                    <td data-label="Name">Chamari Weerasinghe</td>
                                    <td data-label="Email">chamari.w@lab.lk</td>
                                    <td data-label="Specialization">Safety & Protocols</td>
                                    <td data-label="Status"><span class="badge bg-success">Active</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('TEC002')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('TEC002')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="ID">TEC003</td>
                                    <td data-label="Name">Prasanna Kumara</td>
                                    <td data-label="Email">prasanna.k@lab.lk</td>
                                    <td data-label="Specialization">Maintenance</td>
                                    <td data-label="Status"><span class="badge bg-warning">Inactive</span></td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editUser('TEC003')">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn-remove" onclick="removeUser('TEC003')">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Request List Section -->
            <!-- <div id="requestListSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Request List</h3>
                
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
                                            <span class="text-success">Approved</span>
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
                                            <span class="text-danger">Reason: Maintenance</span>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> -->

            <!-- Equipment Browser Section -->
            <!-- <div id="equipmentSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment Management</h3>

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

                    <div class="equipment-grid" id="equipmentGrid"> -->
                        <!-- Equipment cards will be populated here -->
                    <!-- </div>
                </div>
            </div> -->


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
                    <tr>
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
                    <tr>
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
                    <tr>
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
                    <tr>
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
                    <tr>
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
                    <tr>
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



            <!-- Booking History Section -->
            <!-- <div id="historySection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Booking History</h3>
                <div class="card p-4">
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> -->

<!-- Reservation Details Section -->
<div id="historySection" style="display: none;">
    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Reservation Details</h3>
    
    <div class="card p-4">
        <!-- Search and Filter Row -->
        <div class="search-add-row">
            <div class="search-container">
                <input type="text" id="reservationSearch" class="search-input" placeholder="Search by ID, student or lab...">
                <button class="search-btn" onclick="searchReservations()">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            <div class="filter-section" style="margin-bottom: 0;">
                <select class="filter-select" id="statusFilter" onchange="filterReservations()" style="min-width: 150px;">
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

        <!-- Reservation Table -->
        <div class="table-responsive mt-3">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Lab Location</th>
                        <th>Student ID</th>
                        <th>Technical Officer Status</th>
                        <th>Reservation Date</th>
                        <th>Requested Equipment</th>
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

<!-- Reservation Details Modal (for view button) -->
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



            <!-- Activity History Section -->
            <!-- <div id="activitySection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Activity History</h3>

                <div class="card p-4">
                    <div class="filter-section">
                        <input type="date" class="filter-select" id="activityDate" onchange="filterActivities()">
                    </div>

                    <div class="table-responsive">
                        <table class="activity-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Details</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="activityListBody">
                                <tr>
                                    <td data-label="Date & Time">2026-02-20 10:30 AM</td>
                                    <td data-label="Activity"><span class="activity-badge created">Booking Created</span></td>
                                    <td data-label="User">John Doe</td>
                                    <td data-label="Details">Microscope - Lab 01 (2 hours)</td>
                                    <td data-label="Action"><button class="btn-view" onclick="viewActivity('REQ004')">View</button></td>
                                </tr>
                                <tr>
                                    <td data-label="Date & Time">2026-02-20 11:00 AM</td>
                                    <td data-label="Activity"><span class="activity-badge approved">Approved</span></td>
                                    <td data-label="User">Admin</td>
                                    <td data-label="Details">Booking #REQ004 approved by Dr. Smith</td>
                                    <td data-label="Action"><button class="btn-view" onclick="viewActivity('REQ004')">View</button></td>
                                </tr>
                                <tr>
                                    <td data-label="Date & Time">2026-02-20 09:15 AM</td>
                                    <td data-label="Activity"><span class="activity-badge rejected">Rejected</span></td>
                                    <td data-label="User">Admin</td>
                                    <td data-label="Details">Booking #REQ003 rejected - Maintenance required</td>
                                    <td data-label="Action"><button class="btn-view" onclick="viewActivity('REQ003')">View</button></td>
                                </tr>
                                <tr>
                                    <td data-label="Date & Time">2026-02-19 04:30 PM</td>
                                    <td data-label="Activity"><span class="activity-badge returned">Equipment Returned</span></td>
                                    <td data-label="User">Jane Smith</td>
                                    <td data-label="Details">Centrifuge returned to Research Lab</td>
                                    <td data-label="Action"><button class="btn-view" onclick="viewActivity('RET001')">View</button></td>
                                </tr>
                                <tr>
                                    <td data-label="Date & Time">2026-02-19 02:00 PM</td>
                                    <td data-label="Activity"><span class="activity-badge maintenance">Maintenance</span></td>
                                    <td data-label="User">Technician</td>
                                    <td data-label="Details">Incubator #123 under maintenance</td>
                                    <td data-label="Action"><button class="btn-view" onclick="viewActivity('MAINT001')">View</button></td>
                                </tr>
                                <tr>
                                    <td data-label="Date & Time">2026-02-18 03:45 PM</td>
                                    <td data-label="Activity"><span class="activity-badge approved">Approved</span></td>
                                    <td data-label="User">Admin</td>
                                    <td data-label="Details">Booking #REQ005 approved</td>
                                    <td data-label="Action"><button class="btn-view" onclick="viewActivity('REQ005')">View</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> -->

<!-- Supervisor Request Section -->
<div id="activitySection" style="display: none;">
    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Supervisor Requests</h3>

    <div class="card p-4">
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
                        <th>Action</th>
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
        
        <!-- Broken Equipment Card -->
        <!-- <div class="stat-card">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <h3>2</h3>
            <p>Broken Equipment</p>
        </div> -->
        
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

        <!-- Equipment Usage Chart -->
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
                
                <div class="chart-container" style="height: 250px;">
                    <canvas id="equipmentUsageChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Equipment Usage Table -->
 <!-- Second Row: Equipment Usage Table -->
<div class="row">
    <div class="col-12">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold" style="color: #166534;">
                    <i class="bi bi-table me-2"></i>
                    Equipment Usage Details
                </h5>
                <button class="btn-generate" onclick="generateReport('usageTable')">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Generate Table Report
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Equipment Code</th>
                            <th>Equipment Name</th>
                            <th>Available Qty</th>
                            <th>Usage Percentage</th>
                            <th>Status</th>
                            <th>Lab Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>MIC-001</td>
                            <td>Microscope</td>
                            <td><span class="badge" style="background: #22c55e; color: white;">4/8</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar" style="width: 150px;">
                                        <div class="progress-fill" style="width: 80%"></div>
                                    </div>
                                    <span>80%</span>
                                </div>
                            </td>
                            <td>—</td>
                            <td>Lab 01</td>
                        </tr>
                        <tr>
                            <td>CEN-002</td>
                            <td>Centrifuge</td>
                            <td><span class="badge" style="background: #22c55e; color: white;">3/5</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar" style="width: 150px;">
                                        <div class="progress-fill" style="width: 65%"></div>
                                    </div>
                                    <span>65%</span>
                                </div>
                            </td>
                            <td>—</td>
                            <td>Research Lab</td>
                        </tr>
                        <tr>
                            <td>INC-003</td>
                            <td>Incubator</td>
                            <td><span class="badge" style="background: #f59e0b; color: white;">2/4</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar" style="width: 150px;">
                                        <div class="progress-fill" style="width: 45%"></div>
                                    </div>
                                    <span>45%</span>
                                </div>
                            </td>
                            <td><span class="badge bg-warning">Maintenance (3)</span></td>
                            <td>Lab 02</td>
                        </tr>
                        <tr>
                            <td>AUT-004</td>
                            <td>Autoclave</td>
                            <td><span class="badge" style="background: #22c55e; color: white;">6/6</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar" style="width: 150px;">
                                        <div class="progress-fill" style="width: 70%"></div>
                                    </div>
                                    <span>70%</span>
                                </div>
                            </td>
                            <td>—</td>
                            <td>Lab 01</td>
                        </tr>
                        <tr>
                            <td>PHM-005</td>
                            <td>pH Meter</td>
                            <td><span class="badge" style="background: #22c55e; color: white;">3/3</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar" style="width: 150px;">
                                        <div class="progress-fill" style="width: 35%"></div>
                                    </div>
                                    <span>35%</span>
                                </div>
                            </td>
                            <td>—</td>
                            <td>Research Lab</td>
                        </tr>
                        <tr>
                            <td>WAT-006</td>
                            <td>Water Bath</td>
                            <td><span class="badge" style="background: #ef4444; color: white;">5/7</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar" style="width: 150px;">
                                        <div class="progress-fill" style="width: 20%"></div>
                                    </div>
                                    <span>20%</span>
                                </div>
                            </td>
                            <td>—</td>
                            <td>Lab 02</td>
                        </tr>
                    </tbody>
                </table>
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
            

            <!-- Analytics Section -->
            <!-- <div id="analyticsSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Analytics Dashboard</h3>

                <div class="analytics-grid">
                    <div class="stat-card">
                        <i class="bi bi-calendar-check"></i>
                        <h3>24</h3>
                        <p>Total Bookings</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-x-circle"></i>
                        <h3>3</h3>
                        <p>Rejected Requests</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-tools"></i>
                        <h3>15</h3>
                        <p>Active Equipment</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h3>2</h3>
                        <p>Broken Equipment</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-gear"></i>
                        <h3>3</h3>
                        <p>Maintenance Pending</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-3" style="color: #166534;">Equipment Status Overview</h5>
                            <div class="table-responsive">
                                <table class="details-table">
                                    <thead>
                                        <tr>
                                            <th>Equipment Code</th>
                                            <th>Name</th>
                                            <th>Activity Qty</th>
                                            <th>Broken Qty</th>
                                            <th>Maintenance Qty</th>
                                            <th>Lab Location</th>
                                            <th>Usage %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>MIC-001</td>
                                            <td>Microscope</td>
                                            <td>8</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>Lab 01</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: 80%"></div>
                                                </div>
                                                80%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>CEN-002</td>
                                            <td>Centrifuge</td>
                                            <td>5</td>
                                            <td>0</td>
                                            <td>1</td>
                                            <td>Research Lab</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: 65%"></div>
                                                </div>
                                                65%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>INC-003</td>
                                            <td>Incubator</td>
                                            <td>4</td>
                                            <td>1</td>
                                            <td>0</td>
                                            <td>Lab 02</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: 45%"></div>
                                                </div>
                                                45%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>AUT-004</td>
                                            <td>Autoclave</td>
                                            <td>6</td>
                                            <td>0</td>
                                            <td>1</td>
                                            <td>Lab 01</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: 70%"></div>
                                                </div>
                                                70%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PHM-005</td>
                                            <td>pH Meter</td>
                                            <td>3</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>Research Lab</td>
                                            <td>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: 35%"></div>
                                                </div>
                                                35%
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Reports Section -->
            <!-- <div id="reportsSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Report Generation</h3>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="report-card">
                            <div class="report-header">
                                <span class="report-title">Rejected Requests Report</span>
                                <button class="btn-generate" onclick="generateReport('rejected')">Generate</button>
                            </div>
                            <p>Student requests rejected by technical officer with reasons</p>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Student</th>
                                            <th>Equipment</th>
                                            <th>Reason</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#REQ003</td>
                                            <td>Mike Johnson</td>
                                            <td>Incubator</td>
                                            <td>Equipment under maintenance</td>
                                            <td>2026-02-19</td>
                                        </tr>
                                        <tr>
                                            <td>#REQ007</td>
                                            <td>Alice Brown</td>
                                            <td>Centrifuge</td>
                                            <td>Technical issue reported</td>
                                            <td>2026-02-17</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="report-card">
                            <div class="report-header">
                                <span class="report-title">Equipment Usage Report</span>
                                <button class="btn-generate" onclick="generateReport('usage')">Generate</button>
                            </div>
                            <p>Detailed equipment usage statistics</p>
                            <div class="chart-container" style="height: 200px;">
                                <canvas id="reportChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="report-card">
                            <div class="report-header">
                                <span class="report-title">Complete Equipment Details</span>
                                <button class="btn-generate" onclick="generateReport('full')">Generate Full Report</button>
                            </div>
                            <div class="table-responsive">
                                <table class="details-table">
                                    <thead>
                                        <tr>
                                            <th>Equipment Code</th>
                                            <th>Name</th>
                                            <th>Activity Qty</th>
                                            <th>Broken Qty</th>
                                            <th>Maintenance Qty</th>
                                            <th>Lab Location</th>
                                            <th>Usage %</th>
                                            <th>Last Used</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>MIC-001</td>
                                            <td>Microscope</td>
                                            <td>8</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>Lab 01</td>
                                            <td>80%</td>
                                            <td>2026-02-20</td>
                                        </tr>
                                        <tr>
                                            <td>CEN-002</td>
                                            <td>Centrifuge</td>
                                            <td>5</td>
                                            <td>0</td>
                                            <td>1</td>
                                            <td>Research Lab</td>
                                            <td>65%</td>
                                            <td>2026-02-19</td>
                                        </tr>
                                        <tr>
                                            <td>INC-003</td>
                                            <td>Incubator</td>
                                            <td>4</td>
                                            <td>1</td>
                                            <td>0</td>
                                            <td>Lab 02</td>
                                            <td>45%</td>
                                            <td>2026-02-18</td>
                                        </tr>
                                        <tr>
                                            <td>AUT-004</td>
                                            <td>Autoclave</td>
                                            <td>6</td>
                                            <td>0</td>
                                            <td>1</td>
                                            <td>Lab 01</td>
                                            <td>70%</td>
                                            <td>2026-02-20</td>
                                        </tr>
                                        <tr>
                                            <td>PHM-005</td>
                                            <td>pH Meter</td>
                                            <td>3</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>Research Lab</td>
                                            <td>35%</td>
                                            <td>2026-02-16</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

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
    // User Management Functions
    function searchUsers() {
        const searchTerm = document.getElementById('userSearch').value.toLowerCase();

        // Search in all tables
        searchInTable('studentTableBody', searchTerm);
        searchInTable('supervisorTableBody', searchTerm);
        searchInTable('techOfficerTableBody', searchTerm);

        updateUserCounts();
    }

    function searchInTable(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.getElementsByTagName('tr');

        for (let row of rows) {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm) || searchTerm === '') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function updateUserCounts() {
        const studentTable = document.getElementById('studentTableBody');
        const supervisorTable = document.getElementById('supervisorTableBody');
        const techTable = document.getElementById('techOfficerTableBody');
        
        if (studentTable) {
            document.getElementById('studentCount').textContent = 
                '(' + studentTable.children.length + ')';
        }
        if (supervisorTable) {
            document.getElementById('supervisorCount').textContent = 
                '(' + supervisorTable.children.length + ')';
        }
        if (techTable) {
            document.getElementById('techOfficerCount').textContent = 
                '(' + techTable.children.length + ')';
        }
    }

    function addNewUser() {
        alert('Add User modal would open here');
    }

    function editUser(userId) {
        alert('Edit user: ' + userId);
    }

    function removeUser(userId) {
        if (confirm('Are you sure you want to remove user: ' + userId + '?')) {
            alert('User ' + userId + ' removed');
        }
    }

    // Initialize counts on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateUserCounts();
        initCharts();
        showSection('dashboard');
        initEquipmentGrid();
        initCalendar();
        
        // Initialize equipment table if equipment section exists
        if (document.getElementById('equipmentSection')) {
            displayEquipmentTable(equipmentDataTable);
        }
    });

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
        document.getElementById('userManagementSection').style.display = 'none';
        document.getElementById('equipmentSection').style.display = 'none';
        document.getElementById('historySection').style.display = 'none';
        document.getElementById('activitySection').style.display = 'none';
        document.getElementById('analyticsSection').style.display = 'none';
        // document.getElementById('reportsSection').style.display = 'none';

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

        // Initialize equipment grid if showing equipment section
        if (section === 'equipment') {
            initEquipmentGrid();
        }

        // Initialize charts if showing dashboard, analytics, or reports
        if (section === 'dashboard' || section === 'analytics' || section === 'reports') {
            setTimeout(() => {
                initCharts();
            }, 100);
        }
    }

    // ========== EQUIPMENT GRID (Browser) ==========
    const equipmentGridData = [{
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

        equipmentGridData.forEach(item => {
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

    // ========== EQUIPMENT TABLE (Management) ==========
    const equipmentDataTable = [
        {
            code: 'MIC-001',
            name: 'Microscope',
            image: 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png',
            available: 4,
            total: 8,
            maintenance: 2,
            usage: 75,
            location: 'Microbiology Lab 01',
            manufacturer: 'Olympus',
            model: 'CX23',
            purchaseDate: '2024-01-15',
            lastMaintenance: '2026-02-01',
            nextMaintenance: '2026-05-01',
            description: 'Binocular microscope with LED illumination, 4 objective lenses (4x, 10x, 40x, 100x)'
        },
        {
            code: 'CEN-002',
            name: 'Centrifuge',
            image: 'https://cdn-icons-png.flaticon.com/512/2941/2941543.png',
            available: 3,
            total: 5,
            maintenance: 1,
            usage: 60,
            location: 'Research Laboratory',
            manufacturer: 'Eppendorf',
            model: '5424R',
            purchaseDate: '2023-11-20',
            lastMaintenance: '2026-01-15',
            nextMaintenance: '2026-04-15',
            description: 'Refrigerated microcentrifuge, max speed 15,000 rpm'
        },
        {
            code: 'INC-003',
            name: 'Incubator',
            image: 'https://cdn-icons-png.flaticon.com/512/2941/2941538.png',
            available: 2,
            total: 4,
            maintenance: 3,
            usage: 50,
            location: 'Microbiology Lab 02',
            manufacturer: 'Thermo Scientific',
            model: 'Heratherm',
            purchaseDate: '2023-09-10',
            lastMaintenance: '2026-02-10',
            nextMaintenance: '2026-03-10',
            description: 'Microbiological incubator, 100L capacity'
        },
        {
            code: 'AUT-004',
            name: 'Autoclave',
            image: 'https://cdn-icons-png.flaticon.com/512/2941/2941521.png',
            available: 6,
            total: 6,
            maintenance: 0,
            usage: 90,
            location: 'Microbiology Lab 01',
            manufacturer: 'Hirayama',
            model: 'HVE-50',
            purchaseDate: '2024-02-01',
            lastMaintenance: '2026-01-20',
            nextMaintenance: '2026-04-20',
            description: 'Vertical sterilization autoclave, 50L capacity'
        },
        {
            code: 'PHM-005',
            name: 'pH Meter',
            image: 'https://cdn-icons-png.flaticon.com/512/2941/2941556.png',
            available: 3,
            total: 3,
            maintenance: 1,
            usage: 35,
            location: 'Research Laboratory',
            manufacturer: 'Mettler Toledo',
            model: 'FiveEasy',
            purchaseDate: '2024-03-05',
            lastMaintenance: '2026-02-05',
            nextMaintenance: '2026-05-05',
            description: 'Digital pH meter with automatic temperature compensation'
        },
        {
            code: 'WAT-006',
            name: 'Water Bath',
            image: 'https://cdn-icons-png.flaticon.com/512/2941/2941578.png',
            available: 5,
            total: 7,
            maintenance: 2,
            usage: 70,
            location: 'Microbiology Lab 02',
            manufacturer: 'Memmert',
            model: 'WNB 14',
            purchaseDate: '2023-10-12',
            lastMaintenance: '2026-01-25',
            nextMaintenance: '2026-02-25',
            description: 'Digital water bath, 20L capacity'
        }
    ];

    // Search equipment
    function searchEquipment() {
        const searchTerm = document.getElementById('equipmentSearch').value.toLowerCase();
        const filtered = equipmentDataTable.filter(item => 
            item.code.toLowerCase().includes(searchTerm) ||
            item.name.toLowerCase().includes(searchTerm) ||
            item.location.toLowerCase().includes(searchTerm)
        );
        displayEquipmentTable(filtered);
    }

    // Display equipment table
    function displayEquipmentTable(equipment) {
        const tableBody = document.getElementById('equipmentTableBody');
        if (!tableBody) return;
        
        tableBody.innerHTML = '';
        
        equipment.forEach(item => {
            const row = document.createElement('tr');
            
            // Determine badge color based on availability ratio
            const ratio = item.available / item.total;
            let badgeColor = '#22c55e'; // green
            if (ratio < 0.3) badgeColor = '#ef4444'; // red
            else if (ratio < 0.6) badgeColor = '#f59e0b'; // orange
            
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
                        <button class="btn-view" onclick="viewEquipment('${item.code}')" title="View Details">
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

    // View equipment details
    function viewEquipment(code) {
        const equipment = equipmentDataTable.find(item => item.code === code);
        if (!equipment) return;
        
        const detailsContent = document.getElementById('equipmentDetailsContent');
        
        // Format dates
        const purchaseDate = new Date(equipment.purchaseDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const lastMaintenance = new Date(equipment.lastMaintenance).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const nextMaintenance = new Date(equipment.nextMaintenance).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        
        // Check if next maintenance is overdue
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
                        <tr><th>Purchase Date:</th><td>${purchaseDate}</td></tr>
                        <tr><th>Last Maintenance:</th><td>${lastMaintenance}</td></tr>
                        <tr>
                            <th>Next Maintenance:</th>
                            <td>
                                ${nextMaintenance}
                                ${isOverdue ? '<span class="badge bg-danger ms-2">⚠️ Overdue</span>' : ''}
                            </td>
                        </tr>
                        <tr><th>Availability:</th><td><span class="badge" style="background: #22c55e;">${equipment.available}/${equipment.total} units</span></td></tr>
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
        
        new bootstrap.Modal(document.getElementById('equipmentDetailsModal')).show();
    }

    // Add new equipment
    function addEquipment() {
        alert('Add Equipment functionality would open a form modal');
    }

    // Edit equipment
    function editEquipment(code) {
        alert('Edit equipment: ' + code);
    }

    // Remove equipment
    function removeEquipment(code) {
        if (confirm(`Are you sure you want to remove equipment ${code}?`)) {
            // Remove from array
            const index = equipmentDataTable.findIndex(item => item.code === code);
            if (index !== -1) {
                equipmentDataTable.splice(index, 1);
            }
            displayEquipmentTable(equipmentDataTable);
            alert('Equipment removed successfully!');
        }
    }

    // Request Management Functions
    function filterRequests() {
        const filter = document.getElementById('requestFilter').value;
        console.log('Filtering requests:', filter);
    }

    function approveRequest(requestId) {
        if (confirm(`Approve request ${requestId}?`)) {
            alert(`Request ${requestId} approved successfully!`);
        }
    }

    function rejectRequest(requestId) {
        const reason = prompt(`Enter rejection reason for ${requestId}:`);
        if (reason) {
            alert(`Request ${requestId} rejected. Reason: ${reason}`);
        }
    }

    // Activity Functions
    function filterActivities() {
        const date = document.getElementById('activityDate').value;
        console.log('Filtering activities for:', date);
    }

    function viewActivity(activityId) {
        alert(`Viewing details for activity: ${activityId}`);
    }

    // Report Functions
    function generateReport(type) {
        alert(`Generating ${type} report...`);

        const message = document.createElement('div');
        message.className = 'alert alert-success position-fixed top-0 end-0 m-3';
        message.style.zIndex = '9999';
        message.innerHTML = `${type.charAt(0).toUpperCase() + type.slice(1)} report generated successfully!`;
        document.body.appendChild(message);
        setTimeout(() => message.remove(), 3000);
    }

    // Charts Initialization
    let usageChart, monthlyChart, reportChart;

    function initCharts() {
        // Destroy existing charts if they exist
        if (usageChart) usageChart.destroy();
        if (monthlyChart) monthlyChart.destroy();
        if (reportChart) reportChart.destroy();

        // Usage Chart
        const usageCtx = document.getElementById('usageChart')?.getContext('2d');
        if (usageCtx) {
            usageChart = new Chart(usageCtx, {
                type: 'bar',
                data: {
                    labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                    datasets: [{
                        label: 'Number of Completed Practicals/Research',
                        data: [45, 32, 28, 20, 15, 10, 5],
                        backgroundColor: '#22c55e',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Monthly Chart
        const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
        if (monthlyCtx) {
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
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Report Chart
        const reportCtx = document.getElementById('reportChart')?.getContext('2d');
        if (reportCtx) {
            reportChart = new Chart(reportCtx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Equipment Usage',
                        data: [12, 19, 15, 17, 14, 8, 5],
                        backgroundColor: '#22c55e',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
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

    let eventsArr = [];

    // Load events from localStorage
    function loadEvents() {
        const saved = localStorage.getItem("calendarEvents");
        if (saved) {
            eventsArr = JSON.parse(saved);
        }
    }
    loadEvents();

    function saveEvents() {
        localStorage.setItem("calendarEvents", JSON.stringify(eventsArr));
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
                        <div class="event-item" onclick="deleteEvent(this, '${ev.title}')">
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
            eventsHtml = '<div class="no-event">No events scheduled</div>';
        }

        const eventsList = document.getElementById('eventsList');
        if (eventsList) {
            eventsList.innerHTML = eventsHtml;
        }
    }

    // Delete event
    window.deleteEvent = function(element, title) {
        if (confirm('Are you sure you want to delete this event?')) {
            eventsArr.forEach((event, index) => {
                if (event.day === activeDay && event.month === month + 1 && event.year === year) {
                    event.events = event.events.filter(e => e.title !== title);
                    if (event.events.length === 0) {
                        eventsArr.splice(index, 1);
                    }
                }
            });
            saveEvents();
            initCalendar();
            updateEventDisplay(activeDay);
        }
    };

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
            const requestDate = new Date();
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
            addNotification('New equipment request submitted', 'success');

            // Clear form
            document.getElementById('eventName').value = '';
            document.getElementById('eventDetails').value = '';

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
                <div><i class="bi bi-${type === 'success' ? 'check-circle-fill text-success' : 'info-circle-fill text-info'} me-2"></i> ${message}</div>
                <div class="time">Just now</div>
            `;
            list.insertBefore(newNotif, list.firstChild);
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
    },
    {
        id: 'RES-004',
        lab: 'Microbiology Lab 01',
        studentId: 'SCI004',
        studentName: 'Sarah Wilson',
        status: 'ready',
        date: '2026-02-27',
        time: '13:00 - 15:00',
        equipment: 'Autoclave (1), Sterilization Bags (5)',
        purpose: 'Media Sterilization',
        technicalOfficer: 'Mr. Sunil Rathnayake',
        notes: 'All set for use'
    },
    {
        id: 'RES-005',
        lab: 'Research Laboratory',
        studentId: 'SCI005',
        studentName: 'Pathum Perera',
        status: 'pending',
        date: '2026-02-28',
        time: '11:00 - 13:00',
        equipment: 'pH Meter (1), Buffer Solutions (3)',
        purpose: 'Solution Preparation',
        technicalOfficer: 'Mrs. Chamari Weerasinghe',
        notes: 'Awaiting supervisor approval'
    },
    {
        id: 'RES-006',
        lab: 'Microbiology Lab 02',
        studentId: 'SCI006',
        studentName: 'Nimali Fernando',
        status: 'rejected',
        date: '2026-02-23',
        time: '15:00 - 17:00',
        equipment: 'Water Bath (1), Thermometers (2)',
        purpose: 'Enzyme Activity Study',
        technicalOfficer: 'Mr. Prasanna Kumara',
        notes: 'Schedule conflict with another reservation'
    }
];

// Search reservations
function searchReservations() {
    const searchTerm = document.getElementById('reservationSearch').value.toLowerCase();
    const filtered = reservationData.filter(item => 
        item.id.toLowerCase().includes(searchTerm) ||
        item.studentId.toLowerCase().includes(searchTerm) ||
        item.studentName.toLowerCase().includes(searchTerm) ||
        item.lab.toLowerCase().includes(searchTerm)
    );
    displayReservationTable(filtered);
}

// Filter reservations by status
function filterReservations() {
    const statusFilter = document.getElementById('statusFilter').value;
    
    if (statusFilter === 'all') {
        displayReservationTable(reservationData);
    } else {
        const filtered = reservationData.filter(item => item.status === statusFilter);
        displayReservationTable(filtered);
    }
}

// Display reservation table
function displayReservationTable(reservations) {
    const tableBody = document.getElementById('reservationTableBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    reservations.forEach(item => {
        const row = document.createElement('tr');
        
        // Determine status badge class
        let statusClass = '';
        let statusText = '';
        
        switch(item.status) {
            case 'ready':
                statusClass = 'bg-success';
                statusText = 'Ready';
                break;
            case 'pending':
                statusClass = 'bg-warning';
                statusText = 'Pending';
                break;
            case 'rejected':
                statusClass = 'bg-danger';
                statusText = 'Rejected';
                break;
        }
        
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.lab}</td>
            <td>${item.studentId}</td>
            <td><span class="badge ${statusClass}">${statusText}</span></td>
            <td>${item.date}</td>
            <td>
                <button class="btn-view" onclick="viewReservation('${item.id}')" title="View Equipment">
                    <i class="bi bi-eye"></i> View
                </button>
            </td>
            <td>
                <button class="btn-remove" onclick="removeReservation('${item.id}')" title="Remove">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// View reservation details
function viewReservation(id) {
    const reservation = reservationData.find(item => item.id === id);
    if (!reservation) return;
    
    const detailsContent = document.getElementById('reservationDetailsContent');
    
    // Determine status badge
    let statusBadge = '';
    switch(reservation.status) {
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
                    <tr>
                        <th style="width: 200px;">Lab Location:</th>
                        <td>${reservation.lab}</td>
                    </tr>
                    <tr>
                        <th>Student ID:</th>
                        <td>${reservation.studentId} (${reservation.studentName})</td>
                    </tr>
                    <tr>
                        <th>Technical Officer:</th>
                        <td>${reservation.technicalOfficer}</td>
                    </tr>
                    <tr>
                        <th>Reservation Date:</th>
                        <td>${reservation.date}</td>
                    </tr>
                    <tr>
                        <th>Time Slot:</th>
                        <td>${reservation.time}</td>
                    </tr>
                    <tr>
                        <th>Purpose:</th>
                        <td>${reservation.purpose}</td>
                    </tr>
                    <tr>
                        <th>Requested Equipment:</th>
                        <td>${reservation.equipment}</td>
                    </tr>
                    <tr>
                        <th>Notes:</th>
                        <td>${reservation.notes}</td>
                    </tr>
                </table>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('reservationDetailsModal')).show();
}

// Remove reservation
function removeReservation(id) {
    if (confirm(`Are you sure you want to remove reservation ${id}?`)) {
        // Remove from array
        const index = reservationData.findIndex(item => item.id === id);
        if (index !== -1) {
            reservationData.splice(index, 1);
        }
        
        // Refresh table
        displayReservationTable(reservationData);
        
        // Show success message
        alert(`Reservation ${id} has been removed successfully!`);
    }
}

// Add new reservation
function addReservation() {
    alert('Add Reservation modal would open here');
    // In a real app, this would open a form to create a new reservation
}

// Initialize reservation table
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('historySection')) {
        displayReservationTable(reservationData);
    }
});


// ========== SUPERVISOR REQUESTS DATA ==========
const supervisorRequests = [
    {
        id: 'REQ001',
        dateTime: '2026-02-20 10:30 AM',
        timestamp: new Date('2026-02-20T10:30:00'),
        studentName: 'John Doe',
        studentId: 'SCI001',
        equipment: 'Microscope (2)',
        lab: 'Lab 01',
        duration: '2 hours',
        purpose: 'Final Year Research Project',
        status: 'pending',
        supervisor: 'Dr. Kamal Perera',
        notes: 'Awaiting supervisor approval'
    },
    {
        id: 'REQ002',
        dateTime: '2026-02-20 11:00 AM',
        timestamp: new Date('2026-02-20T11:00:00'),
        studentName: 'Jane Smith',
        studentId: 'SCI002',
        equipment: 'Centrifuge (1)',
        lab: 'Research Lab',
        duration: '3 hours',
        purpose: 'DNA Extraction',
        status: 'approved',
        supervisor: 'Prof. Malini Silva',
        notes: 'Approved for research'
    },
    {
        id: 'REQ003',
        dateTime: '2026-02-19 09:15 AM',
        timestamp: new Date('2026-02-19T09:15:00'),
        studentName: 'Mike Johnson',
        studentId: 'SCI003',
        equipment: 'Incubator (1)',
        lab: 'Lab 02',
        duration: '4 hours',
        purpose: 'Bacterial Culture',
        status: 'rejected',
        supervisor: 'Dr. Kamal Perera',
        notes: 'Equipment under maintenance'
    },
    {
        id: 'REQ004',
        dateTime: '2026-02-19 04:30 PM',
        timestamp: new Date('2026-02-19T16:30:00'),
        studentName: 'Sarah Wilson',
        studentId: 'SCI004',
        equipment: 'Autoclave (1)',
        lab: 'Lab 01',
        duration: '1.5 hours',
        purpose: 'Media Sterilization',
        status: 'pending',
        supervisor: 'Dr. Nuwan Jayawardena',
        notes: 'Waiting for approval'
    },
    {
        id: 'REQ005',
        dateTime: '2026-02-18 02:00 PM',
        timestamp: new Date('2026-02-18T14:00:00'),
        studentName: 'Pathum Perera',
        studentId: 'SCI005',
        equipment: 'pH Meter (1)',
        lab: 'Research Lab',
        duration: '2 hours',
        purpose: 'Solution Preparation',
        status: 'approved',
        supervisor: 'Prof. Malini Silva',
        notes: 'Approved'
    },
    {
        id: 'REQ006',
        dateTime: '2026-02-17 03:45 PM',
        timestamp: new Date('2026-02-17T15:45:00'),
        studentName: 'Nimali Fernando',
        studentId: 'SCI006',
        equipment: 'Water Bath (1)',
        lab: 'Lab 02',
        duration: '2 hours',
        purpose: 'Enzyme Study',
        status: 'rejected',
        supervisor: 'Dr. Kamal Perera',
        notes: 'Schedule conflict'
    },
    {
        id: 'REQ007',
        dateTime: '2026-02-15 09:30 AM',
        timestamp: new Date('2026-02-15T09:30:00'),
        studentName: 'Tharindu Silva',
        studentId: 'SCI007',
        equipment: 'Microscope (1)',
        lab: 'Lab 01',
        duration: '3 hours',
        purpose: 'Cell Observation',
        status: 'approved',
        supervisor: 'Dr. Nuwan Jayawardena',
        notes: 'Approved'
    },
    {
        id: 'REQ008',
        dateTime: '2026-02-10 01:00 PM',
        timestamp: new Date('2026-02-10T13:00:00'),
        studentName: 'Dilini Perera',
        studentId: 'SCI008',
        equipment: 'Centrifuge (1)',
        lab: 'Research Lab',
        duration: '2 hours',
        purpose: 'Sample Preparation',
        status: 'approved',
        supervisor: 'Prof. Malini Silva',
        notes: 'Completed'
    }
];

// Filter requests by time range
function filterRequestsByTime() {
    const timeRange = document.getElementById('timeRangeFilter').value;
    const today = new Date();
    let filtered = [];
    
    switch(timeRange) {
        case 'daily':
            // Today's requests
            filtered = supervisorRequests.filter(item => 
                item.timestamp.toDateString() === today.toDateString()
            );
            break;
        case 'weekly':
            // Last 7 days
            const weekAgo = new Date();
            weekAgo.setDate(today.getDate() - 7);
            filtered = supervisorRequests.filter(item => 
                item.timestamp >= weekAgo
            );
            break;
        case 'monthly':
            // Last 30 days
            const monthAgo = new Date();
            monthAgo.setDate(today.getDate() - 30);
            filtered = supervisorRequests.filter(item => 
                item.timestamp >= monthAgo
            );
            break;
        case 'all':
        default:
            filtered = supervisorRequests;
            break;
    }
    
    displayRequestTable(filtered);
}

// Display request table
function displayRequestTable(requests) {
    const tableBody = document.getElementById('requestListBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    // Sort by date (newest first)
    requests.sort((a, b) => b.timestamp - a.timestamp);
    
    requests.forEach(item => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.dateTime}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view" onclick="viewRequest('${item.id}')" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-remove" onclick="removeRequest('${item.id}')" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
    
    // Show message if no requests
    if (requests.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="3" class="text-center">No requests found for this time period</td>`;
        tableBody.appendChild(row);
    }
}

// View request details
function viewRequest(id) {
    const request = supervisorRequests.find(item => item.id === id);
    if (!request) return;
    
    const detailsContent = document.getElementById('requestDetailsContent');
    
    // Determine status badge
    let statusBadge = '';
    switch(request.status) {
        case 'pending':
            statusBadge = '<span class="badge bg-warning">Pending</span>';
            break;
        case 'approved':
            statusBadge = '<span class="badge bg-success">Approved</span>';
            break;
        case 'rejected':
            statusBadge = '<span class="badge bg-danger">Rejected</span>';
            break;
    }
    
    detailsContent.innerHTML = `
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Request: ${request.id}</h4>
                    ${statusBadge}
                </div>
                
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 200px;">Date & Time:</th>
                        <td>${request.dateTime}</td>
                    </tr>
                    <tr>
                        <th>Student:</th>
                        <td>${request.studentName} (${request.studentId})</td>
                    </tr>
                    <tr>
                        <th>Supervisor:</th>
                        <td>${request.supervisor}</td>
                    </tr>
                    <tr>
                        <th>Lab Location:</th>
                        <td>${request.lab}</td>
                    </tr>
                    <tr>
                        <th>Equipment:</th>
                        <td>${request.equipment}</td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td>${request.duration}</td>
                    </tr>
                    <tr>
                        <th>Purpose:</th>
                        <td>${request.purpose}</td>
                    </tr>
                    <tr>
                        <th>Notes:</th>
                        <td>${request.notes}</td>
                    </tr>
                </table>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
}

// Remove request
function removeRequest(id) {
    if (confirm(`Are you sure you want to remove request ${id}?`)) {
        // Remove from array
        const index = supervisorRequests.findIndex(item => item.id === id);
        if (index !== -1) {
            supervisorRequests.splice(index, 1);
        }
        
        // Refresh table with current filter
        filterRequestsByTime();
        
        // Show success message
        alert(`Request ${id} has been removed successfully!`);
    }
}

// Initialize request table
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('activitySection')) {
        filterRequestsByTime(); // Show all by default
    }
});



// ========== ANALYTICS FUNCTIONS ==========

// Initialize charts
let equipmentUsageChart;

function initAnalyticsCharts() {
    const ctx = document.getElementById('equipmentUsageChart')?.getContext('2d');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (equipmentUsageChart) equipmentUsageChart.destroy();
    
    equipmentUsageChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Microscope', 'Centrifuge', 'Incubator', 'Autoclave', 'pH Meter', 'Water Bath'],
            datasets: [{
                label: 'Usage Percentage',
                data: [80, 65, 45, 70, 35, 20],
                backgroundColor: [
                    '#22c55e',
                    '#3b82f6',
                    '#f59e0b',
                    '#8b5cf6',
                    '#ec4899',
                    '#ef4444'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Usage %'
                    }
                }
            }
        }
    });
}

// Rejection reasons data
const rejectionReasons = {
    'REQ003': {
        studentName: 'Mike Johnson',
        studentId: 'SCI003',
        equipment: 'Incubator',
        reason: 'Equipment under maintenance - Scheduled for repair on 2026-02-25',
        rejectedBy: 'Mr. Prasanna Kumara',
        dateTime: '2026-02-19 09:15 AM'
    },
    'REQ007': {
        studentName: 'Alice Brown',
        studentId: 'SCI007',
        equipment: 'Centrifuge',
        reason: 'Technical issue reported - Motor malfunction, awaiting spare parts',
        rejectedBy: 'Mrs. Chamari Weerasinghe',
        dateTime: '2026-02-17 02:30 PM'
    },
    'REQ012': {
        studentName: 'Tharindu Silva',
        studentId: 'SCI012',
        equipment: 'pH Meter',
        reason: 'Calibration required - Device giving inaccurate readings',
        rejectedBy: 'Mr. Sunil Rathnayake',
        dateTime: '2026-02-15 11:45 AM'
    }
};

// View rejection reason
function viewRejectionReason(requestId) {
    const reason = rejectionReasons[requestId];
    if (!reason) return;
    
    const content = document.getElementById('rejectionReasonContent');
    content.innerHTML = `
        <div class="p-2">
            <table class="table table-borderless">
                <tr>
                    <th style="width: 120px;">Request ID:</th>
                    <td><strong>${requestId}</strong></td>
                </tr>
                <tr>
                    <th>Student:</th>
                    <td>${reason.studentName} (${reason.studentId})</td>
                </tr>
                <tr>
                    <th>Equipment:</th>
                    <td>${reason.equipment}</td>
                </tr>
                <tr>
                    <th>Rejected By:</th>
                    <td>${reason.rejectedBy}</td>
                </tr>
                <tr>
                    <th>Date & Time:</th>
                    <td>${reason.dateTime}</td>
                </tr>
                <tr>
                    <th>Reason:</th>
                    <td>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            ${reason.reason}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('rejectionReasonModal')).show();
}

// Generate report function
function generateReport(type) {
    if (type === 'rejected') {
        // Generate rejected requests report
        alert('Generating Rejected Requests Report...');
        
        // Create a simple table for the report
        let reportContent = "REJECTED REQUESTS REPORT\n";
        reportContent += "========================\n\n";
        reportContent += "Request ID | Student ID | Reason | Date & Time\n";
        reportContent += "----------------------------------------\n";
        
        Object.keys(rejectionReasons).forEach(reqId => {
            const r = rejectionReasons[reqId];
            reportContent += `${reqId} | ${r.studentId} | ${r.reason.substring(0, 30)}... | ${r.dateTime}\n`;
        });
        
        console.log(reportContent);
        
    } else if (type === 'usage') {
        // Generate equipment usage chart report
        alert('Generating Equipment Usage Chart Report...');
        
    } else if (type === 'usageTable') {
        // Generate equipment usage table report
        alert('Generating Equipment Usage Table Report...');
        
        // Get table data
        const tableData = [
            { name: 'Microscope', usage: '80%' },
            { name: 'Centrifuge', usage: '65%' },
            { name: 'Incubator', usage: '45%' },
            { name: 'Autoclave', usage: '70%' },
            { name: 'pH Meter', usage: '35%' },
            { name: 'Water Bath', usage: '20%' }
        ];
        
        let reportContent = "EQUIPMENT USAGE REPORT\n";
        reportContent += "======================\n\n";
        reportContent += "Equipment Name | Usage Percentage\n";
        reportContent += "--------------------------------\n";
        
        tableData.forEach(item => {
            reportContent += `${item.name} | ${item.usage}\n`;
        });
        
        console.log(reportContent);
    }
    
    // Show success message
    const message = document.createElement('div');
    message.className = 'alert alert-success position-fixed top-0 end-0 m-3';
    message.style.zIndex = '9999';
    message.innerHTML = `${type === 'rejected' ? 'Rejected Requests' : type === 'usage' ? 'Usage Chart' : 'Usage Table'} report generated successfully!`;
    document.body.appendChild(message);
    setTimeout(() => message.remove(), 3000);
}

// Initialize analytics when section is shown
document.addEventListener('DOMContentLoaded', function() {
    // Check if we need to initialize analytics
    if (document.getElementById('analyticsSection')) {
        setTimeout(() => {
            initAnalyticsCharts();
        }, 500);
    }
});
</script>
</body>

</html>
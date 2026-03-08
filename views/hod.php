<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION["user"]) && isset($_SESSION["user_role"]) && $_SESSION["user_role"] === 'HOD') {
?>


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



            /* Add to your existing CSS */
            .maintenance-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }

            .maintenance-modal.active {
                display: flex;
            }

            .maintenance-modal-content {
                background: white;
                border-radius: 20px;
                width: 90%;
                max-width: 600px;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: modalSlideIn 0.3s ease;
            }

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

            .maintenance-modal-header {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
                padding: 20px 25px;
                border-radius: 20px 20px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .maintenance-modal-header h3 {
                margin: 0;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .maintenance-modal-header .close-btn {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                width: 35px;
                height: 35px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s;
            }

            .maintenance-modal-header .close-btn:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: rotate(90deg);
            }

            .maintenance-modal-body {
                padding: 25px;
            }

            .email-section {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 20px;
                margin-bottom: 20px;
            }

            .email-field {
                margin-bottom: 15px;
            }

            .email-field label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #166534;
                font-size: 0.95rem;
            }

            .email-field input[type="email"] {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-size: 14px;
                transition: all 0.3s;
            }

            .email-field input[type="email"]:focus {
                border-color: #f59e0b;
                outline: none;
                box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
            }

            .email-format-section {
                background: white;
                border-radius: 12px;
                padding: 15px;
                margin: 20px 0;
                border: 1px solid #e0e0e0;
            }

            .email-format-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

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

            .btn-activate:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
            }

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

            .email-format-header h6 {
                margin: 0;
                font-weight: 600;
                color: #166534;
            }

            .edit-format-btn {
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 8px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .edit-format-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
            }

            .email-format-preview {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 10px;
                font-family: monospace;
                font-size: 13px;
                color: #333;
                white-space: pre-wrap;
                max-height: 150px;
                overflow-y: auto;
            }

            .email-format-edit {
                display: none;
            }

            .email-format-edit.active {
                display: block;
            }

            .email-format-edit textarea {
                width: 100%;
                padding: 12px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-family: monospace;
                font-size: 13px;
                min-height: 100px;
                resize: vertical;
            }

            .equipment-selection-section {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 20px;
                margin: 20px 0;
            }

            .section-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

            .section-header h6 {
                margin: 0;
                font-weight: 600;
                color: #166534;
            }

            .add-equipment-btn {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 13px;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .add-equipment-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
            }

            .selected-equipment-list {
                max-height: 200px;
                overflow-y: auto;
            }

            .equipment-item {
                background: white;
                border-radius: 10px;
                padding: 12px 15px;
                margin-bottom: 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: 1px solid #e0e0e0;
                transition: all 0.3s;
            }

            .equipment-item:hover {
                border-color: #f59e0b;
                box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
            }

            .equipment-item-info {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .equipment-item-name {
                font-weight: 600;
                color: #333;
            }

            .equipment-item-details {
                display: flex;
                gap: 15px;
                font-size: 12px;
                color: #666;
            }

            .equipment-item-qty {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .qty-input {
                width: 60px;
                padding: 4px 8px;
                border: 1px solid #e0e0e0;
                border-radius: 5px;
                text-align: center;
            }

            .remove-equipment-btn {
                background: none;
                border: none;
                color: #ef4444;
                cursor: pointer;
                font-size: 16px;
                padding: 5px;
                transition: all 0.3s;
            }

            .remove-equipment-btn:hover {
                transform: scale(1.2);
                color: #dc2626;
            }

            .company-section {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 20px;
                margin: 20px 0;
            }

            .company-list {
                margin-top: 15px;
            }

            .company-tag {
                background: linear-gradient(135deg, #f59e0b20, #d9770620);
                border: 1px solid #f59e0b;
                color: #d97706;
                padding: 8px 15px;
                border-radius: 30px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin: 0 8px 8px 0;
                font-size: 13px;
                font-weight: 500;
            }

            .company-tag i {
                cursor: pointer;
                transition: all 0.3s;
            }

            .company-tag i:hover {
                color: #dc2626;
                transform: scale(1.2);
            }

            .add-company-input {
                display: flex;
                gap: 10px;
                margin-top: 10px;
            }

            .add-company-input input {
                flex: 1;
                padding: 10px 12px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 13px;
            }

            .add-company-input input:focus {
                border-color: #f59e0b;
                outline: none;
            }

            .add-company-btn {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
                border: none;
                padding: 0 20px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                white-space: nowrap;
            }

            .add-company-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
            }

            .maintenance-modal-footer {
                padding: 20px 25px;
                border-top: 1px solid #e0e0e0;
                display: flex;
                justify-content: flex-end;
                gap: 12px;
            }

            .btn-cancel {
                background: linear-gradient(135deg, #6c757d, #5a6268);
                color: white;
                border: none;
                padding: 12px 25px;
                border-radius: 10px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }

            .btn-send {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 10px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .btn-cancel:hover,
            .btn-send:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }

            .btn-send:hover {
                box-shadow: 0 5px 15px rgba(245, 158, 11, 0.4);
            }

            /* Equipment Selection Modal (Broken List) */
            .equipment-select-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            }

            .equipment-select-modal.active {
                display: flex;
            }

            .equipment-select-content {
                background: white;
                border-radius: 20px;
                width: 90%;
                max-width: 500px;
                max-height: 80vh;
                overflow-y: auto;
            }

            .equipment-select-header {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: white;
                padding: 15px 20px;
                border-radius: 20px 20px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .equipment-list {
                padding: 20px;
            }

            .equipment-select-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px;
                border-bottom: 1px solid #f0f0f0;
                cursor: pointer;
                transition: all 0.3s;
            }

            .equipment-select-item:hover {
                background: rgba(34, 197, 94, 0.05);
            }

            .equipment-select-item.selected {
                background: rgba(245, 158, 11, 0.1);
                border-left: 3px solid #f59e0b;
            }

            .equipment-select-info h6 {
                margin: 0 0 5px 0;
                font-weight: 600;
            }

            .equipment-select-info p {
                margin: 0;
                font-size: 12px;
                color: #666;
            }

            .equipment-select-qty {
                width: 70px;
                padding: 5px;
                border: 1px solid #e0e0e0;
                border-radius: 5px;
                text-align: center;
            }

            .equipment-select-footer {
                padding: 15px 20px;
                border-top: 1px solid #e0e0e0;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
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


            /* .modal {
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
        } */

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


            /* Add this after your existing CSS - around line 860 */

            /* Maintenance Modal Styles */
            .maintenance-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }

            .maintenance-modal.active {
                display: flex;
            }

            .maintenance-modal-content {
                background: white;
                border-radius: 20px;
                width: 90%;
                max-width: 600px;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: modalSlideIn 0.3s ease;
            }

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

            .maintenance-modal-header {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
                padding: 20px 25px;
                border-radius: 20px 20px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .maintenance-modal-header h3 {
                margin: 0;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .maintenance-modal-header .close-btn {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                width: 35px;
                height: 35px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s;
            }

            .maintenance-modal-header .close-btn:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: rotate(90deg);
            }

            .maintenance-modal-body {
                padding: 25px;
            }

            .email-section {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 20px;
                margin-bottom: 20px;
            }

            .email-field {
                margin-bottom: 15px;
            }

            .email-field label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #166534;
                font-size: 0.95rem;
            }

            .email-field input[type="email"] {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-size: 14px;
                transition: all 0.3s;
            }

            .email-field input[type="email"]:focus {
                border-color: #f59e0b;
                outline: none;
                box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
            }

            .email-format-section {
                background: white;
                border-radius: 12px;
                padding: 15px;
                margin: 20px 0;
                border: 1px solid #e0e0e0;
            }

            .email-format-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

            .email-format-header h6 {
                margin: 0;
                font-weight: 600;
                color: #166534;
            }

            .edit-format-btn {
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 8px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .edit-format-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
            }

            .email-format-preview {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 10px;
                font-family: monospace;
                font-size: 13px;
                color: #333;
                white-space: pre-wrap;
                max-height: 150px;
                overflow-y: auto;
            }

            .email-format-edit {
                display: none;
            }

            .email-format-edit.active {
                display: block;
            }

            .email-format-edit textarea {
                width: 100%;
                padding: 12px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-family: monospace;
                font-size: 13px;
                min-height: 100px;
                resize: vertical;
            }

            .equipment-selection-section {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 20px;
                margin: 20px 0;
            }

            .section-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

            .section-header h6 {
                margin: 0;
                font-weight: 600;
                color: #166534;
            }

            .add-equipment-btn {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 13px;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .add-equipment-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
            }

            .selected-equipment-list {
                max-height: 200px;
                overflow-y: auto;
            }

            .equipment-item {
                background: white;
                border-radius: 10px;
                padding: 12px 15px;
                margin-bottom: 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: 1px solid #e0e0e0;
                transition: all 0.3s;
            }

            .equipment-item:hover {
                border-color: #f59e0b;
                box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
            }

            .equipment-item-info {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .equipment-item-name {
                font-weight: 600;
                color: #333;
            }

            .equipment-item-details {
                display: flex;
                gap: 15px;
                font-size: 12px;
                color: #666;
            }

            .equipment-item-qty {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .qty-input {
                width: 60px;
                padding: 4px 8px;
                border: 1px solid #e0e0e0;
                border-radius: 5px;
                text-align: center;
            }

            .remove-equipment-btn {
                background: none;
                border: none;
                color: #ef4444;
                cursor: pointer;
                font-size: 16px;
                padding: 5px;
                transition: all 0.3s;
            }

            .remove-equipment-btn:hover {
                transform: scale(1.2);
                color: #dc2626;
            }

            .company-section {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 20px;
                margin: 20px 0;
            }

            .company-list {
                margin-top: 15px;
            }

            .company-tag {
                background: linear-gradient(135deg, #f59e0b20, #d9770620);
                border: 1px solid #f59e0b;
                color: #d97706;
                padding: 8px 15px;
                border-radius: 30px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin: 0 8px 8px 0;
                font-size: 13px;
                font-weight: 500;
            }

            .company-tag i {
                cursor: pointer;
                transition: all 0.3s;
            }

            .company-tag i:hover {
                color: #dc2626;
                transform: scale(1.2);
            }

            .add-company-input {
                display: flex;
                gap: 10px;
                margin-top: 10px;
            }

            .add-company-input input {
                flex: 1;
                padding: 10px 12px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 13px;
            }

            .add-company-input input:focus {
                border-color: #f59e0b;
                outline: none;
            }

            .add-company-btn {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
                border: none;
                padding: 0 20px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                white-space: nowrap;
            }

            .add-company-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
            }

            .maintenance-modal-footer {
                padding: 20px 25px;
                border-top: 1px solid #e0e0e0;
                display: flex;
                justify-content: flex-end;
                gap: 12px;
            }

            .btn-cancel {
                background: linear-gradient(135deg, #6c757d, #5a6268);
                color: white;
                border: none;
                padding: 12px 25px;
                border-radius: 10px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }

            .btn-send {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 10px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .btn-cancel:hover,
            .btn-send:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }

            .btn-send:hover {
                box-shadow: 0 5px 15px rgba(245, 158, 11, 0.4);
            }

            /* Equipment Selection Modal */
            .equipment-select-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            }

            .equipment-select-modal.active {
                display: flex;
            }

            .equipment-select-content {
                background: white;
                border-radius: 20px;
                width: 90%;
                max-width: 500px;
                max-height: 80vh;
                overflow-y: auto;
            }

            .equipment-select-header {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: white;
                padding: 15px 20px;
                border-radius: 20px 20px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .equipment-list {
                padding: 20px;
            }

            .equipment-select-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px;
                border-bottom: 1px solid #f0f0f0;
                cursor: pointer;
                transition: all 0.3s;
            }

            .equipment-select-item:hover {
                background: rgba(34, 197, 94, 0.05);
            }

            .equipment-select-item.selected {
                background: rgba(245, 158, 11, 0.1);
                border-left: 3px solid #f59e0b;
            }

            .equipment-select-info h6 {
                margin: 0 0 5px 0;
                font-weight: 600;
            }

            .equipment-select-info p {
                margin: 0;
                font-size: 12px;
                color: #666;
            }

            .equipment-select-qty {
                width: 70px;
                padding: 5px;
                border: 1px solid #e0e0e0;
                border-radius: 5px;
                text-align: center;
            }

            .equipment-select-footer {
                padding: 15px 20px;
                border-top: 1px solid #e0e0e0;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }

            .company-select {
                width: 100%;
                padding: 6px 10px;
                border: 1px solid #e0e0e0;
                border-radius: 6px;
                font-size: 12px;
                margin-top: 5px;
                background-color: white;
                cursor: pointer;
            }

            .company-select:focus {
                border-color: #f59e0b;
                outline: none;
            }

            .company-badge {
                background: linear-gradient(135deg, #f59e0b20, #d9770620);
                color: #d97706;
                padding: 3px 8px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: 600;
                margin-left: 8px;
            }

            .equipment-item-details {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
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



      <!-- Reservation Details Modal -->
        <!-- Reservation Details Modal - Fixed Version -->
        <div id="resModal"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.6); z-index:999999;
            justify-content:center; align-items:center; overflow-y:auto;">
            <div style="background:white; border-radius:16px; width:90%; max-width:580px;
                margin:30px auto; box-shadow:0 25px 60px rgba(0,0,0,0.4);
                position:relative;">
                <div style="background:linear-gradient(135deg,#22c55e,#16a34a);
                    padding:18px 24px; border-radius:16px 16px 0 0;
                    display:flex; justify-content:space-between; align-items:center;">
                    <h5 style="margin:0; color:white; font-weight:600;">
                        <i class="bi bi-calendar-check"></i>&nbsp;Reservation Details
                    </h5>
                    <button onclick="closeResModal()"
                        style="background:rgba(255,255,255,0.25); border:none; color:white;
                           width:34px; height:34px; border-radius:50%; cursor:pointer;
                           font-size:1.1rem; line-height:1; display:flex; 
                           align-items:center; justify-content:center;">✕</button>
                </div>
                <div id="resModalContent" style="padding:24px; max-height:60vh; overflow-y:auto;"></div>
                <div style="padding:16px 24px; border-top:1px solid #eee; text-align:right;">
                    <button onclick="closeResModal()"
                        style="background:#6c757d; color:white; border:none;
                           padding:10px 28px; border-radius:10px;
                           font-weight:600; cursor:pointer; font-size:0.95rem;">
                        Close
                    </button>
                </div>
            </div>
        </div>
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
                        <?php
                        // Define the function for role counts
                        function getCountByRole(string $role): int
                        {
                            $query = "SELECT COUNT(lab_user.user_id) as count 
                  FROM lab_user 
                  INNER JOIN user_has_role ON lab_user.user_id = user_has_role.user_id 
                  INNER JOIN role ON user_has_role.role_id = role.role_id 
                  WHERE role.role = ?";

                            $types = "s";
                            $params = [$role];

                            $result = Database::search($query, $types, $params);

                            if ($result && $result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                return (int)$row['count'];
                            }

                            return 0;
                        }

                        // Get user counts by role
                        $student_count = getCountByRole('Student');
                        $supervisor_count = getCountByRole('Supervisor');
                        $technical_count = getCountByRole('Technical Officer');

                        // Calculate Equipment Utilization Rate
                        $utilization_query = "
        SELECT 
            (SELECT COALESCE(SUM(qty), 0) FROM equipment) as total_qty,
            (SELECT COALESCE(SUM(qty), 0) FROM broken) as broken_qty,
            (SELECT COALESCE(SUM(qty), 0) FROM equipment_maintenance) as maintenance_qty
    ";

                        $utilization_result = Database::search($utilization_query);
                        $utilization_rate = 0;

                        if ($utilization_result && $utilization_result->num_rows > 0) {
                            $row = $utilization_result->fetch_assoc();
                            $total_qty = (int)$row['total_qty'];
                            $broken_qty = (int)$row['broken_qty'];
                            $maintenance_qty = (int)$row['maintenance_qty'];

                            $available_qty = $total_qty - ($broken_qty + $maintenance_qty);

                            if ($total_qty > 0) {
                                $utilization_rate = round(($available_qty / $total_qty) * 100);
                            }
                        }
                        ?>

                        <div class="stat-card">
                            <i class="bi bi-mortarboard-fill"></i>
                            <h3><?php echo $student_count; ?></h3>
                            <p>Students</p>
                        </div>

                        <div class="stat-card">
                            <i class="bi bi-person-badge-fill"></i>
                            <h3><?php echo $supervisor_count; ?></h3>
                            <p>Supervisors</p>
                        </div>

                        <div class="stat-card">
                            <i class="bi bi-person-gear"></i>
                            <h3><?php echo $technical_count; ?></h3>
                            <p>Technical Officer</p>
                        </div>

                        <div class="stat-card">
                            <i class="bi bi-graph-up"></i>
                            <h3><?php echo $utilization_rate; ?>%</h3>
                            <p>Equipment Utilization Rate</p>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mb-4 justify-content-center">
                        <div class="col-md-3 mb-3">
                            <?php
                            // Count pending reservations
                            $pending_query = "SELECT COUNT(*) as pending_count FROM reservation WHERE status = 'Pending'";
                            $pending_result = Database::search($pending_query);
                            $pending_count = 0;

                            if ($pending_result && $pending_result->num_rows > 0) {
                                $row = $pending_result->fetch_assoc();
                                $pending_count = $row['pending_count'];
                            }
                            ?>

                            <div class="card p-3 text-center">
                                <h6 class="text-muted">TO Pending
                                    <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </h6>
                                <h3 class="text-warning"><?php echo $pending_count; ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <?php
                            // Get today's date in the same format as your database
                            $today = date('Y-m-d'); // Format: 2024-01-15

                            // Count reservations for today
                            $today_query = "SELECT COUNT(*) as today_count FROM reservation WHERE DATE(request_date) = ?";
                            $types = "s";
                            $params = [$today];

                            $today_result = Database::search($today_query, $types, $params);
                            $today_count = 0;

                            if ($today_result && $today_result->num_rows > 0) {
                                $row = $today_result->fetch_assoc();
                                $today_count = $row['today_count'];
                            }
                            ?>

                            <div class="card p-3 text-center">
                                <h6 class="text-muted">Today's Practicals
                                    <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </h6>
                                <h3 class="text-info"><?php echo $today_count; ?></h3>
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
                                <?php
                                // Count maintenance records with "Inprogress" status
                                $maintenance_query = "SELECT COUNT(equipment_maintenance.equipment_maintenance_id) as maintenance_count 
                          FROM equipment_maintenance 
                          INNER JOIN status_of_maintenance ON equipment_maintenance.status_of_maintenance_id = status_of_maintenance.status_of_maintenance_id 
                          WHERE status_of_maintenance.status = ?";

                                $types = "s";
                                $params = ["In Progress"]; // or "In Progress" depending on your exact value

                                $maintenance_result = Database::search($maintenance_query, $types, $params);
                                $maintenance_count = 0;

                                if ($maintenance_result && $maintenance_result->num_rows > 0) {
                                    $row = $maintenance_result->fetch_assoc();
                                    $maintenance_count = $row['maintenance_count'];
                                }
                                ?>

                                <h6 class="text-muted">Maintenance
                                    <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </h6>

                                <h3 class="text-danger"><?php echo $maintenance_count; ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Most Used Equipment -->
                    <div class="row">


                        <div class="col-md-6 mb-4">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h5 class="fw-bold mb-0" style="color: #166534;">Completed</h5>
                                    <small id="usageChartUpdated" class="text-muted" style="font-size:0.75rem;"></small>
                                </div>
                                <small class="text-muted mb-2 d-block" style="font-size:0.8rem;">

                                    Monthly completed reservations &nbsp; <span style="color:#3b82f6;font-size:0.75rem;">- -</span>Average · scroll →

                                </small>
                                <div class="chart-container" id="usageChartScroll"
                                    style="overflow-x:auto; overflow-y:hidden; padding-bottom:4px;">
                                    <div id="usageChartWrapper" style="height:100%; min-width:100%;">
                                        <canvas id="usageChart" style="height:100% !important;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Activity Chart -->

                        <div class="col-md-6 mb-4">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h5 class="fw-bold mb-0" style="color: #166534;">System Progress</h5>
                                    <small id="monthlyChartUpdated" class="text-muted" style="font-size:0.75rem;"></small>
                                </div>
                                <small class="text-muted mb-2 d-block" style="font-size:0.8rem;">
                                    Monthly sessions &nbsp;
                                    <span style="color:#22c55e;">●</span> Sessions &nbsp;
                                    <span style="color:#3b82f6;font-size:0.75rem;">- -</span> Average
                                    &nbsp;· scroll →
                                </small>

                                <!-- chart-container uses your existing CSS (height:300px, position:relative) -->
                                <div class="chart-container" id="monthlyChartScroll"
                                    style="overflow-x:auto; overflow-y:hidden; padding-bottom:4px;">
                                    <div id="monthlyChartWrapper" style="height:100%; min-width:100%;">
                                        <canvas id="monthlyChart" style="height:100% !important;"></canvas>
                                    </div>
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
                                placeholder="Search by ID, mobile, name or email..."
                                oninput="searchUsers()"> <!-- This triggers on EVERY keystroke, including backspace -->
                            <!-- <button class="search-btn" onclick="searchUsers()">
            <i class="bi bi-search"></i> Search
        </button> -->
                        </div>
                        <!-- <button class="add-btn" onclick="addNewUser()">
                        <i class="bi bi-plus-circle"></i> Add User
                    </button> -->
                    </div>

                    <!-- Student Table Card -->



                    <div id="studentTableCard" class="card p-4 mb-4">
                        <h4 class="table-heading mt-0">
                            <i class="bi bi-person-badge"></i> Students
                            <span class="table-count" id="studentCount">
                                <?php
                                // Get counts for both active and total
                                // $count_query = "SELECT 
                                //                     COUNT(*) as total,
                                //                     SUM(CASE WHEN lu.status_user = 1 THEN 1 ELSE 0 END) as active
                                //                FROM lab_user lu
                                //                INNER JOIN user_has_role uhr ON lu.user_id = uhr.user_id
                                //                INNER JOIN role r ON uhr.role_id = r.role_id
                                //                WHERE r.role = 'Student' 
                                //                AND lu.request_status_id = 5 
                                //                AND lu.approved_datetime IS NOT NULL";

                                // $count_result = Database::search($count_query);

                                // if ($count_result && $count_result->num_rows > 0) {
                                //     $row = $count_result->fetch_assoc();
                                //     $total_students = $row['total'];
                                //     $active_students = $row['active'];
                                //     echo "($active_students/$total_students)";

                                // } else {
                                //     echo "(0/0)";
                                // }
                                ?>
                            </span>
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
                                    <?php
                                    // Query to get ALL approved students (both active and inactive)
                                    $query = "SELECT 
                            lu.user_id,
                            lu.first_name,
                            lu.last_name,
                            lu.university_id,
                            lu.mobile,
                            lu.email,
                            lu.img_path,
                            lu.status_user,
                            lu.request_status_id,
                            lu.approved_datetime
                          FROM lab_user lu
                          INNER JOIN user_has_role uhr ON lu.user_id = uhr.user_id
                          INNER JOIN role r ON uhr.role_id = r.role_id
                          WHERE r.role = 'Student' 
                          AND lu.request_status_id = 5 
                          AND lu.approved_datetime IS NOT NULL
                          ORDER BY lu.status_user DESC, lu.join_datetime DESC"; // Active first, then inactive

                                    $result = Database::search($query);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $user_id = $row['user_id'];
                                            $full_name = $row['first_name'] . ' ' . $row['last_name'];

                                            // Fix image path
                                            $profile_image = !empty($row['img_path'])
                                                ? '../' . $row['img_path']
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=50';

                                            // Format mobile number
                                            $mobile = $row['mobile'];
                                            if (strlen($mobile) == 10) {
                                                $mobile = substr($mobile, 0, 3) . '-' . substr($mobile, 3, 3) . '-' . substr($mobile, 6, 4);
                                            }

                                            // Set status based on database value
                                            $status = ($row['status_user'] == 1) ? 'active' : 'inactive';

                                            // Output the row with dynamic status
                                            echo '<tr data-user-id="' . htmlspecialchars($row['university_id']) . '" data-status="' . $status . '">';
                                            echo '<td>';
                                            echo '<img src="' . htmlspecialchars($profile_image) . '" 
                                   style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #22c55e;">';
                                            echo '</td>';
                                            echo '<td>' . htmlspecialchars($row['university_id']) . '</td>';
                                            echo '<td>' . htmlspecialchars($full_name) . '</td>';
                                            echo '<td>' . htmlspecialchars($mobile) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                            echo '<td>';
                                            echo '<div class="action-buttons">';
                                            echo '<button class="btn-edit" onclick="editUser(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                            echo '<i class="bi bi-pencil-square"></i> Edit';
                                            echo '</button>';

                                            // ✅ Show appropriate button based on status_user
                                            if ($row['status_user'] == 1) {
                                                // ACTIVE user - show RED Deactivate button
                                                echo '<button class="btn-deactivate" onclick="toggleUserStatus(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                                echo '<i class="bi bi-person-x"></i> Deactivate';
                                                echo '</button>';
                                            } else {
                                                // INACTIVE user - show GREEN Activate button
                                                echo '<button class="btn-activate" onclick="toggleUserStatus(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                                echo '<i class="bi bi-person-check"></i> Activate';
                                                echo '</button>';
                                            }

                                            echo '</div>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center py-4">No approved students found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>









                    <!-- <div id="studentTableCard" class="card p-4 mb-4">  -->
                    <!-- <h4 class="table-heading mt-0">
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
                </div> -->

                    <!-- Supervisor/Lecturer Table Card -->

                    <!-- Supervisor/Lecturer Table Card -->
                    <div id="supervisorTableCard" class="card p-4 mb-4">
                        <h4 class="table-heading mt-0">
                            <i class="bi bi-person-workspace"></i> Supervisors & Lecturers
                            <span class="table-count" id="supervisorCount">

                            </span>
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
                                    <?php
                                    // Query to get ALL approved supervisors (both active and inactive)
                                    $sup_query = "SELECT 
                                lu.user_id,
                                lu.first_name,
                                lu.last_name,
                                lu.university_id,
                                lu.mobile,
                                lu.email,
                                lu.img_path,
                                lu.status_user,
                                lu.request_status_id,
                                lu.approved_datetime
                              FROM lab_user lu
                              INNER JOIN user_has_role uhr ON lu.user_id = uhr.user_id
                              INNER JOIN role r ON uhr.role_id = r.role_id
                              WHERE r.role = 'Supervisor' 
                              AND lu.request_status_id = 5 
                              AND lu.approved_datetime IS NOT NULL
                              ORDER BY lu.status_user DESC, lu.join_datetime DESC";

                                    $sup_result = Database::search($sup_query);

                                    if ($sup_result && $sup_result->num_rows > 0) {
                                        while ($row = $sup_result->fetch_assoc()) {
                                            $full_name = $row['first_name'] . ' ' . $row['last_name'];

                                            // Fix image path
                                            $profile_image = !empty($row['img_path'])
                                                ? '../' . $row['img_path']
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=50';

                                            // Format mobile number
                                            $mobile = $row['mobile'];
                                            if (strlen($mobile) == 10) {
                                                $mobile = substr($mobile, 0, 3) . '-' . substr($mobile, 3, 3) . '-' . substr($mobile, 6, 4);
                                            }

                                            // Set status based on database value
                                            $status = ($row['status_user'] == 1) ? 'active' : 'inactive';

                                            // Output the row with dynamic status
                                            echo '<tr data-user-id="' . htmlspecialchars($row['university_id']) . '" data-status="' . $status . '">';
                                            echo '<td>';
                                            echo '<img src="' . htmlspecialchars($profile_image) . '" 
                                   style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #22c55e;">';
                                            echo '</td>';
                                            echo '<td>' . htmlspecialchars($row['university_id']) . '</td>';
                                            echo '<td>' . htmlspecialchars($full_name) . '</td>';
                                            echo '<td>' . htmlspecialchars($mobile) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                            echo '<td>';
                                            echo '<div class="action-buttons">';
                                            echo '<button class="btn-edit" onclick="editUser(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                            echo '<i class="bi bi-pencil-square"></i> Edit';
                                            echo '</button>';

                                            // Show appropriate button based on status_user
                                            if ($row['status_user'] == 1) {
                                                // ACTIVE user - show RED Deactivate button
                                                echo '<button class="btn-deactivate" onclick="toggleUserStatus(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                                echo '<i class="bi bi-person-x"></i> Deactivate';
                                                echo '</button>';
                                            } else {
                                                // INACTIVE user - show GREEN Activate button
                                                echo '<button class="btn-activate" onclick="toggleUserStatus(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                                echo '<i class="bi bi-person-check"></i> Activate';
                                                echo '</button>';
                                            }

                                            echo '</div>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center py-4">No approved supervisors found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- <div id="supervisorTableCard" class="card p-4 mb-4">
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
                </div> -->













                    <!-- Technical Officer Table Card -->

                    <!-- Technical Officer Table Card -->
                    <div id="techOfficerTableCard" class="card p-4">
                        <h4 class="table-heading mt-0">
                            <i class="bi bi-person-gear"></i> Technical Officers
                            <span class="table-count" id="techOfficerCount">

                            </span>
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
                                    <?php
                                    // Query to get ALL approved technical officers (both active and inactive)
                                    $tech_query = "SELECT 
                                lu.user_id,
                                lu.first_name,
                                lu.last_name,
                                lu.university_id,
                                lu.mobile,
                                lu.email,
                                lu.img_path,
                                lu.status_user,
                                lu.request_status_id,
                                lu.approved_datetime
                              FROM lab_user lu
                              INNER JOIN user_has_role uhr ON lu.user_id = uhr.user_id
                              INNER JOIN role r ON uhr.role_id = r.role_id
                              WHERE r.role = 'Technical Officer' 
                              AND lu.request_status_id = 5 
                              AND lu.approved_datetime IS NOT NULL
                              ORDER BY lu.status_user DESC, lu.join_datetime DESC";

                                    $tech_result = Database::search($tech_query);

                                    if ($tech_result && $tech_result->num_rows > 0) {
                                        while ($row = $tech_result->fetch_assoc()) {
                                            $full_name = $row['first_name'] . ' ' . $row['last_name'];

                                            // Fix image path
                                            $profile_image = !empty($row['img_path'])
                                                ? '../' . $row['img_path']
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=50';

                                            // Format mobile number
                                            $mobile = $row['mobile'];
                                            if (strlen($mobile) == 10) {
                                                $mobile = substr($mobile, 0, 3) . '-' . substr($mobile, 3, 3) . '-' . substr($mobile, 6, 4);
                                            }

                                            // Set status based on database value
                                            $status = ($row['status_user'] == 1) ? 'active' : 'inactive';

                                            // Output the row with dynamic status
                                            echo '<tr data-user-id="' . htmlspecialchars($row['university_id']) . '" data-status="' . $status . '">';
                                            echo '<td>';
                                            echo '<img src="' . htmlspecialchars($profile_image) . '" 
                                   style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #22c55e;">';
                                            echo '</td>';
                                            echo '<td>' . htmlspecialchars($row['university_id']) . '</td>';
                                            echo '<td>' . htmlspecialchars($full_name) . '</td>';
                                            echo '<td>' . htmlspecialchars($mobile) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                            echo '<td>';
                                            echo '<div class="action-buttons">';
                                            echo '<button class="btn-edit" onclick="editUser(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                            echo '<i class="bi bi-pencil-square"></i> Edit';
                                            echo '</button>';

                                            // Show appropriate button based on status_user
                                            if ($row['status_user'] == 1) {
                                                // ACTIVE user - show RED Deactivate button
                                                echo '<button class="btn-deactivate" onclick="toggleUserStatus(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                                echo '<i class="bi bi-person-x"></i> Deactivate';
                                                echo '</button>';
                                            } else {
                                                // INACTIVE user - show GREEN Activate button
                                                echo '<button class="btn-activate" onclick="toggleUserStatus(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                                echo '<i class="bi bi-person-check"></i> Activate';
                                                echo '</button>';
                                            }

                                            echo '</div>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center py-4">No approved technical officers found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- <div id="techOfficerTableCard" class="card p-4"> 
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
            </div> -->








                <!-- Equipment Management Section -->
                <div id="equipmentSection" style="display: none;">
                    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment Management</h3>


                    <div class="search-add-row" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <div class="search-container" style="flex: 1; max-width: 500px;">
                            <input type="text"
                                id="equipmentSearch"
                                class="search-input"
                                placeholder="Search by code, name or location..."
                                oninput="searchEquipment()">
                        </div>
                        <button class="add-btn" onclick="sendToMaintenance()" style="margin-right: 4px; background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="bi bi-tools"></i> Send to Maintenance
                        </button>
                        <button class="add-btn" onclick="addEquipment()" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                            <i class="bi bi-plus-circle"></i> Add Equipment
                        </button>
                    </div>


                    <div id="equipmentTableCard" class="card p-4">
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

                                        <th>Maintenance Pending</th>
                                        <th>Usage %</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="equipmentTableBody">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>


                            </table>
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
                            <!-- <button class="search-btn" onclick="searchReservations()">
                                <i class="bi bi-search"></i> Search
                            </button> -->
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
                           <button class="add-btn" style="background: linear-gradient(135deg, #f87171, #ef4444);">
    <i class="bi bi-plus-circle"></i> Block Reservation
</button>
                        <!-- <button class="add-btn" onclick="addReservation()" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                            <i class="bi bi-plus-circle"></i> Add Reservation
                        </button> -->
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











































































































                <!-- Request Section -->
                <!-- Request Section -->
<div id="activitySection" style="display: none;">
    <h3 class="mb-4" style="color:white; text-shadow:2px 2px 4px rgba(0,0,0,0.2);">Requests</h3>

    <div class="card p-4">

        <!-- Tabs -->
        <div class="request-tabs" style="margin-bottom:20px;">
            <button class="request-tab active" onclick="switchRequestType('technical')">
                Technical Officer Requests
                <span class="request-count-badge" id="technicalRequestCount">0</span>
            </button>
            <button class="request-tab" onclick="switchRequestType('supervisor')">
                Supervisor Requests
                <span class="request-count-badge" id="supervisorRequestCount">0</span>
            </button>
        </div>

        <!-- Time filter -->
        <div class="filter-section" style="margin-bottom:20px;">
            <select class="filter-select" id="timeRangeFilter"
                    onchange="filterRequestsByTime()" style="min-width:200px;">
                <option value="all">All Time</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>
        </div>

        <!-- Table -->
        <div class="table-responsive mt-3">
            <table class="user-table">
                <thead>
                    <tr id="reqTableHead">
                        <!-- injected by JS -->
                    </tr>
                </thead>
                <tbody id="requestListBody">
                    <!-- injected by JS -->
                </tbody>
            </table>
        </div>

    </div>
</div><!-- end activitySection -->


<!-- ── Request Detail Modal (replaces old requestDetailsModal) ── -->
<div id="reqDetailModal"
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.6); z-index:999998;
            justify-content:center; align-items:center; overflow-y:auto;">
    <div style="background:white; border-radius:16px; width:90%; max-width:560px;
                margin:30px auto; box-shadow:0 25px 60px rgba(0,0,0,0.4); position:relative;">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,#22c55e,#16a34a);
                    padding:18px 24px; border-radius:16px 16px 0 0;
                    display:flex; justify-content:space-between; align-items:center;">
            <h5 style="margin:0; color:white; font-weight:600;">
                <i class="bi bi-journal-check"></i>&nbsp;Request Details
            </h5>
            <button onclick="closeReqModal()"
                style="background:rgba(255,255,255,0.25); border:none; color:white;
                       width:34px; height:34px; border-radius:50%; cursor:pointer;
                       font-size:1.1rem; display:flex; align-items:center; justify-content:center;">✕</button>
        </div>

        <!-- Body -->
        <div id="reqModalContent"
             style="padding:24px; max-height:55vh; overflow-y:auto;"></div>

        <!-- Footer -->
        <div style="padding:16px 24px; border-top:1px solid #eee;
                    display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap;">
            <button id="reqApproveBtn" onclick="submitReqAction('approve')"
                style="background:linear-gradient(135deg,#22c55e,#16a34a);
                       color:white; border:none; padding:10px 22px;
                       border-radius:10px; font-weight:600; cursor:pointer;">
                <i class="bi bi-check-circle me-1"></i>Approve
            </button>
            <button id="reqRejectBtn" onclick="openRejectBox()"
                style="background:linear-gradient(135deg,#ef4444,#dc2626);
                       color:white; border:none; padding:10px 22px;
                       border-radius:10px; font-weight:600; cursor:pointer;">
                <i class="bi bi-x-circle me-1"></i>Reject
            </button>
            <button onclick="closeReqModal()"
                style="background:#6c757d; color:white; border:none;
                       padding:10px 22px; border-radius:10px; font-weight:600; cursor:pointer;">
                Close
            </button>
        </div>

    </div>
</div>


<!-- ── Reject-reason sub-modal ──────────────────────────────────── -->
<div id="reqRejectModal"
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.72); z-index:999999;
            justify-content:center; align-items:center;">
    <div style="background:white; border-radius:16px; width:90%; max-width:420px;
                padding:28px; box-shadow:0 20px 50px rgba(0,0,0,0.4);">
        <h5 style="color:#dc2626; margin-bottom:16px;">
            <i class="bi bi-exclamation-triangle me-2"></i>Rejection Reason
        </h5>
        <textarea id="reqRejectText" rows="4"
            placeholder="Enter reason for rejection..."
            style="width:100%; padding:12px; border:2px solid #e0e0e0;
                   border-radius:10px; font-size:0.95rem; resize:vertical;
                   font-family:inherit; box-sizing:border-box;"></textarea>
        <div style="margin-top:16px; display:flex; justify-content:flex-end; gap:10px;">
            <button onclick="closeRejectBox()"
                style="background:#6c757d; color:white; border:none;
                       padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer;">
                Cancel
            </button>
            <button onclick="submitReqAction('reject')"
                style="background:linear-gradient(135deg,#ef4444,#dc2626);
                       color:white; border:none; padding:10px 22px;
                       border-radius:10px; font-weight:600; cursor:pointer;">
                Confirm Reject
            </button>
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
                                <!-- Add this after the equipment usage table in analytics section (around line 1890) -->
                                <!-- <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card p-4">
                                            <h5 class="fw-bold mb-3" style="color: #166534;">Equipment Usage Overview</h5>
                                            <div class="chart-container">
                                                <canvas id="equipmentUsageChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
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

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>




        <!-- Scripts -->
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // ── 1. ADD this new function ─────────────────────────────────
            // Fetches equipment + AI usage % from PHP controller
            function loadEquipmentWithUsage() {
                const tableBody = document.getElementById('equipmentTableBody');
                if (!tableBody) return;

                // Show loading spinner while Python runs
                tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-success me-2" role="status"
                     style="width:1.5rem;height:1.5rem;"></div>
                <span style="color:#166534;font-weight:600;">
                    🤖 AI analysing equipment usage...
                </span>
            </td>
        </tr>`;

                // hod.php is in LRRS/views/
                // controller is in LRRS/controllers/
                fetch('../controllers/get_equipment_usage.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayEquipmentTable(data.equipment);

                            // Update count + show last analyzed time as tooltip
                            const countEl = document.getElementById('equipmentCount');
                            if (countEl) {
                                countEl.textContent = `(${data.count})`;
                                countEl.title = `AI analyzed at: ${data.analyzed_at}`;
                            }
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
                        ❌ Connection error — check PHP server is running.
                    </td>
                </tr>`;
                    });
            }







































































































            // ========== REQUEST SECTION FUNCTIONS ==========
          // ========== REQUEST SECTION FUNCTIONS (DB-connected) ==========

let currentRequestType = 'technical';
let currentRequestId   = null;   // integer PK of selected reservation
let allRequests        = [];     // raw data from server

// ── Called from showSection('activity') ─────────────────────────
function initRequestSection() {
    currentRequestType = 'technical';
    // Ensure correct tab is highlighted
    const tabs = document.querySelectorAll('.request-tab');
    tabs.forEach(t => t.classList.remove('active'));
    if (tabs[0]) tabs[0].classList.add('active');
    // Reset filter
    const filter = document.getElementById('timeRangeFilter');
    if (filter) filter.value = 'all';
    // Load badges for both tabs immediately
    fetchBothBadgeCounts();
    // Load table
    loadRequests('technical');
}

// ── Fetch data from PHP ──────────────────────────────────────────
function loadRequests(type) {
    const tableBody = document.getElementById('requestListBody');
    if (!tableBody) return;

    // Set column headers
    const head = document.getElementById('reqTableHead');
    if (head) {
        const idLabel = (type === 'technical') ? 'Technical Officer ID' : 'Supervisor ID';
        head.innerHTML = `
            <th>Reservation ID</th>
            <th>${idLabel}</th>
            <th>Date &amp; Time</th>
            <th>Status</th>
            <th>Action</th>`;
    }

    tableBody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-success me-2"></div>
                Loading...
            </td>
        </tr>`;

    fetch(`../controllers/get_requests.php?type=${type}`)
        .then(r => r.json())
        .then(res => {
            allRequests = res.success ? res.data : [];
            // Update THIS tab's badge
            const badgeId = (type === 'technical') ? 'technicalRequestCount' : 'supervisorRequestCount';
            const badgeEl = document.getElementById(badgeId);
            if (badgeEl) badgeEl.textContent = allRequests.length;
            filterRequestsByTime();
        })
        .catch(() => {
            tableBody.innerHTML =
                `<tr><td colspan="5" class="text-center py-4 text-danger">
                    ❌ Failed to load requests. Check server connection.
                 </td></tr>`;
        });
}

// ── Tab switch ───────────────────────────────────────────────────
function switchRequestType(type) {
    currentRequestType = type;
    const tabs = document.querySelectorAll('.request-tab');
    tabs.forEach(t => t.classList.remove('active'));
    tabs[type === 'technical' ? 0 : 1].classList.add('active');
    document.getElementById('timeRangeFilter').value = 'all';
    loadRequests(type);
}

// ── Time filter ──────────────────────────────────────────────────
function filterRequestsByTime() {
    const range = document.getElementById('timeRangeFilter')?.value || 'all';
    const now   = new Date();

    const filtered = allRequests.filter(item => {
        const d = new Date(item.date);
        if (range === 'daily')   return d.toDateString() === now.toDateString();
        if (range === 'weekly')  { const c = new Date(); c.setDate(now.getDate()-7);  return d >= c; }
        if (range === 'monthly') { const c = new Date(); c.setDate(now.getDate()-30); return d >= c; }
        return true;
    });

    renderRequestTable(filtered);
}

// ── Build table rows ─────────────────────────────────────────────
function renderRequestTable(rows) {
    const tbody = document.getElementById('requestListBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (rows.length === 0) {
        tbody.innerHTML =
            `<tr><td colspan="5" class="text-center py-4 text-muted">
                No pending requests found
             </td></tr>`;
        return;
    }

    rows.forEach(item => {
        // Green check-mark status pill
        const statusPill = `
            <span style="display:inline-flex; align-items:center; gap:5px;
                         background:#dcfce7; color:#166534; padding:4px 12px;
                         border-radius:20px; font-size:0.82rem; font-weight:600;">
                <i class="bi bi-check-circle-fill" style="color:#22c55e;"></i>
                Pending
            </span>`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.display_id}</td>
            <td><strong style="color:#166534;">${item.officer_id}</strong></td>
            <td>${item.date}</td>
            <td>${statusPill}</td>
            <td>
                <button class="btn-view" onclick="openReqDetail(${item.id})">
                    <i class="bi bi-eye"></i> View
                </button>
            </td>`;
        tbody.appendChild(tr);
    });
}

// ── Open detail modal ────────────────────────────────────────────
function openReqDetail(id) {
    const item = allRequests.find(r => r.id === id);
    if (!item) return;
    currentRequestId = id;

    const roleLabel = (currentRequestType === 'technical') ? 'Technical Officer ID' : 'Supervisor ID';

    // Notification banner (comment fields)
    let notifHtml = '';
    if (item.comment || item.any_comment) {
        const msg = item.comment || item.any_comment;
        notifHtml = `
            <div style="background:#fffbeb; border-left:4px solid #f59e0b;
                        padding:12px 16px; border-radius:8px; margin-bottom:16px;
                        display:flex; align-items:flex-start; gap:10px;">
                <i class="bi bi-bell-fill" style="color:#f59e0b; font-size:1.1rem; margin-top:2px;"></i>
                <div>
                    <div style="font-weight:600; color:#92400e; font-size:0.85rem; margin-bottom:2px;">
                        Notification / Comment
                    </div>
                    <div style="color:#78350f; font-size:0.9rem;">${msg}</div>
                </div>
            </div>`;
    }

    document.getElementById('reqModalContent').innerHTML = `
        ${notifHtml}
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0; width:170px;">
                    Reservation ID:
                </td>
                <td style="padding:9px 0;">
                    <strong>${item.display_id}</strong>
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0;
                            border-top:1px solid #f0f0f0;">
                    ${roleLabel}:
                </td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    <span style="background:#e8f5e9; color:#166534; padding:3px 12px;
                                 border-radius:12px; font-weight:700;">
                        ${item.officer_id}
                    </span>
                    <span style="margin-left:8px; color:#555; font-size:0.9rem;">
                        ${item.officer_name}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0;
                            border-top:1px solid #f0f0f0;">Student ID:</td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    ${item.student_id}
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0;
                            border-top:1px solid #f0f0f0;">Lab Location:</td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>${item.lab}
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0;
                            border-top:1px solid #f0f0f0;">Date &amp; Time:</td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    ${item.date}
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0;
                            border-top:1px solid #f0f0f0;">Status:</td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    <span style="background:#dcfce7; color:#166534; padding:4px 14px;
                                 border-radius:20px; font-weight:600; font-size:0.85rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>Pending Review
                    </span>
                </td>
            </tr>
        </table>`;

    document.getElementById('reqDetailModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeReqModal() {
    document.getElementById('reqDetailModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Close on backdrop click
document.getElementById('reqDetailModal').addEventListener('click', function(e) {
    if (e.target === this) closeReqModal();
});

// ── Reject sub-modal ─────────────────────────────────────────────
function openRejectBox() {
    document.getElementById('reqRejectText').value = '';
    document.getElementById('reqRejectModal').style.display = 'flex';
}

function closeRejectBox() {
    document.getElementById('reqRejectModal').style.display = 'none';
}

// ── Submit approve / reject ──────────────────────────────────────
function submitReqAction(action) {
    if (!currentRequestId) return;

    const reason = action === 'reject'
                   ? (document.getElementById('reqRejectText')?.value.trim() || '')
                   : '';

    if (action === 'reject' && !reason) {
        alert('Please enter a rejection reason.');
        return;
    }

    // Disable buttons
    ['reqApproveBtn','reqRejectBtn'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.disabled = true;
    });

    const fd = new FormData();
    fd.append('reservation_id', currentRequestId);
    fd.append('action', action);
    if (reason) fd.append('reason', reason);

    fetch('../controllers/process_request_action.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeRejectBox();
                closeReqModal();
                showSuccess(`Request ${action === 'approve' ? 'approved ✓' : 'rejected'} successfully!`);
                // Refresh table + both badges
                loadRequests(currentRequestType);
                fetchBothBadgeCounts();
            } else {
                showError(res.message || 'Action failed.');
            }
        })
        .catch(() => showError('Network error. Please try again.'))
        .finally(() => {
            ['reqApproveBtn','reqRejectBtn'].forEach(id => {
                const btn = document.getElementById(id);
                if (btn) btn.disabled = false;
            });
        });
}

// ── Refresh both tab badge counts ───────────────────────────────
function fetchBothBadgeCounts() {
    Promise.all([
        fetch('../controllers/get_requests.php?type=technical').then(r => r.json()),
        fetch('../controllers/get_requests.php?type=supervisor').then(r => r.json())
    ]).then(([tech, sup]) => {
        const tCount = tech.success ? tech.count : 0;
        const sCount = sup.success  ? sup.count  : 0;
        document.getElementById('technicalRequestCount').textContent = tCount;
        document.getElementById('supervisorRequestCount').textContent = sCount;
        const badge = document.getElementById('requestBadge');
        if (badge) badge.textContent = tCount + sCount;
    }).catch(() => {});
}

// Keep old name working (called from DOMContentLoaded)
function updateRequestCounts() { fetchBothBadgeCounts(); }





















































































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
            // ========== USER ACTIVATE/DEACTIVATE FUNCTION WITH AJAX ==========
            function toggleUserStatus(userId) {
                const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                if (!userRow) return;

                const currentStatus = userRow.getAttribute('data-status');
                const actionCell = userRow.querySelector('.action-buttons');
                const button = actionCell.querySelector(currentStatus === 'active' ? '.btn-deactivate' : '.btn-activate');

                // Determine new status
                const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                const action = currentStatus === 'active' ? 'deactivate' : 'activate';

                // Confirm action
                if (!confirm(`Are you sure you want to ${action} user ${userId}?`)) {
                    return;
                }

                // Show loading state
                const originalButtonHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

                // Create FormData
                const formData = new FormData();
                formData.append('user_id', userId);
                formData.append('action', action);

                // Send AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../controllers/activate_process.php', true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.onload = function() {
                    button.disabled = false;

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);

                            if (response.success) {
                                // Update UI on success
                                userRow.setAttribute('data-status', newStatus);

                                if (newStatus === 'active') {
                                    button.className = 'btn-deactivate';
                                    button.innerHTML = '<i class="bi bi-person-x"></i> Deactivate';
                                    showSuccess(`User ${userId} has been activated successfully!`);
                                    loadUserCounts();
                                } else {
                                    button.className = 'btn-activate';
                                    button.innerHTML = '<i class="bi bi-person-check"></i> Activate';
                                    showSuccess(`User ${userId} has been deactivated successfully!`);
                                    loadUserCounts();
                                }

                                // Update counts if function exists
                                if (typeof updateUserCounts === 'function') {
                                    updateUserCounts();
                                }

                            } else {
                                button.innerHTML = originalButtonHtml;
                                showError(response.message || `Failed to ${action} user.`);
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                            button.innerHTML = originalButtonHtml;
                            showError('Server error occurred.');
                        }
                    } else {
                        button.innerHTML = originalButtonHtml;
                        showError('Connection error. Please try again.');
                    }
                };

                xhr.onerror = function() {
                    button.disabled = false;
                    button.innerHTML = originalButtonHtml;
                    showError('Network error. Please check your connection.');
                };

                xhr.ontimeout = function() {
                    button.disabled = false;
                    button.innerHTML = originalButtonHtml;
                    showError('Request timed out. Please try again.');
                };

                xhr.timeout = 30000;
                xhr.send(formData);
            }

            // Helper function to show success messages
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

            // Helper function to show error messages
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

            // Optional: Function to update user counts
            function updateUserCounts() {
                // You can implement this to refresh counts
                // For example, reload the student count
                console.log('User counts updated');
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
                const tables = [{
                        id: 'studentTableBody',
                        countId: 'studentCount'
                    },
                    {
                        id: 'supervisorTableBody',
                        countId: 'supervisorCount'
                    },
                    {
                        id: 'techOfficerTableBody',
                        countId: 'techOfficerCount'
                    }
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

            function loadUserCounts() {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '../controllers/get_user_counts.php', true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                // Update student counts
                                document.getElementById('studentCount').innerHTML =
                                    `(${response.students.active}/${response.students.total})`;

                                // Update supervisor counts
                                document.getElementById('supervisorCount').innerHTML =
                                    `(${response.supervisors.active}/${response.supervisors.total})`;

                                // Update technical officer counts
                                document.getElementById('techOfficerCount').innerHTML =
                                    `(${response.technical.active}/${response.technical.total})`;
                            }
                        } catch (e) {
                            console.error('Error loading user counts:', e);
                        }
                    }
                };

                xhr.send();
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
                    loadEquipmentWithUsage(); // ← REPLACE old line
                }
                if (section === 'dashboard' || section === 'analytics') {
                    setTimeout(() => {
                        initCharts();
                        initAnalyticsCharts();
                        initCalendar(); // Add this line
                        initCalendarListeners(); // Add this line
                    }, 100);
                }
                if (section === 'history') {
                    document.getElementById('reservationSearch').value = '';
                    document.getElementById('statusFilter').value = 'all';
                    loadReservations();
                }
                if (section === 'activity') filterRequestsByTime();
            }

            // ========== EQUIPMENT FUNCTIONS ==========
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

            const equipmentDataTable = [{
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

            // ── 2. REPLACE your existing displayEquipmentTable() ─────────
            function displayEquipmentTable(equipment) {
                const tableBody = document.getElementById('equipmentTableBody');
                if (!tableBody) return;

                tableBody.innerHTML = '';

                if (!equipment || equipment.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No equipment found</td></tr>';
                    return;
                }
                equipment.sort((a, b) => b.usage - a.usage);
                equipment.forEach(item => {
                    const code = item.code || 'N/A';
                    const name = item.name || 'Unknown';
                    const image = item.image || 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';
                    const maintenance = item.maintenance || 0;
                    const usage = Math.round(parseFloat(item.usage) || 0); // ← from Python AI

                    // Bar colour based on usage level
                    const barColor = '#22c55e'; // always green

                    const row = document.createElement('tr');
                    row.setAttribute('data-equipment-id', code);

                    row.innerHTML = `
            <td>
                <img src="${image}"
                     style="width:50px;height:50px;object-fit:contain;"
                     onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'">
            </td>
            <td>${code}</td>
            <td>${name}</td>
            <td>
              ${maintenance > 0 ? `<span class="badge bg-warning">${maintenance}</span>` : '------'}
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:80px;height:8px;background:#e9ecef;
                                border-radius:4px;overflow:hidden;">
                        <div style="width:${usage}%;height:8px;
                                    background:${barColor};border-radius:4px;
                                    transition:width 0.6s ease;"></div>
                    </div>
                    <span style="font-weight:600;color:${barColor};min-width:44px;">
                        ${usage}%
                    </span>
                </div>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view"
                            onclick="viewEquipment('${code}')" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-edit"
                            onclick="editEquipment('${code}')" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn-remove"
                            onclick="removeEquipment('${code}')" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
                    tableBody.appendChild(row);
                });

                document.getElementById('equipmentCount').textContent = `(${equipment.length})`;
            }


            // ── PASTE THIS ENTIRE FUNCTION into hod.php, replacing the old viewEquipment() ──

            function viewEquipment(code) {

                // Show modal immediately with spinner
                document.getElementById('equipmentDetailsContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status"
                 style="width:2rem;height:2rem;"></div>
            <p class="mt-3 text-muted small fw-semibold">
                🤖 AI is analysing usage data...
            </p>
        </div>
    `;
                new bootstrap.Modal(document.getElementById('equipmentDetailsModal')).show();

                fetch(`get_equipment_details.php?code=${encodeURIComponent(code)}`)
                    .then(res => res.json())
                    .then(eq => {

                        // ── DEBUG mode (when $DEBUG=true in PHP) ─────────────────────────
                        if (eq.debug) {
                            document.getElementById('equipmentDetailsContent').innerHTML = `
                    <div class="p-3">
                        <div class="alert alert-warning">
                            <strong>🔧 DEBUG MODE ON</strong> — set <code>$DEBUG = false</code> for production.
                        </div>
                        <p><strong>shell_exec:</strong> ${eq.shell_exec_enabled}</p>
                        <p><strong>python.exe exists:</strong> ${eq.python_exe_exists}</p>
                        <p><strong>script exists:</strong> ${eq.script_exists}</p>
                        <p><strong>Python raw output:</strong></p>
                        <pre class="bg-light p-3 rounded" style="font-size:12px;max-height:200px;overflow-y:auto;">${eq.raw_output || '(empty)'}</pre>
                        <p><strong>JSON valid:</strong> ${eq.json_decode_result ? '✅ Yes' : '❌ No — Python output is not valid JSON'}</p>
                        <p><strong>JSON error:</strong> ${eq.json_last_error}</p>
                        <hr>
                        <p><strong>Equipment DB row:</strong></p>
                        <pre class="bg-light p-3 rounded" style="font-size:12px;">${JSON.stringify(eq.equipment_db_row, null, 2)}</pre>
                    </div>
                `;
                            return;
                        }

                        // ── PHP-level error ───────────────────────────────────────────────
                        if (eq.error) {
                            document.getElementById('equipmentDetailsContent').innerHTML = `
                    <div class="alert alert-danger m-3">
                        <i class="bi bi-exclamation-circle me-2"></i>${eq.error}
                    </div>`;
                            return;
                        }

                        // ── Safe AI defaults ──────────────────────────────────────────────
                        const ai = eq.ai || {
                            student_id: null,
                            university_id: null,
                            full_name: null,
                            reservation_id: null,
                            confidence: 0,
                            sentiment: "Unknown",
                            keywords: [],
                            analysis: "AI analysis unavailable.",
                            raw_comment: "",
                            mention_found: false
                        };

                        // ── Grant section (only shown if grant_name exists) ───────────────
                        const grantHTML = eq.grant_name ? `
                <tr>
                    <td colspan="2">
                        <div class="mt-2 mb-1">
                            <span class="fw-semibold small text-uppercase" style="color:#d97706;">
                                <i class="bi bi-award-fill me-1"></i>Grant Information
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal" style="width:150px">Grant Name</th>
                    <td><span class="badge bg-warning text-dark">${eq.grant_name}</span></td>
                </tr>
                ${eq.grant_project ? `
                <tr>
                    <th class="text-muted fw-normal">Project</th>
                    <td>${eq.grant_project}</td>
                </tr>` : ''}
                ${(eq.grant_start || eq.grant_end) ? `
                <tr>
                    <th class="text-muted fw-normal">Period</th>
                    <td>${eq.grant_start || '?'} — ${eq.grant_end || '?'}</td>
                </tr>` : ''}
            ` : '';

                        // ── AI section ────────────────────────────────────────────────────
                        const confPct = Math.round((ai.confidence || 0) * 100);
                        const confColor = confPct >= 70 ? 'success' : confPct >= 40 ? 'warning' : 'danger';

                        const aiHTML = ai.student_id ? `
                <tr>
                    <td colspan="2">
                        <div class="mt-2 mb-1">
                            <span class="fw-semibold small text-uppercase text-primary">
                                <i class="bi bi-cpu-fill me-1"></i>AI Analysis — Last Usage
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal" style="width:150px">Last Used By</th>
                    <td>
                        <span class="badge bg-primary fs-6">
                            <i class="bi bi-person-fill me-1"></i>${ai.university_id || ai.student_id}
                        </span>
                        ${ai.full_name
                            ? `<span class="ms-2 fw-semibold text-dark">${ai.full_name}</span>`
                            : ''}
                        <br>
                        <small class="text-muted">Reservation #${ai.reservation_id}</small>
                        ${ai.mention_found === false
                            ? `<br><small class="text-warning">
                                   <i class="bi bi-exclamation-triangle me-1"></i>
                                   Equipment not explicitly mentioned in comments
                               </small>`
                            : ''}
                    </td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal">AI Summary</th>
                    <td><small class="text-muted">${ai.analysis}</small></td>
                </tr>
                ${ai.raw_comment ? `
                <tr>
                    <th class="text-muted fw-normal">Comment</th>
                    <td><small class="fst-italic text-muted">"${ai.raw_comment}"</small></td>
                </tr>` : ''}
            ` : `
                <tr>
                    <td colspan="2">
                        <div class="mt-2 mb-1">
                            <span class="fw-semibold small text-uppercase text-primary">
                                <i class="bi bi-cpu-fill me-1"></i>AI Analysis — Last Usage
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="text-muted fst-italic small">
                            <i class="bi bi-info-circle me-1"></i>
                            No completed usage records found for this equipment.
                        </span>
                    </td>
                </tr>
            `;

                        // ── Build full modal ──────────────────────────────────────────────
                        document.getElementById('equipmentDetailsContent').innerHTML = `
                <div class="row g-0">
                    <!-- Left: image + name + code -->
                    <div class="col-md-4 text-center border-end p-4">
                        <img src="${eq.image_path ? '../' + eq.image_path
                                                   : 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png'}"
                             style="width:140px;height:140px;object-fit:contain;"
                             class="rounded border p-2 bg-light mb-3"
                             onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'">
                        <h5 class="fw-bold mb-1" style="color:#166534;">${eq.name}</h5>
                        <span class="badge bg-secondary">
                            <i class="bi bi-upc-scan me-1"></i>${eq.equipment_code}
                        </span>
                    </div>

                    <!-- Right: details table -->
                    <div class="col-md-8 p-3">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>

                                <!-- Basic info header -->
                                <tr>
                                    <td colspan="2" class="pb-1">
                                        <span class="fw-semibold small text-uppercase text-secondary">
                                            <i class="bi bi-info-circle me-1"></i>Basic Information
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="text-muted fw-normal" style="width:150px">Quantity</th>
                                    <td>${eq.qty ?? '—'}</td>
                                </tr>

                                ${eq.lab_location ? `
                                <tr>
                                    <th class="text-muted fw-normal">Location</th>
                                    <td>
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        ${eq.lab_location}
                                    </td>
                                </tr>` : ''}

                                <tr>
                                    <th class="text-muted fw-normal">Date Added</th>
                                    <td>${eq.added_datetime || '—'}</td>
                                </tr>

                                ${eq.description ? `
                                <tr>
                                    <th class="text-muted fw-normal">Description</th>
                                    <td><small>${eq.description}</small></td>
                                </tr>` : ''}

                                ${grantHTML}
                                ${aiHTML}

                            </tbody>
                        </table>
                    </div>
                </div>
            `;
                    })
                    .catch(err => {
                        document.getElementById('equipmentDetailsContent').innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Fetch error: ${err.message}
                </div>`;
                    });
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



            // Add this function to show events for selected date
            function showEventsForDate(date) {
                const eventsList = document.getElementById('eventsList');
                const dateStr = date.toISOString().split('T')[0]; // Format: YYYY-MM-DD

                // Sample events data - in real app, this would come from database
                const events = {
                    '2026-02-15': [{
                            title: 'Microscope Reservation - Lab 01',
                            time: '10:00 AM - 12:00 PM',
                            type: 'reservation'
                        },
                        {
                            title: 'Centrifuge Maintenance',
                            time: '02:00 PM - 03:00 PM',
                            type: 'maintenance'
                        }
                    ],
                    '2026-02-16': [{
                            title: 'Incubator Calibration',
                            time: '09:00 AM - 10:30 AM',
                            type: 'maintenance'
                        },
                        {
                            title: 'Student Practical - Lab 02',
                            time: '11:00 AM - 01:00 PM',
                            type: 'practical'
                        },
                        {
                            title: 'Autoclave Sterilization',
                            time: '02:00 PM - 04:00 PM',
                            type: 'maintenance'
                        }
                    ],
                    '2026-02-17': [{
                            title: 'Research Project - DNA Extraction',
                            time: '09:30 AM - 12:30 PM',
                            type: 'research'
                        },
                        {
                            title: 'pH Meter Calibration',
                            time: '01:30 PM - 02:30 PM',
                            type: 'maintenance'
                        }
                    ]
                };

                // Get events for selected date or show default message
                const dayEvents = events[dateStr] || [];

                if (dayEvents.length === 0) {
                    eventsList.innerHTML = '<div class="no-event">No events scheduled for this date</div>';
                } else {
                    eventsList.innerHTML = '';
                    dayEvents.forEach(event => {
                        const eventItem = document.createElement('div');
                        eventItem.className = 'event-item';

                        // Determine icon color based on event type
                        let iconColor = '#ffd700'; // default gold
                        if (event.type === 'maintenance') iconColor = '#ef4444'; // red
                        if (event.type === 'practical') iconColor = '#22c55e'; // green
                        if (event.type === 'research') iconColor = '#3b82f6'; // blue

                        eventItem.innerHTML = `
                <div class="title">
                    <i class="fas fa-circle" style="color: ${iconColor};"></i>
                    <div class="event-title">${event.title}</div>
                </div>
                <div class="event-time">${event.time}</div>
            `;

                        eventsList.appendChild(eventItem);
                    });
                }
            }


































            // ========== RESERVATION FUNCTIONS ==========
            // ========== RESERVATION FUNCTIONS ==========

            function searchReservations() {
                const searchTerm = document.getElementById('reservationSearch').value.toLowerCase().trim();
                const statusFilter = document.getElementById('statusFilter').value;

                const rows = document.querySelectorAll('#reservationTableBody tr[data-id]');
                let visible = 0;

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const rowStatus = row.getAttribute('data-status');

                    const matchSearch = searchTerm === '' || text.includes(searchTerm);
                    const matchStatus = statusFilter === 'all' || rowStatus === statusFilter.toLowerCase();

                    if (matchSearch && matchStatus) {
                        row.style.display = '';
                        visible++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                document.getElementById('reservationCount').textContent = '(' + visible + '/' + rows.length + ')';
            }

            function loadReservations() {
                const tableBody = document.getElementById('reservationTableBody');
                tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-success me-2"></div>
                Loading reservations...
            </td>
        </tr>`;

                fetch('../controllers/get_reservations.php')
                    .then(r => r.json())
                    .then(res => {
                        tableBody.innerHTML = '';

                        if (!res.success || res.data.length === 0) {
                            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No reservations found</td></tr>';
                            document.getElementById('reservationCount').textContent = '(0)';
                            return;
                        }

                        res.data.forEach(item => {
                            let badgeClass, statusLower;
                            if (item.status === 'Ready') {
                                badgeClass = 'bg-success';
                                statusLower = 'ready';
                            } else if (item.status === 'Rejected') {
                                badgeClass = 'bg-danger';
                                statusLower = 'rejected';
                            } else {
                                badgeClass = 'bg-warning';
                                statusLower = 'pending';
                            }

                            const row = document.createElement('tr');
                            row.setAttribute('data-id', item.id);
                            row.setAttribute('data-status', statusLower);
                            row.innerHTML = `
                  <td>${item.display_id}</td>
                    <td>${item.lab_location}</td>
                    <td>${item.student_id}</td>
                    <td><span class="badge ${badgeClass}">${item.status}</span></td>
                    <td>${item.date}</td>
                    <td>
                       <button class="btn-view" onclick="viewReservation(${item.id})">
                            <i class="bi bi-eye"></i> View
                        </button>
                    </td>
                `;
                            tableBody.appendChild(row);
                        });

                        document.getElementById('reservationCount').textContent = '(' + res.data.length + ')';
                    })
                    .catch(err => {
                        tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Error loading data</td></tr>`;
                        console.error(err);
                    });
            }

            function viewReservation(id) {
                // Show loading state
                document.getElementById('resModalContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-success me-2" role="status"></div>
            Loading reservation details...
        </div>`;

                // Show modal
                document.getElementById('resModal').style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Prevent background scrolling

                console.log('Fetching reservation ID:', id);

                fetch(`../controllers/get_reservation_details.php?id=${encodeURIComponent(id)}`)
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            document.getElementById('resModalContent').innerHTML =
                                `<div class="alert alert-danger m-3">Error: ${res.message}</div>`;
                            return;
                        }

                        // Create status badge
                        let statusBadge = '';
                        if (res.status === 'Ready') {
                            statusBadge = '<span style="background:#22c55e;color:white;padding:4px 12px;border-radius:20px;font-weight:600;font-size:0.85rem;">Ready</span>';
                        } else if (res.status === 'Rejected') {
                            statusBadge = '<span style="background:#ef4444;color:white;padding:4px 12px;border-radius:20px;font-weight:600;font-size:0.85rem;">Rejected</span>';
                        } else {
                            statusBadge = '<span style="background:#f59e0b;color:white;padding:4px 12px;border-radius:20px;font-weight:600;font-size:0.85rem;">Pending</span>';
                        }

                        // Build rejection reason if exists
                        let rejectionHtml = '';
                        if (res.is_rejected && res.rejected_reason) {
                            rejectionHtml = `
                    <tr>
                        <td style="padding:10px 0;color:#166534;font-weight:600;width:170px;border-top:1px solid #f5f5f5;">Rejection Reason:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">
                            <div style="background:#fff0f0;color:#dc2626;padding:10px;border-radius:8px;border-left:3px solid #ef4444;">
                                ${res.rejected_reason}
                            </div>
                        </td>
                    </tr>
                `;
                        }

                        // Build comment sections if they exist
                        let commentHtml = res.comment ?
                            `<tr><td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Student Comment:</td>
                 <td style="padding:10px 0;border-top:1px solid #f5f5f5;"><em>"${res.comment}"</em></td></tr>` : '';

                        let anyCommentHtml = res.any_comment ?
                            `<tr><td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Additional Notes:</td>
                 <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${res.any_comment}</td></tr>` : '';

                        // Build the complete modal content
                        document.getElementById('resModalContent').innerHTML = `
                <table style="width:100%; border-collapse:collapse;">
                    <tr><td style="padding:10px 0;color:#166534;font-weight:600;width:170px;">Reservation ID:</td>
                        <td style="padding:10px 0;"><strong>${res.id}</strong></td></tr>
                    <tr><td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Lab Location:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${res.lab_location}</td></tr>
                    <tr><td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Student ID:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${res.student_id}</td></tr>
                    <tr><td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Supervisor ID:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${res.supervisor_id}</td></tr>
                    <tr><td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Status:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${statusBadge}</td></tr>
                    <tr><td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Date:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${res.date}</td></tr>
                    ${commentHtml}
                    ${anyCommentHtml}
                    ${rejectionHtml}
                </table>
            `;
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        document.getElementById('resModalContent').innerHTML =
                            `<div class="alert alert-danger m-3">Error loading reservation details</div>`;
                    });
            }

            function closeResModal() {
                document.getElementById('resModal').style.display = 'none';
                document.body.style.overflow = ''; // Restore scrolling
            }
            document.getElementById('resModal').addEventListener('click', function(e) {
                if (e.target === this) closeResModal();
            });

            function addReservation() {
                alert('Add Reservation modal would open here');
            }
















            // ========== ANALYTICS FUNCTIONS ==========
            let usageChart, monthlyChart, equipmentUsageChart;





            let equipmentUsageData = [{
                    name: 'Microscope',
                    usage: 80
                },
                {
                    name: 'Centrifuge',
                    usage: 65
                },
                {
                    name: 'Incubator',
                    usage: 45
                },
                {
                    name: 'Autoclave',
                    usage: 70
                },
                {
                    name: 'pH Meter',
                    usage: 35
                },
                {
                    name: 'Water Bath',
                    usage: 20
                },
                {
                    name: 'Shaker',
                    usage: 55
                },
                {
                    name: 'Hot Plate',
                    usage: 30
                },
                {
                    name: 'Balance',
                    usage: 25
                }
            ];

            function initCharts() {
                const usageCtx = document.getElementById('usageChart')?.getContext('2d');
                if (usageCtx) {
                    if (usageChart) usageChart.destroy();
                    usageChart = new Chart(usageCtx, {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [{
                                    // Bars — green
                                    label: 'Completed',
                                    data: [],
                                    backgroundColor: 'rgba(34, 197, 94, 0.85)',
                                    borderRadius: 8,
                                    yAxisID: 'y'
                                },
                                {
                                    // Average line — blue dashed
                                    label: 'Average',
                                    data: [],
                                    type: 'line',
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'transparent',
                                    borderDash: [6, 4],
                                    tension: 0,
                                    fill: false,
                                    pointRadius: 0,
                                    pointHoverRadius: 0,
                                    yAxisID: 'y'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 10,
                                        color: '#555'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (item) => {
                                            if (item.dataset.label === 'Average')
                                                return ` Avg: ${item.raw}`;
                                            return ` ${item.raw} completed`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 0
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    },
                                    title: {
                                        display: true,
                                        text: 'Reservations'
                                    }
                                }
                            }
                        }
                    });

                    // Load real data immediately + refresh every hour
                    loadUsageChartData();
                    setInterval(loadUsageChartData, 60 * 60 * 1000);
                }


                // ── 2. ADD this as a standalone function (outside initCharts) ─────────────────

                function loadUsageChartData() {
                    fetch('../controllers/get_completed_reservations.php')
                        .then(r => r.json())
                        .then(res => {
                            if (!res.success || !usageChart) return;

                            const labels = res.labels;
                            const data = res.data;
                            const avg = res.average;
                            const avgLine = labels.map(() => avg);

                            usageChart.data.labels = labels;
                            usageChart.data.datasets[0].data = data;
                            usageChart.data.datasets[1].data = avgLine;
                            usageChart.update('active');

                            // Expand wrapper width for horizontal scroll
                            const wrapper = document.getElementById('usageChartWrapper');
                            if (wrapper) {
                                const minWidth = Math.max(
                                    labels.length * 80,
                                    wrapper.parentElement.offsetWidth || 400
                                );
                                wrapper.style.width = minWidth + 'px';
                            }

                            // Scroll to current month (latest = rightmost)
                            const scrollEl = document.getElementById('usageChartScroll');
                            if (scrollEl) {
                                scrollEl.scrollLeft = scrollEl.scrollWidth;
                            }

                            // Update timestamp
                            const ts = document.getElementById('usageChartUpdated');
                            if (ts) ts.textContent = 'Updated: ' + res.updated;
                        })
                        .catch(err => console.warn('Usage chart error:', err));
                }





                const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
                if (monthlyCtx) {
                    if (monthlyChart) monthlyChart.destroy();

                    monthlyChart = new Chart(monthlyCtx, {
                        type: 'line',
                        data: {
                            labels: [],
                            datasets: [{
                                    // Sessions line — green
                                    label: 'Sessions',
                                    data: [],
                                    borderColor: '#22c55e',
                                    backgroundColor: 'rgba(34, 197, 94, 0.08)',
                                    tension: 0.4,
                                    fill: true,
                                    pointBackgroundColor: '#22c55e',
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    yAxisID: 'y'
                                },
                                {
                                    // Average line — blue, dashed
                                    label: 'Average',
                                    data: [],
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'transparent',
                                    borderDash: [6, 4],
                                    tension: 0,
                                    fill: false,
                                    pointRadius: 0,
                                    pointHoverRadius: 0,
                                    yAxisID: 'y'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 10,
                                        color: '#555'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (item) => {
                                            if (item.dataset.label === 'Average')
                                                return ` Avg: ${item.raw} sessions`;
                                            return ` ${item.raw} sessions`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 0
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    },
                                    title: {
                                        display: true,
                                        text: 'Sessions'
                                    }
                                }
                            }
                        }
                    });

                    // Load real data immediately + auto-refresh every hour
                    loadMonthlyChartData();
                    setInterval(loadMonthlyChartData, 60 * 60 * 1000);
                }


                // ── 2. ADD this as a standalone function (outside initCharts) ─────────────────

                function loadMonthlyChartData() {
                    fetch('../controllers/get_session_stats.php')
                        .then(r => r.json())
                        .then(res => {
                            if (!res.success || !monthlyChart) return;

                            const labels = res.labels;
                            const data = res.data;
                            const avg = res.average;

                            // Build flat average line across all months
                            const avgLine = labels.map(() => avg);

                            // Update chart datasets
                            monthlyChart.data.labels = labels;
                            monthlyChart.data.datasets[0].data = data;
                            monthlyChart.data.datasets[1].data = avgLine;
                            monthlyChart.update('active');

                            // Scroll wrapper width — 80px per month, min fills container
                            const wrapper = document.getElementById('monthlyChartWrapper');
                            if (wrapper) {
                                const minWidth = Math.max(
                                    labels.length * 80,
                                    wrapper.parentElement.offsetWidth || 400
                                );
                                wrapper.style.width = minWidth + 'px';
                            }

                            // Scroll to current month (far right)
                            const scrollEl = document.getElementById('monthlyChartScroll');
                            if (scrollEl) {
                                scrollEl.scrollLeft = scrollEl.scrollWidth;
                            }

                            // Update timestamp
                            const ts = document.getElementById('monthlyChartUpdated');
                            if (ts) ts.textContent = 'Updated: ' + res.updated;
                        })
                        .catch(err => console.warn('Session chart error:', err));

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
    <td>${item.display_id}</td>
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
                const blob = new Blob([csv], {
                    type: 'text/csv'
                });
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
                'REQ003': {
                    studentName: 'Mike Johnson',
                    studentId: 'SCI003',
                    reason: 'Equipment under maintenance - Scheduled for repair on 2026-02-25',
                    rejectedBy: 'Mr. Prasanna Kumara',
                    dateTime: '2026-02-19 09:15 AM'
                },
                'REQ007': {
                    studentName: 'Alice Brown',
                    studentId: 'SCI007',
                    reason: 'Technical issue reported - Motor malfunction, awaiting spare parts',
                    rejectedBy: 'Mrs. Chamari Weerasinghe',
                    dateTime: '2026-02-17 02:30 PM'
                },
                'REQ012': {
                    studentName: 'Tharindu Silva',
                    studentId: 'SCI012',
                    reason: 'Calibration required - Device giving inaccurate readings',
                    rejectedBy: 'Mr. Sunil Rathnayake',
                    dateTime: '2026-02-15 11:45 AM'
                }
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










            // ═══════════════════════════════════════════════════════════════════
            // INSTRUCTIONS:
            // 1. Find this comment in hod.php:   // ========== CALENDAR FUNCTIONS ==========
            // 2. Delete EVERYTHING from that comment all the way down to
            //    the closing } of the SECOND initCalendarListeners() function
            //    (the one that says "Initialize calendar when dashboard is shown")
            // 3. Paste this entire block in its place
            // ═══════════════════════════════════════════════════════════════════

            // ========== CALENDAR FUNCTIONS ==========
            let calMonth = new Date().getMonth();
            let calYear = new Date().getFullYear();
            let calEvents = {};

            const calMonthNames = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            function loadCalendarEvents(m, y, callback) {
                fetch(`../controllers/get_calendar_events.php?month=${m + 1}&year=${y}`)
                    .then(r => r.json())
                    .then(res => {
                        calEvents = res.success ? res.events : {};
                        if (callback) callback();
                    })
                    .catch(() => {
                        calEvents = {};
                        if (callback) callback();
                    });
            }

            function initCalendar() {
                const daysGrid = document.getElementById('daysGrid');
                const displayMonth = document.getElementById('displayMonth');
                const eventDay = document.getElementById('eventDay');
                const eventDate = document.getElementById('eventDate');
                const eventsList = document.getElementById('eventsList');
                if (!daysGrid || !displayMonth) return;

                displayMonth.innerHTML = calMonthNames[calMonth] + ' ' + calYear;

                if (eventsList) {
                    eventsList.innerHTML = `
                        <div class="text-center py-4" style="color:rgba(255,255,255,0.5);">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            Loading...
                        </div>`;
                }

                loadCalendarEvents(calMonth, calYear, () => {
                    renderCalendarGrid(daysGrid);

                    const today = new Date();
                    const isCurrentMonth = (today.getMonth() === calMonth && today.getFullYear() === calYear);
                    const displayDate = isCurrentMonth ? today : new Date(calYear, calMonth, 1);

                    if (eventDay) {
                        eventDay.innerHTML = displayDate.toLocaleDateString('en-US', {
                            weekday: 'long'
                        });
                    }
                    if (eventDate) {
                        eventDate.innerHTML = displayDate.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    }

                    showEventsForDate(displayDate);
                    addDayCellClickHandlers();
                });
            }

            function renderCalendarGrid(daysGrid) {
                const firstDay = new Date(calYear, calMonth, 1).getDay();
                const lastDate = new Date(calYear, calMonth + 1, 0).getDate();
                const todayD = new Date();

                let html = '';
                for (let i = 0; i < firstDay; i++) {
                    html += `<div class="day-cell prev-date"></div>`;
                }

                for (let d = 1; d <= lastDate; d++) {
                    const mm = String(calMonth + 1).padStart(2, '0');
                    const dd = String(d).padStart(2, '0');
                    const dateKey = `${calYear}-${mm}-${dd}`;
                    const hasEvent = !!calEvents[dateKey];

                    let classes = 'day-cell';
                    if (d === todayD.getDate() &&
                        calYear === todayD.getFullYear() &&
                        calMonth === todayD.getMonth()) {
                        classes += ' today';
                    }

                    const dot = hasEvent ?
                        `<span style="position:absolute;bottom:3px;left:50%;
                                transform:translateX(-50%);width:6px;height:6px;
                                border-radius:50%;background:#ffd700;display:block;"></span>` :
                        '';

                    const bookedStyle = hasEvent ?
                        `style="background:rgba(255,215,0,0.18);
                                  border:1px solid rgba(255,215,0,0.5);
                                  font-weight:600;"` :
                        '';

                    html += `<div class="${classes}" data-date="${dateKey}" ${bookedStyle}>${d}${dot}</div>`;
                }

                daysGrid.innerHTML = html;
            }

            function showEventsForDate(date) {
                const eventsList = document.getElementById('eventsList');
                if (!eventsList) return;

                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                const dateKey = `${date.getFullYear()}-${mm}-${dd}`;
                const dayEvents = calEvents[dateKey] || [];

                if (dayEvents.length === 0) {
                    eventsList.innerHTML = `
                        <div class="no-event">
                            <i class="bi bi-calendar-x" style="font-size:2rem;opacity:0.4;"></i>
                            <p class="mt-2 mb-0" style="font-size:0.9rem;">No reservations on this date</p>
                        </div>`;
                    return;
                }

                eventsList.innerHTML = '';
                dayEvents.forEach(ev => {
                    let badgeColor = '#22c55e';
                    if (ev.status === 'Pending') badgeColor = '#f59e0b';
                    if (ev.status === 'Rejected') badgeColor = '#ef4444';

                    const item = document.createElement('div');
                    item.className = 'event-item';
                    item.innerHTML = `
                        <div class="title">
                            <i class="fas fa-circle" style="color:#ffd700;font-size:0.6rem;"></i>
                            <div class="event-title">
                                <span class="badge"
                                      style="background:rgba(255,255,255,0.2);font-size:0.8rem;
                                             margin-bottom:4px;display:inline-block;">
                                    ${ev.university_id}
                                </span>
                                &nbsp;${ev.student_name}
                            </div>
                        </div>
                        <div class="event-time" style="margin-left:28px;">
                            <i class="bi bi-geo-alt-fill me-1" style="color:#ffd700;"></i>
                            ${ev.lab_location}
                        </div>
                        <div style="margin-left:28px;margin-top:4px;">
                            <span style="background:${badgeColor};color:white;padding:2px 8px;
                                         border-radius:12px;font-size:0.75rem;font-weight:600;">
                                ${ev.status}
                            </span>
                            <small style="color:rgba(255,255,255,0.6);margin-left:6px;">#${ev.id}</small>
                        </div>
                      
                    `;
                    eventsList.appendChild(item);
                });
            }

            function addDayCellClickHandlers() {
                const cells = document.querySelectorAll('.day-cell:not(.prev-date):not(.next-date)');
                cells.forEach(cell => {
                    cell.addEventListener('click', function() {
                        cells.forEach(c => c.classList.remove('active'));
                        this.classList.add('active');

                        const dateKey = this.getAttribute('data-date');
                        if (!dateKey) return;

                        const parts = dateKey.split('-');
                        const selectedDate = new Date(+parts[0], +parts[1] - 1, +parts[2]);

                        document.getElementById('eventDay').innerHTML =
                            selectedDate.toLocaleDateString('en-US', {
                                weekday: 'long'
                            });
                        document.getElementById('eventDate').innerHTML =
                            selectedDate.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });

                        showEventsForDate(selectedDate);
                    });
                });
            }

            function initCalendarListeners() {
                const prevBtn = document.querySelector('.prev');
                const nextBtn = document.querySelector('.next');
                const todayBtn = document.getElementById('todayBtn');
                const gotoBtn = document.getElementById('gotoBtn');

                if (prevBtn) {
                    prevBtn.addEventListener('click', () => {
                        calMonth--;
                        if (calMonth < 0) {
                            calMonth = 11;
                            calYear--;
                        }
                        initCalendar();
                    });
                }
                if (nextBtn) {
                    nextBtn.addEventListener('click', () => {
                        calMonth++;
                        if (calMonth > 11) {
                            calMonth = 0;
                            calYear++;
                        }
                        initCalendar();
                    });
                }
                if (todayBtn) {
                    todayBtn.addEventListener('click', () => {
                        const d = new Date();
                        calMonth = d.getMonth();
                        calYear = d.getFullYear();
                        initCalendar();
                    });
                }
                if (gotoBtn) {
                    gotoBtn.addEventListener('click', () => {
                        const val = document.getElementById('gotoInput')?.value;
                        const parts = (val || '').split('/');
                        if (parts.length === 2) {
                            const m = parseInt(parts[0]) - 1;
                            const y = parseInt(parts[1]);
                            if (m >= 0 && m < 12 && y > 0) {
                                calMonth = m;
                                calYear = y;
                                initCalendar();
                            } else alert('Invalid date. Use MM/YYYY');
                        } else alert('Invalid format. Use MM/YYYY');
                    });
                }
            }
            // ========== END CALENDAR FUNCTIONS ==========













            // ========== MAINTENANCE MODAL FUNCTIONS ==========
            let selectedEquipment = [];
            let companies = ['TechFix Solutions', 'LabCare Services', 'MedEquip Maintenance'];

            function sendToMaintenance() {
                openMaintenanceModal();
            }

            function openMaintenanceModal() {
                document.getElementById('maintenanceModal').classList.add('active');
                loadCompanies();
                updateSelectedEquipmentList();
            }

            function closeMaintenanceModal() {
                document.getElementById('maintenanceModal').classList.remove('active');
                document.getElementById('emailFormatEdit').classList.remove('active');
            }

            function toggleEmailFormatEdit() {
                const editSection = document.getElementById('emailFormatEdit');
                const previewSection = document.getElementById('emailFormatPreview');
                editSection.classList.toggle('active');
                if (editSection.classList.contains('active')) {
                    previewSection.style.display = 'none';
                } else {
                    previewSection.style.display = 'block';
                }
            }

            function openEquipmentSelectModal() {
                const modal = document.getElementById('equipmentSelectModal');
                const list = document.getElementById('equipmentSelectList');

                // Populate equipment list from your equipment data
                list.innerHTML = '';
                equipmentDataTable.forEach(item => {
                    if (item.maintenance > 0) { // Only show equipment with maintenance pending
                        const div = document.createElement('div');
                        div.className = 'equipment-select-item';
                        div.onclick = () => toggleEquipmentSelection(item.code);

                        // Create company dropdown
                        let companyOptions = '<option value="">Select Company</option>';
                        companies.forEach(c => {
                            companyOptions += `<option value="${c}">${c}</option>`;
                        });

                        div.innerHTML = `
                        <div class="equipment-select-info">
                            <h6>${item.name} (${item.code})</h6>
                            <p>Maintenance Pending: ${item.maintenance} units</p>
                            <select class="company-select" onchange="updateEquipmentCompany('${item.code}', this.value)" onclick="event.stopPropagation()">
                                ${companyOptions}
                            </select>
                        </div>
                        <input type="number" class="equipment-select-qty" value="1" min="1" max="${item.maintenance}" onclick="event.stopPropagation()" onchange="updateEquipmentQty('${item.code}', this.value)">
                    `;
                        list.appendChild(div);
                    }
                });

                modal.classList.add('active');
            }

            function closeEquipmentSelectModal() {
                document.getElementById('equipmentSelectModal').classList.remove('active');
            }

            function toggleEquipmentSelection(code) {
                const item = equipmentDataTable.find(e => e.code === code);
                if (!item) return;

                const existing = selectedEquipment.find(e => e.code === code);
                if (existing) {
                    selectedEquipment = selectedEquipment.filter(e => e.code !== code);
                } else {
                    selectedEquipment.push({
                        code: item.code,
                        name: item.name,
                        qty: 1,
                        maxQty: item.maintenance,
                        company: ''
                    });
                }
                updateSelectedEquipmentList();
            }

            function updateEquipmentQty(code, qty) {
                const item = selectedEquipment.find(e => e.code === code);
                if (item) {
                    item.qty = parseInt(qty) || 1;
                }
                updateSelectedEquipmentList();
            }

            function updateEquipmentCompany(code, company) {
                const item = selectedEquipment.find(e => e.code === code);
                if (item) {
                    item.company = company;
                }
                updateSelectedEquipmentList();
            }

            function addSelectedEquipment() {
                closeEquipmentSelectModal();
                updateSelectedEquipmentList();
            }

            function removeSelectedEquipment(code) {
                selectedEquipment = selectedEquipment.filter(e => e.code !== code);
                updateSelectedEquipmentList();
            }

            function updateSelectedEquipmentList() {
                const list = document.getElementById('selectedEquipmentList');

                if (selectedEquipment.length === 0) {
                    list.innerHTML = '<p class="text-muted text-center py-3">No equipment selected</p>';
                    return;
                }

                list.innerHTML = '';
                selectedEquipment.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'equipment-item';
                    div.innerHTML = `
                    <div class="equipment-item-info">
                        <span class="equipment-item-name">${item.name} (${item.code})</span>
                        <div class="equipment-item-details">
                            <span>Available: ${item.maxQty} units</span>
                            ${item.company ? `<span class="company-badge">Company: ${item.company}</span>` : ''}
                        </div>
                    </div>
                    <div class="equipment-item-qty">
                        <input type="number" class="qty-input" value="${item.qty}" min="1" max="${item.maxQty}" onchange="updateEquipmentQty('${item.code}', this.value)">
                        <button class="remove-equipment-btn" onclick="removeSelectedEquipment('${item.code}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
                    list.appendChild(div);
                });
            }

            function loadCompanies() {
                const list = document.getElementById('companyList');
                list.innerHTML = '';
                companies.forEach(company => {
                    const tag = document.createElement('span');
                    tag.className = 'company-tag';
                    tag.innerHTML = `
                    ${company}
                    <i class="bi bi-x" onclick="removeCompany('${company}')"></i>
                `;
                    list.appendChild(tag);
                });
            }

            function addCompany() {
                const input = document.getElementById('newCompany');
                const company = input.value.trim();
                if (company && !companies.includes(company)) {
                    companies.push(company);
                    loadCompanies();
                    input.value = '';
                }
            }

            function removeCompany(company) {
                companies = companies.filter(c => c !== company);
                loadCompanies();
            }

            function sendMaintenanceRequest() {
                const fromEmail = document.getElementById('fromEmail').value;
                const toEmail = document.getElementById('toEmail').value;
                const ccEmail = document.getElementById('ccEmail').value;

                if (!toEmail) {
                    alert('Please enter recipient email address');
                    return;
                }

                if (selectedEquipment.length === 0) {
                    alert('Please select at least one equipment for maintenance');
                    return;
                }

                // Generate equipment list text with company information
                let equipmentList = '';
                selectedEquipment.forEach(item => {
                    equipmentList += `- ${item.name} (${item.code}): ${item.qty} unit(s)`;
                    if (item.company) {
                        equipmentList += ` [Company: ${item.company}]`;
                    }
                    equipmentList += '\n';
                });

                // Get email format
                let emailContent;
                if (document.getElementById('emailFormatEdit').classList.contains('active')) {
                    emailContent = document.getElementById('emailFormatTextarea').value;
                } else {
                    emailContent = document.getElementById('emailFormatPreview').innerText;
                }

                // Replace placeholders
                emailContent = emailContent
                    .replace('{{equipment_list}}', equipmentList)
                    .replace('{{from_email}}', fromEmail);

                // Here you would send the email via AJAX
                console.log('Sending email to:', toEmail);
                console.log('CC:', ccEmail);
                console.log('Email content:', emailContent);

                // Show success message with email details
                alert(`Maintenance request sent successfully!\n\nTo: ${toEmail}\nCC: ${ccEmail}\n\nEquipment:\n${equipmentList}`);

                closeMaintenanceModal();

                // Reset form
                selectedEquipment = [];
                updateSelectedEquipmentList();
            }

            // Initialize calendar when dashboard is shown
            // function initCalendarListeners() {
            //     const prevBtn = document.querySelector('.prev');
            //     const nextBtn = document.querySelector('.next');
            //     const todayBtn = document.getElementById('todayBtn');
            //     const gotoBtn = document.getElementById('gotoBtn');

            //     if (prevBtn) {
            //         prevBtn.addEventListener('click', () => {
            //             month--;
            //             if (month < 0) {
            //                 month = 11;
            //                 year--;
            //             }
            //             initCalendar();
            //             addDayCellClickHandlers();
            //         });
            //     }

            //     if (nextBtn) {
            //         nextBtn.addEventListener('click', () => {
            //             month++;
            //             if (month > 11) {
            //                 month = 0;
            //                 year++;
            //             }
            //             initCalendar();
            //             addDayCellClickHandlers(); 
            //         });
            //     }

            //     if (todayBtn) {
            //         todayBtn.addEventListener('click', () => {
            //             const d = new Date();
            //             month = d.getMonth();
            //             year = d.getFullYear();
            //             initCalendar();
            //             addDayCellClickHandlers(); /


            //             setTimeout(() => {
            //                 const todayCells = document.querySelectorAll('.day-cell.today');
            //                 todayCells.forEach(cell => cell.classList.add('active'));
            //             }, 50);
            //         });
            //     }

            //     if (gotoBtn) {
            //         gotoBtn.addEventListener('click', () => {
            //             const gotoInput = document.getElementById('gotoInput');
            //             if (!gotoInput) return;

            //             const parts = gotoInput.value.split('/');
            //             if (parts.length === 2) {
            //                 const m = parseInt(parts[0]) - 1,
            //                     y = parseInt(parts[1]);
            //                 if (m >= 0 && m < 12 && y > 0) {
            //                     month = m;
            //                     year = y;
            //                     initCalendar();
            //                     addDayCellClickHandlers(); 
            //                 } else alert('Invalid date. Use MM/YYYY');
            //             } else alert('Invalid format. Use MM/YYYY');
            //         });
            //     }
            // }

            // ========== INITIALIZATION ==========
            document.addEventListener('DOMContentLoaded', function() {
                loadCompanies();
                updateRequestCounts();
                updateVisibleCounts('');
                initCharts();
                showSection('dashboard');
                initCalendar();
                initCalendarListeners(); // Add this line
                loadUserCounts();
                setTimeout(() => addDayCellClickHandlers(), 100);



                loadEquipmentWithUsage();
                if (document.getElementById('analyticsSection')) setTimeout(initAnalyticsCharts, 500);
            });
        </script>

        <!-- Add this before the closing </body> tag -->
        <!-- Notification Dropdown -->
        <div class="notification-dropdown" id="notificationDropdown">
            <div class="notification-header">
                <h6>Notifications</h6>
                <span>3 new</span>
            </div>
            <div class="notification-list">
                <div class="notification-item unread">
                    <div class="fw-bold">New Equipment Request</div>
                    <div>John Doe requested Microscope</div>
                    <div class="time">5 minutes ago</div>
                </div>
                <div class="notification-item unread">
                    <div class="fw-bold">Maintenance Alert</div>
                    <div>Centrifuge maintenance due</div>
                    <div class="time">1 hour ago</div>
                </div>
                <div class="notification-item">
                    <div class="fw-bold">Reservation Approved</div>
                    <div>Lab 01 reservation confirmed</div>
                    <div class="time">2 hours ago</div>
                </div>
            </div>
        </div>




        <!-- Maintenance Request Modal -->
        <div class="maintenance-modal" id="maintenanceModal">
            <div class="maintenance-modal-content">
                <div class="maintenance-modal-header">
                    <h3><i class="bi bi-tools"></i> Send to Maintenance</h3>
                    <button class="close-btn" onclick="closeMaintenanceModal()"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="maintenance-modal-body">
                    <!-- Email Section -->
                    <div class="email-section">
                        <div class="email-field">
                            <label><i class="bi bi-envelope-fill me-2"></i>From Email</label>
                            <input type="email" id="fromEmail" placeholder="your.email@lab.com" value="admin@microbiolab.lk">
                        </div>
                        <div class="email-field">
                            <label><i class="bi bi-envelope-fill me-2"></i>To Email (Dean's Office)</label>
                            <input type="email" id="toEmail" placeholder="dean@science.faculty.lk" value="dean@science.faculty.lk">
                        </div>
                        <div class="email-field">
                            <label><i class="bi bi-envelope-fill me-2"></i>CC (HOD Email)</label>
                            <input type="email" id="ccEmail" placeholder="hod.microbiology@lab.lk" value="hod.microbiology@lab.lk">
                        </div>
                    </div>

                    <!-- Email Format Section -->
                    <div class="email-format-section">
                        <div class="email-format-header">
                            <h6><i class="bi bi-envelope-paper-fill me-2"></i>Email Format</h6>
                            <button class="edit-format-btn" onclick="toggleEmailFormatEdit()">
                                <i class="bi bi-pencil-square"></i> Edit Format
                            </button>
                        </div>
                        <div class="email-format-preview" id="emailFormatPreview">
                            Dear Dean, Faculty of Science,

                            I would like to kindly request the Dean of the Faculty of Science to arrange maintenance services for the following laboratory equipment used in the Microbiology Laboratory.

                            The equipment requires servicing by the respective authorized companies to ensure proper functioning. The details of the equipment and their respective companies are listed below:

                            {{equipment_list}}

                            I would greatly appreciate it if the Dean's Office could contact the relevant companies and arrange the necessary maintenance at your earliest convenience.

                            Equipment Location: Microbiology Laboratory
                            Requested By: Lab Administrator

                            Thank you for your support and assistance.

                            Yours sincerely,
                            Head of Department, Microbiology Department
                        </div>
                        <div class="email-format-edit" id="emailFormatEdit">
                            <textarea id="emailFormatTextarea">Dear Dean, Faculty of Science,

I would like to kindly request the Dean of the Faculty of Science to arrange maintenance services for the following laboratory equipment used in the Microbiology Laboratory.

The equipment requires servicing by the respective authorized companies to ensure proper functioning. The details of the equipment and their respective companies are listed below:

{{equipment_list}}

I would greatly appreciate it if the Dean's Office could contact the relevant companies and arrange the necessary maintenance at your earliest convenience.

Equipment Location: Microbiology Laboratory
Requested By: Lab Administrator

Thank you for your support and assistance.

Yours sincerely,
Head of Department, Microbiology Department</textarea>
                        </div>
                    </div>

                    <!-- Equipment Selection Section -->
                    <div class="equipment-selection-section">
                        <div class="section-header">
                            <h6><i class="bi bi-gear-fill me-2"></i>Equipment for Maintenance</h6>
                            <button class="add-equipment-btn" onclick="openEquipmentSelectModal()">
                                <i class="bi bi-plus-circle"></i> Add Equipment
                            </button>
                        </div>
                        <div class="selected-equipment-list" id="selectedEquipmentList">
                            <p class="text-muted text-center py-3">No equipment selected</p>
                        </div>
                    </div>

                    <!-- Company Section -->
                    <div class="company-section">
                        <h6><i class="bi bi-building me-2"></i>Maintenance Companies</h6>
                        <div class="company-list" id="companyList">
                            <!-- Company tags will appear here -->
                        </div>
                        <div class="add-company-input">
                            <input type="text" id="newCompany" placeholder="Enter maintenance company name">
                            <button class="add-company-btn" onclick="addCompany()">Add</button>
                        </div>
                    </div>
                </div>
                <div class="maintenance-modal-footer">
                    <button class="btn-cancel" onclick="closeMaintenanceModal()">Cancel</button>
                    <button class="btn-send" onclick="sendMaintenanceRequest()">
                        <i class="bi bi-send-fill"></i> Send Request
                    </button>
                </div>
            </div>
        </div>

        <!-- Equipment Selection Modal -->
        <div class="equipment-select-modal" id="equipmentSelectModal">
            <div class="equipment-select-content">
                <div class="equipment-select-header">
                    <h5><i class="bi bi-gear-fill me-2"></i>Select Equipment</h5>
                    <button class="close-btn" onclick="closeEquipmentSelectModal()"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="equipment-list" id="equipmentSelectList">
                    <!-- Equipment list will be populated here -->
                </div>
                <div class="equipment-select-footer">
                    <button class="btn-cancel" onclick="closeEquipmentSelectModal()">Cancel</button>
                    <button class="btn-send" onclick="addSelectedEquipment()">Add Selected</button>
                </div>
            </div>
        </div>

    </body>

    </html>


<?php
} else {
    // If not HOD, redirect to login
    header("Location: ../index.php");
    exit();
}
?>
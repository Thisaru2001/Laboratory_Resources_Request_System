<?php
session_start();
require_once '../config/database.php';
if (isset($_SESSION["user"]) && isset($_SESSION["user_role"]) && $_SESSION["user_role"] === 'Technical Officer') {

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
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
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
        <a onclick="showSection('activity')"><i class="bi bi-activity"></i> Requests</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>

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
                <span class="fw-semibold d-none d-sm-block" style="color: #166534;">Subodhi</span>
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
                        <i class="bi bi-flask"></i>
                        <h3>100</h3>
                        <p>Total Equipment</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-graph-up"></i>
                        <h3>85%</h3>
                        <p>Equipment Utilization Rate</p>
                    </div>
                    <div class="stat-card">
                        <i class="bi bi-tools"></i>
                        <h3>5</h3>
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
                            <h3 class="text-warning">8</h3>
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
                            <h3 class="text-info">15</h3>
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
                                <!-- Data will be populated by JavaScript -->
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
                            <button type="button" class="btn btn-danger" onclick="rejectRequestFromModal()" id="rejectModalBtn">
                                <i class="bi bi-x-circle me-2"></i>Reject
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- End content-area -->
    </div> <!-- End main-content -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
// ========== EQUIPMENT TABLE DATA ==========
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
}];

// ========== REQUESTS DATA ==========
const requests = [
    {
        id: 'REQ001',
        dateTime: '2026-02-20 10:30 AM',
        timestamp: new Date('2026-02-20T10:30:00'),
        studentName: 'John Doe',
        studentId: 'SCI001',
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
        lab: 'Research Lab',
        duration: '2 hours',
        purpose: 'Sample Preparation',
        status: 'approved',
        supervisor: 'Prof. Malini Silva',
        notes: 'Completed'
    }
];

// ========== CALENDAR VARIABLES ==========
let activeDay;
let month = new Date().getMonth();
let year = new Date().getFullYear();
const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];
let eventsArr = [];
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

function viewEquipment(code) {
    const equipment = equipmentDataTable.find(item => item.code === code);
    if (!equipment) return;
    
    const detailsContent = document.getElementById('equipmentDetailsContent');
    
    const purchaseDate = new Date(equipment.purchaseDate).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
    const lastMaintenance = new Date(equipment.lastMaintenance).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
    const nextMaintenance = new Date(equipment.nextMaintenance).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
    
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

function addEquipment() {
    alert('Add Equipment functionality would open a form modal');
}

function editEquipment(code) {
    alert('Edit equipment: ' + code);
}

function removeEquipment(code) {
    if (confirm(`Are you sure you want to remove equipment ${code}?`)) {
        const index = equipmentDataTable.findIndex(item => item.code === code);
        if (index !== -1) {
            equipmentDataTable.splice(index, 1);
        }
        displayEquipmentTable(equipmentDataTable);
        alert('Equipment removed successfully!');
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
function displayRequestTable(requestsList) {
    const tableBody = document.getElementById('requestListBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    requestsList.sort((a, b) => b.timestamp - a.timestamp);
    
    requestsList.forEach(item => {
        const row = document.createElement('tr');
        
        let statusClass = '';
        let statusText = '';
        
        switch(item.status) {
            case 'pending':
                statusClass = 'bg-warning';
                statusText = 'Pending';
                break;
            case 'approved':
                statusClass = 'bg-success';
                statusText = 'Checked';
                break;
            case 'rejected':
                statusClass = 'bg-danger';
                statusText = 'Rejected';
                break;
        }
        
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.dateTime}</td>
            <td>${item.studentId}</td>
            <td>${item.lab}</td>
            <td><span class="badge ${statusClass}">${statusText}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view" onclick="viewRequest('${item.id}')" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-remove" onclick="rejectRequest('${item.id}')" title="Reject">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
    
    if (requestsList.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="6" class="text-center">No requests found</td>`;
        tableBody.appendChild(row);
    }
}

// View request details
function viewRequest(id) {
    const request = requests.find(item => item.id === id);
    if (!request) return;
    
    currentRequestId = id;
    const detailsContent = document.getElementById('requestDetailsContent');
    
    let statusBadge = '';
    switch (request.status) {
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
                    <tr><th style="width: 200px;">Date & Time:</th><td>${request.dateTime}</td></tr>
                    <tr><th>Student:</th><td>${request.studentName} (${request.studentId})</td></tr>
                    <tr><th>Supervisor:</th><td>${request.supervisor}</td></tr>
                    <tr><th>Lab Location:</th><td>${request.lab}</td></tr>
                    <tr><th>Duration:</th><td>${request.duration}</td></tr>
                    <tr><th>Purpose:</th><td>${request.purpose}</td></tr>
                    <tr><th>Notes:</th><td>${request.notes}</td></tr>
                </table>
            </div>
        </div>
    `;
    
    // Show/hide reject button based on status
    const rejectBtn = document.getElementById('rejectModalBtn');
    if (rejectBtn) {
        if (request.status === 'pending') {
            rejectBtn.style.display = 'inline-block';
        } else {
            rejectBtn.style.display = 'none';
        }
    }
    
    new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
}

// Reject request from table
function rejectRequest(id) {
    if (confirm(`Are you sure you want to reject request ${id}?`)) {
        const index = requests.findIndex(item => item.id === id);
        if (index !== -1) {
            requests[index].status = 'rejected';
            filterRequestsByTime();
            alert(`Request ${id} has been rejected!`);
        }
    }
}

// Reject request from modal
function rejectRequestFromModal() {
    if (currentRequestId) {
        rejectRequest(currentRequestId);
        bootstrap.Modal.getInstance(document.getElementById('requestDetailsModal')).hide();
    }
}

// ========== CALENDAR FUNCTIONS ==========
function loadEvents() {
    const saved = localStorage.getItem("calendarEvents");
    if (saved) {
        eventsArr = JSON.parse(saved);
    }
}

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
    loadEvents();
    showSection('dashboard');
    initCalendar();
    
    if (document.getElementById('equipmentTableBody')) {
        displayEquipmentTable(equipmentDataTable);
    }
    
    if (document.getElementById('requestListBody')) {
        filterRequestsByStatus();
    }
});
</script>
</body>

</html>

<?php
} else {
    // If not HOD, redirect to login
    header("Location: ../index.php");
    exit();
}
?>
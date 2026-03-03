<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Microbiology Lab System - Supervisor Dashboard</title>
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

        .nav-tabs .nav-link {
            position: relative;
            padding-right: 25px !important;
        }

        .nav-tabs .nav-link.active .notification-badge {
            background-color: #ffc107;
            color: #000;
        }

/* Additional styles for the request sections */
.nav-tabs .nav-link {
    color: #495057;
    font-weight: 500;
    border: none;
    padding: 10px 20px;
}

.nav-tabs .nav-link.active {
    color: #28a745;
    background: transparent;
    border-bottom: 3px solid #28a745;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    color: #28a745;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
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

.action-btn {
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin: 0 2px;
}

.view-btn {
    background-color: #28a745;
    color: white;
}

.view-btn:hover {
    background-color: #218838;
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

/* Modal */
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
        <a onclick="showSection('student')"><i class="bi bi-people"></i> Student Manage</a>
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
                <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, #22c55e, #16a34a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Supervisor Dashboard</h5>
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

                <!-- Analytics Grid -->
                <!-- <div class="analytics-grid">
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
                </div> -->

                <!-- Quick Stats -->
                <div class="row mb-4 justify-content-center">
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted d-flex justify-content-center align-items-center gap-2">
                                Students
                                <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </h6>
                            <h3 class="text-warning">56</h3>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted d-flex justify-content-center align-items-center gap-2">
                                Request Pending
                                <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </h6>
                            <h3 class="text-danger">8</h3>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card p-3 text-center">
                            <h6 class="text-muted d-flex justify-content-center align-items-center gap-2">
                                Today's Practicals
                                <button class="btn btn-sm btn-outline-primary p-1" onclick="viewPendingRequests()" style="line-height: 1;">
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

            <!-- Student Management Section -->
            <div id="studentSection" style="display: none;">
                <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Student Management</h3>

                <div class="card p-4">
                    <!-- Search and Add Row -->
                    <div class="search-add-row">
                        <div class="search-container">
                            <input type="text" id="studentSearch" class="search-input" placeholder="Search by name, university ID or email...">
                            <button class="search-btn" onclick="searchStudents()">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                        <button class="add-btn" onclick="addStudent()">
                            <i class="bi bi-plus-circle"></i> Add Student
                        </button>
                    </div>

                    <!-- Students Table -->
                    <div class="table-responsive mt-3">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>University ID</th>
                                    <th>Request Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                <tr>
                                    <td><img src="https://ui-avatars.com/api/?name=John+Doe&background=22c55e&color=fff&size=50" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
                                    <td>John Doe</td>
                                    <td>SCI001</td>
                                    <td><span class="request-rate">12 requests</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewStudent('SCI001')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeStudent('SCI001')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><img src="https://ui-avatars.com/api/?name=Jane+Smith&background=22c55e&color=fff&size=50" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
                                    <td>Jane Smith</td>
                                    <td>SCI002</td>
                                    <td><span class="request-rate">8 requests</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewStudent('SCI002')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeStudent('SCI002')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><img src="https://ui-avatars.com/api/?name=Pathum+Perera&background=22c55e&color=fff&size=50" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
                                    <td>Pathum Perera</td>
                                    <td>SCI005</td>
                                    <td><span class="request-rate">3 requests</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewStudent('SCI005')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeStudent('SCI005')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><img src="https://ui-avatars.com/api/?name=Nimali+Fernando&background=22c55e&color=fff&size=50" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
                                    <td>Nimali Fernando</td>
                                    <td>SCI008</td>
                                    <td><span class="request-rate">15 requests</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewStudent('SCI008')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeStudent('SCI008')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><img src="https://ui-avatars.com/api/?name=Mike+Johnson&background=22c55e&color=fff&size=50" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
                                    <td>Mike Johnson</td>
                                    <td>SCI003</td>
                                    <td><span class="request-rate">6 requests</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewStudent('SCI003')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeStudent('SCI003')" title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><img src="https://ui-avatars.com/api/?name=Sarah+Wilson&background=22c55e&color=fff&size=50" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
                                    <td>Sarah Wilson</td>
                                    <td>SCI004</td>
                                    <td><span class="request-rate">10 requests</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewStudent('SCI004')" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn-remove" onclick="removeStudent('SCI004')" title="Remove">
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

            <!-- Student Details Modal -->
            <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-info-circle me-2"></i>
                                Student Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="studentDetailsContent">
                            <!-- Content will be populated by JavaScript -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requests Section with Notification Badges -->
<div id="activitySection" style="display: none;">
    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Requests</h3>

    <!-- Request Type Tabs with Notification Badges -->
    <ul class="nav nav-tabs mb-4" id="requestTabs" role="tablist">
        <li class="nav-item" role="presentation" style="position: relative;">
            <button class="nav-link active" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-selected="true">
                <i class="bi bi-person-plus me-2"></i>Account Requests
                <span class="notification-badge" id="accountNotification">3</span>
            </button>
        </li>
        <li class="nav-item" role="presentation" style="position: relative;">
            <button class="nav-link" id="practical-tab" data-bs-toggle="tab" data-bs-target="#practical" type="button" role="tab" aria-selected="false">
                <i class="bi bi-flask me-2"></i>Practical/Research Requests
                <span class="notification-badge" id="practicalNotification">6</span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="requestTabContent">
        <!-- Account Requests Tab -->
        <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">
            <div class="card p-4">
                <!-- Search Input for Account Requests -->
                <div class="filter-section" style="margin-bottom: 20px;">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="accountSearchInput" placeholder="Search by ID, name, email or status..." onkeyup="searchRequests('account')">
                        </div>
                        <div class="col-md-6">
                            <select class="filter-select" id="accountTimeRangeFilter" onchange="filterRequestsByTime('account')" style="min-width: 200px;">
                                <option value="all">All Time</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Account Requests Table -->
                <div class="table-responsive mt-3">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Date & Time</th>
                                <th>User Name</th>
                                <th>Email</th>
                              
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="accountRequestListBody">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Practical/Research Requests Tab -->
        <div class="tab-pane fade" id="practical" role="tabpanel" aria-labelledby="practical-tab">
            <div class="card p-4">
                <!-- Search Input for Practical/Research Requests -->
                <div class="filter-section" style="margin-bottom: 20px;">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="practicalSearchInput" placeholder="Search by ID, title, researcher or status..." onkeyup="searchRequests('practical')">
                        </div>
                        <div class="col-md-6">
                            <select class="filter-select" id="practicalTimeRangeFilter" onchange="filterRequestsByTime('practical')" style="min-width: 200px;">
                                <option value="all">All Time</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Practical/Research Requests Table -->
                <div class="table-responsive mt-3">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Date & Time</th>
                              
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="practicalRequestListBody">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Details Modal (Updated for both types) -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>
                    <span id="modalTitle">Request Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="approveRequestBtn" onclick="handleRequestAction('approve')">
                    <i class="bi bi-check-circle me-2"></i>Approve
                </button>
                <button type="button" class="btn btn-danger" id="rejectRequestBtn" onclick="handleRequestAction('reject')">
                    <i class="bi bi-x-circle me-2"></i>Reject
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

        </div> <!-- End content-area -->
    </div> 

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

   <script>
// ========== REQUEST DATA ==========
let accountRequests = [
    {
        id: 'ACC001',
        dateTime: '2024-01-15 10:30 AM',
        userName: 'John Doe',
        email: 'john@example.com',
      
        status: 'pending',
        details: {
            studentId: 'STU12345',
            course: 'Computer Science',
            year: '3rd Year',
            purpose: 'Access to laboratory resources'
        }
    },
    {
        id: 'ACC002',
        dateTime: '2024-01-15 02:15 PM',
        userName: 'Jane Smith',
        email: 'jane@example.com',
     
        status: 'approved',
        details: {
            employeeId: 'TCH789',
            department: 'Physics',
            position: 'Senior Lecturer'
        }
    },
    {
        id: 'ACC003',
        dateTime: '2024-01-16 09:30 AM',
        userName: 'Mike Johnson',
        email: 'mike@example.com',
     
        status: 'pending',
        details: {
            studentId: 'STU67890',
            course: 'Biology',
            year: '2nd Year',
            purpose: 'Lab access for research'
        }
    },
    {
        id: 'ACC004',
        dateTime: '2024-01-16 11:45 AM',
        userName: 'Sarah Wilson',
        email: 'sarah@example.com',
      
        status: 'pending',
        details: {
            employeeId: 'RES456',
            department: 'Biochemistry',
            project: 'Enzyme Research'
        }
    }
];

let practicalRequests = [
    {
        id: 'PRAC001',
        dateTime: '2024-01-14 09:00 AM',
     
      
     
        status: 'pending',
        details: {
            institution: 'University of Science',
            duration: '3 months',
            equipment: ['Microscope', 'pH Meter', 'Spectrophotometer'],
            abstract: 'Study of water quality parameters in urban areas'
        }
    },
    {
        id: 'PRAC002',
        dateTime: '2024-01-14 11:30 AM',
       
       
      
        status: 'in_progress',
        details: {
            course: 'Electrical Engineering',
            students: 25,
            labRequired: 'Electronics Lab',
            schedule: 'Monday-Wednesday, 2-5 PM'
        }
    },
    {
        id: 'PRAC003',
        dateTime: '2024-01-15 10:00 AM',
        projectTitle: 'DNA Sequencing',
       
     
        status: 'pending',
        details: {
            institution: 'Medical Research Center',
            duration: '6 months',
            equipment: ['Sequencer', 'Centrifuge', 'PCR Machine'],
            abstract: 'Genetic analysis of bacterial strains'
        }
    },
    {
        id: 'PRAC004',
        dateTime: '2024-01-15 02:30 PM',
      
      
      
        status: 'pending',
        details: {
            institution: 'Biotech Institute',
            duration: '4 months',
            equipment: ['X-ray Diffractometer', 'Microscope'],
            abstract: 'Protein structure determination'
        }
    },
    {
        id: 'PRAC005',
        dateTime: '2024-01-16 09:15 AM',
      
      
      
        status: 'pending',
        details: {
            course: 'Microbiology 301',
            students: 20,
            labRequired: 'Microbiology Lab',
            schedule: 'Tuesday-Thursday, 9-12 AM'
        }
    },
    {
        id: 'PRAC006',
        dateTime: '2024-01-16 01:00 PM',
      
      
       
        status: 'pending',
        details: {
            course: 'Cell Biology',
            students: 15,
            labRequired: 'Tissue Culture Lab',
            schedule: 'Wednesday-Friday, 1-4 PM'
        }
    }
];

// ========== STUDENT DATA ==========
const studentData = [
    {
        id: 'SCI001',
        name: 'John Doe',
        email: 'john.doe@science.lk',
        image: 'https://ui-avatars.com/api/?name=John+Doe&background=22c55e&color=fff&size=50',
        requestRate: 75,
    
        year: '3rd Year',
        phone: '0771234567',
        address: 'Colombo',
        joinDate: '2023-01-15'
    },
    {
        id: 'SCI002',
        name: 'Jane Smith',
        email: 'jane.smith@science.lk',
        image: 'https://ui-avatars.com/api/?name=Jane+Smith&background=22c55e&color=fff&size=50',
        requestRate: 60,
      
        year: '4th Year',
        phone: '0772345678',
        address: 'Kandy',
        joinDate: '2022-09-10'
    },
    {
        id: 'SCI005',
        name: 'Pathum Perera',
        email: 'pathum.p@science.lk',
        image: 'https://ui-avatars.com/api/?name=Pathum+Perera&background=22c55e&color=fff&size=50',
        requestRate: 45,
     
        year: '2nd Year',
        phone: '0773456789',
        address: 'Galle',
        joinDate: '2024-02-20'
    },
    {
        id: 'SCI008',
        name: 'Nimali Fernando',
        email: 'nimali.f@science.lk',
        image: 'https://ui-avatars.com/api/?name=Nimali+Fernando&background=22c55e&color=fff&size=50',
        requestRate: 90,
      
        year: '4th Year',
        phone: '0774567890',
        address: 'Colombo',
        joinDate: '2022-06-15'
    },
    {
        id: 'SCI003',
        name: 'Mike Johnson',
        email: 'mike.j@science.lk',
        image: 'https://ui-avatars.com/api/?name=Mike+Johnson&background=22c55e&color=fff&size=50',
        requestRate: 55,
      
        year: '3rd Year',
        phone: '0775678901',
        address: 'Negombo',
        joinDate: '2023-08-10'
    },
    {
        id: 'SCI004',
        name: 'Sarah Wilson',
        email: 'sarah.w@science.lk',
        image: 'https://ui-avatars.com/api/?name=Sarah+Wilson&background=22c55e&color=fff&size=50',
        requestRate: 80,
       
        year: '4th Year',
        phone: '0776789012',
        address: 'Kandy',
        joinDate: '2022-11-05'
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

// ========== NOTIFICATION FUNCTIONS ==========
function updateNotificationBadges() {
    const accountPending = accountRequests.filter(r => r.status === 'pending').length;
    const practicalPending = practicalRequests.filter(r => r.status === 'pending' || r.status === 'in_progress').length;
    
    const accountBadge = document.getElementById('accountNotification');
    const practicalBadge = document.getElementById('practicalNotification');
    
    if (accountBadge) {
        accountBadge.textContent = accountPending;
        accountBadge.style.display = accountPending > 0 ? 'inline-block' : 'none';
    }
    
    if (practicalBadge) {
        practicalBadge.textContent = practicalPending;
        practicalBadge.style.display = practicalPending > 0 ? 'inline-block' : 'none';
    }
}

// Dashboard function for view pending requests
function viewPendingRequests() {
    showSection('activity');
    
    // Optional: Switch to the tab with most pending requests
    const accountPending = accountRequests.filter(r => r.status === 'pending').length;
    const practicalPending = practicalRequests.filter(r => r.status === 'pending' || r.status === 'in_progress').length;
    
    if (practicalPending > accountPending) {
        // Switch to practical tab
        const practicalTab = document.getElementById('practical-tab');
        if (practicalTab) {
            practicalTab.click();
        }
    }
}

// ========== REQUEST FUNCTIONS (Account & Practical) ==========

// Auto-search function triggered by onkeyup
function searchRequests(type) {
    const searchInput = document.getElementById(type + 'SearchInput');
    const searchTerm = searchInput.value.toLowerCase().trim();
    
    if (type === 'account') {
        filterAccountRequests(searchTerm);
    } else {
        filterPracticalRequests(searchTerm);
    }
}

// Filter account requests based on search term
function filterAccountRequests(searchTerm) {
    const filteredRequests = accountRequests.filter(request => {
        return request.id.toLowerCase().includes(searchTerm) ||
               request.userName.toLowerCase().includes(searchTerm) ||
               request.email.toLowerCase().includes(searchTerm) ||
               request.accountType.toLowerCase().includes(searchTerm) ||
               request.status.toLowerCase().includes(searchTerm);
    });
    
    displayAccountRequests(filteredRequests);
}

// Filter practical requests based on search term
function filterPracticalRequests(searchTerm) {
    const filteredRequests = practicalRequests.filter(request => {
        return request.id.toLowerCase().includes(searchTerm) ||
               request.projectTitle.toLowerCase().includes(searchTerm) ||
               request.researcherName.toLowerCase().includes(searchTerm) ||
               request.type.toLowerCase().includes(searchTerm) ||
               request.status.toLowerCase().includes(searchTerm);
    });
    
    displayPracticalRequests(filteredRequests);
}

// Time range filter function for account/practical requests
function filterRequestsByTime(type) {
    const timeFilter = document.getElementById(type + 'TimeRangeFilter').value;
    const searchTerm = document.getElementById(type + 'SearchInput').value.toLowerCase();
    
    // Get current date for comparison
    const today = new Date();
    const oneDay = 24 * 60 * 60 * 1000;
    const oneWeek = 7 * oneDay;
    const oneMonth = 30 * oneDay;
    
    let filteredRequests;
    
    if (type === 'account') {
        filteredRequests = accountRequests.filter(request => {
            const requestDate = new Date(request.dateTime);
            
            // Apply time filter
            if (timeFilter === 'daily') {
                if (requestDate.toDateString() !== today.toDateString()) return false;
            } else if (timeFilter === 'weekly') {
                if (today - requestDate > oneWeek) return false;
            } else if (timeFilter === 'monthly') {
                if (today - requestDate > oneMonth) return false;
            }
            
            // Apply search filter if search term exists
            if (searchTerm) {
                return request.id.toLowerCase().includes(searchTerm) ||
                       request.userName.toLowerCase().includes(searchTerm) ||
                       request.email.toLowerCase().includes(searchTerm) ||
                       request.accountType.toLowerCase().includes(searchTerm) ||
                       request.status.toLowerCase().includes(searchTerm);
            }
            
            return true;
        });
        
        displayAccountRequests(filteredRequests);
    } else {
        filteredRequests = practicalRequests.filter(request => {
            const requestDate = new Date(request.dateTime);
            
            // Apply time filter
            if (timeFilter === 'daily') {
                if (requestDate.toDateString() !== today.toDateString()) return false;
            } else if (timeFilter === 'weekly') {
                if (today - requestDate > oneWeek) return false;
            } else if (timeFilter === 'monthly') {
                if (today - requestDate > oneMonth) return false;
            }
            
            // Apply search filter if search term exists
            if (searchTerm) {
                return request.id.toLowerCase().includes(searchTerm) ||
                       request.projectTitle.toLowerCase().includes(searchTerm) ||
                       request.researcherName.toLowerCase().includes(searchTerm) ||
                       request.type.toLowerCase().includes(searchTerm) ||
                       request.status.toLowerCase().includes(searchTerm);
            }
            
            return true;
        });
        
        displayPracticalRequests(filteredRequests);
    }
}

// Display account requests
function displayAccountRequests(requests) {
    const tbody = document.getElementById('accountRequestListBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (requests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No account requests found</td></tr>';
        return;
    }
    
    requests.forEach(request => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${request.id}</td>
            <td>${request.dateTime}</td>
            <td>${request.userName}</td>
            <td>${request.email}</td>
          
            <td><span class="status-badge status-${request.status}">${request.status}</span></td>
            <td>
                <button class="action-btn view-btn" onclick="viewRequestDetails('${request.id}', 'account')">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Display practical requests
function displayPracticalRequests(requests) {
    const tbody = document.getElementById('practicalRequestListBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (requests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No practical/research requests found</td></tr>';
        return;
    }
    
    requests.forEach(request => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${request.id}</td>
            <td>${request.dateTime}</td>
           
         
         
            <td><span class="status-badge status-${request.status}">${request.status}</span></td>
            <td>
                <button class="action-btn view-btn" onclick="viewRequestDetails('${request.id}', 'practical')">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// View request details
function viewRequestDetails(requestId, type) {
    let request;
    let modalTitle = document.getElementById('modalTitle');
    
    if (type === 'account') {
        request = accountRequests.find(r => r.id === requestId);
        modalTitle.textContent = 'Account Request Details';
    } else {
        request = practicalRequests.find(r => r.id === requestId);
        modalTitle.textContent = 'Practical/Research Request Details';
    }
    
    if (request) {
        displayRequestDetails(request, type);
        
        // Store current request info for action buttons
        document.getElementById('approveRequestBtn').setAttribute('data-request-id', requestId);
        document.getElementById('approveRequestBtn').setAttribute('data-request-type', type);
        document.getElementById('rejectRequestBtn').setAttribute('data-request-id', requestId);
        document.getElementById('rejectRequestBtn').setAttribute('data-request-type', type);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('requestDetailsModal'));
        modal.show();
    }
}

// Display request details in modal
function displayRequestDetails(request, type) {
    const contentDiv = document.getElementById('requestDetailsContent');
    let html = '';
    
    if (type === 'account') {
        html = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Request ID:</strong> ${request.id}</p>
                    <p><strong>Date & Time:</strong> ${request.dateTime}</p>
                    <p><strong>User Name:</strong> ${request.userName}</p>
                    <p><strong>Email:</strong> ${request.email}</p>
                    <p><strong>Account Type:</strong> ${request.accountType}</p>
                    <p><strong>Status:</strong> <span class="status-badge status-${request.status}">${request.status}</span></p>
                </div>
                <div class="col-md-6">
                    <h6>Additional Details:</h6>
                    ${Object.entries(request.details).map(([key, value]) => 
                        `<p><strong>${key}:</strong> ${value}</p>`
                    ).join('')}
                </div>
            </div>
        `;
    } else {
        html = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Request ID:</strong> ${request.id}</p>
                    <p><strong>Date & Time:</strong> ${request.dateTime}</p>
                    <p><strong>Project Title:</strong> ${request.projectTitle}</p>
                    <p><strong>Researcher Name:</strong> ${request.researcherName}</p>
                    <p><strong>Type:</strong> ${request.type}</p>
                    <p><strong>Status:</strong> <span class="status-badge status-${request.status}">${request.status}</span></p>
                </div>
                <div class="col-md-6">
                    <h6>Additional Details:</h6>
                    ${Object.entries(request.details).map(([key, value]) => {
                        if (Array.isArray(value)) {
                            return `<p><strong>${key}:</strong> ${value.join(', ')}</p>`;
                        }
                        return `<p><strong>${key}:</strong> ${value}</p>`;
                    }).join('')}
                </div>
            </div>
        `;
    }
    
    contentDiv.innerHTML = html;
}

// Handle approve/reject actions
function handleRequestAction(action) {
    const requestId = document.getElementById('approveRequestBtn').getAttribute('data-request-id');
    const requestType = document.getElementById('approveRequestBtn').getAttribute('data-request-type');
    
    if (requestType === 'account') {
        const requestIndex = accountRequests.findIndex(r => r.id === requestId);
        if (requestIndex !== -1) {
            accountRequests[requestIndex].status = action === 'approve' ? 'approved' : 'rejected';
            // Refresh the display
            filterRequestsByTime('account');
        }
    } else {
        const requestIndex = practicalRequests.findIndex(r => r.id === requestId);
        if (requestIndex !== -1) {
            practicalRequests[requestIndex].status = action === 'approve' ? 'approved' : 'rejected';
            // Refresh the display
            filterRequestsByTime('practical');
        }
    }
    
    // Update notification badges
    updateNotificationBadges();
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('requestDetailsModal')).hide();
    
    // Show success message
    alert(`Request ${action === 'approve' ? 'approved' : 'rejected'} successfully!`);
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
    document.getElementById('studentSection').style.display = 'none';
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

// ========== STUDENT MANAGEMENT FUNCTIONS ==========
function searchStudents() {
    const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
    const filtered = studentData.filter(student =>
        student.name.toLowerCase().includes(searchTerm) ||
        student.id.toLowerCase().includes(searchTerm) ||
        student.email.toLowerCase().includes(searchTerm)
    );
    displayStudentTable(filtered);
}

function displayStudentTable(students) {
    const tableBody = document.getElementById('studentTableBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    students.forEach(student => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td><img src="${student.image}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
            <td>${student.name}</td>
            <td>${student.id}</td>
            <td>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div class="progress-bar" style="width: 80px; height: 6px;">
                        <div class="progress-fill" style="width: ${student.requestRate}%"></div>
                    </div>
                    <span style="font-size: 12px; font-weight: 600;">${student.requestRate}%</span>
                </div>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view" onclick="viewStudent('${student.id}')" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-remove" onclick="removeStudent('${student.id}')" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
    
    if (students.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="5" class="text-center">No students found</td>`;
        tableBody.appendChild(row);
    }
}

function viewStudent(id) {
    const student = studentData.find(s => s.id === id);
    if (!student) return;
    
    const detailsContent = document.getElementById('studentDetailsContent');
    
    detailsContent.innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center">
                <img src="${student.image}" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #22c55e;" class="mb-3">
                <h4>${student.name}</h4>
                <p class="text-muted">${student.id}</p>
            </div>
            <div class="col-md-8">
                <table class="table table-borderless">
                    <tr><th style="width: 150px;">Email:</th><td>${student.email}</td></tr>
                    <tr><th>Department:</th><td>${student.department}</td></tr>
                    <tr><th>Year:</th><td>${student.year}</td></tr>
                    <tr><th>Phone:</th><td>${student.phone}</td></tr>
                    <tr><th>Address:</th><td>${student.address}</td></tr>
                    <tr><th>Join Date:</th><td>${student.joinDate}</td></tr>
                    <tr><th>Request Rate:</th><td><span class="request-rate">${student.requestRate} requests</span></td></tr>
                </table>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('studentDetailsModal')).show();
}

function addStudent() {
    alert('Add Student functionality would open a form modal');
    // In a real app, this would open a form to add a new student
}

function removeStudent(id) {
    if (confirm(`Are you sure you want to remove student ${id}?`)) {
        const index = studentData.findIndex(s => s.id === id);
        if (index !== -1) {
            studentData.splice(index, 1);
        }
        displayStudentTable(studentData);
        alert(`Student ${id} has been removed successfully!`);
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

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    // Load events from localStorage
    loadEvents();
    
    // Show dashboard by default
    showSection('dashboard');
    
    // Initialize calendar
    initCalendar();
    
    // Display student table
    displayStudentTable(studentData);
    
    // Display account and practical requests
    displayAccountRequests(accountRequests);
    displayPracticalRequests(practicalRequests);
    
    // Update notification badges
    updateNotificationBadges();
});

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
</script>
</body>

</html>
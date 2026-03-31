<?php
session_start();

// Function to check internet connection
function checkInternetConnection()
{
    $connected = @fsockopen("www.google.com", 80, $errno, $errstr, 5);
    if ($connected) {
        fclose($connected);
        return true;
    }
    return false;
}

// Function to check database connection
function checkDatabaseConnection()
{
    try {
        require_once '../config/database.php';
        // Try a simple query to verify connection
        $test_query = "SELECT 1";
        $result = Database::search($test_query);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Check internet connection first
if (!checkInternetConnection()) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connection Error - MicroLab</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Inter', sans-serif;
                margin: 0;
                padding: 20px;
            }

            .error-card {
                background: white;
                border-radius: 24px;
                padding: 40px;
                max-width: 500px;
                width: 100%;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
                text-align: center;
                animation: slideIn 0.5s ease;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .icon {
                font-size: 4rem;
                color: #ef4444;
                margin-bottom: 20px;
            }

            h2 {
                color: #166534;
                font-weight: 700;
                margin-bottom: 15px;
            }

            p {
                color: #4b5563;
                margin-bottom: 25px;
                line-height: 1.6;
            }

            .btn-retry {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 50px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                text-decoration: none;
                display: inline-block;
            }

            .btn-retry:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3);
            }

            .details {
                background: #f8fafc;
                border-radius: 16px;
                padding: 15px;
                margin-top: 20px;
                text-align: left;
                font-size: 0.9rem;
                color: #64748b;
            }

            .details i {
                color: #22c55e;
                margin-right: 8px;
            }
        </style>
    </head>

    <body>
        <div class="error-card">
            <div class="icon">🌐</div>
            <h2>No Internet Connection</h2>
            <p>Unable to connect to the internet. Please check your network connection and try again.</p>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-retry">
                <i class="bi bi-arrow-repeat me-2"></i>Retry Connection
            </a>
            <div class="details">
                <p><i class="bi bi-info-circle"></i> This application requires an active internet connection to access the database.</p>
                <p class="mb-0"><i class="bi bi-lightbulb"></i> Tips: Check your Wi-Fi/Ethernet connection, disable VPN if active, or contact your network administrator.</p>
            </div>
        </div>
    </body>

    </html>
<?php
    exit();
}

// Then check database connection
if (!checkDatabaseConnection()) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Error - MicroLab</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Inter', sans-serif;
                margin: 0;
                padding: 20px;
            }

            .error-card {
                background: white;
                border-radius: 24px;
                padding: 40px;
                max-width: 600px;
                width: 100%;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
                text-align: center;
                animation: slideIn 0.5s ease;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .icon {
                font-size: 4rem;
                color: #ef4444;
                margin-bottom: 20px;
            }

            h2 {
                color: #166534;
                font-weight: 700;
                margin-bottom: 15px;
            }

            p {
                color: #4b5563;
                margin-bottom: 25px;
                line-height: 1.6;
            }

            .btn-retry {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 50px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                text-decoration: none;
                display: inline-block;
            }

            .btn-retry:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3);
            }

            .details {
                background: #f8fafc;
                border-radius: 16px;
                padding: 15px;
                margin-top: 20px;
                text-align: left;
                font-size: 0.9rem;
                color: #64748b;
            }

            .details i {
                color: #22c55e;
                margin-right: 8px;
            }

            .host-info {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 10px;
                border-radius: 8px;
                margin-top: 15px;
                font-size: 0.85rem;
            }
        </style>
    </head>

    <body>
        <div class="error-card">
            <div class="icon">🗄️</div>
            <h2>Database Connection Failed</h2>
            <p>Unable to connect to the database server. This could be due to:</p>
            <div class="details">
                <p><i class="bi bi-wifi-off"></i> Internet connection issues</p>
                <p><i class="bi bi-server"></i> Database server is down or unreachable</p>
                <p><i class="bi bi-shield-lock"></i> Firewall or security group blocking connection</p>
                <p><i class="bi bi-database"></i> Database credentials are incorrect</p>
                <div class="host-info">
                    <strong>Host:</strong> database-1.csnikggyo5mr.us-east-1.rds.amazonaws.com<br>
                    <strong>Region:</strong> us-east-1<br>
                    <strong>Error:</strong> Hostname could not be resolved
                </div>
            </div>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-retry mt-3">
                <i class="bi bi-arrow-repeat me-2"></i>Retry Connection
            </a>
        </div>
    </body>

    </html>
<?php
    exit();
}



require_once '../config/database.php';

if (isset($_SESSION["user_id"]) && isset($_SESSION["user_role"]) && $_SESSION["user_role"] === 'hod') {
?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Admin Dashboard</title>
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
                min-width: 20px;
                height: 20px;
                display: none;
                /* Hidden by default */
                align-items: center;
                justify-content: center;
                font-size: 0.7rem;
                font-weight: 600;
                padding: 0 5px;
                box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
                animation: pulse 2s infinite;
            }

            .request-badge.visible {
                display: flex;
                /* Show when visible class is added */
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
                gap: 4px;
                flex-wrap: nowrap;
                align-items: center;
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
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                transition: all 0.3s ease;
                white-space: nowrap;
                display: inline-flex;
                align-items: center;
                gap: 2px;
            }

            .btn-view:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
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
                font-size: 2.5rem;
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
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 2px;
                white-space: nowrap;
            }

            .btn-activate:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(34, 197, 94, 0.3);
            }

            .btn-deactivate {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: white;
                border: none;
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 2px;
                white-space: nowrap;
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


            .modal {
                z-index: 9000 !important;
            }

            .modal-backdrop {
                z-index: 8999 !important;
            }

            .modal-content {
                z-index: 9001 !important;
            }

            /* Force modal visibility */
            .modal.show {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                z-index: 9000 !important;
            }

            .modal-dialog {
                z-index: 9001 !important;
                position: relative !important;
            }

            #rejectReasonModal.show .modal-dialog,
            #confirmModal.show .modal-dialog {
                z-index: 9001 !important;
                position: relative !important;
            }

            /* Ensure modal is always on top */
            .modal.fade.show {
                display: flex !important;
                z-index: 9000 !important;
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
                gap: 4px;
                flex-wrap: nowrap;
                align-items: center;
            }



            .notification-bell {
                position: relative;
                display: inline-block;
            }

            .request-badge {
                position: absolute;
                top: -8px;
                right: -8px;
                background-color: #dc3545;
                color: white;
                border-radius: 50%;
                padding: 2px 6px;
                font-size: 10px;
                font-weight: bold;
                min-width: 18px;
                text-align: center;
            }

            .notification-dropdown {
                position: absolute;
                top: 35px;
                right: 0;
                width: 450px;
                max-height: 550px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
                z-index: 1000;
                overflow: hidden;
                animation: slideDown 0.3s ease;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .dropdown-header {
                padding: 12px 16px;
                background: #f8f9fa;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .dropdown-header h6 {
                margin: 0;
                color: #166534;
                font-weight: 600;
            }

            .btn-close-sm {
                background: none;
                border: none;
                font-size: 20px;
                cursor: pointer;
                color: #6c757d;
                padding: 0;
                width: 24px;
                height: 24px;
                line-height: 1;
            }

            .btn-close-sm:hover {
                color: #dc3545;
            }

            .dropdown-body {
                max-height: 500px;
                overflow-y: auto;
            }

            .notification-item {
                padding: 15px;
                border-bottom: 1px solid #f0f0f0;
                transition: background 0.2s;
                position: relative;
            }

            .notification-item:hover {
                background: #f9fafb;
            }

            .notification-item.unread {
                background: #f0fdf4;
                border-left: 3px solid #22c55e;
            }

            .notification-title {
                font-weight: 600;
                color: #1f2937;
                margin-bottom: 8px;
                font-size: 14px;
            }

            .notification-title strong {
                color: #166534;
            }

            .notification-meta {
                font-size: 12px;
                color: #6c757d;
                margin-bottom: 8px;
                line-height: 1.5;
            }

            .notification-meta i {
                margin-right: 4px;
            }

            .notification-actions {
                display: flex;
                gap: 8px;
                margin-top: 10px;
            }

            .btn-approve {
                background: #22c55e;
                color: white;
                border: none;
                padding: 5px 14px;
                border-radius: 6px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }

            .btn-approve:hover {
                background: #16a34a;
                transform: translateY(-1px);
            }

            .btn-reject {
                background: #ef4444;
                color: white;
                border: none;
                padding: 5px 14px;
                border-radius: 6px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }

            .btn-reject:hover {
                background: #dc2626;
                transform: translateY(-1px);
            }

            .btn-view {
                background: #6c757d;
                color: white;
                border: none;
                padding: 5px 14px;
                border-radius: 6px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }

            .btn-view:hover {
                background: #5a6268;
                transform: translateY(-1px);
            }

            .empty-state {
                text-align: center;
                padding: 40px 20px;
                color: #9ca3af;
            }

            .empty-state i {
                font-size: 48px;
                margin-bottom: 10px;
                opacity: 0.5;
            }

            .toast-notification {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: white;
                padding: 12px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 9999;
                transform: translateX(400px);
                transition: transform 0.3s ease;
            }

            .toast-notification.show {
                transform: translateX(0);
            }

            .toast-success {
                border-left: 4px solid #22c55e;
            }

            .toast-success i {
                color: #22c55e;
            }

            .toast-error {
                border-left: 4px solid #ef4444;
            }

            .toast-error i {
                color: #ef4444;
            }

            .toast-warning {
                border-left: 4px solid #f59e0b;
            }

            .toast-warning i {
                color: #f59e0b;
            }


            .btn-edit {
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                color: white;
                border: none;
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 2px;
                white-space: nowrap;
            }

            .btn-edit:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
            }

            .btn-remove {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: white;
                border: none;
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 2px;
                white-space: nowrap;
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
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 2px;
                white-space: nowrap;
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
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 2px;
                white-space: nowrap;
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

            .stat-card {
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .stat-card:hover {
                transform: translateY(-5px) scale(1.02);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            }

            .stat-card:active {
                transform: translateY(-2px) scale(1.01);
            }

            /* Highlight effect for scrolled sections */
            @keyframes highlight-pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
                }

                70% {
                    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
                }
            }

            .table-highlight {
                animation: highlight-pulse 1.5s ease;
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

            /* Modal backdrop animation */
            .modal-backdrop.fade {
                opacity: 0;
                transition: opacity 0.2s linear;
            }

            .modal-backdrop.fade.show {
                opacity: 0.5;
            }

            /* Modal content animation */
            .modal.fade .modal-dialog {
                transform: scale(0.8);
                transition: transform 0.2s ease-in-out;
            }

            .modal.show .modal-dialog {
                transform: scale(1);
            }

            /* Custom scrollbar for modals */
            .modal-content {
                border-radius: 16px;
                overflow: hidden;
                z-index: 9001 !important;
            }

            .modal {
                display: none;
            }

            .modal.show {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                z-index: 9000 !important;
            }

            .modal-header {
                padding: 16px 20px;
            }

            .modal-body {
                padding: 20px;
                max-height: 60vh;
                overflow-y: auto;
            }

            .modal-footer {
                padding: 16px 20px;
                background: #f8f9fa;
            }

            /* Focus styles for textarea */
            #rejectionReason:focus {
                border-color: #dc2626;
                box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.25);
                outline: none;
            }

            /* Button hover effects */
            .modal-footer .btn {
                padding: 8px 20px;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.2s;
            }

            .modal-footer .btn:hover {
                transform: translateY(-1px);
            }

            .modal-footer .btn:active {
                transform: translateY(0);
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

            /* Add to your existing CSS */
            .analytics-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            /* For larger screens, ensure 6 cards show properly */
            @media (min-width: 1200px) {
                .analytics-grid {
                    grid-template-columns: repeat(6, 1fr);
                }
            }

            /* For medium screens, adjust to 3 cards per row */
            @media (max-width: 1199px) and (min-width: 768px) {
                .analytics-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }

            /* For mobile, 1 card per row */
            @media (max-width: 767px) {
                .analytics-grid {
                    grid-template-columns: 1fr;
                }
            }

            /* Center the second row cards */
            .justify-content-center {
                display: flex;
                justify-content: center;
                gap: 20px;
            }

            /* Ensure stat cards in second row match the style */
            .stat-card {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                border-radius: 20px;
                padding: 25px;
                color: white;
                position: relative;
                overflow: hidden;
                transition: all 0.3s;
                height: 100%;
                min-width: 200px;
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

            /* Ensure cards in second row are the same size */
            .row .col-md-3 {
                flex: 0 0 auto;
                width: 250px;
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

            .notification-bell {
                position: relative;
                cursor: pointer;
                padding: 8px;
                border-radius: 12px;
                transition: background 0.3s;
            }

            .notification-bell:hover {
                background: rgba(34, 197, 94, 0.1);
            }

            .notification-badge {
                position: absolute;
                top: 2px;
                right: 2px;
                background: #dc3545;
                color: white;
                border-radius: 50%;
                padding: 1px 5px;
                font-size: 11px;
                font-weight: bold;
                min-width: 18px;
                text-align: center;
                animation: pulse 2s infinite;
            }

            .notification-dropdown {
                display: none;
                position: absolute;
                top: calc(100% + 15px);
                right: 0;
                width: 360px;
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                overflow: hidden;
                border: 1px solid rgba(0, 0, 0, 0.08);
            }

            .notification-dropdown.show {
                display: block;
                animation: slideDown 0.3s ease;
            }

            @keyframes slideDown {
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
                padding: 16px 20px;
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .notification-header h6 {
                color: white;
            }

            .notification-list {
                max-height: 380px;
                overflow-y: auto;
            }

            .notification-item {
                padding: 14px 20px;
                border-bottom: 1px solid #f0f0f0;
                cursor: pointer;
                transition: background 0.2s;
                position: relative;
            }

            .notification-item:hover {
                background: rgba(34, 197, 94, 0.05);
            }

            .notification-item.unread {
                background: rgba(34, 197, 94, 0.06);
                border-left: 3px solid #22c55e;
            }

            .notification-item.unread::before {
                content: '';
                position: absolute;
                top: 18px;
                right: 15px;
                width: 8px;
                height: 8px;
                background: #22c55e;
                border-radius: 50%;
            }

            .notification-item.approval {
                border-left: 3px solid #f59e0b;
                background: rgba(245, 158, 11, 0.05);
            }

            .notification-item .notif-title {
                font-weight: 600;
                font-size: 0.9rem;
                color: #1a1a1a;
                margin-bottom: 3px;
            }

            .notification-item .notif-message {
                font-size: 0.82rem;
                color: #555;
                line-height: 1.4;
                margin-bottom: 4px;
            }

            .notification-item .notif-time {
                font-size: 0.75rem;
                color: #aaa;
            }

            .notification-item .approve-btns {
                display: flex;
                gap: 8px;
                margin-top: 10px;
            }

            .notification-footer {
                padding: 12px 20px;
                text-align: center;
                border-top: 1px solid #f0f0f0;
                background: #fafafa;
            }

            .notification-footer a {
                color: #22c55e;
                font-size: 0.85rem;
                text-decoration: none;
                font-weight: 500;
            }

            .notification-footer a:hover {
                text-decoration: underline;
            }

            .no-notifications {
                padding: 40px 20px;
                text-align: center;
                color: #aaa;
            }

            .no-notifications i {
                font-size: 2.5rem;
                margin-bottom: 10px;
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
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 2px;
                white-space: nowrap;
            }

            /* Request Count Badges */
            /* Request Count Badges - Updated to hide when empty */
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

            /* Hide badge when it contains 0 */
            .request-count-badge:empty,
            .request-count-badge[data-count="0"],
            .request-count-badge:contains("0") {
                display: none !important;
            }

            /* JavaScript will handle this via class, but this is a fallback */
            .request-count-badge.zero-count {
                display: none !important;
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
            <a onclick="showSection('activity')"><i class="bi bi-activity"></i> Requests
                <span class="request-count-badge" id="sidebarRequestBadge">0</span>
            </a>
            <a onclick="showSection('analytics')"><i class="bi bi-download"></i> Download</a>
            <a onclick="showSection('logbook')"><i class="bi bi-book"></i> Logbook</a>
            <!-- <a onclick="showSection('reports')"><i class="bi bi-file-text"></i> Reports</a> -->
            <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>

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

                    <h5 class="fw-bold mb-0" style="background: linear-gradient(135deg, #22c55e, #16a34a); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        Welcome, <span id="userName"><?php echo htmlspecialchars($_SESSION["user_first_name"]); ?></span>
                    </h5>
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

                    <!-- Replace this entire block in your topbar -->
                    <div style="position: relative; display: flex; gap: 12px; align-items: center;">


                        <!-- Notification Bell with Dropdown -->
                        <div class="notification-bell" style="position:relative;">
                            <i class="bi bi-journal-check fs-5" style="color: #166534; cursor: pointer;" onclick="toggleNotificationDropdown()"></i>
                            <span class="request-badge" id="requestBadge">0</span>

                            <!-- Dropdown Menu -->
                            <div id="notificationDropdown" class="notification-dropdown" style="display: none;">
                                <div class="dropdown-header">
                                    <h6>Pending Logbook Reviews</h6>
                                    <button class="btn-close-sm" onclick="toggleNotificationDropdown()">×</button>
                                </div>
                                <div class="dropdown-body" id="notificationList">
                                    <div class="text-center p-3">
                                        <div class="spinner-border text-success spinner-border-sm" role="status"></div>
                                        <p class="mt-2 mb-0">Loading...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

















                        <div class="notification-bell" id="mainNotificationBell" onclick="toggleMainNotifications()" style="position: relative; cursor: pointer;">
                            <i class="bi bi-bell fs-5" style="color: #166534;"></i>
                            <span class="notification-badge" id="notificationBadge" style="display:none;">0</span>
                        </div>

                        <!-- Notification Dropdown — INSIDE same relative parent as bell -->
                        <div class="notification-dropdown" id="mainNotificationDropdown">
                            <div class="notification-header">
                                <h6 class="mb-0">Notifications</h6>
                                <div class="d-flex gap-2 align-items-center">
                                    <span id="notificationNewCount" class="badge bg-success">0 new</span>
                                    <button onclick="markAllRead()" class="btn btn-sm btn-outline-light py-0"
                                        style="font-size:0.75rem; color:white; border-color:rgba(255,255,255,0.5);">
                                        Mark all read
                                    </button>
                                </div>
                            </div>
                            <div class="notification-list" id="mainNotificationList">
                                <div class="text-center py-3 text-muted">
                                    <div class="spinner-border spinner-border-sm"></div>
                                    <span class="ms-2">Loading...</span>
                                </div>
                            </div>
                            <div class="notification-footer">
                                <a href="#" onclick="markAllRead(); return false;">Clear all</a>
                            </div>
                        </div>

                    </div>











                    <span class="fw-semibold d-none d-sm-block" style="color: #166534;">
                        <?php echo htmlspecialchars($_SESSION['user_first_name'] ?? 'User'); ?>
                    </span>
                    <div class="dropdown">
                        <?php

                        // Get user data from session
                        $first_name = $_SESSION['user']['first_name'] ?? $_SESSION['user_first_name'] ?? 'User';
                        $last_name = $_SESSION['user']['last_name'] ?? $_SESSION['user_last_name'] ?? '';
                        $full_name = trim($first_name . ' ' . $last_name);

                        // Get profile image path from session
                        $profile_image = $_SESSION['user']['img_path'] ?? $_SESSION['img_path'] ?? '';

                        error_log("DEBUG: Profile image from session: " . ($profile_image ?: 'EMPTY'));

                        if (!empty($profile_image)) {
                            // Clean the path (remove any leading slashes and fix backslashes)
                            $clean_path = str_replace('\\', '/', $profile_image);
                            $clean_path = ltrim($clean_path, '/');

                            // Remove any 'LRRS/' prefix if it exists (just in case)
                            $clean_path = preg_replace('/^LRRS\//', '', $clean_path);

                            // Detect the base URL dynamically
                            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'];

                            // Get the script name and remove the file (hod.php) to get the directory
                            $script_path = dirname($_SERVER['SCRIPT_NAME']);

                            // Construct the base URL dynamically
                            $base_url = $protocol . '://' . $host . $script_path;

                            // For web path - use relative path that works from views folder
                            $image_url = '../' . $clean_path;

                            // Full system path for file checking
                            $full_path = dirname(__DIR__) . '/' . $clean_path;

                            error_log("DEBUG: Base URL: " . $base_url);
                            error_log("DEBUG: Checking full path: " . $full_path);

                            // Check if file exists
                            if (!file_exists($full_path)) {
                                error_log("DEBUG: File not found at main path, trying alternative path");
                                // Try alternative path - maybe it's stored without 'assets/' prefix
                                $filename = basename($clean_path);
                                $alt_path = 'assets/profile_images/' . $filename;
                                $full_alt_path = dirname(__DIR__) . '/' . $alt_path;
                                error_log("DEBUG: Checking alternate path: " . $full_alt_path);
                                if (file_exists($full_alt_path)) {
                                    $image_url = '../' . $alt_path;
                                    error_log("DEBUG: Found at alternative path: " . $image_url);
                                } else {
                                    error_log("DEBUG: File not found at alternative path either, using avatar");
                                    // Fallback to avatar if image doesn't exist
                                    $image_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=22c55e&color=fff&size=100";
                                    error_log("Image not found, using avatar");
                                }
                            } else {
                                error_log("DEBUG: File found at main path: " . $image_url);
                            }
                        } else {
                            // No profile image, use avatar
                            error_log("DEBUG: No profile image in session, using avatar");
                            $image_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=22c55e&color=fff&size=100";
                        }
                        ?>

                        <img src="<?php echo htmlspecialchars($image_url); ?>"
                            class="profile-img dropdown-toggle"
                            data-bs-toggle="dropdown"
                            alt="<?php echo htmlspecialchars($full_name); ?>">

                        <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                            <li><a class="dropdown-item" style="cursor: pointer;" onclick="loadProfileData();" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="bi bi-person me-2"></i>Profile</a></li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- CONTENT AREA -->
            <div class="content-area">

                <!-- Dashboard Section -->
                <div id="dashboardSection">
                    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Dashboard Overview</h3>

                    <!-- Analytics Cards - All 6 in one row -->
                    <div class="analytics-grid justify-content-center" style="grid-template-columns: repeat(6, 1fr);">
                        <?php
                        // Define the function for role counts - FIXED column names
                        function getCountByRole(string $role): int
                        {
                            $query = "SELECT COUNT(lab_user.id) as count 
              FROM lab_user 
              INNER JOIN lab_user_has_role ON lab_user.id = lab_user_has_role.lab_user_id 
              INNER JOIN role ON lab_user_has_role.role_id = role.id 
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
                        $student_count = getCountByRole('student');
                        $supervisor_count = getCountByRole('supervisor');
                        $technical_count = getCountByRole('technical_officer');

                        // Calculate Equipment Utilization Rate
                        $utilization_query = "
    SELECT 
        (SELECT COALESCE(SUM(total_qty), 0) FROM equipment) as total_qty,
        (SELECT COALESCE(SUM(broken_qty), 0) FROM broken) as broken_qty,
        (SELECT COALESCE(SUM(repair_qty), 0) FROM repair) as repair_qty
";

                        $utilization_result = Database::search($utilization_query);
                        $utilization_rate = 0;

                        if ($utilization_result && $utilization_result->num_rows > 0) {
                            $row = $utilization_result->fetch_assoc();
                            $total_qty = (int)$row['total_qty'];
                            $broken_qty = (int)$row['broken_qty'];
                            $repair_qty = (int)$row['repair_qty'];

                            $available_qty = $total_qty - ($broken_qty + $repair_qty);

                            if ($total_qty > 0) {
                                $utilization_rate = round(($available_qty / $total_qty) * 100);
                            }
                        }

                        // Get today's practicals count
                        $today = date('Y-m-d');
                        $today_query = "SELECT COUNT(*) as today_count FROM reservation WHERE DATE(request_date) = ?";
                        $today_result = Database::search($today_query, "s", [$today]);
                        $today_count = 0;
                        if ($today_result && $today_result->num_rows > 0) {
                            $row = $today_result->fetch_assoc();
                            $today_count = $row['today_count'];
                        }

                        // Get maintenance/repair count
                        $repair_query = "SELECT COUNT(*) as repair_count FROM repair";
                        $repair_result = Database::search($repair_query);
                        $repair_count = 0;
                        if ($repair_result && $repair_result->num_rows > 0) {
                            $row = $repair_result->fetch_assoc();
                            $repair_count = $row['repair_count'];
                        }
                        ?>

                        <!-- Card 1: Students -->
                        <div class="stat-card" onclick="showUserManagementAndScroll('student')" style="cursor: pointer;">
                            <i class="bi bi-mortarboard-fill"></i>
                            <h3><?php echo $student_count; ?></h3>
                            <p>Students</p>
                        </div>

                        <!-- Card 2: Supervisors -->
                        <div class="stat-card" onclick="showUserManagementAndScroll('supervisor')" style="cursor: pointer;">
                            <i class="bi bi-person-badge-fill"></i>
                            <h3><?php echo $supervisor_count; ?></h3>
                            <p>Supervisors</p>
                        </div>

                        <!-- Card 3: Technical Officers -->
                        <div class="stat-card" onclick="showUserManagementAndScroll('technical')" style="cursor: pointer;">
                            <i class="bi bi-person-gear"></i>
                            <h3><?php echo $technical_count; ?></h3>
                            <p>Technical Officers</p>
                        </div>

                        <!-- Card 4: Equipment Utilization -->
                        <div class="stat-card" onclick="showSection('equipment')" style="cursor: pointer;">
                            <i class="bi bi-graph-up"></i>
                            <h3><?php echo $utilization_rate; ?>%</h3>
                            <p>Equipment Utilization</p>
                        </div>

                        <!-- Card 5: Today's Practicals -->
                        <!-- <div class="stat-card" onclick="showSection('history')" style="cursor: pointer;">
                            <i class="bi bi-calendar-check"></i>
                            <h3><?php echo $today_count; ?></h3>
                            <p>Today's Practicals</p>
                        </div> -->

                        <!-- Card 6: Maintenance -->
                        <!-- <div class="stat-card" onclick="viewMaintenanceItems()" style="cursor: pointer;">
                            <i class="bi bi-tools"></i>
                            <h3><?php echo $repair_count; ?></h3>
                            <p>Maintenance</p>
                        </div> -->
                    </div>


                    <div class="row">


                        <div class="col-md-6 mb-4">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h5 class="fw-bold mb-0" style="color: #166534;">Completed</h5>
                                    <small id="usageChartUpdated" class="text-muted" style="font-size:0.75rem;"></small>
                                </div>
                                <small class="text-muted mb-2 d-block" style="font-size:0.8rem;">

                                    Monthly completed Practicals &nbsp; <span style="color:#3b82f6;font-size:0.75rem;">- -</span>Average · scroll →

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
                                        <th>Email</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="studentTableBody">
                                    <?php

                                    $query = "SELECT 
            lu.id as user_id,          
            lu.first_name,
            lu.last_name,
            lu.university_id,
            lu.mobile,
            lu.email,
            lu.img_path,
            lu.status,                  
            lu.approved_datetime
          FROM lab_user lu
          INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id  
          INNER JOIN role r ON uhr.role_id = r.id                     
          WHERE r.role = 'student'                                     
          AND lu.approved_datetime IS NOT NULL
          ORDER BY lu.status DESC, lu.join_datetime DESC";

                                    $result = Database::search($query);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {

                                            $user_id = $row['user_id'];
                                            $full_name = $row['first_name'] . ' ' . $row['last_name'];

                                            // Fix image path
                                            // Fix image path - REMOVED /LRRS/ prefix
                                            $profile_image = !empty($row['img_path'])
                                                ? '/' . ltrim(str_replace('\\', '/', $row['img_path']), '/')  // Just add leading slash
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=50';

                                            // Set status based on database value
                                            $status = ($row['status'] == 1) ? 'active' : 'inactive';

                                            // Output the row with dynamic status
                                            // Replace your existing echo '<tr ...>' line with this:
                                            echo '<tr data-user-id="' . htmlspecialchars($row['university_id']) . '" 
          
         data-status="' . $status . '">';
                                            echo '<td>';
                                            echo '<img src="' . htmlspecialchars($profile_image) . '" 
                                   style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #22c55e;">';
                                            echo '</td>';
                                            echo '<td>' . htmlspecialchars($row['university_id']) . '</td>';
                                            echo '<td>' . htmlspecialchars($full_name) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                            echo '<td>';
                                            echo '<div class="action-buttons">';
                                            // echo '<button class="btn-edit" onclick="editUser(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                            // echo '<i class="bi bi-pencil-square"></i> Edit';
                                            // echo '</button>';

                                            // ✅ Show appropriate button based on status_user
                                            if ($row['status'] == 1) {
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
                                        echo '<tr><td colspan="5" class="text-center py-4">No approved students found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>









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
                                        <th>Email</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="supervisorTableBody">
                                    <?php

                                    $sup_query = "SELECT 
                lu.id,
                lu.first_name,
                lu.last_name,
                lu.university_id,
                lu.mobile,
                lu.email,
                lu.img_path,
                lu.status,  
                lu.approved_datetime
              FROM lab_user lu
              INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
              INNER JOIN role r ON uhr.role_id = r.id
              WHERE r.role = 'supervisor'  
              AND lu.approved_datetime IS NOT NULL
              ORDER BY lu.status DESC, lu.join_datetime DESC";

                                    $sup_result = Database::search($sup_query);

                                    if ($sup_result && $sup_result->num_rows > 0) {
                                        while ($row = $sup_result->fetch_assoc()) {
                                            $full_name = $row['first_name'] . ' ' . $row['last_name'];

                                            $profile_image = !empty($row['img_path'])
                                                ? '/' . ltrim(str_replace('\\', '/', $row['img_path']), '/')
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=22c55e&color=fff&size=50';


                                            $status = ($row['status'] == 1) ? 'active' : 'inactive';


                                            echo '<tr data-user-id="' . htmlspecialchars($row['university_id']) . '" 
          
         data-status="' . $status . '">';
                                            echo '<td>';

                                            echo '<img src="' . htmlspecialchars($profile_image) . '" 
                                   style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #22c55e;">';
                                            echo '</td>';
                                            echo '<td>' . htmlspecialchars($row['university_id']) . '</td>';
                                            echo '<td>' . htmlspecialchars($full_name) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                            echo '<td>';
                                            echo '<div class="action-buttons">';
                                            // echo '<button class="btn-edit" onclick="editUser(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                            // echo '<i class="bi bi-pencil-square"></i> Edit';
                                            // echo '</button>';


                                            if ($row['status'] == 1) {

                                                echo '<button class="btn-deactivate" onclick="toggleUserStatus(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                                echo '<i class="bi bi-person-x"></i> Deactivate';
                                                echo '</button>';
                                            } else {

                                                echo '<button class="btn-activate" onclick="toggleUserStatus(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                                echo '<i class="bi bi-person-check"></i> Activate';
                                                echo '</button>';
                                            }

                                            echo '</div>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="5" class="text-center py-4">No approved supervisors found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>













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
                lu.id,
                lu.first_name,
                lu.last_name,
                lu.university_id,
                lu.mobile,
                lu.email,
                lu.img_path,
                lu.status,  
                lu.approved_datetime
              FROM lab_user lu
              INNER JOIN lab_user_has_role uhr ON lu.id = uhr.lab_user_id
              INNER JOIN role r ON uhr.role_id = r.id
              WHERE r.role = 'technical_officer'  
              AND lu.approved_datetime IS NOT NULL
              ORDER BY lu.status DESC, lu.join_datetime DESC";

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
                                            $status = ($row['status'] == 1) ? 'active' : 'inactive';

                                            // Output the row with dynamic status
                                            // Replace your existing echo '<tr ...>' line with this:
                                            echo '<tr data-user-id="' . htmlspecialchars($row['university_id']) . '" 
          
         data-status="' . $status . '">';
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
                                            // echo '<button class="btn-edit" onclick="editUser(\'' . htmlspecialchars($row['university_id']) . '\')">';
                                            // echo '<i class="bi bi-pencil-square"></i> Edit';
                                            // echo '</button>';

                                            // Show appropriate button based on status_user
                                            if ($row['status'] == 1) {
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









                <!-- Equipment Management Section -->
                <!-- Equipment Management Section -->
                <div id="equipmentSection" style="display: none;">
                    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Equipment Management</h3>

                    <div class="card p-4">
                        <!-- Search and Filter Row -->
                        <div class="search-add-row" style="display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap;">
                            <div class="search-container" style="display: flex; gap: 10px; flex: 2; min-width: 300px;">
                                <input type="text" id="equipmentSearch" class="search-input"
                                    placeholder="Search by name or code..."
                                    onkeyup="searchEquipment()"
                                    style="flex: 1;">
                            </div>

                            <!-- FILTER DROPDOWN - SINGLE ONLY -->
                            <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 200px;">
                                <select id="statusFilterequipment" class="filter-select" onchange="searchEquipmentStatus()"
                                    style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #e0e0e0; background: white;">
                                    <option value="all">All Equipment</option>
                                    <option value="maintenance">Maintenance Pending</option>
                                    <option value="broken">Broken</option>
                                </select>
                            </div>

                            <button class="add-btn" onclick="addEquipment()" style="white-space: nowrap;">
                                <i class="bi bi-plus-circle"></i> Add Equipment
                            </button>
                        </div>

                        <!-- Equipment Table -->
                        <div class="table-responsive mt-3">
                            <table class="user-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Equipment Name</th>
                                        <th>Total Qty</th>
                                        <th>Maintenance Qty</th>
                                        <th>Broken Qty</th>
                                         <th>Location</th>
                                        <th>Usage %</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="equipmentTableBody">
                                    <?php
                                    // Define the equipment data array
                                    $equipmentDataTable = [];

                                    // Fetch equipment data from database
                                    $equipment_query = "SELECT 
            e.id,
            e.code,
            e.name,
            e.description,
            e.total_qty,
            e.image_path,
            (SELECT COALESCE(SUM(broken_qty), 0) FROM broken WHERE equipment_id = e.id) as broken_qty,
            (SELECT COALESCE(SUM(repair_qty), 0) FROM repair WHERE equipment_id = e.id) as repair_qty,
            GROUP_CONCAT(DISTINCT l.location SEPARATOR ', ') as locations
        FROM equipment e
        LEFT JOIN equipment_has_location ehl ON e.id = ehl.equipment_id
        LEFT JOIN location l ON ehl.location_id = l.id
        WHERE e.is_hod_checked = 1
        GROUP BY e.id
        ORDER BY e.name ASC";

                                    $equipment_result = Database::search($equipment_query, '');
                                    
                                    // DEBUG: Check if query executed and has results
                                    error_log("Equipment Query Executed. Rows found: " . ($equipment_result ? $equipment_result->num_rows : 0));
                                    if ($equipment_result && $equipment_result->num_rows > 0) {
                                        $first_row = $equipment_result->fetch_assoc();
                                        error_log("First Equipment Row: " . json_encode($first_row));
                                        $equipment_result->data_seek(0); // Reset pointer
                                    }

                                    if ($equipment_result && $equipment_result->num_rows > 0) {
                                        // Get max booking count for dynamic percentage calculation
                                        $max_bookings_query = "SELECT MAX(booking_count) as max_bookings FROM (
                SELECT COUNT(*) as booking_count FROM book_equipment GROUP BY equipment_id
            ) as counts";
                                        $max_bookings_result = Database::search($max_bookings_query, '');
                                        $max_bookings = 0;
                                        if ($max_bookings_result && $max_bookings_result->num_rows > 0) {
                                            $max_row = $max_bookings_result->fetch_assoc();
                                            $max_bookings = (int)$max_row['max_bookings'];
                                        }

                                        while ($row = $equipment_result->fetch_assoc()) {
                                            $equipment_code = htmlspecialchars($row['code']);
                                            $name = htmlspecialchars($row['name']);
                                            $total_qty = (int)$row['total_qty'];
                                            $broken_qty = (int)$row['broken_qty'];
                                            $repair_qty = (int)$row['repair_qty'];
                                            $equipment_id = (int)$row['id'];
                                            
                                            $equipment_location = !empty($row['locations']) && $row['locations'] !== null 
                                                ? htmlspecialchars($row['locations']) 
                                                : 'Not assigned';

                                            // Calculate available quantity
                                            $available_qty = $total_qty - ($broken_qty + $repair_qty);

                                            // Ensure available quantity is not negative
                                            $available_qty = max(0, $available_qty);

                                            // Calculate usage percentage based on bookings
                                            $usage_query = "SELECT COUNT(*) as booking_count FROM book_equipment WHERE equipment_id = ?";
                                            $usage_result = Database::search($usage_query, "i", [$equipment_id]);
                                            $usage_count = 0;
                                            if ($usage_result && $usage_result->num_rows > 0) {
                                                $usage_row = $usage_result->fetch_assoc();
                                                $usage_count = (int)$usage_row['booking_count'];
                                            }

                                            // Dynamic usage percentage calculation
                                            if ($max_bookings > 0) {
                                                // Based on max bookings among all equipment
                                                $usage_percentage = round(($usage_count / $max_bookings) * 100);
                                            } else {
                                                // Fallback to total quantity based calculation
                                                $usage_percentage = $total_qty > 0 ? round(($usage_count / $total_qty) * 100) : 0;
                                            }
                                            $usage_percentage = min(100, max(0, $usage_percentage)); // Ensure between 0-100

                                            // Set image path
                                            $image_path = !empty($row['img_path'])
                                                ? '/' . ltrim(str_replace('\\', '/', $row['img_path']), '/')
                                                : 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';

                                            // Add to data array for JavaScript
                                            $equipmentDataTable[] = [
                                                'code' => $equipment_code,
                                                'name' => $name,
                                                'image' => $image_path,
                                                'total' => $total_qty,
                                                'available' => $available_qty,
                                                'broken' => $broken_qty,
                                                'maintenance' => $repair_qty,
                                                'location' => $equipment_location,
                                                'usage' => $usage_percentage,
                                                'id' => $equipment_id
                                            ];

                                            // Status badge for available/total ratio (for reference)
                                            $ratio = $total_qty > 0 ? $available_qty / $total_qty : 0;
                                            $statusColor = '#22c55e'; // green
                                            $statusText = 'Good';
                                            if ($ratio < 0.3) {
                                                $statusColor = '#ef4444'; // red
                                                $statusText = 'Critical';
                                            } elseif ($ratio < 0.6) {
                                                $statusColor = '#f59e0b'; // orange
                                                $statusText = 'Low';
                                            }

                                            // Bar color based on usage percentage
                                            $barColor = '#22c55e'; // green
                                            if ($usage_percentage < 30) {
                                                $barColor = '#ef4444'; // red - low usage
                                            } elseif ($usage_percentage < 60) {
                                                $barColor = '#f59e0b'; // orange - medium usage
                                            }
                                    ?>
                                            <tr data-equipment-id="<?php echo $equipment_code; ?>"
                                                data-equipment-id-numeric="<?php echo $equipment_id; ?>"
                                                data-maintenance="<?php echo $repair_qty; ?>"
                                                data-broken="<?php echo $broken_qty; ?>"
                                                data-available="<?php echo $available_qty; ?>"
                                                data-total="<?php echo $total_qty; ?>"
                                                data-status="<?php echo $statusText; ?>">

                                                <td>
                                                    <img src="<?php echo $image_path; ?>"
                                                        style="width:50px;height:50px;object-fit:contain;border-radius:4px;"
                                                        onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'"
                                                        alt="<?php echo $name; ?>">
                                                </td>

                                                <td>
                                                    <strong><?php echo $name; ?></strong>
                                                    <div><small class="text-muted">Code: <?php echo $equipment_code; ?></small></div>
                                                </td>

                                                <td>
                                                    <?php if ($repair_qty > 0): ?>
                                                        <span class="badge bg-warning text-dark"><?php echo $repair_qty; ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?php if ($broken_qty > 0): ?>
                                                        <span class="badge bg-danger"><?php echo $broken_qty; ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?php if ($equipment_location === 'Not assigned'): ?>
                                                        <span class="badge bg-secondary" style="font-size: 0.85rem; padding: 6px 10px;">
                                                            <i class="bi bi-geo-alt"></i> Not Assigned
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info" style="font-size: 0.85rem; padding: 6px 10px; background-color: #0ea5e9 !important;">
                                                            <i class="bi bi-geo-alt"></i> <?php echo $equipment_location; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div style="width:100px;height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;">
                                                            <div style="width:<?php echo $usage_percentage; ?>%;height:8px;background:<?php echo $barColor; ?>;border-radius:4px;transition:width 0.3s ease;"></div>
                                                        </div>
                                                        <span style="font-weight:600;color:<?php echo $barColor; ?>;min-width:45px;">
                                                            <?php echo $usage_percentage; ?>%
                                                        </span>
                                                    </div>
                                                    <small class="text-muted">(<?php echo $usage_count; ?> bookings)</small>
                                                </td>

                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn-view" onclick="viewEquipmentByCode('<?php echo $equipment_code; ?>')" title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn-edit" onclick="editEquipment('<?php echo $equipment_code; ?>')" title="Edit">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                        <button class="btn-remove" onclick="removeEquipment('<?php echo $equipment_code; ?>')" title="Remove">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                    <p class="mt-2">No equipment found in database</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>








































                <!-- Reservation Details Section -->
                <div id="historySection" style="display: none;">
                    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Reservation Details</h3>

                    <!-- Search and Filter Row (Outside card) -->
                    <div class="search-add-row" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <div class="search-container" style="display: flex; gap: 5px; flex: 1;">
                            <input type="text"
                                id="reservationSearch"
                                class="search-input"
                                placeholder="Search by Reservation ID..."
                                oninput="searchReservations()"
                                style="flex: 1; margin: 0;">
                        </div>

                        <div class="filter-section" style="margin-bottom: 0; min-width: 150px;">
                            <select class="filter-select" id="statusFilter" onchange="searchReservations()" style="width: 100%; margin: 0;">
                                <option value="all">All Status</option>
                                <option value="ready">Ready</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>




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
                        <!-- <div class="filter-section" style="margin-bottom:20px;">
                            <select class="filter-select" id="timeRangeFilter"
                                onchange="filterRequestsByTime()" style="min-width:200px;">
                                <option value="all">All Time</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div> -->

                        <!-- Table -->
                        <!-- Table -->
                        <div class="table-responsive mt-3">
                            <table class="user-table">
                                <thead>
                                    <tr id="reqTableHead">
                                        <!-- This will be dynamically populated by JavaScript -->
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


                        <div id="reqModalContent"
                            style="padding:24px; max-height:55vh; overflow-y:auto;"></div>


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
                    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Download</h3>


                    <!-- <div class="analytics-grid">
                     
                        <div class="stat-card">
                            <i class="bi bi-mortarboard-fill"></i>
                            <h3>56</h3>
                            <p>Students</p>
                        </div>

                    
                        <div class="stat-card">
                            <i class="bi bi-person-badge-fill"></i>
                            <h3>5</h3>
                            <p>Supervisors/Lecturers</p>
                        </div>

                       
                        <div class="stat-card">
                            <i class="bi bi-person-gear"></i>
                            <h3>3</h3>
                            <p>Technical Officers</p>
                        </div>

                       
                        <div class="stat-card">
                            <i class="bi bi-tools"></i>
                           <h3><?php echo $utilization_rate; ?>%</h3>
                            <p>Equipment Utilization Rate</p>
                        </div>

                       
                        <div class="stat-card">
                            <i class="bi bi-gear-wide-connected"></i>
                            <h3>3</h3>
                            <p>Maintenance</p>
                        </div>
                    </div> -->

                    <!-- First Row: Rejected Requests Report & Equipment Usage Chart -->
                    <!-- <div class="row"> -->
                    <!-- Rejected Requests Report -->
                    <!-- <div class="col-md-6 mb-4">
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


                                <div class="mb-3">
                                    <input type="text"
                                        id="equipmentUsageSearch"
                                        class="form-control"
                                        placeholder="Search equipment name..."
                                        oninput="filterEquipmentUsage()">
                                </div>


                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead class="sticky-top bg-white">
                                            <tr>
                                                <th>Equipment Name</th>
                                                <th>Usage Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody id="equipmentUsageTableBody">

                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div> -->

                    <!-- Second Row: Download Inventory Button -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card p-4 text-center">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0" style="color: #166534;">
                                        <i class="bi bi-download me-2"></i>
                                        Full Equipment Inventory(.xls)
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

                <!-- Logbook Section -->
                <div id="logbookSection" style="display: none;">
                    <h3 class="mb-4" style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">Logbook Management</h3>

                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0" style="color: #166534;">
                                <i class="bi bi-book-half me-2"></i>
                                Practical Logbooks
                            </h5>
                        </div>

                        <!-- Search Input -->
                        <div class="mb-3">
                            <input type="text"
                                id="logbookSearchInput"
                                class="form-control form-control-lg"
                                placeholder="🔍 Search by Reservation ID (e.g., RES-2026-)..."
                                style="border: 2px solid #e0e0e0; border-radius: 8px;"
                                oninput="filterLogbookTable()">
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 8%;">NO</th>
                                        <th style="width: 35%;">Reservation ID</th>
                                        <th style="width: 27%;">Submitted Date</th>
                                        <th style="width: 30%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="logbookTableBody">
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <div class="spinner-border text-success me-2" role="status" style="width:1.5rem;height:1.5rem;"></div>
                                            <span>Loading logbooks...</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>



                <!-- Rejection Reason Modal -->
                <!-- <div class="modal fade" id="rejectionReasonModal" tabindex="-1" aria-hidden="true">
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
                              
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

        <!-- <div class="modal fade" id="equipmentDetailsModal" tabindex="-1" aria-hidden="true">
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
        </div> -->





        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.2);">

                    <!-- Header -->
                    <div class="modal-header py-3" style="background: linear-gradient(135deg, #059669, #10b981);">
                        <h5 class="modal-title text-white fw-semibold">
                            <i class="bi bi-pencil-square me-2"></i>Edit User Details
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4" style="background: #f8fafc;">
                        <!-- Loading Spinner -->
                        <div id="editUserLoading" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-success" role="status"></div>
                            <p class="mt-2 text-muted small">Loading user data...</p>
                        </div>

                        <!-- Edit User Form -->
                        <div id="editUserForm" style="display: none;">
                            <!-- Hidden Inputs -->
                            <input type="hidden" id="editUserId">
                            <input type="hidden" id="editUserOriginalEmail">
                            <input type="hidden" id="editUserOriginalRole">

                            <!-- Status and University ID Row (Top Section) -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 shadow-sm">
                                        <!-- Status Badge on Left -->
                                        <div>
                                            <span class="text-muted small me-2">Status:</span>
                                            <span id="editUserStatusDisplay">
                                                <span class="badge bg-success">Active</span>
                                            </span>
                                        </div>
                                        <!-- University ID on Right (Increased Font) -->
                                        <div class="d-flex align-items-center">
                                            <span class="text-muted small me-2">University ID:</span>
                                            <span id="editUserUniversityIdDisplay" class="fw-bold" style="font-size: 1.3rem; color: #059669;">---</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Summary Card (with name and email) -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Current Profile Image -->
                                        <div class="position-relative">
                                            <img id="editCurrentImage" src="" alt="Profile"
                                                style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid #10b981;">
                                            <span id="editUserStatus" class="position-absolute bottom-0 end-0">
                                                <span class="badge bg-success p-2 rounded-circle" style="width: 12px; height: 12px;"></span>
                                            </span>
                                        </div>
                                        <!-- User Name and Email -->
                                        <div class="flex-grow-1">
                                            <h5 id="editUserFullName" class="fw-bold mb-1" style="color: #0f172a;">Loading...</h5>
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="bi bi-envelope me-1"></i>
                                                <span id="editUserEmailDisplay">---</span>
                                            </div>
                                        </div>
                                        <!-- Role Badge -->
                                        <div>
                                            <span class="badge bg-opacity-10 p-3 rounded-pill" id="editUserRoleBadge" style="background: rgba(5, 150, 105, 0.1); color: #059669; font-size: 0.85rem;">
                                                <i class="bi bi-tag-fill me-1"></i>
                                                <span id="editUserRoleDisplay">Student</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Grid -->
                            <div class="row g-3">
                                <!-- Left Column - Personal Info -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body p-3">
                                            <h6 class="fw-semibold mb-3" style="color: #059669; font-size: 0.9rem;">
                                                <i class="bi bi-person-circle me-1"></i>Personal Info
                                            </h6>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted mb-1">First Name</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-0"><i class="bi bi-person text-success small"></i></span>
                                                    <input type="text" class="form-control form-control-sm bg-light border-0" id="editFirstName" placeholder="First name">
                                                </div>
                                                <div class="invalid-feedback small" id="editFirstNameError"></div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted mb-1">Last Name</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-0"><i class="bi bi-person text-success small"></i></span>
                                                    <input type="text" class="form-control form-control-sm bg-light border-0" id="editLastName" placeholder="Last name">
                                                </div>
                                                <div class="invalid-feedback small" id="editLastNameError"></div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-muted mb-1">University ID</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-0"><i class="bi bi-card-text text-success small"></i></span>
                                                    <input type="text" class="form-control form-control-sm bg-light border-0" id="editUniversityId" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column - Contact Info -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body p-3">
                                            <h6 class="fw-semibold mb-3" style="color: #059669; font-size: 0.9rem;">
                                                <i class="bi bi-envelope me-1"></i>Contact Info
                                            </h6>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted mb-1">Email</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-0"><i class="bi bi-envelope text-success small"></i></span>
                                                    <input type="email" class="form-control form-control-sm bg-light border-0" id="editEmail" placeholder="email@example.com">
                                                </div>
                                                <div class="invalid-feedback small" id="editEmailError"></div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-muted mb-1">Mobile</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-0"><i class="bi bi-phone text-success small"></i></span>
                                                    <input type="text" class="form-control form-control-sm bg-light border-0" id="editMobile" maxlength="10" placeholder="07XXXXXXXX" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                </div>
                                                <div class="invalid-feedback small" id="editMobileError"></div>
                                                <small class="text-muted d-block mt-1 small">10 digits only</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Role and Supervisor Row (Combined) -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="row g-3">
                                                <!-- Role Selection (Left side - 5 columns) -->
                                                <div class="col-md-5">
                                                    <h6 class="fw-semibold mb-2" style="color: #059669; font-size: 0.9rem;">
                                                        <i class="bi bi-shield-lock me-1"></i>Role
                                                    </h6>
                                                    <select class="form-select form-select-sm bg-light border-0" id="editRole" onchange="handleRoleChange()">
                                                        <option value="student">🎓 Student</option>
                                                        <option value="supervisor">👥 Supervisor</option>
                                                        <option value="technical_officer">🔧 Technical Officer</option>
                                                        <option value="hod">📋 HOD</option>
                                                    </select>
                                                    <div class="invalid-feedback small" id="editRoleError"></div>
                                                </div>

                                                <!-- Supervisor Selection (Right side - 7 columns) - This will be shown/hidden -->
                                                <div class="col-md-7" id="supervisorSection" style="display: none;">
                                                    <h6 class="fw-semibold mb-2" style="color: #059669; font-size: 0.9rem;">
                                                        <i class="bi bi-person-badge me-1"></i>Supervisor
                                                    </h6>
                                                    <div class="d-flex gap-2">
                                                        <select class="form-select form-select-sm bg-light border-0 flex-grow-1" id="editSupervisorId">
                                                            <option value="">Select supervisor...</option>
                                                        </select>
                                                        <button class="btn btn-outline-success btn-sm" onclick="loadSupervisors()" type="button" style="white-space: nowrap;">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback small" id="editSupervisorIdError"></div>
                                                    <div id="currentSupervisor" class="mt-2 small text-muted"></div>
                                                </div>
                                            </div>

                                            <!-- Role Change Warning (Full width) -->
                                            <div id="roleChangeWarning" class="alert alert-warning py-2 px-3 mb-0 small mt-3 d-none">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                <span id="roleChangeWarningText"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Profile Image Upload -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <h6 class="fw-semibold mb-2" style="color: #059669; font-size: 0.9rem;">
                                                <i class="bi bi-image me-1"></i>Update Profile Picture
                                            </h6>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="position-relative">
                                                    <img id="editProfileImagePreview" src="" alt="Preview"
                                                        style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #10b981; display: none;">
                                                    <div id="editProfileImagePlaceholder" class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <i class="bi bi-person-fill text-secondary" style="font-size: 1.5rem;"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <input type="file" class="form-control form-control-sm bg-light border-0" id="editProfileImage"
                                                        accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewEditImage(this)">
                                                    <div class="invalid-feedback small" id="editImageError"></div>
                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                        <small class="text-muted">Max 6MB</small>
                                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                                                            onclick="clearEditImage()" style="display: none; font-size: 0.75rem;" id="clearEditImageBtn">
                                                            <i class="bi bi-x"></i>Clear
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer py-2 px-4 border-0" style="background: #f8fafc;">
                        <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-success btn-sm px-4" id="saveUserChanges" onclick="saveUserChanges()" style="background: linear-gradient(135deg, #059669, #10b981); border: none;">
                            <i class="bi bi-check-circle me-1"></i>Save
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Equipment Modal (same as before, but add this hidden input inside the form) -->
        <div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">

                    <!-- Header - we'll change the title dynamically -->
                    <div class="modal-header py-3" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                        <h5 class="modal-title text-white fw-semibold" id="equipmentModalTitle">
                            <i class="bi bi-plus-circle me-2"></i>Add New Equipment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4" style="background: #f8fafc;">
                        <form id="addEquipmentForm" enctype="multipart/form-data">
                            <!-- Hidden field to store equipment ID for edit mode -->
                            <input type="hidden" id="eqId" value="">

                            <div class="row g-3">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-body p-3">
                                            <h6 class="fw-semibold mb-3" style="color: #22c55e;">
                                                <i class="bi bi-info-circle me-1"></i>Basic Information
                                            </h6>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Equipment Code <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="eqCode"
                                                    placeholder="e.g., MIC-001" required>
                                                <div class="invalid-feedback" id="eqCodeError"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Equipment Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="eqName"
                                                    placeholder="e.g., Microscope" required>
                                                <div class="invalid-feedback" id="eqNameError"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Total Quantity <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="eqQty"
                                                    min="1" value="1" required>
                                                <div class="invalid-feedback" id="eqQtyError"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Simultaneous Users</label>
                                                <input type="number" class="form-control" id="eqSimultaneousUsers"
                                                    min="1" value="1">
                                                <small class="text-muted">Number of users that can use this at once</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-body p-3">
                                            <h6 class="fw-semibold mb-3" style="color: #22c55e;">
                                                <i class="bi bi-image me-1"></i>Equipment Image
                                            </h6>

                                            <div class="text-center mb-3">
                                                <div class="image-preview-container"
                                                    style="width: 150px; height: 150px; margin: 0 auto; 
                                                    border: 2px dashed #22c55e; border-radius: 10px; 
                                                    display: flex; align-items: center; justify-content: center;
                                                    overflow: hidden; background: #f8f9fa;">
                                                    <img id="eqImagePreview" src="#" alt="Preview"
                                                        style="max-width: 100%; max-height: 100%; display: none;">
                                                    <i id="eqImagePlaceholder" class="bi bi-image"
                                                        style="font-size: 3rem; color: #22c55e;"></i>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <input type="file" class="form-control" id="eqImage"
                                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                                    onchange="previewEquipmentImage(this)">
                                                <small class="text-muted">Max size: 6MB (JPG, PNG, GIF, WEBP)</small>
                                                <div id="currentImageInfo" class="mt-2 small text-muted" style="display: none;"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Sterilization Required</label>
                                                <select class="form-select" id="eqSterilization">
                                                    <option value="NO">No</option>
                                                    <option value="YES">Yes</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Reservation Required</label>
                                                <select class="form-select" id="eqReservation">
                                                    <option value="YES">Yes</option>
                                                    <option value="NO">No</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Lab Location <span class="text-danger">*</span></label>
                                                <select class="form-select" id="eqLabLocation">
                                                    <option value="">-- Select Lab Location --</option>
                                                    <option value="13">A12-001</option>
                                                    <option value="14">A12-004</option>
                                                    <option value="15">A12-006</option>
                                                    <option value="16">A11-101 (Special Student Lab)</option>
                                                    <option value="17">A11-108 (Instrument Lab)</option>
                                                    <option value="18">A05-002 (Teaching Lab)</option>
                                                </select>
                                                <div class="invalid-feedback" id="eqLabLocationError"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Full Width Row - Description -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <h6 class="fw-semibold mb-3" style="color: #22c55e;">
                                                <i class="bi bi-card-text me-1"></i>Description
                                            </h6>
                                            <textarea class="form-control" id="eqDescription" rows="3"
                                                placeholder="Enter equipment description..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Rejection Reason Modal -->


                    <!-- Footer - change button text dynamically -->
                    <div class="modal-footer py-3 px-4 border-0" style="background: #f8fafc;">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-success px-4" onclick="saveEquipment()"
                            style="background: linear-gradient(135deg, #22c55e, #16a34a); border: none;">
                            <i class="bi bi-check-circle me-1"></i><span id="modalSaveButtonText">Save Equipment</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Modal -->
        <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header py-4 px-4 border-0" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                        <h5 class="modal-title text-white" id="profileModalLabel">
                            <i class="bi bi-person-circle me-2"></i>My Profile
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="profileForm" enctype="multipart/form-data">
                            <!-- Profile Picture Section -->
                            <div class="text-center mb-4">
                                <div style="position: relative; display: inline-block; cursor: pointer;" onclick="document.getElementById('profileImageInput').click();">
                                    <img id="profilePreview"
                                        src="https://ui-avatars.com/api/?name=Profile&background=22c55e&color=fff&size=120"
                                        alt="Profile"
                                        style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #22c55e;"
                                        onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\' viewBox=\'0 0 120 120\'><circle cx=\'60\' cy=\'60\' r=\'60\' fill=\'%2322c55e\'/><text x=\'50%25\' y=\'54%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-size=\'40\' fill=\'white\' font-family=\'Arial\'>P</text></svg>'">

                                    <input type="file" id="profileImageInput" name="profile_image"
                                        accept="image/*" style="display: none;" onchange="handleProfileImageChange(this)">
                                    <div style="position: absolute; bottom: 0; right: 0; background: #22c55e; color: white; 
                                        width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; 
                                        justify-content: center; border: 3px solid white; font-size: 18px; pointer-events: none;">
                                        <i class="bi bi-camera-fill"></i>
                                    </div>
                                </div>
                                <p class="text-muted mt-2 small">Click image to change profile picture</p>
                            </div>

                            <!-- Form Fields -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label fw-600">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="first_name"
                                        placeholder="Enter first name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label fw-600">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="last_name"
                                        placeholder="Enter last name" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-600">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="mobile" class="form-label fw-600">Mobile Number</label>
                                    <input type="tel" class="form-control" id="mobile" name="mobile"
                                        placeholder="Enter mobile number">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="universityId" class="form-label fw-600">University ID</label>
                                <input type="text" class="form-control" id="universityId" name="university_id"
                                    placeholder="Enter university ID" readonly>
                            </div>

                            <!-- Password Change Section (Collapsible) -->
                            <div class="mt-4 pt-4 border-top">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-3"
                                    onclick="togglePasswordSection()" id="passwordToggleBtn">
                                    <i class="bi bi-lock me-1"></i>Change Password
                                </button>

                                <div id="passwordChangeSection" style="display: none;">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="currentPassword" class="form-label fw-600">Current Password</label>
                                            <input type="password" class="form-control" id="currentPassword"
                                                placeholder="Enter current password">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="newPassword" class="form-label fw-600">New Password</label>
                                            <input type="password" class="form-control" id="newPassword"
                                                placeholder="Enter new password">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="confirmPassword" class="form-label fw-600">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirmPassword"
                                                placeholder="Confirm new password">
                                        </div>
                                    </div>

                                    <div class="alert alert-info" role="alert">
                                        <small>
                                            <i class="bi bi-info-circle me-1"></i>
                                            Password must be at least 8 characters long
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer py-3 px-4 border-0" style="background: #f8fafc;">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-success px-4" onclick="saveHodProfile()"
                            style="background: linear-gradient(135deg, #22c55e, #16a34a); border: none;">
                            <i class="bi bi-check-circle me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Scripts -->
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>



















            var equipmentDataTable = <?php echo json_encode($equipmentDataTable, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            let equipmentUsageData = [];

            // Global toggle functions for inline onclick handlers
            function toggleNotificationDropdown() {
                const dropdown = document.getElementById('notificationDropdown');
                if (dropdown && (dropdown.style.display === 'none' || dropdown.style.display === '')) {
                    dropdown.style.display = 'block';
                    // Load pending logbooks if function exists
                    if (typeof loadPendingLogbooks === 'function') {
                        loadPendingLogbooks();
                    }
                } else if (dropdown) {
                    dropdown.style.display = 'none';
                }
            }

            function toggleMainNotifications() {
                const dropdown = document.getElementById('mainNotificationDropdown');
                if (dropdown) {
                    if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                        dropdown.style.display = 'block';
                    } else {
                        dropdown.style.display = 'none';
                    }
                }
            }

            // Preview image before upload
            function previewEditImage(input) {
                const preview = document.getElementById('editProfileImagePreview');
                const placeholder = document.getElementById('editProfileImagePlaceholder');
                const clearBtn = document.getElementById('clearEditImageBtn');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        placeholder.style.display = 'none';
                        clearBtn.style.display = 'inline-block';
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Clear selected image
            function clearEditImage() {
                const input = document.getElementById('editProfileImage');
                const preview = document.getElementById('editProfileImagePreview');
                const placeholder = document.getElementById('editProfileImagePlaceholder');
                const clearBtn = document.getElementById('clearEditImageBtn');

                input.value = '';
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
                clearBtn.style.display = 'none';
            }

            // Handle role change
            // Handle role change
            function handleRoleChange() {
                const role = document.getElementById('editRole').value;
                const originalRole = document.getElementById('editUserOriginalRole').value;
                const supervisorSection = document.getElementById('supervisorSection');
                const warningDiv = document.getElementById('roleChangeWarning');
                const warningText = document.getElementById('roleChangeWarningText');
                const roleDisplay = document.getElementById('editUserRoleDisplay');

                // Update role badge
                if (roleDisplay) {
                    let roleText = role.charAt(0).toUpperCase() + role.slice(1).replace('_', ' ');
                    roleDisplay.textContent = roleText;
                }

                // Show/hide supervisor section based on role
                if (role === 'student') {
                    supervisorSection.style.display = 'block';
                    supervisorSection.classList.add('fade-in');
                } else {
                    supervisorSection.style.display = 'none';
                    document.getElementById('editSupervisorId').value = '';
                    document.getElementById('currentSupervisor').innerHTML = '';
                }

                // Show warning if role is changing
                if (role !== originalRole && originalRole) {
                    warningDiv.classList.remove('d-none');
                    warningText.textContent = `Changing role from "${originalRole}" to "${role}" will update user permissions and may affect their system access.`;
                } else {
                    warningDiv.classList.add('d-none');
                }
            }

            // Add fade animation
            document.head.insertAdjacentHTML('beforeend', `
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -10px rgba(0,0,0,0.1) !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
        }
    </style>
`);










            // ── 1. ADD this new function ─────────────────────────────────
            // Global variable to store all equipment data
            let allEquipmentData = [];

            function loadEquipmentWithUsage() {
                const tableBody = document.getElementById('equipmentTableBody');
                if (!tableBody) return;

                tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-success me-2" role="status"
                     style="width:1.5rem;height:1.5rem;"></div>
                <span style="color:#166534;font-weight:600;">
                    Loading equipment data...
                </span>
            </td>
        </tr>`;

                fetch('../controllers/get_equipment_usage.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Equipment data received:', data);

                        if (data.success && data.equipment) {
                            allEquipmentData = data.equipment;
                            // Display equipment directly instead of filtering first
                            displayEquipmentTable(allEquipmentData);

                            const filterSelect = document.getElementById('statusFilterequipment');
                            if (filterSelect) filterSelect.value = 'all';
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
                        ❌ Connection error
                    </td>
                </tr>`;
                    });
            }

            function filterAndDisplayEquipment() {
                // Add null checks for filter elements
                const filterSelect = document.getElementById('statusFilterequipment');
                const searchInput = document.getElementById('equipmentSearch');

                // If elements don't exist, display all data
                const filterValue = filterSelect ? filterSelect.value : 'all';
                const searchTerm = searchInput ? (searchInput.value.toLowerCase().trim()) : '';

                if (!allEquipmentData || allEquipmentData.length === 0) {
                    return;
                }

                let filteredData = [...allEquipmentData];

                // Apply status filter
                if (filterValue === 'maintenance') {
                    filteredData = filteredData.filter(item => item.maintenance > 0);
                } else if (filterValue === 'broken') {
                    filteredData = filteredData.filter(item => item.broken > 0);
                }

                // Apply search filter
                if (searchTerm !== '') {
                    filteredData = filteredData.filter(item => {
                        const nameMatch = item.name && item.name.toLowerCase().includes(searchTerm);
                        const codeMatch = item.code && item.code.toLowerCase().includes(searchTerm);
                        return nameMatch || codeMatch;
                    });
                }

                displayEquipmentTable(filteredData);
            }

            // Function to filter equipment by status
            // Called by dropdown
            function searchEquipmentStatus() {
                filterAndDisplayEquipment();
            }
            // Optional: Add a function to show filter status
            function updateFilterStatus(filter, visible, total) {
                const filterSelect = document.getElementById('statusFilterequipment');
                const filterText = filterSelect.options[filterSelect.selectedIndex].text;

                // You could add a small status indicator near the table
                let statusDiv = document.getElementById('filterStatus');
                if (!statusDiv) {
                    statusDiv = document.createElement('div');
                    statusDiv.id = 'filterStatus';
                    statusDiv.style.marginTop = '10px';
                    statusDiv.style.fontSize = '0.9rem';
                    statusDiv.style.color = '#666';

                    // Insert after the filter dropdown or before the table
                    const tableContainer = document.querySelector('.table-responsive');
                    tableContainer.parentNode.insertBefore(statusDiv, tableContainer);
                }

                if (filter === 'all' && visible === total) {
                    statusDiv.innerHTML = `📊 Showing all <strong>${total}</strong> equipment items`;
                } else {
                    statusDiv.innerHTML = `🔍 <strong>${filterText}</strong>: Showing <strong>${visible}</strong> of <strong>${total}</strong> items`;
                }
            }

            // Update your existing searchEquipment function to work with the filter
            // Update your existing searchEquipment function
            function searchEquipment() {
                filterAndDisplayEquipment();
            }

            // ========== LOGBOOK SECTION FUNCTIONS ==========
            function loadLogbooks() {
                const tableBody = document.getElementById('logbookTableBody');
                if (!tableBody) return;

                // Clear search input when reloading
                const searchInput = document.getElementById('logbookSearchInput');
                if (searchInput) {
                    searchInput.value = '';
                }

                tableBody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center py-4">
                <div class="spinner-border text-success me-2" role="status"
                     style="width:1.5rem;height:1.5rem;"></div>
                <span style="color:#166534;font-weight:600;">
                    Loading logbook data...
                </span>
            </td>
        </tr>`;

                fetch('../controllers/get_logbooks_for_hod.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Logbook data received:', data);

                        if (data.success && data.logbooks && data.logbooks.length > 0) {
                            let rowNumber = 1;
                            const rows = data.logbooks.map(logbook => {
                                // Format datetime - handle various date formats
                                let dateTimeStr = 'N/A';
                                if (logbook.datetime_submitted) {
                                    const date = new Date(logbook.datetime_submitted);
                                    dateTimeStr = !isNaN(date) ? date.toLocaleString() : logbook.datetime_submitted;
                                } else if (logbook.creation_datetime) {
                                    const date = new Date(logbook.creation_datetime);
                                    dateTimeStr = !isNaN(date) ? date.toLocaleString() : logbook.creation_datetime;
                                } else if (logbook.datetime) {
                                    const date = new Date(logbook.datetime);
                                    dateTimeStr = !isNaN(date) ? date.toLocaleString() : logbook.datetime;
                                }

                                return `
                    <tr>
                        <td>${rowNumber++}</td>
                        <td>${logbook.reservation_code || 'N/A'}</td>
                        <td><small class="text-muted">${dateTimeStr}</small></td>
                        <td>
                            <button class="btn btn-primary btn-sm" 
                                    onclick="viewLogbookDetailsHODD(${logbook.id})">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    </tr>`;
                            }).join('');
                            tableBody.innerHTML = rows;
                        } else {
                            tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            📚 No logbooks found
                        </td>
                    </tr>`;
                        }
                    })
                    .catch(error => {
                        console.error('Logbook load error:', error);
                        tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-4 text-danger">
                        ❌ Failed to load logbooks
                    </td>
                </tr>`;
                    });
            }

            // Filter logbook table by reservation code
            function filterLogbookTable() {
                const searchInput = document.getElementById('logbookSearchInput');
                const filterValue = searchInput ? searchInput.value.toUpperCase().trim() : '';
                const tableRows = document.querySelectorAll('#logbookTableBody tr');
                let visibleCount = 0;

                tableRows.forEach(row => {
                    // Skip loading and "no data" rows
                    if (row.innerHTML.includes('Loading') ||
                        row.innerHTML.includes('No logbooks found') ||
                        row.innerHTML.includes('Failed to load')) {
                        return;
                    }

                    // Get the reservation ID from the second column (index 1)
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 1) {
                        const reservationCode = cells[1].textContent.toUpperCase().trim();

                        // Show row if filter is empty or matches reservation code
                        if (filterValue === '' || reservationCode.includes(filterValue)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });

                // Show "no results" message if no rows match
                if (visibleCount === 0 && filterValue !== '') {
                    const tableBody = document.getElementById('logbookTableBody');
                    if (tableBody && !tableBody.querySelector('.no-results-row')) {
                        // Check if there are any visible data rows (not counting no-results message)
                        const hasData = tableBody.querySelectorAll('tr[style="display: ""]:not(.no-results-row)').length > 0;
                        if (!hasData) {
                            const noResultsRow = document.createElement('tr');
                            noResultsRow.className = 'no-results-row';
                            noResultsRow.innerHTML = `
                                <td colspan="4" class="text-center py-4 text-muted">
                                    🔍 No logbooks found matching "${filterValue}"
                                </td>
                            `;
                            tableBody.appendChild(noResultsRow);
                        }
                    }
                } else {
                    // Remove no-results message if it exists
                    const noResultsRow = document.querySelector('#logbookTableBody .no-results-row');
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }
                }
            }



























            // ========== CONFIRMATION MODAL =========
            function showConfirmationModal(title, message, onConfirm, confirmText = 'Confirm', isDanger = false) {
                const modalId = 'confirmationModal';
                let modal = document.getElementById(modalId);
                if (modal) modal.remove();

                modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = modalId;
                modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-white" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <h5 class="modal-title">
                        <i class="bi bi-question-circle me-2"></i>${title}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-${isDanger ? 'danger' : 'success'}" id="confirmActionBtn">
                        <i class="bi bi-${isDanger ? 'exclamation-triangle' : 'check-circle'} me-1"></i>${confirmText}
                    </button>
                </div>
            </div>
        </div>`;

                document.body.appendChild(modal);
                const modalInstance = new bootstrap.Modal(modal);

                document.getElementById('confirmActionBtn').addEventListener('click', () => {
                    modalInstance.hide();
                    onConfirm();
                });

                modal.addEventListener('hidden.bs.modal', () => modal.remove());
                modalInstance.show();
            }

            // ========== LOGBOOK DETAIL FUNCTIONS ==========
            function viewLogbookDetailsHOD(logbookId) {
                console.log('Viewing logbook:', logbookId);

                const existing = document.getElementById('logbookDetailModal');
                if (existing) existing.remove();

                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'logbookDetailModal';
                modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header text-white" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <h5 class="modal-title">
                        <i class="bi bi-journal-check me-2"></i>Logbook Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="logbookDetailBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status" style="width:2rem;height:2rem;"></div>
                        <p class="mt-3 text-muted">Loading details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="rejectBtnHOD" onclick="rejectLogbookHOD(${logbookId})" disabled>
                        <i class="bi bi-x-circle me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-success" id="approveBtnHOD" onclick="approveLogbookHOD(${logbookId})" disabled>
                        <i class="bi bi-check-circle me-1"></i>Approve
                    </button>
                </div>
            </div>
        </div>`;

                document.body.appendChild(modal);
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
                modal.addEventListener('hidden.bs.modal', () => modal.remove());

                fetch(`../controllers/get_logbook_details.php?id=${logbookId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success || !data.logbook) {
                            document.getElementById('logbookDetailBody').innerHTML = `
                    <div class="alert alert-danger m-3">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        ${data.message || 'Failed to load logbook details'}
                    </div>`;
                            return;
                        }

                        const lb = data.logbook;
                        const imgPaths = lb.evidence_images || [];

                        const imagesHtml = imgPaths.length > 0 ?
                            `<div style="display:flex;flex-wrap:nowrap;overflow-x:auto;gap:12px;padding:12px;background:#f9fafb;border-radius:8px;">
                    ${imgPaths.map((p, idx) => {
                        const fileName = p.split('/').pop();
                        return `<div style="flex-shrink:0;position:relative;">
                            <img src="../${p}" class="rounded border"
                                 style="height:150px;width:150px;object-fit:cover;cursor:pointer;"
                                 onclick="window.open(this.src,'_blank')"
                                 onerror="this.style.display='none'"
                                 alt="Evidence photo ${idx + 1}">
                            <a href="../${p}" download="${fileName}"
                               class="btn btn-sm btn-outline-primary"
                               style="position:absolute;bottom:8px;right:8px;padding:4px 8px;">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>`;
                    }).join('')}
                  </div>` :
                            '<p class="text-muted fst-italic"><i class="bi bi-info-circle me-1"></i>No photos submitted</p>';

                        document.getElementById('logbookDetailBody').innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-person me-2"></i><strong>Student Info</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted fw-semibold">Name</td><td>${lb.student_name || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">University ID</td><td>${lb.university_id || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Email</td><td>${lb.student_email || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Mobile</td><td>${lb.student_mobile || '—'}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-calendar me-2"></i><strong>Reservation Info</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted fw-semibold">Reservation ID</td><td>${lb.reservation_code || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Lab Location</td><td>${lb.lab_location || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Date</td><td>${lb.submitted_date || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Duration</td><td>${lb.continue_days || 1} day(s)</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-check2 me-2"></i><strong>Approval Status</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted fw-semibold">Supervisor</td>
                                        <td>${lb.sup_is_approved == 1
                                            ? '<span class="badge bg-success">Approved</span>'
                                            : lb.sup_is_approved == 0
                                            ? '<span class="badge bg-danger">Rejected</span>'
                                            : '<span class="badge bg-warning text-dark">Pending</span>'}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Tech Officer</td>
                                        <td>${lb.tech_is_approved == 1
                                            ? '<span class="badge bg-success">Approved</span>'
                                            : lb.tech_is_approved == 0
                                            ? '<span class="badge bg-danger">Rejected</span>'
                                            : '<span class="badge bg-warning text-dark">Pending</span>'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-person-check me-2"></i><strong>Reviewers</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted fw-semibold">Supervisor</td><td>${lb.supervisor_name || 'Not assigned'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Tech Officer</td><td>${lb.tech_officer_name || 'Not Reviewed'}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-chat-left me-2"></i><strong>Comments</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <p class="mb-0">${lb.any_comment || '<span class="text-muted fst-italic">No comments provided</span>'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-images me-2"></i><strong>Evidence Photos${imgPaths.length > 0 ? ` (${imgPaths.length})` : ''}</strong></h6>
                            </div>
                            <div class="card-body p-3">${imagesHtml}</div>
                        </div>
                    </div>
                </div>`;

                        const approveBtnHOD = document.getElementById('approveBtnHOD');
                        const rejectBtnHOD = document.getElementById('rejectBtnHOD');

                        if (true) {
                            approveBtnHOD.disabled = false;
                            rejectBtnHOD.disabled = false;
                        } else {
                            approveBtnHOD.disabled = true;
                            rejectBtnHOD.disabled = true;
                            approveBtnHOD.title = 'Cannot approve until Supervisor and Technical Officer approve';
                            rejectBtnHOD.title = 'Cannot reject until Supervisor and Technical Officer approve';
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        document.getElementById('logbookDetailBody').innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Error loading logbook details
                </div>`;
                    });
            }
            // Unified function for HOD to view logbook details with approve/reject
            function viewLogbookDetailsHODD(logbookId) {
                console.log('Viewing logbook:', logbookId);

                const existing = document.getElementById('logbookDetailModal');
                if (existing) existing.remove();

                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'logbookDetailModal';
                modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header text-white" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <h5 class="modal-title">
                        <i class="bi bi-journal-check me-2"></i>Logbook Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="logbookDetailBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status" style="width:2rem;height:2rem;"></div>
                        <p class="mt-3 text-muted">Loading details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger d-none" id="rejectBtnHOD" onclick="rejectLogbookHOD(${logbookId})" disabled>
                        <i class="bi bi-x-circle me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-success d-none" id="approveBtnHOD" onclick="approveLogbookHOD(${logbookId})" disabled>
                        <i class="bi bi-check-circle me-1"></i>Approve
                    </button>
                </div>
            </div>
        </div>`;

                document.body.appendChild(modal);
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
                modal.addEventListener('hidden.bs.modal', () => modal.remove());

                fetch(`../controllers/get_logbook_details.php?id=${logbookId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success || !data.logbook) {
                            document.getElementById('logbookDetailBody').innerHTML = `
                    <div class="alert alert-danger m-3">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        ${data.message || 'Failed to load logbook details'}
                    </div>`;
                            return;
                        }

                        const lb = data.logbook;
                        const imgPaths = lb.evidence_images || [];

                        const imagesHtml = imgPaths.length > 0 ?
                            `<div style="display:flex;flex-wrap:nowrap;overflow-x:auto;gap:12px;padding:12px;background:#f9fafb;border-radius:8px;">
                    ${imgPaths.map((p, idx) => {
                        const fileName = p.split('/').pop();
                        return `<div style="flex-shrink:0;position:relative;">
                            <img src="../${p}" class="rounded border"
                                 style="height:150px;width:150px;object-fit:cover;cursor:pointer;"
                                 onclick="window.open(this.src,'_blank')"
                                 onerror="this.style.display='none'"
                                 alt="Evidence photo ${idx + 1}">
                            <a href="../${p}" download="${fileName}"
                               class="btn btn-sm btn-outline-primary"
                               style="position:absolute;bottom:8px;right:8px;padding:4px 8px;">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>`;
                    }).join('')}
                  </div>` :
                            '<p class="text-muted fst-italic"><i class="bi bi-info-circle me-1"></i>No photos submitted</p>';

                        document.getElementById('logbookDetailBody').innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-person me-2"></i><strong>Student Info</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted fw-semibold">Name</td><td>${lb.student_name || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">University ID</td><td>${lb.university_id || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Email</td><td>${lb.student_email || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Mobile</td><td>${lb.student_mobile || '—'}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-calendar me-2"></i><strong>Reservation Info</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted fw-semibold">Reservation ID</td><td>${lb.reservation_code || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Lab Location</td><td>${lb.lab_location || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Date</td><td>${lb.submitted_date || '—'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Duration</td><td>${lb.continue_days || 1} day(s)</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-check2 me-2"></i><strong>Approval Status</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                 <tr>
                                        <td class="text-muted fw-semibold">HOD</td>
                                        <td>${lb.hod_is_approved == 1
                                            ? '<span class="badge bg-success">Approved</span>'
                                            : lb.hod_is_approved == 0
                                            ? '<span class="badge bg-danger">Rejected</span>'
                                            : '<span class="badge bg-warning text-dark">Pending</span>'}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Supervisor</td>
                                        <td>${lb.sup_is_approved == 1
                                            ? '<span class="badge bg-success">Approved</span>'
                                            : lb.sup_is_approved == 0
                                            ? '<span class="badge bg-danger">Rejected</span>'
                                            : '<span class="badge bg-warning text-dark">Pending</span>'}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Tech Officer</td>
                                        <td>${lb.tech_is_approved == 1
                                            ? '<span class="badge bg-success">Approved</span>'
                                            : lb.tech_is_approved == 0
                                            ? '<span class="badge bg-danger">Rejected</span>'
                                            : '<span class="badge bg-warning text-dark">Pending</span>'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-person-check me-2"></i><strong>Reviewers</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted fw-semibold">Supervisor</td><td>${lb.supervisor_name || 'Not assigned'}</td></tr>
                                    <tr><td class="text-muted fw-semibold">Tech Officer</td><td>${lb.tech_officer_name || 'Not Reviewed'}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-chat-left me-2"></i><strong>Comments</strong></h6>
                            </div>
                            <div class="card-body p-3">
                                <p class="mb-0">${lb.any_comment || '<span class="text-muted fst-italic">No comments provided</span>'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-images me-2"></i><strong>Evidence Photos${imgPaths.length > 0 ? ` (${imgPaths.length})` : ''}</strong></h6>
                            </div>
                            <div class="card-body p-3">${imagesHtml}</div>
                        </div>
                    </div>
                </div>`;

                        const approveBtnHOD = document.getElementById('approveBtnHOD');
                        const rejectBtnHOD = document.getElementById('rejectBtnHOD');

                        if (true) {
                            approveBtnHOD.disabled = false;
                            rejectBtnHOD.disabled = false;
                        } else {
                            approveBtnHOD.disabled = true;
                            rejectBtnHOD.disabled = true;
                            approveBtnHOD.title = 'Cannot approve until Supervisor and Technical Officer approve';
                            rejectBtnHOD.title = 'Cannot reject until Supervisor and Technical Officer approve';
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        document.getElementById('logbookDetailBody').innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Error loading logbook details
                </div>`;
                    });
            }

            // HOD Approve logbook
            function approveLogbookHOD(logbookId) {
                showConfirmationModal(
                    'Approve Logbook',
                    'Are you sure you want to approve this logbook? This action cannot be undone.',
                    () => {
                        const data = {
                            logbook_id: logbookId,
                            action: 'approve'
                        };

                        fetch('../controllers/approve_logbook_hod.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(data)
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    showToast('Logbook approved successfully!', 'success');
                                    setTimeout(() => location.reload(), 1500);
                                } else {
                                    showToast(data.message || 'Failed to approve', 'error');
                                }
                            })
                            .catch(err => {
                                console.error('Error:', err);
                                showToast('Network error. Please try again.', 'error');
                            });
                    },
                    'Approve',
                    false
                );
            }

            // HOD Reject logbook with reason modal
            function rejectLogbookHOD(logbookId) {
                const modalId = 'rejectReasonModal';
                let modal = document.getElementById(modalId);
                if (modal) modal.remove();

                modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = modalId;
                modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-white" style="background:linear-gradient(135deg,#ef4444,#dc2626);">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>Reject Logbook
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3"><strong>Please provide a reason for rejection:</strong></p>
                    <textarea id="rejectionReason" class="form-control" placeholder="Enter rejection reason..." rows="4" style="resize: vertical;"></textarea>
                    <small class="text-muted d-block mt-2">This reason will be visible to the student.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="rejectConfirmBtn">
                        <i class="bi bi-x-circle me-1"></i>Reject
                    </button>
                </div>
            </div>
        </div>`;

                document.body.appendChild(modal);
                const modalInstance = new bootstrap.Modal(modal);

                document.getElementById('rejectConfirmBtn').addEventListener('click', () => {
                    const reason = document.getElementById('rejectionReason').value.trim();

                    if (!reason) {
                        showToast('Please enter a rejection reason.', 'warning');
                        return;
                    }

                    modalInstance.hide();

                    const data = {
                        logbook_id: logbookId,
                        action: 'reject',
                        reason: reason
                    };

                    fetch('../controllers/approve_logbook_hod.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Logbook rejected successfully!', 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showToast(data.message || 'Failed to reject', 'error');
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            showToast('Network error. Please try again.', 'error');
                        });
                });

                modal.addEventListener('hidden.bs.modal', () => modal.remove());
                modalInstance.show();
            }






























            // ========== REQUEST SECTION FUNCTIONS ==========
            // ========== REQUEST SECTION FUNCTIONS (for user approvals) ==========

            let currentRequestType = 'technical';
            let currentUserId = null; // ID of the user being approved/rejected
            let allRequests = []; // raw data from server

            // Called from showSection('activity')
            function initRequestSection() {
                currentRequestType = 'technical';
                // Ensure correct tab is highlighted
                const tabs = document.querySelectorAll('.request-tab');
                tabs.forEach(t => t.classList.remove('active'));
                if (tabs[0]) tabs[0].classList.add('active');

                // Load requests
                loadRequests('technical');
            }

            // Fetch data from PHP
            function loadRequests(type) {
                const tableBody = document.getElementById('requestListBody');
                if (!tableBody) return;

                // Set column headers with Image column
                const head = document.getElementById('reqTableHead');
                if (head) {
                    head.innerHTML = `
            <th>Image</th>
            <th>University ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Joined Date</th>
            <th>Action</th>
        `;
                }

                tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-success me-2"></div>
                Loading requests...
            </td>
        </tr>
    `;

                fetch(`../controllers/get_requests.php?type=${type}`)
                    .then(r => r.json())
                    .then(res => {
                        console.log('Requests received:', res); // For debugging

                        if (res.success) {
                            allRequests = res.data || [];

                            // Update the tab badge
                            const badgeId = (type === 'technical') ? 'technicalRequestCount' : 'supervisorRequestCount';
                            const badgeEl = document.getElementById(badgeId);
                            if (badgeEl) {
                                badgeEl.textContent = allRequests.length;

                                // Add or remove zero-count class based on count
                                if (allRequests.length === 0) {
                                    badgeEl.classList.add('zero-count');
                                } else {
                                    badgeEl.classList.remove('zero-count');
                                }
                            }

                            // Render the table
                            renderRequestTable(allRequests);

                            // Also update the main notification badge
                            fetchBothBadgeCounts();
                        } else {
                            tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            ❌ ${res.message || 'Failed to load requests'}
                        </td>
                    </tr>
                `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading requests:', error);
                        tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        ❌ Failed to load requests. Check server connection.
                    </td>
                </tr>
            `;
                    });
            }

            // Tab switch
            function switchRequestType(type) {
                currentRequestType = type;
                const tabs = document.querySelectorAll('.request-tab');
                tabs.forEach(t => t.classList.remove('active'));
                tabs[type === 'technical' ? 0 : 1].classList.add('active');
                loadRequests(type);
            }

            // Render table rows
            function renderRequestTable(rows) {
                const tbody = document.getElementById('requestListBody');
                if (!tbody) return;

                tbody.innerHTML = '';

                if (rows.length === 0) {
                    tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    No pending requests found
                </td>
            </tr>
        `;
                    return;
                }

                rows.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-user-id', item.id);

                    // Format date
                    const joinDate = item.join_datetime ? new Date(item.join_datetime).toLocaleDateString() : '—';

                    // Determine image source
                    const imageSrc = item.image || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(item.full_name || 'User') + '&background=22c55e&color=fff&size=50';

                    tr.innerHTML = `
            <td>
                <img src="${imageSrc}"
                     style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #22c55e;"
                     onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(item.full_name || 'User')}&background=22c55e&color=fff&size=40'"
                     alt="${item.full_name}">
            </td>
            <td><strong>${item.university_id || '—'}</strong></td>
            <td>${item.full_name || '—'}</td>
            <td>${item.email || '—'}</td>
            <td>${joinDate}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-view" onclick="openRequestDetail(${item.id})" title="View Details">
                        <i class="bi bi-eye"></i> View
                    </button>
                </div>
            </td>
        `;
                    tbody.appendChild(tr);
                });
            }

            // Open detail modal for user approval
            // Open detail modal for user approval with image
            function openRequestDetail(userId) {
                const item = allRequests.find(r => r.id === userId);
                if (!item) return;

                currentUserId = userId;

                // Determine image source
                const imageSrc = item.image || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(item.full_name || 'User') + '&background=22c55e&color=fff&size=100';

                document.getElementById('reqModalContent').innerHTML = `
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="${imageSrc}"
                 style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:3px solid #22c55e; margin-bottom:10px;"
                 onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(item.full_name || 'User')}&background=22c55e&color=fff&size=100'"
                 alt="${item.full_name}">
        </div>
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0; width:120px;">
                    University ID:
                </td>
                <td style="padding:9px 0;">
                    <strong>${item.university_id || '—'}</strong>
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0; border-top:1px solid #f0f0f0;">
                    Name:
                </td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    ${item.full_name || '—'}
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0; border-top:1px solid #f0f0f0;">
                    Email:
                </td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    ${item.email || '—'}
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0; border-top:1px solid #f0f0f0;">
                    Mobile:
                </td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    ${item.mobile || '—'}
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0; border-top:1px solid #f0f0f0;">
                    Request Type:
                </td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    <span class="badge bg-info">${currentRequestType === 'technical' ? 'Technical Officer' : 'Supervisor'}</span>
                </td>
            </tr>
            <tr>
                <td style="color:#166534; font-weight:600; padding:9px 0; border-top:1px solid #f0f0f0;">
                    Joined Date:
                </td>
                <td style="padding:9px 0; border-top:1px solid #f0f0f0;">
                    ${item.join_datetime ? new Date(item.join_datetime).toLocaleString() : '—'}
                </td>
            </tr>
        </table>
    `;

                document.getElementById('reqDetailModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function closeReqModal() {
                document.getElementById('reqDetailModal').style.display = 'none';
                document.body.style.overflow = '';
            }

            // Close on backdrop click
            document.getElementById('reqDetailModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeReqModal();
            });

            // Reject sub-modal
            function openRejectBox() {
                document.getElementById('reqRejectText').value = '';
                document.getElementById('reqRejectModal').style.display = 'flex';
            }

            function closeRejectBox() {
                document.getElementById('reqRejectModal').style.display = 'none';
            }

            // Submit approve / reject for user account requests
            function submitReqAction(action) {
                if (!currentUserId) return;

                const reason = action === 'reject' ?
                    (document.getElementById('reqRejectText')?.value.trim() || '') :
                    '';

                if (action === 'reject' && !reason) {
                    alert('Please enter a rejection reason.');
                    return;
                }

                // Disable buttons
                ['reqApproveBtn', 'reqRejectBtn'].forEach(id => {
                    const btn = document.getElementById(id);
                    if (btn) btn.disabled = true;
                });

                const fd = new FormData();
                fd.append('user_id', currentUserId);
                fd.append('action', action);
                if (reason) fd.append('reason', reason);

                fetch('../controllers/process_user_action.php', {
                        method: 'POST',
                        body: fd
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            closeRejectBox();
                            closeReqModal();
                            showSuccess(`User request ${action === 'approve' ? 'approved' : 'rejected'} successfully!`);

                            // Refresh the current tab
                            loadRequests(currentRequestType);

                            // Update counts
                            fetchBothBadgeCounts();
                        } else {
                            showError(res.message || 'Action failed.');
                        }
                    })
                    .catch(() => showError('Network error. Please try again.'))
                    .finally(() => {
                        ['reqApproveBtn', 'reqRejectBtn'].forEach(id => {
                            const btn = document.getElementById(id);
                            if (btn) btn.disabled = false;
                        });
                    });
            }






            function viewMaintenanceItems() {
                showSection('equipment');
                setTimeout(function() {
                    const filterSelect = document.getElementById('statusFilterequipment');
                    if (filterSelect) {
                        filterSelect.value = 'maintenance';
                        filterAndDisplayEquipment();
                    }
                    const searchInput = document.getElementById('equipmentSearch');
                    if (searchInput) searchInput.value = '';
                }, 300);
            }






            function displayEquipmentTable(equipment) {
                const tableBody = document.getElementById('equipmentTableBody');
                if (!tableBody) return;

                tableBody.innerHTML = '';

                if (!equipment || equipment.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No equipment found</td></tr>';
                    return;
                }

                equipment.sort((a, b) => b.usage - a.usage);

                equipment.forEach(item => {
                    const code = item.code || 'N/A';
                    const name = item.name || 'Unknown';
                    const image = item.image || 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';
                    const qty = item.qty || 0;
                    const maintenance = item.maintenance || 0;
                    const broken = item.broken || 0;
                    const location = (item.location && item.location.trim() !== '') ? item.location.trim() : null;
                    const usage = Math.round(parseFloat(item.usage) || 0);

                    let barColor = '#22c55e';
                    if (usage < 30) barColor = '#ef4444';
                    else if (usage < 60) barColor = '#f59e0b';

                    const row = document.createElement('tr');
                    row.setAttribute('data-equipment-id', code);
                    row.setAttribute('data-equipment-id-numeric', item.id || '');

                    row.innerHTML = `
            <td>
                <img src="${image}"
                     style="width:50px;height:50px;object-fit:contain;"
                     onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'"
                     alt="${name}">
            </td>
            <td style="font-size: 0.9rem;">${name}</td>
            <td style="width: 80px; padding: 8px 4px; text-align: center;">
                <span style="font-weight: 600;">${qty}</span>
            </td>
            <td style="width: 120px; padding: 8px 4px;">
                ${maintenance > 0 
                    ? `<span class="badge bg-warning">${maintenance}</span>` 
                    : '<span class="text-muted">------</span>'}
            </td>
            <td style="width: 100px; padding: 8px 4px;">
                ${broken > 0 
                    ? `<span class="badge bg-danger">${broken}</span>` 
                    : '<span class="text-muted">------</span>'}
            </td>
            <td style="width: 150px; padding: 8px 4px;">
                ${!location
                    ? `<span class="badge bg-secondary" style="background-color: #9ca3af !important;">
                        <i class="bi bi-geo-alt"></i> Not Assigned
                       </span>`
                    : `<span class="badge bg-info" style="background-color: #0ea5e9 !important;">
                        <i class="bi bi-geo-alt"></i> ${location}
                       </span>`}
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:100px;height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;">
                        <div style="width:${usage}%;height:8px;background:${barColor};border-radius:4px;"></div>
                    </div>
                    <span style="font-weight:600;color:${barColor};min-width:45px;">${usage}%</span>
                </div>
            </td>
            <td style="width: 140px; padding: 6px 2px;">
                <div class="action-buttons">
                    <button class="btn-view" onclick="viewEquipmentByCode('${code}')" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-edit" onclick="editEquipment('${code}')" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn-remove" onclick="removeEquipment('${code}')" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
                    tableBody.appendChild(row);
                });
            }


            function viewEquipmentByCode(code) {
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
                        const addedDate = eq.added_datetime ? new Date(eq.addedDate).toLocaleDateString() : '—';

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
                            <tr><th class="text-muted fw-normal" style="width:160px">Date Added</th><td>${eq.addedDate}</td></tr>
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

            function viewEquipment(equipment) {
                const detailsContent = document.getElementById('equipmentDetailsContent');

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
                    <tr><th>Purchase Date:</th><td>${equipment.purchaseDate}</td></tr>
                    <tr><th>Last Maintenance:</th><td>${equipment.lastMaintenance}</td></tr>
                    <tr>
                        <th>Next Maintenance:</th>
                        <td>
                            ${equipment.nextMaintenance}
                            ${isOverdue ? '<span class="badge bg-danger ms-2">⚠️ Overdue</span>' : ''}
                        </td>
                    </tr>
                    <tr><th>Availability:</th><td><span class="badge" style="background: #22c55e;">${equipment.available}/${equipment.total} units</span></td></tr>
                    <tr><th>Broken:</th><td><span class="badge bg-warning">${equipment.broken_qty}</span></td></tr>
                    <tr><th>In Repair:</th><td><span class="badge bg-info">${equipment.repair_qty}</span></td></tr>
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

                const eqModal = document.getElementById('equipmentDetailsModal');
                const eqExisting = bootstrap.Modal.getInstance(eqModal);
                if (eqExisting) eqExisting.dispose();
                new bootstrap.Modal(eqModal).show();
            }

            // Save new equipment
            function saveNewEquipment() {
                // Get form values
                const code = document.getElementById('eqCode').value.trim();
                const name = document.getElementById('eqName').value.trim();
                const qty = document.getElementById('eqQty').value;
                const simultaneous_users = document.getElementById('eqSimultaneousUsers').value || 1;
                const sterilization = document.getElementById('eqSterilization').value;
                const reservation = document.getElementById('eqReservation').value;
                const description = document.getElementById('eqDescription').value.trim();
                const imageFile = document.getElementById('eqImage').files[0];

                // Validate
                let isValid = true;

                if (!code) {
                    document.getElementById('eqCode').classList.add('is-invalid');
                    document.getElementById('eqCodeError').textContent = 'Equipment code is required';
                    isValid = false;
                } else {
                    document.getElementById('eqCode').classList.remove('is-invalid');
                }

                if (!name) {
                    document.getElementById('eqName').classList.add('is-invalid');
                    document.getElementById('eqNameError').textContent = 'Equipment name is required';
                    isValid = false;
                } else {
                    document.getElementById('eqName').classList.remove('is-invalid');
                }

                if (!qty || qty < 1) {
                    document.getElementById('eqQty').classList.add('is-invalid');
                    document.getElementById('eqQtyError').textContent = 'Quantity must be at least 1';
                    isValid = false;
                } else {
                    document.getElementById('eqQty').classList.remove('is-invalid');
                }

                if (!isValid) return;

                // Show loading state
                const saveBtn = document.querySelector('#addEquipmentModal .btn-success');
                const originalText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

                // Create FormData
                const formData = new FormData();
                formData.append('code', code);
                formData.append('name', name);
                formData.append('qty', qty);
                formData.append('simultaneous_users', simultaneous_users);
                formData.append('sterilization_required', sterilization);
                formData.append('reservation_required', reservation);
                formData.append('description', description);
                if (imageFile) {
                    formData.append('image', imageFile);
                }

                // Send AJAX request
                fetch('../controllers/add_equipment.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;

                        if (data.success) {
                            // Close modal
                            bootstrap.Modal.getInstance(document.getElementById('addEquipmentModal')).hide();

                            // Show success message
                            showSuccess('Equipment added successfully!');

                            // Reload equipment data
                            loadEquipmentWithUsage();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to add equipment'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;
                        alert('Network error. Please try again.');
                    });
            }

            // function editEquipment(code) {
            //     alert('Edit equipment: ' + code);
            // }

            function removeEquipment(code) {
                if (!confirm(`Are you sure you want to remove equipment "${code}"?\n\nThis action cannot be undone.`)) {
                    return;
                }

                // Find button by traversing the DOM — no event needed
                const allBtns = document.querySelectorAll('.btn-remove');
                let btn = null;
                allBtns.forEach(b => {
                    if (b.getAttribute('onclick') && b.getAttribute('onclick').includes(code)) {
                        btn = b;
                    }
                });

                const originalHTML = btn ? btn.innerHTML : '<i class="bi bi-trash"></i>';

                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                }

                fetch('../controllers/delete_equipment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            code: code
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        console.log('Delete response:', data);
                        if (data.success) {
                            showSuccess(`Equipment "${code}" removed successfully!`);
                            loadEquipmentWithUsage();
                        } else {
                            showError(data.message || 'Failed to remove equipment.');
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = originalHTML;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        showError('Network error. Please try again.');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = originalHTML;
                        }
                    });
            }
            // ── Build table rows ─────────────────────────────────────────────
            // function renderRequestTable(rows) {
            //     const tbody = document.getElementById('requestListBody');
            //     if (!tbody) return;
            //     tbody.innerHTML = '';

            //     if (rows.length === 0) {
            //         tbody.innerHTML =
            //             `<tr><td colspan="5" class="text-center py-4 text-muted">
            //     No pending requests found
            //  </td></tr>`;
            //         return;
            //     }

            //     rows.forEach(item => {

            //         const statusPill = `
            // <span style="display:inline-flex; align-items:center; gap:5px;
            //              background:#dcfce7; color:#166534; padding:4px 12px;
            //              border-radius:20px; font-size:0.82rem; font-weight:600;">
            //     <i class="bi bi-check-circle-fill" style="color:#22c55e;"></i>
            //     Pending
            // </span>`;

            //         const tr = document.createElement('tr');
            //         tr.innerHTML = `
            // <td>${item.display_id}</td>
            // <td><strong style="color:#166534;">${item.officer_id}</strong></td>
            // <td>${item.date}</td>
            // <td>${statusPill}</td>
            // <td>
            //     <button class="btn-view" onclick="openReqDetail(${item.id})">
            //         <i class="bi bi-eye"></i> View
            //     </button>
            // </td>`;
            //         tbody.appendChild(tr);
            //     });
            // }

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

            // function closeReqModal() {
            //     document.getElementById('reqDetailModal').style.display = 'none';
            //     document.body.style.overflow = '';
            // }

            // Close on backdrop click
            document.getElementById('reqDetailModal').addEventListener('click', function(e) {
                if (e.target === this) closeReqModal();
            });

            // ── Reject sub-modal ─────────────────────────────────────────────
            // function openRejectBox() {
            //     document.getElementById('reqRejectText').value = '';
            //     document.getElementById('reqRejectModal').style.display = 'flex';
            // }

            // function closeRejectBox() {
            //     document.getElementById('reqRejectModal').style.display = 'none';
            // }

            // ── Submit approve / reject ──────────────────────────────────────
            // function submitReqAction(action) {
            //     if (!currentRequestId) return;

            //     const reason = action === 'reject' ?
            //         (document.getElementById('reqRejectText')?.value.trim() || '') :
            //         '';

            //     if (action === 'reject' && !reason) {
            //         alert('Please enter a rejection reason.');
            //         return;
            //     }


            //     ['reqApproveBtn', 'reqRejectBtn'].forEach(id => {
            //         const btn = document.getElementById(id);
            //         if (btn) btn.disabled = true;
            //     });

            //     const fd = new FormData();
            //     fd.append('reservation_id', currentRequestId);
            //     fd.append('action', action);
            //     if (reason) fd.append('reason', reason);

            //     fetch('../controllers/process_request_action.php', {
            //             method: 'POST',
            //             body: fd
            //         })
            //         .then(r => r.json())
            //         .then(res => {
            //             if (res.success) {
            //                 closeRejectBox();
            //                 closeReqModal();
            //                 showSuccess(`Request ${action === 'approve' ? 'approved ✓' : 'rejected'} successfully!`);

            //                 loadRequests(currentRequestType);
            //                 fetchBothBadgeCounts();
            //             } else {
            //                 showError(res.message || 'Action failed.');
            //             }
            //         })
            //         .catch(() => showError('Network error. Please try again.'))
            //         .finally(() => {
            //             ['reqApproveBtn', 'reqRejectBtn'].forEach(id => {
            //                 const btn = document.getElementById(id);
            //                 if (btn) btn.disabled = false;
            //             });
            //         });
            // }

            // ── Refresh both tab badge counts ───────────────────────────────
            function fetchBothBadgeCounts() {
                Promise.all([
                    fetch('../controllers/get_count.php?type=technical').then(r => r.json()),
                    fetch('../controllers/get_count.php?type=supervisor').then(r => r.json())
                ]).then(([tech, sup]) => {
                    const tCount = tech.success ? tech.count : 0;
                    const sCount = sup.success ? sup.count : 0;

                    // Update tab counts with zero-count class
                    const techBadge = document.getElementById('technicalRequestCount');
                    const supBadge = document.getElementById('supervisorRequestCount');

                    if (techBadge) {
                        techBadge.textContent = tCount;
                        if (tCount === 0) {
                            techBadge.classList.add('zero-count');
                        } else {
                            techBadge.classList.remove('zero-count');
                        }
                    }

                    if (supBadge) {
                        supBadge.textContent = sCount;
                        if (sCount === 0) {
                            supBadge.classList.add('zero-count');
                        } else {
                            supBadge.classList.remove('zero-count');
                        }
                    }

                    // Calculate total pending requests
                    const totalCount = tCount + sCount;

                    // Update the main notification badge (bell icon)
                    const badge = document.getElementById('requestBadge');
                    if (badge) {
                        if (totalCount > 0) {
                            badge.textContent = totalCount;
                            badge.classList.add('visible');
                        } else {
                            badge.textContent = '0';
                            badge.classList.remove('visible');
                        }
                    }

                    // Update the sidebar badge
                    const sidebarBadge = document.getElementById('sidebarRequestBadge');
                    if (sidebarBadge) {
                        if (totalCount > 0) {
                            sidebarBadge.textContent = totalCount;
                            sidebarBadge.classList.remove('zero-count');
                        } else {
                            sidebarBadge.textContent = '0';
                            sidebarBadge.classList.add('zero-count');
                        }
                    }

                    console.log('Request counts updated - Technical:', tCount, 'Supervisor:', sCount, 'Total:', totalCount);
                }).catch(error => {
                    console.error('Error fetching counts:', error);
                });
            }

            // Keep old name working (called from DOMContentLoaded)
            function updateRequestCounts() {
                fetchBothBadgeCounts();
            }

            // Call this function periodically to keep counts updated (every 30 seconds)
            setInterval(fetchBothBadgeCounts, 30000);





            // ========== EQUIPMENT SEARCH FUNCTIONS ==========
            // ========== EQUIPMENT SEARCH FUNCTIONS ==========
            // function searchEquipment() {
            //     // Call the combined filter function
            //     searchEquipmentStatus();
            // }

            // Keep these helper functions for backward compatibility
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
                // This function is now handled by searchEquipmentStatus
                // Keep it for backward compatibility
                const equipmentTable = document.getElementById('equipmentTableBody');
                if (!equipmentTable) return;

                const visibleEquipment = Array.from(equipmentTable.getElementsByTagName('tr'))
                    .filter(row => row.style.display !== 'none').length;
                const totalEquipment = allEquipmentData.length;

                document.getElementById('equipmentCount').textContent =
                    (visibleEquipment > 0 || searchTerm === '') ?
                    '(' + totalEquipment + ')' : '(0)';
            }

            // ========== USER MANAGEMENT FUNCTIONS ==========
            // ========== USER ACTIVATE/DEACTIVATE FUNCTION WITH AJAX ==========
            // ========== USER ACTIVATE/DEACTIVATE FUNCTION WITH AJAX ==========
            function toggleUserStatus(universityId, dbId) {
                console.log('toggleUserStatus called with:', {
                    universityId,
                    dbId
                });

                // Find the row by university ID
                const userRow = document.querySelector(`tr[data-user-id="${universityId}"]`);

                if (!userRow) {
                    console.error('User row not found for ID:', universityId);
                    alert('Error: User row not found');
                    return;
                }

                const currentStatus = userRow.getAttribute('data-status');
                const action = currentStatus === 'active' ? 'deactivate' : 'activate';
                const actionText = action === 'activate' ? 'activate' : 'deactivate';

                if (!confirm(`Are you sure you want to ${actionText} this user?`)) {
                    return;
                }

                // Get the button that was clicked
                const button = event.currentTarget;
                const originalText = button.innerHTML;

                // Show loading state
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';

                // Create FormData with database ID
                const formData = new FormData();
                formData.append('user_id', dbId);
                formData.append('action', action);

                console.log('Sending request with:', {
                    dbId,
                    action
                });

                // Send AJAX request
                fetch('../controllers/activate_process.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response data:', data);
                        button.disabled = false;

                        if (data.success) {
                            // Update UI
                            const newStatus = action === 'activate' ? 'active' : 'inactive';
                            userRow.setAttribute('data-status', newStatus);

                            // Update button appearance
                            if (action === 'activate') {
                                button.className = 'btn-deactivate';
                                button.innerHTML = '<i class="bi bi-person-x"></i> Deactivate';
                                showSuccess('User activated successfully!');
                            } else {
                                button.className = 'btn-activate';
                                button.innerHTML = '<i class="bi bi-person-check"></i> Activate';
                                showSuccess('User deactivated successfully!');
                            }
                        } else {
                            button.innerHTML = originalText;
                            alert('Error: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error in fetch:', error);
                        button.disabled = false;
                        button.innerHTML = originalText;
                        alert('Network error. Please try again.');
                    });
            }
            // Function to clear validation errors
            // Function to clear validation errors
            // Function to clear validation errors
            function clearEditValidationErrors() {
                // Clear all error messages and remove invalid class
                const errorFields = ['FirstName', 'LastName', 'Email', 'Mobile', 'Role', 'Image', 'SupervisorId'];

                errorFields.forEach(field => {
                    const errorDiv = document.getElementById(`edit${field}Error`);
                    const inputField = document.getElementById(`edit${field}`);

                    if (errorDiv) {
                        errorDiv.textContent = '';
                        errorDiv.style.display = 'none';
                    }
                    if (inputField) {
                        inputField.classList.remove('is-invalid');
                    }
                });
            }

            // Function to update user row in table
            function updateUserRowInTable(userId, userData) {
                console.log('Updating row for user:', userId, userData);

                // Try to find the row by university ID first
                let rows = document.querySelectorAll(`tr[data-user-id="${userId}"]`);

                // If not found, try to find by database ID if userData contains it
                if (rows.length === 0 && userData.id) {
                    rows = document.querySelectorAll(`tr[data-db-id="${userData.id}"]`);
                    console.log('Trying to find by database ID:', userData.id, 'Found:', rows.length);
                }

                // If still not found, try to find by university ID from userData
                if (rows.length === 0 && userData.university_id) {
                    rows = document.querySelectorAll(`tr[data-user-id="${userData.university_id}"]`);
                    console.log('Trying to find by university_id from userData:', userData.university_id, 'Found:', rows.length);
                }

                if (rows.length === 0) {
                    console.error('No rows found for user:', userId);
                    return;
                }

                rows.forEach(row => {
                    // Update name (cell index 2)
                    const nameCell = row.cells[2];
                    if (nameCell) {
                        nameCell.innerHTML = `<strong>${userData.first_name} ${userData.last_name}</strong>`;
                    }

                    // Update mobile (cell index 3)
                    const mobileCell = row.cells[3];
                    if (mobileCell) {
                        let mobile = userData.mobile;
                        if (mobile && mobile.length === 10) {
                            mobile = mobile.substr(0, 3) + '-' + mobile.substr(3, 3) + '-' + mobile.substr(6, 4);
                        }
                        mobileCell.textContent = mobile;
                    }

                    // Update email (cell index 4)
                    const emailCell = row.cells[4];
                    if (emailCell) {
                        emailCell.textContent = userData.email;
                    }

                    // Update image (cell index 0)
                    const imageCell = row.cells[0];
                    if (imageCell) {
                        const img = imageCell.querySelector('img');
                        if (img) {
                            img.src = userData.image_url;
                        }
                    }

                    // Update status button
                    const actionCell = row.querySelector('.action-buttons');
                    if (actionCell) {
                        // Find the status button (it might be either activate or deactivate)
                        const deactivateBtn = actionCell.querySelector('.btn-deactivate');
                        const activateBtn = actionCell.querySelector('.btn-activate');

                        if (userData.status == 1) {
                            // User is active - show deactivate button
                            if (deactivateBtn) {
                                deactivateBtn.style.display = 'inline-block';
                            }
                            if (activateBtn) {
                                activateBtn.style.display = 'none';
                            }
                        } else {
                            // User is inactive - show activate button
                            if (deactivateBtn) {
                                deactivateBtn.style.display = 'none';
                            }
                            if (activateBtn) {
                                activateBtn.style.display = 'inline-block';
                            }
                        }
                    }

                    // Update data-status attribute
                    row.setAttribute('data-status', userData.status == 1 ? 'active' : 'inactive');
                });
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




            // Function to show user management and scroll to specific section
            function showUserManagementAndScroll(userType) {
                // First show the user management section
                showSection('userManagement');

                // Small delay to ensure section is visible before scrolling
                setTimeout(function() {
                    let targetElement;

                    // Determine which card to scroll to based on user type
                    switch (userType) {
                        case 'student':
                            targetElement = document.getElementById('studentTableCard');
                            break;
                        case 'supervisor':
                            targetElement = document.getElementById('supervisorTableCard');
                            break;
                        case 'technical':
                            targetElement = document.getElementById('techOfficerTableCard');
                            break;
                        default:
                            return;
                    }

                    // Scroll to the target element with smooth behavior
                    if (targetElement) {
                        // Get the position of the element
                        const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;

                        // Scroll with offset to position it nicely (not at the very top)
                        window.scrollTo({
                            top: elementPosition - 100, // Offset from the top
                            behavior: 'smooth'
                        });

                        // Optional: Add a highlight effect
                        targetElement.style.transition = 'box-shadow 0.5s ease, transform 0.3s ease';
                        targetElement.style.boxShadow = '0 0 0 4px rgba(34, 197, 94, 0.5), 0 20px 40px rgba(0, 0, 0, 0.1)';
                        targetElement.style.transform = 'scale(1.01)';

                        // Remove the highlight after 2 seconds
                        setTimeout(function() {
                            targetElement.style.boxShadow = '';
                            targetElement.style.transform = '';
                        }, 2000);
                    }
                }, 100);
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

            // Global variable to store current editing user ID
            let currentEditUserId = null;
            let allSupervisors = [];

            // Function to load supervisors
            function loadSupervisors() {
                fetch('../controllers/get_supervisors.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            allSupervisors = data.supervisors;
                            const select = document.getElementById('editSupervisorId');
                            select.innerHTML = '<option value="">Select Supervisor</option>';

                            data.supervisors.forEach(sup => {
                                const option = document.createElement('option');
                                option.value = sup.id;
                                option.textContent = `${sup.first_name} ${sup.last_name} (${sup.university_id})`;
                                select.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error loading supervisors:', error));
            }

            // Function to edit user
            // Function to edit user
            function editUser(userId) {
                console.log('========== EDIT USER CALLED ==========');
                console.log('Raw userId parameter:', userId);
                console.log('Type of userId:', typeof userId);

                currentEditUserId = userId;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();

                // Show loading, hide form
                document.getElementById('editUserLoading').style.display = 'block';
                document.getElementById('editUserForm').style.display = 'none';

                // Clear any previous validation errors
                if (typeof clearEditValidationErrors === 'function') {
                    clearEditValidationErrors();
                }

                // Load supervisors for dropdown
                loadSupervisors();

                // Try to find the row to get the database ID
                const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
                let apiUrl = '';

                if (userRow && userRow.dataset.dbId) {
                    // If we have a database ID stored, use it
                    apiUrl = `../controllers/get_user_details.php?user_id=${encodeURIComponent(userRow.dataset.dbId)}`;
                    console.log('Using database ID from row:', userRow.dataset.dbId);
                } else {
                    // Fall back to university_id
                    apiUrl = `../controllers/get_user_details.php?university_id=${encodeURIComponent(userId)}`;
                    console.log('Using university_id:', userId);
                }

                console.log('API URL:', apiUrl);

                // Fetch user details
                fetch(apiUrl)
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('User data response:', data);
                        document.getElementById('editUserLoading').style.display = 'none';

                        if (data.success) {
                            // Store the database ID in the hidden field
                            document.getElementById('editUserId').value = data.user.id || '';

                            // Store original values for comparison
                            document.getElementById('editUserOriginalRole').value = data.user.role_name || '';
                            document.getElementById('editUserOriginalEmail').value = data.user.email || '';

                            // Populate form with user data
                            document.getElementById('editFirstName').value = data.user.first_name || '';
                            document.getElementById('editLastName').value = data.user.last_name || '';
                            document.getElementById('editUniversityId').value = data.user.university_id || '';
                            document.getElementById('editEmail').value = data.user.email || '';
                            document.getElementById('editMobile').value = data.user.mobile || '';
                            document.getElementById('editRole').value = data.user.role_name || '';

                            // Update profile summary
                            document.getElementById('editUserFullName').textContent =
                                (data.user.first_name + ' ' + data.user.last_name).trim() || 'Loading...';
                            document.getElementById('editUserUniversityIdDisplay').textContent =
                                data.user.university_id || '---';
                            document.getElementById('editUserEmailDisplay').textContent =
                                data.user.email || '---';

                            // Set status display
                            const statusDisplay = document.getElementById('editUserStatusDisplay');
                            if (data.user.status == 1) {
                                statusDisplay.innerHTML = '<span class="badge bg-success">Active</span>';
                            } else {
                                statusDisplay.innerHTML = '<span class="badge bg-secondary">Inactive</span>';
                            }

                            // Update role display
                            const roleDisplay = document.getElementById('editUserRoleDisplay');
                            if (roleDisplay) {
                                let roleText = data.user.role_name ?
                                    data.user.role_name.charAt(0).toUpperCase() + data.user.role_name.slice(1).replace('_', ' ') :
                                    'Student';
                                roleDisplay.textContent = roleText;
                            }

                            // Show/hide supervisor section based on role (FIXED: using correct ID)
                            const supervisorSection = document.getElementById('supervisorSection');
                            if (data.user.role_name === 'student') {
                                supervisorSection.style.display = 'block';

                                // Set current supervisor if exists
                                if (data.user.supervisor) {
                                    document.getElementById('editSupervisorId').value = data.user.supervisor.supervisor_id_or_hod_id || '';
                                    document.getElementById('currentSupervisor').innerHTML =
                                        `<i class="bi bi-info-circle me-1"></i>Current supervisor: ${data.user.supervisor.first_name || ''} ${data.user.supervisor.last_name || ''}`;
                                } else {
                                    document.getElementById('currentSupervisor').innerHTML = '';
                                    document.getElementById('editSupervisorId').value = '';
                                }
                            } else {
                                supervisorSection.style.display = 'none';
                                document.getElementById('editSupervisorId').value = '';
                                document.getElementById('currentSupervisor').innerHTML = '';
                            }

                            // Set status badge and status indicator
                            const status = data.user.status == 1 ? 'active' : 'inactive';
                            const statusColor = data.user.status == 1 ? 'bg-success' : 'bg-secondary';

                            // Update status badge in profile
                            const statusIndicator = document.querySelector('#editUserStatus .badge');
                            if (statusIndicator) {
                                statusIndicator.className = `badge ${statusColor} p-2 rounded-circle`;
                            }

                            // Set current image with error handling
                            const currentImage = document.getElementById('editCurrentImage');
                            if (currentImage) {
                                currentImage.onerror = function() {
                                    // If image fails to load, use avatar
                                    this.onerror = null;
                                    this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(data.user.first_name + ' ' + data.user.last_name) + '&background=22c55e&color=fff&size=100';
                                };
                                currentImage.src = data.user.image_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(data.user.first_name + ' ' + data.user.last_name) + '&background=22c55e&color=fff&size=100';
                            }

                            // Show form
                            document.getElementById('editUserForm').style.display = 'block';

                            console.log('Form populated successfully');
                        } else {
                            alert('Error loading user data: ' + (data.message || 'Unknown error'));
                            modal.hide();
                        }
                    })
                    .catch(error => {
                        console.error('Error in fetch:', error);
                        document.getElementById('editUserLoading').style.display = 'none';
                        alert('Error loading user data. Check console for details (F12).');
                        modal.hide();
                    });
            }



            // Function to save user changes
            // Function to save user changes
            // Function to save user changes
            function saveUserChanges() {
                // Get form data
                const userId = document.getElementById('editUserId').value;
                const universityId = document.getElementById('editUniversityId').value;

                if (!userId) {
                    alert('Error: User ID not found');
                    return;
                }

                // Get the button and show loading
                const btn = document.getElementById('saveUserChanges');
                const originalContent = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';

                // Create FormData
                const formData = new FormData();
                formData.append('user_id', userId);
                formData.append('first_name', document.getElementById('editFirstName').value.trim());
                formData.append('last_name', document.getElementById('editLastName').value.trim());
                formData.append('email', document.getElementById('editEmail').value.trim());
                formData.append('mobile', document.getElementById('editMobile').value.trim());
                formData.append('role', document.getElementById('editRole').value);
                formData.append('original_email', document.getElementById('editUserOriginalEmail').value);
                formData.append('original_role', document.getElementById('editUserOriginalRole').value);

                // Add supervisor if exists
                const supervisorId = document.getElementById('editSupervisorId')?.value;
                if (supervisorId) {
                    formData.append('supervisor_id', supervisorId);
                }

                // Add profile image if selected
                const imageFile = document.getElementById('editProfileImage').files[0];
                if (imageFile) {
                    formData.append('profile_image', imageFile);
                }

                // Send AJAX request like signup
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../controllers/update_user.php', true);
                xhr.timeout = 30000;

                xhr.onload = function() {
                    // Reset button
                    btn.disabled = false;
                    btn.innerHTML = originalContent;

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);

                            if (response.success) {
                                // Show success message
                                showSuccess('User updated successfully!');

                                // Update the user row in table
                                const lookupId = universityId || response.user.university_id;
                                updateUserRowInTable(lookupId, response.user);

                                // Close modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                                if (modal) {
                                    modal.hide();
                                }

                                // Reset image preview
                                document.getElementById('editProfileImage').value = '';
                                document.getElementById('editProfileImagePreview').style.display = 'none';
                                document.getElementById('editProfileImagePlaceholder').style.display = 'flex';
                                document.getElementById('clearEditImageBtn').style.display = 'none';

                                // Reload counts if function exists
                                if (typeof loadUserCounts === 'function') {
                                    loadUserCounts();
                                }
                            } else {
                                if (response.errors) {
                                    // Display field errors
                                    for (let field in response.errors) {
                                        const errorDiv = document.getElementById(`edit${field.charAt(0).toUpperCase() + field.slice(1)}Error`);
                                        const inputField = document.getElementById(`edit${field.charAt(0).toUpperCase() + field.slice(1)}`);

                                        if (errorDiv) {
                                            errorDiv.textContent = response.errors[field];
                                            errorDiv.style.display = 'block';
                                        }
                                        if (inputField) {
                                            inputField.classList.add('is-invalid');
                                        }
                                    }
                                } else {
                                    alert('Error: ' + (response.message || 'Update failed'));
                                }
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                            alert('Server error occurred. Please try again.');
                        }
                    } else {
                        alert('Connection error. Please try again.');
                    }
                };

                xhr.onerror = function() {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    alert('Network error. Please check your connection.');
                };

                xhr.ontimeout = function() {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    alert('Request timed out. Please try again.');
                };

                xhr.send(formData);
            }

            // Add event listener for role change
            document.addEventListener('DOMContentLoaded', function() {
                const roleSelect = document.getElementById('editRole');
                if (roleSelect) {
                    roleSelect.addEventListener('change', function() {
                        const supervisorSection = document.getElementById('supervisorSection');
                        if (this.value === 'student') {
                            supervisorSection.style.display = 'block';
                        } else {
                            supervisorSection.style.display = 'none';
                        }
                    });
                }

                // Mobile input validation
                const mobileInput = document.getElementById('editMobile');
                if (mobileInput) {
                    mobileInput.addEventListener('input', function(e) {
                        // Only allow numbers
                        this.value = this.value.replace(/[^0-9]/g, '');

                        // Limit to 10 digits
                        if (this.value.length > 10) {
                            this.value = this.value.slice(0, 10);
                        }
                    });
                }
            });

            // ========== SIDEBAR & NAVIGATION ==========
            // ========== SIDEBAR & NAVIGATION ==========
            function toggleSidebar() {
                document.getElementById("sidebar").classList.toggle("active");
                document.getElementById("sidebarOverlay").classList.toggle("active");
            }

            function toggleNotifications() {
                document.getElementById("notificationDropdown").classList.toggle("show");
            }

            function showSection(section) {
                console.log('Showing section:', section);
                const sections = ['dashboard', 'userManagement', 'equipment', 'history', 'activity', 'analytics', 'logbook'];
                sections.forEach(s => {
                    const el = document.getElementById(s + 'Section');
                    if (el) el.style.display = 'none';
                });

                const sectionElement = document.getElementById(section + 'Section');
                if (sectionElement) {
                    sectionElement.style.display = 'block';
                    console.log('Section displayed:', section);
                } else {
                    console.error('Section not found:', section + 'Section');
                }

                document.querySelectorAll('.sidebar a').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('onclick')?.includes(section)) {
                        link.classList.add('active');
                    }
                });

                if (section === 'equipment') {
                    loadEquipmentWithUsage();
                }
                if (section === 'dashboard' || section === 'analytics') {
                    setTimeout(() => {
                        if (typeof initCharts === 'function') initCharts();
                        if (typeof initAnalyticsCharts === 'function') initAnalyticsCharts();
                        if (typeof initCalendar === 'function') initCalendar();
                        if (typeof initCalendarListeners === 'function') initCalendarListeners();
                    }, 100);
                }
                if (section === 'history') {
                    const searchInput = document.getElementById('reservationSearch');
                    if (searchInput) searchInput.value = '';
                    const statusFilter = document.getElementById('statusFilter');
                    if (statusFilter) statusFilter.value = 'all';
                    if (typeof loadReservations === 'function') loadReservations();
                }
                if (section === 'activity') {
                    if (typeof initRequestSection === 'function') initRequestSection();
                }
                if (section === 'logbook') {
                    if (typeof loadLogbooks === 'function') loadLogbooks();
                }
            }





            //     function displayEquipmentTable(equipment) {
            //         const tableBody = document.getElementById('equipmentTableBody');
            //         if (!tableBody) return;

            //         tableBody.innerHTML = '';

            //         if (!equipment || equipment.length === 0) {
            //             tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No equipment found</td></tr>';
            //             return;
            //         }


            //         equipment.sort((a, b) => b.usage - a.usage);

            //         equipment.forEach(item => {
            //             const code = item.code || 'N/A';
            //             const name = item.name || 'Unknown';
            //             const image = item.image || 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png';
            //             const maintenance = item.maintenance || 0;
            //             const broken = item.broken || 0;
            //             const usage = Math.round(parseFloat(item.usage) || 0);


            //             let barColor = '#22c55e'; 
            //             if (usage < 30) barColor = '#ef4444'; 
            //             else if (usage < 60) barColor = '#f59e0b';

            //             const row = document.createElement('tr');
            //             row.setAttribute('data-equipment-id', code);
            //             row.setAttribute('data-equipment-id-numeric', item.id || '');

            //             row.innerHTML = `
            //     <td>
            //         <img src="${image}"
            //              style="width:50px;height:50px;object-fit:contain;"
            //              onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'"
            //              alt="${name}">
            //     </td>
            //     <td><strong>${name}</strong></td>
            //     <td>
            //         ${maintenance > 0 
            //             ? `<span class="badge bg-warning">${maintenance}</span>` 
            //             : '<span class="text-muted">------</span>'}
            //     </td>
            //     <td>
            //         ${broken > 0 
            //             ? `<span class="badge bg-danger">${broken}</span>` 
            //             : '<span class="text-muted">------</span>'}
            //     </td>
            //     <td>
            //         <div class="d-flex align-items-center gap-2">
            //             <div style="width:100px;height:8px;background:#e9ecef;
            //                         border-radius:4px;overflow:hidden;">
            //                 <div style="width:${usage}%;height:8px;
            //                             background:${barColor};border-radius:4px;
            //                             transition:width 0.6s ease;"></div>
            //             </div>
            //             <span style="font-weight:600;color:${barColor};min-width:45px;">
            //                 ${usage}%
            //             </span>
            //         </div>

            //     </td>
            //     <td>
            //         <div class="action-buttons">
            //             <button class="btn-view"
            //                     onclick="viewEquipment('${code}')" title="View Details">
            //                 <i class="bi bi-eye"></i>
            //             </button>
            //             <button class="btn-edit"
            //                     onclick="editEquipment('${code}')" title="Edit">
            //                 <i class="bi bi-pencil-square"></i>
            //             </button>
            //             <button class="btn-remove"
            //                     onclick="removeEquipment('${code}')" title="Remove">
            //                 <i class="bi bi-trash"></i>
            //             </button>
            //         </div>
            //     </td>
            // `;
            //             tableBody.appendChild(row);
            //         });

            //         const countEl = document.getElementById('equipmentCount');
            //         if (countEl) {
            //             countEl.textContent = `(${equipment.length})`;
            //         }
            //     }

            function viewEquipment(code) {

                document.getElementById('equipmentDetailsContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status"
                 style="width:2rem;height:2rem;"></div>
            <p class="mt-3 text-muted small fw-semibold">
                Loading equipment details...
            </p>
        </div>
    `;
                new bootstrap.Modal(document.getElementById('equipmentDetailsModal')).show();

                fetch(`get_equipment_details.php?code=${encodeURIComponent(code)}`)
                    .then(res => res.json())
                    .then(eq => {
                        if (eq.error) {
                            document.getElementById('equipmentDetailsContent').innerHTML = `
                    <div class="alert alert-danger m-3">
                        <i class="bi bi-exclamation-circle me-2"></i>${eq.error}
                    </div>`;
                            return;
                        }


                        const lastUsageHTML = eq.last_booking ? `
                <tr>
                    <td colspan="2">
                        <div class="mt-3 mb-2">
                            <span class="fw-semibold small text-uppercase text-primary">
                                <i class="bi bi-clock-history me-1"></i>Last Usage Details
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal" style="width:150px">Student ID</th>
                    <td>
                        <span class="badge bg-primary fs-6">
                            <i class="bi bi-person-fill me-1"></i>${eq.last_booking.university_id}
                        </span>
                        <span class="ms-2 fw-semibold text-dark">${eq.last_booking.full_name}</span>
                    </td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal">Booking Date</th>
                    <td>${new Date(eq.last_booking.booking_date).toLocaleString()}</td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal">Quantity Used</th>
                    <td>${eq.last_booking.qty} unit(s)</td>
                </tr>
                ${eq.last_booking.comment ? `
                <tr>
                    <th class="text-muted fw-normal">Student Comment</th>
                    <td><em class="text-muted">"${eq.last_booking.comment}"</em></td>
                </tr>` : ''}
                ${eq.last_booking.any_comment ? `
                <tr>
                    <th class="text-muted fw-normal">Additional Notes</th>
                    <td><em class="text-muted">"${eq.last_booking.any_comment}"</em></td>
                </tr>` : ''}
                <tr>
                    <th class="text-muted fw-normal">Reservation ID</th>
                    <td><span class="badge bg-secondary">#${eq.last_booking.reservation_id}</span></td>
                </tr>
            ` : `
                <tr>
                    <td colspan="2">
                        <div class="mt-3 mb-2">
                            <span class="fw-semibold small text-uppercase text-primary">
                                <i class="bi bi-clock-history me-1"></i>Last Usage Details
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="text-muted fst-italic py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        No usage records found for this equipment.
                    </td>
                </tr>
            `;


                        const statsHTML = `
                <tr>
                    <td colspan="2">
                        <div class="mt-3 mb-2">
                            <span class="fw-semibold small text-uppercase text-success">
                                <i class="bi bi-bar-chart me-1"></i>Usage Statistics (Last 6 Months)
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal">Total Bookings</th>
                    <td>${eq.total_bookings || 0}</td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal">Completed Bookings</th>
                    <td>${eq.completed_bookings || 0}</td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal">Usage Rate</th>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:100px;height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;">
                                <div style="width:${eq.usage}%;height:8px;background:#22c55e;border-radius:4px;"></div>
                            </div>
                            <span style="font-weight:600;color:#22c55e;">${eq.usage}%</span>
                        </div>
                    </td>
                </tr>
            `;

                        // Build full modal - removed location since it's not in your table
                        document.getElementById('equipmentDetailsContent').innerHTML = `
                <div class="row g-0">
                    <!-- Left: image + name + code -->
                    <div class="col-md-4 text-center border-end p-4">
                        <img src="${eq.image_path ? eq.image_path : 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png'}"
                             style="width:140px;height:140px;object-fit:contain;"
                             class="rounded border p-2 bg-light mb-3"
                             onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'">
                        <h5 class="fw-bold mb-1" style="color:#166534;">${eq.name}</h5>
                        <span class="badge bg-secondary">
                            <i class="bi bi-upc-scan me-1"></i>${eq.equipment_code}
                        </span>
                        <div class="mt-3">
                            <span class="badge bg-info text-dark">
                                <i class="bi bi-box-seam me-1"></i>Total Qty: ${eq.qty}
                            </span>
                            ${eq.maintenance > 0 ? `
                            <span class="badge bg-warning ms-2">
                                <i class="bi bi-tools me-1"></i>Maintenance: ${eq.maintenance}
                            </span>
                            ` : ''}
                        </div>
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
                                    <th class="text-muted fw-normal">Date Added</th>
                                    <td>${eq.added_datetime ? new Date(eq.added_datetime).toLocaleDateString() : '—'}</td>
                                </tr>

                                ${eq.description ? `
                                <tr>
                                    <th class="text-muted fw-normal">Description</th>
                                    <td><small>${eq.description}</small></td>
                                </tr>` : ''}

                                <!-- Statistics Section -->
                                ${statsHTML}

                                <!-- Last Usage Section with Student Details -->
                                ${lastUsageHTML}
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



            let isEditMode = false;
            let currentEquipmentId = null;

            // Open add equipment modal
            function addEquipment() {
                isEditMode = false;
                currentEquipmentId = null;

                document.getElementById('addEquipmentForm').reset();
                document.getElementById('eqId').value = '';

                document.getElementById('eqImagePreview').style.display = 'none';
                document.getElementById('eqImagePlaceholder').style.display = 'block';
                document.getElementById('currentImageInfo').style.display = 'none';

                document.getElementById('eqQty').value = '1';
                document.getElementById('eqSimultaneousUsers').value = '1';
                document.getElementById('eqSterilization').value = 'NO';
                document.getElementById('eqReservation').value = 'YES';
                document.getElementById('eqLabLocation').value = '';

                document.getElementById('equipmentModalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Equipment';
                document.getElementById('modalSaveButtonText').textContent = 'Save Equipment';

                clearEquipmentErrors();

                const modal = new bootstrap.Modal(document.getElementById('addEquipmentModal'));
                modal.show();
            }

            // Clear form errors
            function clearEquipmentErrors() {
                const errorFields = ['eqCode', 'eqName', 'eqQty', 'eqLabLocation'];
                errorFields.forEach(field => {
                    const element = document.getElementById(field);
                    if (element) element.classList.remove('is-invalid');
                    const errorElement = document.getElementById(field + 'Error');
                    if (errorElement) errorElement.textContent = '';
                });
            }

            // Preview image
            function previewEquipmentImage(input) {
                const preview = document.getElementById('eqImagePreview');
                const placeholder = document.getElementById('eqImagePlaceholder');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        placeholder.style.display = 'none';
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Save equipment (add or update)
            function saveEquipment() {
                const id = document.getElementById('eqId').value;
                const code = document.getElementById('eqCode').value.trim();
                const name = document.getElementById('eqName').value.trim();
                const qty = document.getElementById('eqQty').value;
                const simultaneous_users = document.getElementById('eqSimultaneousUsers').value || 1;
                const sterilization = document.getElementById('eqSterilization').value;
                const reservation = document.getElementById('eqReservation').value;
                const labLocation = document.getElementById('eqLabLocation').value;
                const description = document.getElementById('eqDescription').value.trim();
                const imageFile = document.getElementById('eqImage').files[0];

                // Validate
                let isValid = true;

                if (!code) {
                    document.getElementById('eqCode').classList.add('is-invalid');
                    document.getElementById('eqCodeError').textContent = 'Equipment code is required';
                    isValid = false;
                } else {
                    document.getElementById('eqCode').classList.remove('is-invalid');
                }

                if (!name) {
                    document.getElementById('eqName').classList.add('is-invalid');
                    document.getElementById('eqNameError').textContent = 'Equipment name is required';
                    isValid = false;
                } else {
                    document.getElementById('eqName').classList.remove('is-invalid');
                }

                if (!qty || qty < 1) {
                    document.getElementById('eqQty').classList.add('is-invalid');
                    document.getElementById('eqQtyError').textContent = 'Quantity must be at least 1';
                    isValid = false;
                } else {
                    document.getElementById('eqQty').classList.remove('is-invalid');
                }

                if (!labLocation) {
                    document.getElementById('eqLabLocation').classList.add('is-invalid');
                    document.getElementById('eqLabLocationError').textContent = 'Lab location is required';
                    isValid = false;
                } else {
                    document.getElementById('eqLabLocation').classList.remove('is-invalid');
                }

                if (!isValid) return;

                const saveBtn = document.querySelector('#addEquipmentModal .btn-success');
                const originalText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

                const formData = new FormData();

                if (id) formData.append('id', id);

                formData.append('code', code);
                formData.append('name', name);
                formData.append('qty', qty);
                formData.append('simultaneous_users', simultaneous_users);
                formData.append('sterilization_required', sterilization);
                formData.append('reservation_required', reservation);
                formData.append('location_id', labLocation);
                formData.append('description', description);
                if (imageFile) formData.append('image', imageFile);

                const url = id ? '../controllers/update_equipment.php' : '../controllers/add_equipment.php';

                fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;

                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('addEquipmentModal')).hide();
                            showSuccess(id ? 'Equipment updated successfully!' : 'Equipment added successfully!');
                            loadEquipmentWithUsage();
                        } else {
                            alert('Error: ' + (data.message || 'Operation failed'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;
                        alert('Network error. Please try again.');
                    });
            }

            // Edit equipment
            function editEquipment(code) {
                isEditMode = true;

                document.getElementById('equipmentModalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Equipment';
                document.getElementById('modalSaveButtonText').textContent = 'Update Equipment';

                clearEquipmentErrors();

                const modal = new bootstrap.Modal(document.getElementById('addEquipmentModal'));
                modal.show();

                document.getElementById('eqCode').value = 'Loading...';
                document.getElementById('eqName').value = 'Loading...';
                document.getElementById('eqCode').disabled = true;
                document.getElementById('eqName').disabled = true;

                fetch(`../controllers/get_equipment_details.php?code=${encodeURIComponent(code)}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('eqCode').disabled = false;
                        document.getElementById('eqName').disabled = false;

                        if (data.success) {
                            currentEquipmentId = data.equipment.id;
                            document.getElementById('eqId').value = data.equipment.id;

                            document.getElementById('eqCode').value = data.equipment.code || '';
                            document.getElementById('eqName').value = data.equipment.name || '';
                            document.getElementById('eqQty').value = data.equipment.total_qty || 1;
                            document.getElementById('eqSimultaneousUsers').value = data.equipment.simultaneous_users || 1;
                            document.getElementById('eqSterilization').value = data.equipment.sterilization_required || 'NO';
                            document.getElementById('eqReservation').value = data.equipment.reservation_required || 'YES';
                            document.getElementById('eqLabLocation').value = data.equipment.location_id || '';
                            document.getElementById('eqDescription').value = data.equipment.description || '';

                            if (data.equipment.image_path) {
                                const imagePath = data.equipment.image_path;
                                document.getElementById('eqImagePreview').src = imagePath;
                                document.getElementById('eqImagePreview').style.display = 'block';
                                document.getElementById('eqImagePlaceholder').style.display = 'none';
                                document.getElementById('currentImageInfo').innerHTML = 'Current image: <img src="' + imagePath + '" style="height: 30px; width: 30px; object-fit: cover; border-radius: 4px;">';
                                document.getElementById('currentImageInfo').style.display = 'block';
                            }
                        } else {
                            alert('Error loading equipment details: ' + (data.message || 'Unknown error'));
                            modal.hide();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('eqCode').disabled = false;
                        document.getElementById('eqName').disabled = false;
                        alert('Network error. Please try again.');
                        modal.hide();
                    });
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
                        console.log('Reservations data:', res);
                        tableBody.innerHTML = '';

                        if (!res.success || res.data.length === 0) {
                            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No reservations found</td></tr>';
                            document.getElementById('reservationCount').textContent = '(0)';
                            return;
                        }

                        res.data.forEach(item => {
                            // Determine status badge based on your status values
                            let badgeClass, statusLower;

                            // Map your status values to display
                            const status = item.status || 'pending';

                            if (status === 'ready' || status === 'Ready') {
                                badgeClass = 'bg-success';
                                statusLower = 'Ready';
                            } else if (status === 'rejected' || status === 'Rejected') {
                                badgeClass = 'bg-danger';
                                statusLower = 'Rejected';
                            } else if (status === 'TO_Pending' || status === 'TO_Pending') {
                                badgeClass = 'bg-info';
                                statusLower = 'TO_Pending';
                            } else {
                                badgeClass = 'bg-warning';
                                statusLower = 'Pending';
                            }

                            // Use the correct field names from your database
                            const reservationId = item.display_id || item.id || 'N/A';
                            const location = item.lab_location || '—';
                            const studentId = item.student_id || '—';
                            const displayStatus = item.raw_status || status;
                            const date = item.date || '—';

                            const row = document.createElement('tr');
                            row.setAttribute('data-id', item.id);
                            row.setAttribute('data-status', statusLower);
                            row.innerHTML = `
                    <td>${reservationId}</td>
                    <td>${location}</td>
                    <td>${studentId}</td>
                    <td><span class="badge ${badgeClass}">${displayStatus}</span></td>
                    <td>${date}</td>
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
                        console.error('Error loading reservations:', err);
                        tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Error loading data</td></tr>`;
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
                document.body.style.overflow = 'hidden';

                console.log('Fetching reservation ID:', id, 'Type:', typeof id);

                fetch(`../controllers/get_reservation_details.php?id=${encodeURIComponent(id)}`)
                    .then(r => r.json())
                    .then(res => {
                        console.log('API Response:', res);

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

                        // Format date nicely
                        let formattedDate = res.date || '—';
                        if (res.date && res.date !== '—') {
                            try {
                                const dateObj = new Date(res.date);
                                if (!isNaN(dateObj.getTime())) {
                                    formattedDate = dateObj.toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                }
                            } catch (e) {
                                console.error('Date formatting error:', e);
                            }
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

                        // Build comment if exists
                        let commentHtml = res.comment ?
                            `<tr><td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Student Comment:</td>
                 <td style="padding:10px 0;border-top:1px solid #f5f5f5;"><em>"${res.comment}"</em></td></tr>` : '';

                        // Build equipment list HTML
                        let equipmentHtml = '';
                        if (res.equipment && res.equipment.length > 0) {
                            let equipmentRows = '';
                            res.equipment.forEach(item => {
                                equipmentRows += `
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 12px 8px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        <img src="${item.image}" 
                                             style="width: 100%; height: 100%; object-fit: contain;"
                                             onerror="this.src='https://cdn-icons-png.flaticon.com/512/2941/2941514.png'"
                                             alt="${item.name}">
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #166534;">${item.name}</div>
                                        <div style="font-size: 0.8rem; color: #6b7280;">Code: ${item.code}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 12px 8px; text-align: center; vertical-align: middle;">
                                <span style="background: #22c55e; color: white; padding: 4px 12px; border-radius: 20px; font-weight: 600; font-size: 0.9rem;">
                                    ${item.booked_qty}
                                </span>
                            </td>
                        </tr>
                    `;
                            });

                            equipmentHtml = `
                    <tr>
                        <td colspan="2" style="padding: 20px 0 10px 0;">
                            <div style="background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; margin-top: 15px;">
                                <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); padding: 15px 20px; border-bottom: 2px solid #22c55e;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <h6 style="margin: 0; color: #166534; font-weight: 700; font-size: 1rem;">
                                            <i class="bi bi-tools" style="margin-right: 8px;"></i>
                                            Equipment Requested
                                        </h6>
                                        <span style="background: #22c55e; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            ${res.equipment_count} item${res.equipment_count > 1 ? 's' : ''}
                                        </span>
                                    </div>
                                </div>
                                <div style="padding: 0 15px;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background: #f9fafb;">
                                                <th style="padding: 12px 8px; text-align: left; color: #374151; font-weight: 600; font-size: 0.85rem; border-bottom: 2px solid #e5e7eb;">Equipment</th>
                                                <th style="padding: 12px 8px; text-align: center; color: #374151; font-weight: 600; font-size: 0.85rem; border-bottom: 2px solid #e5e7eb;">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${equipmentRows}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
                        } else {
                            equipmentHtml = `
                    <tr>
                        <td colspan="2" style="padding: 15px 0;">
                            <div style="background: #f9fafb; border-radius: 12px; padding: 30px 20px; text-align: center; border: 1px dashed #d1d5db; margin-top: 15px;">
                                <i class="bi bi-tools" style="font-size: 2.5rem; color: #9ca3af; margin-bottom: 10px; display: block;"></i>
                                <p style="color: #6b7280; margin: 0; font-size: 0.95rem;">No equipment associated with this reservation</p>
                            </div>
                        </td>
                    </tr>
                `;
                        }

                        // Build the complete modal content
                        document.getElementById('resModalContent').innerHTML = `
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="padding:10px 0;color:#166534;font-weight:600;width:170px;">Reservation ID:</td>
                        <td style="padding:10px 0;"><strong>${res.id || 'N/A'}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Lab Location:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${res.lab_location || '—'}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Student ID:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">
                            <strong>${res.student_id || '—'}</strong>
                            ${res.student_name ? `<span style="color:#666; margin-left:8px;">(${res.student_name})</span>` : ''}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Supervisor ID:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${res.supervisor_id || '—'}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Status:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${statusBadge}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#166534;font-weight:600;border-top:1px solid #f5f5f5;">Request Date:</td>
                        <td style="padding:10px 0;border-top:1px solid #f5f5f5;">${formattedDate}</td>
                    </tr>
                    ${commentHtml}
                    ${rejectionHtml}
                    ${equipmentHtml}
                </table>
            `;
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        document.getElementById('resModalContent').innerHTML =
                            `<div class="alert alert-danger m-3">Error loading reservation details: ${err.message}</div>`;
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
            let usageChart, monthlyChart;






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
                                    backgroundColor: '#3b82f6',
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
                // displayEquipmentUsageTable(equipmentUsageData);
            }

            // Global chart variable
            let equipmentUsageChart = null;

            function initAnalyticsCharts() {
                const ctx = document.getElementById('equipmentUsageChart')?.getContext('2d');
                if (!ctx) return;

                // Show loading state
                ctx.canvas.style.opacity = '0.5';

                // Fetch real data for chart
                fetch('../controllers/get_equipment_usage_stats.php?limit=6')
                    .then(response => response.json())
                    .then(data => {
                        ctx.canvas.style.opacity = '1';

                        if (data.success && data.equipment.length > 0) {
                            // Destroy old chart if exists
                            if (equipmentUsageChart) equipmentUsageChart.destroy();

                            // Get top 6 equipment by usage
                            const topEquipment = data.equipment.slice(0, 6);
                            const labels = topEquipment.map(e => e.name);
                            const usageData = topEquipment.map(e => e.usage);

                            // Generate colors based on usage
                            const colors = usageData.map(usage => {
                                if (usage >= 70) return '#22c55e'; // high usage - green
                                if (usage >= 40) return '#f59e0b'; // medium usage - orange
                                return '#ef4444'; // low usage - red
                            });

                            equipmentUsageChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Usage Percentage',
                                        data: usageData,
                                        backgroundColor: colors,
                                        borderRadius: 8,
                                        barPercentage: 0.7
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: (context) => {
                                                    const item = topEquipment[context.dataIndex];
                                                    return `${context.raw}% (${item.bookings} bookings)`;
                                                }
                                            }
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
                        } else {
                            ctx.canvas.parentNode.innerHTML = '<div class="text-center py-4 text-muted">No equipment data available</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading chart data:', error);
                        ctx.canvas.parentNode.innerHTML = '<div class="text-center py-4 text-danger">Error loading chart data</div>';
                    });
            }

            // Load equipment usage data from database
            function loadEquipmentUsageData() {
                const tableBody = document.getElementById('equipmentUsageTableBody');
                if (!tableBody) return;

                // Show loading state
                tableBody.innerHTML = `
        <tr>
            <td colspan="2" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-success me-2"></div>
                Loading equipment data...
            </td>
        </tr>`;

                // Fetch equipment usage data from server
                fetch('../controllers/get_equipment_usage_stats.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            equipmentUsageData = data.equipment;
                            displayEquipmentUsageTable(equipmentUsageData);
                        } else {
                            tableBody.innerHTML = `
                    <tr>
                        <td colspan="2" class="text-center py-4 text-danger">
                            ❌ Failed to load equipment data
                        </td>
                    </tr>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading equipment usage:', error);
                        tableBody.innerHTML = `
                <tr>
                    <td colspan="2" class="text-center py-4 text-danger">
                        ❌ Connection error
                    </td>
                </tr>`;
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

                if (!data || data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="2" class="text-center py-4">No equipment found</td></tr>';
                    return;
                }

                data.forEach(item => {
                    // Determine color based on usage percentage
                    let color = '#22c55e'; // green for >=60%
                    if (item.usage < 30) color = '#ef4444'; // red for <30%
                    else if (item.usage < 60) color = '#f59e0b'; // orange for 30-59%

                    const row = document.createElement('tr');
                    row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <span class="fw-semibold">${item.name}</span>
                    <small class="text-muted ms-2">(${item.code})</small>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="progress-bar" style="width: 120px; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                        <div class="progress-fill" style="width: ${item.usage}%; height: 8px; background: ${color}; border-radius: 4px;"></div>
                    </div>
                    <span style="color: ${color}; font-weight: 600; min-width: 45px;">${item.usage}%</span>
                    <small class="text-muted">(${item.bookings} bookings)</small>
                </div>
            </td>
        `;
                    tableBody.appendChild(row);
                });
            }

            // Call this when analytics section is shown
            function initAnalyticsSection() {
                loadEquipmentUsageData();
            }

            function downloadInventory() {
                showSuccess('Generating report, please wait...');

                fetch('get_equipment_fulllist_excel.php')
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error('Server error: ' + text.substring(0, 200));
                            });
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'equipment_inventory_' + new Date().toISOString().slice(0, 10) + '.xls';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        showSuccess('Inventory downloaded successfully!');
                    })
                    .catch(error => {
                        console.error('Download error:', error);
                        showError('Download failed: ' + error.message);
                    });
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
                    // Fetch real rejected requests data
                    fetch('../controllers/get_rejected_requests.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.requests.length > 0) {
                                let report = "REJECTED REQUESTS REPORT\n";
                                report += "=======================\n\n";
                                report += "Request ID | Student ID | Reason | Date\n";
                                report += "----------------------------------------\n";

                                data.requests.forEach(req => {
                                    report += `${req.reservation_id} | ${req.university_id} | ${req.reason} | ${req.rejected_date}\n`;
                                });

                                console.log(report);
                                downloadReport(report, 'rejected_requests.txt');
                            } else {
                                alert('No rejected requests found');
                            }
                        })
                        .catch(() => alert('Error loading rejected requests'));

                } else if (type === 'usage') {
                    // Use existing equipmentUsageData
                    if (equipmentUsageData.length > 0) {
                        let report = "EQUIPMENT USAGE REPORT\n";
                        report += "======================\n\n";
                        report += "Equipment Name | Code | Usage % | Bookings\n";
                        report += "----------------------------------------\n";

                        equipmentUsageData.forEach(item => {
                            report += `${item.name} | ${item.code} | ${item.usage}% | ${item.bookings}\n`;
                        });

                        console.log(report);
                        downloadReport(report, 'equipment_usage.txt');
                    } else {
                        alert('No equipment data available');
                    }
                }
            }

            // Helper function to download report
            function downloadReport(content, filename) {
                const blob = new Blob([content], {
                    type: 'text/plain'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                // Show success message
                showSuccess(`${filename} generated successfully!`);
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
                            ${ev.location}
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
                loadNotifications();
                startNotificationPolling();
                loadPendingLogbooks();
                startLogbookPolling();


                loadEquipmentWithUsage();
                if (document.getElementById('analyticsSection')) setTimeout(initAnalyticsCharts, 500);
            });






            // ========== NOTIFICATION SYSTEM ==========
            let notificationsData = [];
            let notificationPollInterval = null;

            function toggleNotifications() {
                const dropdown = document.getElementById('notificationDropdown');
                const isShowing = dropdown.classList.contains('show');

                // Close if already open
                if (isShowing) {
                    dropdown.classList.remove('show');
                    return;
                }

                // Open and load
                dropdown.classList.add('show');
                loadNotifications();
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                const bell = e.target.closest('.notification-bell');
                const dropdown = e.target.closest('.notification-dropdown');
                if (!bell && !dropdown) {
                    document.getElementById('notificationDropdown').classList.remove('show');
                }
            });

            function loadNotifications() {
                fetch('../controllers/fetch_notifications.php')
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) return;

                        notificationsData = data.notifications;
                        renderNotifications(data.notifications);
                        updateNotificationBadge(data.unread_count);
                    })
                    .catch(err => {
                        console.error('Notification fetch error:', err);
                        document.getElementById('mainNotificationList').innerHTML = `
                <div class="no-notifications">
                    <i class="bi bi-wifi-off d-block"></i>
                    Failed to load notifications
                </div>`;
                    });
            }

            function renderNotifications(notifications) {
                const list = document.getElementById('mainNotificationList');
                const newSpan = document.getElementById('notificationNewCount');

                const unread = notifications.filter(n => n.status === 'unread').length;
                newSpan.textContent = unread + ' new';

                if (!notifications || notifications.length === 0) {
                    list.innerHTML = `
            <div class="no-notifications">
                <i class="bi bi-bell-slash d-block"></i>
                <p class="mt-2">No notifications yet</p>
            </div>`;
                    return;
                }

                let html = '';
                notifications.forEach(n => {
                    const isUnread = n.status === 'unread';
                    const isApproval = n.need_approval == 1;
                    const isHod = <?php echo json_encode($_SESSION['user_role'] ?? ''); ?> === 'hod';

                    // Icon based on content
                    let icon = 'bi-info-circle-fill text-primary';
                    if (isApproval) icon = 'bi-exclamation-circle-fill text-warning';
                    if (n.description.toLowerCase().includes('approved')) icon = 'bi-check-circle-fill text-success';
                    if (n.description.toLowerCase().includes('rejected')) icon = 'bi-x-circle-fill text-danger';

                    const timeAgo = getTimeAgo(n.created_datetime);

                    // Approve/Reject buttons — only for HOD on equipment approval notifications
                    let approvalBtns = '';
                    if (isApproval && isHod) {
                        approvalBtns = `
                <div class="approve-btns" style="display:flex; gap:6px; margin-top:8px;">
                    <button class="btn btn-sm btn-success py-1 px-2"
                            onclick="approveEquipmentFromNotif(${n.id}, event)"
                            style="font-size:0.78rem;">
                        <i class="bi bi-check2 me-1"></i>Approve
                    </button>
                    <button class="btn btn-sm btn-danger py-1 px-2"
                            onclick="rejectEquipmentFromNotif(${n.id}, event)"
                            style="font-size:0.78rem;">
                        <i class="bi bi-x me-1"></i>Reject
                    </button>
                </div>`;
                    }

                    // Mark as read button — shown on ALL unread notifications
                    const markReadBtn = (isUnread && !isApproval) ? `
    <button class="btn btn-sm btn-outline-secondary py-0 px-2 mt-1"
            onclick="markOneRead(${n.id}, this.closest('.notification-item'), event)"
            style="font-size:0.72rem;">
        <i class="bi bi-check me-1"></i>Mark read
    </button>` : '';

                    html += `
            <div class="notification-item ${isUnread ? 'unread' : ''} ${isApproval ? 'approval' : ''}"
                 data-notif-id="${n.id}">
                <div class="d-flex gap-2">
                    <i class="bi ${icon} mt-1" style="font-size:1rem; flex-shrink:0;"></i>
                    <div class="flex-grow-1">
                        <div class="notif-message">${n.description}</div>
                        <div class="notif-time"><i class="bi bi-clock me-1"></i>${timeAgo}</div>
                        <div class="d-flex gap-2 flex-wrap align-items-center mt-1">
                            ${markReadBtn}
                        </div>
                        ${approvalBtns}
                    </div>
                </div>
            </div>`;
                });

                list.innerHTML = html;
            }

            function updateNotificationBadge(count) {
                const badge = document.getElementById('notificationBadge');
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }


            // toggleNotificationDropdown is now defined at the top of the script

            // toggleMainNotifications is now defined at the top of the script

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                // Close logbook reviews dropdown
                const logbookBell = document.querySelector('[onclick="toggleNotificationDropdown()"]');
                const logbookDropdown = document.getElementById('notificationDropdown');
                if (logbookBell && logbookDropdown) {
                    const bellParent = logbookBell.closest('.notification-bell');
                    if (bellParent && !bellParent.contains(event.target)) {
                        logbookDropdown.style.display = 'none';
                    }
                }

                // Close main notifications dropdown
                const mainBell = document.getElementById('mainNotificationBell');
                const mainDropdown = document.getElementById('mainNotificationDropdown');
                if (mainBell && mainDropdown) {
                    if (!mainBell.contains(event.target) && !mainDropdown.contains(event.target)) {
                        mainDropdown.style.display = 'none';
                    }
                }
            });

            // Load pending logbooks for HOD approval
            function loadPendingLogbooks() {
                fetch('fetch_pending_logbooks.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                            updateNotificationList([]);
                            return;
                        }
                        updateNotificationList(data);
                        updateBadgeCount(data.length);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('notificationList').innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-exclamation-circle"></i>
                    <p>Error loading notifications</p>
                </div>
            `;
                    });
            }

            // Update notification list
            function updateNotificationList(logbooks) {
                const container = document.getElementById('notificationList');

                if (!logbooks || logbooks.length === 0) {
                    container.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-check-circle"></i>
                <p>No pending logbooks to review</p>
            </div>
        `;
                    return;
                }

                let html = '';
                logbooks.forEach(logbook => {
                    const statusClass = logbook.status === 'unread' ? 'unread' : '';
                    const bothApproved = logbook.supervisor_approved && logbook.technical_officer_approved;

                    html += `
            <div class="notification-item ${statusClass}" id="logbook-${logbook.id}">
                <div class="notification-title">
                    <strong>Logbook #${logbook.id}</strong> - ${logbook.student_name}
                </div>
                <div class="notification-meta">
                    <i class="bi bi-person-badge"></i> ${logbook.university_id}<br>
                    <i class="bi bi-receipt"></i> Reservation: ${logbook.reservation_code}<br>
                    <i class="bi bi-calendar"></i> Submitted: ${logbook.submitted_date}<br>
                    <i class="bi bi-image"></i> Photos: ${logbook.has_photos} image(s)
                </div>
                <div class="notification-meta" style="margin-top: 8px;">
                  <span style="display:inline-block;padding:4px 8px;background:${logbook.supervisor_approved === 1 ? '#d4edda' : logbook.supervisor_approved === 0 ? '#f8d7da' : '#fff3cd'};color:${logbook.supervisor_approved === 1 ? '#155724' : logbook.supervisor_approved === 0 ? '#842029' : '#856404'};border-radius:4px;font-size:12px;margin-right:5px;">
    <i class="bi ${logbook.supervisor_approved === 1 ? 'bi-check-circle' : logbook.supervisor_approved === 0 ? 'bi-x-circle' : 'bi-clock'}"></i> Supervisor: ${logbook.supervisor_approved === 1 ? 'Approved' : logbook.supervisor_approved === 0 ? 'Rejected' : 'Pending'}
</span>
<span style="display:inline-block;padding:4px 8px;background:${logbook.technical_officer_approved === 1 ? '#d4edda' : logbook.technical_officer_approved === 0 ? '#f8d7da' : '#fff3cd'};color:${logbook.technical_officer_approved === 1 ? '#155724' : logbook.technical_officer_approved === 0 ? '#842029' : '#856404'};border-radius:4px;font-size:12px;">
    <i class="bi ${logbook.technical_officer_approved === 1 ? 'bi-check-circle' : logbook.technical_officer_approved === 0 ? 'bi-x-circle' : 'bi-clock'}"></i> Tech Officer: ${logbook.technical_officer_approved === 1 ? 'Approved' : logbook.technical_officer_approved === 0 ? 'Rejected' : 'Pending'}
</span>
                </div>
                
                ${!bothApproved ? `
                <div style="background: #fff3cd; color: #856404; padding: 8px 12px; border-radius: 4px; font-size: 12px; margin: 8px 0;">
                    <i class="bi bi-info-circle"></i> Awaiting approval from Supervisor and/or Technical Officer
                </div>
                ` : ''}
                <div class="notification-actions justify-content-center">
                   
                    <button class="btn-view" onclick="viewLogbookDetailsHOD(${logbook.id})">
                        <i class="bi bi-eye"></i> View
                    </button>
                </div>
            </div>
        `;
                });
                container.innerHTML = html;
            }

            // Update badge count
            function updateBadgeCount(count) {
                const badge = document.getElementById('requestBadge');
                if (badge) {
                    badge.textContent = count;
                    badge.style.display = count > 0 ? 'inline-block' : 'none';
                }
            }

            // Show toast notification
            function showToast(message, type = 'info') {
                const toastId = 'toast_' + Date.now();
                const toastHTML = `
        <div id="${toastId}" class="toast-notification toast-${type} show">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;

                const toastContainer = document.body.appendChild(document.createElement('div'));
                toastContainer.innerHTML = toastHTML;

                // Auto-remove after 3 seconds
                setTimeout(() => {
                    const toast = document.getElementById(toastId);
                    if (toast) {
                        toast.remove();
                        toastContainer.remove();
                    }
                }, 3000);
            }

            // Approve logbook
            function approveLogbook(logbookId) {
                // Get the logbook item to check approval status from notification dropdown
                const logbookItem = document.getElementById(`logbook-${logbookId}`);
                const approveBtn = logbookItem?.querySelector('.btn-approve');

                // If called from notification dropdown and button is disabled, prevent action
                if (logbookItem && approveBtn && approveBtn.disabled) {
                    showToast('Cannot approve until both Supervisor and Technical Officer have approved', 'warning');
                    return;
                }

                if (!confirm('Are you sure you want to approve this logbook?')) return;

                console.log('Approving logbook:', logbookId);

                fetch('approve_logbook.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            logbook_id: logbookId,
                            action: 'approve'
                        })
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            // Close modal if it exists
                            const viewModal = document.getElementById('logbookViewModal');
                            if (viewModal) {
                                const modalInstance = bootstrap.Modal.getInstance(viewModal);
                                if (modalInstance) modalInstance.hide();
                            }

                            // Remove the item from notification list
                            const item = document.getElementById(`logbook-${logbookId}`);
                            if (item) item.remove();

                            // Update badge count
                            const remainingItems = document.querySelectorAll('.notification-item').length;
                            updateBadgeCount(remainingItems);

                            // Show success message
                            showToast(data.message || 'Logbook approved successfully!', 'success');

                            // Refresh list if no items left
                            if (remainingItems === 0) {
                                document.getElementById('notificationList').innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-check-circle"></i>
                        <p>No pending logbooks to review</p>
                    </div>
                `;
                            }
                        } else {
                            console.error('Approval failed:', data.message);
                            showToast(data.message || 'Error approving logbook', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Network error. Please try again.', 'error');
                    });
            }

            // Reject logbook
            let currentLogbookId = null;
            let confirmCallback = null;
            let rejectCallback = null;

            // Show confirmation modal
            function showConfirmModal(title, message, confirmText, confirmClass, onConfirm) {
                const modalElement = document.getElementById('confirmModal');
                const modalHeader = document.getElementById('confirmModalHeader');
                const modalTitle = document.getElementById('confirmModalTitle');
                const modalBody = document.getElementById('confirmModalBody');
                const confirmBtn = document.getElementById('confirmModalBtn');

                // Reset classes
                modalHeader.className = 'modal-header';
                confirmBtn.className = 'btn';

                // Set title and message
                modalTitle.innerHTML = `<i class="bi bi-question-circle me-2"></i>${title}`;
                modalBody.innerHTML = message;
                confirmBtn.innerHTML = `<i class="bi bi-check-circle me-2"></i>${confirmText}`;
                confirmBtn.classList.add(confirmClass);

                // Set callback
                confirmCallback = onConfirm;

                // Remove previous event listeners
                const newConfirmBtn = confirmBtn.cloneNode(true);
                confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

                newConfirmBtn.addEventListener('click', () => {
                    closeModalManually('confirmModal');
                    if (confirmCallback) confirmCallback();
                });

                // Manually show the modal
                const existingBackdrop = document.querySelector('.modal-backdrop');
                if (existingBackdrop) existingBackdrop.remove();

                modalElement.classList.add('show');
                modalElement.style.display = 'block';

                // Create modal backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.style.zIndex = '8999';
                document.body.appendChild(backdrop);

                // Prevent body scroll
                document.body.style.overflow = 'hidden';
                document.body.classList.add('modal-open');

                console.log('Confirmation modal shown manually');

                // Try Bootstrap as well if available
                if (typeof bootstrap !== 'undefined') {
                    try {
                        const modal = new bootstrap.Modal(modalElement, {
                            backdrop: false,
                            keyboard: false
                        });
                        modal.show();
                    } catch (e) {
                        console.log('Bootstrap modal backup called');
                    }
                }
            }


            // Show rejection reason modal
            function showRejectModal(logbookId) {
                // Get the logbook item to check approval status
                const logbookItem = document.getElementById(`logbook-${logbookId}`);
                const rejectBtn = logbookItem?.querySelector('.btn-reject');

                if (rejectBtn && rejectBtn.disabled) {
                    showToast('Cannot reject until both Supervisor and Technical Officer have approved', 'warning');
                    return;
                }

                console.log('showRejectModal called:', logbookId);
                currentLogbookId = logbookId;

                try {
                    const modalElement = document.getElementById('rejectReasonModal');
                    const textarea = document.getElementById('rejectionReason');

                    console.log('Modal element exists:', !!modalElement);
                    console.log('Textarea exists:', !!textarea);

                    if (!modalElement || !textarea) {
                        console.error('Modal or textarea not found');
                        const reason = prompt('Enter rejection reason:');
                        if (reason && reason.trim()) executeReject(logbookId, reason);
                        return;
                    }

                    // Clear textarea
                    textarea.value = '';
                    textarea.classList.remove('is-invalid');

                    // Remove any error div
                    const errorDiv = document.getElementById('reasonError');
                    if (errorDiv) errorDiv.remove();

                    // Manually show the modal
                    // Remove any existing backdrop
                    const existingBackdrop = document.querySelector('.modal-backdrop');
                    if (existingBackdrop) existingBackdrop.remove();

                    // Add show class to modal
                    modalElement.classList.add('show');
                    modalElement.style.display = 'block';

                    console.log('Modal element classes after manual add:', modalElement.className);
                    console.log('Modal display style:', window.getComputedStyle(modalElement).display);

                    // Create modal backdrop
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.style.zIndex = '8999';
                    document.body.appendChild(backdrop);

                    console.log('Backdrop created and appended');

                    // Prevent body scroll
                    document.body.style.overflow = 'hidden';
                    document.body.classList.add('modal-open');

                    // If Bootstrap is available, also try Bootstrap method
                    if (typeof bootstrap !== 'undefined') {
                        try {
                            const modal = new bootstrap.Modal(modalElement, {
                                backdrop: false, // We're handling backdrop manually
                                keyboard: false
                            });
                            modal.show();
                            console.log('Bootstrap modal also showed (fallback)');
                        } catch (e) {
                            console.log('Bootstrap modal show failed, but manual show succeeded:', e.message);
                        }
                    }

                } catch (error) {
                    console.error('Error in showRejectModal:', error);
                    const reason = prompt('Enter rejection reason:');
                    if (reason && reason.trim()) executeReject(logbookId, reason);
                }
            }

            // Confirm rejection
            function confirmReject() {
                const reason = document.getElementById('rejectionReason').value.trim();

                if (!reason) {
                    // Show error in modal
                    const textarea = document.getElementById('rejectionReason');
                    textarea.classList.add('is-invalid');

                    // Add error message if not exists
                    let errorDiv = document.getElementById('reasonError');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.id = 'reasonError';
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.innerHTML = '<i class="bi bi-exclamation-circle"></i> Please enter a reason for rejection';
                        textarea.parentNode.appendChild(errorDiv);
                    }

                    // Focus and shake animation
                    textarea.style.animation = 'shake 0.5s';
                    setTimeout(() => {
                        textarea.style.animation = '';
                    }, 500);

                    return;
                }

                // Close rejection modal manually
                closeModalManually('rejectReasonModal');

                // Show confirmation modal
                showConfirmModal(
                    'Reject Logbook',
                    '<div class="text-center"><i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 48px;"></i><p class="mt-3">Are you sure you want to reject this logbook?</p><p class="text-muted small">Reason: <strong>' + escapeHtml(reason) + '</strong></p><p class="text-danger small">This action cannot be undone.</p></div>',
                    'Yes, Reject',
                    'btn-danger',
                    () => {
                        executeReject(currentLogbookId, reason);
                    }
                );
            }

            // Helper function to close modals manually
            function closeModalManually(modalId) {
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    // Remove show class
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';

                    console.log('Modal closed manually:', modalId);

                    // Try Bootstrap method as fallback
                    if (typeof bootstrap !== 'undefined') {
                        try {
                            const instance = bootstrap.Modal.getInstance(modalElement);
                            if (instance) {
                                instance.hide();
                            }
                        } catch (e) {
                            console.log('Bootstrap modal hide not available, but manual close done');
                        }
                    }

                    // Remove backdrop
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }

                    // Restore body scroll
                    document.body.style.overflow = '';
                    document.body.classList.remove('modal-open');
                }
            }

            // Execute reject
            function executeReject(logbookId, reason) {
                // Show loading state
                // showToast('Processing rejection...', 'info');

                fetch('approve_logbook.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            logbook_id: logbookId,
                            action: 'reject',
                            reason: reason
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Close modal if it exists
                            const viewModal = document.getElementById('logbookViewModal');
                            if (viewModal) {
                                const modalInstance = bootstrap.Modal.getInstance(viewModal);
                                if (modalInstance) modalInstance.hide();
                            }

                            // Close rejection reason modal
                            const rejectModal = document.getElementById('rejectReasonModal');
                            if (rejectModal) {
                                const rejectModalInstance = bootstrap.Modal.getInstance(rejectModal);
                                if (rejectModalInstance) rejectModalInstance.hide();
                            }

                            // Remove the item from list
                            const item = document.getElementById(`logbook-${logbookId}`);
                            if (item) {
                                item.style.animation = 'slideOut 0.3s ease';
                                setTimeout(() => item.remove(), 300);
                            }

                            // Update badge count
                            const remainingItems = document.querySelectorAll('.notification-item').length;
                            updateBadgeCount(remainingItems);

                            // Show success message
                            showToast(data.message || 'Logbook rejected successfully', 'warning');

                            // Refresh list if no items left
                            if (remainingItems === 0) {
                                setTimeout(() => {
                                    document.getElementById('notificationList').innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-check-circle"></i>
                            <p>No pending logbooks to review</p>
                        </div>
                    `;
                                }, 300);
                            }
                        } else {
                            showToast(data.message || 'Error rejecting logbook', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Network error. Please try again.', 'error');
                    });
            }



            // Execute approve
            function executeApprove(logbookId) {
                // Show loading state
                //   showToast('Processing approval...', 'info');

                fetch('approve_logbook.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            logbook_id: logbookId,
                            action: 'approve'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the item from list with animation
                            const item = document.getElementById(`logbook-${logbookId}`);
                            if (item) {
                                item.style.animation = 'slideOut 0.3s ease';
                                setTimeout(() => item.remove(), 300);
                            }

                            // Update badge count
                            const remainingItems = document.querySelectorAll('.notification-item').length;
                            updateBadgeCount(remainingItems);

                            // Show success message
                            showToast(data.message || 'Logbook approved successfully!', 'success');

                            // Refresh list if no items left
                            if (remainingItems === 0) {
                                setTimeout(() => {
                                    document.getElementById('notificationList').innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-check-circle"></i>
                            <p>No pending logbooks to review</p>
                        </div>
                    `;
                                }, 300);
                            }
                        } else {
                            showToast(data.message || 'Error approving logbook', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Network error. Please try again.', 'error');
                    });
            }

            // Helper function to escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Updated reject function
            function rejectLogbook(logbookId) {
                //  alert('You will be prompted to enter a reason for rejection in the next step.');
                showRejectModal(logbookId);
            }

            // Add animation styles
            const style = document.createElement('style');
            style.textContent = `
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .is-invalid {
        border-color: #dc2626 !important;
    }
    
    .invalid-feedback {
        display: block !important;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
`;
            document.head.appendChild(style);

            // Optional: Clear rejection reason error when typing
            document.addEventListener('DOMContentLoaded', function() {
                const reasonTextarea = document.getElementById('rejectionReason');
                if (reasonTextarea) {
                    reasonTextarea.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                        const errorDiv = document.getElementById('reasonError');
                        if (errorDiv) errorDiv.remove();
                    });
                }
            });

            // View logbook details with approve/reject in modal



            function markOneRead(notifId, element, event) {
                // Stop propagation if triggered by button click
                if (event) event.stopPropagation();

                // Already read — do nothing
                if (!element.classList.contains('unread')) return;

                element.classList.remove('unread', 'approval');

                // Hide the mark read button
                const markBtn = element.querySelector('.btn-outline-secondary');
                if (markBtn) markBtn.remove();

                const formData = new FormData();
                formData.append('notification_id', notifId);

                fetch('../controllers/mark_notifications_read.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const currentCount = parseInt(document.getElementById('notificationBadge').textContent) || 0;
                            updateNotificationBadge(Math.max(0, currentCount - 1));

                            // Update the "X new" counter
                            const remaining = document.querySelectorAll('.notification-item.unread').length;
                            document.getElementById('notificationNewCount').textContent = remaining + ' new';
                        }
                    });
            }

            function markAllRead() {
                fetch('../controllers/mark_notifications_read.php', {
                        method: 'POST',
                        body: new FormData() // empty = mark all
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI
                            document.querySelectorAll('.notification-item').forEach(item => {
                                item.classList.remove('unread', 'approval');
                            });
                            updateNotificationBadge(0);
                            document.getElementById('notificationNewCount').textContent = '0 new';
                        }
                    });
            }

            // HOD: Approve equipment from notification
            function approveEquipmentFromNotif(notifId, event) {
                event.stopPropagation();

                // Replace confirm() with a toast-style confirmation
                showConfirmToast('Approve this equipment?', () => {
                    const btn = event.target.closest('button');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                    fetch('../controllers/get_equipment_from_notif.php?notif_id=' + notifId)
                        .then(r => r.json())
                        .then(data => {
                            if (!data.success) {
                                showError('Error: ' + data.message);
                                btn.disabled = false;
                                btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Approve';
                                return null;
                            }

                            // If already processed, just refresh and return
                            if (data.already_processed) {
                                showSuccess('Equipment approved!');
                                loadNotifications();
                                return null;
                            }

                            const formData = new FormData();
                            formData.append('equipment_id', data.equipment_id);
                            formData.append('action', 'approve');
                            formData.append('notif_id', notifId);

                            return fetch('../controllers/approve_equipment.php', {
                                method: 'POST',
                                body: formData
                            });
                        })
                        .then(r => r ? r.json() : null)
                        .then(data => {
                            if (!data) return;
                            if (data.success) {
                                showSuccess('Equipment approved successfully!');
                                loadNotifications();
                                if (document.getElementById('equipmentSection').style.display === 'block') {
                                    loadEquipmentWithUsage();
                                }
                                // Refresh page after 1.5 seconds to show all updates
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            } else {
                                showError('Error: ' + data.message);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            showError('Network error. Please try again.');
                        });
                });
            }

            // Add this helper function alongside your existing showSuccess/showError
            function showConfirmToast(message, onConfirm) {
                // Remove any existing confirm toast
                const existing = document.getElementById('confirmToast');
                if (existing) existing.remove();

                const toast = document.createElement('div');
                toast.id = 'confirmToast';
                toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        max-width: 340px;
        width: 90%;
        animation: slideInRight 0.3s ease;
        border-left: 4px solid #f59e0b;
    `;

                toast.innerHTML = `
        <style>
            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(50px); }
                to   { opacity: 1; transform: translateX(0); }
            }
        </style>
        <div style="display:flex; align-items:flex-start; gap:12px;">
            <i class="bi bi-question-circle-fill"
               style="color:#f59e0b; font-size:1.4rem; flex-shrink:0; margin-top:2px;"></i>
            <div style="flex:1;">
                <div style="font-weight:700; color:#1a1a1a; margin-bottom:4px;">Confirm Action</div>
                <div style="color:#555; font-size:0.9rem; margin-bottom:14px;">${message}</div>
                <div style="display:flex; gap:8px;">
                    <button id="confirmToastYes"
                        style="flex:1; background:linear-gradient(135deg,#22c55e,#16a34a);
                               color:white; border:none; padding:8px 0;
                               border-radius:8px; font-weight:600; cursor:pointer;
                               font-size:0.88rem;">
                        <i class="bi bi-check2 me-1"></i>Yes, Approve
                    </button>
                    <button id="confirmToastNo"
                        style="flex:1; background:#f1f5f9; color:#555;
                               border:none; padding:8px 0;
                               border-radius:8px; font-weight:600; cursor:pointer;
                               font-size:0.88rem;">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;

                document.body.appendChild(toast);

                document.getElementById('confirmToastYes').onclick = () => {
                    toast.remove();
                    onConfirm();
                };
                document.getElementById('confirmToastNo').onclick = () => {
                    toast.remove();
                };

                // Auto-dismiss after 10 seconds
                setTimeout(() => {
                    if (toast.parentNode) toast.remove();
                }, 10000);
            }

            // HOD: Reject equipment from notification
            function rejectEquipmentFromNotif(notifId, event) {
                event.stopPropagation();

                const reason = prompt('Reason for rejection (optional):');
                if (reason === null) return; // cancelled

                const btn = event.target.closest('button');
                btn.disabled = true;

                fetch('../controllers/get_equipment_from_notif.php?notif_id=' + notifId)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Error: ' + data.message);
                            btn.disabled = false;
                            return null;
                        }

                        // If already processed, just refresh
                        if (data.already_processed) {
                            alert('Equipment already processed!');
                            loadNotifications();
                            return null;
                        }

                        const formData = new FormData();
                        formData.append('equipment_id', data.equipment_id);
                        formData.append('action', 'reject');
                        formData.append('reason', reason);
                        formData.append('notif_id', notifId);

                        return fetch('../controllers/approve_equipment.php', {
                            method: 'POST',
                            body: formData
                        });
                    })
                    .then(r => r ? r.json() : null)
                    .then(data => {
                        if (!data) return;
                        if (data.success) {
                            alert('Equipment rejected.');
                            loadNotifications();
                            // Refresh page after 1.5 seconds to show all updates
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Network error');
                    });
            }

            // Helper: time ago
            function getTimeAgo(datetimeStr) {
                if (!datetimeStr) return 'Unknown time';
                const now = new Date();
                const past = new Date(datetimeStr.replace(' ', 'T'));
                const diff = Math.floor((now - past) / 1000);

                if (diff < 60) return 'Just now';
                if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
                if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
                if (diff < 604800) return Math.floor(diff / 86400) + ' day(s) ago';
                return past.toLocaleDateString();
            }

            // Auto-refresh badge every 30 seconds (even when dropdown is closed)
            function startNotificationPolling() {
                notificationPollInterval = setInterval(() => {
                    fetch('../controllers/fetch_notifications.php')
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) updateNotificationBadge(data.unread_count);
                        })
                        .catch(() => {}); // silently fail
                }, 30000);
            }

            // Auto-refresh pending logbooks badge every 30 seconds
            function startLogbookPolling() {
                setInterval(() => {
                    fetch('fetch_pending_logbooks.php')
                        .then(r => r.json())
                        .then(data => {
                            if (Array.isArray(data)) {
                                updateBadgeCount(data.length);
                            }
                        })
                        .catch(() => {}); // silently fail
                }, 30000);
            }

            // ========== PROFILE FUNCTIONS ==========
            function openProfileModal() {
                loadProfileData();
                const profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
                profileModal.show();
            }

            function loadProfileData() {
                // Load current user data into the modal
                const firstName = '<?php echo htmlspecialchars($_SESSION["user_first_name"] ?? ""); ?>';
                const lastName = '<?php echo htmlspecialchars($_SESSION["user_last_name"] ?? ""); ?>';
                const email = '<?php echo htmlspecialchars($_SESSION["user"]["email"] ?? ""); ?>';
                const mobile = '<?php echo htmlspecialchars($_SESSION["user"]["mobile"] ?? ""); ?>';
                const universityId = '<?php echo htmlspecialchars($_SESSION["user"]["university_id"] ?? ""); ?>';
                const imgPath = '<?php echo htmlspecialchars($_SESSION["img_path"] ?? ""); ?>';
                const fullName = '<?php echo htmlspecialchars(($_SESSION["user_first_name"] ?? "") . " " . ($_SESSION["user_last_name"] ?? "")); ?>';

                document.getElementById('firstName').value = firstName;
                document.getElementById('lastName').value = lastName;
                document.getElementById('email').value = email;
                document.getElementById('mobile').value = mobile;
                document.getElementById('universityId').value = universityId;

                // Reset file input
                document.getElementById('profileImageInput').value = '';

                // Set profile image - make sure it loads correctly
                const profilePreview = document.getElementById('profilePreview');
                if (imgPath && imgPath.trim() !== '') {
                    // imgPath is just the filename, construct full path going up from views folder
                    profilePreview.src = '../assets/profile_images/' + imgPath + '?t=' + new Date().getTime();
                } else {
                    profilePreview.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(fullName) + '&background=22c55e&color=fff&size=120';
                }
            }

            function handleProfileImageChange(input) {
                const file = input.files[0];
                if (file) {
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please select an image file (JPEG, PNG, GIF, or WebP)');
                        input.value = '';
                        return;
                    }

                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB');
                        input.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('profilePreview').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }

            function previewProfileImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('profilePreview').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }

            function saveHodProfile() {
                const form = document.getElementById('profileForm');
                const formData = new FormData(form);

                // Check if password needs to be changed
                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                // If user is trying to change password
                if (currentPassword || newPassword || confirmPassword) {
                    // Validate password fields
                    if (!currentPassword) {
                        alert('Please enter your current password');
                        return;
                    }
                    if (!newPassword) {
                        alert('Please enter a new password');
                        return;
                    }
                    if (!confirmPassword) {
                        alert('Please confirm your new password');
                        return;
                    }
                    if (newPassword !== confirmPassword) {
                        alert('New passwords do not match');
                        return;
                    }
                    if (newPassword.length < 8) {
                        alert('New password must be at least 8 characters long');
                        return;
                    }

                    formData.append('current_password', currentPassword);
                    formData.append('new_password', newPassword);
                }

                // Show loading state
                const saveBtn = event.target;
                const originalText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';

                fetch('../controllers/update_hod_profile.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;

                        if (data.success) {
                            alert('Profile updated successfully!' + (currentPassword ? ' Password changed.' : ''));

                            // Update session display
                            document.getElementById('userName').textContent = data.full_name;

                            // Update profile image if changed
                            if (data.img_path) {
                                document.querySelector('.profile-img').src = data.img_path + '?t=' + new Date().getTime();
                            }

                            // Close modal
                            const profileModal = bootstrap.Modal.getInstance(document.getElementById('profileModal'));
                            if (profileModal) {
                                profileModal.hide();
                            }

                            // Reload page or update session
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to update profile'));
                        }
                    })
                    .catch(error => {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;
                        console.error('Error:', error);
                        alert('An error occurred while saving your profile');
                    });
            }

            // Toggle password change section
            function togglePasswordSection() {
                const section = document.getElementById('passwordChangeSection');
                const btn = document.getElementById('passwordToggleBtn');

                if (section.style.display === 'none') {
                    section.style.display = 'block';
                    btn.innerHTML = '<i class="bi bi-lock me-1"></i>Hide Password Change';
                } else {
                    section.style.display = 'none';
                    btn.innerHTML = '<i class="bi bi-lock me-1"></i>Change Password';
                    // Clear password fields when closing
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmPassword').value = '';
                }
            }

            // ========== INIT ==========
            // document.addEventListener('DOMContentLoaded', function() {

            // });
        </script>

        <!-- Add this before the closing </body> tag -->
        <!-- Notification Dropdown -->
        <!-- Notification Dropdown -->

        <!-- <div class="notification-dropdown" id="notificationDropdown">
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
        </div> -->




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
                    <!-- <div class="email-format-section">
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
                    </div> -->

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


        <div class="modal fade" id="rejectReasonModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-x-circle me-2"></i>Reject Logbook
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeModalManually('rejectReasonModal')"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Please provide reason for rejection:</label>
                            <textarea id="rejectionReason" class="form-control" rows="4"
                                placeholder="Enter detailed reason why this logbook is being rejected..."></textarea>
                            <small class="text-muted">This reason will be sent to the student.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModalManually('rejectReasonModal')">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmReject()">
                            <i class="bi bi-check-circle"></i> Confirm Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" id="confirmModalHeader">
                        <h5 class="modal-title" id="confirmModalTitle">
                            <i class="bi bi-question-circle me-2"></i>Confirm Action
                        </h5>
                        <button type="button" class="btn-close" onclick="closeModalManually('confirmModal')"></button>
                    </div>
                    <div class="modal-body" id="confirmModalBody">
                        Are you sure you want to perform this action?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModalManually('confirmModal')">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="button" class="btn" id="confirmModalBtn">
                            <i class="bi bi-check-circle"></i> Confirm
                        </button>
                    </div>
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
<?php
declare(strict_types=1);
session_start();

require_once '../config/database.php';

// Check if supervisor is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_GET['id'] ?? 0;

if (!$student_id) {
    header('Location: dashboard.php');
    exit;
}

// Get student details
$query = "SELECT id, university_id, firstname, lastname, email, phone, 
                 joindate, status, who_approved as supervisor_id
          FROM lab_user 
          WHERE id = ? AND who_approved = ?";
$result = Database::search($query, "ii", [$student_id, $_SESSION['user_id']]);

if ($result === false || $result->num_rows === 0) {
    header('Location: dashboard.php?error=notfound');
    exit;
}

$student = $result->fetch_assoc();
$isPending = $student['status'] == 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        .status-accepted {
            background-color: #28a745;
            color: #fff;
        }
        .btn-remove {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }
        .btn-remove:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-person-badge me-2"></i>
                            Student Details
                        </h4>
                        <a href="javascript:void(0)" onclick="window.close()" class="btn btn-light btn-sm">
                            <i class="bi bi-x-lg"></i> Close
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Status Badge -->
                        <div class="text-end mb-3">
                            <span class="status-badge <?php echo $isPending ? 'status-pending' : 'status-accepted'; ?>">
                                <i class="bi <?php echo $isPending ? 'bi-hourglass-split' : 'bi-check-circle-fill'; ?> me-1"></i>
                                <?php echo $isPending ? 'Pending Approval' : 'Accepted'; ?>
                            </span>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">University ID:</div>
                            <div class="col-md-8"><?php echo htmlspecialchars($student['university_id']); ?></div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Full Name:</div>
                            <div class="col-md-8">
                                <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Email:</div>
                            <div class="col-md-8">
                                <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>">
                                    <?php echo htmlspecialchars($student['email']); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Phone:</div>
                            <div class="col-md-8">
                                <?php echo htmlspecialchars($student['phone'] ?? 'Not provided'); ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Request Date:</div>
                            <div class="col-md-8">
                                <?php echo date('F d, Y h:i A', strtotime($student['joindate'])); ?>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex gap-2 justify-content-end">
                            <?php if ($isPending): ?>
                                <button class="btn-remove" onclick="removeStudent(<?php echo $student['id']; ?>)" 
                                        title="Reject this request">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                                <button class="btn-accept" onclick="acceptStudent(<?php echo $student['id']; ?>, 'view')">
                                    <i class="bi bi-check-circle"></i> Accept Request
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="bi bi-check-circle-fill"></i> Already Accepted
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-secondary" onclick="window.close()">
                                <i class="bi bi-x-circle"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Function to accept student
    function acceptStudent(studentId, source = 'view') {
        if (!confirm('Accept this student account request?')) return;
        
        fetch('accept_student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ student_id: studentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Student accepted successfully!');
                
                // Update UI
                const statusBadge = document.querySelector('.status-badge');
                statusBadge.className = 'status-badge status-accepted';
                statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Accepted';
                
                // Disable accept and remove buttons
                const acceptBtn = document.querySelector('.btn-accept');
                const removeBtn = document.querySelector('.btn-remove');
                
                if (acceptBtn) {
                    acceptBtn.disabled = true;
                    acceptBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Accepted';
                    acceptBtn.classList.remove('btn-accept');
                    acceptBtn.classList.add('btn-secondary');
                }
                
                if (removeBtn) {
                    removeBtn.disabled = true;
                    removeBtn.style.opacity = '0.5';
                }
                
                // Add success message
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success mt-3';
                successDiv.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Student has been accepted.';
                
                const cardBody = document.querySelector('.card-body');
                cardBody.insertBefore(successDiv, cardBody.querySelector('hr'));
                
                // Refresh parent window
                if (window.opener && !window.opener.closed) {
                    window.opener.refreshNotifications();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error accepting student');
        });
    }
    
    // Function to remove/reject student
    function removeStudent(studentId) {
        if (!confirm('Are you sure you want to remove this student request? This action cannot be undone.')) return;
        
        fetch('remove_student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ student_id: studentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Student request removed successfully!');
                
                // Refresh parent and close
                if (window.opener && !window.opener.closed) {
                    window.opener.refreshNotifications();
                }
                window.close();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing student');
        });
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
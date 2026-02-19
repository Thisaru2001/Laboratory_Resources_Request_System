<?php
session_start();
include "./config/database.php";

// CSRF token for AJAX login
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$university_id = $_COOKIE["university_id"] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Microbiology Lab System - Login</title>

<link rel="stylesheet" href="./assets/css/signin.css">
<link rel="icon" type="image/svg+xml" href="./assets/resources/flask.svg">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>
<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0">
      <div class="modal-body text-center p-4">
        <p id="modalMessage" class="mb-4"></p>
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>


<div class="auth-card">

    <!-- Left Image -->
    <div class="left-image d-none d-md-block">
        <img src="./assets/resources/signin.png" alt="Lab Image">
    </div>

    <!-- Right Form -->
    <div class="auth-form">

        <h3 class="brand fw-bold text-center mb-1">
            <i class="bi bi-flask text-success"></i> Microbiology Lab
        </h3>
        <p class="text-center text-muted mb-4">
            University of Kelaniya / Faculty of Science
        </p>

        <h2 class="brand text-center fw-bold mb-3">Sign In</h2>

        <div class="tab-content">

            <!-- LOGIN -->
         <div class="tab-pane fade show active" id="login">
    <form id="loginForm" onsubmit="return false;"> <!-- prevent default submit -->

        <div class="mb-3">
            <label class="form-label">University ID</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input id="university_id" name="university_id"
                       value="<?= htmlspecialchars($university_id) ?>"
                       type="text" class="form-control" placeholder="University ID" required>
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" id="password" name="password"
                       class="form-control" placeholder="Password" required>
            </div>
        </div>

        <!-- Remember Me Checkbox + Forgot Password -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember_me">
                <label class="form-check-label" for="remember_me">
                    Remember Me
                </label>
            </div>
            <a href="#" onclick="forgotPassword();" class="small">Forgot password?</a>
        </div>

        <div class="d-grid mb-2">
            <!-- Sign In Button -->
            <button type="button" onclick="signin();" class="btn btn-success btn-lg">Sign In</button>

            <!-- Sign Up Link Text -->
            <p class="text-center mt-3 mb-0">
                Don't have an account? 
                <a href="#" class="link-success text-decoration-none">Sign up</a>
            </p>
        </div>

        <!-- CSRF token hidden for AJAX -->
        <input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    </form>
</div>


        </div>

    </div>
</div>

<script src="./assets/script/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

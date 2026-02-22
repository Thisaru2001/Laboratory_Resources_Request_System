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

<!-- Google reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --primary-green: #22c55e;
        --dark-green: #16a34a;
        --darker-green: #15803d;
    }

    html, body {
        height: 100%;
        overflow: hidden;
    }

    body {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        font-family: 'Inter', 'Segoe UI', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        padding: 20px;
    }

    /* Animated background elements */
    body::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        top: -100px;
        right: -100px;
        animation: float 20s infinite ease-in-out;
    }

    body::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        bottom: -150px;
        left: -150px;
        animation: float 25s infinite ease-in-out reverse;
    }

    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        25% { transform: translate(50px, 50px) rotate(90deg); }
        50% { transform: translate(100px, 0) rotate(180deg); }
        75% { transform: translate(50px, -50px) rotate(270deg); }
    }

    .auth-card {
        background: white;
        border-radius: 40px;
        box-shadow: 0 30px 70px rgba(0, 0, 0, 0.3);
        display: flex;
        overflow: hidden;
        width: 100%;
        max-width: 1100px;
        height: 600px;
        position: relative;
        z-index: 10;
        animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        margin: 0 auto;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .left-image {
        flex: 1;
        background: linear-gradient(135deg, #166534 0%, #14532d 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .left-image::before {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
        top: -50%;
        left: -50%;
        animation: rotate 20s infinite linear;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .left-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3));
        animation: floatImage 6s ease-in-out infinite;
        position: relative;
        z-index: 2;
    }

    @keyframes floatImage {
        0%, 100% { transform: translateY(0) scale(1.02); }
        50% { transform: translateY(-10px) scale(1.03); }
    }

    .auth-form {
        flex: 1;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 0;
        overflow: hidden;
    }

    .form-container {
        width: 100%;
        max-width: 380px;
        margin: 0 auto;
        padding: 20px;
    }

    .brand {
        color: #166534;
        font-weight: 700;
        letter-spacing: -0.5px;
        text-align: center;
        margin-bottom: 5px;
        font-size: 1.4rem;
    }

    .brand i {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.8rem;
        margin-right: 6px;
        animation: spin 10s infinite linear;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .subtitle {
        color: #6b7280;
        font-size: 0.85rem;
        text-align: center;
        margin-bottom: 15px;
    }

    h2.brand {
        font-size: 1.6rem;
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 5px;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .input-group {
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        background: #f9fafb;
        height: 48px;
    }

    .input-group:focus-within {
        border-color: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
    }

    .input-group-text {
        background: #f9fafb;
        border: none;
        color: #22c55e;
        font-size: 1rem;
        padding: 0 15px;
    }

    .form-control {
        border: none;
        padding: 0 15px;
        font-size: 0.95rem;
        background: #f9fafb;
        height: 100%;
    }

    .form-control:focus {
        box-shadow: none;
        outline: none;
        background: #f9fafb;
    }

    .form-control::placeholder {
        color: #9ca3af;
        font-weight: 300;
        font-size: 0.9rem;
    }

    .form-check-input {
        border: 2px solid #d1d5db;
        cursor: pointer;
        width: 1rem;
        height: 1rem;
        margin-top: 2px;
    }

    .form-check-input:checked {
        background-color: #22c55e;
        border-color: #22c55e;
    }

    .form-check-label {
        color: #4b5563;
        font-weight: 500;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .btn-success {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border: none;
        padding: 12px 20px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(34, 197, 94, 0.3);
        color: white;
        width: 100%;
        height: 48px;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
        background: linear-gradient(135deg, #16a34a, #22c55e);
    }

    .small {
        color: #22c55e;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .small:hover {
        color: #16a34a;
        text-decoration: underline;
    }

    .link-success {
        color: #22c55e;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .link-success:hover {
        color: #16a34a;
        text-decoration: underline;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #166534, #14532d);
        color: white;
        padding: 15px 20px;
        border: none;
    }

    .modal-header .modal-title {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .modal-header .btn-close {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        padding: 6px;
        font-size: 0.8rem;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 10px 20px 20px;
        border: none;
    }

    /* reCAPTCHA container */
    .g-recaptcha {
        margin: 10px 0;
        display: flex;
        justify-content: center;
        transform: scale(0.9);
    }

    /* Loading spinner */
    .spinner-border {
        width: 1rem;
        height: 1rem;
        margin-right: 6px;
    }

    /* Error message */
    .text-danger {
        color: #ef4444 !important;
        font-size: 0.8rem;
        margin-top: 5px;
    }

    /* Remove any extra lines */
    .auth-form::before,
    .auth-form::after {
        display: none;
    }

    /* Responsive Design */
    @media (max-width: 991px) {
        .auth-card {
            height: auto;
            min-height: 500px;
            flex-direction: column;
        }
        
        .left-image {
            display: none;
        }
        
        .auth-form {
            height: auto;
            min-height: 500px;
            padding: 20px;
        }
        
        .form-container {
            max-width: 350px;
        }
    }

    @media (max-width: 576px) {
        body {
            padding: 10px;
        }
        
        .auth-card {
            height: auto;
            min-height: 450px;
            border-radius: 30px;
        }
        
        .auth-form {
            padding: 15px;
        }
        
        .form-container {
            max-width: 100%;
            padding: 10px;
        }
        
        .brand {
            font-size: 1.2rem;
        }
        
        .brand i {
            font-size: 1.5rem;
        }
        
        h2.brand {
            font-size: 1.4rem;
            margin-bottom: 15px;
        }
        
        .btn-success {
            height: 44px;
            font-size: 0.95rem;
        }
    }

    @media (max-width: 380px) {
        .g-recaptcha {
            transform: scale(0.8);
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        .small {
            align-self: flex-end;
        }
    }

    /* Remove any animation that might cause layout shifts */
    .btn-success {
        animation: none;
    }
</style>

<!-- Keep original favicon -->
<link rel="icon" type="image/svg+xml" href="./assets/resources/flask.svg">

</head>
<body>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem; margin-bottom: 15px;"></i>
        <p id="modalMessage" class="mb-3"></p>
        <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- reCAPTCHA Modal -->
<div class="modal fade" id="recaptchaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-shield-check me-2"></i>
                    Verification
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted mb-2 small">Complete security check</p>
                <div class="g-recaptcha mb-2" data-sitekey="6LcM0HMsAAAAAGiNWLW0WX5DFTSKF4F8mlQdX5SO" data-callback="recaptchaSuccess"></div>
                <div id="recaptchaResponse" class="text-danger small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="verifyBtn" onclick="verifyRecaptcha()" disabled>Verify</button>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-key me-2"></i>
                    Reset Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="reset_university_id" class="form-control" placeholder="University ID">
                </div>
                <div class="g-recaptcha mb-2" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" data-callback="resetRecaptchaCallback"></div>
                <div id="resetRecaptchaResponse" class="text-danger small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" onclick="sendResetLink()">Send Reset Link</button>
            </div>
        </div>
    </div>
</div>

<!-- Sign Up Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>
                    Contact Admin
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                    <i class="bi bi-envelope text-success me-3 fs-5"></i>
                    <span class="small">lab.admin@kln.ac.lk</span>
                </div>
                <div class="d-flex align-items-center p-2 bg-light rounded">
                    <i class="bi bi-telephone text-success me-3 fs-5"></i>
                    <span class="small">+94 11 290 3214</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="auth-card">

    <!-- Left Image -->
    <div class="left-image d-none d-md-block">
        <img src="./assets/resources/signin.png" alt="Lab Image">
    </div>

    <!-- Right Form - Perfectly Fitted -->
    <div class="auth-form">
        <div class="form-container">
            <h3 class="brand">
                <i class="bi bi-flask"></i> Microbiology Lab
            </h3>
            <p class="subtitle">
                University of Kelaniya
            </p>

            <h2 class="brand">Sign In</h2>

            <form id="loginForm" onsubmit="return false;">
                <div class="mb-3">
                    <label class="form-label">University ID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input id="university_id" name="university_id"
                               value="<?= htmlspecialchars($university_id) ?>"
                               type="text" class="form-control" placeholder="Enter your ID" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me">
                        <label class="form-check-label" for="remember_me">
                            Remember Me
                        </label>
                    </div>
                    <a href="#" onclick="showForgotPassword(); return false;" class="small">Forgot password?</a>
                </div>

                <div class="d-grid">
                    <button type="button" onclick="showRecaptchaModal();" class="btn btn-success" id="signinBtn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        <span id="loginText">Sign In</span>
                    </button>

                    <p class="text-center mt-3 mb-0 small">
                        Don't have an account? 
                        <a href="#" class="link-success" onclick="showSignUp(); return false;">Sign up</a>
                    </p>
                </div>

                <input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            </form>

            <div class="text-center mt-3">
                <small class="text-muted" style="font-size: 0.7rem;">© 2026 Microbiology Lab</small>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="./assets/script/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>



</body>
</html>
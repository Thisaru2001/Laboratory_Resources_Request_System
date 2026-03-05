<?php
session_start();
include "./config/database.php";

// Get cookies if they exist
$university_id = $_COOKIE["university_id"] ?? '';
$password = $_COOKIE["password"] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Microbiology Lab System - Login</title>

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

        html,
        body {
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

        /* Laboratory Decorative Elements - All positioned outside */
        .lab-microscope {
            position: fixed;
            font-size: 150px;
            opacity: 0.2;
            top: 10px;
            right: 20px;
            animation: float 15s infinite ease-in-out;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        .lab-test-tube {
            position: fixed;
            font-size: 180px;
            opacity: 0.2;
            bottom: 10px;
            left: 20px;
            animation: floatReverse 18s infinite ease-in-out;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        .lab-alembic {
            position: fixed;
            font-size: 120px;
            opacity: 0.15;
            top: 30%;
            left: 5%;
            animation: floatSlow 20s infinite ease-in-out;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        .lab-petri {
            position: fixed;
            font-size: 140px;
            opacity: 0.15;
            bottom: 20%;
            right: 5%;
            animation: floatMedium 22s infinite ease-in-out;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        .lab-dna {
            position: fixed;
            font-size: 130px;
            opacity: 0.12;
            top: 60%;
            left: 10%;
            animation: rotate 25s infinite linear;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        .lab-microbe {
            position: fixed;
            font-size: 110px;
            opacity: 0.12;
            top: 15%;
            left: 20%;
            animation: bounce 8s infinite ease-in-out;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        .lab-flask {
            position: fixed;
            font-size: 160px;
            opacity: 0.1;
            top: 70%;
            right: 15%;
            animation: floatFlask 19s infinite ease-in-out;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        .lab-beaker {
            position: fixed;
            font-size: 140px;
            opacity: 0.1;
            top: 45%;
            right: 25%;
            animation: floatBeaker 21s infinite ease-in-out;
            z-index: 1;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        /* Bubble animations */
        .bubble-container {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
        }

        .bubble {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }

        .bubble1 {
            width: 40px;
            height: 40px;
            bottom: 10%;
            left: 15%;
            animation: bubbleUp 12s infinite ease-out;
        }

        .bubble2 {
            width: 25px;
            height: 25px;
            bottom: 20%;
            right: 25%;
            animation: bubbleUp 8s infinite ease-out;
            animation-delay: 2s;
        }

        .bubble3 {
            width: 35px;
            height: 35px;
            bottom: 5%;
            left: 40%;
            animation: bubbleUp 10s infinite ease-out;
            animation-delay: 4s;
        }

        .bubble4 {
            width: 20px;
            height: 20px;
            bottom: 30%;
            right: 40%;
            animation: bubbleUp 9s infinite ease-out;
            animation-delay: 1s;
        }

        .bubble5 {
            width: 30px;
            height: 30px;
            bottom: 15%;
            left: 70%;
            animation: bubbleUp 11s infinite ease-out;
            animation-delay: 3s;
        }

        .bubble6 {
            width: 45px;
            height: 45px;
            bottom: 25%;
            right: 15%;
            animation: bubbleUp 13s infinite ease-out;
            animation-delay: 5s;
        }

        /* Animation Keyframes */
        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            25% {
                transform: translate(30px, -30px) rotate(10deg);
            }

            50% {
                transform: translate(50px, 0) rotate(0deg);
            }

            75% {
                transform: translate(20px, 30px) rotate(-10deg);
            }
        }

        @keyframes floatReverse {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            25% {
                transform: translate(-30px, 30px) rotate(-10deg);
            }

            50% {
                transform: translate(-50px, 0) rotate(0deg);
            }

            75% {
                transform: translate(-20px, -30px) rotate(10deg);
            }
        }

        @keyframes floatSlow {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(40px, -20px) rotate(15deg);
            }

            66% {
                transform: translate(-20px, 40px) rotate(-15deg);
            }
        }

        @keyframes floatMedium {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            25% {
                transform: translate(-40px, 30px) scale(1.1);
            }

            50% {
                transform: translate(30px, -40px) scale(0.9);
            }

            75% {
                transform: translate(-30px, -30px) scale(1.05);
            }
        }

        @keyframes floatFlask {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            30% {
                transform: translateY(-40px) rotate(15deg);
            }

            60% {
                transform: translateY(20px) rotate(-15deg);
            }
        }

        @keyframes floatBeaker {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            40% {
                transform: translateY(-50px) scale(1.1);
            }

            80% {
                transform: translateY(30px) scale(0.95);
            }
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-60px) scale(1.2);
            }
        }

        @keyframes bubbleUp {
            0% {
                transform: translateY(0) scale(1);
                opacity: 0.3;
            }

            50% {
                transform: translateY(-150px) scale(1.5);
                opacity: 0.5;
            }

            100% {
                transform: translateY(-300px) scale(0.5);
                opacity: 0;
            }
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
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
            top: -50%;
            left: -50%;
            animation: rotate 20s infinite linear;
        }

        .left-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.3));
            animation: floatImage 6s ease-in-out infinite;
            position: relative;
            z-index: 2;
        }

        @keyframes floatImage {

            0%,
            100% {
                transform: translateY(0) scale(1.02);
            }

            50% {
                transform: translateY(-10px) scale(1.03);
            }
        }

        .auth-form {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 500px;
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

        h2.brand {
            font-size: 1.6rem;
            margin-bottom: 20px;
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
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .subtitle {
            color: #6b7280;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 15px;
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

        /* Message/Error div */
        #msgdiv1 {
            margin-bottom: 15px;
        }

        .alert {
            border-radius: 12px;
            font-size: 0.9rem;
            padding: 10px 15px;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 24px;
            border: none;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            z-index: 1050;
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

        /* Password show button */
        .input-group .btn-outline-secondary {
            border: 2px solid #e5e7eb;
            border-left: none;
            background: #f9fafb;
            color: #22c55e;
            font-weight: 500;
        }

        .input-group .btn-outline-secondary:hover {
            background: #22c55e;
            color: white;
            border-color: #22c55e;
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
                min-height: 500px;
                padding: 20px;
            }

            .form-container {
                max-width: 350px;
            }
        }

        @media (max-width: 768px) {

            .lab-microscope,
            .lab-test-tube,
            .lab-alembic,
            .lab-petri,
            .lab-dna,
            .lab-microbe,
            .lab-flask,
            .lab-beaker {
                font-size: 80px;
                opacity: 0.15;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .auth-card {
                min-height: 450px;
            }

            .auth-form {
                padding: 15px;
                min-height: 450px;
            }

            .form-container {
                max-width: 100%;
                padding: 10px;
            }

            .brand {
                font-size: 1.2rem;
            }

            h2.brand {
                font-size: 1.4rem;
                margin-bottom: 15px;
            }

            .brand i {
                font-size: 1.5rem;
            }

            .btn-success {
                height: 44px;
                font-size: 0.95rem;
            }

            .lab-microscope,
            .lab-test-tube,
            .lab-alembic,
            .lab-petri,
            .lab-dna,
            .lab-microbe,
            .lab-flask,
            .lab-beaker {
                font-size: 60px;
            }
        }

        @media (max-width: 380px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .small {
                align-self: flex-end;
            }

            .lab-microscope,
            .lab-test-tube,
            .lab-alembic,
            .lab-petri,
            .lab-dna,
            .lab-microbe,
            .lab-flask,
            .lab-beaker {
                font-size: 50px;
            }
        }
    </style>

    <!-- Keep original favicon -->
    <link rel="icon" type="image/svg+xml" href="./assets/resources/flask.svg">

</head>

<body>
    <!-- Laboratory Decorative Elements - All positioned OUTSIDE the card -->
    <div class="lab-microscope">🔬</div>
    <div class="lab-test-tube">🧪</div>
    <div class="lab-alembic">⚗️</div>
    <div class="lab-petri">🧫</div>
    <div class="lab-dna">🧬</div>
    <div class="lab-microbe">🦠</div>
    <div class="lab-flask">🧪</div>
    <div class="lab-beaker">🥼</div>

    <!-- Bubble Container -->
    <div class="bubble-container">
        <div class="bubble bubble1"></div>
        <div class="bubble bubble2"></div>
        <div class="bubble bubble3"></div>
        <div class="bubble bubble4"></div>
        <div class="bubble bubble5"></div>
        <div class="bubble bubble6"></div>
    </div>

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

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="fpmodal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-key me-2"></i>Forgot Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="np" placeholder="Enter new password">
                                <button class="btn btn-outline-secondary" type="button" onclick="showPassword1()" id="npb">Show</button>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Re-type Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="rnp" placeholder="Re-type password">
                                <button class="btn btn-outline-secondary" type="button" onclick="showPassword2()" id="rnpb">Show</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Verification Code</label>
                            <input type="text" class="form-control" id="vcode" placeholder="Enter verification code">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="resetPassword()">
                        <i class="bi bi-check-circle me-2"></i>Reset Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Auth Card -->
    <div class="auth-card">
        <!-- Left Image -->
        <div class="left-image d-none d-md-block">
            <img src="./assets/resources/signin.png" alt="Lab Image">
        </div>

        <!-- Right Form -->
        <div class="auth-form">
            <div class="form-container">
                <h3 class="brand">
                    <i class="bi bi-flask"></i> Microbiology Lab
                </h3>
                <p class="subtitle">
                    University of Kelaniya
                </p>

                <h2 class="brand">Sign In</h2>

                <!-- Error Message Div -->
                <div class="col-12 d-none" id="msgdiv1">
                    <div class="alert alert-danger" role="alert" id="msg1"></div>
                </div>

                <form>
                    <div class="mb-3">
                        <label class="form-label">University ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input id="university_id"
                                value="<?= htmlspecialchars($university_id) ?>"
                                type="text"
                                class="form-control"
                                placeholder="Enter your ID"
                                oninput="this.value = this.value.toUpperCase()"
                                required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password"
                                id="password"
                                value="<?= htmlspecialchars($password) ?>"
                                class="form-control"
                                placeholder="Enter your password"
                                required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember_me" <?= $university_id ? 'checked' : '' ?>>
                            <label class="form-check-label" for="remember_me">
                                Remember Me
                            </label>
                        </div>
                        <a href="#" onclick="forgotPassword(); return false;" class="small">
                            <i class="bi bi-question-circle"></i> Forgot password?
                        </a>
                    </div>

                    <div class="d-grid">
                        <button type="button" onclick="signin();" class="btn btn-success" id="signinBtn">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            <span id="loginText">Sign In</span>
                        </button>

                        <p class="text-center mt-3 mb-0 small">
                            Don't have an account?
                            <a href="./views/signup.php" class="link-success">
                                <i class="bi bi-person-plus"></i> Sign up
                            </a>
                        </p>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <small class="text-muted" style="font-size: 0.7rem;">© 2026 Microbiology Lab</small>
                </div>
            </div>
        </div>
    </div>

  

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        

 // Global variable for forgot password modal
    var forgotPasswordModal;

    // Sign in function
    function signin() {
       
        var university_id = document.getElementById("university_id");
        var password = document.getElementById("password");
        var remember_me = document.getElementById("remember_me");

        if (!university_id.value || !password.value) {
            document.getElementById("msg1").innerHTML = "Please enter both University ID and Password";
            document.getElementById("msgdiv1").className = "d-block";
            return;
        }

        var form = new FormData();
        form.append("university_id", university_id.value);
        form.append("password", password.value);
        form.append("remember_me", remember_me.checked);

        var request = new XMLHttpRequest();

        request.onreadystatechange = function() {
            if (request.status == 200 && request.readyState == 4) {
                var response = request.responseText;

                if (response == "success") {
                  
                } else {
                     
                    document.getElementById("msg1").innerHTML = response;
                    document.getElementById("msgdiv1").className = "d-block";
                }
            }
        }

        request.open("POST", "/LRRS/controllers/signin_process.php", true);
        request.send(form);
    }

    // Forgot password function
    function forgotPassword() {
        var university_id = document.getElementById("university_id");

        if (!university_id.value) {
            document.getElementById("msg1").innerHTML = "Please enter your University ID";
            document.getElementById("msgdiv1").className = "d-block";
            return;
        }

        var request = new XMLHttpRequest();

        request.onreadystatechange = function() {
            if (request.status == 200 && request.readyState == 4) {
                var text = request.responseText;

                if (text == "Success") {
                    alert("Verification code has been sent successfully. Please check your email.");
                    var modal = document.getElementById("fpmodal");
                    forgotPasswordModal = new bootstrap.Modal(modal);
                    forgotPasswordModal.show();
                } else {
                    document.getElementById("msg1").innerHTML = text;
                    document.getElementById("msgdiv1").className = "d-block";
                }
            }
        }

        request.open("GET", "./functions/forgotPasswordProcess.php?university_id=" + university_id.value, true);
        request.send();
    }

    // Show/Hide password for first field
    function showPassword1() {
        var textfield = document.getElementById("np");
        var button = document.getElementById("npb");

        if (textfield.type == "password") {
            textfield.type = "text";
            button.innerHTML = "Hide";
        } else {
            textfield.type = "password";
            button.innerHTML = "Show";
        }
    }

    // Show/Hide password for second field
    function showPassword2() {
        var textfield = document.getElementById("rnp");
        var button = document.getElementById("rnpb");

        if (textfield.type == "password") {
            textfield.type = "text";
            button.innerHTML = "Hide";
        } else {
            textfield.type = "password";
            button.innerHTML = "Show";
        }
    }

    // Reset password function
    function resetPassword() {
        var university_id = document.getElementById("university_id");
        var newPassword = document.getElementById("np");
        var retypePassword = document.getElementById("rnp");
        var verification = document.getElementById("vcode");

        if (!newPassword.value || !retypePassword.value || !verification.value) {
            alert("Please fill in all fields");
            return;
        }

        if (newPassword.value !== retypePassword.value) {
            alert("Passwords do not match");
            return;
        }

        var form = new FormData();
        form.append("university_id", university_id.value);
        form.append("new_password", newPassword.value);
        form.append("retype_password", retypePassword.value);
        form.append("verification_code", verification.value);

        var request = new XMLHttpRequest();

        request.onreadystatechange = function() {
            if (request.status == 200 && request.readyState == 4) {
                var response = request.responseText;
                if (response == "success") {
                    alert("Password updated successfully.");
                    forgotPasswordModal.hide();
                    
                    // Clear the form
                    document.getElementById("np").value = "";
                    document.getElementById("rnp").value = "";
                    document.getElementById("vcode").value = "";
                } else {
                    alert(response);
                }
            }
        }

        request.open("POST", "./functions/resetPasswordProcess.php", true);
        request.send(form);
    }





// Main signup function
function createAccount(event) {
    const btn = event ? event.target : document.querySelector('button[onclick="createAccount()"]');
    const originalContent = btn ? btn.innerHTML : 'Create Account';

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating Account...';
    }

    const formData = new FormData();
    
    const firstName = document.getElementById('first_name')?.value.trim();
    const lastName = document.getElementById('last_name')?.value.trim();
    const universityId = document.getElementById('university_id')?.value.trim();
    const mobile = document.getElementById('mobile')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    const password = document.getElementById('password')?.value;
    const supervisorEmail = document.getElementById('supervisor_email')?.value.trim();

    if (!firstName || !lastName || !universityId || !mobile || !email || !password || !supervisorEmail) {
        showMessage('error', 'Please fill in all required fields');
        if (btn) resetButton(btn, originalContent);
        return;
    }

    formData.append('first_name', firstName);
    formData.append('last_name', lastName);
    formData.append('university_id', universityId);
    formData.append('mobile', mobile);
    formData.append('email', email);
    formData.append('password', password);
    formData.append('supervisor_email', supervisorEmail);

    if (typeof grecaptcha !== 'undefined') {
        const recaptchaResponse = grecaptcha.getResponse();
        if (recaptchaResponse) {
            formData.append('recaptcha_token', recaptchaResponse);
        }
    }

    const profileImageInput = document.getElementById('profileImageInput');
    if (profileImageInput && profileImageInput.files.length > 0) {
        const profileImage = profileImageInput.files[0];
        if (!profileImage.type.startsWith('image/')) {
            showMessage('error', 'Please select a valid image file');
            if (btn) resetButton(btn, originalContent);
            return;
        }
        if (profileImage.size > 6 * 1024 * 1024) {
            showMessage('error', 'Image size must be less than 6MB');
            if (btn) resetButton(btn, originalContent);
            return;
        }
        formData.append('profile_image', profileImage);
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../controllers/signup_process.php', true);
    xhr.timeout = 30000;

    xhr.onload = function() {
        if (btn) resetButton(btn, originalContent);
        
        if (xhr.status === 200 && xhr.readyState == 4) {
            try {
                console.log('Server response:', xhr.responseText);
                const response = JSON.parse(xhr.responseText);
               
                if (response.status_user == 'success') {
                    showMessage('success', response.message || 'Account created successfully!');
            
                    document.getElementById('first_name').value = '';
                    document.getElementById('last_name').value = '';
                    document.getElementById('university_id').value = '';
                    document.getElementById('mobile').value = '';
                    document.getElementById('email').value = '';
                    document.getElementById('password').value = '';
                    document.getElementById('supervisor_email').value = '';
                    
                    const profileInput = document.getElementById('profileImageInput');
                    if (profileInput) profileInput.value = '';
                    
                    const profilePreview = document.getElementById('profilePreview');
                    if (profilePreview) {
                        profilePreview.src = 'https://ui-avatars.com/api/?name=User&background=22c55e&color=fff&size=100';
                    }
                    
                    if (typeof grecaptcha !== 'undefined') {
                        grecaptcha.reset();
                    }
                    
                    setTimeout(() => {
                        window.location.href = '../index.php';
                    }, 3000);
                    
                } else {
                    showMessage('error', response.message || 'Account creation failed');
                    
                    if (response.fields && Array.isArray(response.fields)) {
                        highlightFields(response.fields);
                    }
                }
            } catch (e) {
                console.error('Parse error:', e);
                console.error('Raw response:', xhr.responseText);
                showMessage('error', 'Server error occurred. Please check console for details.');
            }
        } else if (xhr.status === 429) {
            showMessage('error', 'Too many attempts. Please try again later.');
        } else {
            showMessage('error', 'Connection error. Please check your internet and try again.');
        }
    };

    xhr.onerror = function() {
        if (btn) resetButton(btn, originalContent);
        showMessage('error', 'Network error. Please check your connection.');
    };

    xhr.ontimeout = function() {
        if (btn) resetButton(btn, originalContent);
        showMessage('error', 'Request timed out. Please try again.');
    };

    xhr.send(formData);
    
    return false;
}



// Show message function
function showMessage(type, message) {
    const modal = document.getElementById('messageModal');
    const modalMessage = document.getElementById('modalMessage');
    
    if (modal && modalMessage) {
        const modalTitle = document.getElementById('messageModalTitle');
        const modalHeader = modal.querySelector('.modal-header');
        
        if (type === 'success') {
            modalTitle.textContent = 'Success';
            modalHeader.className = 'modal-header bg-success text-white';
        } else {
            modalTitle.textContent = 'Error';
            modalHeader.className = 'modal-header bg-danger text-white';
        }
        
        modalMessage.textContent = message;
        
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
        
        modal.addEventListener('hidden.bs.modal', function () {
            const triggerButton = document.querySelector('button[onclick="createAccount(event)"]');
            if (triggerButton) {
                triggerButton.focus();
            }
        }, { once: true });
        
    } else {
        alert((type === 'success' ? 'Success: ' : 'Error: ') + message);
    }
}


// SHOW FORGOT PASSWORD MODAL - NO reCAPTCHA
function showForgotPassword() {
    // Get the University ID from the signin page input
    const universityId = document.getElementById('university_id').value.trim();
    
    if (!universityId) {
        showModal("Please enter your University ID first");
        return;
    }
    
    // Store it in the global variable
    resetUniversityId = universityId;
    
    // Display it in the modal
    const displayElement = document.getElementById('displayUniversityId');
    if (displayElement) {
        displayElement.textContent = universityId;
    }
    
    // Clear any previous error messages
    const errorDiv = document.getElementById('resetResponse');
    if (errorDiv) {
        errorDiv.innerHTML = '';
    }
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
    modal.show();
}


// Show modal function
function showModal(message) {
    const modalMessage = document.getElementById("modalMessage");
    if (modalMessage) {
        modalMessage.textContent = message;
    }

    const modal = new bootstrap.Modal(document.getElementById("messageModal"));
    modal.show();
}

// Helper function to highlight fields (if needed)
function highlightFields(fields) {
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.classList.add('is-invalid');
        }
    });
}

// Equipment Management Variables
let equipmentData = [
    {
        code: 'MIC-001',
        name: 'Microscope',
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png',
        available: 4,
        total: 8,
        maintenance: 2,
        usage: 75
    },
    {
        code: 'CEN-002',
        name: 'Centrifuge',
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941543.png',
        available: 3,
        total: 5,
        maintenance: 1,
        usage: 60
    },
    {
        code: 'INC-003',
        name: 'Incubator',
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941538.png',
        available: 2,
        total: 4,
        maintenance: 3,
        usage: 50
    },
    {
        code: 'AUT-004',
        name: 'Autoclave',
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941521.png',
        available: 6,
        total: 6,
        maintenance: 0,
        usage: 90
    },
    {
        code: 'PHM-005',
        name: 'pH Meter',
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941556.png',
        available: 3,
        total: 3,
        maintenance: 1,
        usage: 35
    },
    {
        code: 'WAT-006',
        name: 'Water Bath',
        image: 'https://cdn-icons-png.flaticon.com/512/2941/2941578.png',
        available: 5,
        total: 7,
        maintenance: 2,
        usage: 70
    }
];

// Search equipment
function searchEquipment() {
    const searchTerm = document.getElementById('equipmentSearch').value.toLowerCase();
    const tableBody = document.getElementById('equipmentTableBody');
    
    if (!tableBody) return;
    
    const filtered = equipmentData.filter(item => 
        item.code.toLowerCase().includes(searchTerm) ||
        item.name.toLowerCase().includes(searchTerm)
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
                    <button class="btn-edit" onclick="editEquipment('${item.code}')">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn-remove" onclick="removeEquipment('${item.code}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}


// Add new equipment
function addEquipment() {
    // In a real app, this would open a modal
    alert('Add Equipment functionality would open a form modal');
}

// Edit equipment
function editEquipment(code) {
    alert('Edit equipment: ' + code);
}

// Remove equipment
function removeEquipment(code) {
    if (confirm(`Are you sure you want to remove equipment ${code}?`)) {
        equipmentData = equipmentData.filter(item => item.code !== code);
        displayEquipmentTable(equipmentData);
        alert('Equipment removed successfully!');
    }
}

// Initialize equipment table on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('equipmentSection')) {
        displayEquipmentTable(equipmentData);
    }
});


    </script>

</body>

</html>
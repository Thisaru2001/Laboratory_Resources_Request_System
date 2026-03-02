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
            text-shadow: 0 0 20px rgba(0,0,0,0.3);
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
            text-shadow: 0 0 20px rgba(0,0,0,0.3);
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
            text-shadow: 0 0 20px rgba(0,0,0,0.3);
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
            text-shadow: 0 0 20px rgba(0,0,0,0.3);
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
            text-shadow: 0 0 20px rgba(0,0,0,0.3);
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
            text-shadow: 0 0 20px rgba(0,0,0,0.3);
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
            text-shadow: 0 0 20px rgba(0,0,0,0.3);
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
            text-shadow: 0 0 20px rgba(0,0,0,0.3);
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
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(30px, -30px) rotate(10deg); }
            50% { transform: translate(50px, 0) rotate(0deg); }
            75% { transform: translate(20px, 30px) rotate(-10deg); }
        }

        @keyframes floatReverse {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-30px, 30px) rotate(-10deg); }
            50% { transform: translate(-50px, 0) rotate(0deg); }
            75% { transform: translate(-20px, -30px) rotate(10deg); }
        }

        @keyframes floatSlow {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(40px, -20px) rotate(15deg); }
            66% { transform: translate(-20px, 40px) rotate(-15deg); }
        }

        @keyframes floatMedium {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(-40px, 30px) scale(1.1); }
            50% { transform: translate(30px, -40px) scale(0.9); }
            75% { transform: translate(-30px, -30px) scale(1.05); }
        }

        @keyframes floatFlask {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            30% { transform: translateY(-40px) rotate(15deg); }
            60% { transform: translateY(20px) rotate(-15deg); }
        }

        @keyframes floatBeaker {
            0%, 100% { transform: translateY(0) scale(1); }
            40% { transform: translateY(-50px) scale(1.1); }
            80% { transform: translateY(30px) scale(0.95); }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-60px) scale(1.2); }
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
            .lab-microscope, .lab-test-tube, .lab-alembic, .lab-petri, .lab-dna, .lab-microbe, .lab-flask, .lab-beaker {
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

            .lab-microscope, .lab-test-tube, .lab-alembic, .lab-petri, .lab-dna, .lab-microbe, .lab-flask, .lab-beaker {
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

            .lab-microscope, .lab-test-tube, .lab-alembic, .lab-petri, .lab-dna, .lab-microbe, .lab-flask, .lab-beaker {
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

     <script src="./assets/script/script.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
 
</body>

</html>
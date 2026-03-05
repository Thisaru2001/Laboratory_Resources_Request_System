<?php
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Microbiology Lab System - Sign Up</title>

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

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            25% {
                transform: translate(50px, 50px) rotate(90deg);
            }

            50% {
                transform: translate(100px, 0) rotate(180deg);
            }

            75% {
                transform: translate(50px, -50px) rotate(270deg);
            }
        }

        .auth-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.3);
            display: flex;
            overflow: hidden;
            width: 100%;
            max-width: 1200px;
            height: 680px;
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
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
            top: -50%;
            left: -50%;
            animation: rotate 20s infinite linear;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
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
            flex-direction: column;
            justify-content: flex-start;
            height: 100%;
            padding: 30px 35px;
            overflow-y: auto;
        }

        .auth-form::-webkit-scrollbar {
            width: 5px;
        }

        .auth-form::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .auth-form::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 10px;
        }

        .form-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .brand {
            color: #166534;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-align: center;
            font-size: 1.6rem;
        }

        .brand i {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.2rem;
            margin-right: 8px;
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
            font-size: 0.95rem;
            text-align: center;
            margin-bottom: 15px;
        }

        h2.brand {
            font-size: 2rem;
            margin-bottom: 25px;
            color: #166534;
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
            height: 45px;
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

        .row {
            margin-left: -8px;
            margin-right: -8px;
        }

        .col-md-6 {
            padding-left: 8px;
            padding-right: 8px;
        }

        /* Profile Image Section */
        .profile-section {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            margin: 15px 0;
            border: 2px dashed #e5e7eb;
            transition: all 0.3s;
        }

        .profile-section:hover {
            border-color: #22c55e;
            background: #f0fdf4;
        }

        #profilePreview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #22c55e;
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.2);
            transition: all 0.3s;
        }

        #profilePreview:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
        }

        .btn-outline-success {
            border: 2px solid #22c55e;
            color: #22c55e;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            background: white;
        }

        .btn-outline-success:hover {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border: none;
            padding: 14px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.3);
            color: white;
            height: 50px;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        .link-success {
            color: #22c55e;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .link-success:hover {
            color: #16a34a;
            text-decoration: underline;
        }

        .text-muted.small {
            color: #6b7280 !important;
            font-size: 0.8rem;
            margin-top: 10px;
        }

        /* Password strength indicator */
        #passwordStrength {
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
            transition: all 0.3s;
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
                max-height: 90vh;
                flex-direction: column;
            }

            .left-image {
                display: none;
            }

            .auth-form {
                height: auto;
                min-height: auto;
                padding: 25px;
            }

            .form-container {
                max-width: 100%;
            }

            .brand {
                font-size: 1.5rem;
            }

            h2.brand {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .auth-card {
                border-radius: 30px;
            }

            .auth-form {
                padding: 20px;
            }

            .brand {
                font-size: 1.4rem;
            }

            .brand i {
                font-size: 1.8rem;
            }

            h2.brand {
                font-size: 1.6rem;
                margin-bottom: 20px;
            }

            .subtitle {
                font-size: 0.85rem;
            }

            .btn-success {
                height: 46px;
                font-size: 0.95rem;
            }

            .row {
                flex-direction: column;
            }

            .col-md-6 {
                width: 100%;
                margin-bottom: 10px;
            }

            .col-md-6:last-child {
                margin-bottom: 0;
            }
        }

        @media (max-width: 380px) {
            .auth-form {
                padding: 15px;
            }

            .profile-section {
                padding: 15px;
            }

            #profilePreview {
                width: 80px;
                height: 80px;
            }

            .brand {
                font-size: 1.2rem;
            }

            h2.brand {
                font-size: 1.4rem;
            }
        }
    </style>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/resources/flask.svg">

</head>

<body>

    <div class="auth-card">

        <!-- Left Image -->
        <div class="left-image d-none d-md-block">
            <img src="../assets/resources/signin.png" alt="Lab Image">
        </div>

        <!-- Right Form -->
        <div class="auth-form">
            <div class="form-container">
                <!-- Brand -->
                <h3 class="brand fw-bold mb-1">
                    <i class="bi bi-flask"></i> Microbiology Lab
                </h3>
                <p class="subtitle">
                    University of Kelaniya • Faculty of Science
                </p>

                <h2 class="brand text-center fw-bold">Create Account</h2>

                <!-- SIGN UP FORM  -->
                <form onsubmit="createAccount(event); return false;">
                    <!-- First & Last Name -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" id="first_name" class="form-control" placeholder="John" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" id="last_name" class="form-control" placeholder="Doe" required>
                            </div>
                        </div>
                    </div>

                    <!-- University ID -->
                    <div class="mb-3">
                        <label class="form-label">University ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                            <input type="text" id="university_id" class="form-control" 
                                   placeholder="SC/####/###" 
                                   oninput="this.value = this.value.toUpperCase()"
                                   required>
                        </div>
                    </div>

                    <!-- Mobile -->
                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="tel" id="mobile" class="form-control" 
                                   placeholder="07########" 
                                   pattern="[0-9]{10}" 
                                   title="Please enter a valid 10-digit mobile number"
                                   required>
                        </div>
                    </div>

                    <!-- User Email -->
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="email" class="form-control" placeholder="john.doe@example.com" required>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="password" class="form-control" 
                                   placeholder="••••••••" 
                                   onkeyup="checkPasswordStrength()"
                                   required>
                        </div>
                        <small class="text-muted" id="passwordStrength">Use 8+ characters with letters & numbers</small>
                    </div>

                    <!-- Supervisor Email -->
                    <div class="mb-3">
                        <label class="form-label">Supervisor Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-check"></i></span>
                            <input type="email" id="supervisor_email" class="form-control" 
                                   placeholder="supervisor@kln.ac.lk" 
                                   pattern=".*@kln\.ac\.lk$"
                                   title="Please use your supervisor's @kln.ac.lk email"
                                   required>
                        </div>
                    </div>

                    <!-- Profile Image -->
                    <div class="profile-section text-center">
                        <label class="form-label d-block mb-3">Profile Image</label>

                        <!-- Default profile preview -->
                        <img id="profilePreview"
                            src="https://ui-avatars.com/api/?name=User&background=22c55e&color=fff&size=100"
                            class="rounded-circle mb-3"
                            style="width:100px;height:100px;object-fit:cover;border:3px solid #22c55e;">

                        <input type="file"
                            name="profile_image"
                            id="profileImageInput"
                            class="d-none"
                            accept="image/*">

                        <div>
                            <button type="button"
                                class="btn btn-outline-success"
                                onclick="document.getElementById('profileImageInput').click();">
                                <i class="bi bi-camera me-2"></i>Choose Image
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">Upload a profile photo (optional)</small>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-grid mb-2">
                        <button type="submit" class="btn btn-success" id="signupBtn">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </button>
                        <a href="../index.php"
                            class="link-success text-decoration-none mt-3 d-block text-center">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Already have an account? Sign In
                        </a>
                    </div>

                    <p class="text-muted text-center small mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Account will be activated after Supervisor approval
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="messageModalTitle">Success</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="modalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Image Preview Script
        document.getElementById('profileImageInput').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                showError('Please select an image file');
                return;
            }

            if (file.size > 6 * 1024 * 1024) {
                showError('File size should be less than 6MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

        // Password strength checker
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthEl = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthEl.innerHTML = 'Use 8+ characters with letters & numbers';
                strengthEl.style.color = '#6b7280';
            } else if (password.length < 8) {
                strengthEl.innerHTML = '❌ Too short (minimum 8 characters)';
                strengthEl.style.color = '#dc3545';
            } else if (!/[A-Z]/.test(password)) {
                strengthEl.innerHTML = '⚠️ Add an uppercase letter';
                strengthEl.style.color = '#f59e0b';
            } else if (!/[0-9]/.test(password)) {
                strengthEl.innerHTML = '⚠️ Add a number';
                strengthEl.style.color = '#f59e0b';
            } else {
                strengthEl.innerHTML = '✅ Strong password';
                strengthEl.style.color = '#22c55e';
            }
        }

        // Show success message
        function showSuccess(message) {
            document.getElementById('modalMessage').textContent = message;
            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();
        }

        // Show error message
        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            const modal = new bootstrap.Modal(document.getElementById('errorModal'));
            modal.show();
        }

        // Reset button state
        function resetButton(btn, originalContent) {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }

        // Main signup function
        function createAccount(event) {
            event.preventDefault();
            
            const btn = document.getElementById('signupBtn');
            const originalContent = btn.innerHTML;

            // Validate all fields
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const universityId = document.getElementById('university_id').value.trim();
            const mobile = document.getElementById('mobile').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const supervisorEmail = document.getElementById('supervisor_email').value.trim();

            // Basic validation
            if (!firstName || !lastName || !universityId || !mobile || !email || !password || !supervisorEmail) {
                showError('Please fill in all required fields');
                return;
            }

            // Mobile validation
            if (!/^[0-9]{10}$/.test(mobile)) {
                showError('Please enter a valid 10-digit mobile number');
                return;
            }

            // Password validation
            if (password.length < 8) {
                showError('Password must be at least 8 characters long');
                return;
            }

            if (!/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                showError('Password must contain at least one uppercase letter and one number');
                return;
            }

            // Supervisor email validation
            if (!supervisorEmail.endsWith('@kln.ac.lk')) {
                showError('Supervisor email must be a valid @kln.ac.lk address');
                return;
            }

            // Disable button and show loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating Account...';

            const formData = new FormData();
            formData.append('first_name', firstName);
            formData.append('last_name', lastName);
            formData.append('university_id', universityId);
            formData.append('mobile', mobile);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('supervisor_email', supervisorEmail);

            const profileImageInput = document.getElementById('profileImageInput');
            if (profileImageInput && profileImageInput.files.length > 0) {
                formData.append('profile_image', profileImageInput.files[0]);
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../controllers/signup_process.php', true);
            xhr.timeout = 30000;

            xhr.onload = function() {
                resetButton(btn, originalContent);
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                       
                        if (response.status_user === 'success') {
                            showSuccess(response.message || 'Account created successfully!');
                            
                            // Clear form
                            document.getElementById('first_name').value = '';
                            document.getElementById('last_name').value = '';
                            document.getElementById('university_id').value = '';
                            document.getElementById('mobile').value = '';
                            document.getElementById('email').value = '';
                            document.getElementById('password').value = '';
                            document.getElementById('supervisor_email').value = '';
                            
                            const profileInput = document.getElementById('profileImageInput');
                            if (profileInput) profileInput.value = '';
                            
                            document.getElementById('profilePreview').src = 'https://ui-avatars.com/api/?name=User&background=22c55e&color=fff&size=100';
                            
                            // Redirect after 3 seconds
                            setTimeout(() => {
                                window.location.href = '../index.php';
                            }, 3000);
                            
                        } else {
                            showError(response.message || 'Account creation failed');
                            
                            if (response.fields && Array.isArray(response.fields)) {
                                response.fields.forEach(field => {
                                    const element = document.getElementById(field);
                                    if (element) {
                                        element.classList.add('is-invalid');
                                    }
                                });
                            }
                        }
                    } catch (e) {
                        console.error('Parse error:', e);
                        showError('Server error occurred. Please try again.');
                    }
                } else {
                    showError('Connection error. Please try again.');
                }
            };

            xhr.onerror = function() {
                resetButton(btn, originalContent);
                showError('Network error. Please check your connection.');
            };

            xhr.ontimeout = function() {
                resetButton(btn, originalContent);
                showError('Request timed out. Please try again.');
            };

            xhr.send(formData);
            
            return false;
        }
    </script>

</body>

</html>
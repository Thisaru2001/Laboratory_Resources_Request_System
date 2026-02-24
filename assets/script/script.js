let tempUniversityId = "";
let tempPassword = "";
let tempRememberMe = 0;

function signin() {
    showRecaptchaModal();
}

// Main signup function
// Main signup function
function createAccount(event) {
    // ✅ FIX: Define btn and originalContent at the start
    const btn = event ? event.target : document.querySelector('button[onclick="createAccount()"]');
    const originalContent = btn ? btn.innerHTML : 'Create Account';
    
    // ADD THIS reCAPTCHA CHECK
    if (typeof grecaptcha !== 'undefined') {
        const recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            showMessage('error', 'Please complete the reCAPTCHA verification');
            if (btn) resetButton(btn, originalContent);
            return;
        }
    }

    // Disable button and show loading
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating Account...';
    }

    // Create FormData
    const formData = new FormData();
    
    // Append all form fields with validation
    const firstName = document.getElementById('first_name')?.value.trim();
    const lastName = document.getElementById('last_name')?.value.trim();
    const universityId = document.getElementById('university_id')?.value.trim();
    const mobile = document.getElementById('mobile')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    const password = document.getElementById('password')?.value;
    const supervisorEmail = document.getElementById('supervisor_email')?.value.trim();

    // Validate required fields
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

    // Add reCAPTCHA token if available
    if (typeof grecaptcha !== 'undefined') {
        const recaptchaResponse = grecaptcha.getResponse();
        if (recaptchaResponse) {
            formData.append('recaptcha_token', recaptchaResponse);
        }
    }

    // Add profile image if selected
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

    // Create AJAX request
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
                    
                    if (typeof grecaptcha !== 'undefined') {
                        grecaptcha.reset();
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

// Helper function to reset button
function resetButton(btn, originalContent) {
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}

// Generate simple token for CSRF (backup)
function generateSimpleToken() {
    return Math.random().toString(36).substring(2, 15) + 
           Math.random().toString(36).substring(2, 15) +
           Date.now().toString(36);
}

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
        
        // Add event listener to handle focus when modal hides
        modal.addEventListener('hidden.bs.modal', function () {
            // Return focus to the button that opened the modal
            const triggerButton = document.querySelector('button[onclick="createAccount(event)"]');
            if (triggerButton) {
                triggerButton.focus();
            }
        }, { once: true });
        
    } else {
        alert((type === 'success' ? 'Success: ' : 'Error: ') + message);
    }
}

// Highlight fields with errors
function highlightFields(fields) {
    // Remove existing highlights
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
    
    // Add new highlights
    fields.forEach(field => {
        const input = document.getElementById(field.name);
        if (input) {
            input.classList.add('is-invalid');
            
            // Check if feedback already exists
            let feedback = input.nextElementSibling;
            while (feedback && !feedback.classList.contains('invalid-feedback')) {
                feedback = feedback.nextElementSibling;
            }
            
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                input.parentNode.appendChild(feedback);
            }
            
            feedback.textContent = field.message;
        }
    });
}

// Toggle between signup and signin
function showSignUp(show) {
    if (show === 0) {
        window.location.href = 'signin.html';
    }
}


///create account finish

function showRecaptchaModal() {
    tempUniversityId = document.getElementById('university_id').value.trim();
    tempPassword = document.getElementById('password').value;
    tempRememberMe = document.getElementById('remember_me')?.checked ? 1 : 0;

    if (!tempUniversityId) {
        showModal("Please enter your University ID");
        return;
    }

    if (!tempPassword) {
        showModal("Please enter your password");
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('recaptchaModal'));
    modal.show();
}

function recaptchaSuccess() {
    const verifyBtn = document.getElementById('verifyBtn');
    if (verifyBtn) {
        verifyBtn.disabled = false;
    }
}

function verifyRecaptcha() {
    const recaptchaResponse = grecaptcha.getResponse();

    if (!recaptchaResponse) {
        document.getElementById('recaptchaResponse').innerHTML = "Please complete the reCAPTCHA";
        return;
    }

    bootstrap.Modal.getInstance(document.getElementById('recaptchaModal')).hide();
    processLogin(recaptchaResponse);
}

function processLogin(recaptchaToken) {
    const btn = document.getElementById('signinBtn');
    const originalContent = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Signing In...';

  

    const form = new FormData();
    form.append("u", tempUniversityId);
    form.append("p", tempPassword);
    form.append("r", tempRememberMe);
 
    form.append("recaptcha", recaptchaToken);

    const request = new XMLHttpRequest();
    request.open("POST", "controllers/signin_process.php", true);

    request.onreadystatechange = function () {
        if (request.readyState === 4) {
            btn.disabled = false;
            btn.innerHTML = originalContent;

            if (request.status === 200) {
                try {
                    const response = JSON.parse(request.responseText);

                    if (response.status === "success") {
                        showModal(response.msg || "Login successful");

                        setTimeout(() => {
                            window.location.href = "dashboard.php";
                        }, 1000);
                    } else {
                        showModal(response.msg || "Login failed");
                        if (typeof grecaptcha !== 'undefined') {
                            grecaptcha.reset();
                        }
                    }
                } catch (e) {
                    console.error(request.responseText);
                    showModal("Server response error");
                }
            } else {
                showModal("Connection error. Try again.");
            }
        }
    };

    request.send(form);
}

function forgotPassword() {
    const university_id = document.getElementById("university_id").value.trim();
    

    if (!university_id) {
        showModal("Please enter your University ID");
        return;
    }

    const form = new FormData();
    form.append("u", university_id);
    

    const request = new XMLHttpRequest();
    request.open("POST", "controllers/forgot_password_process.php", true);

    request.onreadystatechange = function () {
        if (request.readyState === 4) {
            if (request.status === 200) {
                try {
                    const response = JSON.parse(request.responseText);
                    showModal(response.msg || "Verification email sent.");
                } catch (e) {
                    showModal("Server error");
                }
            } else {
                showModal("Request failed");
            }
        }
    };

    request.send(form);
}

/* ======================================================
   GLOBAL MESSAGE MODAL
====================================================== */
function showModal(message) {
    const modalMessage = document.getElementById("modalMessage");
    if (modalMessage) {
        modalMessage.textContent = message;
    }

    const modal = new bootstrap.Modal(document.getElementById("messageModal"));
    modal.show();
}
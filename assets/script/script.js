let tempUniversityId = "";
let tempPassword = "";
let tempRememberMe = 0;


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






function processLogin() {
    const btn = document.getElementById('signinBtn');
    const originalContent = btn.innerHTML;

    // Get values from form inputs
    const university_id = document.getElementById('university_id').value.trim();
    const password = document.getElementById('password').value.trim();
    const remember_me = document.getElementById('remember_me').checked ? 1 : 0;

    // Validate inputs
    if (!university_id || !password) {
        showModal("Please enter both University ID and Password", "error");
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Signing In...';

    const form = new FormData();
    form.append("u", university_id);
    form.append("p", password);
    form.append("r", remember_me);

    const request = new XMLHttpRequest();
    const url = "/LRRS/controllers/signin_process.php";
    
    console.log("Sending request to:", url);
    console.log("University ID:", university_id);
    console.log("Remember me:", remember_me);
    
    request.open("POST", url, true);

    request.onreadystatechange = function () {
        if (request.readyState === 4) {
            btn.disabled = false;
            btn.innerHTML = originalContent;

            if (request.status === 200) {
                try {
                    const responseText = request.responseText.trim();
                    console.log("Raw response:", responseText);
                    
                    // Check if response starts with HTML tags
                    if (responseText.startsWith('<')) {
                        console.error("Received HTML instead of JSON");
                        // Try to extract JSON if it's at the end
                        const jsonStart = responseText.indexOf('{');
                        if (jsonStart > -1) {
                            const jsonPart = responseText.substring(jsonStart);
                            try {
                                const response = JSON.parse(jsonPart);
                                if (response.status === "error") {
                                    showModal(response.msg || "Login failed", "error");
                                } else {
                                    showModal(response.msg || "Login successful", "success");
                                    setTimeout(() => {
                                        window.location.href = "./views/student.php";
                                    }, 1500);
                                }
                            } catch (e) {
                                showModal("Server error. Please check error logs.", "error");
                            }
                        } else {
                            showModal("Server error. Please check error logs.", "error");
                        }
                        return;
                    }
                    
                    const response = JSON.parse(responseText);
                    console.log("Parsed response:", response);

                    if (response.status === "success") {
                        showModal(response.msg || "Login successful", "success");

                        setTimeout(() => {
                            window.location.href = "./views/student.php";
                        }, 1500);
                    } else {
                        showModal(response.msg || "Login failed", "error");
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    console.error('Raw response:', request.responseText);
                    showModal("Server response error. Please try again.", "error");
                }
            } else {
                showModal("Connection error. Status: " + request.status, "error");
            }
        }
    };

    request.send(form);
}

// Update showModal function to handle different message types
function showModal(message, type = "error") {
    // Check if message modal exists
    let messageModal = document.getElementById('messageModal');
    
    if (!messageModal) {
        // Create modal if it doesn't exist
        createMessageModal();
        messageModal = document.getElementById('messageModal');
    }
    
    let modalMessage = document.getElementById('modalMessage');
    let modalTitle = document.getElementById('messageModalTitle');
    let modalHeader = messageModal.querySelector('.modal-header');
    
    if (modalMessage) modalMessage.innerHTML = message;
    
    if (type === "success") {
        if (modalTitle) modalTitle.innerHTML = "Success";
        if (modalHeader) {
            modalHeader.className = "modal-header bg-success text-white";
            // Update icon if exists
            let icon = messageModal.querySelector('.modal-body i');
            if (icon) {
                icon.className = "bi bi-check-circle-fill text-success";
            }
        }
    } else {
        if (modalTitle) modalTitle.innerHTML = "Error";
        if (modalHeader) {
            modalHeader.className = "modal-header bg-danger text-white";
            // Update icon if exists
            let icon = messageModal.querySelector('.modal-body i');
            if (icon) {
                icon.className = "bi bi-exclamation-circle-fill text-danger";
            }
        }
    }
    
    let modal = new bootstrap.Modal(messageModal);
    modal.show();
}

// Create message modal if it doesn't exist
function createMessageModal() {
    const modalHTML = `
        <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="messageModalTitle">Message</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem; margin-bottom: 15px;"></i>
                        <p id="modalMessage" class="mb-3"></p>
                        <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

// Also add this helper function if not present
function resetButton(btn, originalContent) {
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}




var forgotPasswordModal;

function forgotPassword() {
    var university_id = document.getElementById("university_id");
    
    // Check if email element exists
    if (!university_id) {
        console.error("Element with id 'university_id' not found");
        return;
    }
    
    if (university_id.value.trim() === "") {
        alert("Please enter your University ID");
        return;
    }
    
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
        if (request.readyState == 4) {
            if (request.status == 200) {
                var text = request.responseText.trim();
                
                // Check for success (case-sensitive match with PHP)
                if (text == "Success" || text == "success") { 
                    alert("Verification code has been sent successfully. Please check your Email.");
                    
                    var modal = document.getElementById("fpmodal");
                    if (modal) {
                        forgotPasswordModal = new bootstrap.Modal(modal);
                        forgotPasswordModal.show();
                    } else {
                        console.error("Modal element with id 'fpmodal' not found");
                    }
                } else {
                    alert("Error: " + text);
                }
            } else {
                console.error("Request failed with status: " + request.status);
            }
        }
    }

    request.open("GET", "/LRRS/controllers/forgotPasswordProcess.php?e=" + encodeURIComponent(university_id.value), true);
    request.send();
}

function resetPassword() {
    var university_id = document.getElementById("university_id");
    var newPassword = document.getElementById("np");
    var retypePassword = document.getElementById("rnp");
    var verification = document.getElementById("vcode");

    // Validate inputs
    if (!university_id.value || !newPassword.value || !retypePassword.value || !verification.value) {
        alert("Please fill in all fields");
        return;
    }

    if (newPassword.value !== retypePassword.value) {
        alert("Passwords do not match");
        return;
    }

    if (newPassword.value.length < 6) {
        alert("Password must be at least 6 characters long");
        return;
    }

    var form = new FormData();
    form.append("e", university_id.value);
    form.append("n", newPassword.value);
    form.append("r", retypePassword.value);
    form.append("v", verification.value);

    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
        if (request.status == 200 && request.readyState == 4) {
            var response = request.responseText.trim();
            if (response == "success") {
                alert("Password updated successfully. Please login with your new password.");
                if (forgotPasswordModal) {
                    forgotPasswordModal.hide();
                }
                // Clear fields
                document.getElementById("np").value = "";
                document.getElementById("rnp").value = "";
                document.getElementById("vcode").value = "";
            } else {
                alert(response);
            }
        }
    }

    request.open("POST", "/LRRS/controllers/resetPasswordProcess.php", true);
    request.send(form);
}


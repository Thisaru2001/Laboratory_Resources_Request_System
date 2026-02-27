let tempUniversityId = "";
let tempPassword = "";
let tempRememberMe = "";

// Global variables for forgot password flow
let resetUniversityId = '';

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

// Helper function to reset button
function resetButton(btn, originalContent) {
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
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

// Show reCAPTCHA modal for sign in
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

// Process login
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
    const url = "/LRRS/controllers/signin_process.php";
    
    console.log("Sending request to:", url);
    
    request.open("POST", url, true);

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
                            window.location.href = "./views/student.php";
                        }, 1000);
                    } else {
                        showModal(response.msg || "Login failed");
                        if (typeof grecaptcha !== 'undefined') {
                            grecaptcha.reset();
                        }
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    console.error('Raw response:', request.responseText);
                    showModal("Server response error");
                }
            } else {
                showModal("Connection error. Status: " + request.status);
            }
        }
    };

    request.send(form);
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

// Send reset code (Step 1) - NO reCAPTCHA
function sendResetCode() {
    console.log("sendResetCode called");
    
    // Use the stored resetUniversityId (from signin page input)
    const university_id = resetUniversityId;
    console.log("University ID:", university_id);

    if (!university_id) {
        document.getElementById('resetResponse').innerHTML = 'University ID not found';
        return;
    }

    const btn = document.getElementById('sendCodeBtn');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

    const form = new FormData();
    form.append('u', university_id);

    const request = new XMLHttpRequest();
    request.open('POST', '/LRRS/controllers/forgot_password_process.php', true);

    request.onreadystatechange = function() {
        if (request.readyState === 4) {
            btn.disabled = false;
            btn.innerHTML = originalContent;

            if (request.status === 200) {
                try {
                    const response = JSON.parse(request.responseText);
                    console.log("Server response:", response);
                    
                    if (response.status === 'success') {
                        // Close current modal
                        bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
                        
                        // Open verify code modal
                        setTimeout(() => {
                            const verifyModal = new bootstrap.Modal(document.getElementById('verifyCodeModal'));
                            verifyModal.show();
                        }, 500);
                    } else {
                        document.getElementById('resetResponse').innerHTML = response.msg;
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    console.error('Raw response:', request.responseText);
                    showModal('Server error occurred');
                }
            } else {
                showModal('Connection error');
            }
        }
    };

    request.send(form);
}

// Verify code (Step 2) - NO reCAPTCHA
function verifyCode() {
    const code = document.getElementById('verification_code').value.trim();
    
    if (!code || code.length !== 6) {
        document.getElementById('codeError').innerHTML = 'Please enter a valid 6-digit code';
        return;
    }

    const btn = document.getElementById('verifyCodeBtn');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verifying...';

    const form = new FormData();
    form.append('u', resetUniversityId);
    form.append('code', code);

    const request = new XMLHttpRequest();
    request.open('POST', '/LRRS/controllers/verify_code_process.php', true);

    request.onreadystatechange = function() {
        if (request.readyState === 4) {
            btn.disabled = false;
            btn.innerHTML = originalContent;

            if (request.status === 200) {
                try {
                    const response = JSON.parse(request.responseText);
                    if (response.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('verifyCodeModal')).hide();
                        setTimeout(() => {
                            const resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
                            resetModal.show();
                        }, 500);
                    } else {
                        document.getElementById('codeError').innerHTML = response.msg;
                    }
                } catch (e) {
                    showModal('Server error occurred');
                }
            } else {
                showModal('Connection error');
            }
        }
    };

    request.send(form);
}

// Reset password (Step 3)
function resetPassword() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    // Validate passwords
    if (!newPassword || newPassword.length < 8) {
        document.getElementById('passwordError').innerHTML = 'Password must be at least 8 characters';
        return;
    }

    if (newPassword !== confirmPassword) {
        document.getElementById('passwordError').innerHTML = 'Passwords do not match';
        return;
    }

    const btn = document.getElementById('resetPasswordBtn');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Resetting...';

    const form = new FormData();
    form.append('u', resetUniversityId);
    form.append('password', newPassword);

    const request = new XMLHttpRequest();
    request.open('POST', '/LRRS/controllers/reset_password_process.php', true);

    request.onreadystatechange = function() {
        if (request.readyState === 4) {
            btn.disabled = false;
            btn.innerHTML = originalContent;

            if (request.status === 200) {
                try {
                    const response = JSON.parse(request.responseText);
                    if (response.status === 'success') {
                        // Close reset modal and show success modal
                        bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
                        setTimeout(() => {
                            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                            successModal.show();
                            
                            // Clear fields
                            document.getElementById('new_password').value = '';
                            document.getElementById('confirm_password').value = '';
                            document.getElementById('verification_code').value = '';
                            
                            // Redirect to signin page after 2 seconds
                            setTimeout(() => {
                                window.location.href = '/LRRS/index.php';
                            }, 2000);
                        }, 500);
                    } else {
                        document.getElementById('passwordError').innerHTML = response.msg;
                    }
                } catch (e) {
                    showModal('Server error occurred');
                }
            } else {
                showModal('Connection error');
            }
        }
    };

    request.send(form);
}

// Resend code
function resendCode() {
    sendResetCode();
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


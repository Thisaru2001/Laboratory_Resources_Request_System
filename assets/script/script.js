

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


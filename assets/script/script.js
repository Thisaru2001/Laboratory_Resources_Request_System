function signin() {
    const university_id = document.getElementById("university_id").value.trim();
    const password = document.getElementById("password").value;
    const remember_me = document.getElementById("remember_me").checked ? 1 : 0;
    const csrf_token = document.getElementById("csrf_token").value;

    const form = new FormData();
    form.append("u", university_id);
    form.append("p", password);
    form.append("r", remember_me);
    form.append("csrf_token", csrf_token);

    const request = new XMLHttpRequest();
    request.open("POST", "controllers/signin_process.php", true);

    request.onreadystatechange = function() {
        if (request.readyState === 4) {
            if (request.status === 200) {
                try {
                    const response = JSON.parse(request.responseText);
                    if (response.status === "success") {
                        alert(response.msg || "Login successful");
                      //  window.location.href = "dashboard.php"; // redirect after login
                    } else {
                       showModal(response.msg || "Login failed", "error");
                    }
                } catch (e) {
                    console.error("Invalid JSON response", e);
                }
            } else {
                console.error("AJAX error:", request.status);
            }
        }
    };

    request.send(form);
}


function showModal(message) {
    // Set the message text
    const modalMessage = document.getElementById("modalMessage");
    modalMessage.textContent = message;

    // Always success color
    const btn = document.querySelector("#messageModal .btn");
    btn.className = "btn btn-success";

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById("messageModal"));
    modal.show();
}


var forgotPasswordModal;

function forgotPassword() {
    var university_id = document.getElementById("university_id").value.trim();
    var csrf_token = document.getElementById("csrf_token").value;

    if(university_id === ""){
        showModal("Please enter your email.");
        return;
    }

    var form = new FormData();
    form.append("u", university_id);
    form.append("csrf_token", csrf_token);

    var request = new XMLHttpRequest();
    request.open("POST", "controllers/forgot_password_process.php", true);

    request.onreadystatechange = function () {
        if (request.readyState === 4) {
            if (request.status === 200) {
                try {
                    const response = JSON.parse(request.responseText);

                    // Always show success-style modal
                    showModal(response.msg || "A verification code has been sent.");

                    // If needed, show modal for entering code
                    // var modalEl = document.getElementById("fpmodal");
                    // forgotPasswordModal = new bootstrap.Modal(modalEl);
                    // forgotPasswordModal.show();

                } catch (e) {
                    console.error("Invalid JSON response", e);
                    showModal("Server error. Try again later.");
                }
            } else {
                console.error("AJAX error:", request.status);
                showModal("AJAX request failed. Please try again.");
            }
        }
    };

    request.send(form);
}



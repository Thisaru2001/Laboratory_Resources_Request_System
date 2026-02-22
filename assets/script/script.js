

/* ======================================================
   TEMP VARIABLES
====================================================== */
let tempUniversityId = "";
let tempPassword = "";
let tempRememberMe = 0;


/* ======================================================
   SIGN IN BUTTON CLICK
====================================================== */
function signin() {
    showRecaptchaModal();
}


/* ======================================================
   SHOW RECAPTCHA MODAL
====================================================== */
function showRecaptchaModal() {

    tempUniversityId =
        document.getElementById('university_id').value.trim();

    tempPassword =
        document.getElementById('password').value;

    tempRememberMe =
        document.getElementById('remember_me').checked ? 1 : 0;

    if (!tempUniversityId) {
        showModal("Please enter your University ID");
        return;
    }

    if (!tempPassword) {
        showModal("Please enter your password");
        return;
    }

    const modal =
        new bootstrap.Modal(document.getElementById('recaptchaModal'));

    modal.show();
}


/* ======================================================
   RECAPTCHA SUCCESS CALLBACK
====================================================== */
function recaptchaSuccess() {
    document.getElementById('verifyBtn').disabled = false;
}


/* ======================================================
   VERIFY RECAPTCHA BUTTON
====================================================== */
function verifyRecaptcha() {

    const recaptchaResponse = grecaptcha.getResponse();

    if (!recaptchaResponse) {
        document.getElementById('recaptchaResponse')
            .innerHTML = "Please complete the reCAPTCHA";
        return;
    }

    bootstrap.Modal
        .getInstance(document.getElementById('recaptchaModal'))
        .hide();

    processLogin(recaptchaResponse);
}


/* ======================================================
   LOGIN PROCESS (AJAX)
====================================================== */
function processLogin(recaptchaToken) {

    const btn = document.getElementById('signinBtn');
    const originalContent = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span> Signing In...';

    const csrf_token =
        document.getElementById("csrf_token").value;

    const form = new FormData();
    form.append("u", tempUniversityId);
    form.append("p", tempPassword);
    form.append("r", tempRememberMe);
    form.append("csrf_token", csrf_token);
    form.append("recaptcha", recaptchaToken);

    const request = new XMLHttpRequest();
    request.open("POST", "controllers/signin_process.php", true);

    request.onreadystatechange = function () {

        if (request.readyState === 4) {

            btn.disabled = false;
            btn.innerHTML = originalContent;

            if (request.status === 200) {

                try {

                    const response =
                        JSON.parse(request.responseText);

                    if (response.status === "success") {

                        showModal(response.msg || "Login successful");

                        setTimeout(() => {
                            window.location.href = "dashboard.php";
                        }, 1000);

                    } else {
                        showModal(response.msg || "Login failed");
                        grecaptcha.reset();
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


/* ======================================================
   FORGOT PASSWORD
====================================================== */
function forgotPassword() {

    const university_id =
        document.getElementById("university_id").value.trim();

    const csrf_token =
        document.getElementById("csrf_token").value;

    if (!university_id) {
        showModal("Please enter your University ID");
        return;
    }

    const form = new FormData();
    form.append("u", university_id);
    form.append("csrf_token", csrf_token);

    const request = new XMLHttpRequest();
    request.open("POST",
        "controllers/forgot_password_process.php", true);

    request.onreadystatechange = function () {

        if (request.readyState === 4) {

            if (request.status === 200) {

                try {
                    const response =
                        JSON.parse(request.responseText);

                    showModal(
                        response.msg ||
                        "Verification email sent."
                    );

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

    document.getElementById("modalMessage").textContent = message;

    const modal =
        new bootstrap.Modal(document.getElementById("messageModal"));

    modal.show();
}


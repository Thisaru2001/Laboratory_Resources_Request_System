<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Microbiology Lab System - Login</title>

<link rel="stylesheet" href="./assets/resources/signin.css">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>

<div class="auth-card">

    <!-- Left Image -->
    <div class="left-image d-none d-md-block">
        <img src="./assets/resources/signin.png" alt="Lab Image">
    </div>

    <!-- Right Form -->
    <div class="auth-form">

        <h3 class="brand fw-bold text-center mb-1">
            <i class="bi bi-flask"></i> Microbiology Lab
        </h3>
        <p class="text-center text-muted mb-4">
            University of Kelaniya/Faculty of science
        </p>


          <h2 class="brand text-center fw-bold mb-3">Sign In</h2>
      

        <div class="tab-content">

            <!-- LOGIN -->
            <div class="tab-pane fade show active" id="login">
                <form action="login.php">

                
                            <?php
                            $email = "";
                            $password = "";

                            if (isset($_COOKIE["University_id"])) {
                                $University_id = $_COOKIE["email"];
                            }

                            if (isset($_COOKIE["password"])) {
                                $password = $_COOKIE["password"];
                            }
                            ?>


                    <div class="mb-3">
                        <label class="form-label">University ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input id="university" type="text" class="form-control" placeholder="University ID" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" placeholder="Password" required>
                        </div>
                    </div>

                    <div class="text-end mb-3">
                        <a href="forgot-password.php" class="small">Forgot password?</a>
                    </div>

                 <div class="d-grid mb-2">
    <!-- Sign In Button -->
    <button onclick="signin();"  class="btn btn-success btn-lg">Sign In</button>

    <!-- Sign Up Link Text -->
    <p class="text-center mt-3 mb-0">
        Don't have an account? 
        <a href="signup.html" class="link-success text-decoration-none">Sign up</a>
    </p>
</div>


                </form>
            </div>

          

        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="./assets/script/script.js"></script>
</body>
</html>

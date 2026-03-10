
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Microbiology Lab System - Equipment Registration</title>

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

        /* Laboratory Decorative Elements */
        .lab-microscope, .lab-test-tube, .lab-alembic, .lab-petri, 
        .lab-dna, .lab-microbe, .lab-flask, .lab-beaker, .lab-centrifuge, .lab-autoclave {
            position: fixed;
            font-size: 8rem;
            opacity: 0.12;
            z-index: 1;
            user-select: none;
            pointer-events: none;
            color: rgba(255, 255, 255, 0.8);
            text-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            filter: none;
            -webkit-text-stroke: 1px rgba(255, 255, 255, 0.1);
        }

        .lab-microscope { 
            top: 2%; 
            left: 2%; 
            animation: float 15s infinite ease-in-out;
            transform: rotate(-15deg); 
        }

        .lab-test-tube { 
            top: 2%; 
            right: 2%; 
            animation: floatReverse 18s infinite ease-in-out;
            transform: rotate(10deg); 
        }

        .lab-alembic { 
            bottom: 2%; 
            left: 2%; 
            animation: floatSlow 20s infinite ease-in-out;
            transform: rotate(20deg); 
        }

        .lab-petri { 
            bottom: 2%; 
            right: 2%; 
            animation: floatMedium 22s infinite ease-in-out;
            transform: rotate(-5deg); 
        }

        .lab-dna { 
            top: 50%; 
            left: 1%; 
            animation: rotate 25s infinite linear;
            transform: translateY(-50%) rotate(45deg); 
        }

        .lab-microbe { 
            top: 50%; 
            right: 1%; 
            animation: bounce 8s infinite ease-in-out;
            transform: translateY(-50%) rotate(-25deg); 
        }

        .lab-centrifuge { 
            bottom: 25%; 
            left: 5%; 
            animation: floatFlask 19s infinite ease-in-out;
            transform: rotate(15deg); 
        }

        .lab-autoclave { 
            top: 25%; 
            right: 5%; 
            animation: floatBeaker 21s infinite ease-in-out;
            transform: rotate(-10deg); 
        }

        /* Animation Keyframes */
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

        /* Bubble Container */
        .bubble-container {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 2;
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
            height: 650px;
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

        .form-control, .form-select {
            border: none;
            padding: 0 15px;
            font-size: 0.95rem;
            background: #f9fafb;
            height: 100%;
        }

        .form-control:focus, .form-select:focus {
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

        /* Equipment Image Section */
        .equipment-section {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            margin: 15px 0;
            border: 2px dashed #e5e7eb;
            transition: all 0.3s;
        }

        .equipment-section:hover {
            border-color: #22c55e;
            background: #f0fdf4;
        }

        #equipmentPreview {
            width: 100px;
            height: 100px;
            border-radius: 16px;
            object-fit: cover;
            border: 3px solid #22c55e;
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.2);
            transition: all 0.3s;
            background: white;
            padding: 5px;
        }

        #equipmentPreview:hover {
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

        /* Toggle switches for YES/NO fields */
        .toggle-switch {
            display: flex;
            gap: 20px;
            margin-top: 5px;
        }

        .toggle-option {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .toggle-option input[type="radio"] {
            accent-color: #22c55e;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .toggle-option label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            color: #374151;
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

            .lab-microscope, .lab-test-tube, .lab-alembic, .lab-petri, 
            .lab-dna, .lab-microbe, .lab-centrifuge, .lab-autoclave {
                font-size: 4rem;
            }
        }

        @media (max-width: 768px) {
            .lab-microscope, .lab-test-tube, .lab-alembic, .lab-petri, 
            .lab-dna, .lab-microbe, .lab-centrifuge, .lab-autoclave {
                font-size: 4rem;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .auth-card {
                border-radius: 30px;
                max-width: 95%;
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

            .lab-microscope, .lab-test-tube, .lab-alembic, .lab-petri, 
            .lab-dna, .lab-microbe, .lab-centrifuge, .lab-autoclave {
                font-size: 3rem;
            }

            .toggle-switch {
                gap: 15px;
            }
        }

        @media (max-width: 380px) {
            .auth-form {
                padding: 15px;
            }

            .equipment-section {
                padding: 15px;
            }

            #equipmentPreview {
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

    <!-- Laboratory Decorative Elements -->
    <div class="lab-microscope">🔬</div>
    <div class="lab-test-tube">🧪</div>
    <div class="lab-alembic">⚗️</div>
    <div class="lab-petri">🧫</div>
    <div class="lab-dna">🧬</div>
    <div class="lab-microbe">🦠</div>
    <div class="lab-centrifuge">🧪</div>
    <div class="lab-autoclave">🏭</div>

    <!-- Bubble Container -->
    <div class="bubble-container">
        <div class="bubble bubble1"></div>
        <div class="bubble bubble2"></div>
        <div class="bubble bubble3"></div>
        <div class="bubble bubble4"></div>
        <div class="bubble bubble5"></div>
        <div class="bubble bubble6"></div>
    </div>

    <div class="auth-card">

        <!-- Left Image - Changed to equipment image -->
        <div class="left-image d-none d-md-block">
            <img src="../assets/resources/equipment.png" alt="Lab Equipment" onerror="this.src='https://img.icons8.com/fluency/480/microscope.png'">
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

                <h2 class="brand text-center fw-bold">Equipment Registration</h2>

                <!-- EQUIPMENT REGISTRATION FORM -->
                <form onsubmit="registerEquipment(event); return false;">
                    <!-- Equipment Code & Name -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Equipment Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" id="code" class="form-control" placeholder="EQ001" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Equipment Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-gear"></i></span>
                                <input type="text" id="name" class="form-control" placeholder="Microscope" required>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity & Location -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Total Quantity</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calculator"></i></span>
                                <input type="number" id="total_qty" class="form-control" placeholder="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                                <select id="location_id" class="form-select" required>
                                    <option value="" selected disabled>Select Location</option>
                                    <option value="1">Main Laboratory</option>
                                    <option value="2">Research Lab</option>
                                    <option value="3">Teaching Lab</option>
                                    <option value="4">Storage Room</option>
                                    <option value="5">Preparation Room</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Simultaneous Users & Description -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Simultaneous Users</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-people"></i></span>
                                <input type="number" id="simultaneous_users" class="form-control" placeholder="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-text-paragraph"></i></span>
                                <input type="text" id="description" class="form-control" placeholder="Brief description">
                            </div>
                        </div>
                    </div>

                    <!-- Sterilization Required & Reservation Required -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Sterilization Required</label>
                            <div class="input-group" style="height: auto; background: transparent; border: none;">
                                <div class="toggle-switch">
                                    <label class="toggle-option">
                                        <input type="radio" name="sterilization_required" value="YES" checked> <span>YES</span>
                                    </label>
                                    <label class="toggle-option">
                                        <input type="radio" name="sterilization_required" value="NO"> <span>NO</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reservation Required</label>
                            <div class="input-group" style="height: auto; background: transparent; border: none;">
                                <div class="toggle-switch">
                                    <label class="toggle-option">
                                        <input type="radio" name="reservation_required" value="YES" checked> <span>YES</span>
                                    </label>
                                    <label class="toggle-option">
                                        <input type="radio" name="reservation_required" value="NO"> <span>NO</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Image -->
                    <div class="equipment-section text-center">
                        <label class="form-label d-block mb-3">Equipment Image</label>

                        <!-- Default equipment preview -->
                        <img id="equipmentPreview"
                            src="https://img.icons8.com/fluency/480/microscope.png"
                            alt="Equipment"
                            class="rounded mb-3"
                            style="width:100px;height:100px;object-fit:cover;border:3px solid #22c55e;">

                        <input type="file"
                            name="equipment_image"
                            id="equipmentImageInput"
                            class="d-none"
                            accept="image/*">

                        <div>
                            <button type="button"
                                class="btn btn-outline-success"
                                onclick="document.getElementById('equipmentImageInput').click();">
                                <i class="bi bi-camera me-2"></i>Upload Image
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">Upload equipment photo (optional)</small>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mb-2">
                        <button type="submit" class="btn btn-success" id="registerBtn">
                            <i class="bi bi-plus-circle me-2"></i>Register Equipment
                        </button>
                        <a href="equipment_list.php" class="link-success text-decoration-none mt-3 d-block text-center">
                            <i class="bi bi-list me-1"></i>
                            View Equipment List
                        </a>
                    </div>

                    <p class="text-muted text-center small mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Equipment registration requires HOD approval
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
        document.getElementById('equipmentImageInput').addEventListener('change', function() {
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
                document.getElementById('equipmentPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

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

        // Main equipment registration function
        function registerEquipment(event) {
            event.preventDefault();

            const btn = document.getElementById('registerBtn');
            const originalContent = btn.innerHTML;

            // Validate all fields
            const code = document.getElementById('code').value.trim();
            const name = document.getElementById('name').value.trim();
            const total_qty = document.getElementById('total_qty').value;
            const location_id = document.getElementById('location_id').value;
            const simultaneous_users = document.getElementById('simultaneous_users').value;
            const description = document.getElementById('description').value.trim();
            const sterilization_required = document.querySelector('input[name="sterilization_required"]:checked').value;
            const reservation_required = document.querySelector('input[name="reservation_required"]:checked').value;

            // Basic validation
            if (!code || !name || !total_qty || !location_id || !simultaneous_users) {
                showError('Please fill in all required fields');
                return;
            }

            // Quantity validation
            if (parseInt(total_qty) < 1) {
                showError('Total quantity must be at least 1');
                return;
            }

            if (parseInt(simultaneous_users) < 1) {
                showError('Simultaneous users must be at least 1');
                return;
            }

            if (parseInt(simultaneous_users) > parseInt(total_qty)) {
                showError('Simultaneous users cannot exceed total quantity');
                return;
            }

            // Disable button and show loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Registering...';

            const formData = new FormData();
            formData.append('code', code);
            formData.append('name', name);
            formData.append('total_qty', total_qty);
            formData.append('location_id', location_id);
            formData.append('simultaneous_users', simultaneous_users);
            formData.append('description', description);
            formData.append('sterilization_required', sterilization_required);
            formData.append('reservation_required', reservation_required);
            formData.append('added_datatime', new Date().toISOString().slice(0, 19).replace('T', ' '));
            formData.append('is_hod_checked', '0');

            const equipmentImageInput = document.getElementById('equipmentImageInput');
            if (equipmentImageInput && equipmentImageInput.files.length > 0) {
                formData.append('equipment_image', equipmentImageInput.files[0]);
            }

            // AJAX request to your equipment registration endpoint
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../controllers/equipment_register_process.php', true);
            xhr.timeout = 30000;

            xhr.onload = function() {
                resetButton(btn, originalContent);

                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);

                        if (response.status === 'success') {
                            showSuccess(response.message || 'Equipment registered successfully!');

                            // Clear form
                            document.getElementById('code').value = '';
                            document.getElementById('name').value = '';
                            document.getElementById('total_qty').value = '';
                            document.getElementById('location_id').value = '';
                            document.getElementById('simultaneous_users').value = '';
                            document.getElementById('description').value = '';

                            // Reset radio buttons to default
                            document.querySelectorAll('input[name="sterilization_required"]')[0].checked = true;
                            document.querySelectorAll('input[name="reservation_required"]')[0].checked = true;

                            const equipmentInput = document.getElementById('equipmentImageInput');
                            if (equipmentInput) equipmentInput.value = '';

                            document.getElementById('equipmentPreview').src = 'https://img.icons8.com/fluency/480/microscope.png';

                            // Redirect after 3 seconds
                            setTimeout(() => {
                                window.location.href = 'equipment_list.php';
                            }, 3000);

                        } else {
                            showError(response.message || 'Equipment registration failed');
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
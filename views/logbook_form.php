<?php
session_start();

// Session guard — redirect if not logged in

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Microbiology Lab - Practical Finish Logbook</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="assets/resources/flask.svg">
   <link rel="icon" type="image/svg+xml" href="../assets/resources/flask.svg">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        :root { --g:#22c55e; --gd:#16a34a; --gdk:#15803d; }

        html { height:100%; }
        body {
            min-height:100%;
            background: linear-gradient(135deg,#22c55e 0%,#16a34a 100%);
            overflow-y:auto; font-family:'Inter','Segoe UI',sans-serif;
            display:flex; align-items:flex-start; justify-content:center;
            padding:30px 16px 50px; position:relative;
        }
        body::before {
            content:''; position:fixed; width:300px; height:300px;
            background:rgba(255,255,255,.10); border-radius:50%;
            top:-100px; right:-100px;
            animation:bgFloat 20s infinite ease-in-out; pointer-events:none; z-index:0;
        }
        body::after {
            content:''; position:fixed; width:400px; height:400px;
            background:rgba(255,255,255,.05); border-radius:50%;
            bottom:-150px; left:-150px;
            animation:bgFloat 25s infinite ease-in-out reverse; pointer-events:none; z-index:0;
        }
        @keyframes bgFloat {
            0%,100%{ transform:translate(0,0) rotate(0deg); }
            25%{ transform:translate(50px,50px) rotate(90deg); }
            50%{ transform:translate(100px,0) rotate(180deg); }
            75%{ transform:translate(50px,-50px) rotate(270deg); }
        }
        .lab-deco { position:fixed; font-size:7rem; opacity:.11; z-index:1; user-select:none; pointer-events:none; }
        .lab-microscope{ top:2%;    left:2%;  animation:d1 15s infinite ease-in-out; }
        .lab-test-tube { top:2%;    right:2%; animation:d2 18s infinite ease-in-out; }
        .lab-alembic   { bottom:2%; left:2%;  animation:d3 20s infinite ease-in-out; }
        .lab-petri     { bottom:2%; right:2%; animation:d4 22s infinite ease-in-out; }
        .lab-dna       { top:48%; left:1%;  animation:spinD 25s infinite linear;     transform:translateY(-50%); }
        .lab-microbe   { top:48%; right:1%; animation:bnc  8s  infinite ease-in-out; transform:translateY(-50%); }
        @keyframes d1 { 0%,100%{transform:translate(0,0) rotate(0deg);} 25%{transform:translate(30px,-30px) rotate(10deg);} 50%{transform:translate(50px,0);} 75%{transform:translate(20px,30px) rotate(-10deg);} }
        @keyframes d2 { 0%,100%{transform:translate(0,0) rotate(0deg);} 25%{transform:translate(-30px,30px) rotate(-10deg);} 50%{transform:translate(-50px,0);} 75%{transform:translate(-20px,-30px) rotate(10deg);} }
        @keyframes d3 { 0%,100%{transform:translate(0,0);} 33%{transform:translate(40px,-20px) rotate(15deg);} 66%{transform:translate(-20px,40px) rotate(-15deg);} }
        @keyframes d4 { 0%,100%{transform:translate(0,0) scale(1);} 25%{transform:translate(-40px,30px) scale(1.1);} 50%{transform:translate(30px,-40px) scale(.9);} 75%{transform:translate(-30px,-30px) scale(1.05);} }
        @keyframes spinD { from{transform:translateY(-50%) rotate(0deg);} to{transform:translateY(-50%) rotate(360deg);} }
        @keyframes bnc   { 0%,100%{transform:translateY(-50%) scale(1);} 50%{transform:translateY(calc(-50% - 40px)) scale(1.15);} }
        .bubble-container { position:fixed; inset:0; overflow:hidden; pointer-events:none; z-index:2; }
        .bubble { position:absolute; background:rgba(255,255,255,.28); border-radius:50%; box-shadow:0 0 18px rgba(255,255,255,.28); animation:bubbleUp ease-out infinite; }
        .b1{width:38px;height:38px;bottom:10%;left:15%;  animation-duration:12s;}
        .b2{width:24px;height:24px;bottom:20%;right:25%; animation-duration:8s;  animation-delay:2s;}
        .b3{width:33px;height:33px;bottom:5%; left:40%;  animation-duration:10s; animation-delay:4s;}
        .b4{width:19px;height:19px;bottom:30%;right:40%; animation-duration:9s;  animation-delay:1s;}
        .b5{width:28px;height:28px;bottom:15%;left:70%;  animation-duration:11s; animation-delay:3s;}
        .b6{width:42px;height:42px;bottom:25%;right:15%; animation-duration:13s; animation-delay:5s;}
        @keyframes bubbleUp {
            0%  { transform:translateY(0) scale(1);       opacity:.3; }
            50% { transform:translateY(-150px) scale(1.5); opacity:.5; }
            100%{ transform:translateY(-320px) scale(.5);  opacity:0;  }
        }
        .logbook-card {
            background:white; border-radius:32px;
            box-shadow:0 30px 70px rgba(0,0,0,.3);
            width:100%; max-width:860px;
            position:relative; z-index:10;
            border-left:7px solid var(--g);
            animation:slideUp .8s cubic-bezier(.4,0,.2,1);
        }
        @keyframes slideUp { from{opacity:0;transform:translateY(50px);} to{opacity:1;transform:translateY(0);} }
        .logbook-form { padding:32px 36px 28px; }
        .brand { color:#166534; font-weight:700; letter-spacing:-.5px; }
        .brand-icon {
            background:linear-gradient(135deg,#22c55e,#16a34a);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            font-size:2.2rem; display:inline-block;
            animation:spinIcon 10s infinite linear;
        }
        @keyframes spinIcon { from{transform:rotate(0deg);} to{transform:rotate(360deg);} }
        .form-label { font-weight:600; color:#374151; margin-bottom:5px; font-size:.83rem; text-transform:uppercase; letter-spacing:.3px; }
        .input-group { border-radius:12px; overflow:hidden; border:2px solid #e5e7eb; transition:all .3s; background:#f9fafb; }
        .input-group:focus-within { border-color:#22c55e; box-shadow:0 0 0 3px rgba(34,197,94,.12); }
        .input-group-text { background:#f9fafb; border:none; color:#22c55e; font-size:1rem; padding:0 14px; }
        .form-control { border:none; padding:10px 14px; font-size:.92rem; background:#f9fafb; color:#1f2937; }
        .form-control:focus { box-shadow:none; outline:none; background:#f9fafb; }
        .form-control::placeholder { color:#9ca3af; font-weight:300; font-size:.88rem; }
        .photo-section { background:#f9fafb; border:2px solid #e5e7eb; border-radius:16px; padding:16px 18px; }
        .camera-bar { background:#e6f7e6; border-radius:50px; padding:8px 16px; display:flex; align-items:center; gap:9px; color:#166534; font-size:.84rem; border:1px solid #bbf7d0; margin-bottom:14px; }
        .preview-strip { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:12px; }
        .thumb-item { position:relative; width:80px; height:80px; border-radius:12px; overflow:hidden; border:2px solid #22c55e; flex-shrink:0; animation:thumbIn .2s ease; }
        @keyframes thumbIn { from{opacity:0;transform:scale(.8);} to{opacity:1;transform:scale(1);} }
        .thumb-item img { width:100%; height:100%; object-fit:cover; display:block; }
        .thumb-item .thumb-rm { position:absolute; top:3px; right:3px; width:18px; height:18px; background:rgba(220,38,38,.88); border:none; border-radius:50%; color:#fff; font-size:.55rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:transform .12s; z-index:5; }
        .thumb-item .thumb-rm:hover { transform:scale(1.2); }
        .upload-row { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .upload-btn-wrap { position:relative; display:inline-block; }
        .upload-btn { display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:linear-gradient(135deg,#22c55e,#16a34a); color:white; border-radius:30px; font-size:.88rem; font-weight:600; cursor:pointer; border:none; transition:all .25s; box-shadow:0 4px 12px rgba(34,197,94,.3); user-select:none; white-space:nowrap; }
        .upload-btn:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(34,197,94,.4); }
        .upload-btn.disabled { background:#d1fae5; color:#6b7280; cursor:not-allowed; box-shadow:none; transform:none; }
        .upload-btn-wrap input[type="file"] { position:absolute; inset:0; opacity:0; width:100%; height:100%; cursor:pointer; }
        .upload-btn-wrap.disabled input[type="file"] { pointer-events:none; }
        .upload-status { font-size:.82rem; color:#6b7280; }
        .upload-status .count-now { font-weight:700; color:#16a34a; }
        .camera-modal { position:fixed; inset:0; background:rgba(0,0,0,.95); z-index:9998; display:none; flex-direction:column; align-items:center; justify-content:center; }
        .camera-modal.show { display:flex; }
        .camera-container { position:relative; width:100%; max-width:500px; background:black; border-radius:16px; overflow:hidden; aspect-ratio:3/4; }
        .camera-video { width:100%; height:100%; object-fit:cover; }
        .camera-canvas { display:none; }
        .camera-controls { position:absolute; bottom:20px; left:0; right:0; display:flex; justify-content:center; gap:16px; z-index:100; }
        .camera-btn { width:60px; height:60px; border-radius:50%; border:3px solid white; background:rgba(34,197,94,.9); color:white; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1.5rem; transition:all .2s; }
        .camera-btn:hover { transform:scale(1.1); background:rgba(34,197,94,1); }
        .camera-btn.cancel { background:rgba(220,38,38,.8); }
        .camera-btn.cancel:hover { background:rgba(220,38,38,1); }
        .camera-header { position:absolute; top:16px; left:0; right:0; display:flex; justify-content:space-between; align-items:center; padding:0 20px; z-index:100; }
        .camera-title { color:white; font-weight:600; font-size:1.1rem; }
        .camera-close-btn { background:rgba(0,0,0,.5); color:white; border:none; width:40px; height:40px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1.5rem; transition:all .2s; }
        .camera-close-btn:hover { background:rgba(0,0,0,.8); }
        .upload-status .count-max { color:#8b5cf6; }
        .camera-upload-btn { display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:#8b5cf6; color:white; border-radius:30px; font-size:.88rem; font-weight:600; cursor:pointer; border:none; transition:all .25s; box-shadow:0 4px 12px rgba(139,92,246,.3); user-select:none; white-space:nowrap; }
        .camera-upload-btn:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(139,92,246,.4); }
        .camera-upload-btn.disabled { background:#d8b4fe; cursor:not-allowed; box-shadow:none; transform:none; }
        @media(max-width:600px) { .camera-container { max-width:100%; } }
        .verify-wrap { margin-bottom:1.25rem; }
        .verify-box { background:#f0fdf4; border:2px solid #bbf7d0; border-radius:16px; padding:16px 20px; display:flex; align-items:flex-start; gap:14px; cursor:pointer; transition:border-color .2s,background .2s,box-shadow .2s; user-select:none; }
        .verify-box:hover { border-color:#22c55e; box-shadow:0 0 0 3px rgba(34,197,94,.1); }
        .verify-box.checked { border-color:#16a34a; background:#dcfce7; box-shadow:0 0 0 3px rgba(34,197,94,.15); }
        .verify-box.invalid { border-color:#dc2626!important; background:#fff5f5!important; box-shadow:0 0 0 3px rgba(220,38,38,.12)!important; animation:shake .35s ease; }
        @keyframes shake { 0%,100%{transform:translateX(0);} 25%{transform:translateX(-6px);} 75%{transform:translateX(6px);} }
        .verify-chk { appearance:none; -webkit-appearance:none; width:22px; height:22px; min-width:22px; border:2px solid #86efac; border-radius:6px; background:white; cursor:pointer; position:relative; margin-top:2px; transition:all .2s; flex-shrink:0; }
        .verify-chk:checked { background:linear-gradient(135deg,#22c55e,#16a34a); border-color:#15803d; }
        .verify-chk:checked::after { content:''; position:absolute; left:5px; top:2px; width:8px; height:12px; border:2.5px solid white; border-top:none; border-left:none; transform:rotate(45deg); }
        .verify-chk:focus { outline:none; box-shadow:none; }
        .verify-text { font-size:.88rem; color:#166534; font-weight:500; line-height:1.6; }
        .verify-text strong { color:#14532d; font-weight:700; }
        .verify-hint { display:none; font-size:.78rem; color:#dc2626; margin-top:6px; margin-left:4px; }
        .verify-hint.show { display:block; }
        .btn-success { background:linear-gradient(135deg,#22c55e,#16a34a); border:none; padding:11px 28px; border-radius:30px; font-weight:600; transition:all .3s; box-shadow:0 5px 15px rgba(34,197,94,.3); color:white; }
        .btn-success:hover  { transform:translateY(-2px); box-shadow:0 8px 25px rgba(34,197,94,.4); }
        .btn-success:active { transform:translateY(0); }
        .btn-outline-success { border:2px solid #22c55e; color:#22c55e; padding:11px 28px; border-radius:30px; font-weight:600; background:transparent; transition:all .3s; }
        .btn-outline-success:hover { background:#22c55e; color:white; }
        .action-bar { display:flex; gap:14px; justify-content:flex-end; margin-top:22px; padding-top:18px; border-top:2px dashed #e5e7eb; }
        #submitOverlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:9999; align-items:center; justify-content:center; flex-direction:column; gap:14px; }
        #submitOverlay.show { display:flex; }
        #submitOverlay .sp-text { color:white; font-weight:600; font-size:1rem; }
        @media(max-width:768px) { .logbook-form{padding:22px 18px;} .lab-deco{font-size:4rem;} }
        @media(max-width:480px) { .logbook-form{padding:18px 14px;} .action-bar{flex-direction:column;} .action-bar .btn{width:100%;justify-content:center;} }
    </style>
</head>

<body>

    <div class="lab-deco lab-microscope">🔬</div>
    <div class="lab-deco lab-test-tube">🧪</div>
    <div class="lab-deco lab-alembic">⚗️</div>
    <div class="lab-deco lab-petri">🧫</div>
    <div class="lab-deco lab-dna">🧬</div>
    <div class="lab-deco lab-microbe">🦠</div>

    <div class="bubble-container">
        <div class="bubble b1"></div><div class="bubble b2"></div>
        <div class="bubble b3"></div><div class="bubble b4"></div>
        <div class="bubble b5"></div><div class="bubble b6"></div>
    </div>

    <!-- Spinner overlay shown while submitting -->
    <div id="submitOverlay">
        <div class="spinner-border text-light" style="width:3rem;height:3rem;" role="status"></div>
        <span class="sp-text">Submitting your evidence…</span>
    </div>

    <div class="logbook-card">
        <div class="logbook-form">

            <!-- Header -->
            <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                <i class="bi bi-flask brand-icon"></i>
                <div>
                    <h2 class="brand fw-bold mb-0" style="font-size:1.75rem;">Practical Finish Logbook</h2>
                    <p class="text-muted mb-0" style="font-size:.88rem;">University of Kelaniya &bull; Microbiology Laboratory</p>
                </div>
                <span class="badge bg-success ms-auto px-3 py-2" style="font-size:.75rem;">Evidence Submission</span>
            </div>

            <form id="logbookForm">

                <!-- University ID & Email — pre-filled & readonly from session -->
                <div class="row mb-3 g-3">
                    <div class="col-md-6">
                        <label class="form-label"><i class="bi bi-card-text me-1"></i>University ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                            <input type="text" id="university_id" class="form-control"
                                placeholder="e.g. BS/2024/001"
                                oninput="this.value=this.value.toUpperCase()"
                                value="<?= htmlspecialchars($_SESSION['university_id'] ?? '') ?>"
                                <?= !empty($_SESSION['university_id']) ? 'readonly' : 'required' ?>>
                        </div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label"><i class="bi bi-receipt me-1"></i>Reservation ID (send this ID to your email)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash"></i></span>
                            <input type="text" id="reservation_id" class="form-control"
                                placeholder="RES-20##-####"
                                oninput="this.value=this.value.toUpperCase()"
                                value="<?= htmlspecialchars($_SESSION['reservation_id'] ?? '') ?>"
                                <?= !empty($_SESSION['reservation_id']) ? 'readonly' : 'required' ?>>
                        </div>
                    </div>
                </div>

                <!-- Equipment Photos -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-camera me-1"></i>Equipment Evidence Photos
                        <span class="text-muted fw-normal text-lowercase">(1 – 4 images)</span>
                    </label>
                    <div class="photo-section">
                        <div class="camera-bar">
                            <i class="bi bi-camera-fill"></i>
                            <span>Tap <strong>Add Photo</strong> to capture from camera or pick from gallery</span>
                        </div>
                        <div class="preview-strip" id="previewStrip"></div>
                        <div class="upload-row">
                            <div class="upload-btn-wrap" id="uploadWrap">
                                <label class="upload-btn" id="uploadLabel">
                                    <i class="bi bi-plus-circle-fill"></i> Add Photos
                                </label>
                                <input type="file" id="photoInput"
                                       accept="image/jpeg,image/png,image/gif,image/webp"
                                       multiple>
                            </div>
                            <button type="button" class="camera-upload-btn" id="cameraBtn" title="Capture from camera">
                                <i class="bi bi-camera"></i> Camera
                            </button>
                            <span class="upload-status">
                                <span class="count-now" id="photoCount">0</span>
                                <span class="count-max"> / 4 photos &bull; each max 8 MB</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Comments -->
                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-chat-dots me-1"></i>Comments</label>
                    <div class="input-group" style="height:auto;">
                        <span class="input-group-text align-items-start pt-3"><i class="bi bi-pencil"></i></span>
                        <textarea id="comment" class="form-control" rows="4"
                            placeholder="Describe equipment used, any issues encountered…"
                            style="background:#f9fafb;resize:vertical;"></textarea>
                    </div>
                </div>

                <!-- Declaration checkbox -->
                <div class="verify-wrap">
                    <label class="verify-box" id="verifyBox" for="verifyCheck">
                        <input type="checkbox" class="verify-chk" id="verifyCheck">
                        <span class="verify-text">
                            I hereby confirm that I have <strong>completely finished the practical session</strong>,
                            all equipment has been properly handled and returned to its original condition,
                            and any faults, damages, or issues with the equipment have been
                            <strong>clearly and accurately described in the comments section</strong> above.
                            This declaration is <strong>true and accurate</strong> to the best of my knowledge.
                        </span>
                    </label>
                    <div class="verify-hint" id="verifyHint">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        You must confirm this declaration before submitting.
                    </div>
                </div>

                <!-- Actions -->
                <div class="action-bar">
                    <button type="button" class="btn btn-outline-success" onclick="resetForm()">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                    </button>
                    <button type="button" class="btn btn-success" id="submitBtn" onclick="submitLogbook()">
                        <i class="bi bi-check-circle me-2"></i>Submit Evidence
                    </button>
                </div>

                <p class="text-muted small text-center mt-3 mb-0">
                    <i class="bi bi-shield-check me-1"></i>
                    Your submission will be recorded in the practical logbook
                </p>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Camera Capture Modal -->
    <div class="camera-modal" id="cameraModal">
        <div class="camera-container">
            <video class="camera-video" id="cameraVideo" playsinline></video>
            <canvas class="camera-canvas" id="captureCanvas"></canvas>
            
            <div class="camera-header">
                <span class="camera-title">Capture Photo <span id="cameraCounter" style="font-size:0.9em;margin-left:8px;background:rgba(34,197,94,0.9);padding:4px 12px;border-radius:20px;">1/4</span></span>
                <button type="button" class="camera-close-btn" id="cameraCancelBtn" title="Close camera">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="camera-controls">
                <button type="button" class="camera-btn cancel" id="cameraStopBtn" title="Cancel">
                    <i class="bi bi-x"></i>
                </button>
                <button type="button" class="camera-btn" id="cameraCaptureBtn" title="Capture photo">
                    <i class="bi bi-camera-fill"></i>
                </button>
            </div>
            
            <div style="position:absolute;bottom:90px;left:0;right:0;text-align:center;color:white;font-size:0.9rem;display:none;" id="cameraSaveMsg">
                ✓ Saved! Take another? Click the camera icon again.
            </div>
        </div>
    </div>

    <!-- Success Modal — static backdrop, auto-redirects after 3 s -->
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog"><div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Submitted!</h5>
            </div>
            <div class="modal-body">
                <p id="successMessage" class="mb-2"></p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-clock me-1"></i>
                    Redirecting in <span id="countdown">1</span> seconds…
                </p>
            </div>
        </div></div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content border-0">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><p id="errorMessage" class="mb-0"></p></div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div></div>
    </div>

    <script>
    (function () {
        const MAX_BYTES  = 8 * 1024 * 1024;
        const OK_TYPES   = ['image/jpeg','image/png','image/gif','image/webp'];
        const MAX_PHOTOS = 4;

        // ── POST target — separate backend file ───────────────────────────
        const PROCESS_URL = 'logbook_process.php';

        const photoInput  = document.getElementById('photoInput');
        const strip       = document.getElementById('previewStrip');
        const countEl     = document.getElementById('photoCount');
        const uploadWrap  = document.getElementById('uploadWrap');
        const uploadLabel = document.getElementById('uploadLabel');
        const verifyBox   = document.getElementById('verifyBox');
        const verifyChk   = document.getElementById('verifyCheck');
        const verifyHint  = document.getElementById('verifyHint');
        const overlay     = document.getElementById('submitOverlay');
        const submitBtn   = document.getElementById('submitBtn');
        
        // Camera elements
        const cameraBtn = document.getElementById('cameraBtn');
        const cameraModal = document.getElementById('cameraModal');
        const cameraVideo = document.getElementById('cameraVideo');
        const captureCanvas = document.getElementById('captureCanvas');
        const cameraCaptureBtn = document.getElementById('cameraCaptureBtn');
        const cameraStopBtn = document.getElementById('cameraStopBtn');
        const cameraCancelBtn = document.getElementById('cameraCancelBtn');
        
        let files = [];  // { name, dataUrl }
        let cameraStream = null;

        function syncUI() {
            countEl.textContent = files.length;
            const full = files.length >= MAX_PHOTOS;
            uploadWrap.classList.toggle('disabled', full);
            uploadLabel.classList.toggle('disabled', full);
            cameraBtn.classList.toggle('disabled', full);
            uploadLabel.innerHTML = full
                ? '<i class="bi bi-check2-all"></i> Max photos added'
                : '<i class="bi bi-plus-circle-fill"></i> Add Photos';
        }

        function renderStrip() {
            strip.innerHTML = '';
            files.forEach((f, idx) => {
                const item = document.createElement('div');
                item.className = 'thumb-item';
                const img = document.createElement('img');
                img.src = f.dataUrl; img.alt = 'Photo ' + (idx + 1);
                const rm = document.createElement('button');
                rm.type = 'button'; rm.className = 'thumb-rm'; rm.title = 'Remove';
                rm.innerHTML = '<i class="bi bi-x"></i>';
                rm.addEventListener('click', () => {
                    files.splice(idx, 1);
                    renderStrip(); syncUI();
                    photoInput.value = '';
                });
                item.appendChild(img);
                item.appendChild(rm);
                strip.appendChild(item);
            });
            syncUI();
        }

        photoInput.addEventListener('change', function () {
            const selectedFiles = Array.from(this.files);
            photoInput.value = '';
            if (selectedFiles.length === 0) return;

            // Show warning if user selected more than 4 files
            let filesToProcess = selectedFiles;
            if (selectedFiles.length > MAX_PHOTOS) {
                showError('You selected ' + selectedFiles.length + ' images. Maximum is 4. Only the first 4 will be added.');
                filesToProcess = selectedFiles.slice(0, MAX_PHOTOS);
            }

            let added = 0;
            let skipped = 0;
            const errors = [];

            filesToProcess.forEach(file => {
                // Can't add more than needed (already at capacity)
                if (files.length + added >= MAX_PHOTOS) {
                    skipped++;
                    return;
                }
                // Validate file size
                if (file.size > MAX_BYTES) {
                    errors.push('"' + file.name + '" exceeds the 8 MB limit.');
                    skipped++;
                    return;
                }
                // Validate file type
                if (!OK_TYPES.includes(file.type)) {
                    errors.push('"' + file.name + '" is unsupported. Use JPG, PNG, or WEBP.');
                    skipped++;
                    return;
                }
                // Read and add
                const rd = new FileReader();
                rd.onload = e => {
                    files.push({ name: file.name, dataUrl: e.target.result });
                    renderStrip();
                };
                rd.readAsDataURL(file);
                added++;
            });

            // Show summary of validation errors (if any)
            if (errors.length > 0) {
                const msg = 'Issues with some files:\n' + errors.join('\n') + 
                    (skipped > errors.length ? '\n\nAdded ' + added + ', skipped ' + (skipped - errors.length) + '.' : '');
                showError(msg);
            }
        });

        verifyChk.addEventListener('change', function () {
            verifyBox.classList.toggle('checked', this.checked);
            if (this.checked) { verifyBox.classList.remove('invalid'); verifyHint.classList.remove('show'); }
        });

        // ────── CAMERA FUNCTIONALITY ──────────────────────────────
        cameraBtn.addEventListener('click', async function (e) {
            e.preventDefault();
            if (files.length >= MAX_PHOTOS) {
                showError('Maximum 4 photos allowed. Remove one first.');
                return;
            }
            
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' },
                    audio: false
                });
                cameraVideo.srcObject = cameraStream;
                updateCameraCounter();
                document.getElementById('cameraSaveMsg').style.display = 'none';
                cameraModal.classList.add('show');
            } catch (err) {
                if (err.name === 'NotAllowedError') {
                    showError('Camera permission denied. Please allow camera access and try again.');
                } else if (err.name === 'NotFoundError') {
                    showError('No camera device found. Please check your device.');
                } else {
                    showError('Unable to access camera: ' + err.message);
                }
            }
        });

        function updateCameraCounter() {
            const remaining = MAX_PHOTOS - files.length;
            document.getElementById('cameraCounter').textContent = (files.length + 1) + '/' + MAX_PHOTOS;
        }

        cameraCaptureBtn.addEventListener('click', function () {
            if (!cameraStream) return;
            
            // Validate: can't capture if already at max
            if (files.length >= MAX_PHOTOS) {
                showError('Maximum 4 photos reached. Remove one first if you want to capture another.');
                return;
            }
            
            const video = cameraVideo;
            const canvas = captureCanvas;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
            const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            files.push({ 
                name: 'camera_' + timestamp + '.jpg', 
                dataUrl: dataUrl 
            });
            renderStrip();
            
            // Show save message with count
            const saveMsg = document.getElementById('cameraSaveMsg');
            const remaining = MAX_PHOTOS - files.length;
            if (remaining > 0) {
                saveMsg.innerHTML = '✓ Saved! (' + files.length + '/' + MAX_PHOTOS + ') Capture ' + remaining + ' more?';
            } else {
                saveMsg.innerHTML = '✓ Saved! All ' + MAX_PHOTOS + ' photos captured!';
            }
            saveMsg.style.display = 'block';
            updateCameraCounter();
            
            // Auto-hide message after 2 seconds
            setTimeout(() => {
                saveMsg.style.display = 'none';
            }, 2500);
            
            // Check if max reached - auto close
            if (files.length >= MAX_PHOTOS) {
                setTimeout(() => {
                    closeCamera();
                    showError('✓ All 4 photos captured! Ready to submit your evidence.');
                }, 2500);
            }
        });

        function closeCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            cameraModal.classList.remove('show');
        }

        cameraStopBtn.addEventListener('click', closeCamera);
        cameraCancelBtn.addEventListener('click', closeCamera);

        window.resetForm = function () {
            document.getElementById('university_id').value = '';
            document.getElementById('reservation_id').value        = '';
            document.getElementById('comment').value       = '';
            files = []; renderStrip();
            verifyChk.checked = false;
            verifyBox.classList.remove('checked', 'invalid');
            verifyHint.classList.remove('show');
        };

        function showError(msg) {
            document.getElementById('errorMessage').textContent = msg;
            new bootstrap.Modal(document.getElementById('errorModal')).show();
        }

        window.submitLogbook = function () {
            const uniId   = document.getElementById('university_id').value.trim();
            const reservationId   = document.getElementById('reservation_id').value.trim();
            const comment = document.getElementById('comment').value.trim();

            if (!uniId) { showError('Please enter your University ID.'); return; }
            if (!reservationId) {
                showError('Please enter your Reservation ID.'); return;
            }
            if (!verifyChk.checked) {
                verifyBox.classList.add('invalid');
                verifyHint.classList.add('show');
                verifyBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                showError('Please read and confirm the declaration before submitting.');
                return;
            }

            // Build POST body
            const body = new URLSearchParams();
            body.append('university_id', uniId);
            body.append('reservation_id',         reservationId);
            body.append('comment',       comment);
            body.append('declaration',   '1');
            files.forEach(f => {
                body.append('photo_data[]',  f.dataUrl);
                body.append('photo_names[]', f.name);
            });

            overlay.classList.add('show');
            submitBtn.disabled = true;

            // ── POST to logbook_process.php ───────────────────────────────
            fetch(PROCESS_URL, {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    body.toString()
            })
            .then(r => r.json())
            .then(res => {
                overlay.classList.remove('show');
                submitBtn.disabled = false;

                if (res.ok) {
                    document.getElementById('successMessage').textContent = res.message;
                    new bootstrap.Modal(document.getElementById('successModal')).show();
                    let s = 1;
                    const tick = setInterval(() => {
                        s--;
                        document.getElementById('countdown').textContent = s;
                        if (s <= 0) { clearInterval(tick); window.location.href = '../index.php'; }
                    }, 1000);
                } else {
                    showError(res.message || 'Submission failed. Please try again.');
                }
            })
            .catch(() => {
                overlay.classList.remove('show');
                submitBtn.disabled = false;
                showError('Network error. Please check your connection and try again.');
            });
        };

        renderStrip();
    })();
    </script>

</body>
</html>
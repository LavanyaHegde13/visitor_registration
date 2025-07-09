<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Security - Visitor Registration</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0b3d91;
            --secondary-color: #1e88e5;
            --accent-color: #43a047;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --light-text: #f8f9fa;
            --border-radius: 12px;
        }

        body {
            background-color: var(--light-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            padding: 0;
            margin: 0;
            color: var(--dark-text);
            min-height: 100vh;
        }
        
        .app-container {
            max-width: 480px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            min-height: 100vh;
            position: relative;
        }
        
        .app-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            border-bottom: 4px solid var(--secondary-color);
            position: relative;
            margin-top: 0px;
        }
        
        .app-header h1 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 600;
        }

        .menu-bar {
            background-color: #002c6d;
            color: white;
            display: flex;
            justify-content: flex-end;
            padding: 8px 15px;
            font-size: 0.9rem;
        }

        .menu-bar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .menu-bar a:hover {
            color: #bbdefb;
        }

        .menu-bar i {
            margin-right: 5px;
        }
        
        .status-bar {
            background-color: #001f4d;
            color: white;
            padding: 6px 15px;
            font-size: 0.8rem;
            display: flex;
            justify-content: space-between;
        }
        
        .status-bar i {
            margin-left: 5px;
        }
        
        .app-body {
            padding: 20px;
        }
        
        .form-section {
            background-color: white;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .section-header {
            background-color: var(--secondary-color);
            color: white;
            padding: 12px 15px;
            font-weight: 500;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }
        
        .section-header i {
            margin-right: 10px;
        }
        
        .section-content {
            padding: 15px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.25);
            border-color: var(--secondary-color);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-text);
            margin-bottom: 8px;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn-primary:hover {
            background-color: #388e3c;
            border-color: #388e3c;
            transform: translateY(-1px);
        }
        
        .btn-primary:active {
            transform: translateY(1px);
        }
        
        .btn-outline-primary {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
            border-radius: 8px;
            padding: 10px 15px;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .selfie-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        #camera-preview {
            max-width: 100%;
            border-radius: 8px;
            margin: 15px 0;
            display: block;
            border: 2px solid var(--secondary-color);
        }
        
        #selfie-preview {
            max-width: 180px;
            height: auto;
            border-radius: 8px;
            margin: 15px auto;
            display: none;
            border: 2px solid var(--accent-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .camera-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
            width: 100%;
        }
        
        .camera-note {
            color: #6c757d;
            font-size: 0.9em;
            margin-top: 8px;
            text-align: center;
        }
        
        #error {
            border-radius: var(--border-radius);
            margin-top: 15px;
            padding: 12px;
            border-left: 4px solid #dc3545;
        }
        
        .required-badge {
            color: red;
            font-size: 1.2rem;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
            vertical-align: middle;
        }
        
        .floating-security-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: rgba(255,255,255,0.9);
            color: var(--primary-color);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .floating-security-badge i {
            margin-right: 5px;
            color: var(--accent-color);
        }
        
        .submit-container {
            padding: 20px;
            background-color: white;
            position: sticky;
            bottom: 0;
            border-top: 1px solid #dee2e6;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .input-with-icon input {
            padding-right: 40px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            text-align: center;
            width: 80%;
            max-width: 300px;
        }
        
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--accent-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin: 20px auto;
            animation: spin 2s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .selfie-status {
            text-align: center;
            margin: 10px 0;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header">
            <h1><i class="fas fa-id-card-alt"></i> LAB VISITOR REGISTRATION</h1>
        </div>
        <div class="menu-bar">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="logout.php" id="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <div class="app-body">
            <form id="registration-form" action="submit.php" method="POST">
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-user"></i> Visitor Information
                    </div>
                    <div class="section-content">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="required-badge">*</span></label>
                            <div class="input-with-icon">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter visitor's full name" required>
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="whatsapp" class="form-label">WhatsApp Number <span class="required-badge">*</span></label>
                            <div class="input-with-icon">
                                <input type="tel" class="form-control" id="whatsapp" name="whatsapp" placeholder="Enter WhatsApp number" required>
                                <i class="fab fa-whatsapp"></i>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-with-icon">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address (optional)">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="coming-from" class="form-label">Coming From <span class="required-badge">*</span></label>
                            <div class="input-with-icon">
                                <input type="text" class="form-control" id="coming-from" name="coming_from" placeholder="Company/Organization/Location" required>
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="meeting-to" class="form-label">Meeting With <span class="required-badge">*</span></label>
                            <div class="input-with-icon">
                                <input type="text" class="form-control" id="meeting-to" name="meeting_to" placeholder="Name of person being visited" required>
                                <i class="fas fa-user-tie"></i>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="selfie" class="form-label">Visitor Photo <span class="required-badge">*</span></label>
                            <div class="selfie-container">
                                <div class="selfie-status" id="selfie-status">Camera will be used when submitting</div>
                                <video id="camera-preview" autoplay playsinline></video>
                                <img id="selfie-preview" src="" alt="Visitor Photo">
                                <input type="hidden" id="selfie-data" name="selfie_data" required>
                                <div class="camera-buttons">
                                    <button type="button" id="rotate-btn" class="btn btn-outline-primary">
                                        <i class="fas fa-sync"></i> Switch Camera
                                    </button>
                                </div>
                                <p id="camera-note" class="camera-note" style="display: none;">
                                    <i class="fas fa-exclamation-circle"></i> Camera not accessible. Please enable camera permissions.
                                </p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="otp" class="form-label">Security OTP <span class="required-badge">*</span></label>
                            <div class="input-with-icon">
                                <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" required>
                                <i class="fas fa-key"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="error" class="alert alert-danger" role="alert" style="display:none;"></div>
            </form>
        </div>
        
        <div class="submit-container">
            <button type="button" id="submit-btn" class="btn btn-primary w-100">
                <i class="fas fa-check-circle"></i> REGISTER VISITOR
            </button>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal" id="loading-modal">
        <div class="modal-content">
            <h3>Please Wait</h3>
            <div class="spinner"></div>
            <p>Your profile is generating. Please wait for a while...</p>
        </div>
    </div>

    <!-- Bootstrap 5 JS (with Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // OTP for security verification
        const FIXED_OTP = "123456";
        
        // Get elements
        const form = document.getElementById('registration-form');
        const cameraPreview = document.getElementById('camera-preview');
        const rotateBtn = document.getElementById('rotate-btn');
        const selfieStatus = document.getElementById('selfie-status');
        const selfiePreview = document.getElementById('selfie-preview');
        const selfieData = document.getElementById('selfie-data');
        const submitBtn = document.getElementById('submit-btn');
        const loadingModal = document.getElementById('loading-modal');
        const errorEl = document.getElementById('error');
        const cameraNote = document.getElementById('camera-note');

        // Camera settings
        let currentStream = null;
        let facingMode = /Android|iPhone/i.test(navigator.userAgent) ? 'environment' : 'user'; // Prefer back camera on mobile
        
        // Start camera when page loads
        window.addEventListener('DOMContentLoaded', startCamera);
        
        // Function to stop camera
        function stopCamera() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
                cameraPreview.srcObject = null;
            }
        }

        // Function to start camera
        async function startCamera() {
            try {
                // Stop any existing stream
                stopCamera();

                // Show loading message
                selfieStatus.textContent = "Starting camera...";
                
                // Request camera with the current facing mode
                const constraints = {
                    video: { 
                        facingMode: { ideal: facingMode },
                        width: { ideal: 150 },
                        height: { ideal: 150 }
                    }
                };
                
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                cameraPreview.srcObject = stream;
                currentStream = stream;
                
                // Wait for video to be ready
                await new Promise(resolve => {
                    cameraPreview.onloadedmetadata = resolve;
                });
                
                selfieStatus.textContent = facingMode === 'environment' ? 
                    "Ready! Using back camera" : 
                    "Ready! Using front camera";
                cameraPreview.style.display = 'block';
                selfiePreview.style.display = 'none';
                cameraNote.style.display = 'none';
                errorEl.style.display = 'none';
                submitBtn.disabled = false;
                
            } catch (err) {
                console.error('Error accessing camera:', err);
                selfieStatus.textContent = "Could not access camera. Please check camera permissions.";
                cameraPreview.style.display = 'none';
                selfiePreview.style.display = 'none';
                cameraNote.style.display = 'block';
                errorEl.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Error accessing camera: ${err.message}`;
                errorEl.style.display = 'block';
                submitBtn.disabled = true;
            }
        }

        // Function to capture photo
        function capturePhoto() {
            if (!currentStream || cameraPreview.videoWidth === 0 || cameraPreview.videoHeight === 0) {
                return null;
            }
            
            const canvas = document.createElement('canvas');
            canvas.width = cameraPreview.videoWidth;
            canvas.height = cameraPreview.videoHeight;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(cameraPreview, 0, 0, canvas.width, canvas.height);
            
            return canvas.toDataURL('image/jpeg', 0.8);
        }

        // Toggle camera between front and back
        rotateBtn.addEventListener('click', () => {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            selfieStatus.textContent = facingMode === 'environment' ? 
                "Switching to back camera..." : 
                "Switching to front camera...";
            startCamera();
        });
        
        // Function to show loading modal
        function showLoadingModal() {
            loadingModal.style.display = 'flex';
        }
        
        // Function to hide loading modal
        function hideLoadingModal() {
            loadingModal.style.display = 'none';
        }

        // Handle form submission
        submitBtn.addEventListener('click', async function() {
            // Validate the form
            const name = document.getElementById('name').value.trim();
            const whatsapp = document.getElementById('whatsapp').value.trim();
            const comingFrom = document.getElementById('coming-from').value.trim();
            const meetingTo = document.getElementById('meeting-to').value.trim();
            const otp = document.getElementById('otp').value.trim();

            let errors = [];

            if (!name) errors.push('<i class="fas fa-exclamation-circle"></i> Name is required');
            if (!whatsapp) errors.push('<i class="fas fa-exclamation-circle"></i> WhatsApp number is required');
            if (!comingFrom) errors.push('<i class="fas fa-exclamation-circle"></i> Coming From is required');
            if (!meetingTo) errors.push('<i class="fas fa-exclamation-circle"></i> Meeting To is required');
            if (!otp) errors.push('<i class="fas fa-exclamation-circle"></i> OTP is required');
            else if (otp !== FIXED_OTP) errors.push('<i class="fas fa-exclamation-circle"></i> Invalid OTP code (use 123456 for testing)');

            if (errors.length > 0) {
                errorEl.innerHTML = errors.join('<br>');
                errorEl.style.display = 'block';
                errorEl.scrollIntoView({ behavior: 'smooth' });
                return;
            }
            
            // Ensure camera is active
            if (!currentStream) {
                await startCamera();
                await new Promise(resolve => setTimeout(resolve, 500)); // Wait for stream to stabilize
            }
            
            // Capture the photo
            const photoData = capturePhoto();
            
            if (!photoData) {
                errorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Could not capture photo. Please ensure camera is active.';
                errorEl.style.display = 'block';
                errorEl.scrollIntoView({ behavior: 'smooth' });
                return;
            }
            
            // Set the photo data to the hidden field
            selfieData.value = photoData;
            
            // Display the selfie preview and hide camera
            selfiePreview.src = photoData;
            selfiePreview.style.display = 'block';
            cameraPreview.style.display = 'none';
            stopCamera();
            
            // Show loading modal
            showLoadingModal();
            
            // Simulate profile generation process (3 seconds delay for demo)
            setTimeout(() => {
                form.submit();
            }, 3000);
        });
        
        // Handle visibility change to manage camera
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                if (!currentStream) {
                    startCamera();
                }
            } else {
                stopCamera();
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', stopCamera);
    </script>
</body>
</html>
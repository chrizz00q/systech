<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

// You can fetch and display user data here, for example:
$user_name = $_SESSION['user'];  // User info
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Capture with Auto Flashlight</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            background-color: #f5f5f5;
            color: #333;
        }

        .sidenav {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #333;
            padding-top: 20px;
            padding-right: 10px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
        }

        .sidenav .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 10px;
            border: 3px solid #fff;
        }

        .sidenav .profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidenav .name {
            font-size: 18px;
            color: white;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }

        .sidenav a {
            color: white;
            padding: 12px;
            text-decoration: none;
            font-size: 18px;
            text-align: center;
            width: 100%;
            border-bottom: 1px solid #bbb;
        }

        .sidenav a:hover {
            background-color: #575757;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: linear-gradient(135deg, #ffffff, #f7f7f7);
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            padding: 25px;
            width: 100%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: auto;
            position: relative;
        }

        h2 {
            color: #5d4037;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        video {
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            margin-top: 10px;
            transform: scaleX(-1);
        }

        .flashlight-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            display: none;
        }

        canvas,
        img {
            width: 100%;
            border-radius: 12px;
            margin-top: 15px;
        }

        button {
            background: #6d4c41;
            color: white;
            border: none;
            padding: 12px 18px;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 15px;
            transition: 0.3s;
            width: 100%;
            max-width: 200px;
        }

        button:hover {
            background: #5d4037;
        }

        #messageBox {
            font-size: 20px;
            font-weight: bold;
            color: #6d4c41;
            padding: 10px;
            width: 100%;
            text-align: center;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            transition: all 0.3s ease-in-out;
        }

        #messageBox.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        #messageBox.hide {
            opacity: 0;
            transform: translateX(-50%) translateY(20px);
        }

        #messageText {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            display: none;
        }

        #send {
            display: none;
        }

        /* New Clock In/Out Section with Radio Buttons */
        #clockInOutSection {
            margin-top: 20px;
            display: none;
        }

        #clockInOutSection label {
            margin-right: 15px;
        }
    </style>
</head>

<body>

    <!-- Side navigation -->
    <div class="sidenav">
        <div class="profile-img">
            <!-- Display user profile image from session -->
            <img src="<?php echo isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'default-image.jpg'; ?>" alt="Profile Image">
        </div>

        <div class="name">
            <!-- Display user name from session -->
            <?php echo isset($_SESSION['user']) ? $_SESSION['user'] : 'Guest'; ?>
        </div>

        <a href="user-gallery.php">Gallery</a>
        <a href="setting.php">Settings</a>
        <a href="#">About</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main content with camera -->
    <div class="main-content">
        <div class="container">
            <h2>Take a Photo with Date, Time, and Location</h2>
            <video id="video" autoplay></video>
            <div id="flashlightOverlay" class="flashlight-overlay"></div>
            <canvas id="canvas" style="display:none;"></canvas>
            <img id="previewImage" alt="Photo Preview" style="display:none;">
            <button id="capture" style="display:none;">Capture Photo</button>
            <button id="retake" style="display:none;">Retake Photo</button>
            <button id="confirm" style="display:none;">Confirm Photo</button>
            <div id="messageBox"></div>
            <textarea id="messageText" placeholder="Enter your message..." style="display:none;"></textarea>
            <button id="send" style="display:none;">Send</button>

            <!-- Clock In/Out Section -->
            <div id="clockInOutSection">
                <label for="clockIn">
                    <input type="radio" name="clock" id="clockIn" /> Clock In
                </label><br>
                <label for="clockOut">
                    <input type="radio" name="clock" id="clockOut" /> Clock Out
                </label>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById("video");
        const canvas = document.getElementById("canvas");
        const ctx = canvas.getContext("2d");
        const previewImage = document.getElementById("previewImage");
        const captureBtn = document.getElementById("capture");
        const retakeBtn = document.getElementById("retake");
        const confirmBtn = document.getElementById("confirm");
        const messageBox = document.getElementById("messageBox");
        const flashlightOverlay = document.getElementById("flashlightOverlay");

        let attempts = 0;
        const maxAttempts = 3;

        // Start Camera (Mirrored Preview)
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
                captureBtn.style.display = "block"; // Show the capture button when camera is ready
                detectBrightness();
            })
            .catch(err => console.error("Camera access denied:", err));

        // Detect Brightness from Video Feed
        function detectBrightness() {
            const videoWidth = video.videoWidth;
            const videoHeight = video.videoHeight;
            const frame = document.createElement("canvas");
            const frameCtx = frame.getContext("2d");
            frame.width = videoWidth;
            frame.height = videoHeight;

            setInterval(() => {
                frameCtx.drawImage(video, 0, 0, videoWidth, videoHeight);
                const imageData = frameCtx.getImageData(0, 0, videoWidth, videoHeight);
                const data = imageData.data;

                let totalBrightness = 0;
                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i];
                    const g = data[i + 1];
                    const b = data[i + 2];
                    totalBrightness += (r + g + b) / 3;
                }

                const averageBrightness = totalBrightness / (data.length / 4);
                if (averageBrightness < 100) {
                    flashlightOverlay.style.display = "block";
                } else {
                    flashlightOverlay.style.display = "none";
                }
            }, 500);
        }

        // Capture Photo
        captureBtn.addEventListener("click", () => {
            if (attempts < maxAttempts) {
                attempts++;
                takePhoto();
                if (attempts < maxAttempts) {
                    captureBtn.style.display = "none";
                    retakeBtn.style.display = "block";
                    confirmBtn.style.display = "block";
                } else {
                    autoConfirm();
                }
            }
        });

        function takePhoto() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            ctx.save();
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            ctx.restore();

            const dateTime = new Date().toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            // Add location (human-readable address)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    getLocationName(latitude, longitude, (location) => {
                        ctx.fillStyle = "#6d4c41";
                        ctx.font = "20px Arial";
                        ctx.fillText(location, 20, canvas.height - 40);
                        ctx.fillText(dateTime, 20, canvas.height - 70);
                        previewImage.src = canvas.toDataURL("image/png");
                        previewImage.style.display = "block";
                        video.style.display = "none";
                    });
                });
            } else {
                ctx.fillStyle = "#6d4c41";
                ctx.font = "24px Arial";
                ctx.fillText(dateTime, 20, canvas.height - 70);
                previewImage.src = canvas.toDataURL("image/png");
                previewImage.style.display = "block";
                video.style.display = "none";
            }
        }

        // Fetch Human-Readable Location Name from OpenStreetMap Nominatim API
        function getLocationName(latitude, longitude, callback) {
            const url = `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const location = data.display_name;
                    callback(location);
                });
        }

        // Retake Photo
        retakeBtn.addEventListener("click", () => {
            previewImage.style.display = "none";
            video.style.display = "block";
            captureBtn.style.display = "block";
            retakeBtn.style.display = "none";
            confirmBtn.style.display = "none";
        });

        // Confirm Photo
        confirmBtn.addEventListener("click", () => {
            messageBox.innerText = "Photo confirmed!";
            messageBox.classList.add("show");
            setTimeout(() => messageBox.classList.remove("show"), 2000);
        });

        // Auto Confirm Photo After Maximum Attempts
        function autoConfirm() {
            confirmBtn.style.display = "none";
            retakeBtn.style.display = "none";
            messageBox.innerText = "Photo confirmed!";
            messageBox.classList.add("show");
            setTimeout(() => messageBox.classList.remove("show"), 2000);
        }
    </script>
</body>

</html>

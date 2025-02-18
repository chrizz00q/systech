<?php
// Start session to access logged-in user's data
session_start();

// Database configuration
$host = 'localhost';  // your database host
$username = 'root';   // your database username
$password = '';       // your database password
$database = 'systech'; // your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming the user is logged in, and their user ID is stored in session
$userId = $_SESSION['user_id'] ?? null; // Use the logged-in user's ID from session

// Check if user ID is available (if not, they are not logged in)
if ($userId) {
    // Query to fetch the user's name from the users table
    $sql = "SELECT name, contact_number, email, profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        $userName = $userData['name']; // Set the username
        $userEmail = $userData['email']; // Get user email
        $userContact = $userData['contact_number']; // Get user contact number
        $profileImage = $userData['profile_image']; // Get profile image
    } else {
        $userName = 'Guest'; // Default if no user found
        $userEmail = '';
        $userContact = '';
        $profileImage = 'default-image.jpg'; // Default image if none exists
    }
} else {
    $userName = 'Guest'; // If not logged in, show 'Guest'
    $userEmail = '';
    $userContact = '';
    $profileImage = 'default-image.jpg'; // Default image if not logged in
}

// Function to get user settings like contact number and email
function getUserSettings($userId) {
    global $conn;
    $sql = "SELECT preference_key, preference_value FROM settings WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['preference_key']] = $row['preference_value'];
    }

    return $settings;
}

// Function to update user settings
function updateUserSetting($userId, $preferenceKey, $preferenceValue) {
    global $conn;
    $sql = "SELECT id FROM settings WHERE user_id = ? AND preference_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $preferenceKey);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $sql = "UPDATE settings SET preference_value = ? WHERE user_id = ? AND preference_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $preferenceValue, $userId, $preferenceKey);
    } else {
        $sql = "INSERT INTO settings (user_id, preference_key, preference_value) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $userId, $preferenceKey, $preferenceValue);
    }

    $stmt->execute();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newContactNumber = $_POST['contact_number'];
    $newEmail = $_POST['email'];

    // Update contact number and email
    updateUserSetting($userId, 'contact_number', $newContactNumber);
    updateUserSetting($userId, 'email', $newEmail);

    // Get user settings (including contact number and email)
    $userSettings = getUserSettings($userId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(to right, #B8860b, #000); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            color: #fff;
        }

        .container { 
            background: #fff; 
            color: #333; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1); 
            width: 100%; 
            max-width: 450px; /* Increased max-width */
            text-align: center; 
        }

        .profile-header h2 { 
            margin-bottom: 30px; 
            color: #333; 
            font-size: 1.8em;
            font-weight: 600;
            text-transform: uppercase; /* Added text transformation for emphasis */
        }

        .profile-info { 
            display: flex; 
            align-items: center; 
            margin-bottom: 25px; 
            text-align: left; 
            flex-wrap: wrap; /* Ensures responsiveness */
        }

        .profile-image { 
            width: 100px; 
            height: 100px; 
            border-radius: 50%; 
            background-color: #ddd; 
            margin-right: 20px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            overflow: hidden; 
        }

        .profile-image img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }

        .profile-text { 
            display: flex; 
            flex-direction: column; 
            align-items: flex-start; /* Align text to the left */
        }

        .profile-name { 
            font-weight: bold; 
            font-size: 1.4em; /* Adjusted font size for better visibility */
            color: #333; 
            margin-bottom: 5px; 
        }

        .profile-details { 
            font-size: 0.9em; 
            color: #666; 
            line-height: 1.5;
        }

        .settings-list { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
        }

        .settings-list li { 
            padding: 18px 0; 
            border-bottom: 1px solid #ddd; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }

        .settings-list li a { 
            color: #333; 
            text-decoration: none; 
            font-size: 1.1em; 
            transition: color 0.3s ease; 
        }

        .settings-list li a:hover { 
            color: #B8860b; 
        }

        .settings-list li span { 
            color: #888; 
            font-size: 1.3em; 
        }

        .settings-list li:last-child { 
            border-bottom: none; 
        }

        input[type="text"] {
            padding: 10px; /* Increased padding for better usability */
            font-size: 1.1em; /* Increased font size for easier reading */
            margin-bottom: 15px; /* Increased bottom margin for spacing */
            width: 100%;
            border-radius: 6px;
            border: 1px solid #ddd;
            background-color: #f8f8f8;
        }

        input[type="file"] {
            padding: 10px; /* Increased padding for better usability */
            font-size: 1.1em; 
            margin-bottom: 20px; /* Increased margin for better spacing */
            width: 100%;
            border-radius: 6px;
            border: 1px solid #ddd;
            background-color: #f8f8f8;
        }

        .save-btn { 
            background-color: #B8860b; 
            color: #fff; 
            border: none; 
            padding: 12px 24px; /* Adjusted padding for better button size */
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 1.2em; /* Increased font size for better visibility */
            margin-top: 20px; 
            transition: background-color 0.3s ease;
        }

        .save-btn:hover { 
            background-color: #8b5e2e; 
        }

        /* Mobile responsiveness */
        @media (max-width: 600px) { 
            .container { 
                padding: 25px; 
                width: 90%; 
            }

            .profile-info { 
                flex-direction: column; 
                align-items: flex-start; 
            }

            .profile-image { 
                margin-bottom: 15px; 
            }

            .profile-name { 
                font-size: 1.3em; /* Adjusted font size for mobile */
            }

            .settings-list li { 
                padding: 12px 0; 
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <h2>Profile Settings</h2>
        </div>

        <div class="profile-info">
            <div class="profile-image">
                <img id="profile-img" src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Picture">  
            </div>
            <div class="profile-text">
                <div class="profile-name"><?php echo htmlspecialchars($userName); ?></div>
                <div class="profile-details">
                    SYSTECH<br>
                    INTEGRATION & SECURITY SOLUTIONS, INC<br>
                    Quezon City, Metro Manila, Philippines
                </div>
            </div>
        </div>

        <form action="settings.php" method="POST">
            <ul class="settings-list">
                <li>
                    <a href="javascript:void(0);">Edit profile picture</a>
                    <input type="file" id="profile-picture-input" onchange="changeProfilePicture()">
                </li>
            </ul>

            <div id="contact-info">
                <label for="contact-number">Contact Number:</label>
                <input type="text" id="contact-number" name="contact_number" value="<?php echo isset($userSettings['contact_number']) ? htmlspecialchars($userSettings['contact_number']) : ''; ?>" placeholder="Enter new contact number">

                <label for="email">Email:</label>
                <input type="text" id="email" name="email" value="<?php echo isset($userSettings['email']) ? htmlspecialchars($userSettings['email']) : ''; ?>" placeholder="Enter new email">
            </div>

            <button type="submit" class="save-btn">Save Changes</button>
        </form>
    </div>

    <script>
        function changeProfilePicture() {
            const fileInput = document.getElementById('profile-picture-input');
            const file = fileInput.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onloadend = function () {
                    document.getElementById('profile-img').src = reader.result;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>

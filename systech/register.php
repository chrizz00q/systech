<?php
session_start(); // Start the session first

// Include the database connection
include('database.php');

$error = ""; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input and sanitize
    $username = trim($_POST['user']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $contact_number = trim($_POST['contact_number']);

    // Validate input fields
    if (empty($username) || empty($email) || empty($password) || empty($contact_number)) {
        $error = 'All fields are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = 'Email is already registered!';
        } else {
            // Insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, contact_number) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $contact_number);
            
            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                header('Location: login.php'); // Redirect to login page
                exit;
            } else {
                $error = 'Failed to register user. Please try again.';
            }

            $stmt->close();
        }

        $check_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(to right, #B8860b, #000); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 320px; text-align: center; }
        h2 { margin-bottom: 20px; }
        input { width: 90%; max-width: 280px; padding: 10px; margin: 10px auto; border: 1px solid #ccc; border-radius: 5px; display: block; }
        button { background: #B8860b; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; border-radius: 5px; transition: .3s; }
        button:hover { background: #644906; }
        .error { color: red; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="login-container">
        <form action="register.php" method="post">
            <h2>Register</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <input type="text" name="user" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="contact_number" placeholder="Contact Number" required>
            <button type="submit">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>
</html>

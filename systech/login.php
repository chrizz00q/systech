<?php
// Include the database connection
include('database.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get login input
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to find user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User found, verify password
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            header('Location: index.php'); // Redirect to dashboard
            exit;
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'No user found with this email';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
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
        <form action="login.php" method="post">
            <h2>Login</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>
</body>
</html>

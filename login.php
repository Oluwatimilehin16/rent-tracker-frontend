<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['submit'])) {
    // Call your API instead of direct database
   $api_url = 'https://rent-tracker-api.onrender.com/login.php'; // make sure this is the right file

$post_data = [
    'email' => $_POST['email'],
    'password' => $_POST['password']
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
    die('API request failed.');
}

$result = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('JSON decode error: ' . json_last_error_msg());
}

if (!isset($result['success'])) {
    die('Malformed API response: ' . print_r($result, true));
}

    if ($result['success']) {
        $user = $result['user'];
        $_SESSION['users_id'] = $user['id'];
        $_SESSION['users_name'] = $user['firstname'];
        $_SESSION['users_email'] = $user['email'];
        $_SESSION['users_role'] = $user['users_role'];

        // Redirect based on role
        if ($user['users_role'] == 'landlord') {
            $_SESSION['landlord_id'] = $user['id'];
            $_SESSION['landlord_name'] = $user['firstname'];
            $_SESSION['landlord_email'] = $user['email'];
            header("Location: add_bill.php");
            exit();
        } else {
            $_SESSION['tenant_id'] = $user['id'];
            $_SESSION['tenant_name'] = $user['firstname'];
            $_SESSION['tenant_email'] = $user['email'];

            if ($user['class_status'] == 'in_class') {
                header('Location: dashboard.php');
                exit();
            } else {
                header('Location: join_class.php');
                exit();
            }
        }
    } else {
        $message[] = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rent & Utility Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="logo">
    <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
</div>

<?php
if (isset($message)) {
    foreach ($message as $message) { 
        echo '<div class="message"><span>' . $message . '</span></div>';
    }
}
?>

<div class="login-container">
    <form class="login-form" method="post">
        <h2>Login to Your Account</h2>
        <div class="input-group">
            <input type="text" placeholder="Email or Username" name="email" required>
        </div>
        <div class="input-group">
            <input type="password" placeholder="Password" name="password" required>
        </div>
        <div class="options">
            <a href="#">Forgot Password?</a>
        </div>
        <button type="submit" name="submit" class="login-btn">Login</button>
        <p class="signup-text">Don't have an account? <a href="register.php">Sign Up</a></p>
    </form>
</div>

<footer>
    <div class="footer-content">
        <p>&copy; 2025 Rent Tracker. All rights reserved.</p>
    </div>
</footer>
<script src="script.js"></script>
</body>
</html>



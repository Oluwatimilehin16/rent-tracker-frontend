<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['submit'])) {
    $api_url = 'https://rent-tracker-api.onrender.com/register.php'; // Change to your API URL
    
    $post_data = [
        'firstname' => $_POST['firstname'],
        'lastname' => $_POST['lastname'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'users_role' => $_POST['users_role']
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($result['success']) {
        $user = $result['user'];
        
        // Set session variables
        $_SESSION['users_id'] = $user['id'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['users_role'] = $user['users_role'];
        
        // Redirect based on role
        if ($user['users_role'] === "tenant") {
            header('location: dashboard.php');
            exit();
        } elseif ($user['users_role'] === "landlord") {
            header("Location: add_bill.php");
            exit();
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
    <title>Register - Rent & Utility Tracker</title>
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
        <form class="reg-form" action="register.php" method="POST">
        <h2>Register</h2>
        <div class="input-groupreg">
            <input type="text" name="firstname" placeholder="First Name" required>
        </div>
        <div class="input-groupreg">
            <input type="text" name="lastname" placeholder="Last Name" required>
        </div>
        <div class="input-groupreg">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-groupreg">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <div class="input-groupreg">
            <select name="users_role" class="select-field" required>
            <option value="">Select user type</option>
                <option value="tenant">Tenant</option>
                <option value="landlord">Landlord</option>
            </select>
        </div>
            <button type="submit" name="submit" class="login-btn">Register</button>
            <p class="signup-text">Already have an account? <a href="login.php">Login</a></p>
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

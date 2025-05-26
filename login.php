<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Fetch user based on email
    $select_user = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'") or die(mysqli_error($conn));

    if (mysqli_num_rows($select_user) > 0) {
        $row = mysqli_fetch_assoc($select_user);

        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['users_id'] = $row['id']; 
            $_SESSION['users_name'] = $row['firstname'];
            $_SESSION['users_email'] = $row['email'];
            $_SESSION['users_role'] = $row['users_role']; // Ensure this matches your DB field

            // Redirect based on user role
            if ($row['users_role'] == 'landlord') {
                $_SESSION['landlord_id'] = $row['id'];
                $_SESSION['landlord_name'] = $row['firstname'];
                $_SESSION['landlord_email'] = $row['email'];
                header("Location: add_bill.php");
                exit();
            } else {
                $_SESSION['tenant_id'] = $row['id'];
                $_SESSION['tenant_name'] = $row['firstname'];
                $_SESSION['tenant_email'] = $row['email'];

                // Check if tenant is already part of a class
                $tenant_id = $row['id'];
                $class_check = mysqli_query($conn, "SELECT * FROM class_members WHERE tenant_id = '$tenant_id'");

                if (!$class_check) {
                    die("Query Error: " . mysqli_error($conn));
                }

                if (mysqli_num_rows($class_check) > 0) {
                    // Tenant already in class
                    header('Location: dashboard.php');
                    exit();
                } else {
                    // Tenant not yet in any class
                    header('Location: join_class.php');
                    exit();
                }
            }
        } else {
            $message[] = 'Incorrect password!';
        }
    } else {
        $message[] = 'Incorrect email or password!';
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



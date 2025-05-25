<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['submit'])) {
    $firstname = htmlspecialchars($_POST["firstname"]);
    $lastname  = htmlspecialchars($_POST["lastname"]);
    $email     = htmlspecialchars($_POST["email"]);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $users_role = htmlspecialchars($_POST["users_role"]);

    // Check if the user already exists
    $select_user = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'") or die(mysqli_error($conn));

    if (mysqli_num_rows($select_user) > 0) {
        $message[] = 'User already exists!';
    } else {
        $query = "INSERT INTO `users`(`firstname`, `lastname`, `email`, `password`, `users_role`) 
                  VALUES ('$firstname','$lastname', '$email', '$password', '$users_role')";
        if (mysqli_query($conn, $query)) {
            $users_id = mysqli_insert_id($conn);

            // Set session variables
            $_SESSION['users_id'] = $users_id;
            $_SESSION['firstname'] = $firstname;
            $_SESSION['email'] = $email;
            $_SESSION['users_role'] = $users_role;

            // Redirect based on role
            if ($users_role === "tenant") {
                header('location: dashboard.php');
                exit();
            } elseif ($users_role === "landlord") {
                header("Location: add_bill.php");
                exit();
            }
        } else {
            $message[] = 'Registration failed, please try again!';
        }
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

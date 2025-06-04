<?php
include 'config.php';
header('Content-Type: application/json');

// Get data from mobile app
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

// Check if user exists (using your exact code)
$select_user = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'") or die(mysqli_error($conn));

if (mysqli_num_rows($select_user) > 0) {
    $row = mysqli_fetch_assoc($select_user);
    
    // Verify password (using your exact code)
    if (password_verify($password, $row['password'])) {
        // Login successful - send user data to mobile app
        $response = array(
            'success' => true,
            'message' => 'Login successful!',
            'user' => array(
                'id' => $row['id'],
                'firstname' => $row['firstname'],
                'email' => $row['email'],
                'users_role' => $row['users_role']
            )
        );
    } else {
        $response = array(
            'success' => false,
            'message' => 'Incorrect password!'
        );
    }
} else {
    $response = array(
        'success' => false,
        'message' => 'Incorrect email or password!'
    );
}

echo json_encode($response);
?>
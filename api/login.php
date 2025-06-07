<?php
header('Content-Type: application/json'); // Tell browser this is JSON
include '../config.php';

// Get data sent from mobile app
$input = json_decode(file_get_contents('php://input'), true);
$email = mysqli_real_escape_string($conn, $input['email']);
$password = mysqli_real_escape_string($conn, $input['password']);

// Your same login logic
$select_user = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'") or die(mysqli_error($conn));

if (mysqli_num_rows($select_user) > 0) {
    $row = mysqli_fetch_assoc($select_user);
    
    if (password_verify($password, $row['password'])) {
        // SUCCESS - Send back user info
        if ($row['users_role'] == 'landlord') {
            echo json_encode([
                'success' => true,
                'user_type' => 'landlord',
                'user_id' => $row['id'],
                'user_name' => $row['firstname'],
                'user_email' => $row['email'],
                'redirect' => 'landlord_dashboard'
            ]);
        } else {
            // Check if tenant is in class
            $tenant_id = $row['id'];
            $class_check = mysqli_query($conn, "SELECT * FROM class_members WHERE tenant_id = '$tenant_id'");
            
            if (mysqli_num_rows($class_check) > 0) {
                echo json_encode([
                    'success' => true,
                    'user_type' => 'tenant',
                    'user_id' => $row['id'],
                    'user_name' => $row['firstname'],
                    'user_email' => $row['email'],
                    'redirect' => 'tenant_dashboard'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'user_type' => 'tenant',
                    'user_id' => $row['id'],
                    'user_name' => $row['firstname'],
                    'user_email' => $row['email'],
                    'redirect' => 'join_class'
                ]);
            }
        }
    } else {
        // WRONG PASSWORD
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect password!'
        ]);
    }
} else {
    // USER NOT FOUND
    echo json_encode([
        'success' => false,
        'message' => 'Incorrect email or password!'
    ]);
}
?>
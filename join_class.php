<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['tenant_id']) || $_SESSION['users_role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];

// Handle form submission to join a class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_class'])) {
    $class_code = mysqli_real_escape_string($conn, $_POST['class_code']);

    // Check if class exists
    $class_check = mysqli_query($conn, "SELECT * FROM classes WHERE class_code = '$class_code'");
    if (mysqli_num_rows($class_check) > 0) {
        $class = mysqli_fetch_assoc($class_check);
        $class_id = $class['id'];

        // Check if tenant already belongs to a class
        $already_joined = mysqli_query($conn, "SELECT * FROM class_members WHERE tenant_id = '$tenant_id'");
        if (mysqli_num_rows($already_joined) > 0) {
            $error_message = "You’ve already joined a class.";
        } else {
            // Insert into class_members
            $join_class_query = "INSERT INTO class_members (class_id, tenant_id) VALUES ('$class_id', '$tenant_id')";
            $insert_member = mysqli_query($conn, $join_class_query);

            // Insert into user_classes so their name appears in view_bills
            $insert_user_class = mysqli_query($conn, "INSERT INTO user_classes (class_id, user_id) VALUES ('$class_id', '$tenant_id')");

            if ($insert_member && $insert_user_class) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Error joining class. Try again later.";
            }
        }
    } else {
        $error_message = "Class with that code doesn't exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Class</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="join_class.css">
</head>
<body>

<div class="join-class-container">
    <div class="join-class">
        <h3>Connect with Your Landlord</h3>
        
        <?php
        if (isset($error_message)) {
            echo "<p class='error'>$error_message</p>";
        }
        ?>

        <form method="POST" action="">
            <label for="class_code">Enter your access code:</label>
            <input type="text" id="class_code" name="class_code" required placeholder="Enter your unique access code" autofocus>
            <button type="submit" name="join_class">Join</button>
        </form>

        <div class="info">
            <p>Not sure about the access code? Contact your landlord or check your email for more details.</p>
        </div>
    </div>
</div>

</body>
</html>


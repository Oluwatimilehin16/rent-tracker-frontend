<?php
include 'config.php';
session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$class_message = "";

if (isset($_POST['create_class'])) {
    $class_name = trim($_POST['class_name']);
    $class_code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 6); // random code

    $stmt = $conn->prepare("INSERT INTO classes (landlord_id, class_name, class_code) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $landlord_id, $class_name, $class_code);
    $stmt->execute();

    $class_message = "✅ Class <strong>$class_name</strong> created successfully! <br> 
                      Share this code with tenants: <span class='code'>$class_code</span>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class</title>
    <link rel="stylesheet" href="create_class.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="page-wrapper">
<header>
  <div class="top-bar">
    <div class="logo">
      <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
    </div>
    <div class="hamburger" onclick="toggleMenu()">
      <i class="fas fa-bars"></i>
    </div>
  </div>

  <nav id="main-nav">
    <nav>
        <ul>
            <li><a href="create_class.php"><i class="fas fa-user-plus"></i> Invite</a></li>
            <li><a href="add_bill.php" class="active"><i class="fas fa-plus-circle"></i> Add Bill</a></li>
            <li><a href="view_bills.php"><i class="fas fa-list-alt"></i> View Bills</a></li>
            <li><a href="create_groupchat.php"><i class="fas fa-users"></i> Create group chat</a></li>
            <li><a href="landlord_group_chats.php">💬 View My Group Chats</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</header>


<div class="container">
    <h1>Create a New Group</h1>
    <p>Give your group (tenants) a name and a unique code will be generated automatically.</p>

    <?php if (!empty($class_message)) : ?>
        <div class="success-box"><?php echo $class_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="create_class.php">
        <input type="text" name="class_name" placeholder="e.g., Apartment A" required>
        <button type="submit" name="create_class">Create Group</button>
    </form>
</div>
</div>
<footer>
    <div class="footer-content">
        <p>&copy; <?php echo date("Y"); ?> Rent & Utility Tracker. All rights reserved. Developed by Your Rentals Team</p>
    </div>
</footer>
<script>
function toggleMenu() {
    const navList = document.querySelector('#main-nav ul');
    navList.classList.toggle('show');
}
</script>

</body>
</html>

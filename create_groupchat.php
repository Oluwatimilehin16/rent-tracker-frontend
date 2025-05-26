<?php
include 'config.php';
session_start();

// Ensure only landlord can access
if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];

// Fetch all landlord's classes
$classes_query = mysqli_query($conn, "SELECT * FROM classes WHERE landlord_id = '$landlord_id'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = mysqli_real_escape_string($conn, $_POST['group_name']);
    $selected_classes = $_POST['class_ids']; // array

    if (!empty($group_name) && !empty($selected_classes)) {
        // Insert into group_chats
        mysqli_query($conn, "INSERT INTO group_chats (landlord_id, name) VALUES ('$landlord_id', '$group_name')");
        $group_id = mysqli_insert_id($conn);

        // Insert selected classes into group_chat_classes
        foreach ($selected_classes as $class_id) {
            mysqli_query($conn, "INSERT INTO group_chat_classes (group_id, class_id) VALUES ('$group_id', '$class_id')");
        }

        echo "<script>alert('Group chat created successfully!'); window.location='create_groupchat.php';</script>";
        exit();
    } else {
        echo "<script>alert('Please provide a name and select at least one class.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create Group Chat</title>
    <link rel="stylesheet" href="create_groupchat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- FontAwesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
    </div>
    <nav>
        <ul>
            <li><a href="create_class.php"><i class="fas fa-user-plus"></i> Invite</a></li>
            <li><a href="add_bill.php"><i class="fas fa-plus-circle"></i> Add Bill</a></li>
            <li><a href="view_bills.php"><i class="fas fa-list-alt"></i> View Bills</a></li>
            <li><a href="create_groupchat.php" class="active"><i class="fas fa-users"></i> Create group chat</a></li>
            <li><a href="landlord_group_chats.php">💬 View My Group Chats</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</header>

<div class="content-wrapper">
    <div class="container">
        <h1>Create Group Chat</h1>

        <form method="POST" action="">
            <input type="text" name="group_name" placeholder="Enter Group Chat Name" required autocomplete="off" />

            <label for="class_ids">Select Classes to Include</label>
            <div class="checkboxes" id="class_ids" role="group" aria-label="Select classes">
                <?php while ($row = mysqli_fetch_assoc($classes_query)): ?>
                    <label>
                        <input type="checkbox" name="class_ids[]" value="<?php echo htmlspecialchars($row['id']); ?>" />
                        <?php echo htmlspecialchars($row['class_name']); ?>
                    </label>
                <?php endwhile; ?>
            </div>

            <button type="submit">Create Group</button>
        </form>
    </div>
</div>

<footer>
    <div class="footer-content">
        <p>© 2025 RentTracker. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
  const socket = io('https://rent-tracker-backend.onrender.com'); // Or use your server IP if hosting externally
</script>

</body>
</html>

<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$landlord_name = $_SESSION['landlord_name'];

// Suggested bill names
$suggested_bills = ['Rent', 'Water', 'Electricity', 'Gas', 'Internet', 'Maintenance'];

// Get all classes created by the landlord
$classes_result = mysqli_query($conn, "SELECT * FROM classes WHERE landlord_id = '$landlord_id'");

// Handle form submission
if (isset($_POST['submit'])) {
    $bill_name = ($_POST['bill_name'] === 'other') ? mysqli_real_escape_string($conn, $_POST['other_bill_name']) : mysqli_real_escape_string($conn, $_POST['bill_name']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);

    // Insert bill
    $insert_bill = mysqli_query($conn, "INSERT INTO bills (bill_name, amount, due_date, landlord_id, class_id)
                                        VALUES ('$bill_name', '$amount', '$due_date', '$landlord_id', '$class_id')");

    if ($insert_bill) {
        echo "<script>alert('Bill added successfully!'); window.location.href = 'add_bill.php';</script>";
    } else {
        echo "<script>alert('Error adding bill: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Bill - Rent & Utility Tracker</title>
    <link rel="stylesheet" href="add_bill.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
    </div>
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

<div class="add-bill-container">
    <div class="content-wrapper">
        <h2><i class="fas fa-file-invoice-dollar"></i> Add New Bill, Landlord <?php echo $landlord_name; ?> 👋</h2>
        <p class="explanatory-text">Fill in the bill details and assign it to one of your group.</p>

        <form method="POST" class="add-bill-form">
            <div class="form-group">
                <label for="class_id"><i class="fas fa-users"></i> Select Group (Tenant)</label>
                <select name="class_id" id="class_id" required>
                    <option value="">-- Choose Group (Tenant) --</option>
                    <?php while ($class = mysqli_fetch_assoc($classes_result)) : ?>
                        <option value="<?= $class['id'] ?>"><?= $class['class_name'] ?> (<?= $class['class_code'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="bill_name"><i class="fas fa-file-signature"></i> Bill Name</label>
                <select name="bill_name" id="bill_name" required>
                    <option value="">Select Bill Type</option>
                    <?php foreach ($suggested_bills as $bill) : ?>
                        <option value="<?= $bill ?>"><?= $bill ?></option>
                    <?php endforeach; ?>
                    <option value="other">Other (Specify Below)</option>
                </select>
            </div>

            <div class="form-group" id="other_bill_name_group" style="display: none;">
                <label for="other_bill_name"><i class="fas fa-pen"></i> Specify Bill Name</label>
                <input type="text" name="other_bill_name" id="other_bill_name" placeholder="Enter custom bill name">
            </div>

            <div class="form-group">
                <label for="amount"><i class="fas fa-coins"></i> Amount (₦)</label>
                <input type="number" name="amount" id="amount" placeholder="Enter amount" required>
            </div>

            <div class="form-group">
                <label for="due_date"><i class="fas fa-calendar-alt"></i> Due Date</label>
                <input type="date" name="due_date" id="due_date" required>
            </div>

            <button type="submit" name="submit" class="submit-btn"><i class="fas fa-plus"></i> Add Bill</button>
        </form>
    </div>
</div>

<footer>
    <div class="footer-content">
        <p>&copy; <?php echo date("Y"); ?> Rent & Utility Tracker. All rights reserved.</p>
        <p>Developed by Your Rentals Team</p>
    </div>
</footer>

<script>
    // Toggle visibility of 'Other' input
    document.getElementById('bill_name').addEventListener('change', function () {
        document.getElementById('other_bill_name_group').style.display = (this.value === 'other') ? 'block' : 'none';
    });
</script>

</body>
</html>

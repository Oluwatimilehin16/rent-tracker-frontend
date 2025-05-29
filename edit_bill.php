<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$bill_id = (int)$_GET['id'];
$success_message = $error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_name = mysqli_real_escape_string($conn, $_POST['bill_name']);
    $amount = (float)$_POST['amount'];
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $user_id = (int)$_POST['users_id'];

    $status = isset($_POST['status']) ? 1 : 0;
    $payment_date = null;

    if ($status === 1 && !empty($_POST['payment_date'])) {
        $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date']);
    } elseif ($status === 1) {
        $payment_date = date('Y-m-d');
    }

    $payment_date_sql = $payment_date ? "'$payment_date'" : "NULL";

    $update_query = "
        UPDATE bills SET 
            bill_name = '$bill_name',
            amount = $amount,
            due_date = '$due_date',
            users_id = $user_id,
            status = $status
        WHERE id = $bill_id AND landlord_id = '$landlord_id'
    ";

    if (mysqli_query($conn, $update_query)) {
        $success_message = "✅ Bill updated successfully!";
    } else {
        $error_message = "❌ Failed to update bill: " . mysqli_error($conn);
    }
}

// Fetch bill data to prefill the form
$bill_query = "
    SELECT b.*, u.firstname AS tenant_firstname, u.id AS users_id
    FROM bills b
    JOIN user_classes uc ON b.class_id = uc.class_id
    JOIN users u ON uc.user_id = u.id
    WHERE b.id = ?
";

$stmt = $conn->prepare($bill_query);
$stmt->bind_param("i", $bill_id); // "i" means integer
$stmt->execute();
$bill_result = $stmt->get_result();
$bill = $bill_result->fetch_assoc();

// Fetch tenants under the landlord
$tenants_result = mysqli_query($conn, "
    SELECT u.id, u.firstname 
    FROM users u 
    INNER JOIN user_classes uc ON u.id = uc.user_id 
    INNER JOIN classes c ON uc.class_id = c.id 
    WHERE c.landlord_id = '$landlord_id'
    GROUP BY u.id
");


// Fetch landlord's classes
$classes_result = mysqli_query($conn, "SELECT id, class_name FROM classes WHERE landlord_id = '$landlord_id'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            background-color: #f8f9fa;
            color: #333;
            overflow-x: hidden;
        }

        /* Header */
        header {
            background-color: #218838;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        header .logo img {
            height: 40px;
        }

        .hamburger {
            display: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
            background: none;
            border: none;
        }

        nav ul {
            display: flex;
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin: 0 10px;
        }

        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: block;
        }

        nav ul li a:hover,
        nav ul li a.active {
            color: #ecc700;
            background-color: rgba(255,255,255,0.1);
        }

        .content-wrapper {
            margin-top: 70px;
            padding: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .title {
            color: #186c2c;
            text-align: center;
            margin-bottom: 5px;
            font-size: 24px;
            font-variant: small-caps;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #218838;
            display: block;
        }

        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container input[type="date"],
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            margin-bottom: 20px;
            transition: border 0.3s ease;
        }

        .form-container input:focus,
        .form-container select:focus,
        .form-container textarea:focus {
            border-color: #218838;
            outline: none;
        }

        .form-container textarea {
            resize: vertical;
            height: 100px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-container input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 10px;
        }

        .form-container .btn-primary {
            background-color: #218838;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            width: 100%;
        }

        .form-container .btn-primary:hover {
            background-color: #186c2c;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .header-content {
                flex-wrap: wrap;
            }

            nav {
                width: 100%;
                order: 3;
            }

            nav ul {
                display: none;
                flex-direction: column;
                width: 100%;
                background-color: #218838;
                margin-top: 15px;
                border-radius: 6px;
            }

            nav.active ul {
                display: flex;
            }

            nav ul li {
                margin: 0;
                width: 100%;
            }

            nav ul li a {
                padding: 12px 20px;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }

            nav ul li:last-child a {
                border-bottom: none;
            }

            .content-wrapper {
                margin-top: 70px;
                padding: 15px;
            }

            .title {
                font-size: 20px;
                margin-bottom: 20px;
            }

            .form-container {
                padding: 20px;
                margin: 0 10px;
            }

            .form-container input,
            .form-container select,
            .form-container textarea {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }

        @media (max-width: 480px) {
            header {
                padding: 10px 15px;
            }

            .content-wrapper {
                padding: 10px;
            }

            .form-container {
                padding: 15px;
                margin: 0 5px;
            }

            .title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
            </div>
            <button class="hamburger" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <nav id="main-nav">
                <ul>
                    <li><a href="create_class.php"><i class="fas fa-user-plus"></i> Invite</a></li>
                    <li><a href="add_bill.php"><i class="fas fa-plus-circle"></i> Add Bill</a></li>
                    <li><a href="view_bills.php" class="active"><i class="fas fa-list-alt"></i> View Bills</a></li>
                    <li><a href="create_groupchat.php"><i class="fas fa-users"></i> Create group chat</a></li>
                    <li><a href="landlord_group_chats.php">💬 View My Group Chats</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="content-wrapper">
        <h1 class="title">Edit Bill for: <?= htmlspecialchars($bill['tenant_firstname'] ?? 'Tenant') ?></h1>
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <label for="bill_name">Bill Name</label>
                <input type="text" id="bill_name" name="bill_name" value="<?php echo htmlspecialchars($bill['bill_name']); ?>" required>

                <label for="amount">Amount (₦)</label>
                <input type="number" id="amount" step="0.01" name="amount" value="<?php echo $bill['amount']; ?>" required>

                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" value="<?php echo $bill['due_date']; ?>" required>

               <label for="user_id">Assign to Tenant</label>
                <select id="user_id" name="users_id" required>
                 <option value="">Select a tenant</option>
                    <?php while ($tenant = mysqli_fetch_assoc($tenants_result)): ?>
                 <option value="<?= $tenant['id'] ?>" <?= $tenant['id'] == $bill['users_id'] ? 'selected' : '' ?>>
                 <?= htmlspecialchars($tenant['firstname']) ?>
                </option>
            <?php endwhile; ?>
            </select>


                <div class="checkbox-wrapper">
                    <input type="checkbox" id="status" name="status" value="1" <?php echo $bill['status'] ? 'checked' : ''; ?>>
                    <label for="status">Mark as Paid</label>
                </div>

                <button type="submit" class="btn-primary">Update Bill</button>
            </form>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const nav = document.getElementById('main-nav');
            nav.classList.toggle('active');
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const nav = document.getElementById('main-nav');
            const hamburger = document.querySelector('.hamburger');
            
            if (!nav.contains(event.target) && !hamburger.contains(event.target)) {
                nav.classList.remove('active');
            }
        });

        // Close menu when window is resized to desktop size
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('main-nav').classList.remove('active');
            }
        });
    </script>
</body>
</html>
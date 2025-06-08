<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$bill_id = (int)$_GET['id'];

// Call API instead of direct database query
$api_url = 'https://rent-tracker-api.onrender.com/view_bill.php?landlord_id=' . $landlord_id . '&bill_id=' . $bill_id;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (!$result['success']) {
    header("Location: view_bills.php");
    exit();
}

$bill = $result['bill'];
$days_diff = $result['due_status']['days_diff'];
$is_overdue = $result['due_status']['is_overdue'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Details - <?php echo htmlspecialchars($bill['bill_name']); ?></title>
    <link rel="stylesheet" href="view_bills.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .bill-details {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .bill-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .bill-amount {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.1em;
            color: #2c3e50;
        }
        
        .status-large {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-unpaid {
            background: #f8d7da;
            color: #721c24;
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .action-buttons a {
            display: inline-block;
            margin: 0 10px;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
        }
        
        .btn-edit:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="page-container">
    <header>
        <div class="logo">
            <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
        </div>
        <div class="hamburger" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>
        <nav id="main-nav">
            <ul>
                <li><a href="create_class.php"><i class="fas fa-user-plus"></i> Invite</a></li>
                <li><a href="add_bill.php"><i class="fas fa-plus-circle"></i> Add Bill</a></li>
                <li><a href="view_bills.php"><i class="fas fa-list-alt"></i> View Bills</a></li>
                <li><a href="create_groupchat.php"><i class="fas fa-users"></i> Create group chat</a></li>
                <li><a href="landlord_group_chats.php">💬 View My Group Chats</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="content-wrapper">
        <a href="view_bills.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Bills
        </a>
        
        <div class="bill-details">
            <div class="bill-header">
                <h1><?php echo htmlspecialchars($bill['bill_name']); ?></h1>
                <div class="bill-amount">₦<?php echo number_format($bill['amount'], 2); ?></div>
                <span class="status-large <?php echo $bill['status'] ? 'status-paid' : 'status-unpaid'; ?>">
                    <?php echo $bill['status'] ? 'PAID' : 'NOT PAID'; ?>
                </span>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Group/Class</div>
                    <div class="info-value"><?php echo htmlspecialchars($bill['class_name'] ?? 'N/A'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Tenant</div>
                    <div class="info-value"><?php echo htmlspecialchars($bill['tenant_name'] ?? 'No tenant assigned'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Due Date</div>
                    <div class="info-value">
                        <?php echo date('F j, Y', strtotime($bill['due_date'])); ?>
                        <?php if (!$bill['status']): ?>
                            <br>
                            <small style="color: <?php echo $days_diff < 0 ? '#dc3545' : ($days_diff <= 7 ? '#ffc107' : '#6c757d'); ?>">
                                <?php 
                                if ($days_diff < 0) {
                                    echo "Overdue by " . abs($days_diff) . " days";
                                } elseif ($days_diff == 0) {
                                    echo "Due today";
                                } elseif ($days_diff <= 7) {
                                    echo "Due in $days_diff days";
                                } else {
                                    echo "Due in $days_diff days";
                                }
                                ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($bill['status'] && $bill['payment_date']): ?>
                <div class="info-item">
                    <div class="info-label">Payment Date</div>
                    <div class="info-value"><?php echo date('F j, Y', strtotime($bill['payment_date'])); ?></div>
                </div>
                <?php endif; ?>
                
                
                <?php if ($bill['tenant_email']): ?>
                <div class="info-item">
                    <div class="info-label">Tenant Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($bill['tenant_email']); ?></div>
                </div>
                <?php endif; ?>
            
            <div class="action-buttons">
                <a href="edit_bill.php?id=<?php echo $bill['id']; ?>" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit Bill
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleMenu() {
        document.getElementById('main-nav').classList.toggle('active');
    }
</script>
</body>
</html>
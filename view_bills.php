<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$landlord_name = $_SESSION['landlord_name'];
$today = date('Y-m-d');

// Handle delete action
if (isset($_POST['delete_bill'])) {
    $bill_id = (int)$_POST['bill_id'];
    $delete_query = "DELETE FROM bills WHERE id = $bill_id AND landlord_id = '$landlord_id'";
    if (mysqli_query($conn, $delete_query)) {
        $success_message = "Bill deleted successfully!";
    } else {
        $error_message = "Error deleting bill: " . mysqli_error($conn);
    }
}

// Handle remind action
if (isset($_POST['send_reminder'])) {
    $bill_id = (int)$_POST['bill_id'];
    // Add your reminder logic here (email, SMS, notification, etc.)
    $success_message = "Reminder sent successfully!";
}

// Fetch bills data - Fixed query to properly get tenant information
$query = "
    SELECT DISTINCT b.id, b.bill_name, b.amount, b.due_date, b.status,
           c.class_name, 
           CONCAT(u.firstname, ' ', u.lastname) AS tenant_name
    FROM bills b
    LEFT JOIN classes c ON c.id = b.class_id
    LEFT JOIN user_classes uc ON uc.class_id = c.id
    LEFT JOIN users u ON u.id = uc.user_id AND u.users_role = 'tenant'
    WHERE b.landlord_id = '$landlord_id'
    ORDER BY b.due_date ASC
";
$result = mysqli_query($conn, $query);

// Statistics
$total_bills = 0;
$paid_bills = 0;
$overdue_bills = 0;
$upcoming_bills = 0;
$total_amount = 0;
$paid_amount = 0;
$bills = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $bills[] = $row;
        $total_bills++;
        
        // Fixed status checking - assuming status = 1 means paid, status = 0 means unpaid
        if ($row['status'] == 1) {
            $paid_bills++;
            $paid_amount += $row['amount'];
        }
        $total_amount += $row['amount'];
        
        // Calculate days difference correctly
        $due_date = new DateTime($row['due_date']);
        $today_date = new DateTime($today);
        $diff = $today_date->diff($due_date);
        $days_diff = $diff->invert ? -$diff->days : $diff->days;

        // Count overdue and upcoming bills only for unpaid bills
        if ($row['status'] == 0) {
            if ($days_diff < 0) {
                $overdue_bills++;
            } elseif ($days_diff >= 0 && $days_diff <= 7) {
                $upcoming_bills++;
            }
        }
    }
}

$filter = $_GET['filter'] ?? 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bills - Rent & Utility Tracker</title>
    <link rel="stylesheet" href="view_bills.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: none;
            border-radius: 8px;
            width: 300px;
            text-align: center;
        }
        
        .modal-buttons {
            margin-top: 20px;
        }
        
        .modal-buttons button {
            margin: 0 10px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
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
                <li><a href="view_bills.php" class="active"><i class="fas fa-list-alt"></i> View Bills</a></li>
                <li><a href="create_groupchat.php"><i class="fas fa-users"></i> Create group chat</a></li>
                <li><a href="landlord_group_chats.php">💬 View My Group Chats</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="content-wrapper">
        <h1 class="title">Bills Dashboard for <?php echo htmlspecialchars($landlord_name); ?> 👋</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="card card-total">
                <div class="card-icon"><i class="fas fa-file-invoice"></i></div>
                <div class="card-value"><?php echo $total_bills; ?></div>
                <div class="card-label">Total Bills</div>
                <div class="card-money">₦<?php echo number_format($total_amount, 2); ?></div>
            </div>
            <div class="card card-paid">
                <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                <div class="card-value"><?php echo $paid_bills; ?></div>
                <div class="card-label">Paid Bills</div>
                <div class="card-money">₦<?php echo number_format($paid_amount, 2); ?></div>
            </div>
            <div class="card card-overdue">
                <div class="card-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="card-value"><?php echo $overdue_bills; ?></div>
                <div class="card-label">Overdue</div>
            </div>
            <div class="card card-upcoming">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <div class="card-value"><?php echo $upcoming_bills; ?></div>
                <div class="card-label">Due Soon</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="controls">
            <div class="filter-controls">
                <?php
                $filters = [
                    'all' => ['All', $total_bills],
                    'paid' => ['Paid', $paid_bills],
                    'unpaid' => ['Unpaid', $total_bills - $paid_bills],
                    'overdue' => ['Overdue', $overdue_bills],
                    'upcoming' => ['Upcoming', $upcoming_bills]
                ];

                foreach ($filters as $key => [$label, $count]) {
                    $active = $filter === $key ? 'active' : '';
                    echo "<a href='?filter=$key' class='filter-btn $active'>
                        <i class='fas fa-filter'></i> $label 
                        <span class='filter-badge'>$count</span>
                    </a>";
                }
                ?>
            </div>
        </div>

        <!-- Bills Table -->
        <div class="bills-table">
            <table>
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Bill</th>
                        <th>Tenant</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bills as $bill):
                        $due_date = new DateTime($bill['due_date']);
                        $today_date = new DateTime($today);
                        $diff = $today_date->diff($due_date);
                        $days_diff = $diff->invert ? -$diff->days : $diff->days;

                        // Fixed filter logic
                        $show_bill = true;
                        if ($filter === 'paid' && $bill['status'] != 1) $show_bill = false;
                        if ($filter === 'unpaid' && $bill['status'] == 1) $show_bill = false;
                        if ($filter === 'overdue' && ($bill['status'] == 1 || $days_diff >= 0)) $show_bill = false;
                        if ($filter === 'upcoming' && ($bill['status'] == 1 || $days_diff < 0 || $days_diff > 7)) $show_bill = false;
                        
                        if (!$show_bill) continue;

                        // Fixed due text logic
                        if ($bill['status'] == 1) {
                            $payment_date = $bill['payment_date'] ? $bill['payment_date'] : $today;
                            $due_text = 'Paid on ' . date('M j, Y', strtotime($payment_date));
                            $due_class = 'due-paid';
                        } else {
                            if ($days_diff < 0) {
                                $due_text = "Overdue by " . abs($days_diff) . " days";
                                $due_class = 'due-overdue';
                            } elseif ($days_diff <= 7) {
                                $due_text = $days_diff == 0 ? "Due today" : "Due in $days_diff days";
                                $due_class = 'due-soon';
                            } else {
                                $due_text = date('F j, Y', strtotime($bill['due_date']));
                                $due_class = 'due-normal';
                            }
                        }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($bill['class_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($bill['bill_name']); ?></td>
                            <td><?php echo htmlspecialchars($bill['tenant_name'] ?? 'No tenant assigned'); ?></td>
                            <td>₦<?php echo number_format($bill['amount'], 2); ?></td>
                            <td><span class="due-date <?php echo $due_class; ?>"><?php echo $due_text; ?></span></td>
                            <td>
                                <span class="status-badge <?php echo $bill['status'] ? 'status-paid' : 'status-not-paid'; ?>">
                                    <?php echo $bill['status'] ? 'Paid' : 'Not Paid'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_bill.php?id=<?php echo $bill['id']; ?>" title="View" class="action-btn">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_bill.php?id=<?php echo $bill['id']; ?>" title="Edit" class="action-btn">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($bill['status'] == 0): ?>
                                    <button onclick="showRemindModal(<?php echo $bill['id']; ?>)" title="Remind" class="action-btn btn-remind">
                                        <i class="fas fa-bell"></i>
                                    </button>
                                <?php endif; ?>
                                <button onclick="showDeleteModal(<?php echo $bill['id']; ?>)" title="Delete" class="action-btn btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete this bill? This action cannot be undone.</p>
        <div class="modal-buttons">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="bill_id" id="deleteBillId">
                <button type="submit" name="delete_bill" class="btn-danger">Delete</button>
                <button type="button" onclick="closeModal('deleteModal')" class="btn-secondary">Cancel</button>
            </form>
        </div>
    </div>
</div>

<!-- Remind Modal -->
<div id="remindModal" class="modal">
    <div class="modal-content">
        <h3>Send Reminder</h3>
        <p>Send a payment reminder to the tenant for this bill?</p>
        <div class="modal-buttons">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="bill_id" id="remindBillId">
                <button type="submit" name="send_reminder" class="btn-primary">Send Reminder</button>
                <button type="button" onclick="closeModal('remindModal')" class="btn-secondary">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleMenu() {
        document.getElementById('main-nav').classList.toggle('active');
    }
    
    function showDeleteModal(billId) {
        document.getElementById('deleteBillId').value = billId;
        document.getElementById('deleteModal').style.display = 'block';
    }
    
    function showRemindModal(billId) {
        document.getElementById('remindBillId').value = billId;
        document.getElementById('remindModal').style.display = 'block';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const deleteModal = document.getElementById('deleteModal');
        const remindModal = document.getElementById('remindModal');
        
        if (event.target == deleteModal) {
            deleteModal.style.display = 'none';
        }
        if (event.target == remindModal) {
            remindModal.style.display = 'none';
        }
    }
</script>
</body>
</html>
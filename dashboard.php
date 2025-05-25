<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['tenant_id']) || $_SESSION['users_role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$tenant_id    = $_SESSION['tenant_id'];
$tenant_email = $_SESSION['tenant_email']; // still available if needed
$firstname    = $_SESSION['users_name'];

// Check if tenant has joined any class
$class_check = mysqli_query($conn, "SELECT * FROM class_members WHERE tenant_id = '$tenant_id'");
if (mysqli_num_rows($class_check) == 0) {
    header("Location: join_class.php");
    exit();
}
?>
<?php
$total_bills_result = mysqli_query($conn, "
    SELECT SUM(amount) as total FROM bills 
    WHERE class_id IN (SELECT class_id FROM class_members WHERE tenant_id = '$tenant_id')
");

$total_paid_result = mysqli_query($conn, "
    SELECT SUM(b.amount) as paid FROM payments p 
    JOIN bills b ON p.bill_id = b.id 
    WHERE p.tenant_id = '$tenant_id'
");

$total_bills = mysqli_fetch_assoc($total_bills_result)['total'] ?? 0;
$total_paid  = mysqli_fetch_assoc($total_paid_result)['paid'] ?? 0;
$balance     = $total_bills - $total_paid;

// Get counts for dashboard stats
$overdue_count = 0;
$due_soon_count = 0;
$total_unpaid_count = 0;

// Pre-calculate counts for summary cards
$class_result_count = mysqli_query($conn, "
    SELECT c.id AS class_id FROM class_members cm
    JOIN classes c ON cm.class_id = c.id
    WHERE cm.tenant_id = '$tenant_id'
");

while ($class = mysqli_fetch_assoc($class_result_count)) {
    $class_id = $class['class_id'];
    $bills = mysqli_query($conn, "SELECT id, due_date FROM bills WHERE class_id = '$class_id'");
    
    while ($bill = mysqli_fetch_assoc($bills)) {
        $bill_id = $bill['id'];
        $due_date = $bill['due_date'];
        
        $payment_check = mysqli_query($conn, "
            SELECT * FROM payments 
            WHERE bill_id = '$bill_id' AND tenant_id = '$tenant_id'
        ");
        $paid = mysqli_num_rows($payment_check) > 0;
        
        if (!$paid) {
            $total_unpaid_count++;
            if (strtotime($due_date) < time()) {
                $overdue_count++;
            } elseif ((strtotime($due_date) - time()) < (7 * 24 * 60 * 60)) {
                $due_soon_count++;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard - RentTracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css" />
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="./assets/logo.png" alt="RentTracker" style="height: 40px;"></a>
    </div>
    <nav>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="join_class.php"><i class="fas fa-user-plus"></i> Join Group</a></li>
            <li><a href="tenant_group_chat.php"><i class="fas fa-envelope"></i> Messages</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</header>

<div class="dashboard">
    <div class="welcome-section">
        <div class="welcome">
            <i class="fas fa-user-circle"></i>
            Welcome back, <?php echo htmlspecialchars($firstname); ?>!
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Bills</h3>
                <div class="amount">₦<?php echo number_format($total_bills, 2); ?></div>
                <div class="description">All assigned bills</div>
            </div>
            
            <div class="stat-card">
                <h3>Paid Amount</h3>
                <div class="amount">₦<?php echo number_format($total_paid, 2); ?></div>
                <div class="description">Successfully paid</div>
            </div>
            
            <div class="stat-card balance">
                <h3>Outstanding Balance</h3>
                <div class="amount">₦<?php echo number_format($balance, 2); ?></div>
                <div class="description">Amount pending</div>
            </div>
            
            <div class="stat-card overdue">
                <h3>Overdue Bills</h3>
                <div class="amount"><?php echo $overdue_count; ?></div>
                <div class="description">Require immediate attention</div>
            </div>
            
            <div class="stat-card due-soon">
                <h3>Due Soon</h3>
                <div class="amount"><?php echo $due_soon_count; ?></div>
                <div class="description">Due within 7 days</div>
            </div>
            
            <div class="stat-card">
                <h3>Total Unpaid</h3>
                <div class="amount"><?php echo $total_unpaid_count; ?></div>
                <div class="description">Pending payments</div>
            </div>
        </div>
    </div>

    <div class="bills-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-file-invoice-dollar"></i>
                Your Bills & Payments
            </h3>
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterBills('all')">All Bills</button>
                <button class="filter-btn" onclick="filterBills('unpaid')">Unpaid</button>
                <button class="filter-btn" onclick="filterBills('overdue')">Overdue</button>
                <button class="filter-btn" onclick="filterBills('due-soon')">Due Soon</button>
            </div>
        </div>

        <table class="bills-table" id="billsTable">
            <thead>
                <tr>
                    <th><i class="fas fa-bolt"></i> Utility</th>
                    <th><i class="fas fa-naira-sign"></i> Amount</th>
                    <th><i class="fas fa-calendar"></i> Due Date</th>
                    <th><i class="fas fa-check-circle"></i> Status</th>
                    <th><i class="fas fa-user-tie"></i> Landlord</th>
                    <th><i class="fas fa-sticky-note"></i> Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get all classes tenant is part of
                $class_result = mysqli_query($conn, "
                    SELECT 
                        c.id AS class_id, 
                        u.firstname AS landlord_fname, 
                        u.lastname AS landlord_lname 
                    FROM class_members cm
                    JOIN classes c ON cm.class_id = c.id
                    JOIN users u ON c.landlord_id = u.id
                    WHERE cm.tenant_id = '$tenant_id'
                ");

                $has_bills = false;
                while ($class = mysqli_fetch_assoc($class_result)) {
                    $class_id      = $class['class_id'];
                    $landlord_name = $class['landlord_fname'] . " " . $class['landlord_lname'];
                    $landlord_initials = strtoupper(substr($class['landlord_fname'], 0, 1) . substr($class['landlord_lname'], 0, 1));

                    // Get unique bills assigned to this class
                    $bills = mysqli_query($conn, "SELECT DISTINCT id, bill_name, amount, due_date FROM bills WHERE class_id = '$class_id' ORDER BY due_date ASC");
                    while ($bill = mysqli_fetch_assoc($bills)) {
                        $has_bills = true;
                        $bill_id   = $bill['id'];
                        $bill_name = $bill['bill_name'];
                        $amount    = $bill['amount'];
                        $due_date  = $bill['due_date'];
                        $due_soon = (strtotime($due_date) - time()) < (7 * 24 * 60 * 60); // due in < 7 days
                        $is_overdue = strtotime($due_date) < time();

                        // Check if this tenant has paid
                        $payment_check = mysqli_query($conn, "
                            SELECT * FROM payments 
                            WHERE bill_id = '$bill_id' AND tenant_id = '$tenant_id'
                        ");
                        $paid = mysqli_num_rows($payment_check) > 0;

                        $note = "";
                        // Check if co-tenant(s) have paid
                        $co_query = mysqli_query($conn, "
                            SELECT tenant_id FROM class_members 
                            WHERE class_id = '$class_id' AND tenant_id != '$tenant_id'
                        ");

                        while ($co = mysqli_fetch_assoc($co_query)) {
                            $co_id = $co['tenant_id'];
                            $co_paid = mysqli_query($conn, "
                                SELECT * FROM payments 
                                WHERE bill_id = '$bill_id' AND tenant_id = '$co_id'
                            ");
                            if (mysqli_num_rows($co_paid) > 0) {
                                $note = "Co-tenant has paid";
                                break;
                            }
                        }

                        // Determine utility type for icon
                        $utility_class = "utility-other";
                        $utility_icon = "fas fa-file-invoice";
                        $bill_lower = strtolower($bill_name);
                        
                        if (strpos($bill_lower, 'electric') !== false || strpos($bill_lower, 'power') !== false) {
                            $utility_class = "utility-electricity";
                            $utility_icon = "fas fa-bolt";
                        } elseif (strpos($bill_lower, 'water') !== false) {
                            $utility_class = "utility-water";
                            $utility_icon = "fas fa-tint";
                        } elseif (strpos($bill_lower, 'gas') !== false) {
                            $utility_class = "utility-gas";
                            $utility_icon = "fas fa-fire";
                        } elseif (strpos($bill_lower, 'internet') !== false || strpos($bill_lower, 'wifi') !== false) {
                            $utility_class = "utility-internet";
                            $utility_icon = "fas fa-wifi";
                        } elseif (strpos($bill_lower, 'rent') !== false) {
                            $utility_class = "utility-rent";
                            $utility_icon = "fas fa-home";
                        }

                        $row_class = "";
                        $filter_class = "";
                        if (!$paid) {
                            $filter_class = "unpaid";
                            if ($is_overdue) {
                                $row_class = "overdue";
                                $filter_class .= " overdue";
                            } elseif ($due_soon) {
                                $row_class = "due-soon";
                                $filter_class .= " due-soon";
                            }
                        } else {
                            $filter_class = "paid";
                        }

                        echo "<tr class='$row_class' data-filter='$filter_class'>
                                <td>
                                    <div style='display: flex; align-items: center;'>
                                        <span class='utility-icon $utility_class'>
                                            <i class='$utility_icon'></i>
                                        </span>
                                        <strong>$bill_name</strong>
                                    </div>
                                </td>
                                <td><strong>₦" . number_format($amount, 2) . "</strong></td>
                                <td class='due-date'>" . date('M d, Y', strtotime($due_date)) . "</td>
                                <td>";
                        
                        if ($paid) {
                            echo "<span class='status-badge status-paid'>Paid</span>";
                        } else {
                            echo "<a href='make_payment.php?bill_id=$bill_id' class='pay-btn'>Pay Now</a>";
                        }
                        
                        echo "</td>
                                <td>
                                    <div class='landlord-info'>
                                        <div class='landlord-avatar'>$landlord_initials</div>
                                        <span>$landlord_name</span>
                                    </div>
                                </td>
                                <td>";
                        
                        if ($note) {
                            echo "<span class='note-tag'>$note</span>";
                        } else {
                            echo "-";
                        }
                        
                        echo "</td>
                            </tr>";
                    }
                }

                if (!$has_bills) {
                    echo "<tr><td colspan='6' class='no-bills'>
                            <i class='fas fa-file-invoice'></i>
                            <h3>No bills found</h3>
                            <p>You don't have any bills assigned yet.</p>
                          </td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterBills(filter) {
    const table = document.getElementById('billsTable');
    const rows = table.querySelectorAll('tbody tr');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    rows.forEach(row => {
        const filterData = row.getAttribute('data-filter');
        
        if (filter === 'all') {
            row.style.display = '';
        } else if (filter === 'unpaid' && filterData && filterData.includes('unpaid')) {
            row.style.display = '';
        } else if (filter === 'overdue' && filterData && filterData.includes('overdue')) {
            row.style.display = '';
        } else if (filter === 'due-soon' && filterData && filterData.includes('due-soon')) {
            row.style.display = '';
        } else if (!filterData) {
            // This is the "no bills" row
            row.style.display = filter === 'all' ? '' : 'none';
        } else {
            row.style.display = 'none';
        }
    });
}

// Add some interactive feedback
document.addEventListener('DOMContentLoaded', function() {
    const payButtons = document.querySelectorAll('.pay-btn');
    payButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.innerHTML = '<i class="fas fa-credit-card"></i> Pay Now';
        });
        btn.addEventListener('mouseleave', function() {
            this.innerHTML = 'Pay Now';
        });
    });
});
</script>

</body>
</html>
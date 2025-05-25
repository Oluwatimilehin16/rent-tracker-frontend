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

// Fetch bills data
$query = "
    SELECT b.id, b.bill_name, b.amount, b.due_date, b.status,
           c.class_name, 
           CONCAT(u.firstname, ' ', u.lastname) AS tenant_name
    FROM bills b
    LEFT JOIN classes c ON c.id = b.class_id
    LEFT JOIN user_classes uc ON uc.class_id = b.class_id
    LEFT JOIN users u ON u.id = uc.user_id AND u.users_role = 'tenant'
    WHERE b.landlord_id = '$landlord_id'
    ORDER BY b.due_date ASC
";
$result = mysqli_query($conn, $query);

// Statistics
$total_bills = mysqli_num_rows($result);
$paid_bills = 0;
$overdue_bills = 0;
$upcoming_bills = 0;
$total_amount = 0;
$paid_amount = 0;
$bills = [];
$today = date('Y-m-d'); // Make sure this is set


if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $bills[] = $row;
        if ($row['status'] == 1) {
            $paid_bills++;
            $paid_amount += $row['amount'];
        }
        $total_amount += $row['amount'];
        $days_diff = (new DateTime($today))->diff(new DateTime($row['due_date']))->format("%r%a");

        if ($days_diff < 0 && $row['status'] == 0) $overdue_bills++;
        elseif ($days_diff >= 0 && $days_diff <= 7 && $row['status'] == 0) $upcoming_bills++;


    }
}

$filter = $_GET['filter'] ?? 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Bills - Rent & Utility Tracker</title>
    <link rel="stylesheet" href="view_bills.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="page-container">
    <header>
        <div class="logo">
            <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
        </div>
        <nav>
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
                        $due = new DateTime($bill['due_date']);
                        $diff = (new DateTime($today))->diff($due)->format("%r%a");

                        // Filter logic
                       if (
                            ($filter === 'paid' && $bill['status'] != 1) ||
                            ($filter === 'unpaid' && $bill['status'] == 1) ||
                            ($filter === 'overdue' && ($bill['status'] == 1 || $due >= new DateTime($today))) ||
                            ($filter === 'upcoming' && ($bill['status'] == 1 || $due < new DateTime($today) || $diff > 7))
                        ) continue;


                        $due_text = $bill['status'] == 1
                            ? 'Paid on ' . date('M j, Y', strtotime($bill['payment_date'] ?? $today))
                            : ($diff < 0 ? "Overdue by " . abs($diff) . " days" :
                               ($diff <= 7 ? "Due in $diff days" :
                               date('F j, Y', strtotime($bill['due_date']))));

                        $due_class = $bill['status'] == 1 ? 'due-paid' :
                                     ($diff < 0 ? 'due-overdue' :
                                     ($diff <= 7 ? 'due-soon' : 'due-normal'));

                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($bill['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($bill['bill_name']); ?></td>
                            <td><?php echo htmlspecialchars($bill['tenant_name']); ?></td>
                            <td>₦<?php echo number_format($bill['amount'], 2); ?></td>
                            <td><span class="due-date <?php echo $due_class; ?>"><?php echo $due_text; ?></span></td>
                            <td>
                                <span class="status-badge <?php echo $bill['status'] ? 'status-paid' : 'status-not-paid'; ?>">
                                    <?php echo $bill['status'] ? 'Paid' : 'Not Paid'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_bill.php?id=<?php echo $bill['id']; ?>" title="View"><i class="fas fa-eye"></i></a>
                                <a href="edit_bill.php?id=<?php echo $bill['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php if ($bill['status'] == 0): ?>
                                    <a href="#" onclick="showRemindModal(<?php echo $bill['id']; ?>)" title="Remind"><i class="fas fa-bell"></i></a>
                                <?php endif; ?>
                                <a href="#" onclick="showDeleteModal(<?php echo $bill['id']; ?>)" title="Delete"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>

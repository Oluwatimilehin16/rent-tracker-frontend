<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['tenant_id']) || $_SESSION['users_role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];
$tenant_email = $_SESSION['tenant_email'];
$firstname = $_SESSION['users_name'];

$api_base_url = 'https://rent-tracker-api.onrender.com'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard - RentTracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css" />
    <style>
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.1);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
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
        
        <div class="stats-grid" id="statsGrid">
            <div class="loading">
                <div class="spinner"></div>
                Loading dashboard statistics...
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
            <tbody id="billsTableBody">
                <tr>
                    <td colspan="6" class="loading">
                        <div class="spinner"></div>
                        Loading bills...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const tenantId = '<?php echo $tenant_id; ?>';
const apiBaseUrl = '<?php echo $api_base_url; ?>';

// Load dashboard data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadBills();
});

async function loadDashboardStats() {
    try {
        const response = await fetch(`${apiBaseUrl}/tenant_dashboard_stats.php?tenant_id=${tenantId}`);
        const data = await response.json();
        
        if (data.success) {
            if (!data.has_class) {
                // Redirect to join class page if no class found
                window.location.href = 'join_class.php';
                return;
            }
            
            displayStats(data.data);
        } else {
            displayStatsError(data.error || 'Failed to load statistics');
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
        displayStatsError('Network error occurred');
    }
}

function displayStats(stats) {
    const statsGrid = document.getElementById('statsGrid');
    statsGrid.innerHTML = `
        <div class="stat-card">
            <h3>Total Bills</h3>
            <div class="amount">₦${numberFormat(stats.total_bills)}</div>
            <div class="description">All assigned bills</div>
        </div>
        
        <div class="stat-card">
            <h3>Paid Amount</h3>
            <div class="amount">₦${numberFormat(stats.total_paid)}</div>
            <div class="description">Successfully paid</div>
        </div>
        
        <div class="stat-card balance">
            <h3>Outstanding Balance</h3>
            <div class="amount">₦${numberFormat(stats.balance)}</div>
            <div class="description">Amount pending</div>
        </div>
        
        <div class="stat-card overdue">
            <h3>Overdue Bills</h3>
            <div class="amount">${stats.overdue_count}</div>
            <div class="description">Require immediate attention</div>
        </div>
        
        <div class="stat-card due-soon">
            <h3>Due Soon</h3>
            <div class="amount">${stats.due_soon_count}</div>
            <div class="description">Due within 7 days</div>
        </div>
        
        <div class="stat-card">
            <h3>Total Unpaid</h3>
            <div class="amount">${stats.total_unpaid_count}</div>
            <div class="description">Pending payments</div>
        </div>
    `;
}

function displayStatsError(message) {
    const statsGrid = document.getElementById('statsGrid');
    statsGrid.innerHTML = `
        <div class="error">
            <i class="fas fa-exclamation-triangle"></i>
            Error loading statistics: ${message}
        </div>
    `;
}

async function loadBills() {
    try {
        const response = await fetch(`${apiBaseUrl}/tenant_bills.php?tenant_id=${tenantId}`);
        const data = await response.json();
        
        if (data.success) {
            displayBills(data.data);
        } else {
            displayBillsError(data.error || 'Failed to load bills');
        }
    } catch (error) {
        console.error('Error loading bills:', error);
        displayBillsError('Network error occurred');
    }
}

function displayBills(bills) {
    const billsTableBody = document.getElementById('billsTableBody');
    
    if (bills.length === 0) {
        billsTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="no-bills">
                    <i class="fas fa-file-invoice"></i>
                    <h3>No bills found</h3>
                    <p>You don't have any bills assigned yet.</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let rowsHtml = '';
    bills.forEach(bill => {
        const rowClass = bill.status === 'overdue' ? 'overdue' : (bill.status === 'due_soon' ? 'due-soon' : '');
        const filterClass = getFilterClass(bill);
        
        rowsHtml += `
            <tr class="${rowClass}" data-filter="${filterClass}">
                <td>
                    <div style="display: flex; align-items: center;">
                        <span class="utility-icon utility-${bill.utility_type}">
                            <i class="${bill.utility_icon}"></i>
                        </span>
                        <strong>${bill.bill_name}</strong>
                    </div>
                </td>
                <td><strong>₦${numberFormat(bill.amount)}</strong></td>
                <td class="due-date">${bill.due_date_formatted}</td>
                <td>
                    ${bill.paid 
                        ? '<span class="status-badge status-paid">Paid</span>'
                        : `<a href="make_payment.php?bill_id=${bill.id}" class="pay-btn">Pay Now</a>`
                    }
                </td>
                <td>
                    <div class="landlord-info">
                        <div class="landlord-avatar">${bill.landlord_initials}</div>
                        <span>${bill.landlord_name}</span>
                    </div>
                </td>
                <td>
                    ${bill.note ? `<span class="note-tag">${bill.note}</span>` : '-'}
                </td>
            </tr>
        `;
    });
    
    billsTableBody.innerHTML = rowsHtml;
    
    // Add hover effects to pay buttons
    addPayButtonEffects();
}

function getFilterClass(bill) {
    let filterClass = '';
    if (bill.paid) {
        filterClass = 'paid';
    } else {
        filterClass = 'unpaid';
        if (bill.is_overdue) {
            filterClass += ' overdue';
        } else if (bill.due_soon) {
            filterClass += ' due-soon';
        }
    }
    return filterClass;
}

function displayBillsError(message) {
    const billsTableBody = document.getElementById('billsTableBody');
    billsTableBody.innerHTML = `
        <tr>
            <td colspan="6" class="error">
                <i class="fas fa-exclamation-triangle"></i>
                Error loading bills: ${message}
            </td>
        </tr>
    `;
}

function numberFormat(number) {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
}

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
            // This is the "no bills" or error row
            row.style.display = filter === 'all' ? '' : 'none';
        } else {
            row.style.display = 'none';
        }
    });
}

function addPayButtonEffects() {
    const payButtons = document.querySelectorAll('.pay-btn');
    payButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.innerHTML = '<i class="fas fa-credit-card"></i> Pay Now';
        });
        btn.addEventListener('mouseleave', function() {
            this.innerHTML = 'Pay Now';
        });
    });
}
</script>

</body>
</html>
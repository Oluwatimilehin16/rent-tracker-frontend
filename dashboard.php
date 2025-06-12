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
        /* Mobile Responsive Styles */
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

        /* Mobile Header Styles */
   

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 8px;
            border: none;
            background: none;
            z-index: 1001;
            color: #f8f9fa;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background-color: #333;
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        /* Mobile Navigation */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            nav {
                position: fixed;
                top: 0;
                right: -100%;
                width: 280px;
                height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                transition: right 0.3s ease;
                z-index: 1000;
                padding-top: 80px;
                box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            }

            nav.active {
                right: 0;
            }

            nav ul {
                flex-direction: column;
                padding: 0;
                margin: 0;
            }

            nav ul li {
                margin: 0;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }

            nav ul li:last-child {
                border-bottom: none;
            }

            nav ul li a {
                display: block;
                padding: 20px 30px;
                color: white;
                text-decoration: none;
                font-size: 16px;
                transition: background-color 0.3s;
            }

            nav ul li a:hover {
                background-color: rgba(255,255,255,0.1);
            }

            nav ul li a i {
                margin-right: 15px;
                width: 20px;
                text-align: center;
            }

            /* Mobile overlay */
            .nav-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100vh;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .nav-overlay.active {
                display: block;
            }
        }

        /* Mobile Dashboard Styles */
        @media (max-width: 768px) {
            .dashboard {
                padding: 10px;
            }

            .welcome-section {
                margin-bottom: 20px;
            }

            .welcome {
                font-size: 18px;
                text-align: center;
                padding: 15px;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin-top: 15px;
            }

            .stat-card {
                padding: 15px;
                text-align: center;
                border-radius: 8px;
                background: white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .stat-card h3 {
                font-size: 12px;
                margin: 0 0 8px 0;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .stat-card .amount {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .stat-card .description {
                font-size: 10px;
                color: #888;
            }
        }

        /* Mobile Bills Section */
        @media (max-width: 768px) {
            .section-header {
                text-align: center;
                margin-bottom: 15px;
            }

            .section-title {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .filter-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                justify-content: center;
            }

            .filter-btn {
                padding: 8px 12px;
                font-size: 12px;
                border: 1px solid #ddd;
                background: white;
                border-radius: 20px;
                cursor: pointer;
                transition: all 0.3s;
            }

            .filter-btn.active {
                background: #007bff;
                color: white;
                border-color: #007bff;
            }

            /* Mobile Table - Card View */
            .bills-table {
                display: none;
            }

            .bills-mobile {
                display: block;
            }

            .bill-card {
                background: white;
                border-radius: 10px;
                padding: 15px;
                margin-bottom: 15px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border-left: 4px solid #007bff;
            }

            .bill-card.overdue {
                border-left-color: #dc3545;
            }

            .bill-card.due-soon {
                border-left-color: #ffc107;
            }

            .bill-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .bill-utility {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .utility-icon {
                width: 35px;
                height: 35px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f8f9fa;
                color: #007bff;
            }

            .bill-amount {
                font-size: 18px;
                font-weight: bold;
                color: #333;
            }

            .bill-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin: 10px 0;
            }

            .bill-detail {
                text-align: center;
            }

            .bill-detail-label {
                font-size: 11px;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 3px;
            }

            .bill-detail-value {
                font-size: 13px;
                font-weight: 500;
            }

            .bill-actions {
                margin-top: 15px;
                text-align: center;
            }

            .pay-btn {
                background: linear-gradient(135deg, #007bff, #0056b3);
                color: white;
                padding: 10px 20px;
                border-radius: 25px;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                display: inline-block;
                transition: all 0.3s;
            }

            .pay-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,123,255,0.3);
            }

            .status-badge {
                padding: 5px 12px;
                border-radius: 15px;
                font-size: 12px;
                font-weight: 500;
            }

            .status-paid {
                background: #d4edda;
                color: #155724;
            }

            .landlord-info {
                display: flex;
                align-items: center;
                gap: 8px;
                justify-content: center;
            }

            .landlord-avatar {
                width: 25px;
                height: 25px;
                border-radius: 50%;
                background: #007bff;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 11px;
                font-weight: bold;
            }

            .note-tag {
                background: #f8f9fa;
                padding: 3px 8px;
                border-radius: 10px;
                font-size: 11px;
                color: #666;
            }

            .no-bills {
                text-align: center;
                padding: 40px 20px;
                color: #666;
            }

            .no-bills i {
                font-size: 48px;
                margin-bottom: 15px;
                color: #ddd;
            }

            .no-bills h3 {
                margin: 15px 0 10px 0;
                color: #333;
            }
        }

        /* Desktop Table View */
        @media (min-width: 769px) {
            .bills-mobile {
                display: none;
            }

            .bills-table {
                display: table;
                width: 100%;
            }
        }

        /* Very small screens */
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .bill-details {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-direction: column;
                align-items: center;
            }

            .filter-btn {
                width: 120px;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="./assets/logo.png" alt="RentTracker" style="height: 40px;"></a>
    </div>
    
    <button class="hamburger" id="hamburgerBtn">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <nav id="navigation">
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="join_class.php"><i class="fas fa-user-plus"></i> Join Group</a></li>
            <li><a href="tenant_group_chat.php"><i class="fas fa-envelope"></i> Messages</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    
    <div class="nav-overlay" id="navOverlay"></div>
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

        <!-- Desktop Table View -->
        <table class="bills-table" id="billsTable">
            <thead>
                <tr>
                    <th><i class="fas fa-bolt"></i> Utility</th>
                    <th><i class="fas fa-naira-sign"></i> Amount</th>
                    <th><i class="fas fa-calendar"></i> Due Date</th>
                    <th><i class="fas fa-check-circle"></i> Status</th>
                    <th><i class="fas fa-user-tie"></i> Landlord</th>
                </tr>
            </thead>
            <tbody id="billsTableBody">
                <tr>
                    <td colspan="5" class="loading">
                        <div class="spinner"></div>
                        Loading bills...
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Mobile Card View -->
        <div class="bills-mobile" id="billsMobile">
            <div class="loading">
                <div class="spinner"></div>
                Loading bills...
            </div>
        </div>
    </div>
</div>

<script>
const tenantId = '<?php echo $tenant_id; ?>';
const apiBaseUrl = '<?php echo $api_base_url; ?>';

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const navigation = document.getElementById('navigation');
    const navOverlay = document.getElementById('navOverlay');

    hamburgerBtn.addEventListener('click', function() {
        hamburgerBtn.classList.toggle('active');
        navigation.classList.toggle('active');
        navOverlay.classList.toggle('active');
    });

    navOverlay.addEventListener('click', function() {
        hamburgerBtn.classList.remove('active');
        navigation.classList.remove('active');
        navOverlay.classList.remove('active');
    });

    // Close menu when clicking on nav links
    const navLinks = navigation.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            hamburgerBtn.classList.remove('active');
            navigation.classList.remove('active');
            navOverlay.classList.remove('active');
        });
    });

    // Load dashboard data
    loadDashboardStats();
    loadBills();
});

async function loadDashboardStats() {
    try {
        const response = await fetch(`${apiBaseUrl}/tenant_dashboard_stats.php?tenant_id=${tenantId}`);
        const data = await response.json();
        
        if (data.success) {
            if (!data.has_class) {
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
            displayBillsMobile(data.data);
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
                <td colspan="5" class="no-bills">
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
            </tr>
        `;
    });
    
    billsTableBody.innerHTML = rowsHtml;
    addPayButtonEffects();
}

function displayBillsMobile(bills) {
    const billsMobileContainer = document.getElementById('billsMobile');
    
    if (bills.length === 0) {
        billsMobileContainer.innerHTML = `
            <div class="no-bills">
                <i class="fas fa-file-invoice"></i>
                <h3>No bills found</h3>
                <p>You don't have any bills assigned yet.</p>
            </div>
        `;
        return;
    }
    
    let cardsHtml = '';
    bills.forEach(bill => {
        const cardClass = bill.is_overdue ? 'overdue' : (bill.due_soon ? 'due-soon' : '');
        const filterClass = getFilterClass(bill);
        
        cardsHtml += `
            <div class="bill-card ${cardClass}" data-filter="${filterClass}">
                <div class="bill-header">
                    <div class="bill-utility">
                        <span class="utility-icon">
                            <i class="${bill.utility_icon}"></i>
                        </span>
                        <strong>${bill.bill_name}</strong>
                    </div>
                    <div class="bill-amount">₦${numberFormat(bill.amount)}</div>
                </div>
                
                <div class="bill-details">
                    <div class="bill-detail">
                        <div class="bill-detail-label">Due Date</div>
                        <div class="bill-detail-value">${bill.due_date_formatted}</div>
                    </div>
                    <div class="bill-detail">
                        <div class="bill-detail-label">Landlord</div>
                        <div class="bill-detail-value">
                            <div class="landlord-info">
                                <div class="landlord-avatar">${bill.landlord_initials}</div>
                                <span>${bill.landlord_name}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${bill.note ? `
                    <div style="margin: 10px 0; text-align: center;">
                        <span class="note-tag">${bill.note}</span>
                    </div>
                ` : ''}
                
                <div class="bill-actions">
                    ${bill.paid 
                        ? '<span class="status-badge status-paid">Paid</span>'
                        : `<a href="make_payment.php?bill_id=${bill.id}" class="pay-btn">
                            <i class="fas fa-credit-card"></i> Pay Now
                           </a>`
                    }
                </div>
            </div>
        `;
    });
    
    billsMobileContainer.innerHTML = cardsHtml;
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
    const billsMobileContainer = document.getElementById('billsMobile');
    
    const errorHtml = `
        <div class="error">
            <i class="fas fa-exclamation-triangle"></i>
            Error loading bills: ${message}
        </div>
    `;
    
    billsTableBody.innerHTML = `
        <tr>
            <td colspan="5">${errorHtml}</td>
        </tr>
    `;
    
    billsMobileContainer.innerHTML = errorHtml;
}

function numberFormat(number) {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
}

function filterBills(filter) {
    const table = document.getElementById('billsTable');
    const mobileContainer = document.getElementById('billsMobile');
    const tableRows = table.querySelectorAll('tbody tr');
    const mobileCards = mobileContainer.querySelectorAll('.bill-card');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter table rows
    tableRows.forEach(row => {
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
            row.style.display = filter === 'all' ? '' : 'none';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Filter mobile cards
    mobileCards.forEach(card => {
        const filterData = card.getAttribute('data-filter');
        
        if (filter === 'all') {
            card.style.display = '';
        } else if (filter === 'unpaid' && filterData && filterData.includes('unpaid')) {
            card.style.display = '';
        } else if (filter === 'overdue' && filterData && filterData.includes('overdue')) {
            card.style.display = '';
        } else if (filter === 'due-soon' && filterData && filterData.includes('due-soon')) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function addPayButtonEffects() {
    const payButtons = document.querySelectorAll('.pay-btn');
    payButtons.forEach(btn => {
        if (!btn.innerHTML.includes('fas fa-credit-card')) {
            btn.addEventListener('mouseenter', function() {
                this.innerHTML = '<i class="fas fa-credit-card"></i> Pay Now';
            });
            btn.addEventListener('mouseleave', function() {
                this.innerHTML = 'Pay Now';
            });
        }
    });
}
</script>

</body>
</html>
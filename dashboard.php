<?php
// Simple session check for initial page load
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['tenant_id']) || $_SESSION['users_role'] !== 'tenant') {
    header("Location: login.php");
    exit();
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
    <!-- Loading State -->
    <div id="loadingState" class="loading-container">
        <div class="spinner"></div>
        <p>Loading your dashboard...</p>
    </div>

    <!-- Error State -->
    <div id="errorState" class="error-container" style="display: none;">
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Something went wrong</h3>
            <p id="errorMessage">Unable to load dashboard data. Please try again.</p>
            <button onclick="loadDashboard()" class="retry-btn">
                <i class="fas fa-redo"></i> Try Again
            </button>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div id="dashboardContent" style="display: none;">
        <div class="welcome-section">
            <div class="welcome">
                <i class="fas fa-user-circle"></i>
                Welcome back, <span id="tenantName">...</span>!
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Bills</h3>
                    <div class="amount" id="totalBills">₦0.00</div>
                    <div class="description">All assigned bills</div>
                </div>
                
                <div class="stat-card">
                    <h3>Paid Amount</h3>
                    <div class="amount" id="totalPaid">₦0.00</div>
                    <div class="description">Successfully paid</div>
                </div>
                
                <div class="stat-card balance">
                    <h3>Outstanding Balance</h3>
                    <div class="amount" id="balance">₦0.00</div>
                    <div class="description">Amount pending</div>
                </div>
                
                <div class="stat-card overdue">
                    <h3>Overdue Bills</h3>
                    <div class="amount" id="overdueBills">0</div>
                    <div class="description">Require immediate attention</div>
                </div>
                
                <div class="stat-card due-soon">
                    <h3>Due Soon</h3>
                    <div class="amount" id="dueSoonBills">0</div>
                    <div class="description">Due within 7 days</div>
                </div>
                
                <div class="stat-card">
                    <h3>Total Unpaid</h3>
                    <div class="amount" id="totalUnpaid">0</div>
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
                <tbody id="billsTableBody">
                    <!-- Bills will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const API_BASE_URL = 'https://rent-tracker-api.onrender.com'; 

// Global variables
let dashboardData = null;
let currentFilter = 'all';

// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDashboard();
});

async function loadDashboard() {
    showLoading();
    
    try {
        const response = await fetch(`${API_BASE_URL}/dashboard_api.php`, {
            method: 'GET',
            credentials: 'include', // Include session cookies
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (!data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            throw new Error(data.error || 'Failed to load dashboard data');
        }

        dashboardData = data.data;
        populateDashboard(dashboardData);
        showDashboard();

    } catch (error) {
        console.error('Dashboard loading error:', error);
        showError(error.message);
    }
}

function populateDashboard(data) {
    // Populate tenant info
    document.getElementById('tenantName').textContent = data.tenant.name;

    // Populate stats
    const stats = data.stats;
    document.getElementById('totalBills').textContent = `₦${formatNumber(stats.total_bills)}`;
    document.getElementById('totalPaid').textContent = `₦${formatNumber(stats.total_paid)}`;
    document.getElementById('balance').textContent = `₦${formatNumber(stats.balance)}`;
    document.getElementById('overdueBills').textContent = stats.overdue_count;
    document.getElementById('dueSoonBills').textContent = stats.due_soon_count;
    document.getElementById('totalUnpaid').textContent = stats.total_unpaid_count;

    // Populate bills table
    populateBillsTable(data.bills);
}

function populateBillsTable(bills) {
    const tableBody = document.getElementById('billsTableBody');
    
    if (!bills || bills.length === 0) {
        tableBody.innerHTML = `
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

    tableBody.innerHTML = '';
    
    bills.forEach(bill => {
        const row = document.createElement('tr');
        
        // Add CSS classes for filtering and styling
        let rowClasses = [];
        if (bill.is_overdue && !bill.paid) {
            rowClasses.push('overdue');
        } else if (bill.due_soon && !bill.paid) {
            rowClasses.push('due-soon');
        }
        
        row.className = rowClasses.join(' ');
        row.setAttribute('data-filter', bill.filter_classes.join(' '));

        row.innerHTML = `
            <td>
                <div style="display: flex; align-items: center;">
                    <span class="utility-icon ${bill.utility_type.class}">
                        <i class="${bill.utility_type.icon}"></i>
                    </span>
                    <strong>${bill.name}</strong>
                </div>
            </td>
            <td><strong>₦${formatNumber(bill.amount)}</strong></td>
            <td class="due-date">${bill.due_date_formatted}</td>
            <td>
                ${bill.paid 
                    ? '<span class="status-badge status-paid">Paid</span>'
                    : `<a href="make_payment.php?bill_id=${bill.id}" class="pay-btn">Pay Now</a>`
                }
            </td>
            <td>
                <div class="landlord-info">
                    <div class="landlord-avatar">${bill.landlord.initials}</div>
                    <span>${bill.landlord.name}</span>
                </div>
            </td>
            <td>
                ${bill.note ? `<span class="note-tag">${bill.note}</span>` : '-'}
            </td>
        `;
        
        tableBody.appendChild(row);
    });

    // Add hover effects to pay buttons
    addPayButtonEffects();
}

function filterBills(filter) {
    const table = document.getElementById('billsTable');
    const rows = table.querySelectorAll('tbody tr');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    currentFilter = filter;
    
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

// Utility functions
function formatNumber(num) {
    return new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num);
}

function showLoading() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('errorState').style.display = 'none';
    document.getElementById('dashboardContent').style.display = 'none';
}

function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('dashboardContent').style.display = 'none';
}

function showDashboard() {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'none';
    document.getElementById('dashboardContent').style.display = 'block';
}

// Auto-refresh dashboard every 5 minutes
setInterval(loadDashboard, 5 * 60 * 1000);
</script>

<style>
/* Loading and Error States */
.loading-container, .error-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    text-align: center;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error-message {
    max-width: 400px;
}

.error-message i {
    font-size: 48px;
    color: #dc3545;
    margin-bottom: 20px;
}

.retry-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 20px;
}

.retry-btn:hover {
    background: #0056b3;
}

/* Responsive design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .bills-table {
        font-size: 14px;
    }
    
    .filter-buttons {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .filter-btn {
        font-size: 12px;
        padding: 8px 12px;
    }
}
</style>

</body>
</html>
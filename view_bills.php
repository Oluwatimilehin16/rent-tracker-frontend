<?php
// Only keep session validation - remove all database operations
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$landlord_name = $_SESSION['landlord_name'];
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

        /* Loading state styles */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="card card-total">
                <div class="card-icon"><i class="fas fa-file-invoice"></i></div>
                <div class="card-value">0</div>
                <div class="card-label">Total Bills</div>
                <div class="card-money">₦0.00</div>
            </div>
            <div class="card card-paid">
                <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                <div class="card-value">0</div>
                <div class="card-label">Paid Bills</div>
                <div class="card-money">₦0.00</div>
            </div>
            <div class="card card-overdue">
                <div class="card-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="card-value">0</div>
                <div class="card-label">Overdue</div>
            </div>
            <div class="card card-upcoming">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <div class="card-value">0</div>
                <div class="card-label">Due Soon</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="controls">
            <div class="filter-controls">
                <a href="?filter=all" class="filter-btn active">
                    <i class="fas fa-filter"></i> All 
                    <span class="filter-badge">0</span>
                </a>
                <a href="?filter=paid" class="filter-btn">
                    <i class="fas fa-filter"></i> Paid 
                    <span class="filter-badge">0</span>
                </a>
                <a href="?filter=unpaid" class="filter-btn">
                    <i class="fas fa-filter"></i> Unpaid 
                    <span class="filter-badge">0</span>
                </a>
                <a href="?filter=overdue" class="filter-btn">
                    <i class="fas fa-filter"></i> Overdue 
                    <span class="filter-badge">0</span>
                </a>
                <a href="?filter=upcoming" class="filter-btn">
                    <i class="fas fa-filter"></i> Upcoming 
                    <span class="filter-badge">0</span>
                </a>
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
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">
                            <div class="spinner"></div>Loading bills...
                        </td>
                    </tr>
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
            <input type="hidden" id="deleteBillId">
            <button type="button" onclick="handleDeleteConfirm()" class="btn-danger">Delete</button>
            <button type="button" onclick="closeModal('deleteModal')" class="btn-secondary">Cancel</button>
        </div>
    </div>
</div>

<!-- Remind Modal -->
<div id="remindModal" class="modal">
    <div class="modal-content">
        <h3>Send Reminder</h3>
        <p>Send a payment reminder to the tenant for this bill?</p>
        <div class="modal-buttons">
            <input type="hidden" id="remindBillId">
            <button type="button" onclick="handleRemindConfirm()" class="btn-primary">Send Reminder</button>
            <button type="button" onclick="closeModal('remindModal')" class="btn-secondary">Cancel</button>
        </div>
    </div>
</div>

<script>
    // Pass PHP session data to JavaScript
    window.landlordId = '<?php echo $landlord_id; ?>';
    window.landlordName = '<?php echo htmlspecialchars($landlord_name); ?>';
</script>
<script>
    // Configuration - Update these URLs to your API endpoints
const API_BASE_URL = 'https://rent-tracker-api.onrender.com'; // Replace with your actual API domain
const API_ENDPOINTS = {
    getBills: `${API_BASE_URL}/get_bills_api.php`,
    deleteBill: `${API_BASE_URL}/delete_bill_api.php`,
    sendReminder: `${API_BASE_URL}/send_reminder_api.php`
};

// Global variables
let currentBills = [];
let allBills = [];
let currentStats = {};
let landlordId = '';
let landlordName = '';

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Get landlord info from session/localStorage or make an API call
    // For now, assuming these are available globally
    landlordId = window.landlordId || '';
    landlordName = window.landlordName || '';
    
    if (!landlordId) {
        console.error('Landlord ID not found');
        window.location.href = 'login.php';
        return;
    }
    
    // Get filter from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'all';
    
    // Load bills data
    loadBills(filter);
    
    // Set up event listeners
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.getAttribute('href').split('filter=')[1];
            updateFilter(filter);
        });
    });
    
    // Modal close events
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
}

// Load bills from API
async function loadBills(filter = 'all') {
    try {
        showLoading(true);
        
        const response = await fetch(`${API_ENDPOINTS.getBills}?landlord_id=${encodeURIComponent(landlordId)}&filter=${encodeURIComponent(filter)}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            currentBills = result.data.bills;
            allBills = result.data.all_bills;
            currentStats = result.data.statistics;
            
            updateDashboard();
            updateBillsTable();
            updateFilterButtons();
            
            // Update URL without reloading page
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('filter', filter);
            window.history.replaceState({}, '', newUrl);
            
        } else {
            showError(result.message || 'Failed to load bills');
        }
    } catch (error) {
        console.error('Error loading bills:', error);
        showError('Failed to connect to server. Please try again.');
    } finally {
        showLoading(false);
    }
}

// Update dashboard cards
function updateDashboard() {
    // Update title
    const titleElement = document.querySelector('.title');
    if (titleElement) {
        titleElement.textContent = `Bills Dashboard for ${landlordName} 👋`;
    }
    
    // Update dashboard cards
    updateCard('.card-total .card-value', currentStats.total_bills || 0);
    updateCard('.card-total .card-money', `₦${formatMoney(currentStats.total_amount || 0)}`);
    
    updateCard('.card-paid .card-value', currentStats.paid_bills || 0);
    updateCard('.card-paid .card-money', `₦${formatMoney(currentStats.paid_amount || 0)}`);
    
    updateCard('.card-overdue .card-value', currentStats.overdue_bills || 0);
    updateCard('.card-upcoming .card-value', currentStats.upcoming_bills || 0);
}

// Update a specific card
function updateCard(selector, value) {
    const element = document.querySelector(selector);
    if (element) {
        element.textContent = value;
    }
}

// Update bills table
function updateBillsTable() {
    const tbody = document.querySelector('.bills-table tbody');
    if (!tbody) return;
    
    if (currentBills.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No bills found for the selected filter.</td></tr>';
        return;
    }
    
    tbody.innerHTML = currentBills.map(bill => `
        <tr>
            <td>${escapeHtml(bill.class_name || 'N/A')}</td>
            <td>${escapeHtml(bill.bill_name)}</td>
            <td>${escapeHtml(bill.tenant_name || 'No tenant assigned')}</td>
            <td>₦${formatMoney(bill.amount)}</td>
            <td><span class="due-date ${bill.due_class}">${escapeHtml(bill.due_text)}</span></td>
            <td>
                <span class="status-badge ${bill.status == 1 ? 'status-paid' : 'status-not-paid'}">
                    ${bill.status == 1 ? 'Paid' : 'Not Paid'}
                </span>
            </td>
            <td>
                <a href="view_bill.php?id=${bill.id}" title="View" class="action-btn">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="edit_bill.php?id=${bill.id}" title="Edit" class="action-btn">
                    <i class="fas fa-edit"></i>
                </a>
                ${bill.status == 0 ? `
                    <button onclick="showRemindModal(${bill.id})" title="Remind" class="action-btn btn-remind">
                        <i class="fas fa-bell"></i>
                    </button>
                ` : ''}
                <button onclick="showDeleteModal(${bill.id})" title="Delete" class="action-btn btn-delete">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Update filter buttons
function updateFilterButtons() {
    const filters = {
        all: currentStats.total_bills || 0,
        paid: currentStats.paid_bills || 0,
        unpaid: currentStats.unpaid_bills || 0,
        overdue: currentStats.overdue_bills || 0,
        upcoming: currentStats.upcoming_bills || 0
    };
    
    Object.keys(filters).forEach(filterKey => {
        const badge = document.querySelector(`a[href*="filter=${filterKey}"] .filter-badge`);
        if (badge) {
            badge.textContent = filters[filterKey];
        }
    });
}

// Update filter and reload data
async function updateFilter(filter) {
    // Update active filter button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`a[href*="filter=${filter}"]`)?.classList.add('active');
    
    // Reload bills with new filter
    await loadBills(filter);
}

// Delete bill
async function deleteBill(billId) {
    try {
        showLoading(true);
        
        const response = await fetch(API_ENDPOINTS.deleteBill, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                bill_id: billId,
                landlord_id: landlordId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(result.message);
            // Reload bills to update the display
            const currentFilter = new URLSearchParams(window.location.search).get('filter') || 'all';
            await loadBills(currentFilter);
        } else {
            showError(result.message || 'Failed to delete bill');
        }
    } catch (error) {
        console.error('Error deleting bill:', error);
        showError('Failed to connect to server. Please try again.');
    } finally {
        showLoading(false);
    }
}

// Send reminder
async function sendReminder(billId) {
    try {
        showLoading(true);
        
        const response = await fetch(API_ENDPOINTS.sendReminder, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                bill_id: billId,
                landlord_id: landlordId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(result.message);
        } else {
            showError(result.message || 'Failed to send reminder');
        }
    } catch (error) {
        console.error('Error sending reminder:', error);
        showError('Failed to connect to server. Please try again.');
    } finally {
        showLoading(false);
    }
}

// Modal functions
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

// Handle modal form submissions
function handleDeleteConfirm() {
    const billId = document.getElementById('deleteBillId').value;
    if (billId) {
        deleteBill(parseInt(billId));
        closeModal('deleteModal');
    }
}

function handleRemindConfirm() {
    const billId = document.getElementById('remindBillId').value;
    if (billId) {
        sendReminder(parseInt(billId));
        closeModal('remindModal');
    }
}

// Utility functions
function formatMoney(amount) {
    return parseFloat(amount || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showLoading(show) {
    // You can implement a loading indicator here
    // For example, disable buttons, show spinner, etc.
    const buttons = document.querySelectorAll('button, .action-btn');
    buttons.forEach(btn => {
        btn.disabled = show;
    });
}

function showSuccess(message) {
    // Remove existing alerts
    removeExistingAlerts();
    
    // Create success alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-success';
    alert.textContent = message;
    
    // Insert after the title
    const title = document.querySelector('.title');
    title.parentNode.insertBefore(alert, title.nextSibling);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function showError(message) {
    // Remove existing alerts
    removeExistingAlerts();
    
    // Create error alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger';
    alert.textContent = message;
    
    // Insert after the title
    const title = document.querySelector('.title');
    title.parentNode.insertBefore(alert, title.nextSibling);
    
    // Auto-remove after 8 seconds
    setTimeout(() => {
        alert.remove();
    }, 8000);
}

function removeExistingAlerts() {
    document.querySelectorAll('.alert').forEach(alert => alert.remove());
}

// Navigation functions (existing)
function toggleMenu() {
    document.getElementById('main-nav').classList.toggle('active');
}
</script>
</body>
</html>
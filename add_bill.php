<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_name = $_SESSION['landlord_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bill - Rent & Utility Tracker</title>
    <link rel="stylesheet" href="add_bill.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

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

        <!-- Loading indicator -->
        <div id="loading" style="display: none; text-align: center; margin: 20px 0;">
            <i class="fas fa-spinner fa-spin"></i> Loading...
        </div>

        <!-- Error/Success messages -->
        <div id="message-container" style="display: none; margin: 20px 0; padding: 15px; border-radius: 5px;">
            <span id="message-text"></span>
        </div>

        <form id="add-bill-form" class="add-bill-form">
            <div class="form-group">
                <label for="class_id"><i class="fas fa-users"></i> Select Group (Tenant)</label>
                <select name="class_id" id="class_id" required>
                    <option value="">-- Loading classes... --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="bill_name"><i class="fas fa-file-signature"></i> Bill Name</label>
                <select name="bill_name" id="bill_name" required>
                    <option value="">Select Bill Type</option>
                </select>
            </div>

            <div class="form-group" id="other_bill_name_group" style="display: none;">
                <label for="other_bill_name"><i class="fas fa-pen"></i> Specify Bill Name</label>
                <input type="text" name="other_bill_name" id="other_bill_name" placeholder="Enter custom bill name">
            </div>

            <div class="form-group">
                <label for="amount"><i class="fas fa-coins"></i> Amount (₦)</label>
                <input type="number" name="amount" id="amount" placeholder="Enter amount" required step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="due_date"><i class="fas fa-calendar-alt"></i> Due Date</label>
                <input type="date" name="due_date" id="due_date" required>
            </div>

            <button type="submit" id="submit-btn" class="submit-btn">
                <i class="fas fa-plus"></i> Add Bill
            </button>
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
// Configuration - Update this with your API base URL
const API_BASE_URL = 'https://rent-tracker-api.onrender.com';

// API helper class
class BillAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        // Get landlord ID from PHP session
        this.landlordId = '<?php echo $_SESSION['landlord_id'] ?? ''; ?>';
    }

    async getClassesAndBills() {
        const response = await fetch(`${this.baseUrl}/landlord_classes_api.php`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include'  // ← Add this to include cookies/session
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to fetch classes');
        }

        return data;
    }

    async addBill(billData) {
        // Add landlord_id to bill data
        billData.landlord_id = this.landlordId;

        const response = await fetch(`${this.baseUrl}/add_bill_api.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(billData),
            credentials: 'include'  // ← Add this to include cookies/session
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to add bill');
        }

        return data;
    }
}
// Initialize API
const billAPI = new BillAPI(API_BASE_URL);

// DOM elements
const loadingEl = document.getElementById('loading');
const messageContainer = document.getElementById('message-container');
const messageText = document.getElementById('message-text');
const classSelect = document.getElementById('class_id');
const billNameSelect = document.getElementById('bill_name');
const otherBillNameGroup = document.getElementById('other_bill_name_group');
const otherBillNameInput = document.getElementById('other_bill_name');
const form = document.getElementById('add-bill-form');
const submitBtn = document.getElementById('submit-btn');

// Utility functions
function showLoading(show = true) {
    loadingEl.style.display = show ? 'block' : 'none';
}

function showMessage(message, isError = false) {
    messageContainer.style.display = 'block';
    messageContainer.className = isError ? 'error-message' : 'success-message';
    messageContainer.style.backgroundColor = isError ? '#ffebee' : '#e8f5e8';
    messageContainer.style.color = isError ? '#c62828' : '#2e7d32';
    messageContainer.style.border = isError ? '1px solid #ef5350' : '1px solid #66bb6a';
    messageText.textContent = message;
    
    // Auto-hide success messages after 5 seconds
    if (!isError) {
        setTimeout(() => {
            messageContainer.style.display = 'none';
        }, 5000);
    }
}

function hideMessage() {
    messageContainer.style.display = 'none';
}

function populateClassDropdown(classes) {
    classSelect.innerHTML = '<option value="">-- Choose Group (Tenant) --</option>';
    
    classes.forEach(cls => {
        const option = document.createElement('option');
        option.value = cls.id;
        option.textContent = `${cls.class_name} (${cls.class_code}) - ${cls.tenant_count} tenant${cls.tenant_count !== 1 ? 's' : ''}`;
        classSelect.appendChild(option);
    });
}

function populateBillTypeDropdown(suggestedBills) {
    billNameSelect.innerHTML = '<option value="">Select Bill Type</option>';
    
    suggestedBills.forEach(bill => {
        const option = document.createElement('option');
        option.value = bill;
        option.textContent = bill;
        billNameSelect.appendChild(option);
    });
    
    // Add "Other" option
    const otherOption = document.createElement('option');
    otherOption.value = 'other';
    otherOption.textContent = 'Other (Specify Below)';
    billNameSelect.appendChild(otherOption);
}

function setSubmitButtonState(loading = false) {
    if (loading) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Bill...';
    } else {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add Bill';
    }
}

// Load initial data
async function loadInitialData() {
    showLoading(true);
    hideMessage();
    
    try {
        const data = await billAPI.getClassesAndBills();
        populateClassDropdown(data.data.classes);
        populateBillTypeDropdown(data.data.suggested_bills);
        
        if (data.data.classes.length === 0) {
            showMessage('No tenant groups found. Please create a group first before adding bills.', true);
        }
        
    } catch (error) {
        console.error('Error loading initial data:', error);
        showMessage('Error loading data: ' + error.message, true);
    } finally {
        showLoading(false);
    }
}

// Form submission handler
form.addEventListener('submit', async function(e) {
    e.preventDefault();
    hideMessage();
    setSubmitButtonState(true);
    
    try {
        const formData = new FormData(this);
        const billData = {
            bill_name: formData.get('bill_name'),
            amount: formData.get('amount'),
            due_date: formData.get('due_date'),
            class_id: formData.get('class_id')
        };

        // Handle "other" bill name
        if (billData.bill_name === 'other') {
            const otherBillName = formData.get('other_bill_name');
            if (!otherBillName || !otherBillName.trim()) {
                throw new Error('Please specify the custom bill name.');
            }
            billData.other_bill_name = otherBillName.trim();
        }

        // Validate amount
        if (!billData.amount || parseFloat(billData.amount) <= 0) {
            throw new Error('Please enter a valid amount greater than 0.');
        }

        // Validate due date
        const dueDate = new Date(billData.due_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (dueDate < today) {
            const confirm = window.confirm('The due date is in the past. Do you want to continue?');
            if (!confirm) {
                return;
            }
        }

        const result = await billAPI.addBill(billData);
        
        showMessage(`Bill "${result.data.bill_name}" added successfully for ${result.data.class_name}!`, false);
        
        // Reset form
        this.reset();
        otherBillNameGroup.style.display = 'none';
        
    } catch (error) {
        console.error('Error adding bill:', error);
        showMessage('Error adding bill: ' + error.message, true);
    } finally {
        setSubmitButtonState(false);
    }
});

// Bill name change handler
billNameSelect.addEventListener('change', function() {
    const isOther = this.value === 'other';
    otherBillNameGroup.style.display = isOther ? 'block' : 'none';
    
    if (isOther) {
        otherBillNameInput.required = true;
        otherBillNameInput.focus();
    } else {
        otherBillNameInput.required = false;
        otherBillNameInput.value = '';
    }
});

// Hamburger menu toggle
function toggleMenu() {
    document.getElementById('main-nav').classList.toggle('active');
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadInitialData();
});

// Set minimum date to today
document.getElementById('due_date').min = new Date().toISOString().split('T')[0];
</script>

<style>
/* Additional styles for messages */
.error-message, .success-message {
    border-radius: 5px;
    padding: 15px;
    margin: 20px 0;
    font-weight: 500;
}

.error-message {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ef5350;
}

.success-message {
    background-color: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #66bb6a;
}

#submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

#loading {
    font-size: 16px;
    color: #666;
}
</style>

</body>
</html>
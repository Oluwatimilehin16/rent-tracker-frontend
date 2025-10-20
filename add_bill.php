<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$landlord_name = $_SESSION['landlord_name'];

// Suggested bill names
$suggested_bills = ['Rent', 'Water', 'Electricity', 'Gas', 'Internet', 'Maintenance'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bill - Rent & Utility Tracker</title>
    <link rel="stylesheet" href="add_bill.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .error-message {
            color: #d32f2f;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            display: none;
        }
        .success-message {
            color: #2e7d32;
            background-color: #e8f5e8;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            display: none;
        }
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
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

        <div id="loading" class="loading">
            <i class="fas fa-spinner fa-spin"></i> Loading classes...
        </div>

        <div id="error-message" class="error-message"></div>
        <div id="success-message" class="success-message"></div>

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
                    <?php foreach ($suggested_bills as $bill) : ?>
                        <option value="<?= $bill ?>"><?= $bill ?></option>
                    <?php endforeach; ?>
                    <option value="other">Other (Specify Below)</option>
                </select>
            </div>

            <div class="form-group" id="other_bill_name_group" style="display: none;">
                <label for="other_bill_name"><i class="fas fa-pen"></i> Specify Bill Name</label>
                <input type="text" name="other_bill_name" id="other_bill_name" placeholder="Enter custom bill name">
            </div>

            <div class="form-group">
                <label for="amount"><i class="fas fa-coins"></i> Amount (₦)</label>
                <input type="number" name="amount" id="amount" placeholder="Enter amount" required min="1" step="0.01">
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
const API_BASE_URL = 'https://rent-tracker-api.onrender.com';
const landlordId = <?php echo json_encode($landlord_id); ?>;

// Show/hide messages
function showMessage(message, type = 'error') {
    const errorDiv = document.getElementById('error-message');
    const successDiv = document.getElementById('success-message');
    
    if (type === 'error') {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        successDiv.style.display = 'none';
    } else {
        successDiv.textContent = message;
        successDiv.style.display = 'block';
        errorDiv.style.display = 'none';
    }
}

function hideMessages() {
    document.getElementById('error-message').style.display = 'none';
    document.getElementById('success-message').style.display = 'none';
}

// Load classes from API
async function loadClasses() {
    const loading = document.getElementById('loading');
    const classSelect = document.getElementById('class_id');
    
    loading.style.display = 'block';
    
    try {
        const response = await fetch(`${API_BASE_URL}/get_classes_api.php?landlord_id=${landlordId}`);
        const data = await response.json();
        
        if (data.success) {
            classSelect.innerHTML = '<option value="">-- Choose Group (Tenant) --</option>';
            
            data.classes.forEach(cls => {
                const option = document.createElement('option');
                option.value = cls.id;
                option.textContent = `${cls.class_name} (${cls.class_code})`;
                classSelect.appendChild(option);
            });
            
            if (data.classes.length === 0) {
                classSelect.innerHTML = '<option value="">-- No classes found --</option>';
                showMessage('No tenant groups found. Please create a group first.', 'error');
            }
        } else {
            showMessage(data.message || 'Failed to load classes', 'error');
            classSelect.innerHTML = '<option value="">-- Failed to load classes --</option>';
        }
    } catch (error) {
        console.error('Error loading classes:', error);
        showMessage('Network error while loading classes. Please check your connection.', 'error');
        classSelect.innerHTML = '<option value="">-- Error loading classes --</option>';
    } finally {
        loading.style.display = 'none';
    }
}

// Handle form submission
document.getElementById('add-bill-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    hideMessages();
    
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Bill...';
    
    const formData = new FormData(this);
    
    // Handle custom bill name
    let billName = formData.get('bill_name');
    if (billName === 'other') {
        billName = formData.get('other_bill_name');
        if (!billName || billName.trim() === '') {
            showMessage('Please specify the custom bill name.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add Bill';
            return;
        }
    }
    
    const billData = {
        bill_name: billName,
        amount: formData.get('amount'),
        due_date: formData.get('due_date'),
        class_id: formData.get('class_id'),
        landlord_id: landlordId
    };
    
    try {
        const response = await fetch(`${API_BASE_URL}/add_bill_api.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(billData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message || 'Bill added successfully!', 'success');
            this.reset(); // Clear form
            document.getElementById('other_bill_name_group').style.display = 'none';
        } else {
            showMessage(data.message || 'Failed to add bill', 'error');
        }
    } catch (error) {
        console.error('Error adding bill:', error);
        showMessage('Network error. Please check your connection and try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add Bill';
    }
});

// Toggle hamburger menu
function toggleMenu() {
    document.getElementById('main-nav').classList.toggle('active');
}

// Toggle visibility of 'Other' input
document.getElementById('bill_name').addEventListener('change', function () {
    const otherGroup = document.getElementById('other_bill_name_group');
    const otherInput = document.getElementById('other_bill_name');
    
    if (this.value === 'other') {
        otherGroup.style.display = 'block';
        otherInput.required = true;
    } else {
        otherGroup.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
});

// Load classes when page loads
document.addEventListener('DOMContentLoaded', loadClasses);
</script>

</body>
</html>
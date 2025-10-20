<?php
session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class</title>
    <link rel="stylesheet" href="create_class.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="page-wrapper">
<header>
    <div class="logo">
        <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
    </div>
    <div class="menu-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </div>
    <nav>
        <ul>
            <li><a href="create_class.php" class="active"><i class="fas fa-user-plus"></i> Invite</a></li>
            <li><a href="add_bill.php"><i class="fas fa-plus-circle"></i> Add Bill</a></li>
            <li><a href="view_bills.php"><i class="fas fa-list-alt"></i> View Bills</a></li>
            <li><a href="create_groupchat.php"><i class="fas fa-users"></i> Create group chat</a></li>
            <li><a href="landlord_group_chats.php">💬 View My Group Chats</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <h1>Create a New Group</h1>
    <p>Give your group (tenants) a name and a unique code will be generated automatically.</p>

    <div id="error-message" class="error-message"></div>
    <div id="success-box" class="success-box"></div>
    <div id="loading" class="loading">
        <i class="fas fa-spinner fa-spin"></i> Creating group...
    </div>

    <form id="createClassForm">
        <div class="form-group">
            <label for="class_name">Group Name</label>
            <input type="text" 
                   id="class_name" 
                   name="class_name" 
                   placeholder="e.g., Apartment A" 
                   required 
                   maxlength="100"
                   autocomplete="off">
            <div id="char-counter" class="char-counter">0/100 characters</div>
        </div>
        
        <button type="submit" id="submitBtn">
            <i class="fas fa-plus"></i> Create Group
        </button>
    </form>
</div>
</div>

<footer>
    <div class="footer-content">
        <p>&copy; <?php echo date("Y"); ?> Rent & Utility Tracker. All rights reserved. Developed by Your Rentals Team</p>
    </div>
</footer>

<script>
const landlordId = <?php echo json_encode($landlord_id); ?>;
const API_BASE_URL = 'https://rent-tracker-api.onrender.com'; 

function toggleMenu() {
    const navList = document.querySelector('nav ul');
    navList.classList.toggle('show');
}

// Show/hide messages
function showMessage(message, type = 'error') {
    const errorDiv = document.getElementById('error-message');
    const successDiv = document.getElementById('success-box');
    
    // Hide both first
    errorDiv.style.display = 'none';
    successDiv.style.display = 'none';
    successDiv.classList.remove('show');
    
    if (type === 'success') {
        successDiv.innerHTML = message;
        successDiv.classList.add('show');
        successDiv.style.display = 'block';
    } else {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
    
    // Auto-hide error messages after 5 seconds
    if (type === 'error') {
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 5000);
    }
}

// Show/hide loading
function setLoading(loading) {
    const loadingDiv = document.getElementById('loading');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('createClassForm');
    
    loadingDiv.style.display = loading ? 'block' : 'none';
    submitBtn.disabled = loading;
    
    if (loading) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    } else {
        submitBtn.innerHTML = '<i class="fas fa-plus"></i> Create Group';
    }
    
    // Disable form inputs during loading
    const inputs = form.querySelectorAll('input, button');
    inputs.forEach(input => {
        input.disabled = loading;
    });
}

// Character counter
function updateCharCounter() {
    const input = document.getElementById('class_name');
    const counter = document.getElementById('char-counter');
    const currentLength = input.value.length;
    const maxLength = 100;
    
    counter.textContent = `${currentLength}/${maxLength} characters`;
    
    // Update counter color based on length
    counter.classList.remove('warning', 'error');
    if (currentLength > maxLength * 0.8) {
        counter.classList.add('warning');
    }
    if (currentLength >= maxLength) {
        counter.classList.add('error');
    }
}

// Copy code to clipboard
async function copyCode(code, button) {
    try {
        await navigator.clipboard.writeText(code);
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.disabled = true;
        
        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy code:', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        button.textContent = 'Copied!';
        setTimeout(() => {
            button.textContent = 'Copy';
        }, 2000);
    }
}

// Create class via API
async function createClass(className) {
    try {
        setLoading(true);
        
        const response = await fetch(`${API_BASE_URL}/create_class_api.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                landlord_id: landlordId,
                class_name: className
            })
        });

        const data = await response.json();
        
        if (data.success) {
            const successMessage = `
                ✅ Class <strong>${escapeHtml(data.data.class_name)}</strong> created successfully! <br> 
                Share this code with tenants: 
                <span class="code" onclick="copyCode('${data.data.class_code}', this.nextElementSibling)" title="Click to copy">
                    ${data.data.class_code}
                </span>
                <button class="copy-btn" onclick="copyCode('${data.data.class_code}', this)">Copy</button>
                <br><br>
                <small>Class ID: ${data.data.class_id} | Created: ${formatDateTime(data.data.created_at)}</small>
            `;
            
            showMessage(successMessage, 'success');
            
            // Reset form
            document.getElementById('createClassForm').reset();
            updateCharCounter();
            
            // Optional: Show success for longer time
            setTimeout(() => {
                document.getElementById('success-box').style.display = 'none';
            }, 30000); // 30 seconds
            
        } else {
            showMessage(data.message || 'Failed to create class');
        }
    } catch (error) {
        console.error('Error creating class:', error);
        showMessage('Failed to connect to server. Please check your internet connection and try again.');
    } finally {
        setLoading(false);
    }
}

// Form submission handler
document.getElementById('createClassForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const className = document.getElementById('class_name').value.trim();
    
    // Client-side validation
    if (!className) {
        showMessage('Please enter a class name');
        return;
    }
    
    if (className.length < 2) {
        showMessage('Class name must be at least 2 characters long');
        return;
    }
    
    if (className.length > 100) {
        showMessage('Class name must be less than 100 characters');
        return;
    }
    
    // Check for invalid characters
    if (/[<>"']/.test(className)) {
        showMessage('Class name contains invalid characters');
        return;
    }
    
    await createClass(className);
});

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
}

// Setup event listeners
document.addEventListener('DOMContentLoaded', function() {
    const classNameInput = document.getElementById('class_name');
    
    // Character counter
    classNameInput.addEventListener('input', updateCharCounter);
    
    // Initialize character counter
    updateCharCounter();
    
    // Focus on input when page loads
    classNameInput.focus();
});
</script>

</body>
</html>
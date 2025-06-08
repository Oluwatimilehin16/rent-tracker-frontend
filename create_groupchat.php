<?php
session_start();

// Ensure only landlord can access
if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create Group Chat</title>
    <link rel="stylesheet" href="create_groupchat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- FontAwesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .error-message {
            color: #e74c3c;
            background-color: #fdf2f2;
            border: 1px solid #fecaca;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            display: none;
        }
        .success-message {
            color: #059669;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            display: none;
        }
        .checkboxes {
            max-height: 300px;
            overflow-y: auto;
        }
        .class-item {
            padding: 8px;
            border: 1px solid #e5e7eb;
            margin: 5px 0;
            border-radius: 4px;
            background-color: #f9fafb;
        }
        button:disabled {
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
    <div class="menu-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </div>
    <nav>
        <ul>
            <li><a href="create_class.php"><i class="fas fa-user-plus"></i> Invite</a></li>
            <li><a href="add_bill.php"><i class="fas fa-plus-circle"></i> Add Bill</a></li>
            <li><a href="view_bills.php"><i class="fas fa-list-alt"></i> View Bills</a></li>
            <li><a href="create_groupchat.php" class="active"><i class="fas fa-users"></i> Create group chat</a></li>
            <li><a href="landlord_group_chats.php">💬 View My Group Chats</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</header>

<div class="content-wrapper">
    <div class="container">
        <h1>Create Group Chat</h1>

        <div id="error-message" class="error-message"></div>
        <div id="success-message" class="success-message"></div>
        <div id="loading" class="loading">
            <i class="fas fa-spinner fa-spin"></i> Loading...
        </div>

        <form id="createGroupForm">
            <input type="text" id="group_name" name="group_name" placeholder="Enter Group Chat Name" required autocomplete="off" />

            <label for="class_ids">Select Classes to Include</label>
            <div class="checkboxes" id="class_ids" role="group" aria-label="Select classes">
                <!-- Classes will be loaded here via API -->
            </div>

            <button type="submit" id="submitBtn">Create Group</button>
        </form>
    </div>
</div>

<footer>
    <div class="footer-content">
        <p>© 2025 RentTracker. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
    const socket = io('https://rent-tracker-backend.onrender.com'); 
    const landlordId = <?php echo json_encode($landlord_id); ?>;
    const API_BASE_URL = 'https://rent-tracker-api.onrender.com'; 

    function toggleMenu() {
        const nav = document.querySelector('nav');     
        nav.classList.toggle('show');                  
    }

    // Show/hide messages
    function showMessage(message, type = 'error') {
        const errorDiv = document.getElementById('error-message');
        const successDiv = document.getElementById('success-message');
        
        // Hide both first
        errorDiv.style.display = 'none';
        successDiv.style.display = 'none';
        
        if (type === 'success') {
            successDiv.textContent = message;
            successDiv.style.display = 'block';
        } else {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
        }, 5000);
    }

    // Show/hide loading
    function setLoading(loading) {
        const loadingDiv = document.getElementById('loading');
        const submitBtn = document.getElementById('submitBtn');
        
        loadingDiv.style.display = loading ? 'block' : 'none';
        submitBtn.disabled = loading;
        submitBtn.textContent = loading ? 'Creating...' : 'Create Group';
    }

    // Load classes from API
    async function loadClasses() {
        try {
            setLoading(true);
            
            const response = await fetch(`${API_BASE_URL}/create_groupchat_api.php?landlord_id=${landlordId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();
            
            if (data.success) {
                displayClasses(data.data.classes);
            } else {
                showMessage(data.message || 'Failed to load classes');
            }
        } catch (error) {
            console.error('Error loading classes:', error);
            showMessage('Failed to connect to server. Please try again.');
        } finally {
            setLoading(false);
        }
    }

    // Display classes in checkboxes
    function displayClasses(classes) {
        const classContainer = document.getElementById('class_ids');
        
        if (classes.length === 0) {
            classContainer.innerHTML = '<p>No classes found. <a href="create_class.php">Create a class first</a>.</p>';
            return;
        }
        
        classContainer.innerHTML = classes.map(classItem => `
            <div class="class-item">
                <label>
                    <input type="checkbox" name="class_ids[]" value="${classItem.id}" />
                    ${escapeHtml(classItem.class_name)}
                    <small style="color: #666; display: block;">Created: ${formatDate(classItem.created_at)}</small>
                </label>
            </div>
        `).join('');
    }

    // Create group chat
    async function createGroupChat(groupName, selectedClasses) {
        try {
            setLoading(true);
            
            const response = await fetch(`${API_BASE_URL}/create_groupchat_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    landlord_id: landlordId,
                    group_name: groupName,
                    class_ids: selectedClasses
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showMessage(`Group chat "${data.data.group_name}" created successfully with ${data.data.total_classes} classes!`, 'success');
                
                // Reset form
                document.getElementById('createGroupForm').reset();
                
                // Optionally redirect after success
                setTimeout(() => {
                    window.location.href = 'landlord_group_chats.php';
                }, 2000);
            } else {
                showMessage(data.message || 'Failed to create group chat');
            }
        } catch (error) {
            console.error('Error creating group chat:', error);
            showMessage('Failed to connect to server. Please try again.');
        } finally {
            setLoading(false);
        }
    }

    // Form submission handler
    document.getElementById('createGroupForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const groupName = document.getElementById('group_name').value.trim();
        const selectedCheckboxes = document.querySelectorAll('input[name="class_ids[]"]:checked');
        const selectedClasses = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
        
        // Validation
        if (!groupName) {
            showMessage('Please enter a group chat name');
            return;
        }
        
        if (groupName.length < 2 || groupName.length > 100) {
            showMessage('Group name must be between 2 and 100 characters');
            return;
        }
        
        if (selectedClasses.length === 0) {
            showMessage('Please select at least one class');
            return;
        }
        
        await createGroupChat(groupName, selectedClasses);
    });

    // Utility functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }

    // Load classes when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadClasses();
    });
</script>

</body>
</html>
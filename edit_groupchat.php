<?php
session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];

// Get group_id from URL
if (!isset($_GET['group_id'])) {
    header("Location: landlord_group_chats.php");
    exit();
}

$group_id = $_GET['group_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Group Chat | RentTracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="edit_groupchat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            <li><a href="add_bill.php"><i class="fas fa-plus-circle"></i> Add Bill</a></li>
            <li><a href="view_bills.php"><i class="fas fa-list-alt"></i> View Bills</a></li>
            <li><a href="create_groupchat.php"><i class="fas fa-users"></i> Create group chat</a></li>
            <li><a href="landlord_group_chats.php" class="active">💬 View My Group Chats</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-edit"></i> Edit Group Chat</h1>
        <p id="group-name-subtitle">Loading group chat details...</p>
    </div>

    <div class="content">
        <div class="nav-breadcrumb">
            <i class="fas fa-home"></i>
            <a href="landlord_group_chats.php">My Group Chats</a> > 
            <span id="breadcrumb-name">Loading...</span>
        </div>

        <div id="message-container"></div>
        
        <div id="loading-spinner" style="text-align: center; padding: 20px;">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Loading group chat details...</p>
        </div>

        <div id="main-content" style="display: none;">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-value" id="total-members">0</div>
                    <div class="stat-label">Total Members</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-envelope"></i>
                    <div class="stat-value" id="message-count">0</div>
                    <div class="stat-label">Messages Sent</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-layer-group"></i>
                    <div class="stat-value" id="classes-count">0</div>
                    <div class="stat-label">Classes Added</div>
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-users"></i> Current Members</h2>
                <div id="current-members-container">
                    <!-- Members will be loaded here -->
                </div>
            </div>

            <div class="section">
                <h2><i class="fas fa-user-plus"></i> Add New Members</h2>
                <div id="add-members-container">
                    <!-- Add members form will be loaded here -->
                </div>
            </div>

            <div class="section danger-zone">
                <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                <p style="margin-bottom: 20px;">
                    Deleting this group chat will permanently remove all messages and cannot be undone.
                </p>
                <button type="button" class="btn btn-danger" onclick="showDeleteModal()">
                    <i class="fas fa-trash"></i> Delete Group Chat
                </button>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="landlord_group_chats.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to My Group Chats
                </a>
                <a href="group_chat_landlord.php?group_id=<?= $group_id ?>" class="btn btn-primary">
                    <i class="fas fa-comments"></i> View Chat Messages
                </a>
            </div>
        </div>
    </div>
</div>

<footer style="background-color: #218838; color: white; text-align: center; padding: 20px; margin-top: auto;">
    &copy; <?= date('Y') ?> RentTracker. All rights reserved. | <a href="privacy.php" style="color: #ecc700;">Privacy Policy</a> | <a href="terms.php" style="color: #ecc700;">Terms of Service</a>
</footer>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
        <p>Are you absolutely sure you want to delete the group chat "<strong id="delete-group-name"></strong>"?</p>
        <p style="color: #dc3545; font-weight: bold;">This action cannot be undone!</p>
        
        <div class="modal-buttons">
            <button type="button" class="btn btn-secondary" onclick="hideDeleteModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-danger" onclick="deleteGroup()">
                <i class="fas fa-trash"></i> Yes, Delete Group Chat
            </button>
        </div>
    </div>
</div>

<script>
const API_BASE_URL = 'https://rent-tracker-api.onrender.com'; 
const groupId = <?= $group_id ?>;
const landlordId = <?= $landlord_id ?>;
let groupData = null;

// Show message to user
function showMessage(message, type) {
    const messageContainer = document.getElementById('message-container');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
    `;
    
    messageContainer.innerHTML = ''; // Clear previous messages
    messageContainer.appendChild(messageDiv);
    
    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }
    
    // Scroll to message
    messageDiv.scrollIntoView({ behavior: 'smooth' });
}

// Load group chat details
async function loadGroupDetails() {
    try {
        const response = await fetch(`${API_BASE_URL}/groupchat_api.php?action=get_group_details&group_id=${groupId}&landlord_id=${landlordId}`);
        const result = await response.json();
        
        if (!result.success) {
            if (response.status === 404) {
                // Group not found or access denied
                window.location.href = 'landlord_group_chats.php';
                return;
            }
            throw new Error(result.message);
        }
        
        groupData = result.data;
        displayGroupDetails();
        
    } catch (error) {
        console.error('Error loading group details:', error);
        showMessage('Error loading group chat details: ' + error.message, 'error');
    }
}

// Display group details
function displayGroupDetails() {
    // Update header
    document.getElementById('group-name-subtitle').textContent = `Manage members and settings for "${groupData.group_chat.name}"`;
    document.getElementById('breadcrumb-name').textContent = `Edit "${groupData.group_chat.name}"`;
    document.getElementById('delete-group-name').textContent = groupData.group_chat.name;
    
    // Update stats
    document.getElementById('total-members').textContent = groupData.stats.total_members;
    document.getElementById('message-count').textContent = groupData.stats.message_count;
    document.getElementById('classes-count').textContent = groupData.current_members.length;
    
    // Display current members
    displayCurrentMembers();
    
    // Display add members form
    displayAddMembersForm();
    
    // Hide loading spinner and show content
    document.getElementById('loading-spinner').style.display = 'none';
    document.getElementById('main-content').style.display = 'block';
}

// Display current members
function displayCurrentMembers() {
    const container = document.getElementById('current-members-container');
    
    if (groupData.current_members.length === 0) {
        container.innerHTML = `
            <div class="no-classes">
                <i class="fas fa-users-slash"></i>
                <h3>No Classes Added</h3>
                <p>This group chat doesn't have any classes added yet.</p>
            </div>
        `;
        return;
    }
    
    const membersList = groupData.current_members.map(member => `
        <div class="member-item">
            <div class="member-info">
                <h4>${escapeHtml(member.class_name)}</h4>
                <p><i class="fas fa-users"></i> ${member.member_count} tenant${member.member_count != 1 ? 's' : ''} in this class</p>
            </div>
            <button type="button" class="btn btn-danger btn-small" onclick="removeClass(${member.class_id}, '${escapeHtml(member.class_name)}')">
                <i class="fas fa-times"></i> Remove
            </button>
        </div>
    `).join('');
    
    container.innerHTML = `<div class="member-list">${membersList}</div>`;
}

// Display add members form
function displayAddMembersForm() {
    const container = document.getElementById('add-members-container');
    
    if (groupData.available_classes.length === 0) {
        container.innerHTML = `
            <div class="no-classes">
                <i class="fas fa-info-circle"></i>
                <h3>No Available Classes</h3>
                <p>All your classes have been added to this group chat, or you don't have any other classes to add.</p>
                <a href="create_class.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Class
                </a>
            </div>
        `;
        return;
    }
    
    const optionsList = groupData.available_classes.map(cls => 
        `<option value="${cls.id}">${escapeHtml(cls.class_name)} (${cls.member_count} tenant${cls.member_count != 1 ? 's' : ''})</option>`
    ).join('');
    
    container.innerHTML = `
        <form onsubmit="addClass(event)">
            <div class="form-group">
                <label for="class_id">Select a class to add to this group chat:</label>
                <select name="class_id" id="class_id" required>
                    <option value="">Choose a class...</option>
                    ${optionsList}
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Class to Group Chat
            </button>
        </form>
    `;
}

// Add class to group chat
async function addClass(event) {
    event.preventDefault();
    
    const classId = document.getElementById('class_id').value;
    if (!classId) return;
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch(`${API_BASE_URL}/groupchat_api.php?action=add_class`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                group_id: groupId,
                class_id: parseInt(classId),
                landlord_id: landlordId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            // Reload the group details to update the display
            await loadGroupDetails();
        } else {
            showMessage(result.message, 'error');
        }
        
    } catch (error) {
        console.error('Error adding class:', error);
        showMessage('Error adding class to group chat: ' + error.message, 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Remove class from group chat
async function removeClass(classId, className) {
    if (!confirm(`Are you sure you want to remove "${className}" from the group chat?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/groupchat_api.php?action=remove_class`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                group_id: groupId,
                class_id: classId,
                landlord_id: landlordId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            // Reload the group details to update the display
            await loadGroupDetails();
        } else {
            showMessage(result.message, 'error');
        }
        
    } catch (error) {
        console.error('Error removing class:', error);
        showMessage('Error removing class from group chat: ' + error.message, 'error');
    }
}

// Delete group chat
async function deleteGroup() {
    hideDeleteModal();
    
    try {
        const response = await fetch(`${API_BASE_URL}/groupchat_api.php?action=delete_group`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                group_id: groupId,
                landlord_id: landlordId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Redirect to group chats list with success message
            window.location.href = 'landlord_group_chats.php?deleted=1';
        } else {
            showMessage(result.message, 'error');
        }
        
    } catch (error) {
        console.error('Error deleting group:', error);
        showMessage('Error deleting group chat: ' + error.message, 'error');
    }
}

// Utility function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Modal functions
function showDeleteModal() {
    document.getElementById('deleteModal').style.display = 'block';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideDeleteModal();
    }
});

function toggleMenu() {
    document.getElementById('main-nav').classList.toggle('active');
}

// Load group details when page loads
document.addEventListener('DOMContentLoaded', loadGroupDetails);
</script>

</body>
</html>
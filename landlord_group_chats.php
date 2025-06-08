<?php
session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Group Chats | RentTracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="landlord_group_chats.css">
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
            <li><a href="create_groupchat.php" class="active"><i class="fas fa-users"></i> Create group chat</a></li>
            <li><a href="landlord_group_chats.php">💬 View My Group Chats</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <!-- Loading state -->
    <div id="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading your group chats...</p>
    </div>

    <!-- Error state -->
    <div id="error-state" class="error-state" style="display: none;">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Unable to Load Data</h3>
        <p id="error-message">Something went wrong. Please try again.</p>
        <button onclick="loadDashboardData()" class="retry-btn">
            <i class="fas fa-redo"></i> Retry
        </button>
    </div>

    <!-- Main content (hidden initially) -->
    <div id="main-content" style="display: none;">
        <div class="dashboard-header">
            <div class="welcome-section">
                <div class="avatar" id="landlord-avatar">L</div>
                <div class="welcome-text">
                    <h1 id="welcome-title">Welcome, Landlord</h1>
                    <p>Here's an overview of your group chats and activity.</p>
                </div>
            </div>
            <a class="create-btn" href="create_groupchat.php">
                <i class="fas fa-plus-circle"></i> Create New Group Chat
            </a>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-comments"></i>
                <div class="stat-value" id="total-group-chats">0</div>
                <div class="stat-label">Group Chats Created</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-value" id="total-tenants">0</div>
                <div class="stat-label">Unique Tenants Engaged</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-envelope"></i>
                <div class="stat-value" id="total-messages">0</div>
                <div class="stat-label">Total Messages Sent</div>
            </div>
        </div>

        <div class="summary">
            <div>
                <h2>Activity Summary</h2>
                <p class="summary-text" id="activity-summary">
                    Loading activity summary...
                </p>
            </div>
        </div>

        <div class="filter-sort">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search group chats..." id="search-chat">
            </div>
            <div class="sort-options">
                <button class="sort-btn active" data-sort="newest"><i class="fas fa-sort-amount-down"></i> Newest</button>
                <button class="sort-btn" data-sort="oldest"><i class="fas fa-sort-amount-up"></i> Oldest</button>
                <button class="sort-btn" data-sort="name"><i class="fas fa-sort-alpha-down"></i> Name</button>
            </div>
        </div>

        <div id="chat-grid-container"></div>
    </div>
</div>

<footer>
    &copy; <?= date('Y') ?> RentTracker. All rights reserved. | <a href="privacy.php">Privacy Policy</a> | <a href="terms.php">Terms of Service</a>
</footer>

<style>
.loading-state, .error-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.loading-state i {
    font-size: 3em;
    margin-bottom: 20px;
    color: #007bff;
}

.error-state i {
    font-size: 3em;
    margin-bottom: 20px;
    color: #dc3545;
}

.retry-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 20px;
}

.retry-btn:hover {
    background: #0056b3;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
const API_BASE_URL = 'https://rent-tracker-api.onrender.com'; 

const landlordId = <?= json_encode($landlord_id) ?>;

let dashboardData = null;
let filteredChats = [];

async function loadDashboardData() {
    try {
        document.getElementById('loading').style.display = 'block';
        document.getElementById('error-state').style.display = 'none';
        document.getElementById('main-content').style.display = 'none';

        const response = await fetch(`${API_BASE_URL}/landlord_dashboard_api.php?landlord_id=${landlordId}`);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to load dashboard data');
        }

        dashboardData = result.data;
        filteredChats = [...dashboardData.group_chats];
        
        populateDashboard();
        
        document.getElementById('loading').style.display = 'none';
        document.getElementById('main-content').style.display = 'block';
        document.getElementById('main-content').classList.add('fade-in');

    } catch (error) {
        console.error('Error loading dashboard:', error);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('error-state').style.display = 'block';
        document.getElementById('error-message').textContent = error.message;
    }
}

function populateDashboard() {
    const { landlord, group_chats, statistics } = dashboardData;
    
    // Update landlord info
    const avatar = document.getElementById('landlord-avatar');
    const welcomeTitle = document.getElementById('welcome-title');
    
    avatar.textContent = landlord.firstname ? landlord.firstname.charAt(0).toUpperCase() : 'L';
    welcomeTitle.textContent = `Welcome, Landlord ${landlord.firstname}`;
    
    // Update statistics
    document.getElementById('total-group-chats').textContent = statistics.total_group_chats;
    document.getElementById('total-tenants').textContent = statistics.total_tenants;
    document.getElementById('total-messages').textContent = statistics.total_messages;
    
    // Update activity summary
    const activitySummary = document.getElementById('activity-summary');
    const groupText = statistics.total_group_chats === 1 ? 'group chat' : 'group chats';
    const tenantText = statistics.total_tenants === 1 ? 'tenant' : 'tenants';
    const messageText = statistics.total_messages === 1 ? 'message' : 'messages';
    
    activitySummary.innerHTML = `
        ${landlord.firstname}, you've created <strong>${statistics.total_group_chats}</strong> ${groupText} 
        engaging <strong>${statistics.total_tenants}</strong> ${tenantText} and exchanged 
        <strong>${statistics.total_messages}</strong> ${messageText} in total.
    `;
    
    // Render group chats
    renderGroupChats(filteredChats);
}

function renderGroupChats(chats) {
    const container = document.getElementById('chat-grid-container');
    
    if (chats.length === 0 && filteredChats.length === 0) {
        // No chats at all
        container.innerHTML = `
            <div class="no-chats">
                <i class="fas fa-comments-alt"></i>
                <h3>No Group Chats Found</h3>
                <p>You haven't created any group chats yet. Get started by creating your first group chat.</p>
                <a class="create-btn" href="create_groupchat.php">
                    <i class="fas fa-plus-circle"></i> Create Your First Group Chat
                </a>
            </div>
        `;
        return;
    }
    
    if (chats.length === 0) {
        // No results from search/filter
        container.innerHTML = `
            <div class="no-chats">
                <i class="fas fa-search"></i>
                <h3>No Matching Chats Found</h3>
                <p>Try adjusting your search criteria.</p>
            </div>
        `;
        return;
    }
    
    const chatGrid = document.createElement('div');
    chatGrid.className = 'chat-grid';
    
    chats.forEach(chat => {
        const chatCard = document.createElement('div');
        chatCard.className = 'chat-card';
        chatCard.innerHTML = `
            <div class="chat-header">
                <div class="chat-title">${escapeHtml(chat.name)}</div>
                <div class="chat-date">Created: ${chat.formatted_date}</div>
                <div class="chat-badge">Active</div>
            </div>
            <div class="chat-content">
                <div class="chat-meta">
                    <i class="fas fa-hashtag"></i> Group ID: ${chat.id}
                </div>
                <div class="chat-stats">
                    <div class="chat-stat">
                        <div class="chat-stat-value">${chat.member_count}</div>
                        <div class="chat-stat-label">Members</div>
                    </div>
                    <div class="chat-stat">
                        <div class="chat-stat-value">${chat.message_count}</div>
                        <div class="chat-stat-label">Messages</div>
                    </div>
                </div>
                <div class="chat-actions">
                    <a class="chat-btn view-link" href="group_chat_landlord.php?group_id=${chat.id}">
                        <i class="fas fa-comments"></i> View Chat
                    </a>
                    <a class="chat-btn edit-link" href="edit_groupchat.php?group_id=${chat.id}">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        `;
        chatGrid.appendChild(chatCard);
    });
    
    container.innerHTML = '';
    container.appendChild(chatGrid);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function setupEventListeners() {
    // Search functionality
    const searchInput = document.getElementById('search-chat');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            if (!dashboardData) return;
            
            filteredChats = dashboardData.group_chats.filter(chat => {
                return chat.name.toLowerCase().includes(searchTerm) || 
                       chat.id.toString().includes(searchTerm);
            });
            
            renderGroupChats(filteredChats);
        });
    }
    
    // Sort buttons
    const sortButtons = document.querySelectorAll('.sort-btn');
    sortButtons.forEach(button => {
        button.addEventListener('click', function() {
            sortButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const sortType = this.dataset.sort;
            sortChats(sortType);
        });
    });
}

function sortChats(sortType) {
    if (!dashboardData) return;
    
    let sortedChats = [...filteredChats];
    
    switch(sortType) {
        case 'newest':
            sortedChats.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            break;
        case 'oldest':
            sortedChats.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            break;
        case 'name':
            sortedChats.sort((a, b) => a.name.localeCompare(b.name));
            break;
    }
    
    renderGroupChats(sortedChats);
}

function toggleMenu() {
    document.getElementById('main-nav').classList.toggle('active');
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadDashboardData();
});
</script>

</body>
</html>
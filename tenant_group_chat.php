<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ensure tenant is logged in
if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];

$api_base_url = 'https://rent-tracker-api.onrender.com'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Messages - RentTracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="tenant_group_chat.css">
    <style>
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .spinner {
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 3px solid rgba(0,0,0,.1);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 10px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .retry-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .retry-btn:hover {
            background-color: #0056b3;
        }
        #contentArea {
            min-height: 400px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <a href="index.php"><img src="./assets/logo.png" alt="RentTracker" style="height: 40px;"></a>
        </div>

        <!-- Hamburger Menu -->
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
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

    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-comments"></i> My Group Chats</h1>
            <p>Connect with your rental community and stay updated</p>
        </div>

        <!-- Content Area -->
        <div id="contentArea">
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading your group chats...</p>
            </div>
        </div>
    </div>

    <script>
        
        const tenantId = '<?php echo $tenant_id; ?>';
        const apiBaseUrl = '<?php echo $api_base_url; ?>';
        
        let groupsData = [];
        let currentFilter = 'all';
        let currentView = 'grid';
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
});

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadGroupChats();
        });

        async function loadGroupChats() {
            try {
                const response = await fetch(`${apiBaseUrl}/tenant_group_chats.php?tenant_id=${tenantId}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    groupsData = data.data.groups;
                    displayContent(data.data);
                } else {
                    displayError(data.error || 'Failed to load group chats');
                }
            } catch (error) {
                console.error('Error loading group chats:', error);
                displayError('Network error occurred. Please check your connection.');
            }
        }

        function displayContent(data) {
            const contentArea = document.getElementById('contentArea');
            
            if (data.groups.length === 0) {
                contentArea.innerHTML = `
                    <div class="no-groups">
                        <i class="fas fa-comments"></i>
                        <h3>No Group Chats Available</h3>
                        <p>You are not part of any accepted class yet. Join a class to access group chats!</p>
                        <a href="join_class.php" class="join-btn">
                            <i class="fas fa-user-plus"></i>
                            Join a Class
                        </a>
                    </div>
                `;
                return;
            }

            contentArea.innerHTML = `
                <!-- Controls Section -->
                <div class="controls-section">
                    <div class="search-filter-container">
                        <div class="search-box">
                            <input type="text" id="searchGroups" placeholder="Search groups..." onkeyup="filterGroups()">
                            <i class="fas fa-search"></i>
                        </div>
                        <select class="filter-dropdown" id="statusFilter" onchange="filterGroups()">
                            <option value="all">All Groups</option>
                            <option value="active">Active Groups</option>
                            <option value="inactive">Quiet Groups</option>
                        </select>
                        <div class="view-toggle">
                            <button class="view-btn active" onclick="toggleView('grid')">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="view-btn" onclick="toggleView('list')">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-container">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>${data.statistics.total_groups}</h3>
                        <p>Total Groups</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-comment-dots"></i>
                        <h3>${data.statistics.active_groups}</h3>
                        <p>Active Chats</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <h3>${data.statistics.last_activity_formatted}</h3>
                        <p>Last Activity</p>
                    </div>
                </div>

                <!-- Groups Container -->
                <div class="groups-container" id="groupsContainer">
                    ${renderGroupCards(data.groups)}
                </div>

                <!-- No Groups Found Message (hidden by default) -->
                <div class="no-groups" id="noGroupsMessage" style="display: none;">
                    <i class="fas fa-search"></i>
                    <h3>No groups found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                </div>
            `;
        }

        function renderGroupCards(groups) {
            return groups.map((group, index) => `
                <div class="group-card" data-status="${group.status}" style="animation-delay: ${index * 0.1}s;">
                    <div class="group-header">
                        <div class="group-info">
                            <h3>${escapeHtml(group.name)}</h3>
                            <p>Landlord: ${escapeHtml(group.landlord_name)}</p>
                        </div>
                        <div class="group-status ${group.status === 'active' ? 'status-active' : 'status-inactive'}">
                            <i class="fas fa-circle"></i>
                            ${group.status === 'active' ? 'Active' : 'Quiet'}
                        </div>
                    </div>
                    
                    <div class="group-stats">
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <span class="number">${group.active_members}</span>
                            <span class="label">Members</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-comments"></i>
                            <span class="number">${group.message_count}</span>
                            <span class="label">Messages</span>
                        </div>
                    </div>

                    <div class="last-activity">
                        <i class="fas fa-clock"></i>
                        Last message: ${group.last_activity_formatted}
                    </div>

                    <div class="action-buttons">
                        <a href="group_chat_room.php?group_id=${group.id}" class="btn btn-primary">
                            <i class="fas fa-comment"></i>
                            Open Chat
                        </a>
                        <button class="btn btn-secondary" onclick="viewGroupInfo(${group.id}, '${escapeHtml(group.name).replace(/'/g, "\\'")}')">
                            <i class="fas fa-info"></i>
                            Info
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function displayError(message) {
            const contentArea = document.getElementById('contentArea');
            contentArea.innerHTML = `
                <div class="error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Group Chats</h3>
                    <p>${message}</p>
                    <button class="retry-btn" onclick="loadGroupChats()">
                        <i class="fas fa-refresh"></i> Try Again
                    </button>
                </div>
            `;
        }

        // Search and Filter Functionality
        function filterGroups() {
            const searchTerm = document.getElementById('searchGroups').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const groupCards = document.querySelectorAll('.group-card');
            let visibleCount = 0;

            groupCards.forEach(card => {
                const groupName = card.querySelector('h3').textContent.toLowerCase();
                const landlordName = card.querySelector('.group-info p').textContent.toLowerCase();
                const groupStatus = card.getAttribute('data-status');
                
                const matchesSearch = groupName.includes(searchTerm) || landlordName.includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || groupStatus === statusFilter;
                
                if (matchesSearch && matchesStatus) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show/hide no groups message
            const noGroupsMessage = document.getElementById('noGroupsMessage');
            if (noGroupsMessage) {
                if (visibleCount === 0) {
                    noGroupsMessage.style.display = 'block';
                } else {
                    noGroupsMessage.style.display = 'none';
                }
            }
            
            currentFilter = statusFilter;
        }

        // View Toggle
        function toggleView(viewType) {
            const buttons = document.querySelectorAll('.view-btn');
            const container = document.getElementById('groupsContainer');
            
            if (!container) return;
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.closest('.view-btn').classList.add('active');
            
            if (viewType === 'list') {
                container.style.gridTemplateColumns = '1fr';
            } else {
                container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(350px, 1fr))';
            }
            
            currentView = viewType;
        }

        // Group Info Modal
        function viewGroupInfo(groupId, groupName) {
            const group = groupsData.find(g => g.id === groupId);
            if (group) {
                alert(`Group Information\n\nGroup: ${groupName}\nID: ${groupId}\nStatus: ${group.status}\nMembers: ${group.active_members}\nMessages: ${group.message_count}\nLast Activity: ${group.last_activity_formatted}\n\nThis would show detailed group information including:\n• Member list\n• Group settings\n• Recent activity\n• Join/leave options`);
            }
        }

        // Utility function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Auto-refresh functionality (optional)
        let refreshInterval;
        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                loadGroupChats();
            }, 30000); // Refresh every 30 seconds
        }

        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }

        // Start auto-refresh when page is visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });

        // Initialize auto-refresh
        startAutoRefresh();

        
    </script>
</body>
</html>
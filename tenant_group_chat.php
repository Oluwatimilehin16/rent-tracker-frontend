<?php
include 'config.php';
session_start();

// Ensure tenant is logged in
if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];

// Fetch class IDs the tenant belongs to
$tenant_class_ids = [];
$stmt = $conn->prepare("SELECT class_id FROM user_classes WHERE user_id = ?");
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tenant_class_ids[] = $row['class_id'];
}
$stmt->close();

if (empty($tenant_class_ids)) {
    // Show beautiful no groups message
    $no_groups = true;
    $groups_data = [];
} else {
    // Build placeholders for prepared statement
    $class_ids_placeholder = implode(",", array_fill(0, count($tenant_class_ids), '?'));

    // Simplified query without user_group_activity table
 $query = "
    SELECT gc.id, gc.name, u.lastname AS landlord_name,
        (SELECT COUNT(*) FROM group_chat_classes gcc_sub WHERE gcc_sub.group_id = gc.id) AS class_count,
        (SELECT COUNT(*) FROM group_chat_messages gcm WHERE gcm.group_id = gc.id) AS message_count,
        (SELECT COUNT(DISTINCT uc.user_id)
         FROM user_classes uc
         JOIN group_chat_classes gcc2 ON uc.class_id = gcc2.class_id
         WHERE gcc2.group_id = gc.id
        ) AS active_members,
        (SELECT MAX(gcm3.timestamp) FROM group_chat_messages gcm3 WHERE gcm3.group_id = gc.id) AS last_activity
    FROM group_chats gc
    JOIN users u ON gc.landlord_id = u.id
    JOIN group_chat_classes gcc ON gc.id = gcc.group_id
    WHERE gcc.class_id IN ($class_ids_placeholder)
    GROUP BY gc.id
    ORDER BY last_activity DESC
";



    $stmt = $conn->prepare($query);
    $types = str_repeat("i", count($tenant_class_ids));
    $stmt->bind_param($types, ...$tenant_class_ids);
    $stmt->execute();
    $groups = $stmt->get_result();
    
    $groups_data = [];
    while ($row = $groups->fetch_assoc()) {
        $groups_data[] = $row;
    }
    $no_groups = empty($groups_data);
}

// Calculate statistics
$total_groups = count($groups_data);
$active_groups = 0;
$last_activity_time = null;

foreach ($groups_data as $group) {
    if ($group['message_count'] > 0) {
        $active_groups++;
    }
    if ($group['last_activity'] && (!$last_activity_time || $group['last_activity'] > $last_activity_time)) {
        $last_activity_time = $group['last_activity'];
    }
}

// Function to format time difference
function timeAgo($datetime) {
    if (!$datetime) return 'No activity';

    $now = new DateTime('now', new DateTimeZone('UTC'));  // or your timezone
    $past = new DateTime($datetime, new DateTimeZone('UTC'));

    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}


$last_activity_formatted = $last_activity_time ? timeAgo($last_activity_time) : 'No activity';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Messages - RentTracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="tenant_group_chat.css">
</head>
<body>
    <!-- Header -->
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

    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-comments"></i> My Group Chats</h1>
            <p>Connect with your rental community and stay updated</p>
        </div>

        <?php if (!$no_groups): ?>
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
                    <h3><?= $total_groups ?></h3>
                    <p>Total Groups</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-comment-dots"></i>
                    <h3><?= $active_groups ?></h3>
                    <p>Active Chats</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3><?= $last_activity_formatted ?></h3>
                    <p>Last Activity</p>
                </div>
            </div>

            <!-- Groups Container -->
            <div class="groups-container" id="groupsContainer">
                <?php foreach ($groups_data as $index => $group): ?>
                    <div class="group-card" data-status="<?= $group['message_count'] > 0 ? 'active' : 'inactive' ?>" style="animation-delay: <?= $index * 0.1 ?>s;">
                        <div class="group-header">
                            <div class="group-info">
                                <h3><?= htmlspecialchars($group['name']) ?></h3>
                                <p>Landlord: <?= htmlspecialchars($group['landlord_name']) ?></p>
                            </div>
                            <div class="group-status <?= $group['message_count'] > 0 ? 'status-active' : 'status-inactive' ?>">
                                <i class="fas fa-circle"></i>
                                <?= $group['message_count'] > 0 ? 'Active' : 'Quiet' ?>
                            </div>
                        </div>
                        
                        <div class="group-stats">
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <span class="number"><?= $group['active_members'] ?: $group['class_count'] ?></span>
                                <span class="label">Members</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-comments"></i>
                                <span class="number"><?= $group['message_count'] ?></span>
                                <span class="label">Messages</span>
                            </div>
                        </div>

                        <div class="last-activity">
                            <i class="fas fa-clock"></i>
                            Last message: <?= timeAgo($group['last_activity']) ?>
                        </div>

                        <div class="action-buttons">
                            <a href="group_chat_room.php?group_id=<?= $group['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-comment"></i>
                                Open Chat
                            </a>
                            <button class="btn btn-secondary" onclick="viewGroupInfo(<?= $group['id'] ?>, '<?= htmlspecialchars($group['name'], ENT_QUOTES) ?>')">
                                <i class="fas fa-info"></i>
                                Info
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- No Groups Found Message (hidden by default) -->
            <div class="no-groups" id="noGroupsMessage" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>No groups found</h3>
                <p>Try adjusting your search or filter criteria</p>
            </div>

        <?php else: ?>
            <!-- No Groups Available -->
            <div class="no-groups">
                <i class="fas fa-comments"></i>
                <h3>No Group Chats Available</h3>
                <p>You are not part of any accepted class yet. Join a class to access group chats!</p>
                <a href="join_class.php" class="join-btn">
                    <i class="fas fa-user-plus"></i>
                    Join a Class
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
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
        }

        // View Toggle
        function toggleView(viewType) {
            const buttons = document.querySelectorAll('.view-btn');
            const container = document.getElementById('groupsContainer');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.closest('.view-btn').classList.add('active');
            
            if (viewType === 'list') {
                container.style.gridTemplateColumns = '1fr';
            } else {
                container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(350px, 1fr))';
            }
        }

        // Group Info Modal
        function viewGroupInfo(groupId, groupName) {
            alert(`Group Information\n\nGroup: ${groupName}\nID: ${groupId}\n\nThis would show detailed group information including:\n• Member list\n• Group settings\n• Recent activity\n• Join/leave options`);
        }

        // Initialize page animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add staggered animations to cards
            const cards = document.querySelectorAll('.group-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>
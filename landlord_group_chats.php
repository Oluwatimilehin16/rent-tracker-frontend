<?php
include 'config.php';
session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];

// Get landlord details
$landlord = null;
$landlord_query = "SELECT firstname FROM users WHERE id = ?";
$stmt = $conn->prepare($landlord_query);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $landlord = $result->fetch_assoc();
}

// Get group chats
$group_query = "SELECT id, name, created_at FROM group_chats WHERE landlord_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($group_query);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$group_chats_result = $stmt->get_result();
$total_group_chats = $group_chats_result->num_rows;

// Get total tenants across all landlord's classes
$tenant_query = "
    SELECT COUNT(DISTINCT uc.user_id) AS tenant_count
    FROM group_chat_classes gcc
    JOIN user_classes uc ON gcc.class_id = uc.class_id
    JOIN group_chats gc ON gcc.group_id = gc.id
    WHERE gc.landlord_id = ?
";
$stmt = $conn->prepare($tenant_query);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$tenant_result = $stmt->get_result();
$tenant_data = $tenant_result->fetch_assoc();
$total_tenants = $tenant_data['tenant_count'] ?? 0;

// Get total messages in landlord’s group chats
$message_query = "
    SELECT COUNT(*) AS message_count
    FROM group_chat_messages gcm
    JOIN group_chats gc ON gcm.group_id = gc.id
    WHERE gc.landlord_id = ?
";
$stmt = $conn->prepare($message_query);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$message_result = $stmt->get_result();
$message_data = $message_result->fetch_assoc();
$total_messages = $message_data['message_count'] ?? 0;
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

<div class="container">
    <div class="dashboard-header">
        <div class="welcome-section">
            <div class="avatar">
                <?= isset($landlord['firstname']) ? strtoupper(substr($landlord['firstname'], 0, 1)) : 'L' ?>
            </div>
            <div class="welcome-text">
                <h1>Welcome, Landlord <?= htmlspecialchars($landlord['firstname']) ?></h1>
                <p>Here’s an overview of your group chats and activity.</p>
            </div>
        </div>
        <a class="create-btn" href="create_groupchat.php">
            <i class="fas fa-plus-circle"></i> Create New Group Chat
        </a>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <i class="fas fa-comments"></i>
            <div class="stat-value"><?= $total_group_chats ?></div>
            <div class="stat-label">Group Chats Created</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="stat-value"><?= $total_tenants ?></div>
            <div class="stat-label">Unique Tenants Engaged</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-envelope"></i>
            <div class="stat-value"><?= $total_messages ?></div>
            <div class="stat-label">Total Messages Sent</div>
        </div>
    </div>

    <div class="summary">
        <div>
            <h2>Activity Summary</h2>
            <p class="summary-text">
                <?= htmlspecialchars($landlord['firstname']) ?>, you've created <strong><?= $total_group_chats ?></strong> group chat<?= $total_group_chats == 1 ? '' : 's' ?> 
                engaging <strong><?= $total_tenants ?></strong> tenant<?= $total_tenants == 1 ? '' : 's' ?> and exchanged 
                <strong><?= $total_messages ?></strong> message<?= $total_messages == 1 ? '' : 's' ?> in total.
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

    <?php if ($total_group_chats > 0): ?>
        <div class="chat-grid">
            <?php while ($row = $group_chats_result->fetch_assoc()): ?>
                <?php
                    $group_id = $row['id'];

                    // Get members count
                    $member_stmt = $conn->prepare("
                        SELECT COUNT(DISTINCT uc.user_id) AS member_count
                        FROM group_chat_classes gcc
                        JOIN user_classes uc ON gcc.class_id = uc.class_id
                        WHERE gcc.group_id = ?
                    ");
                    $member_stmt->bind_param("i", $group_id);
                    $member_stmt->execute();
                    $member_result = $member_stmt->get_result();
                    $member_count = $member_result->fetch_assoc()['member_count'] ?? 0;

                    // Get message count
                    $message_stmt = $conn->prepare("
                        SELECT COUNT(*) AS msg_count
                        FROM group_chat_messages
                        WHERE group_id = ?
                    ");
                    $message_stmt->bind_param("i", $group_id);
                    $message_stmt->execute();
                    $msg_result = $message_stmt->get_result();
                    $message_count = $msg_result->fetch_assoc()['msg_count'] ?? 0;
                ?>

                <div class="chat-card">
                    <div class="chat-header">
                        <div class="chat-title"><?= htmlspecialchars($row['name']) ?></div>
                        <div class="chat-date">Created: <?= date('M d, Y', strtotime($row['created_at'])) ?></div>
                        <div class="chat-badge">Active</div>
                    </div>
                    <div class="chat-content">
                        <div class="chat-meta">
                            <i class="fas fa-hashtag"></i> Group ID: <?= $row['id'] ?>
                        </div>
                        <div class="chat-stats">
                            <div class="chat-stat">
                                <div class="chat-stat-value"><?= $member_count ?></div>
                                <div class="chat-stat-label">Members</div>
                            </div>
                            <div class="chat-stat">
                                <div class="chat-stat-value"><?= $message_count ?></div>
                                <div class="chat-stat-label">Messages</div>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <a class="chat-btn view-link" href="group_chat_landlord.php?group_id=<?= $row['id'] ?>">
                                <i class="fas fa-comments"></i> View Chat
                            </a>
                            <a class="chat-btn edit-link" href="edit_groupchat.php?group_id=<?= $row['id'] ?>">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-chats">
            <i class="fas fa-comments-alt"></i>
            <h3>No Group Chats Found</h3>
            <p>You haven't created any group chats yet. Get started by creating your first group chat.</p>
            <a class="create-btn" href="create_groupchat.php">
                <i class="fas fa-plus-circle"></i> Create Your First Group Chat
            </a>
        </div>
    <?php endif; ?>
</div>

<footer>
    &copy; <?= date('Y') ?> RentTracker. All rights reserved. | <a href="privacy.php">Privacy Policy</a> | <a href="terms.php">Terms of Service</a>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('search-chat');
    const chatCards = document.querySelectorAll('.chat-card');
    
    if(searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            chatCards.forEach(card => {
                const title = card.querySelector('.chat-title').textContent.toLowerCase();
                const id = card.querySelector('.chat-meta').textContent.toLowerCase();
                
                if(title.includes(searchTerm) || id.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Sort buttons (functionality to be added later)
    const sortButtons = document.querySelectorAll('.sort-btn');
    sortButtons.forEach(button => {
        button.addEventListener('click', function() {
            sortButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            alert("Sorting by " + this.dataset.sort + " is under development.");
        });
    });
});
</script>

</body>
</html>

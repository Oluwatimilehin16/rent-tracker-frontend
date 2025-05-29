<?php
include 'config.php';
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

// Verify that this group belongs to the current landlord
$verify_query = "SELECT id, name FROM group_chats WHERE id = ? AND landlord_id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $group_id, $landlord_id);
$stmt->execute();
$verify_result = $stmt->get_result();

if ($verify_result->num_rows === 0) {
    header("Location: landlord_group_chats.php");
    exit();
}

$group_chat = $verify_result->fetch_assoc();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_group'])) {
        // Delete group chat and all related data
        $conn->begin_transaction();
        
        try {
            // Delete group chat messages
            $delete_messages = "DELETE FROM group_chat_messages WHERE group_id = ?";
            $stmt = $conn->prepare($delete_messages);
            $stmt->bind_param("i", $group_id);
            $stmt->execute();
            
            // Delete group chat classes associations
            $delete_classes = "DELETE FROM group_chat_classes WHERE group_id = ?";
            $stmt = $conn->prepare($delete_classes);
            $stmt->bind_param("i", $group_id);
            $stmt->execute();
            
            // Delete the group chat itself
            $delete_group = "DELETE FROM group_chats WHERE id = ?";
            $stmt = $conn->prepare($delete_group);
            $stmt->bind_param("i", $group_id);
            $stmt->execute();
            
            $conn->commit();
            header("Location: landlord_group_chats.php?deleted=1");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error deleting group chat: " . $e->getMessage();
            $message_type = "error";
        }
    }
    
    if (isset($_POST['add_member'])) {
        $class_id = $_POST['class_id'];
        
        // Check if class already exists in this group
        $check_existing = "SELECT id FROM group_chat_classes WHERE group_id = ? AND class_id = ?";
        $stmt = $conn->prepare($check_existing);
        $stmt->bind_param("ii", $group_id, $class_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $message = "This class is already added to the group chat.";
            $message_type = "error";
        } else {
            // Add class to group chat
            $add_class = "INSERT INTO group_chat_classes (group_id, class_id) VALUES (?, ?)";
            $stmt = $conn->prepare($add_class);
            $stmt->bind_param("ii", $group_id, $class_id);
            
            if ($stmt->execute()) {
                $message = "Class added to group chat successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding class to group chat.";
                $message_type = "error";
            }
        }
    }
    
    if (isset($_POST['remove_class'])) {
        $class_id = $_POST['class_id'];
        
        // Remove class from group chat
        $remove_class = "DELETE FROM group_chat_classes WHERE group_id = ? AND class_id = ?";
        $stmt = $conn->prepare($remove_class);
        $stmt->bind_param("ii", $group_id, $class_id);
        
        if ($stmt->execute()) {
            $message = "Class removed from group chat successfully!";
            $message_type = "success";
        } else {
            $message = "Error removing class from group chat.";
            $message_type = "error";
        }
    }
}

// Get current members (classes) in this group
$current_members_query = "
    SELECT gcc.class_id, c.class_name, COUNT(uc.user_id) as member_count
    FROM group_chat_classes gcc
    JOIN classes c ON gcc.class_id = c.id
    LEFT JOIN user_classes uc ON c.id = uc.class_id
    WHERE gcc.group_id = ?
    GROUP BY gcc.class_id, c.class_name
    ORDER BY c.class_name
";
$stmt = $conn->prepare($current_members_query);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$current_members = $stmt->get_result();

// Get available classes that can be added (landlord's classes not yet in this group)
$available_classes_query = "
    SELECT c.id, c.class_name, COUNT(uc.user_id) as member_count
    FROM classes c
    LEFT JOIN user_classes uc ON c.id = uc.class_id
    WHERE c.landlord_id = ? 
    AND c.id NOT IN (
        SELECT class_id FROM group_chat_classes WHERE group_id = ?
    )
    GROUP BY c.id, c.class_name
    ORDER BY c.class_name
";
$stmt = $conn->prepare($available_classes_query);
$stmt->bind_param("ii", $landlord_id, $group_id);
$stmt->execute();
$available_classes = $stmt->get_result();

// Get group stats
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM group_chat_messages WHERE group_id = ?) as message_count,
        (SELECT COUNT(DISTINCT uc.user_id) 
         FROM group_chat_classes gcc 
         JOIN user_classes uc ON gcc.class_id = uc.class_id 
         WHERE gcc.group_id = ?) as total_members
";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("ii", $group_id, $group_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
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
        <p>Manage members and settings for "<?= htmlspecialchars($group_chat['name']) ?>"</p>
    </div>

    <div class="content">
        <div class="nav-breadcrumb">
            <i class="fas fa-home"></i>
            <a href="landlord_group_chats.php">My Group Chats</a> > 
            <span>Edit "<?= htmlspecialchars($group_chat['name']) ?>"</span>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-value"><?= $stats['total_members'] ?></div>
                <div class="stat-label">Total Members</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-envelope"></i>
                <div class="stat-value"><?= $stats['message_count'] ?></div>
                <div class="stat-label">Messages Sent</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-layer-group"></i>
                <div class="stat-value"><?= $current_members->num_rows ?></div>
                <div class="stat-label">Classes Added</div>
            </div>
        </div>

        <div class="section">
            <h2><i class="fas fa-users"></i> Current Members</h2>
            <?php if ($current_members->num_rows > 0): ?>
                <div class="member-list">
                    <?php 
                    $current_members->data_seek(0); // Reset result pointer
                    while ($member = $current_members->fetch_assoc()): 
                    ?>
                        <div class="member-item">
                            <div class="member-info">
                                <h4><?= htmlspecialchars($member['class_name']) ?></h4>
                                <p><i class="fas fa-users"></i> <?= $member['member_count'] ?> tenant<?= $member['member_count'] != 1 ? 's' : '' ?> in this class</p>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="class_id" value="<?= $member['class_id'] ?>">
                                <button type="submit" name="remove_class" class="btn btn-danger btn-small"
                                        onclick="return confirm('Are you sure you want to remove this class from the group chat?')">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-classes">
                    <i class="fas fa-users-slash"></i>
                    <h3>No Classes Added</h3>
                    <p>This group chat doesn't have any classes added yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2><i class="fas fa-user-plus"></i> Add New Members</h2>
            <?php if ($available_classes->num_rows > 0): ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="class_id">Select a class to add to this group chat:</label>
                        <select name="class_id" id="class_id" required>
                            <option value="">Choose a class...</option>
                            <?php while ($class = $available_classes->fetch_assoc()): ?>
                                <option value="<?= $class['id'] ?>">
                                    <?= htmlspecialchars($class['class_name']) ?> 
                                    (<?= $class['member_count'] ?> tenant<?= $class['member_count'] != 1 ? 's' : '' ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_member" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Class to Group Chat
                    </button>
                </form>
            <?php else: ?>
                <div class="no-classes">
                    <i class="fas fa-info-circle"></i>
                    <h3>No Available Classes</h3>
                    <p>All your classes have been added to this group chat, or you don't have any other classes to add.</p>
                    <a href="create_class.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Class
                    </a>
                </div>
            <?php endif; ?>
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

<footer style="background-color: #218838; color: white; text-align: center; padding: 20px; margin-top: auto;">
    &copy; <?= date('Y') ?> RentTracker. All rights reserved. | <a href="privacy.php" style="color: #ecc700;">Privacy Policy</a> | <a href="terms.php" style="color: #ecc700;">Terms of Service</a>
</footer>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
        <p>Are you absolutely sure you want to delete the group chat "<strong><?= htmlspecialchars($group_chat['name']) ?></strong>"?</p>
        <p style="color: #dc3545; font-weight: bold;">This action cannot be undone!</p>
        
        <div class="modal-buttons">
            <button type="button" class="btn btn-secondary" onclick="hideDeleteModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <form method="POST" style="display: inline;">
                <button type="submit" name="delete_group" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Yes, Delete Group Chat
                </button>
            </form>
        </div>
    </div>
</div>

<script>
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
</script>

</body>
</html>
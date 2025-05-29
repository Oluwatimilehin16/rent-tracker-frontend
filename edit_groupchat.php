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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .nav-breadcrumb {
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .nav-breadcrumb a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .nav-breadcrumb a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .member-list {
            display: grid;
            gap: 15px;
        }

        .member-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .member-info h4 {
            color: #333;
            margin-bottom: 5px;
        }

        .member-info p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group select:focus {
            outline: none;
            border-color: #007bff;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 25px;
            background: #fff5f5;
        }

        .danger-zone h3 {
            color: #dc3545;
            margin-bottom: 15px;
        }

        .no-classes {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .no-classes i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ddd;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }

        .modal-content h3 {
            color: #dc3545;
            margin-bottom: 20px;
        }

        .modal-buttons {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .member-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .modal-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

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
</script>

</body>
</html>
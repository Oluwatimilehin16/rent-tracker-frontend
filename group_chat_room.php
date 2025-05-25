<?php
include 'config.php';
session_start();

$group_id = intval($_GET['group_id'] ?? 0);

// Determine if user is tenant or landlord
if (isset($_SESSION['tenant_id'])) {
    $user_id = $_SESSION['tenant_id'];
    $user_role = 'tenant';

    // ✅ Check if tenant has access to this group
    $check_access = mysqli_query($conn, "
        SELECT gcc.group_id 
        FROM group_chat_classes gcc
        JOIN user_classes uc ON gcc.class_id = uc.class_id
        WHERE uc.user_id = '$user_id' AND gcc.group_id = '$group_id'
    ");
} elseif (isset($_SESSION['landlord_id'])) {
    $user_id = $_SESSION['landlord_id'];
    $user_role = 'landlord';

    // ✅ Check if landlord owns the group
    $check_access = mysqli_query($conn, "
        SELECT id FROM group_chats 
        WHERE id = '$group_id' AND landlord_id = '$user_id'
    ");
} else {
    header("Location: login.php");
    exit();
}

if (mysqli_num_rows($check_access) == 0) {
    echo "Access denied.";
    exit();
}

// ✅ Fetch group chat name
$group = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM group_chats WHERE id = '$group_id'"));

// ✅ Fetch user name
$name_result = mysqli_query($conn, "SELECT firstname FROM users WHERE id = '$user_id'");
$user_name = mysqli_fetch_assoc($name_result)['firstname'] ?? 'Unknown';

// ✅ Fetch group members (same query as landlord logic)
$members_query = mysqli_query($conn, "
    SELECT u.id, u.firstname, u.email, 'tenant' as role 
    FROM users u 
    JOIN user_classes uc ON u.id = uc.user_id
    JOIN group_chat_classes gcc ON uc.class_id = gcc.class_id
    WHERE gcc.group_id = '$group_id'

    UNION

    SELECT u.id, u.firstname, u.email, 'landlord' as role
    FROM users u
    JOIN group_chats gc ON u.id = gc.landlord_id
    WHERE gc.id = '$group_id'
");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($group['name']) ?> - Tenant Group Chat</title>

    <link rel="stylesheet" href="group_chat_landlord.css"> <!-- Use the CSS you pasted -->
    <script src="https://cdn.socket.io/4.0.1/socket.io.min.js"></script>
</head>
<body>
    <div class="connection-status connecting" id="connectionStatus">Connecting...</div>
<div class="chat-container">
<div class="toggle-sidebar-btn">
        <button class="header-btn" onclick="toggleSidebar()" id="toggleBtn">Hide</button>
    </div>

    <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span>Group Members</span>
    </div>

         <div class="member-list" id="memberList">
            <?php while ($member = mysqli_fetch_assoc($members_query)): ?>
            <div class="member-item" data-user-id="<?php echo $member['id']; ?>" data-role="<?php echo $member['role']; ?>">
                <div class="member-avatar">
                    <?php echo strtoupper(substr($member['firstname'], 0, 1)); ?>
                </div>
                <div class="member-info">
                    <div class="member-name"><?php echo htmlspecialchars($member['firstname']); ?></div>
                    <div class="member-role"><?php echo $member['role']; ?></div>
                </div>
                <div class="online-indicator" style="display: none;"></div>
            </div>
            <?php endwhile; ?>
        </div>

         <div class="sidebar-footer">
        <a href="tenant_group_chat.php" class="dashboard-button">← Back to Dashboard</a>
    </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-wrapper">
        <div class="chat-header">
            <div>
                <span><?php echo htmlspecialchars($group['name']); ?></span>
                <div style="font-size: 12px; font-weight: normal; opacity: 0.8;" id="onlineCount">
                    Loading members...
                </div>
            </div>
            <div class="header-actions">
                <button class="header-btn" onclick="toggleSearch()">Search</button>
                <button class="header-btn" onclick="clearChat()">Clear</button>
            </div>
        </div>

        <div class="search-container hidden" id="searchContainer">
            <div style="position: relative;">
                <input type="text" class="search-input" id="searchInput" placeholder="Search messages..." autocomplete="off">
                <div class="search-results" id="searchResults"></div>
            </div>
        </div>

        <div id="chat-box"></div>
        
        <div class="typing-indicator hidden" id="typingIndicator"></div>

        <div class="input-area">
            <div class="input-wrapper">
                <textarea id="messageInput" placeholder="Type your message..." rows="1" autofocus></textarea>
            </div>
            <button class="send-btn" onclick="sendMessage()" id="sendBtn">
                <span>Send</span>
            </button>
        </div>
    </div>
</div>

<!-- ✅ Socket.IO -->
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
    const socket = io("http://localhost:3000"); // Update this for production
    const groupId = <?php echo $group_id; ?>;
    const senderId = <?php echo $user_id; ?>;
    const senderName = <?php echo json_encode($user_name); ?>;
    const senderRole = "<?php echo $user_role; ?>";

    
    const chatBox = document.getElementById("chat-box");
    const messageInput = document.getElementById("messageInput");
    const sendBtn = document.getElementById("sendBtn");
    const typingIndicator = document.getElementById("typingIndicator");
    const connectionStatus = document.getElementById("connectionStatus");
    const searchContainer = document.getElementById("searchContainer");
    const searchInput = document.getElementById("searchInput");
    const searchResults = document.getElementById("searchResults");

    let isTyping = false;
    let typingTimeout;
    let allMessages = [];
    let lastMessageSender = null;
    let lastMessageTime = null;

    // ✅ Connection status
    socket.on("connect", () => {
        connectionStatus.textContent = "Connected";
        connectionStatus.className = "connection-status connected";
        setTimeout(() => connectionStatus.style.display = "none", 3000);
    });

    socket.on("disconnect", () => {
        connectionStatus.textContent = "Disconnected";
        connectionStatus.className = "connection-status disconnected";
        connectionStatus.style.display = "block";
    });

    // ✅ Join group
    socket.emit("join-group", groupId);

    // ✅ Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        
        // Typing indicator
        if (!isTyping) {
            isTyping = true;
            socket.emit("typing-start", { group_id: groupId, sender_name: senderName });
        }
        
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            isTyping = false;
            socket.emit("typing-stop", { group_id: groupId });
        }, 1000);
    });

    // ✅ Send on Enter (without Shift)
    messageInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // ✅ Send message
    function sendMessage() {
        const msg = messageInput.value.trim();
        if (!msg) return;

        sendBtn.disabled = true;
        
        socket.emit("send-group-message", {
            group_id: groupId,
            sender_id: senderId,
            sender_name: senderName,
            sender_role: senderRole,
            message: msg
        });

        messageInput.value = "";
        messageInput.style.height = 'auto';
        
        // Stop typing indicator
        if (isTyping) {
            isTyping = false;
            socket.emit("typing-stop", { group_id: groupId });
        }
        
        setTimeout(() => sendBtn.disabled = false, 500);
    }

    // ✅ Format time
    function formatLocalTime(utcString) {
        const date = new Date(utcString);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);
        
        if (diffInHours < 24) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } else {
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + 
                   ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    }

    // ✅ Group consecutive messages
    function shouldGroupMessage(data) {
        if (!lastMessageSender) return false;
        
        const currentTime = new Date(data.timestamp);
        const timeDiff = (currentTime - lastMessageTime) / (1000 * 60); // minutes
        
        return lastMessageSender === data.sender_id && timeDiff < 5;
    }

    // ✅ Append message with grouping
    function appendMessage(data) {
        allMessages.push(data);
        
        const isMe = data.sender_id == senderId;
        const isGrouped = shouldGroupMessage(data);
        const time = formatLocalTime(data.timestamp);

        if (!isGrouped) {
            // Create new message group
            const groupDiv = document.createElement("div");
            groupDiv.classList.add("message-group");
            groupDiv.setAttribute("data-sender", data.sender_id);
            chatBox.appendChild(groupDiv);
        }

        const messageDiv = document.createElement("div");
        messageDiv.classList.add("message");
        if (isMe) messageDiv.classList.add("me");

        if (isMe) {
            messageDiv.innerHTML = `
                <div class="me-bubble">
                    <div class="message-content">${data.message}</div>
                    <div class="timestamp">${time} <span class="message-status">✓</span></div>
                </div>
            `;
        } else {
            const showName = !isGrouped;
            messageDiv.innerHTML = `
                <div class="other-bubble">
                    ${showName ? `<div class="sender-name">${data.sender_name || "Someone"}</div>` : ''}
                    <div class="message-content">${data.message}</div>
                    <div class="timestamp">${time}</div>
                </div>
            `;
        }

        // Add to the last message group or create new one
        const lastGroup = chatBox.lastElementChild;
        if (lastGroup && lastGroup.getAttribute("data-sender") === data.sender_id.toString() && isGrouped) {
            lastGroup.appendChild(messageDiv);
        } else {
            const newGroup = document.createElement("div");
            newGroup.classList.add("message-group");
            newGroup.setAttribute("data-sender", data.sender_id);
            newGroup.appendChild(messageDiv);
            chatBox.appendChild(newGroup);
        }

        lastMessageSender = data.sender_id;
        lastMessageTime = new Date(data.timestamp);
        
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // ✅ Typing indicators
    socket.on("user-typing", (data) => {
        if (data.sender_name !== senderName) {
            typingIndicator.textContent = `${data.sender_name} is typing...`;
            typingIndicator.classList.remove("hidden");
        }
    });

    socket.on("user-stopped-typing", () => {
        typingIndicator.classList.add("hidden");
    });

    // ✅ Search functionality
    function toggleSearch() {
        searchContainer.classList.toggle("hidden");
        if (!searchContainer.classList.contains("hidden")) {
            searchInput.focus();
        } else {
            searchInput.value = "";
            searchResults.style.display = "none";
        }
    }

    searchInput.addEventListener("input", function() {
        const query = this.value.toLowerCase().trim();
        searchResults.innerHTML = "";
        
        if (query.length < 2) {
            searchResults.style.display = "none";
            return;
        }

        const results = allMessages.filter(msg => 
            msg.message.toLowerCase().includes(query) || 
            msg.sender_name.toLowerCase().includes(query)
        ).slice(0, 10);

        if (results.length > 0) {
            searchResults.style.display = "block";
            results.forEach(result => {
                const div = document.createElement("div");
                div.classList.add("search-result-item");
                div.innerHTML = `
                    <strong>${result.sender_name}</strong>: ${result.message.substring(0, 50)}...
                    <div style="font-size: 0.8em; color: #666;">${formatLocalTime(result.timestamp)}</div>
                `;
                div.addEventListener("click", () => {
                    // Highlight the message (you can implement scrolling to message here)
                    searchResults.style.display = "none";
                    searchInput.value = "";
                });
                searchResults.appendChild(div);
            });
        } else {
            searchResults.style.display = "none";
        }
    });

    // ✅ Clear chat (visual only)
    function clearChat() {
        if (confirm("Clear chat history? (This only clears your view, not the actual messages)")) {
            chatBox.innerHTML = "";
            allMessages = [];
            lastMessageSender = null;
            lastMessageTime = null;
        }
    }

    // ✅ Toggle sidebar
    function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleBtn");

    // Toggle "hidden" class instead of manually setting display styles
    sidebar.classList.toggle("hidden");
    toggleBtn.textContent = sidebar.classList.contains("hidden") ? "Show" : "Hide";
}


    // ✅ Load past messages
    window.onload = function () {
        fetch(`fetch_group_messages.php?group_id=${groupId}`)
            .then(response => response.json())
            .then(messages => {
                messages.forEach(appendMessage);
                chatBox.scrollTop = chatBox.scrollHeight;
                
                // Update online count
                document.getElementById("onlineCount").textContent = 
                    `${document.querySelectorAll('.member-item').length} members`;
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                chatBox.innerHTML = '<div class="text-center text-muted">Failed to load messages</div>';
            });
    };

    // ✅ Real-time message receive
    socket.on("group-message", data => {
        appendMessage(data);
    });

    // ✅ Handle window resize
    window.addEventListener("resize", function() {
        if (window.innerWidth > 768) {
            document.getElementById("sidebar").classList.remove("hidden");
            document.getElementById("sidebar").style.display = "flex";
        }
    });

    // ✅ Click outside to close search
    document.addEventListener("click", function(e) {
        if (!searchContainer.contains(e.target)) {
            searchResults.style.display = "none";
        }
    });
</script>
</body>
</html>

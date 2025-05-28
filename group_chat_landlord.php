<?php
include 'config.php';
session_start();

// ✅ Ensure landlord is logged in
if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$landlord_name = $_SESSION['landlord_name']; // Make sure this is stored at login
$group_id = intval($_GET['group_id'] ?? 0);

// ✅ Verify landlord owns this group chat
$check_access = mysqli_query($conn, "
    SELECT name FROM group_chats 
    WHERE id = '$group_id' AND landlord_id = '$landlord_id'
");

if (mysqli_num_rows($check_access) == 0) {
    echo "Access denied.";
    exit();
}

$group = mysqli_fetch_assoc($check_access);

// ✅ Get group members from 'users' table (no need for a separate landlords table)
$members_query = mysqli_query($conn, "
    SELECT u.id, u.firstname, u.email, 'tenant' as role 
    FROM users u 
    JOIN user_classes uc ON u.id = uc.user_id
    JOIN group_chat_classes gcc ON uc.class_id = gcc.class_id
    WHERE gcc.group_id = '$group_id'

    UNION

    SELECT u.id, u.firstname, u.email, 'landlord' as role
    FROM users u
    WHERE u.id = '$landlord_id'
");

?>



<!DOCTYPE html>
<html>
<head>
    <title>Group Chat (Landlord): <?php echo htmlspecialchars($group['name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="group_chat_landlord.css">
</head>
<body>

<div class="connection-status connecting" id="connectionStatus">Connecting...</div>

<div class="chat-container">
    <!-- Sidebar -->
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
        <a href="landlord_group_chats.php" class="dashboard-button">← Back to Dashboard</a>
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
    const socket = io("https://rent-tracker-backend.onrender.com");

    const groupId = <?php echo $group_id; ?>;
    const senderId = <?php echo $landlord_id; ?>;
    const senderName = <?php echo json_encode($landlord_name); ?>;
    const senderRole = "landlord";

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

    // ✅ Step 1: Confirm connection
    socket.on("connect", () => {
        console.log("✅ Connected to Socket.IO server");
        connectionStatus.textContent = "Connected";
        connectionStatus.className = "connection-status connected";
        setTimeout(() => connectionStatus.style.display = "none", 3000);
    });

    socket.on("disconnect", () => {
        console.log("⚠️ Disconnected from Socket.IO server");
        connectionStatus.textContent = "Disconnected";
        connectionStatus.className = "connection-status disconnected";
        connectionStatus.style.display = "block";
    });

    // ✅ Step 2: Join group
    socket.emit("join-group", groupId);
    console.log("📡 Joining group:", groupId);

    // ✅ Typing indicator
    messageInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';

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

    messageInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // ✅ Step 3: Send message
    function sendMessage() {
        const msg = messageInput.value.trim();
        if (!msg) return;

        console.log("📤 Sending message:", msg);
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

        if (isTyping) {
            isTyping = false;
            socket.emit("typing-stop", { group_id: groupId });
        }

        setTimeout(() => sendBtn.disabled = false, 500);
    }

    // ✅ Format timestamp
    function formatLocalTime(utcString) {
        const date = new Date(utcString);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);

        return diffInHours < 24
            ? date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            : date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + 
              ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function shouldGroupMessage(data) {
        if (!lastMessageSender) return false;

        const currentTime = new Date(data.timestamp);
        const timeDiff = (currentTime - lastMessageTime) / (1000 * 60);
        return lastMessageSender === data.sender_id && timeDiff < 5;
    }

    function appendMessage(data) {
        console.log("📥 Received message:", data);
        allMessages.push(data);

        const isMe = data.sender_id == senderId;
        const isGrouped = shouldGroupMessage(data);
        const time = formatLocalTime(data.timestamp);

        if (!isGrouped) {
            const groupDiv = document.createElement("div");
            groupDiv.classList.add("message-group");
            groupDiv.setAttribute("data-sender", data.sender_id);
            chatBox.appendChild(groupDiv);
        }

        const messageDiv = document.createElement("div");
        messageDiv.classList.add("message");
        if (isMe) messageDiv.classList.add("me");

        messageDiv.innerHTML = isMe
            ? `<div class="me-bubble">
                   <div class="message-content">${data.message}</div>
                   <div class="timestamp">${time} <span class="message-status">✓</span></div>
               </div>`
            : `<div class="other-bubble">
                   ${!isGrouped ? `<div class="sender-name">${data.sender_name || "Someone"}</div>` : ''}
                   <div class="message-content">${data.message}</div>
                   <div class="timestamp">${time}</div>
               </div>`;

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

    // ✅ Step 4: Receive message
    socket.on("group-message", data => {
        appendMessage(data);
    });

    socket.on("user-typing", (data) => {
        if (data.sender_name !== senderName) {
            typingIndicator.textContent = `${data.sender_name} is typing...`;
            typingIndicator.classList.remove("hidden");
        }
    });

    socket.on("user-stopped-typing", () => {
        typingIndicator.classList.add("hidden");
    });

    function toggleSearch() {
        searchContainer.classList.toggle("hidden");
        if (!searchContainer.classList.contains("hidden")) {
            searchInput.focus();
        } else {
            searchInput.value = "";
            searchResults.style.display = "none";
        }
    }

    searchInput.addEventListener("input", function () {
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
                    searchResults.style.display = "none";
                    searchInput.value = "";
                });
                searchResults.appendChild(div);
            });
        } else {
            searchResults.style.display = "none";
        }
    });

    function clearChat() {
        if (confirm("Clear chat history? (This only clears your view, not the actual messages)")) {
            chatBox.innerHTML = "";
            allMessages = [];
            lastMessageSender = null;
            lastMessageTime = null;
        }
    }

    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("toggleBtn");
        sidebar.classList.toggle("hidden");
        toggleBtn.textContent = sidebar.classList.contains("hidden") ? "Show" : "Hide";
    }

    // ✅ Step 5: Load messages from DB
    window.onload = function () {
        fetch(`fetch_group_messages.php?group_id=${groupId}`)
            .then(response => response.json())
            .then(messages => {
                console.log("⬇️ Loaded past messages:", messages.length);
                messages.forEach(appendMessage);
                chatBox.scrollTop = chatBox.scrollHeight;

                document.getElementById("onlineCount").textContent =
                    `${document.querySelectorAll('.member-item').length} members`;
            })
            .catch(error => {
                console.error("❌ Error loading messages:", error);
                chatBox.innerHTML = '<div class="text-center text-muted">Failed to load messages</div>';
            });
    };

    // ✅ Handle resize
    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            document.getElementById("sidebar").classList.remove("hidden");
            document.getElementById("sidebar").style.display = "flex";
        }
    });

    // ✅ Hide search on click outside
    document.addEventListener("click", function (e) {
        if (!searchContainer.contains(e.target)) {
            searchResults.style.display = "none";
        }
    });
</script>

</body>
</html>
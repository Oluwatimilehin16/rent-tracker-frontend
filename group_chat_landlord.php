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
    console.log("✅ Connected to Socket.IO server, ID:", socket.id);
    connectionStatus.textContent = "Connected";
    connectionStatus.className = "connection-status connected";
    setTimeout(() => connectionStatus.style.display = "none", 3000);
});

socket.on("disconnect", (reason) => {
    console.log("❌ Disconnected from server. Reason:", reason);
    connectionStatus.textContent = "Disconnected";
    connectionStatus.className = "connection-status disconnected";
    connectionStatus.style.display = "block";
});

socket.on("connect_error", (error) => {
    console.error("🔌 Connection error:", error);
});

socket.on("error", (error) => {
    console.error("❌ Socket error:", error);
    alert("Error: " + error.message);
});

// ✅ Join group with confirmation
socket.emit("join-group", groupId);
console.log("📡 Attempting to join group:", groupId);

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
    if (!msg) {
        console.log("⚠️ Empty message, not sending");
        return;
    }

    if (!socket.connected) {
        console.error("❌ Socket not connected");
        alert("Not connected to server. Please refresh the page.");
        return;
    }

    console.log("📤 PREPARING TO SEND MESSAGE:", msg);
    sendBtn.disabled = true;

    const messageData = {
        group_id: groupId,
        sender_id: senderId,
        sender_name: senderName,
        sender_role: senderRole,
        message: msg
    };

    console.log("📤 SENDING MESSAGE DATA:", messageData);
    console.log("📊 Current connection status:", socket.connected);
    console.log("📊 Group ID:", groupId, "Sender ID:", senderId);

    socket.emit("send-group-message", messageData);

    messageInput.value = "";
    messageInput.style.height = 'auto';

    if (isTyping) {
        isTyping = false;
        socket.emit("typing-stop", { group_id: groupId });
    }

    setTimeout(() => sendBtn.disabled = false, 1000);
}

// ✅ Enhanced message receive handling
socket.on("group-message", data => {
    console.log("📥 RECEIVED MESSAGE FROM SERVER:");
    console.log("📋 Raw data:", data);
    console.log("📋 Data type:", typeof data);
    console.log("📋 Data structure:", JSON.stringify(data, null, 2));
    
    // Check if data is valid
    if (!data) {
        console.error("❌ Received null/undefined data");
        return;
    }
    
    if (!data.message) {
        console.error("❌ Received data missing message field:", data);
        return;
    }
    
    if (!data.sender_id) {
        console.error("❌ Received data missing sender_id field:", data);
        return;
    }
    
    console.log("✅ Message data validation passed, calling appendMessage");
    appendMessage(data);
});
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
    console.log("📝 APPENDING MESSAGE TO UI:");
    console.log("📋 Message content:", data.message);
    console.log("📋 Sender ID:", data.sender_id, "Current user ID:", senderId);
    console.log("📋 Timestamp:", data.timestamp);
    
    // Check for duplicates
    const isDuplicate = allMessages.some(msg => 
        msg.message === data.message && 
        msg.sender_id === data.sender_id && 
        Math.abs(new Date(msg.timestamp) - new Date(data.timestamp)) < 2000
    );
    
    if (isDuplicate) {
        console.log("🔄 Duplicate message detected, skipping");
        return;
    }
    
    allMessages.push(data);
    console.log("📊 Total messages now:", allMessages.length);

    const isMe = data.sender_id == senderId;
    const isGrouped = shouldGroupMessage(data);
    const time = formatLocalTime(data.timestamp);

    console.log("📋 Message properties:", { isMe, isGrouped, time });

    const messageDiv = document.createElement("div");
    messageDiv.classList.add("message");
    if (isMe) messageDiv.classList.add("me");

    const messageHTML = isMe
        ? `<div class="me-bubble">
               <div class="message-content">${escapeHtml(data.message)}</div>
               <div class="timestamp">${time} <span class="message-status">✓</span></div>
           </div>`
        : `<div class="other-bubble">
               ${!isGrouped ? `<div class="sender-name">${escapeHtml(data.sender_name || "Someone")}</div>` : ''}
               <div class="message-content">${escapeHtml(data.message)}</div>
               <div class="timestamp">${time}</div>
           </div>`;

    messageDiv.innerHTML = messageHTML;
    console.log("📋 Generated HTML:", messageHTML);

    // Add to chat
    const lastGroup = chatBox.lastElementChild;
    if (lastGroup && lastGroup.getAttribute("data-sender") === data.sender_id.toString() && isGrouped) {
        lastGroup.appendChild(messageDiv);
        console.log("📎 Added to existing message group");
    } else {
        const newGroup = document.createElement("div");
        newGroup.classList.add("message-group");
        newGroup.setAttribute("data-sender", data.sender_id);
        newGroup.appendChild(messageDiv);
        chatBox.appendChild(newGroup);
        console.log("📎 Created new message group");
    }

    lastMessageSender = data.sender_id;
    lastMessageTime = new Date(data.timestamp);
    
    console.log("📊 Chat box children count:", chatBox.children.length);
    console.log("📊 Scrolling to bottom");
    chatBox.scrollTop = chatBox.scrollHeight;
    
    console.log("✅ MESSAGE SUCCESSFULLY ADDED TO UI");
}

// ✅ Add HTML escape function
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

// ✅ Debug the chat box
console.log("📊 Chat box element:", chatBox);
console.log("📊 Chat box styles:", window.getComputedStyle(chatBox));

// ✅ Test function - call this in console to verify message display
window.testMessage = function() {
    const testData = {
        id: 999,
        group_id: groupId,
        sender_id: senderId,
        sender_name: senderName,
        sender_role: senderRole,
        message: "Test message " + new Date().getTime(),
        timestamp: new Date().toISOString()
    };
    console.log("🧪 Testing with fake message:", testData);
    appendMessage(testData);
};

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
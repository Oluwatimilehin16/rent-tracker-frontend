<?php
session_start();

// ✅ Ensure landlord is logged in
if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$landlord_name = $_SESSION['landlord_name']; // Make sure this is stored at login
$group_id = intval($_GET['group_id'] ?? 0);

// Set your API base URL
$API_BASE_URL = "https://rent-tracker-api.onrender.com"; // Replace with your actual API URL

?>

<!DOCTYPE html>
<html>
<head>
    <title>Group Chat (Landlord)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"  href="group_chat_landlord.css">
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
            <!-- Members will be loaded via API -->
        </div>
   
        <div class="sidebar-footer">
            <a href="landlord_group_chats.php" class="dashboard-button">← Back to Dashboard</a>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-wrapper">
        <div class="chat-header">
            <div>
                <span id="groupName">Loading...</span>
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
    // Configuration
    const API_BASE_URL = "<?php echo $API_BASE_URL; ?>"; // Your API base URL
    const socket = io("https://rent-tracker-backend.onrender.com"); 
    const groupId = <?php echo $group_id; ?>;
    const senderId = <?php echo $landlord_id; ?>;
    const senderName = <?php echo json_encode($landlord_name); ?>;
    const senderRole = "landlord";

    // DOM elements
    const chatBox = document.getElementById("chat-box");
    const messageInput = document.getElementById("messageInput");
    const sendBtn = document.getElementById("sendBtn");
    const typingIndicator = document.getElementById("typingIndicator");
    const connectionStatus = document.getElementById("connectionStatus");
    const searchContainer = document.getElementById("searchContainer");
    const searchInput = document.getElementById("searchInput");
    const searchResults = document.getElementById("searchResults");
    const memberList = document.getElementById("memberList");
    const groupNameElement = document.getElementById("groupName");
    const onlineCountElement = document.getElementById("onlineCount");

    // State variables
    let isTyping = false;
    let typingTimeout;
    let allMessages = [];
    let lastMessageSender = null;
    let lastMessageTime = null;
    let groupData = null;
    let membersData = [];

    // ✅ API Helper Functions
    async function apiRequest(endpoint, options = {}) {
        try {
            const response = await fetch(`${API_BASE_URL}/${endpoint}`, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // ✅ Verify access to group
    async function verifyGroupAccess() {
        try {
            const response = await apiRequest(`group_chat_access_tenant_api.php?landlord_id=${senderId}&group_id=${groupId}`);
            
            if (response.success) {
                groupData = response.data.group;
                groupNameElement.textContent = groupData.name;
                return true;
            } else {
                alert('Access denied to this group chat');
                window.location.href = 'landlord_group_chats.php';
                return false;
            }
        } catch (error) {
            console.error('Error verifying group access:', error);
            alert('Error accessing group chat');
            window.location.href = 'landlord_group_chats.php';
            return false;
        }
    }

    // ✅ Load group members
    async function loadGroupMembers() {
        try {
            const response = await apiRequest(`group_members_api.php?group_id=${groupId}&landlord_id=${senderId}`);
            
            if (response.success) {
                membersData = response.data.members;
                renderMembers();
                onlineCountElement.textContent = `${membersData.length} members`;
            } else {
                console.error('Failed to load members:', response.message);
                memberList.innerHTML = '<div class="error">Failed to load members</div>';
            }
        } catch (error) {
            console.error('Error loading members:', error);
            memberList.innerHTML = '<div class="error">Error loading members</div>';
        }
    }

    // ✅ Render members in sidebar
    function renderMembers() {
        memberList.innerHTML = '';
        
        membersData.forEach(member => {
            const memberDiv = document.createElement('div');
            memberDiv.className = 'member-item';
            memberDiv.setAttribute('data-user-id', member.id);
            memberDiv.setAttribute('data-role', member.role);
            
            memberDiv.innerHTML = `
                <div class="member-avatar">
                    ${member.avatar}
                </div>
                <div class="member-info">
                    <div class="member-name">${member.firstname}</div>
                    <div class="member-role">${member.role}</div>
                </div>
                <div class="online-indicator" style="display: none;"></div>
            `;
            
            memberList.appendChild(memberDiv);
        });
    }

    // ✅ Load past messages|
    async function loadMessages() {
        try {
            const response = await apiRequest(`group_messages_api.php?group_id=${groupId}&landlord_id=${senderId}&limit=50&offset=0`);
            
            if (response.success) {
                allMessages = response.data.messages;
                allMessages.forEach(appendMessage);
                chatBox.scrollTop = chatBox.scrollHeight;
            } else {
                console.error('Failed to load messages:', response.message);
                chatBox.innerHTML = '<div class="text-center text-muted">Failed to load messages</div>';
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            chatBox.innerHTML = '<div class="text-center text-muted">Error loading messages</div>';
        }
    }

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

    // ✅ Auto-resize textarea and typing indicator
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
        if (!allMessages.some(msg => msg.id === data.id)) {
            allMessages.push(data);
        }
        
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

        sidebar.classList.toggle("hidden");
        toggleBtn.textContent = sidebar.classList.contains("hidden") ? "Show" : "Hide";
    }

    // ✅ Initialize page
    window.onload = async function () {
        try {
            // Verify access first
            const hasAccess = await verifyGroupAccess();
            if (!hasAccess) return;
            
            // Load members and messages in parallel
            await Promise.all([
                loadGroupMembers(),
                loadMessages()
            ]);
            
        } catch (error) {
            console.error('Error initializing page:', error);
            alert('Error loading chat. Please try again.');
        }
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
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$group_id = intval($_GET['group_id'] ?? 0);

// Determine if user is tenant or landlord
if (isset($_SESSION['tenant_id'])) {
    $user_id = $_SESSION['tenant_id'];
    $user_role = 'tenant';
} elseif (isset($_SESSION['landlord_id'])) {
    $user_id = $_SESSION['landlord_id'];
    $user_role = 'landlord';
} else {
    header("Location: login.php");
    exit();
}

if (!$group_id) {
    echo "Invalid group ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Chat</title>
    <link rel="stylesheet" href="group_chat_landlord.css">
    <script src="https://cdn.socket.io/4.0.1/socket.io.min.js"></script>
</head>
<body>
    <div class="connection-status connecting" id="connectionStatus">Loading...</div>
    
    <div class="chat-container">
        <div class="toggle-sidebar-btn">
            <button class="header-btn" onclick="toggleSidebar()" id="toggleBtn">Hide</button>
        </div>

        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span>Group Members</span>
            </div>

            <div class="member-list" id="memberList">
                <!-- Members will be loaded dynamically -->
            </div>

            <div class="sidebar-footer">
                <a href="tenant_group_chat.php" class="dashboard-button">← Back to Dashboard</a>
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

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
    // Configuration
    const API_BASE_URL = 'https://rent-tracker-api.onrender.com'; // Replace with your actual API URL
    const SOCKET_URL = 'https://rent-tracker-backend.onrender.com'; // Update for production
    
    // User and group data
    const groupId = <?php echo $group_id; ?>;
    const userId = <?php echo $user_id; ?>;
    const userRole = "<?php echo $user_role; ?>";
    
    let userName = '';
    let groupData = null;
    
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
    const groupNameEl = document.getElementById("groupName");
    const onlineCountEl = document.getElementById("onlineCount");

    // Socket.io
    const socket = io(SOCKET_URL);
    
    // State variables
    let isTyping = false;
    let typingTimeout;
    let allMessages = [];
    let lastMessageSender = null;
    let lastMessageTime = null;

    // Initialize the chat
    async function initializeChat() {
        try {
            connectionStatus.textContent = "Loading chat data...";
            
            // Load group access and basic data
            const response = await fetch(`${API_BASE_URL}/group_chat_access_api.php?group_id=${groupId}&user_id=${userId}&user_role=${userRole}`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message);
            }
            
            groupData = result.data;
            userName = groupData.user.name;
            
            // Update UI with group data
            groupNameEl.textContent = groupData.group.name;
            document.title = `${groupData.group.name} - Group Chat`;
            onlineCountEl.textContent = `${groupData.member_count} members`;
            
            // Populate members list
            populateMembersList(groupData.members);
            
            // Load messages
            await loadMessages();
            
            // Connect to socket
            setupSocketConnection();
            
            connectionStatus.textContent = "Connected";
            connectionStatus.className = "connection-status connected";
            setTimeout(() => connectionStatus.style.display = "none", 3000);
            
        } catch (error) {
            console.error('Error initializing chat:', error);
            connectionStatus.textContent = "Failed to load chat";
            connectionStatus.className = "connection-status disconnected";
            
            chatBox.innerHTML = `
                <div style="text-align: center; color: #666; padding: 20px;">
                    <p>Failed to load chat: ${error.message}</p>
                    <button onclick="location.reload()" style="margin-top: 10px;">Retry</button>
                </div>
            `;
        }
    }
    
    // Populate members list
    function populateMembersList(members) {
        memberList.innerHTML = '';
        members.forEach(member => {
            const memberDiv = document.createElement('div');
            memberDiv.className = 'member-item';
            memberDiv.setAttribute('data-user-id', member.id);
            memberDiv.setAttribute('data-role', member.role);
            
            memberDiv.innerHTML = `
                <div class="member-avatar">
                    ${member.firstname.charAt(0).toUpperCase()}
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
    
    // Load messages from API
    async function loadMessages() {
        try {
            const response = await fetch(`${API_BASE_URL}/fetch_group_messages_api.php?group_id=${groupId}&user_id=${userId}&user_role=${userRole}&limit=50`);
            const result = await response.json();
            
            if (result.success) {
                result.data.messages.forEach(message => {
                    appendMessage(message);
                });
                chatBox.scrollTop = chatBox.scrollHeight;
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            chatBox.innerHTML = '<div class="text-center text-muted">Failed to load messages</div>';
        }
    }
    
    // Setup socket connection
    function setupSocketConnection() {
        // Connection status handlers
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

        // Join group
        socket.emit("join-group", groupId);

        // Message handlers
        socket.on("group-message", data => {
            appendMessage(data);
        });

        // Typing indicators
        socket.on("user-typing", (data) => {
            if (data.sender_name !== userName) {
                typingIndicator.textContent = `${data.sender_name} is typing...`;
                typingIndicator.classList.remove("hidden");
            }
        });

        socket.on("user-stopped-typing", () => {
            typingIndicator.classList.add("hidden");
        });
    }

    // Auto-resize textarea and typing indicator
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        
        // Typing indicator
        if (!isTyping) {
            isTyping = true;
            socket.emit("typing-start", { group_id: groupId, sender_name: userName });
        }
        
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            isTyping = false;
            socket.emit("typing-stop", { group_id: groupId });
        }, 1000);
    });

    // Send on Enter (without Shift)
    messageInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Send message function
    function sendMessage() {
        const msg = messageInput.value.trim();
        if (!msg) return;

        sendBtn.disabled = true;
        
        socket.emit("send-group-message", {
            group_id: groupId,
            sender_id: userId,
            sender_name: userName,
            sender_role: userRole,
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

    // Format time
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

    // Group consecutive messages
    function shouldGroupMessage(data) {
        if (!lastMessageSender) return false;
        
        const currentTime = new Date(data.timestamp);
        const timeDiff = (currentTime - lastMessageTime) / (1000 * 60); // minutes
        
        return lastMessageSender === data.sender_id && timeDiff < 5;
    }

    // Append message with grouping
    function appendMessage(data) {
        allMessages.push(data);
        
        const isMe = data.sender_id == userId;
        const isGrouped = shouldGroupMessage(data);
        const time = formatLocalTime(data.timestamp);

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

    // Search functionality
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

    // Clear chat (visual only)
    function clearChat() {
        if (confirm("Clear chat history? (This only clears your view, not the actual messages)")) {
            chatBox.innerHTML = "";
            allMessages = [];
            lastMessageSender = null;
            lastMessageTime = null;
        }
    }

    // Toggle sidebar
// REPLACE your existing window resize and toggle functions with these:

// Updated toggle sidebar function
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleBtn");

    sidebar.classList.toggle("hidden");
    
    // Update button text based on state
    if (sidebar.classList.contains("hidden")) {
        toggleBtn.textContent = "Show";
    } else {
        toggleBtn.textContent = "Hide";
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener("click", function(e) {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleBtn");
    
    // Only apply this behavior on mobile screens
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            if (!sidebar.classList.contains("hidden")) {
                sidebar.classList.add("hidden");
                toggleBtn.textContent = "Show";
            }
        }
    }
});

// REPLACE your window resize handler with this:
window.addEventListener("resize", function() {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleBtn");
    
    if (window.innerWidth > 768) {
        // Desktop: show sidebar by default
        sidebar.classList.remove("hidden");
        sidebar.style.display = "flex";
        toggleBtn.textContent = "Hide";
    } else {
        // Mobile: hide sidebar by default and reset styles
        sidebar.classList.add("hidden");
        sidebar.style.display = ""; // Reset inline style
        toggleBtn.textContent = "Show";
    }
});

// ADD this initialization for mobile-first approach
function initializeMobileLayout() {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleBtn");
    
    if (window.innerWidth <= 768) {
        // Start with sidebar hidden on mobile
        sidebar.classList.add("hidden");
        toggleBtn.textContent = "Show";
    } else {
        // Start with sidebar visible on desktop
        sidebar.classList.remove("hidden");
        toggleBtn.textContent = "Hide";
    }
}

// UPDATE your window.onload function:
window.onload = function() {
    initializeMobileLayout(); // Initialize mobile layout first
    initializeChat();
};
</script>
</body>
</html>
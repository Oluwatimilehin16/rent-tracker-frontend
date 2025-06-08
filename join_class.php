<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['tenant_id']) || $_SESSION['users_role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Class</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="join_class.css">
</head>
<body>

<div class="join-class-container">
    <div class="join-class">
        <h3>Connect with Your Landlord</h3>
        
        <div id="message" class="message" style="display: none;"></div>
        
        <form id="joinClassForm">
            <label for="class_code">Enter your access code:</label>
            <input type="text" id="class_code" name="class_code" required placeholder="Enter your unique access code" autofocus>
            <button type="submit" id="joinButton">Join</button>
        </form>
        
        <div class="info">
            <p>Not sure about the access code? Contact your landlord or check your email for more details.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('joinClassForm');
    const messageDiv = document.getElementById('message');
    const joinButton = document.getElementById('joinButton');
    const classCodeInput = document.getElementById('class_code');
    
    // Your API base URL - update this to your Render API URL
    const API_BASE_URL = 'https://rent-tracker-api.onrender.com'; // Replace with your actual API URL
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const classCode = classCodeInput.value.trim();
        
        if (!classCode) {
            showMessage('Please enter a class code', 'error');
            return;
        }
        
        // Disable button and show loading state
        joinButton.disabled = true;
        joinButton.textContent = 'Joining...';
        
        try {
            const response = await fetch(`${API_BASE_URL}/join_class_api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    tenant_id: '<?php echo $tenant_id; ?>',
                    class_code: classCode
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage(data.message, 'success');
                // Redirect to dashboard after successful join
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1500);
            } else {
                showMessage(data.message, 'error');
            }
            
        } catch (error) {
            console.error('Error:', error);
            showMessage('Network error. Please try again.', 'error');
        } finally {
            // Re-enable button
            joinButton.disabled = false;
            joinButton.textContent = 'Join';
        }
    });
    
    function showMessage(message, type) {
        messageDiv.textContent = message;
        messageDiv.className = `message ${type}`;
        messageDiv.style.display = 'block';
        
        // Auto-hide success messages after 3 seconds
        if (type === 'success') {
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);
        }
    }
});
</script>
</body>
</html>


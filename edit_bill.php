<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$bill_id = (int)$_GET['id'];

// API base URL - update this to your Render API URL
$api_base_url = 'https://rent-tracker-api.onrender.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bill</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            background-color: #f8f9fa;
            color: #333;
            overflow-x: hidden;
        }

        /* Header */
        header {
            background-color: #218838;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        header .logo img {
            height: 40px;
        }

        .hamburger {
            display: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
            background: none;
            border: none;
        }

        nav ul {
            display: flex;
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin: 0 10px;
        }

        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: block;
        }

        nav ul li a:hover,
        nav ul li a.active {
            color: #ecc700;
            background-color: rgba(255,255,255,0.1);
        }

        .content-wrapper {
            margin-top: 70px;
            padding: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .title {
            color: #186c2c;
            text-align: center;
            margin-bottom: 5px;
            font-size: 24px;
            font-variant: small-caps;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #218838;
            display: block;
        }

        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container input[type="date"],
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            margin-bottom: 20px;
            transition: border 0.3s ease;
        }

        .form-container input:focus,
        .form-container select:focus,
        .form-container textarea:focus {
            border-color: #218838;
            outline: none;
        }

        .form-container textarea {
            resize: vertical;
            height: 100px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-container input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 10px;
        }

        .form-container .btn-primary {
            background-color: #218838;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            width: 100%;
        }

        .form-container .btn-primary:hover {
            background-color: #186c2c;
        }

        .form-container .btn-primary:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .loading {
            text-align: center;
            color: #666;
            padding: 20px;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .header-content {
                flex-wrap: wrap;
            }

            nav {
                width: 100%;
                order: 3;
            }

            nav ul {
                display: none;
                flex-direction: column;
                width: 100%;
                background-color: #218838;
                margin-top: 15px;
                border-radius: 6px;
            }

            nav.active ul {
                display: flex;
            }

            nav ul li {
                margin: 0;
                width: 100%;
            }

            nav ul li a {
                padding: 12px 20px;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }

            nav ul li:last-child a {
                border-bottom: none;
            }

            .content-wrapper {
                margin-top: 70px;
                padding: 15px;
            }

            .title {
                font-size: 20px;
                margin-bottom: 20px;
            }

            .form-container {
                padding: 20px;
                margin: 0 10px;
            }

            .form-container input,
            .form-container select,
            .form-container textarea {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }

        @media (max-width: 480px) {
            header {
                padding: 10px 15px;
            }

            .content-wrapper {
                padding: 10px;
            }

            .form-container {
                padding: 15px;
                margin: 0 5px;
            }

            .title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <a href="index.php"><img src="./assets/logo.png" alt="RentTracker"></a>
            </div>
            <button class="hamburger" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <nav id="main-nav">
                <ul>
                    <li><a href="create_class.php"><i class="fas fa-user-plus"></i> Invite</a></li>
                    <li><a href="add_bill.php"><i class="fas fa-plus-circle"></i> Add Bill</a></li>
                    <li><a href="view_bills.php" class="active"><i class="fas fa-list-alt"></i> View Bills</a></li>
                    <li><a href="create_groupchat.php"><i class="fas fa-users"></i> Create group chat</a></li>
                    <li><a href="landlord_group_chats.php">💬 View My Group Chats</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="content-wrapper">
        <h1 class="title" id="page-title">Edit Bill</h1>
        
        <div id="message-container"></div>
        
        <div id="loading" class="loading">
            <i class="fas fa-spinner fa-spin"></i> Loading bill details...
        </div>

        <div class="form-container" id="form-container" style="display: none;">
            <form id="edit-bill-form">
                <label for="bill_name">Bill Name</label>
                <input type="text" id="bill_name" name="bill_name" required>

                <label for="amount">Amount (₦)</label>
                <input type="number" id="amount" step="0.01" name="amount" required>

                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" required>

                <label for="user_id">Assign to Tenant</label>
                <select id="user_id" name="users_id" required>
                    <option value="">Select a tenant</option>
                </select>

                <div class="checkbox-wrapper">
                    <input type="checkbox" id="status" name="status" value="paid">
                    <label for="status">Mark as Paid</label>
                </div>

                <button type="submit" class="btn-primary" id="submit-btn">
                    <i class="fas fa-save"></i> Update Bill
                </button>
            </form>
        </div>
    </div>

    <script>
        const API_BASE_URL = '<?php echo $api_base_url; ?>';
        const LANDLORD_ID = <?php echo $landlord_id; ?>;
        const BILL_ID = <?php echo $bill_id; ?>;

        // Show message function
        function showMessage(message, type = 'success') {
            const messageContainer = document.getElementById('message-container');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            messageContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            
            // Auto-hide success messages after 3 seconds
            if (type === 'success') {
                setTimeout(() => {
                    messageContainer.innerHTML = '';
                }, 3000);
            }
        }

        // Fetch bill details and tenants on page load
        async function loadPageData() {
            try {
                console.log('Fetching data from:', API_BASE_URL);
                
                // Fetch bill details and tenants in parallel
                const [billResponse, tenantsResponse] = await Promise.all([
                    fetch(`${API_BASE_URL}/get_bill_details.php?bill_id=${BILL_ID}&landlord_id=${LANDLORD_ID}`),
                    fetch(`${API_BASE_URL}/get_landlord_tenants.php?landlord_id=${LANDLORD_ID}`)
                ]);

                console.log('Bill response status:', billResponse.status);
                console.log('Tenants response status:', tenantsResponse.status);

                if (!billResponse.ok || !tenantsResponse.ok) {
                    throw new Error(`HTTP Error - Bill: ${billResponse.status}, Tenants: ${tenantsResponse.status}`);
                }

                // Get response text first to check if it's valid JSON
                const billText = await billResponse.text();
                const tenantsText = await tenantsResponse.text();
                
                console.log('Bill response:', billText.substring(0, 200));
                console.log('Tenants response:', tenantsText.substring(0, 200));

                let billData, tenantsData;
                try {
                    billData = JSON.parse(billText);
                    tenantsData = JSON.parse(tenantsText);
                } catch (jsonError) {
                    throw new Error('Invalid JSON response from API. Check API endpoints and PHP errors.');
                }

                if (!billData.success || !tenantsData.success) {
                    throw new Error(billData.message || tenantsData.message || 'Failed to load data');
                }

                // Populate form with bill data
                populateForm(billData.data);
                
                // Populate tenants dropdown
                populateTenants(tenantsData.data, billData.data.users_id);

                // Update page title
                document.getElementById('page-title').textContent = 
                    `Edit Bill for: ${billData.data.tenant_firstname || 'Tenant'}`;

                // Hide loading, show form
                document.getElementById('loading').style.display = 'none';
                document.getElementById('form-container').style.display = 'block';

            } catch (error) {
                console.error('Error loading page data:', error);
                showMessage('Failed to load bill details: ' + error.message, 'error');
                document.getElementById('loading').style.display = 'none';
            }
        }

        // Populate form with bill data
        function populateForm(bill) {
            document.getElementById('bill_name').value = bill.bill_name || '';
            document.getElementById('amount').value = bill.amount || '';
            document.getElementById('due_date').value = bill.due_date || '';
            document.getElementById('status').checked = bill.status === 'paid';
        }

        // Populate tenants dropdown
        function populateTenants(tenants, selectedTenantId) {
            const select = document.getElementById('user_id');
            
            tenants.forEach(tenant => {
                const option = document.createElement('option');
                option.value = tenant.id;
                option.textContent = tenant.firstname;
                option.selected = tenant.id == selectedTenantId;
                select.appendChild(option);
            });
        }

        // Handle form submission
        document.getElementById('edit-bill-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            try {
                const formData = new FormData(this);
                const data = {
                    bill_id: BILL_ID,
                    landlord_id: LANDLORD_ID,
                    bill_name: formData.get('bill_name'),
                    amount: parseFloat(formData.get('amount')),
                    due_date: formData.get('due_date'),
                    users_id: parseInt(formData.get('users_id')),
                    status: formData.get('status') === 'paid' ? 'paid' : 'unpaid'
                };

                const response = await fetch(`${API_BASE_URL}/update_bill.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('✅ ' + result.message, 'success');
                    // Optionally redirect after successful update
                    // setTimeout(() => window.location.href = 'view_bills.php', 2000);
                } else {
                    showMessage('❌ ' + result.message, 'error');
                }

            } catch (error) {
                console.error('Error updating bill:', error);
                showMessage('❌ Failed to update bill. Please try again.', 'error');
            } finally {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Navigation functions
        function toggleMenu() {
            const nav = document.getElementById('main-nav');
            nav.classList.toggle('active');
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const nav = document.getElementById('main-nav');
            const hamburger = document.querySelector('.hamburger');
            
            if (!nav.contains(event.target) && !hamburger.contains(event.target)) {
                nav.classList.remove('active');
            }
        });

        // Close menu when window is resized to desktop size
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('main-nav').classList.remove('active');
            }
        });

        // Load page data when DOM is ready
        document.addEventListener('DOMContentLoaded', loadPageData);
    </script>
</body>
</html>
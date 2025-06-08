<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['landlord_id'])) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];
$bill_id = (int)$_GET['id'];

if (!$bill_id) {
    header("Location: view_bills.php");
    exit();
}
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
            padding: 20px;
            color: #666;
        }

        .payment-date-container {
            display: none;
            margin-top: 10px;
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
                font-size: 16px;
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
        
        <div id="alert-container"></div>

        <div class="form-container">
            <div id="loading" class="loading">
                <i class="fas fa-spinner fa-spin"></i> Loading bill data...
            </div>

            <form id="edit-bill-form" style="display: none;">
                <label for="bill_name">Bill Name</label>
                <input type="text" id="bill_name" name="bill_name" required>

                <label for="amount">Amount (₦)</label>
                <input type="number" id="amount" step="0.01" name="amount" required>

                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" required>

                <label for="users_id">Assign to Tenant</label>
                <select id="users_id" name="users_id" required>
                    <option value="">Select a tenant</option>
                </select>

                <div class="checkbox-wrapper">
                    <input type="checkbox" id="status" name="status" value="paid" onchange="togglePaymentDate()">
                    <label for="status">Mark as Paid</label>
                </div>

                <div id="payment-date-container" class="payment-date-container">
                    <label for="payment_date">Payment Date</label>
                    <input type="date" id="payment_date" name="payment_date">
                </div>

                <button type="submit" class="btn-primary" id="submit-btn">
                    <i class="fas fa-save"></i> Update Bill
                </button>
            </form>
        </div>
    </div>

    <script>
        const API_BASE_URL = 'https://rent-tracker-api.onrender.com';
        const billId = <?php echo $bill_id; ?>;
        const landlordId = <?php echo $landlord_id; ?>;

        // Load bill data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadBillData();
        });

        async function loadBillData() {
            try {
                const response = await fetch(`${API_BASE_URL}?id=${billId}&landlord_id=${landlordId}`);
                const result = await response.json();

                if (result.success) {
                    populateForm(result.data);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('edit-bill-form').style.display = 'block';
                } else {
                    showAlert('error', result.message || 'Failed to load bill data');
                }
            } catch (error) {
                console.error('Error loading bill data:', error);
                showAlert('error', 'Failed to load bill data. Please try again.');
            }
        }

        function populateForm(data) {
            const { bill, tenants } = data;
            
            // Update page title
            document.getElementById('page-title').textContent = `Edit Bill for: ${bill.tenant_firstname || 'Tenant'}`;
            
            // Populate form fields
            document.getElementById('bill_name').value = bill.bill_name || '';
            document.getElementById('amount').value = bill.amount || '';
            document.getElementById('due_date').value = bill.due_date || '';
            document.getElementById('status').checked = bill.status === 'paid';
            document.getElementById('payment_date').value = bill.payment_date || '';
            
            // Populate tenants dropdown
            const tenantsSelect = document.getElementById('users_id');
            tenantsSelect.innerHTML = '<option value="">Select a tenant</option>';
            
            tenants.forEach(tenant => {
                const option = document.createElement('option');
                option.value = tenant.id;
                option.textContent = tenant.firstname;
                option.selected = tenant.id == bill.users_id;
                tenantsSelect.appendChild(option);
            });

            // Show/hide payment date based on status
            togglePaymentDate();
        }

        function togglePaymentDate() {
            const statusCheckbox = document.getElementById('status');
            const paymentDateContainer = document.getElementById('payment-date-container');
            const paymentDateInput = document.getElementById('payment_date');
            
            if (statusCheckbox.checked) {
                paymentDateContainer.style.display = 'block';
                if (!paymentDateInput.value) {
                    paymentDateInput.value = new Date().toISOString().split('T')[0];
                }
            } else {
                paymentDateContainer.style.display = 'none';
                paymentDateInput.value = '';
            }
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
                    bill_name: formData.get('bill_name'),
                    amount: parseFloat(formData.get('amount')),
                    due_date: formData.get('due_date'),
                    users_id: parseInt(formData.get('users_id')),
                    status: formData.get('status') === 'paid',
                    payment_date: formData.get('payment_date') || null
                };

                const response = await fetch(`${API_BASE_URL}?id=${billId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('success', result.message || 'Bill updated successfully!');
                    // Optionally redirect after success
                    setTimeout(() => {
                        window.location.href = 'view_bills.php';
                    }, 2000);
                } else {
                    showAlert('error', result.message || 'Failed to update bill');
                }
            } catch (error) {
                console.error('Error updating bill:', error);
                showAlert('error', 'Failed to update bill. Please try again.');
            } finally {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            
            alertContainer.innerHTML = `
                <div class="alert ${alertClass}">
                    ${message}
                </div>
            `;
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 5000);
            }
        }

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
    </script>
</body>
</html>
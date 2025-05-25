<?php
include 'config.php';
session_start();

if ($_SESSION['role'] == 'tenant' && isset($_GET['id'])) {
    $bill_id = $_GET['id'];
    $tenant_id = $_SESSION['user_id'];

    $sql = "INSERT INTO payments (tenant_id, bill_id, amount_paid) 
            SELECT ?, id, amount FROM bills WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $tenant_id, $bill_id);

    if ($stmt->execute()) {
        $conn->query("UPDATE bills SET status = 'paid' WHERE id = $bill_id");
        echo "Payment successful!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

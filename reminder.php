<?php
include 'config.php';

$today = date("Y-m-d");
$one_month_before = date("Y-m-d", strtotime("+1 month"));
$one_week_before = date("Y-m-d", strtotime("+1 week"));

$sql = "SELECT b.bill_name, b.due_date, u.email 
        FROM bills b JOIN users u ON b.landlord_id = u.id 
        WHERE (b.due_date = ? AND b.bill_name = 'Rent') 
           OR (b.due_date = ? AND b.bill_name != 'Rent')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $one_month_before, $one_week_before);
$stmt->execute();
$result = $stmt->get_result();

while ($bill = $result->fetch_assoc()) {
    $to = $bill['email'];
    $subject = "Upcoming Payment Reminder";
    $message = "Your payment for {$bill['bill_name']} is due on {$bill['due_date']}. Please pay on time.";
    mail($to, $subject, $message);
}

echo "Reminders sent!";
?>

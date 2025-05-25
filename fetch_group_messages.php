<?php
include 'config.php';
session_start();

if (!isset($_SESSION['tenant_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$tenant_id = $_SESSION['tenant_id'];
$group_id = intval($_GET['group_id'] ?? 0);

// ✅ Check if tenant has access to this group
$check = mysqli_query($conn, "
    SELECT gcc.group_id 
    FROM group_chat_classes gcc
    JOIN user_classes uc ON gcc.class_id = uc.class_id
    WHERE uc.user_id = '$tenant_id' AND gcc.group_id = '$group_id'
");

if (mysqli_num_rows($check) == 0) {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit();
}

// ✅ Get past messages with sender name
$query = mysqli_query($conn, "
    SELECT gm.sender_id, u.firstname, u.lastname, gm.message, gm.timestamp 
    FROM group_chat_messages gm
    JOIN users u ON gm.sender_id = u.id
    WHERE gm.group_id = '$group_id'
    ORDER BY gm.timestamp ASC
");

$data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = [
        "sender_id" => $row["sender_id"],
        "sender_name" => trim($row["firstname"] . ' ' . $row["lastname"]),
        "message" => $row["message"],
        "timestamp" => date("Y-m-d H:i", strtotime($row["timestamp"]))
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>

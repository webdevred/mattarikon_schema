<?php require("util.php");

header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT message FROM notifications WHERE expiration_time > CAST('" . $current_time . "' AS TIME)";
$query = $conn->query($sql);

$notifications = [];
while ($row = $query->fetch_object()) {
    $notifications[] = $row->message;
}

echo json_encode(["notifications" => $notifications]);

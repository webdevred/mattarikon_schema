<?php 
require("util.php");

if( ! isset($_SESSION["current_user"])) {
    echo "oh no site broken =/";
} else {
    $notification_stmt = $conn->prepare("INSERT INTO notifications (message, expiration_time) VALUES (?, ?)");
    $notification_stmt->bind_param("ss", $_POST["message"], $_POST["expiration_time"]);
    $notification_stmt->execute();
}

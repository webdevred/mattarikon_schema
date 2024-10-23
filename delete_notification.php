<?php 
require("util.php");

if( ! isset($_SESSION["current_user"])) {
    echo "oh no site broken =/";
} else {
   $conn->query("DELETE FROM notifications;");
   header("Location: index.php");
} ?>

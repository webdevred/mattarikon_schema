<?php

require("util.php");

if( ! isset($_SESSION["current_user"])) {
    die("finnished deleting the files");
} 

$stmt = $conn->prepare("DELETE FROM activities WHERE id = ?;");

foreach($_POST["delete_activity"] as $activity_id => $checked) {
    if($checked == "on") {
        $stmt->bind_param("s", $activity_id);
        $stmt->execute();
    }
}

header("Location: list_activities.php");

?>

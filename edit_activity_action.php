<?php

require("util.php");

if( ! isset($_SESSION["current_user"])) {
    die("finnished deleting the files");
} 

/*if( ! ( is_numeric($_POST["hour"]) AND is_numeric($_POST["minute"]) ) ) {
   die("die");
}*/

$name = $_POST["name"];
$room = $_POST["room"];
$responsible_staff = $_POST["responsible_staff"];
$summary = $_POST["summary"];
$type = $_POST["type"];

$start_time = sprintf("%s:00", $_POST["start_time"]);
$end_time = sprintf("%s:00", $_POST["end_time"]);

$activity_id = null;
if(empty($_POST["activity_id"])) {
    $stmt = $conn->prepare("INSERT INTO activities (name, type, responsible_staff, summary) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $type, $responsible_staff, $summary);
    $stmt->execute();
    $activity_id = $conn->insert_id;
  
    $stmt = $conn->prepare("INSERT INTO activities_time_and_place (activity_id, room, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $activity_id, $room, $start_time, $end_time);
    $stmt->execute();
} else {
    $activity_id = $_POST["activity_id"];
   
    $stmt = $conn->prepare("UPDATE activities SET name = ?, type = ?, responsible_staff = ?, summary = ? WHERE id = ?;");
    $stmt->bind_param("ssssi", $name, $type, $responsible_staff, $summary, $activity_id );
    $stmt->execute();
   
    $stmt = $conn->prepare("INSERT INTO activities_time_and_place (activity_id, room, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $activity_id, $room, $start_time, $end_time);
    $stmt->execute();
    
    if(isset($_POST["purge_old_times"]) AND !empty($_POST["purge_old_times"])) {
        $stmt = $conn->prepare("DELETE FROM `activities_time_and_place` WHERE id IN 
                                ( SELECT id FROM ( SELECT id, ROW_NUMBER() OVER(ORDER BY timestamp DESC) AS rownum 
                                    FROM activities_time_and_place WHERE activity_id = ? ) AS row_numbers 
                                WHERE rownum > 1 );");
        $stmt->bind_param("i", $activity_id);
        $stmt->execute();
    }
   
   if(isset($_POST["create_notification"]) AND !empty($_POST["create_notification"])) {
        $notification_stmt = $conn->prepare("INSERT INTO notifications (message, expiration_time) VALUES (?, ?)");
        
        $datetime = new DateTime($_POST["start_time"], new DateTimeZone("Europe/Stockholm"));
        $datetime->modify('+15 minutes');
        $notification_expiration_time = $datetime->format('H:i:s');
        $notification_stmt->bind_param("ss", $_POST["notification_message"], $notification_expiration_time);
        $notification_stmt->execute();
   }
}

header("Location: edit_activity.php?activity_id=". $activity_id)

?>
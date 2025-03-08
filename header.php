<?php require("util.php"); ?>
<html>
<head>
<link rel="stylesheet" href="style.css" />
<link rel="icon" href="https://mattarikon.se/onewebmedia/Mattarikon_favico.png">
<title>Schema | Mattarikon</title>
</head>
<body <?php if(isset($_GET["m"]) AND $_GET["m"] == "1" )  { ?> class="on-monitor"<?php } ?>>
<div id="page">
<!-- <h1>Mattarikon <?php echo date("Y"); ?></h1> -->
    <aside id="clock"><?php
                      global $current_time;
                      echo $current_time; ?></aside>
<header>
  <img src="Mattarikon_logo_2.png" />
  <?php if(isset($_SESSION["current_user"])) { ?>
    <span class="admin-bar">
    Current user: <?php  echo $_SESSION["current_user"]->username; ?> 
    | <a href="edit_activity">Lägg till ny aktivitet</a> 
        | <a href="create_notification">Lägg till ny notis</a>
    | <a href="list_activities">Lista alla aktiviteter (för debug)</a>
      | <a href="delete_notification">Ta bort notis</a>
    | <a href="logout">Logga ut</a>
    </span><?php } 
  
  $sql = "SELECT message FROM notifications WHERE expiration_time > CAST('" . $current_time ."' AS TIME)";
  $query = $conn->query($sql);
  
  
  while($notification = $query->fetch_object()) { 
    ?><div class="notification"><img src="icons/notifiering.png" /><?php echo $notification->message; ?></div>
  <?php } ?>
</header>
<div>

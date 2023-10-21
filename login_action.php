<?php

session_start();

require("util.php");

if( isset($_POST["username"]) AND  isset($_POST["password"]) ) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $options = ['cost' => 11];
   
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?;");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0 ) {
        $user= $result->fetch_object();
        if( password_verify( $password, $user->password_hash ) ) {
            unset($user->password_hash);
            $_SESSION["current_user"] = $user;
            header("Location: index.php");
        } else {
            die("really bad, you broke it =(");
        }
    } else {
        die("oh no. what happened?");
    }
} else {
   die("really big problems >:(");
}


?>
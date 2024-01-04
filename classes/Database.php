<?php

class Database {
    static public function connect() {
        require_once("config.php");

        $dsn = "mysql:host=". SERVER_HOSTNAME .";dbname=". DATABASE_NAME;
        // Create connection
        $conn = new PDO($dsn, USERNAME, PASSWORD);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    }
}

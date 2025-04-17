<?php session_start(); 

require("config.php");
$minute = "";
$datetime = new DateTime(null, new DateTimeZone("Europe/Stockholm"));
if( ! isset($_GET["t"])) {
    $current_time = $datetime->format("H:i");
} else {
    $current_time = $_GET["t"];
}

$minute = substr($current_time, 3, 2);

if (file_exists(__DIR__ ."/.htaccess") and $_SERVER['REQUEST_METHOD'] === 'GET') {
    $request = $_SERVER['REQUEST_URI'];

    if (str_ends_with($_SERVER["REQUEST_URI"], "index.php")) {
        $newUrl = dirname($request);
        header("Location: $newUrl", true, 301);
	exit;
    } elseif (strpos($request, '.php') !== false) {
        $newUrl = str_replace('.php', '', $request);
        header("Location: $newUrl", true, 301);
	exit;
    }
}

function list_activities($filter = 0) {
    global $conn;
    global $current_time;

    $qwhere = "";
    switch($filter) {
    case 1:
	    $qwhere = "WHERE ut.end_time > CAST('" . $current_time . "' AS TIME) AND ut.start_time <= CAST('" . $current_time . "' AS TIME)";
	    break;
    case 2:
        $qwhere = "WHERE ut.start_time > CAST('" . $current_time . "' AS TIME)";
	break;
    }

    $monitor_column = " 'ACTIVITY' ";
    if($filter != 0 and isset($_GET["m"])) {
        if ($filter == 3) {
            $monitor_column = " IF(TIMEDIFF(ut.end_time,ut.start_time) > CAST('03:00:00' AS TIME), 'FULLDAY', 'ACTIVITY') ";
            $qwhere = "WHERE TIMEDIFF(ut.end_time,ut.start_time) > CAST('03:00:00' AS TIME) ";
        } else {
            $qwhere .= "AND TIMEDIFF(ut.end_time,ut.start_time) <= CAST('03:00:00' AS TIME) ";
        }
    }
    $sql = "WITH time_and_place_ids AS ( SELECT DISTINCT activity_id,
            min(id) OVER(PARTITION by activity_id ) AS outdated_time_id,
            max(id) OVER(PARTITION by activity_id) AS updated_time_id
        FROM
       `activities_time_and_place`)
        SELECT 
              a.id, a.name, a.type, a.responsible_staff, a.summary,
              ut.room,
              TIME_FORMAT(ut.start_time, '%H:%i') AS updated_start_time,
              TIME_FORMAT(ut.end_time, '%H:%i') AS updated_end_time,
              TIME_FORMAT(ot.start_time, '%H:%i') AS outdated_start_time,
              TIME_FORMAT(ot.end_time, '%H:%i') AS outdated_end_time,
              at.icon_filename, at.display_name AS type_name,
              IF(type = 'MOVIE', 'MOVIE', " . $monitor_column . ") AS activity_column,
              ROW_NUMBER() OVER (PARTITION BY type = 'MOVIE' ORDER BY updated_start_time) as type_rownumber
              FROM activities AS a
              INNER JOIN activity_types AS at ON a.type = at.name
              INNER JOIN time_and_place_ids AS ti ON a.id = ti.activity_id 
              INNER JOIN activities_time_and_place AS ut ON ut.id = ti.updated_time_id
              LEFT JOIN activities_time_and_place AS ot ON ti.outdated_time_id <> ti.updated_time_id AND ot.id = ti.outdated_time_id
              " . $qwhere ." ORDER BY updated_start_time";

    $activities = $conn->query($sql);
    return $activities;
}

// Create connection
$conn = new mysqli(SERVER_HOSTNAME, USERNAME, PASSWORD, DATABASE_NAME);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}?>

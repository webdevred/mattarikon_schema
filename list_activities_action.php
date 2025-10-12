<?php

require("util.php");

if( ! isset($_SESSION["current_user"])) {
    die("finnished deleting the files");
} 

$stmt = $conn->prepare("DELETE FROM activities WHERE id = ?;");
if(isset($_POST["delete_activities"]))  {
foreach($_POST["delete_activity"] as $activity_id => $checked) {
    if($checked == "on") {
        $stmt->bind_param("s", $activity_id);
        $stmt->execute();
    }
}
} elseif (isset($_POST["update_date"])) {
$filename = 'config.php';
$file = fopen($filename, 'r');
$code = fread($file,filesize($filename));
$pattern = "/(define\s*\(\s*'CONVENTION_DATE'\s*,\s*')[^']*('\s*\)\s*;)/";
$new_code = preg_replace($pattern, '${1}' . $_POST["date"] . '${2}', $code);
file_put_contents($filename, $new_code);
}

header("Location: list_activities.php");

?>

<?php require("util.php");

$activities = [];
$qry = list_activities();
while( $activity = $qry->fetch_object() ) {
    $activities = [
    'id' => $activity->id,
    'name' => $activity->name,
    'type' => $activity->type,
    'responsible_staff' => $activity->responsible_staff,
    'summary' => $activity->summary,
    'room' => $activity->room,
    'updated_start_time' => $activity->updated_start_time,
    'updated_end_time' => $activity->updated_end_time,
    'outdated_start_time' => $activity->outdated_start_time,
    'outdated_end_time' => $activity->outdated_end_time,
    'icon_filename' => $activity->icon_filename,
    'type_name' => $activity->type_name,
    'type_rownumber' => $activity->type_rownumber];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($activities);


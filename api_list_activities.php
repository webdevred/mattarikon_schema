<?php require("util.php");

function objToAssoc($activity) {
    return ['id' => $activity->id,
            'name' => $activity->name,
            'type' => $activity->type,
            'responsible_staff' => $activity->responsible_staff,
            'summary' => $activity->summary,
            'room' => $activity->room,
            'explicit' => ! empty ($activity->explicit),
            'updated_start_time' => $activity->updated_start_time,
            'updated_end_time' => $activity->updated_end_time,
            'outdated_start_time' => $activity->outdated_start_time,
            'outdated_end_time' => $activity->outdated_end_time,
            'icon_filename' => $activity->icon_filename,
            'type_name' => $activity->type_name,
            'type_rownumber' => $activity->type_rownumber];
}

header('Content-Type: application/json; charset=utf-8');

if( ! defined("CONVENTION_DATE") OR ( defined("CONVENTION_DATE") AND CONVENTION_DATE == date("Y-m-d") )
    OR (isset($_GET["c"]) AND isset($_GET["c"]) == "1" ) OR isset($_GET["t"]) ) {

    $data = ["current" => [], "coming" => []];

    $current_activity_qry = list_activities(1);
    while( $activity = $current_activity_qry->fetch_object() ) {
        $data["current"][] = objToAssoc($activity);
    }
    
    $coming_activity_qry = list_activities(2);
    while( $activity = $coming_activity_qry->fetch_object() ) {
        $data["coming"][] = objToAssoc($activity);
    }

    echo json_encode(["data" => $data]);
} else {
    $activities = [];
    $qry = list_activities();
    while( $activity = $qry->fetch_object() ) {
        $activities[] = objToAssoc($activity);        
    }

    echo json_encode(["data" => ["activities" => $activities]]);
}

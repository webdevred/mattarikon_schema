<?php require("header.php");

if( ! isset($_SESSION["current_user"])) {
    echo "oh no site broken =/";
} else {
    ?><form method="POST" action="list_activities_action.php"><table>
        <button type="submit">Ta bort markerade akiviteter som har ikryssad checkbox (du kan kryssa i checkboxen i listan)</button>
      <tr>
          <td></td>
          <th>ID</th>
          <th>Name</th>
          <th>Movie/Föreläsning</th>
          <th>Rum</th>
          <th>Värd</th>
          <th>Tid</th>
      </tr>
    <?php
    $sql = "WITH time_and_place_ids AS ( SELECT DISTINCT activity_id,
            min(id) OVER(PARTITION by activity_id ) AS outdated_time_id,
            max(id) OVER(PARTITION by activity_id) AS updated_time_id
        FROM
       `activities_time_and_place`)
        SELECT 
              a.id, a.name, a.type, at.display_name AS type_display_name, a.responsible_staff, a.summary,
              ut.room,
              TIME_FORMAT(ut.start_time, '%H:%i') AS start_time,
              TIME_FORMAT(ut.end_time, '%H:%i') AS end_time
              FROM activities AS a
              INNER JOIN activity_types AS at ON at.name = a.type
              LEFT JOIN time_and_place_ids AS ti ON a.id = ti.activity_id 
              LEFT JOIN activities_time_and_place AS ut ON ut.id = ti.updated_time_id";
    $query = $conn->query($sql);
    
    while( $activity = $query->fetch_object() ) {
        ?><tr>
            <td><input type="checkbox" name="delete_activity[<?php echo $activity->id; ?>]"></td>
            <td><?php echo $activity->id; ?></td>
            <td><a href="edit_activity.php?activity_id=<?php echo $activity->id; ?>"><?php echo $activity->name; ?></a></td>
            <td><?php echo sprintf("%s (%s)", $activity->type_display_name, $activity->type); ?></td>
            <td><?php echo $activity->room; ?></td>
            <td><?php echo $activity->responsible_staff; ?></td>
            <td><?php echo (!empty($activity->start_time) AND ! empty($activity->end_time) ) ? sprintf("%s - %s", $activity->start_time, $activity->end_time) : ""; ?></td>
        </tr><?php
    }
    
    ?></table></form><?php
}

require("footer.php");

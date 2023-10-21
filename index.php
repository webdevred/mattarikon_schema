<?php require("header.php");

function list_activities($qwhere = "") {
    global $conn;
    $query = $conn->query("WITH time_and_place_ids AS ( SELECT DISTINCT activity_id,
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
              ROW_NUMBER() OVER (PARTITION BY type = 'MOVIE' ORDER BY updated_start_time) as type_rownumber
              FROM activities AS a
              INNER JOIN activity_types AS at ON a.type = at.name
              INNER JOIN time_and_place_ids AS ti ON a.id = ti.activity_id 
              INNER JOIN activities_time_and_place AS ut ON ut.id = ti.updated_time_id
              LEFT JOIN activities_time_and_place AS ot ON ti.outdated_time_id <> ti.updated_time_id AND ot.id = ti.outdated_time_id
              " . $qwhere ." ORDER BY updated_start_time");
    ?><div class="activity-container">
       <h3 class="desktop-only-heading">Filmer</h3>
       <h3 class="desktop-only-heading">Aktiviteter</h3><?php
    while( $activity = $query->fetch_object() ) {
   
   $start_time_datetime = new DateTime($activity->updated_start_time);
   $end_time_datetime = new DateTime($activity->updated_end_time);
   $duration = $end_time_datetime->diff($start_time_datetime)->format("%H") * 60 + $end_time_datetime->diff($start_time_datetime)->format("%i");

   ?>
    <section class="activity grid-start-<?php echo $activity->type_rownumber + 1; ?> activity-<?php echo strtolower($activity->type); ?>">
        <img src="icons/<?php echo $activity->icon_filename; ?>" />
        <span class="time"><?php echo sprintf("%s - %s (%d min)", $activity->updated_start_time, $activity->updated_end_time, $duration); ?></span> i <strong><?php echo $activity->room; ?></strong>
        <?php if( !empty($activity->outdated_start_time)) {
            ?><div class="changed-time">Ändrad från <?php echo sprintf("%s - %s", $activity->outdated_start_time, $activity->outdated_end_time); ?></div><?php
        } ?>
        <h3><?php echo $activity->name; ?></h3>
        <?php if(!empty($activity->summary)) { ?> <p><?php echo $activity->summary; ?></p><?php } ?>
        <?php if(isset($_SESSION["current_user"])) { ?><a href="edit_activity.php?activity_id=<?php echo $activity->id; ?>">Redigera aktivitet</a><br><?php } ?>
        <strong>Värd:</strong> <?php echo $activity->responsible_staff; ?>
        <br />
    </section>
   <?php } ?>
  </div><?php
}
if( ! defined("CONVENTION_DATE") OR ( defined("CONVENTION_DATE") AND CONVENTION_DATE == date("Y-m-d") ) OR (isset($_GET["c"]) AND isset($_GET["c"]) == "1" ) OR isset($_GET["t"]) ) {
  ?><h2>Pågående</h2>
  <?php 
  global $current_time;
  list_activities("WHERE ut.end_time > CAST('" . $current_time . "' AS TIME) AND ut.start_time <= CAST('" . $current_time . "' AS TIME)"); 
  ?><h2>Kommande</h2>
  <?php list_activities("WHERE ut.start_time > CAST('" . $current_time . "' AS TIME)"); 
} else {
  ?><h2>Schema</h2>
  <?php list_activities(); 
}

?>
<div id="links">
<a href="https://discord.mangakai.se">Discord</a>
<a href="https://www.mangakai.se/hem/om-mangakai/bli-medlem">Bli medlem</a>
<a href="https://www.mattarikon.se/">Mattarikon</a>
</div>
<script defer src="index.js"></script>

<?php require("footer.php") ?>
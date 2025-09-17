<?php require("header.php");

function show_activities($filter) {
    global $minute;
    $qry = list_activities($filter);
    ?><div class="activity-container"><?php
    if($filter == 1 or $filter == 2) { ?>
       <h3 class="desktop-only-heading">Filmer</h3>
       <h3 class="desktop-only-heading">Aktiviteter</h3>
<?php
}
$activity_max_fullday_row_number = 0;
$activities = [];
    while( $activity = $qry->fetch_object() ) {
        $activities[] = $activity;
        if($activity->type_rownumber > $activity_max_fullday_row_number and $activity->activity_column == 'FULLDAY') {
            $activity_max_fullday_row_number = $activity->type_rownumber;
        }
    }

    $group_index = (int) (($minute + 1) / (60 / ($activity_max_fullday_row_number + 1))) - 1;

    if ($group_index == $activity_max_fullday_row_number) $group_index -= 2;

foreach($activities as $activity) {    
   $start_time_datetime = new DateTime($activity->updated_start_time);
   $end_time_datetime = new DateTime($activity->updated_end_time);
   $duration = $end_time_datetime->diff($start_time_datetime)->format("%H") * 60 + $end_time_datetime->diff($start_time_datetime)->format("%i");
   $grid_start_class = 'grid-start-' . $activity->type_rownumber + 2;
   if($filter != 0) {
   if($activity->activity_column == 'FULLDAY' and $activity->type_rownumber >= $group_index) {
       $grid_start_class = 'grid-start-' . $activity->type_rownumber + (int) $group_index;
   } elseif($activity->activity_column == 'FULLDAY') {
       $grid_start_class = 'activity-hide';       
   }
   }
   ?>
    <section class="activity <?php echo $grid_start_class; ?> activity-<?php echo strtolower($activity->activity_column); ?>">
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
<?php if ($activity->explicit) { ?>
        <strong style="color: red;">OBS! Ej lämpat för barn. Åldersgräns 18+</strong>
<?php } ?>
    </section>
   <?php } ?>
  </div><?php
}

?><div class="activities"><?php
if( ! defined("CONVENTION_DATE") OR ( defined("CONVENTION_DATE") AND CONVENTION_DATE == date("Y-m-d") ) OR (isset($_GET["c"]) AND isset($_GET["c"]) == "1" ) OR isset($_GET["t"]) ) {
  ?><h2>Pågående</h2>
  <?php 
  show_activities(1);
  ?><h2>Kommande</h2>
<?php show_activities(2);
} else {
  ?><h2>Schema</h2>
  <?php show_activities(0);
}
?></div><?php
if(isset($_GET["m"]) and $_GET["m"] == 1) { ?>
     <div class="fullday-activities">
     <h3 class="monitor-only-heading">Heldagsaktiviteter</h3><?php
     show_activities(3);
     ?></div><?php
 }

?>
</div>
<div id="links">
<?php if(! isset($_GET["m"]) or $_GET["m"] != 1) { ?>
<a href="https://discord.mangakai.se">Discord</a>
<a href="https://www.mangakai.se/hem/om-mangakai/bli-medlem">Bli medlem</a>
<a href="https://www.mattarikon.se/">Mattarikon</a>
<?php } ?>
<script defer src="index.js"></script>

<?php require("footer.php") ?>

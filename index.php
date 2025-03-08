<?php require("header.php");

function show_activities($filter) {
    $qry = list_activities($filter);
    ?><div class="activity-container">
       <h3 class="desktop-only-heading">Filmer</h3>
       <h3 class="desktop-only-heading">Aktiviteter</h3><?php
    while( $activity = $qry->fetch_object() ) {
   
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
  show_activities(1);
  ?><h2>Kommande</h2>
  <?php show_activities(2);
} else {
  ?><h2>Schema</h2>
  <?php show_activities(0);
}

?>
<div id="links">
<a href="https://discord.mangakai.se">Discord</a>
<a href="https://www.mangakai.se/hem/om-mangakai/bli-medlem">Bli medlem</a>
<a href="https://www.mattarikon.se/">Mattarikon</a>
</div>
<script defer src="index.js"></script>

<?php require("footer.php") ?>

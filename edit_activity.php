<?php require("header.php");

require("classes/Activity.php");

if( ! isset($_SESSION["current_user"])) {
    echo "oh no site broken =/";
} else {

if( isset($_GET["activity_id"] )) {
    $activity = Activity::getActivity($_GET["activity_id"]);
}
    
?>

<form method="POST" action="edit_activity_action.php">
  <input type="hidden" name="activity_id" value="<?php echo isset($activity) ? $activity->id : ""; ?>">
  <fieldset>
    <label for="name">Namn på film/aktiviteten?</label>
    <input id="name" name="name" value="<?php echo isset($activity) ? $activity->name : ""; ?>">
  </fieldset>
  <fieldset>
    <label for="type">Film/Föreläsning?</label>
    <select name="type">
        <option>-- Välj en --</option>
        <?php $activity_type_query = $conn->query("SELECT name, display_name FROM activity_types ORDER BY display_name ASC;");
          while($activity_type = $activity_type_query->fetch_object()) { 
          $selected = "";
          if($activity_type->name == $activity->type) {
             $selected = " selected ";
          } ?>
          <option <?php echo $selected; ?> value="<?php echo $activity_type->name; ?>"><?php echo $activity_type->display_name; ?></option>
        <?php } ?>
    </select>
  </fieldset>
  <fieldset>
      <label for="responsible-staff">Namn på den ansvarige?</label>
      <input id="responsible-staff" name="responsible_staff" value="<?php echo isset($activity) ? $activity->responsible_staff : ""; ?>">
  </fieldset>
  <fieldset>
      <label for="room">Sal/Rum?</label>
      <input id="room" name="room" value="<?php echo isset($activity) ? $activity->room : ""; ?>">
  </fieldset>
  <fieldset>
      <label for="summary">Sammanfattning?</label>
      <textarea id="summary" name="summary"><?php echo isset($activity) ? $activity->summary : ""; ?></textarea>
  </fieldset>
  <fieldset>
      <label for="start-time">Starttid?</label>
      <input id="start-time" name="start_time" value="<?php echo isset($activity) ? $activity->newest_start_time : ""; ?>">
  </fieldset>
  <fieldset>
      <label for="end-time">Sluttid?</label>
      <input id="end-time" name="end_time" value="<?php echo isset($activity) ? $activity->newest_end_time : ""; ?>">
  </fieldset>
  <?php if(isset($activity)) { ?>
  <fieldset>
    <label for="purge-old-times">Ta bort gamla tider?</label>
    <input type="checkbox" id="purge-old-times" name="purge_old_times">
  </fieldset>
  <fieldset>
      <label for="create_notification">Skapa notification om förändringar?</label>
      <input type="checkbox" id="create-notification-checkbox" name="create_notification">
      <textarea disabled name="notification_message"></textarea>
  </fieldset>
  <script src="edit_activity.js"></script>
  <?php } ?>
  <fieldset>
    <button>Spara</button>
  </fieldset>
</form>
<?php

}

require("footer.php"); ?>

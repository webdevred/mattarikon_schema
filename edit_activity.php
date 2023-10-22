<?php require("header.php");

if( ! isset($_SESSION["current_user"])) {
    echo "oh no site broken =/";
} else {

list($activity_id, $name, $type, $responsible_staff, $summary, $room, $start_time, $end_time) = ["", "", "", "", "", "", "08:00", "10:00" ];

if( isset($_GET["activity_id"] )) {
    $stmt = $conn->prepare("SELECT a.id as activity_id, a.name, a.type, a.responsible_staff, a.summary, 
                          room, 
                          TIME_FORMAT(t.start_time, '%H:%i') AS start_Time,
                          TIME_FORMAT(t.end_time, '%H:%i') AS end_time
                          FROM activities AS a
                          LEFT JOIN activities_time_and_place AS t ON a.id = t.activity_id 
                          WHERE a.id = ? ORDER BY t.timestamp LIMIT 1;");
    $stmt->bind_param("i", $_GET["activity_id"]);
    $stmt->execute();
    $stmt->bind_result($activity_id, $name, $type, $responsible_staff, $summary, $room, $start_time, $end_time);
    $stmt->fetch();
    $stmt->close();
}

?>

<form method="POST" action="edit_activity_action.php">
  <input type="hidden" name="activity_id" value="<?php echo $activity_id; ?>">
  <fieldset>
    <label for="name">Namn på film/aktiviteten?</label>
    <input id="name" name="name" value="<?php echo $name; ?>">
  </fieldset>
  <fieldset>
    <label for="type">Film/Föreläsning?</label>
    <select name="type">
        <option>-- Välj en --</option>
        <?php $activity_type_query = $conn->query("SELECT name, display_name FROM activity_types ORDER BY display_name ASC;");
          while($activity_type = $activity_type_query->fetch_object()) { 
          $selected = "";
          if($activity_type->name == $type) {
             $selected = " selected ";
          } ?>
          <option <?php echo $selected; ?> value="<?php echo $activity_type->name; ?>"><?php echo $activity_type->display_name; ?></option>
        <?php } ?>
    </select>
  </fieldset>
  <fieldset>
      <label for="responsible-staff">Namn på den ansvarige?</label>
      <input id="responsible-staff" name="responsible_staff" value="<?php echo $responsible_staff; ?>">
  </fieldset>
  <fieldset>
      <label for="room">Sal/Rum?</label>
      <input id="room" name="room" value="<?php echo $room; ?>">
  </fieldset>
  <fieldset>
      <label for="summary">Sammanfattning?</label>
      <textarea id="summary" name="summary"><?php echo $summary; ?></textarea>
  </fieldset>
  <fieldset>
      <label for="start-time">Starttid?</label>
      <input id="start-time" name="start_time" value="<?php echo $start_time; ?>">
  </fieldset>
  <fieldset>
      <label for="end-time">Sluttid?</label>
      <input id="end-time" name="end_time" value="<?php echo $end_time; ?>">
  </fieldset>
  <?php if(! empty($activity_id)) { ?>
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

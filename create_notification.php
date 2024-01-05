<?php require("header.php");

if( ! isset($_SESSION["current_user"])) {
    echo "oh no site broken =/";
} else { ?>
   <form method="POST" action="create_notification_action.php">
        <fieldset>
            <label for="expiration-time">Sluttid?</label>
            <textarea name="message"></textarea>
        </fieldset>
        <fieldset>
            <label for="expiration-time">Sluttid?</label>
            <input pattern="[0-9]{2}:[0-9]{2}" id="expiration-time" name="expiration_time" value="08:00">
        </fieldset>
   </form>
<?php } 

require("footer.php"); ?>
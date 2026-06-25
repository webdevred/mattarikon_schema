<?php require("header.php"); ?>

<div class="activities"
     id="activities"
     data-logged-in="<?php echo isset($_SESSION["current_user"]) ? '1' : '0'; ?>">
</div>

<?php if(isset($_GET["m"]) and $_GET["m"] == 1) { ?>
     <div class="fullday-activities">
     <h3 class="monitor-only-heading">Heldagsaktiviteter</h3>
     <div id="fullday-activities"></div>
     </div>
<?php } ?>

</div>
<div id="links">
<?php if(! isset($_GET["m"]) or $_GET["m"] != 1) { ?>
<a href="https://discord.mangakai.se">Discord</a>
<a href="https://www.mangakai.se/hem/om-mangakai/bli-medlem">Bli medlem</a>
<a href="https://www.mattarikon.se/">Mattarikon</a>
<?php } ?>
<script defer src="index.js"></script>
<script type="module" src="schedule.js"></script>

<?php require("footer.php") ?>

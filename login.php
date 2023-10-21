<?php

require("header.php");

?> 
<form action="login_action.php" method="POST">
  <input name="username" maxlength="80">
  <input name="password" type="password">
  <button>Logga in</button>
</form>
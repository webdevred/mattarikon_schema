<?php

if($_SERVER['SCRIPT_FILENAME'] ==  __FILE__) {
  die("=(");
}

define("SERVER_HOSTNAME", "${DATABASE_HOSTNAME}");
define("USERNAME", "${DATABASE_USERNAME}");
define("PASSWORD", "${DATABASE_PASSWORD}");
define("DATABASE_NAME", "${DATABASE_NAME}");

define("CONVENTION_DATE", "2025-04-19");

?>

<?php
session_start();
unset($_SESSION['user_id']);

include "../lib/global.inc.php";

redirect_to("../index.php");

?>
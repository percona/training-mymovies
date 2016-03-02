<?php
include "../lib/global.inc.php";

require_valid_user();

$me->add_friend($_GET['id']);
redirect_to("home.php");

?>
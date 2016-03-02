<?php
include "../lib/global.inc.php";

require_valid_user();

$me->add_rating($_GET['id'], $_GET['vote']);
redirect_to("../movie.php?id=".$_GET['id']);

?>
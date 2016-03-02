<?php
include "../lib/global.inc.php";

require_valid_user();

$me->remove_favorite_movie($_GET['id']);
redirect_to("../movie.php?id=".$_GET['id']);

?>
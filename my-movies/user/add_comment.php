<?php
include "../lib/global.inc.php";

require_valid_user();

$me->add_comment($_POST['type'], $_POST['id'], $_POST['comment']);

if ($_POST['type']=='actor') {
	redirect_to("../actor.php?id=$_POST[id]");
} else {
	redirect_to("../movie.php?id=$_POST[id]");
}

?>
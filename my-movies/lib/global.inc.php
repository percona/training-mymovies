<?php

include "config.inc.php";

include "template.inc.php";
include "classes/movie.class.php";
include "classes/actor.class.php";
include "classes/character.class.php";
include "classes/user.class.php";
include "common.inc.php";
include "instrumentation.php";

Instrumentation::get_instance()->start_request(true);

$conn = new MySQLi_perf(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

if (isset($_SESSION['user_id']))
{
	$me = new user($_SESSION['user_id']);
	$me->update_last_login_date();		
}

?>

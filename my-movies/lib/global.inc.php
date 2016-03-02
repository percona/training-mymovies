<?php

include "template.inc.php";
include "classes/movie.class.php";
include "classes/actor.class.php";
include "classes/character.class.php";
include "classes/user.class.php";
include "common.inc.php";
include "config.inc.php";
include "instrumentation.php";

Instrumentation::get_instance()->start_request(true);

MySQL_perf::mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, true);
mysql_select_db(MYSQL_DATABASE);
MySQL_perf::mysql_query("SET NAMES utf8");

if (isset($_SESSION['user_id']))
{
	$me = new user($_SESSION['user_id']);
	$me->update_last_login_date();		
}

?>

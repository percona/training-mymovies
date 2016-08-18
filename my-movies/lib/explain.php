<?php

include "global.inc.php";

/*
 This is just a very dumb utility that plugs in with the end
 of page debugging feature to find the execution plan of a
 query that ran.  It's not going to be used in production.
*/

$result = mysql_query_wrapper("EXPLAIN " . $_GET['query']);

print "<pre>";

while($row = $result->fetch_assoc())
{
	print_r($row);
}

print "</pre>";

?>
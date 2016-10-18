<?php

function get_number_of_users()
{
	$result = mysql_query_wrapper("SELECT COUNT(*) AS c FROM users");
	$count = $result->fetch_row();
	
	return $count[0];
}

function get_number_of_movies()
{
	$result = mysql_query_wrapper("SELECT COUNT(*) AS c FROM title");
	$count = $result->fetch_row();
	
	return $count[0];
}

function get_number_of_actors()
{
	$result = mysql_query_wrapper("SELECT COUNT(*) AS c FROM name");
	$count = $result->fetch_row();
	
	return $count[0];
}

function get_random_movie()
{
	$result = mysql_query_wrapper("SELECT * FROM title WHERE title != '' AND kind_id = 1 ORDER BY RAND() LIMIT 1");
	
	return $result->fetch_assoc();
}

function get_random_actor()
{
	$result = mysql_query_wrapper("SELECT * FROM name ORDER BY RAND() LIMIT 1");
	
	return $result->fetch_assoc();
}

function get_random_user()
{
	$result = mysql_query_wrapper("SELECT * FROM users ORDER BY RAND() LIMIT 1");
	
	return $result->fetch_assoc();
}

function redirect_to($url)
{
	header("Location: $url");
}

function get_comments()
{
	$return = array();
	$result = mysql_query_wrapper("SELECT * FROM comments ORDER BY id DESC limit 10");
	
	while($row = $result->fetch_assoc())
	{
		$return[] = $row;
	}
	
	return $return;
}

function get_being_viewed($limit = 5)
{
	$return = array();
	$result = mysql_query_wrapper("SELECT DISTINCT type, viewed_id FROM page_views ORDER BY viewed_id DESC LIMIT $limit");
	
	while($row = $result->fetch_assoc())
	{
		$return[] = $row;
	}
	
	return $return;
}

function get_users_online()
{
	$return = array();
	$result = mysql_query_wrapper("SELECT * FROM users WHERE last_login_date > NOW()-INTERVAL 10 MINUTE ORDER BY last_login_date DESC LIMIT 10");
	
	while($row = $result->fetch_assoc())
	{
		$return[] = $row['id'];
	}
	
	return $return;
}

function update_page_views($type, $id)
{
	global $me;
	
	$my_id = is_object($me) ? $me->id : 0;
	
	mysql_query_wrapper("INSERT INTO page_views (type, viewed_id, viewed, user_id)
		VALUES ('$type', $id, NOW(), $my_id)") or mysql_error();
}

function require_valid_user($fail_location = BASE_URI)
{
	if (!is_logged_in())
	{
		redirect_to($fail_location);
	}
}

function is_logged_in()
{
	global $me;
	
	return isset($me);
}

function h($text)
{
	return htmlentities($text, ENT_QUOTES,"UTF-8");
}

function create_new_user($first_name, $last_name, $email)
{
	global $conn
	
	mysql_query_wrapper(sprintf("INSERT INTO users (first_name, last_name, email_address)
		VALUES ('%s', '%s', '%s')",
		$conn->escape_string($first_name),
		$conn->escape_string($last_name),
		$conn->escape_string($email)
	));
	
	return $conn->insert_id;
}

function mysql_query_wrapper($query)
{
	global $conn, $__queries;
	
	/*
	 This is a very light-weight wrapper around mysql_query() that
	 understands:
	 
	 - The performance instrumentation class
	 - Writing to a debug log if a query was invalid.
	 - Logging all queries.  If debug mode is on, the logged
	   queries display at the end of the page.
	
	*/
	
	if (!isset($__queries)) $__queries = array();
	
	$__queries[] = $query;
	
	$return = MySQLi_perf::mysqli_query($conn, $query);
	
	if (!$return)
	{
		debug_write($query);
	}
	
	return $return;
}

function debug_write($string)
{
	if (DEBUG_MODE)
	{
		print $string;
	}
	else
	{
		error_log("mymovies: " . $string);
	}
}

?>

<?php

function start_template($title = false)
{
	global $me;
	
	if (!$title)
	{
		$title = 'Welcome to MyMovies';
	}
	
	print '<html>
<head><title>'.$title.'</title>
<link rel="stylesheet" type="text/css" href="'.BASE_URI.'css/reset.css"/>
<link rel="stylesheet" type="text/css" href="'.BASE_URI.'css/main.css"/>
<body>
<div id="login">';

	if (isset($me))
	{
		print "You are logged in as $me->email_address.  [<a id='user_home' href=\"".BASE_URI."user/home.php\">Home</a>] [<a id='user_logout' href=\"".BASE_URI."user/logout.php\">Logout</a>]";

	}
	else
	{
		print "<a id='user_login' href=\"".BASE_URI."user/login.php\">Login</a> <a id='user_signup' href=\"".BASE_URI."user/signup.php\">Signup</a>";
	}

print '</div> <!-- login -->
<div id="main">
<div id="search">
<form action="'.BASE_URI.'search.php">
<select name="search_type">
 <option value="actors">Search: Actors</option>
 <option value="movies">Search: Movies</option>
 <option value="characters">Search: Characters</option>
 <option value="users">Search: Users</option>
</select> <input type="text" name="q" value="" /> <input type="submit" value="Go!" />
</form>
</div>
<h1><a id="home_logo" href="'.BASE_URI.'">MyMovies</a></h1>
';
}

function end_template()
{
	global $__queries;
	
	print "<div style='clear: both'></div>
	</div> <!-- main -->";
	
	if (DEBUG_MODE)
	{
		print Instrumentation::get_instance()->dump_counters();
		
		if (is_array($__queries))
		{
			print "<pre>";
			
			foreach($__queries as $query)
			{
				print $query . " <a href='".BASE_URI."lib/explain.php?query=".urlencode($query)."'>EXPLAIN</a><br /><br />";
			}
			
			print "</pre>";
		}
	}
	
	print "</body></html>";
}

?>
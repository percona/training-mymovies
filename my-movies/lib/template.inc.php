<?php

function start_template()
{
	global $me;
	print '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Movies</title>
  <link rel="stylesheet" type="text/css" href="'.BASE_URI.'css/bootstrap-cosmo.min.css"/>
</head>
<body>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="/my-movies/index.php">My Movies</a>
    </div>

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

      <form class="navbar-form navbar-left" action="'.BASE_URI.'search.php">
        <div class="form-group">
          <select name="search_type">
            <option value="actors">Search: Actors</option>
            <option value="movies">Search: Movies</option>
            <option value="characters">Search: Characters</option>
            <option value="users">Search: Users</option>
          </select>
        </div>
        <input type="text" name="q" value="" />
        <button type="submit" class="btn btn-default">Go</button>
      </form>

      <ul class="nav navbar-nav navbar-right">
';

	if (isset($me))
        {
	    print str_repeat(" ", 8) . "<li><p class=\"navbar-text\">Logged in as <a href=\"".BASE_URI."user/home.php\" class=\"navbar-link\">$me->email_address</a></p></li>\n";
	    print str_repeat(" ", 8) . "<li><a href=\"".BASE_URI."user/home.php\">Home</a></li>\n";
            print str_repeat(" ", 8) . "<li><a href=\"".BASE_URI."user/logout.php\">Logout</a></li>\n";
        }
        else
        {
            print str_repeat(" ", 8) . "<li><a href=\"".BASE_URI."user/login.php\">Login</a></li>";
            print str_repeat(" ", 8) . "<li><a href=\"".BASE_URI."user/signup.php\">Signup</a></li>";
        }

print '      </ul>

    </div>
  </div>
</nav>

<div class="container">
  <h1>My Movies</h1>
  <hr/>
';
}

function end_template()
{
	global $__queries;
	
	if (DEBUG_MODE)
	{
		print "<!-- DEBUGING -->\n<hr/>\n";
		print "<h4>Stats</h4>\n";
		print Instrumentation::get_instance()->dump_counters();
		
		if (is_array($__queries))
		{
			print "<br/><h4>Queries:</h4><pre>";
			
			foreach($__queries as $query)
			{
				print $query . " <a href='".BASE_URI."lib/explain.php?query=".urlencode($query)."'>EXPLAIN</a><br/><br/>";
			}
			
			print "</pre>";
		}
		
		print "\n<!-- END DEBUGGING -->\n";
	}
	
	print "\n<hr/>\n";
	print "<p class=\"text-center\">My Movies &copy; 2017 - Acme Co.</p>\n";
	print "</div><!-- container -->\n";
	print "</body>\n</html>";
}

?>

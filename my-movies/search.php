<?php

include "lib/global.inc.php";
start_template();

$term = mysqli_real_escape_string($conn, $_GET['q']);
$search_type = $_GET['search_type'];

?>

<div class="row">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading"><h2>Returning Search Results for "<?php echo h($_GET['q'])?>"</h2></div>
      <div class="panel-body">
<?php 
	if ($search_type == 'movies')
	{
		print str_repeat(" ", 8) . "<h3>Movies</h3>\n";

		$result = mysql_query_wrapper("SELECT * FROM title WHERE title LIKE '$term%' AND kind_id = 1 LIMIT 100");
		if ($result->num_rows == 0)
		{
			print str_repeat(" ", 8) . "<p><em>No Results</em></p>\n";
		}
		else
		{
			while($row = $result->fetch_assoc())
			{
				$movie = new movie($row['id']);
				print str_repeat(" ", 8) . "<p><a href='".$movie->link()."'>$movie->title</a> ($movie->production_year)</p>\n";
			}
		}
	}
	elseif ($search_type == 'actors')
	{ 
		print str_repeat(" ", 8) . "<h3>Actors</h3>\n";

		$result = mysql_query_wrapper("SELECT * FROM name WHERE name LIKE '$term%'");
		if ($result->num_rows == 0)
		{
			print str_repeat(" ", 8) . "<p><em>No Results</em></p>\n";
		}
		else
		{
			while($row = $result->fetch_assoc())
			{
				$actor = new actor($row['id']);
				print str_repeat(" ", 8) . "<p><a href='".$actor->link()."'>$actor->name</a></p>\n";
			}
		}
	}
	elseif($search_type == 'characters')
	{
		print str_repeat(" ", 8) . "<h3>Characters</h3>\n";

		$result = mysql_query_wrapper("SELECT * FROM char_name WHERE name LIKE '$term%' LIMIT 100");
		if ($result->num_rows == 0)
		{
			print str_repeat(" ", 8) . "<p><em>No Results</em></p>\n";
		}
		else
		{
			while($row = $result->fetch_assoc())
			{
				$character = new character($row['id']);
				print str_repeat(" ", 8) . "<p><a href='character.php?id=$character->id'>$character->name</a></p>\n";
			}
		}
	}
	elseif($search_type=='users')
	{
		print str_repeat(" ", 8) . "<h3>Users</h3>\n";

		$result = mysql_query_wrapper("SELECT * FROM users WHERE first_name = '$term' OR last_name = '$term' OR email_address = '$term' LIMIT 100");
		if ($result->num_rows == 0)
		{
			print str_repeat(" ", 8) . "<p><em>No Results</em></p>\n";
		}
		else
		{
			while($row = $result->fetch_assoc())
			{
				$user = new user($row['id']);
				print str_repeat(" ", 8) . "<p><a href='".$user->link()."'>$user->email_address</a></p>\n";
			}
		}
	}
?>
      </div>
    </div>
  </div>
</div>

<?php
end_template();
?>

<?php

/* @TODO: Write pagination function */

include "lib/global.inc.php";
start_template();

$term		 = mysqli_real_escape_string($conn, $_GET['q']);
$search_type = $_GET['search_type'];

?>

<h2>Returning Search Results for "<?php echo h($_GET['q'])?>"</h2>

<?php if ($search_type == 'movies') { ?>

<h3>Movies</h3>
<?php

$result = mysql_query_wrapper("SELECT * FROM title WHERE title LIKE '$term%' AND kind_id = 1 LIMIT 100");

if ($result->num_rows == 0)
{
	print "<i>No results</i>";
}
else
{
	while($row = $result->fetch_assoc())
	{
		$movie = new movie($row['id']);
		print "<a href='".$movie->link()."'>$movie->title</a> ($movie->production_year)<br />";
	}
}

}
elseif ($search_type == 'actors')
{ ?>

<h3>Actors</h3>

<?php

$result = mysql_query_wrapper("SELECT * FROM name WHERE name LIKE '$term%'");

if ($result->num_rows == 0)
{
	print "<i>No results</i>";
}
else
{
	while($row = $result->fetch_assoc())
	{
		$actor = new actor($row['id']);
		print "<a href='".$actor->link()."'>$actor->name</a><br />";
	}
}

}
elseif($search_type == 'characters')
{ ?>

<h3>Characters</h3>

<?php

$result = mysql_query_wrapper("SELECT * FROM char_name WHERE name LIKE '$term%' LIMIT 100");

if ($result->num_rows == 0)
{
	print "<i>No results</i>";
}
else
{
	while($row = $result->fetch_assoc())
	{
		$character = new character($row['id']);
		print "<a href='character.php?id=$character->id'>$character->name</a><br />";
	}
}

}
elseif($search_type=='users')
{ ?>

<h3>Users</h3>

<?php

	$result = mysql_query_wrapper("SELECT * FROM users WHERE first_name = '$term' 
	OR last_name = '$term' OR email_address = '$term' LIMIT 100");

	if ($result->num_rows == 0)
	{
		print "<i>No results</i>";
	}
	else
	{
		while($row = $result->fetch_assoc())
		{
			$user = new user($row['id']);
			print "<a href='".$user->link()."'>$user->email_address</a><br />";
		}
	}
}

end_template();

?>
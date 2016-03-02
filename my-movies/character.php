<?php

include "lib/global.inc.php";

start_template();

$character = new character($_GET['id']);
update_page_views('character', $character->id);

?>
<h1><?php echo $character->name; ?></h1>
<h2>Movies with this character</h2>

<?php

foreach($character->movies() as $row)
{
	$movie = new movie($row['movie_id']);
	$actor = new actor($row['person_id']);
	print "<div style='width: 300px; clear: both; float: left'><a id='credits_$i' href='".$movie->link()."'>$movie->title</a> ($movie->production_year)</div><div style='float: left;'>played by <a href='".$actor->link()."'>".$actor->name ."</a></div>";
}

end_template();

?>
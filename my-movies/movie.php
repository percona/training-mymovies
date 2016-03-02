<?php

include "lib/global.inc.php";
start_template();

$movie = new movie($_GET['id']);
update_page_views('movie', $movie->id);
$movie->update_info();

if (is_logged_in()) {

	if (!$me->is_favorite_movie($movie->id)) {
		print "<div id='favourite_movie' style='float: right'><a id='favourite_1' href='user/add_favorite_movie.php?id=$movie->id'>Add as favourite!</a></div>";
	} else {
		print "<div id='favourite_movie' style='float: right'><a id='favourite_1' href='user/remove_favorite_movie.php?id=$movie->id'>Remove as favourite!</a></div>";
	}	
}

?>


<h2><?php echo $movie->title?> (<?php echo $movie->production_year?>)</h2>
Runtime: <?php echo $movie->info[1][0]?> minutes<br />
Genre: <?php echo $movie->info[3][0]?><br />
Language(s): <?php echo $movie->info[4][0]?><br />
Gross: <?php echo $movie->info[107][0]?><br />
Rating: <?php echo $movie->info[97][0]?><br />
Average Score: <?php if (!$movie->average_score()) { echo "<i>Not enough votes</i>"; } else { echo $movie->average_score(); } ?><br />

<h3>Rate this movie</h3>
<?php

if (is_logged_in()) {

	if ($me->have_rated($movie->id)) {
		print "<i>You have already voted.</i>";
	} else {
		for ($i=1;$i<=10;$i++) {
			print "<a href='user/add_movie_rating.php?id=$movie->id&vote=$i'>$i</a>&nbsp;";
		}
	}
	
} else {
	print "<i>You are not logged in</i>";
}
?>
<div id="cast" style="clear: both; padding-top: 10px;">

<h3>Cast</h3>

<?php
$i=0;
foreach($movie->cast() as $cast) {
	$i++;
	
	$actor 		= new actor($cast['person_id']);
	$character  = new character($cast['person_role_id']);

	if (!$character->id) {
		
		/*
		 There is no "character" for this role, aka
		 "Unknown".  Show the same row, but don't make
		 it a link.
		*/

	print "<div style='width: 300px; clear: both; float: left'><a id='actor_$i' href=\"".$actor->link().
	"\">$actor->name</a></div>" . "<div style='float: left;'> <i>as</i> $character->name</div>";
	
	} else {
	
	print "<div style='width: 300px; clear: both; float: left'><a id='actor_$i' href=\"".$actor->link().
	"\">$actor->name</a></div>" . "<div style='float: left;'> <i>as</i> <a href='".$character->link()."'>$character->name</a></div>";

	}


}

?>
</div>
<div id="comments" style="clear: both; padding-top: 10px;">
<h3>Comments <a id='add_comment' href="user/comment.php?id=<?php echo $movie->id?>&type=movie">[+]</a></h3>

<?php
$comments = $movie->comments();

if (empty($comments)) {
	print "<i>No comments</i>";
} else {
	$i=0;
	foreach($comments as $comment) {
		$i++;
		$user = new user($comment['user_id']);
		print "<a id='comment_$i' href='".$user->link()."'>$user->email_address</a> wrote: $comment[comment]<hr />";
	}
}
?>

</div>
<?php end_template(); ?>
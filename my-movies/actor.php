<?php

include "lib/global.inc.php";
start_template();

$actor = new actor($_GET['id']);
update_page_views('actor', $actor->id);
$actor->update_info();

if (is_logged_in()) {

	if (!$me->is_favorite_actor($actor->id)) {
		print "<div id='favourite_movie' style='float: right'><a id='favourite_1' href='user/add_favorite_actor.php?id=$actor->id'>Add as favourite!</a></div>";
	} else {
		print "<div id='favourite_movie' style='float: right'><a id='favourite_1' href='user/remove_favorite_actor.php?id=$actor->id'>Remove as favourite!</a></div>";
	}	
}

?>
<h2><?php echo $actor->name?></h2>
Date of Birth: <?php echo $actor->info[21][0]?><br />
Born: <?php echo $actor->info[26][0]?><br />
Height: <?php echo $actor->info[22][0]?><br />

<?php echo substr($actor->info[19][0], 0, 500)?>...<br />
<div id="credits">
<h3>Credits</h3>
<?php
$i=0;
foreach($actor->credits() as $movie_in) {
	$i++;
	$movie = new movie($movie_in['movie_id']);
	
	if ($movie->kind_id != 1)
		continue;
	
	$character = new character($movie_in['person_role_id']);

	if (!$character->id) {
		
		/*
		 There is no "character" for this role, aka
		 "Unknown".  Show the same row, but don't make
		 it a link.
		*/

		print "<div style='width: 300px; clear: both; float: left'><a id='credits_$i' href='".$movie->link()."'>$movie->title</a> ($movie->production_year)</div><div style='float: left;'>as ".$character->name."</div>";
	
	} else {
	
		print "<div style='width: 300px; clear: both; float: left'><a 
		id='credits_$i' href='".$movie->link()."'>$movie->title</a> ($movie->production_year)</div><div 
		style='float: left;'>as <a href='".$character->link()."'>$character->name</a></div>";

	}
}

?>
</div>
<div style="clear: both"></div>
<div id="comments" style="clear: both; padding-top: 10px;">
<h3>Comments <a id='add_comment' href="user/comment.php?id=<?php echo $actor->id?>&type=actor">[+]</a></h3>

<?php
$comments = $actor->comments();

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
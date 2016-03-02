<?php
include "../lib/global.inc.php";

require_valid_user();
start_template();

?>

<h1>Home</h1>
<h2>Comments</h2>

<?php 

foreach($me->comments() as $comment) {
	
	if ($comment['type']=='movie') {
	
		$movie = new movie($comment['type_id']);
		print "You commented on <a href='".$movie->link()."'>".$movie->title."</a><br />";		
	
	} elseif ($comment['type']=='actor') {

		$actor = new actor($comment['type_id']);
		print "You commented on <a href='".$actor->link()."'>".$actor->name."</a><br />";		
	
	}

}
?>
<h2>Ratings</h2>
<?php

foreach($me->ratings() as $rating) {
	
	$movie = new movie($rating['movie_id']);
	print "You voted $rating[rating] for <a href='".$movie->link()."'>" . $movie->title . "</a><br />";
}
?>
<h2>Favorite Actors</h2>

<?php 

foreach($me->favorite_actors() as $favorite) {

	$actor = new actor($favorite);
	print "<a href='".$actor->link()."'>".$actor->name."</a><br />";

}

?>
<h2>Favorite Movies</h2>

<?php 

foreach($me->favorite_movies() as $favorite) {
	
	$movie = new movie($favorite);
	print "<a href='".$movie->link()."'>".$movie->title."</a><br />";

}

?>

<h2>Friends</h2>

<?php

foreach($me->friends() as $user_id) {
	
	$user = new user($user_id);
	print "<a href='".$user->link()."' title=\"$user->email_address\">$user->email_address</a><br />";

}
?>

<?php end_template(); ?>

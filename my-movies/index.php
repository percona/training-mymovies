<?php

include "lib/global.inc.php";
start_template();

?>

<p>There are <?php echo get_number_of_users(); ?> users, <?php echo get_number_of_movies(); ?> movies
and <?php echo get_number_of_actors(); ?> actors in the system.</p>

<div class="box" id="random_movie">
	<h3>Random Movie</h3>
	<?php 
	$movie = get_random_movie();
	$movie = new movie($movie['id']);
	?>
	<a id='random_movie_1' href="movie.php?id=<?php echo $movie->id?>"><?php echo h($movie->title)?></a> (<?php echo $movie->production_year?>)
</div>

<div class="box" id="comments">
	<h3>Latest comments</h3>

<?php 
$i=0;
foreach(get_comments() as $comment) {
	$i++;

	$user = new user($comment['user_id']);
	$movie = new movie($comment['type_id']);
	print "<a id='comments_$i' href=\"".$user->link()."\">$user->email_address</a> commented on <a id='movie_comment_$i' href='".$movie->link()."'>".$movie->title."</a><br />";

}

?>

</div>

<div class="box" id="featured_users">
	<h3>Featured User</h3>
	<?php

	$user = get_random_user();
	$user = new user($user['id']);
	print "<a id='featured_users_1' href='".$user->link()."' title=\"$user->email_address\">$user->email_address</a><br />";

	?>
	
</div>

<div class="box" id="online_now">

<h3>Online right now</h3>
<?php 
$i=0;
foreach(get_users_online() as $user_id) {
	$i++;
	$user = new user($user_id);
	print "<a id='online_now_$i' href='".$user->link()."' title=\"$user->email_address\">$user->email_address</a><br />";

}

?>
</div>

<div class="box" id="recently_viewed">

<h3>Being viewed right now</h3>
<ul>
<?php
$i=0;
foreach(get_being_viewed() as $item) {
$i++;

	switch($item['type']) {
		case 'movie':
			$movie = new movie($item['viewed_id']);
			print "<li><a id='recently_viewed_$i' href=\"movie.php?id=$movie->id\">". h($movie->title) . "</a> ($movie->production_year)</li>\n";
		break;
		case 'actor':
			$actor = new actor($item['viewed_id']);
			print "<li><a id='recently_viewed_$i' href=\"actor.php?id=$actor->id\">". h($actor->name) . "</a></li>\n";
		break;
		default:
			print "<li><i>unsure</i></li>";
		break;
	}

}
?>
</ul>

</div>

<?php end_template(); ?>
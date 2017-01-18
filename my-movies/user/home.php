<?php

include "../lib/global.inc.php";

require_valid_user(BASE_URI . 'user/login.php');

start_template();

?>

<div class="row">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading"><h2>Home - <?= $me->get_display_name()?></h2></div>
      <div class="panel-body">

        <div class="row">
          <div class="col-lg-4">
            <div class="list-group">
              <h3>Comments</h3>
<?php
	$comments = $me->comments();
	if (empty($comments))
	{
		print str_repeat(" ", 14) . "<p><em>No Comments</em></p>\n";
	}
	else
	{
		foreach($comments as $comment)
		{
			if ($comment['type'] == 'movie')
			{
				$movie = new movie($comment['type_id']);
				print str_repeat(" ", 14) . "<p>You commented on <a href='".$movie->link()."'>".$movie->title."</a></p>\n";
			}
			elseif ($comment['type'] == 'actor')
			{
				$actor = new actor($comment['type_id']);
				print str_repeat(" ", 14) . "<p>You commented on <a href='".$actor->link()."'>".$actor->name."</a></p>\n";
			}
		}
	}
?>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="list-group">
              <h3>Ratings</h3>
<?php
	$ratings = $me->ratings();
	if (empty($ratings))
	{
		print str_repeat(" ", 14) . "<p><em>No Ratings</em></p>\n";
	}
	else
	{
		foreach($ratings as $rating)
		{
			$movie = new movie($rating['movie_id']);
			print str_repeat(" ", 14) . "<p>You voted $rating[rating] for <a href='".$movie->link()."'>" . $movie->title . "</a></p>\n";
		}
	}
?>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="list-group">
              <h3>Favorite Actors</h3>
<?php 
	$favActors = $me->favorite_actors();
	if (empty($favActors))
	{
		print str_repeat(" ", 14) . "<p><em>No Favorites</em></p>\n";
	}
	else
	{
		foreach($favActors as $favorite)
		{
			$actor = new actor($favorite);
			print str_repeat(" ", 14) . "<p><a href='".$actor->link()."'>".$actor->name."</a></p>\n";
		}
	}
?>
            </div>
          </div>
        </div><!-- row -->

        <div class="row">
          <div class="col-lg-4">
            <div class="list-group">
              <h3>Favorite Movies</h3>
<?php 
	$favMovies = $me->favorite_movies();
	if (empty($favMovies))
	{
		print str_repeat(" ", 14) . "<p><em>No Favorites</em></p>\n";
	}
	else
	{
		foreach($favMovies as $favorite)
		{
			$movie = new movie($favorite);
			print str_repeat(" ", 14) . "<p><a href='".$movie->link()."'>".$movie->title."</a></p>\n";
		}
	}
?>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="list-group">
              <h3>Friends</h3>
<?php
	$friends = $me->friends();
	if (empty($friends))
	{
		print str_repeat(" ", 14) . "<p><em>No Friends</em></p>\n";
	}
	else
	{
		foreach($me->friends() as $user_id)
		{
			$user = new user($user_id);
			print str_repeat(" ", 14) . "<p><a href='".$user->link()."' title=\"$user->email_address\">$user->email_address</a></p>\n";
		}
	}
?>
            </div>
          </div>
        </div><!-- row -->

      </div>
    </div>
  </div>
</div>

<?php
end_template();
?>

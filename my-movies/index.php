<?php

include "lib/global.inc.php";
start_template();

?>

<div class="row">
  <div class="col-lg-12">
    <p>There are <?= number_format(get_number_of_users()); ?> users, <?= number_format(get_number_of_movies()); ?> movies and <?= number_format(get_number_of_actors()); ?> actors in the system.</p>
  </div>
</div>

<div class="row">
  <div class="col-lg-12"">

    <div class="row">
      <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">Featured Movie</div>
          <div class="panel-body">
<?php
	$movie = get_random_movie();
	$movie = new movie($movie['id']);
	print str_repeat(" ", 10) . "<p><a href=\"movie.php?id=$movie->id\">".h($movie->title)."</a> ($movie->production_year)</p>\n";
?>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">Latest Comments</div>
          <div class="panel-body">
<?php
	$comments = get_comments();
	if (empty($comments))
	{
		print "<p><em>No Comments</em></p>\n";
	}
	else
	{
		foreach($comments as $comment)
		{
			$user = new user($comment['user_id']);
			$movie = new movie($comment['type_id']);
			print str_repeat(" ", 10) . "<p><a href=\"".$user->link()."\">$user->email_address</a> commented on <a href='".$movie->link()."'>".$movie->title."</a></p>\n";
		}
	}
?>
          </div>
        </div>
      </div>      
    </div>

    <div class="row">
      <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">Online Right Now</div>
          <div class="panel-body">
<?php
	$users = get_users_online();
	if (empty($users))
	{
		print str_repeat(" ", 10) . "<p><em>Nobody Online</em></p>\n";
	}
	else
	{
		foreach(get_users_online() as $user_id)
		{
			$user = new user($user_id);
			print str_repeat(" ", 10) . "<p><a href='".$user->link()."' title=\"$user->email_address\">$user->email_address</a></p>\n";
		}
	}
?>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">Featured User</div>
          <div class="panel-body">
<?php
	$user = get_random_user();
	$user = new user($user['id']);
	print str_repeat(" ", 10) . "<p><a href='".$user->link()."' title=\"$user->email_address\">$user->email_address</a></p>\n";
?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="row">
  <div class="col-lg-6">

    <div class="panel panel-default">
      <div class="panel-heading">Being Viewed Right Now</div>
      <div class="panel-body">
<?php
	$viewed = get_being_viewed();
	if (empty($viewed))
	{
		print str_repeat(" ", 8) . "<p><em>Nothing Being Viewed</em></p>\n";
	}
	else
	{
		print str_repeat(" ", 8) . "<ul>\n";
		foreach(get_being_viewed() as $item)
		{
                        switch($item['type'])
                        {
                               	case 'movie':
                                        $movie = new movie($item['viewed_id']);
                                       	print str_repeat(" ", 10) . "<li><a href=\"movie.php?id=$movie->id\">". h($movie->title) . "</a> ($movie->production_year)</li>\n";
                                       	break;
                                case 'actor':
                                       	$actor = new actor($item['viewed_id']);
                                       	print str_repeat(" ", 10) . "<li><a href=\"actor.php?id=$actor->id\">". h($actor->name) . "</a></li>\n";
                                       	break;
                               	default:
                                       	print str_repeat(" ", 10) . "<li><em>Unsure</em></li>\n";
                                       	break;
                        }
                }
		print str_repeat(" ", 8) . "</ul>\n";
        }
?>
      </div>
    </div>

  </div>
</div>

<?php
end_template();
?>

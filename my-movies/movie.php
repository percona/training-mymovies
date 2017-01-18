<?php

include "lib/global.inc.php";
start_template();

$movie = new movie($_GET['id']);
update_page_views('movie', $movie->id);
$movie->update_info();

?>

<div class="row">
  <div class="col-lg-12">
    <div class="bs-component">
      <div class="panel panel-default">
        <div class="panel-heading"><h2><?php echo $movie->title?> (<?php echo $movie->production_year?>)</h2></div>
        <div class="panel-body">

          <div class="row">

            <div class="col-lg-4">
              <p><strong>Runtime:</strong> <?php echo $movie->info[1][0]?> minutes</p>
              <p><strong>Genre:</strong> <?php echo $movie->info[3][0]?></p>
              <p><strong>Language(s):</strong> <?php echo $movie->info[4][0]?></p>
              <p><strong>Gross:</strong> <?php echo $movie->info[107][0]?></p>
              <p><strong>Rating:</strong> <?php echo $movie->info[97][0]?></p>
              <p><strong>Average Score:</strong> <?php if (!$movie->average_score()) { echo "<i>Not enough votes</i>"; } else { echo $movie->average_score(); } ?></p>
            </div>

            <div class="col-lg-4">
              <h3>Rate this movie</h3>
<?php
		if (is_logged_in())
		{
			if ($me->have_rated($movie->id))
				print str_repeat(" ", 14) . "<p><em>You have already voted.</em></p>\n";
			else
			{
				print str_repeat(" ", 14) . "<p>\n";
				for ($i = 1; $i <= 10; $i++)
				{
					print "<a href='user/add_movie_rating.php?id=$movie->id&vote=$i'>$i</a>&nbsp;&nbsp;&nbsp;";
				}
				print "</p>\n";
			}

			if (!$me->is_favorite_movie($movie->id))
				print str_repeat(" ", 14) . "<p><a href='user/add_favorite_movie.php?id=$movie->id'>Add as favorite!</a></p>\n";
			else
				print str_repeat(" ", 14) . "<p><a href='user/remove_favorite_movie.php?id=$movie->id'>Remove as favorite!</a></p>\n";
		}
		else
			print "<p><em>You are not logged in</em></p>";
?>
            </div>

            <div class="col-lg-4">
              <h3>Cast</h3>
<?php
		foreach($movie->cast() as $cast)
		{
			$actor = new actor($cast['person_id']);
			$character = new character($cast['person_role_id']);
			
			if (!$character->id)
				print str_repeat(" ", 14) . "<p><a href=\"".$actor->link()."\">$actor->name</a>&nbsp;<em>as</em>&nbsp;$character->name</p>\n";
			else
				print str_repeat(" ", 14) . "<p><a href=\"".$actor->link()."\">$actor->name</a>&nbsp;<em>as</em>&nbsp;<a href='".$character->link()."'>$character->name</a></p>\n";
		}
?>
            </div>

          </div><!-- close row -->

          <div class="row col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading"><h4>Comments <a href="user/comment.php?id=<?php echo $movie->id?>&type=movie">[+]</a></h4></div>
              <div class="panel-body">
<?php
		$comments = $movie->comments();

		if (empty($comments))
			print str_repeat(" ", 16) . "<p><em>No comments</em></p>\n";
		else
		{
			foreach($comments as $comment)
			{
				$user = new user($comment['user_id']);
				print str_repeat(" ", 16) . "<p><a href='".$user->link()."'>$user->email_address</a> wrote: $comment[comment]</p>\n";
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
</div>

<?php
end_template();
?>

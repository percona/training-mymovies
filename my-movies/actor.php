<?php

include "lib/global.inc.php";
start_template();

$actor = new actor($_GET['id']);
update_page_views('actor', $actor->id);
$actor->update_info();

$a_sex = ($actor->gender == 'f' ? "Actress" : "Actor");

?>

<div class="row">
  <div class="col-lg-12">
    <div class="bs-component">
      <div class="panel panel-default">
        <div class="panel-heading"><h2><?= $a_sex?> - <?= $actor->name?></h2></div>
        <div class="panel-body">

          <div class="row">
            <div class="col-lg-10">
              <p><strong>Date of Birth:</strong> <?= $actor->info[21][0]?></p>
              <p><strong>Birth Name:</strong> <?= $actor->info[26][0]?></p>
              <p><strong>Height:</strong> <?= $actor->info[22][0]?></p>
            </div>
            <div class="col-lg-2">
<?php
		if (is_logged_in())
		{
			if (!$me->is_favorite_actor($actor->id))
				print str_repeat(" ", 14) . "<a href='user/add_favorite_actor.php?id=$actor->id'>Add as favorite!</a>\n";
			else
				print str_repeat(" ", 14) . "<a href='user/remove_favorite_actor.php?id=$actor->id'>Remove as favorite!</a>\n";
		}
?>
            </div>
          </div><!-- row -->

          <div class="row">
            <div class="col-lg-12">
              <p><strong>Info:</strong> <?= $actor->info[19][0]?></p>
            </div>
          </div><!-- row -->

          <div class="row">
            <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="panel-heading"><h4>Comments <a href="user/comment.php?id=<?= $actor->id?>&type=actor">[+]</a></h4></div>
                <div class="panel-body">
<?php
		$comments = $actor->comments();
		if (empty($comments))
			print "<i>No comments</i>";
		else
		{
			foreach($comments as $comment)
			{
				$user = new user($comment['user_id']);
				print str_repeat(" ", 18) . "<p><a href='".$user->link()."'>$user->email_address</a> wrote: $comment[comment]</p>\n";
			}
		}
?>
                </div>
              </div>
            </div>
          </div><!-- row -->

          <div class="row">
            <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="panel-heading"><h4>Credits</h4></div>
                <table class="table">
<?php
		foreach($actor->credits() as $movie_in)
		{
			$movie = new movie($movie_in['movie_id']);
			if ($movie->kind_id != 1)
				continue;

			$character = new character($movie_in['person_role_id']);
			if (!$character->id)
			{
				// There is no "character" for this role, aka "Unknown".  Show the same row, but don't make it a link.
				print str_repeat(" ", 18) . "<tr>\n";
				print str_repeat(" ", 20) . "<td><a href='".$movie->link()."'>$movie->title</a> ($movie->production_year)</td>\n";
				print str_repeat(" ", 20) . "<td>as ".$character->name."</td>\n";
				print str_repeat(" ", 18) . "</tr>\n";
			}
			else
			{
				print str_repeat(" ", 18) . "<tr>\n";
				print str_repeat(" ", 20) . "<td><a href='".$movie->link()."'>$movie->title</a> ($movie->production_year)</td>\n";
				print str_repeat(" ", 20) . "<td>as <a href='".$character->link()."'>$character->name</a></td>\n";
				print str_repeat(" ", 18) . "</tr>\n";
			}
		}
?>
                </table>
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

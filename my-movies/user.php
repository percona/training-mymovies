<?php

include "lib/global.inc.php";
start_template();

$user = new user($_GET['id']);

?>

<div class="row">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading"><h2>User Information</h2></div>
      <div class="panel-body">

        <div class="col-lg-6">
          <p><strong>Name:</strong> <?= $user->first_name ?> <?= $user->last_name?></p>
          <p><strong>Last Login Date:</strong> <?= $user->last_login_date?></p>
          <p><strong>Email:</strong> <?= $user->email_address?></p>
        </div>

        <div class="col-lg-6">
          <div class="panel panel-default">
            <div class="panel-heading"><h4>Friends [<a href="user/add_friend.php?id=<?= $user->id?>">+</a>]</h4></div>
            <div class="panel-body">
<?php
	foreach($user->friends() as $friend_id)
	{
		$friend = new user($friend_id);
		print str_repeat(" ", 14) . "<p><a href=\"".$friend->link()."\">$friend->email_address</a></p>\n";
	}
?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

</div><!-- row -->

<?php
end_template();
?>

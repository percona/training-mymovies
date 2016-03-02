<?php

include "lib/global.inc.php";
start_template();

$user = new user($_GET['id']);

?>

<h2>User Information</h2>

Name: <?php echo $user->first_name?> <?php echo $user->last_name?><br />
Last Login Date: <?php echo $user->last_login_date?><br />
Email: <?php echo $user->email_address?><br />
<br />

<h3>Friends [<a id="add_friend" href="user/add_friend.php?id=<?php echo $user->id?>">+</a>]</h3>
<?php
$i=0;
foreach($user->friends() as $friend_id) {
	$i++;
	$friend = new user($friend_id);
	print "<a id='friend_$i' href=\"".$friend->link()."\">$friend->email_address</a><br />";

}

?>


<?php end_template(); ?>
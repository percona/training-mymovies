<?php

include "../lib/global.inc.php";

if ($_GET['login'] == 'yes')
{
	$tmp = get_random_user();
	$_SESSION['user_id'] = $tmp['id'];
	
	redirect_to("home.php");
}
elseif (isset($_POST['email_address']))
{
	$result = mysql_query_wrapper(sprintf("SELECT id FROM users WHERE email_address = '%s'", 
		mysqli_real_escape_string($conn, $_POST['email_address'])
	));
	
	if ($result->num_rows == 1)
	{
		$_id = $result->fetch_row();
		$_SESSION['user_id'] = $_id[0];
		
		redirect_to("home.php");
	}
}

start_template();

?>

<h2>Sign is as an existing user</h2>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']?>">

Email Address: <input type='text' name='email_address'><br />

<input type="submit" value="Login" />
</form>

<br />
<br />

<a id="random_login" href="<?php echo $_SERVER['PHP_SELF']?>?login=yes">login as random user</a>

<?php end_template(); ?>

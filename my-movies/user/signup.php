<?php
include "../lib/global.inc.php";

if (!empty($_POST)) {

	$_SESSION['user_id'] = create_new_user($_POST['first_name'], $_POST['last_name'], $_POST['email_address']);
	redirect_to("home.php");

}

print start_template();

?>

<h2>Create a new account</h2>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']?>">

First Name: <input type="text" name="first_name" /><br />
Last Name: <input type="text" name="last_name" /><br />
Email Address: <input type="text" name="email_address" /><br />
<br />
<input type="submit" value="Create Account" />

</form>

<?php print end_template(); ?>

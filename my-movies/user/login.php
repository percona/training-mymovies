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

<div class="row">
  <div class="col-lg-5">
    <div class="well">
      <form method="POST" action="<?php echo $_SERVER['PHP_SELF']?>" class="form-horizontal">
      <fieldset>
        <legend>Sign In</legend>

        <div class="form-group">
          <label for="email" class="col-lg-2 control-label">Email</label>
          <div class="col-lg-10">
            <input type="text" name="email_address" class="form-control" id="email">
          </div>
        </div>

        <div class="form-group">
          <div class="col-lg-10 col-lg-offset-2">
            <input type="submit" class="btn btn-primary" value="Login"/>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-10 col-lg-offset-2">
            <a href="<?php echo $_SERVER['PHP_SELF']?>?login=yes">login as random user</a>
          </div>
        </div>

      </fieldset>
      </form>
    </div>
  </div>
</div>

<?php
end_template();
?>

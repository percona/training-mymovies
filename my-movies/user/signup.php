<?php

include "../lib/global.inc.php";

if (!empty($_POST))
{
	$_SESSION['user_id'] = create_new_user($_POST['first_name'], $_POST['last_name'], $_POST['email']);
	redirect_to("home.php");
}

start_template();

?>

<div class="row">
  <div class="col-lg-6">
    <div class="well">
      <form method="POST" action="<?php echo $_SERVER['PHP_SELF']?>" class="form-horizontal">
      <fieldset>
        <legend>Create New User</legend>

        <div class="form-group">
          <label for="firstName" class="col-lg-3 control-label">First Name</label>
          <div class="col-lg-9">
            <input type="text" class="form-control" id="firstName" name="first_name" />
          </div>
        </div>

        <div class="form-group">
          <label for="lastName" class="col-lg-3 control-label">Last Name</label>
          <div class="col-lg-9">
            <input type="text" class="form-control" id="lastName" name="last_name" />
          </div>
        </div>

        <div class="form-group">
          <label for="email" class="col-lg-3 control-label">Email</label>
          <div class="col-lg-9">
            <input type="text" class="form-control" id="email" name="email" />
          </div>
        </div>

        <div class="form-group">
          <div class="col-lg-9 col-lg-offset-3">
            <input type="submit" class="btn btn-primary" value="Create Account" />
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

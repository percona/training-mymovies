<?php

include "../lib/global.inc.php";

require_valid_user(BASE_URI . 'user/login.php');

start_template();

switch($_GET['type'])
{
	case 'movie':
		$obj = new movie($_GET['id']);
		$display = $obj->title;
		break;
	case 'actor':
		$obj = new actor($_GET['id']);
		$display = $obj->name;
		break;
}

?>

<div class="row">
  <div class="col-lg-6">
    <div class="panel panel-default">
      <div class="panel-heading">Add Comment - <em><?= $display?></em></div>
      <div class="panel-body">

        <form method="POST" action="add_comment.php" class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label for="textArea" class="col-lg-2 control-label">Comment:</label>
              <div class="col-lg-10">
                <textarea class="form-control" rows="3" id="textArea" name="comment">This is the default comment!</textarea>
              </div>
            </div>
            <div class="form-group">
              <div class="col-lg-10 col-lg-offset-2">
                <input type="hidden" name="id" value="<?= h($_GET['id'])?>" />
                <input type="hidden" name="type" value="<?= h($_GET['type'])?>" />
                <button type="submit" id="comment_submit" class="btn btn-primary">Post Comment</button>
              </div>
            </div>
          </fieldset>
        </form>

      </div>
    </div>
  </div>
</div>

<?php
end_template();
?>

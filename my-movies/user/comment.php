<?php

include "../lib/global.inc.php";
start_template();

?>

<h2>Add Comment</h2>
<form method="POST" action="add_comment.php">
<input type="hidden" name="id" value="<?php echo h($_GET['id'])?>" />
<input type="hidden" name="type" value="<?php echo h($_GET['type'])?>" />

<textarea name="comment">
This is the default comment!
</textarea><br />
<input type="submit" id="comment_submit" value="Post Comment" />
</form>

<?php end_template(); ?>
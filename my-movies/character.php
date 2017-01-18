<?php

include "lib/global.inc.php";
start_template();

$character = new character($_GET['id']);
update_page_views('character', $character->id);

?>

<div class="row">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading"><h2>Movies with this Character - <?= $character->name?></h2></div>
      <table class="table">
<?php
	foreach($character->movies() as $row)
	{
		$movie = new movie($row['movie_id']);
		$actor = new actor($row['person_id']);
		
		print str_repeat(" ", 8) . "<tr>\n";
		print str_repeat(" ", 10) . "<td><a href='".$movie->link()."'>$movie->title</a> ($movie->production_year)</td>\n";
		print str_repeat(" ", 10) . "<td>played by <a href='".$actor->link()."'>".$actor->name ."</a></td>\n";
		print str_repeat(" ", 8) . "</tr>\n";
	}
?>
      </table>
    </div>
  </div>
</div>

<?php
end_template();
?>

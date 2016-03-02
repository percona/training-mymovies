<?php

class movie {
	var $id;
	var $info = array();

	function link() {
		return BASE_URI . "movie.php?id=$this->id&title=".urlencode($this->title);
	}

	function __construct($id) {
	
		$this->id = (int) $id;
		$result = mysql_query_wrapper("SELECT * FROM title WHERE id = $this->id");
		
		if (!$result) {
			
			throw new Exception("Query failed with " . mysql_error());
			
		} else {
		
			foreach(mysql_fetch_assoc($result) as $column => $value) {
				$this->$column = $value;
			}
			
		}
	
	}
	
	function update_info() {
	
		$result = mysql_query_wrapper("SELECT * FROM movie_info WHERE movie_id = $this->id");
		
		while($row = mysql_fetch_assoc($result)) {
			$this->info[$row['info_type_id']][] = $row['info'];
		}
			
	}
	
	function average_score() {
	
		$result = mysql_query_wrapper("SELECT AVG(rating) avg FROM movie_ratings WHERE movie_id = $this->id");
		return mysql_result($result,0,'avg'); /* may be null */
			
	}
	
	function cast() {
	
		$return = array();
		$result = mysql_query_wrapper("SELECT * FROM cast_info WHERE movie_id = $this->id and role_id = 1 ORDER BY nr_order ASC");
		
		while($row = mysql_fetch_assoc($result)) {
			$return[] = $row;
		}
		
		return $return;
		
	}
		
	function comments() {
	
		$return = array();
		$result = mysql_query_wrapper("SELECT * FROM comments WHERE type='movie' and type_id = $this->id ORDER BY id DESC");
		while($row = mysql_fetch_assoc($result)) {
			$return[] = $row;
		}

		return $return;
	
	}

}
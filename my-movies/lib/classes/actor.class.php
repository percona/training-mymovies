<?php

class actor {
	var $id;
	var $info = array();

	function link() {
		return BASE_URI . "actor.php?id=$this->id&name=".urlencode($this->name);
	}

	function __construct($id) {
	
		$this->id = (int) $id;
		$result = mysql_query_wrapper("SELECT * FROM name WHERE id = $this->id");
		
		if (!$result) {
			
			throw new Exception("Query failed with " . mysql_error());
			
		} else {
		
			foreach(mysql_fetch_assoc($result) as $column => $value) {
				$this->$column = $value;
			}
			
		}
		
		
		$name = explode(", ", $this->name);
		$this->name_original = $this->name;
		$this->name = $name[1] . " " . $name[0];
		
	
	}
	

	function update_info() {
	
		$result = mysql_query_wrapper("SELECT * FROM person_info WHERE person_id = $this->id");
		
		while($row = mysql_fetch_assoc($result)) {
			$this->info[$row['info_type_id']][] = $row['info'];
		}
			
	}
	

	function credits() {
	
		$return = array();
		$result = mysql_query_wrapper("SELECT cast_info.* FROM cast_info INNER JOIN 
		title on (cast_info.movie_id=title.id)
		WHERE cast_info.person_id = $this->id
		AND title.kind_id = 1
		ORDER BY title.production_year DESC, title.id DESC");
		
		if (!$result) {
			throw new Exception("Query failed with " . mysql_error());

		} else {		
		
			while($row = mysql_fetch_assoc($result)) {
				$return[] = $row;
			}
		
		}
		
		return $return;
		
	}

	function comments() {
	
		$return = array();
		$result = mysql_query_wrapper("SELECT * FROM comments WHERE type='actor' and type_id = $this->id ORDER BY id DESC");
		while($row = mysql_fetch_assoc($result)) {
			$return[] = $row;
		}

		return $return;
	
	}

}
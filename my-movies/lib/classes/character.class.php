<?php

class character {
	var $id;
	var $name = 'Unknown';

	function link() {
		return BASE_URI . "character.php?id=$this->id&name=".urlencode($this->name);
	}

	function __construct($id) {
	
		$this->id = (int) $id;
		$result = mysql_query_wrapper("SELECT * FROM char_name WHERE id = $this->id");
		
		if (!$result) {
			
			throw new Exception("Query failed with " . mysql_error());
			
		} else {
		
			if (!mysql_numrows($result)) {
				return; // Query OK, but char not found.
			}
			
			foreach(mysql_fetch_assoc($result) as $column => $value) {
				$this->$column = $value;
			}
			
		}
	
	}
	
	function movies() {

		$return = array();
		$result = mysql_query("SELECT * FROM cast_info WHERE person_role_id = $this->id");

		while($row = mysql_fetch_assoc($result)) {
			$return[] = $row;
		}
		
		return $return;

	}
	
}
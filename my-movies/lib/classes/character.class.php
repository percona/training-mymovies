<?php

class character
{
	var $id;
	var $name = 'Unknown';
	
	function link()
	{
		return BASE_URI . "character.php?id=$this->id&name=".urlencode($this->name);
	}

	function __construct($id)
	{
		$this->id = (int) $id;
		$result = mysql_query_wrapper("SELECT * FROM char_name WHERE id = $this->id");
		
		if (!$result)
			throw new Exception("Query failed with " . mysql_error());
		else
		{
			if ($result->num_rows == 0)
				return;
			
			foreach($result->fetch_assoc() as $column => $value)
			{
				$this->$column = $value;
			}
		}
	}
	
	function movies()
	{
		$return = array();
		$result = mysql_query_wrapper("SELECT * FROM cast_info WHERE person_role_id = $this->id");
		
		while($row = $result->fetch_assoc())
		{
			$return[] = $row;
		}
		
		return $return;
	}
}
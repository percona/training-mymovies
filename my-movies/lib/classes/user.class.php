<?php

class user {
	var $id;
	var $email_address;
	var $first_name;
	var $last_name;

	function link() {
		return BASE_URI . "user.php?id=$this->id&email=$this->email_address";
	}

	function __construct($id) {
	
		$this->id = $id;
		$result = mysql_query_wrapper("SELECT * FROM users WHERE id = $this->id");
		
		if (!$result) {
			throw new Exception("Query failed with " . mysql_error());
		} else {
		
			foreach(mysql_fetch_assoc($result) as $column => $value) {
				$this->$column = $value;
			}
			
		}
	
	}
	
	function update_last_login_date() {
	
		/*
		 This feature is used to show who is online now,
		 and figure out which users have not logged in
		 recently, and we should sent them offers to 
		 invite them to come back and see what is new!
		*/
	
		mysql_query_wrapper("UPDATE users SET last_login_date = NOW() WHERE id = $this->id");

	}
	
	function add_favorite($id, $type) {
	
		$id = (int) $id;
		mysql_query_wrapper("INSERT INTO favorites (user_id, type_id, type) VALUES ($this->id, $id, '$type')");

	}

	function remove_favorite($id, $type) {
	
		$id = (int) $id;
		mysql_query_wrapper("DELETE FROM favorites WHERE user_id = $this->id AND type_id = $id AND type='$type'");
	
	}
	
	function remove_favorite_actor($id) {
		return $this->remove_favorite($id, 'actor');
	}

	function remove_favorite_movie($id) {
		return $this->remove_favorite($id, 'movie');
	}

	function add_favorite_actor($id) {
		return $this->add_favorite($id, 'actor');
	}

	function add_favorite_movie($id) {
		return $this->add_favorite($id, 'movie');
	}
	

	function is_favorite_movie($id) {	
		return in_array($id, $this->favorite_movies());
	}

	function is_favorite_actor($id) {	
		return in_array($id, $this->favorite_actors());
	}

	function comments() {
		$return = array();
		$result = mysql_query_wrapper("SELECT * FROM comments WHERE user_id = $this->id");
		
		if (!$result) {
			throw new Exception("mysql error ". mysql_error());
		} else {
		
			while($row = mysql_fetch_assoc($result)) {	
				$return[] = $row;
			}
		}

		return $return;
	}
	
	function ratings() {
		
		$return = array();
		$result = mysql_query_wrapper("SELECT * FROM movie_ratings WHERE user_id = $this->id ORDER BY id DESC LIMIT 20");
		
		if (!$result) {
			throw new Exception("mysql error ". mysql_error());
		} else {
		
			while($row = mysql_fetch_assoc($result)) {	
				$return[] = $row;
			}
		}

		return $return;

	}
	
	function favorites($type) {

		$return = array();
		$result = mysql_query_wrapper("SELECT * FROM favorites WHERE user_id = $this->id AND type='$type'");
		
		if (!$result) {
			throw new Exception("mysql error ". mysql_error());
		} else {
		
			while($row = mysql_fetch_assoc($result)) {	
				$return[] = $row['type_id'];
			}
		}
		
		return $return;

	}

	function favorite_actors() {
		return $this->favorites('actor');
	}

	function favorite_movies() {
		return $this->favorites('movie');
	}
	
	function friends() {
	
		$return = array();
		$result = mysql_query_wrapper("SELECT user2 FROM user_friends WHERE user1 = $this->id");
		if (!$result) {
			throw new Exception("mysql error ". mysql_error());
		} else {
		
			while($row = mysql_fetch_assoc($result)) {	
				$return[] = $row['user2'];
			}
		}
		
		return $return;
	
	}
	
	
	function have_rated($movie_id) {
	
		$result = mysql_query_wrapper(sprintf("SELECT id FROM movie_ratings WHERE
			user_id = %d AND movie_id = %d", $this->id, $movie_id));
			
		return mysql_numrows($result);

	}
	
	function add_rating($id, $vote) {
	
		/*
		 Are you profiling?  This may or may not be a hidden
	 	 easter egg for you to fix.
		*/

		sleep(2);
	
		mysql_query_wrapper(sprintf("INSERT INTO movie_ratings (user_id, movie_id, rating)
			VALUES (%d, %d, %d)", $this->id, $id, $vote));
	
	}
	
	function add_friend($id) {
	
		/* Friendship is mutually exclusive, and 
		   granted automatically for usability. */
		
		if (!in_array($id, $this->friends())) {
			mysql_query_wrapper("INSERT INTO user_friends (user1, user2) VALUES ($this->id, $id), ($id, $this->id)");
		}
		
	}
	
	function add_comment($type='movie', $type_id, $comment) {
		mysql_query_wrapper("INSERT INTO comments (user_id, type, type_id, comment_time, comment) VALUES
			($this->id, '$type', '$type_id', NOW(), '".mysql_real_escape_string($comment)."')");
		
	}

}

?>
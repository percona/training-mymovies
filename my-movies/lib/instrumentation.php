<?php
/*
/* Copywrite 2010 Justin Swanhart and Percona Inc.
 All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the <organization> nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/



/* CONTENTS
 * Three classes for easing the implementation of instrumentation in your application.
 * Instrumentation - Provides an interface for storing counters and exporting them to the apache environment
 * MySQLi_perf     - Instrumented extension of the MySQLi interface and static functions for the functional one
 * MySQL_perf      - Instrumented abstract class with static functions to replace the mysql functional interface
 * 
 * 
 * BASIC USAGE
 * --------------------------------------------------------------------------------------
 * To automatically record CPU usage, memory usage and other metrics be sure to 
 * start the instrumentation request very early in the life of your application.
 * Ideally, this should be the first thing your application does.
 
 * Instrumentation::get_instance()->start_request();
 * 
 * IF YOU ARE USING SESSIONS:
 * --------------------------------------------------------------------------------------
 * You can ask start_request to call session_start() to create or resume a session.  This
 * will ensure that time to create the session is accounted for in your application:
 * 
 * Instrumentation::get_instance()->start_request(true);
 * 
 * The time to create the session is recorded in the session_create_time metric.
 * If you are using a session handler that makes calls to  external resources via non-instrumented
 * calls, this will at least capture the time appropriately so that you can determine if session creation 
 * is a bottleneck for your application.
 * 
 * AUTO CAPTURE SESSION INFORMATION
 * --------------------------------------------------------------------------------------
 * You can auto-capture session information like the username by using the $grab_keys argument:
 * Instrumentation::get_instance()->start_request(true, array('uname'));
 * 
 * That will start (or resume) a session, and record $_SESSION['uname'] into an instrumentation
 * variable (SESSION_uname) that will be auto-exported to the apache environment.  This allows you
 * to put your application username into the apache log, for example.
 */

/* The instrumentation class implements a set of counter variables similar to MySQL 
 * status counters.  Counters are created dynamically, there is no
 * fixed list.
 * 
 * The counters are automatically exported to the Apache environment
 * for consumption by Apache logs.
 * 
 * CPU usage, memory usage, and other metrics are recorded.
 * 
 * Also included is a function that will add a SQL comment to
 * a given SQL file which includes the file offset and location of
 * the function call which originated the SQL.
 * 
 */
 class Instrumentation {
  	private $counters = array(); /* key/value pairs of instrumentation metrics */
  	private $blacklist = array();/* keys to never export to the Apache environment */
  	private static $instance;
   	private $debug = 0;
   	private static $started_at=0;

   	
  	private function __construct() {
  			/* Initialize counters we want to ensure are in the output
			 * environment, but that might not be inintialized if there
			 * are no calls to the related functionality
			 */
    		$this->set('mysql_query_count',0);
			$this->set('mysql_prepare_count',0);
			$this->set('mysql_prepare_time',0);
			$this->set('mysql_connection_count', 0);
			$this->set('mysql_connect_time', 0);  
			$this->set('mysql_query_exec_time',0); 
			$this->set('mysql_deadlock_count', 0);
			
			$this->set('memcache_connection_count',0); 
			$this->set('memcache_delete_count',0);
			$this->set('memcache_delete_time',0);
			$this->set('memcache_miss_count',0); 
			$this->set('memcache_get_count',0); 
			$this->set('memcache_get_time', 0);
			$this->set('memcache_set_count',0);
			$this->set('memcache_set_time', 0);
			$this->set('memcache_add_count',0);
			$this->set('memcache_add_time', 0);
			$this->set('memcache_replace_count',0);
			$this->set('memcache_replace_time', 0);
			$this->set('memcache_increment_count',0);
			$this->set('memcache_increment_time', 0);
			$this->set('memcache_decrement_count',0);
			$this->set('memcache_decrement_time', 0);			
  	}

  	/* If the timer was started, then return the amount
  	 * of time elapsed in seconds with microsecond resolution.
  	 * 
  	 * By default, the function resets the timer to the
  	 * current time.
  	 */
  	public function timer($reset = true) {
  	    $elapsed = 0;
  		if(Instrumentation::$started_at)
  			$elapsed = microtime(true) - Instrumentation::$started_at; 
  		if($reset) Instrumentation::$started_at = microtime(true);
  		return $elapsed;
  	}
  	
  	public function debug($enabled = false) {
  		$this->debug=$enabled;
  	}
  	
  	/* Intrumentation is a singleton*/
    public static function get_instance() 
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }
    
  	/* Increment a numeric counter.  */
    public function increment($counter, $amt = 1) {
    	  if(empty($this->counters[$counter])) {
    	    $this->counters[$counter] = 0;
    	  }
    	  
    	  $this->counters[$counter] += $amt;
    }
    
    /* Append a string to the specified counter */
    public function append($counter, $val) {
    	  if(empty($this->counters[$counter])) 
    	    $this->counters[$counter] = "";
    	  
    	  $this->counters[$counter] .= $val;
    }
    
    /* Set the counter to a specific value */
    public function set($counter, $val) {
    	$this->counters[$counter] = $val;
    }
    
    /* Return the value of a counter */
    public function get($counter) {
    	if(!empty($this->counters[$counter])) return $this->counters[$counter];
    	return false;
    }
    
    /* Export all of the counters into the apache 
     * environment with a CTR_ prefix on each counter name.
     * 
     * Any counters on the blacklist will not be added.
     */
    public function export_counters() {
		foreach($this->counters as $counter => $val) {
			if(!array_key_exists($counter, $this->blacklist)) { 
				$type = gettype($val);
				switch($type) {
			    	  case 'string':
				      case 'double':
				      case 'boolean':
				      case 'integer':
				      break;
				default:
					$val = serialize($val);
				}
		      
				apache_setenv("CTR_{$counter}", $val); 
			}
		}
    }
    
    public function dump_counters($format = "table", $prefix="") {
    	$out = "";
		foreach($this->counters as $counter => $val) {
			switch(gettype($val)) {
    	  		case 'string':
    	  		case 'double':
    	  		case 'boolean':
    	  		case 'integer':
    	  			break;
    	  		default:
    	  			$val = serialize($val);
    	  		}
    	  	
    	  		if ($format == "table")
    	  		   $out .= "<tr><td>$counter<td>$val</tr>";
    	  		else
    	  			$out .=" %\{CTR_{$counter}\}e"; 
    	}
    	if($format=="table") return "<table border=1><tr><td>counter<td>value</tr>{$out}</table>";
    	return "<pre>
    	$out
    	</pre>";
    }
    
    /* Add PHP stack information to a query along with the
     * unique id for the request and the session id.
     */
    public function instrument_query($query_sql="", $deadlock_count=0, $keys = array('request_id', 'session_id', 'SESSION_uname')) {
    	$query_header = "";
    	
    	if ($query_sql) {
    		/* the first frame is the original caller of whatever function led us here */
    	$tmp = debug_backtrace(); // supress php notice by creating a tmp var first.
		$frame = array_pop($tmp);
		$file  = basename($frame['file']);
    		$query_header = "-- File: {$file}\tLine: {$frame['line']}\tFunction: {$frame['function']}\t";
        	foreach($keys as $x => $key) {
        	  	$val = $this->get($key);
            		if($val) {
				$val = str_replace(array("\t","\n","\0"), "", $val); /* all other chars are safe in comments */ 
				$key = strtolower(str_replace(array(": ","\t","\n","\0"), "", $key)); 
            			$query_header .= "\t{$key}: {$val}";
            		}
            	}
            
        }
        if($this->debug) echo "<pre>$query_header\n$query_sql;</pre>";
        return trim($query_header) . "\n" . $query_sql;
    }


    /* This function is called from the 'config.php' script to
     * indicate the start of a new request.
     * 
     * We check to see if the id is already set, but this should
     * note be necessary since config.php is included with require_once().
     * 
     * The Instrumentation::end_request function is added as a shutdown 
     * function.
     */
    public function start_request($start_session = false, $grab_keys = array()) {
    	
    	if(!$this->get('request_id')) {
    		$this->set('php_service_time', microtime(true));
		
				
			$usage = getrusage();

			/* Capture current memory usage */
			$this->set('memory_usage', memory_get_usage());

			/* Capture CPU usage information */
			$this->set('cpu_user', $usage["ru_utime.tv_sec"]*1e6 + $usage["ru_utime.tv_usec"]);  	
			$this->set('cpu_system', $usage["ru_stime.tv_sec"]*1e6 + $usage["ru_stime.tv_usec"]);

			if(!session_id() && $start_session) {
				$this->timer();
				session_start();
				$this->set('session_create_time', $this->timer());
			}
			
			/* Record the session information if it exists, and grab any session
			 * information into counters if we have been requested to do so.
			 */
			if(session_id()) {	
			  $this->set('session_id', session_id());
			  foreach($grab_keys as $key) {
			  	$this->set("SESSION_{$key}", empty($_SESSION[$key]) ? "" : $_SESSION[$key]);
			  }
			}
			
			$this->set('action', $_SERVER['REQUEST_METHOD']);
    		$this->set('request_id', sha1($_SERVER['REMOTE_PORT'] . $_SERVER['REMOTE_ADDR'] . $_SERVER['REQUEST_URI'] . microtime(true)));

    		register_shutdown_function(array('Instrumentation','end_request'));
    	}
    	
    }
    
    /* Add a counter to the blacklist.
     * 
     * Counters on the blacklist are not exported to the Apache environment.
    */
    public function add_counter_to_blacklist($counter) {
    	$this->blacklist[$counter] = $counter;
    }
    
    /* Remove a counter from the blacklist */
    public function remove_counter_from_blacklist($counter) {
    	if(!empty($this->blacklist[$counter])) unset($this->blacklist[$counter]);
    }
    
    /* Export the performance counters to the
     * Apache environment and does some calculations, like
     * calculating the total request time in microseconds.
     */
    public static function end_request() {
    	$instance = Instrumentation::get_instance();
		$usage = getrusage();
		
    	$instance->set('cpu_user', (($usage["ru_utime.tv_sec"]*1e6+$usage["ru_utime.tv_usec"]) - $instance->get('cpu_user')) / 1e6);  	
		$instance->set('cpu_system', (($usage["ru_stime.tv_sec"]*1e6+$usage["ru_stime.tv_usec"]) - $instance->get('cpu_system')) / 1e6);
    	$instance->set('total_cpu_time', ($instance->get('cpu_user') + $instance->get('cpu_system')) );
    	$instance->set('memory_usage', memory_get_usage($instance->get('memory_usage')));
    	
    	$instance->set('php_service_time', microtime(true) - $instance->get('php_service_time'));
    	apache_setenv('php_instrumented', 1);
    	
    	$instance->export_counters();
    	
    }
}

/* This is a drop-in replacement for the MySQLi object which is instrumented 
 * using the instrumentation class.
 * 
 * Change all instances of:  $obj = new mysqli(...)
 *                   with:   $obj = new mysqli_perf(...)
 *                   
 * If you use the functional interface replace:  mysqli_connect(...)
 *                                        with:  MySQLi_per::mysqli_connect(...)
 */
class MySQLi_perf extends MySQLi {
	#>1 IS ONLY SAFE IN AUTOCOMMIT MODE!!!!
	public static $deadlock_try_limit = 1;
	
	/* Object interface constructor */
	public function __construct($host=NULL, $user=NULL, $pass=NULL, $db=NULL){
		if($host === NULL) ini_get("mysql.default_host");
		if($user === NULL) ini_get("mysql.default_user");
	  	if($pass === NULL) ini_get("mysql.default_password");
	  	Instrumentation::get_instance()->increment('mysql_connection_count');
	  	Instrumentation::get_instance()->timer();
      	parent::__construct($host, $user, $pass, $db);
      	Instrumentation::get_instance()->increment('mysql_connect_time', Instrumentation::get_instance()->timer());
	}
	

    
    public function query($query, $resultmode = NULL) {
    	$instance = Instrumentation::get_instance();
    	
    	$retry_count = 0;
    	
		while($retry_count < MySQLi_perf::$deadlock_try_limit) {
			$query = $instance->instrument_query($query, $retry_count);
    		$instance->increment('mysql_query_count', 1);
			$retry_count = 0;
			
	    	$instance->timer();
	    	$r = parent::query($query, $resultmode);
	    	$instance->increment('mysql_query_exec_time', $instance->timer());
	    	
			if (mysqli_errno($this) == 1213) { /* 1213 (ER_LOCK_DEADLOCK)
									  Deadlock detected retry operation */
	    		$instance->increment('mysql_deadlock_count', 1);
				++$retry_count;
				continue; // loop to the start of the while loop
			}
			
			break;
	    	
		}
	    
		return $r;
    }
    
    public function multi_query($query, $resultmode = NULL) {
    	$instance = Instrumentation::get_instance();
    	
    	$retry_count = 0;
    	
		while($retry_count < MySQLi_perf::$deadlock_try_limit) {
			$query = $this->instrument_query($query, $retry_count);
    		$instance->increment('mysql_query_count', 1);
			$retry_count = 0;
			
	    	Instrumentation::get_instance()->timer();
	    	$r = parent::multi_query($query, $resultmode);
	    	
	    	$instance->increment('mysql_query_exec_time', Instrumentation::get_instance()->timer());
	    	
			if ($this->errno() == 1213) { /* 1213 (ER_LOCK_DEADLOCK)
									  Deadlock detected retry operation */
	    		$instance->increment('mysql_deadlock_count', 1);
				++$retry_count;
				continue; // loop to the start of the while loop
			}
			
			break;
	    	
		}
	    
		return $result;
    }

	/* emulate functional mysqli_connect interface */
	public static function mysqli_connect($host=NULL, $user=NULL, $pass=NULL, $db=NULL, $port=NULL, $socket=NULL) {	
		
		Instrumentation::get_instance()->timer();;
      	$r = mysqli_connect($host, $user, $pass, $db, $port, $socket);
      	Instrumentation::get_instance()->increment('mysql_connection_count');
      	Instrumentation::get_instance()->increment('mysql_connect_time', Instrumentation::get_instance()->timer());
      	return $r;
	}
	
	/* emulate functional mysqli_query interface */
    public static function mysqli_query($link, $query, $resultmode = NULL) {
    	$instance = Instrumentation::get_instance();
    	
    	$retry_count = 0;
		$result = false;
		while($retry_count < MySQLi_perf::$deadlock_try_limit) {
			$query = $instance->instrument_query($query, $retry_count);
    		$instance->increment('mysql_query_count', 1);
			$retry_count = 0;
			
	    	Instrumentation::get_instance()->timer();
	    	$result = mysqli_query($link, $query, $resultmode);
	    	$errno = mysqli_errno($link);
	    	$instance->increment('mysql_query_exec_time', Instrumentation::get_instance()->timer());
	    	
			if ($errno == 1213) { /* 1213 (ER_LOCK_DEADLOCK)
									  Deadlock detected retry operation */
	    		$instance->increment('mysql_deadlock_count', 1);
				++$retry_count;
				continue; // loop to the start of the while loop
			}
			
			break;
	    	
		}
	    
		return $result;
    }
    
    /* emulate functional mysqli_multi_query interface. only increment counter by one for now.. */
    public static function mysqli_multi_query($query, $resultmode = MYSQL_STORE_RESULT) {
    	$instance = Instrumentation::get_instance();
    	
    	$retry_count = 0;
    	
		while($retry_count < MySQLi_perf::$deadlock_try_limit) {
			$query = $this->instrument_query($query, $retry_count);
    		$instance->increment('mysql_query_count', 1);
			$retry_count = 0;
			
	    	Instrumentation::get_instance()->timer();
	    	$r = parent::multi_query($query, $resultmode);
	    	
	    	$instance->increment('mysql_query_exec_time', Instrumentation::get_instance()->timer());
	    	
			if ($this->errno() == 1213) { /* 1213 (ER_LOCK_DEADLOCK)
									  Deadlock detected retry operation */
	    		$instance->increment('mysql_deadlock_count', 1);
				++$retry_count;
				continue; // loop to the start of the while loop
			}
			
			break;
	    	
		}
	    
		return $result;
    }
    
    
}

/* This class can be used to replace calls to the non-object oriented mysql_ class of functions.
 * replace calls to mysql_connect with MySQL_perf::connect
 *                  mysql_query with MySQL_perf::mysql_query
 *           
 * This class is abstract and can't be instantiated because the mysql_ interface is not object oriented.
 * Thus this class functions only as a convenient namespace for the functions.
 */
 abstract class MySQL_perf {
	private static $connection_list = array();
	#>1 IS ONLY SAFE IN AUTOCOMMIT MODE!!!!
	public static $deadlock_try_limit = 1;
	
	public static function mysql_connect( $host = NULL, $user=NULL, $pass=NULL, $new_link = NULL , int $flags = NULL ) {
    	
		if($host === NULL) ini_get("mysql.default_host");
		if($user === NULL) ini_get("mysql.default_user");
	  	if($pass === NULL) ini_get("mysql.default_password");
	    if($flags === NULL) $flags = 0;
	    
	  	/* PHP reuses a connection if one has already been established, so don't increment 
	  	 * the counter on connection reuse.
	  	 */
	  	if($new_link || empty(MySQL_perf::$connection_list[$host . $user . $pass . $flags])) {
	  		Instrumentation::get_instance()->increment('mysql_connection_count');
	  	}
	  	
	  	/* record the connection */
	  	MySQL_perf::$connection_list[$host . $user . $pass . $flags] = 1;
	  	Instrumentation::get_instance()->timer();
	  	$r = mysql_connect($host, $user, $pass, $new_link, $flags);
	  	Instrumentation::get_instance()->increment('mysql_connect_time', Instrumentation::get_instance()->timer());
	  	return $r; 
	}
	
	public static function mysql_close($link=NULL) {
	
		mysql_close(); // @TODO: count closes.
	
	}
	
	public static function mysql_query($sql, $conn=false) {
    	$instance = Instrumentation::get_instance();
    	
    	$retry_count = 0;

        
		while($retry_count < MySQL_perf::$deadlock_try_limit) {
    		$instance->increment('mysql_query_count', 1);
    		$instance->instrument_query($sql, $retry_count);
			
			$result = false;
			$errno = -1;
			Instrumentation::get_instance()->timer();
			if ($conn !== false) {
				$result = mysql_query($sql, $conn);
				$errno = mysql_errno($conn);
			} else {
				$result = mysql_query($sql);
				$errno = mysql_errno(); /* fixing a bug: removing $conn */
			}
    		$instance->increment('mysql_query_exec_time',Instrumentation::get_instance()->timer());
			
			if ($errno == 1213) { /* 1213 (ER_LOCK_DEADLOCK)
									  Deadlock detected retry operation */

				++$retry_count;
				continue; // loop to the start of the while loop
			}

			break; /* exit loop to return result */

		}
		
		return $result;
    }	
}

?>
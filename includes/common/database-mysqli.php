<?php

class PBDatabase_connection_mysqli extends PBDatabase_connection{

	private $_connection = null;

	function __construct(){
		parent::__construct("mysqli");
	}

	public function connect(){
		global $pb_config;

		if(!function_exists("mysqli_connect")){
			die("MySQLi not supported");
		}

		$this->_connection = @mysqli_connect($pb_config->db_host, $pb_config->db_username, $pb_config->db_userpass, $pb_config->db_name) Or die("Error On DB Connection : MYSQLI");

		return isset($this->_connection);
	}

	public  function escape_string($str_){
		return mysqli_real_escape_string($this->_connection, $str_);
	}

	public function query($query_){
		return mysqli_query($this->_connection, $query_);
	}
	public  function inserted_id(){
		return mysqli_insert_id($this->_connection);
	}

	public function num_rows($resource_){
		return @mysqli_num_rows($resource_); 
	}
	public function fetch_array($resource_, $option_ = null){
		switch($option_){
			case PB_MYSQL_NUM :
				$option_ = MYSQLI_NUM;	
				break;
			case PB_MYSQL_BOTH :
				$option_ = MYSQLI_BOTH;
				break;
			case PB_MYSQL_ASSOC : 
			default : 
				$option_ = MYSQLI_ASSOC;
				break;
		}

		return mysqli_fetch_array($resource_, $option_);
	}


	public function commit(){
		mysqli_query($this->_connection, "commit");
	}
	public function rollback(){
		mysqli_query($this->_connection, "rollback");
	}
	public function close_connection(){
		@mysqli_close($this->_connection);
	}
}

function _pb_database_mysqli_add_connection_type($results_){
	$results_["mysqli"] = array(
		'name' => 'MySQLi',
		'connection_class' => "PBDatabase_connection_mysqli",
	);
	return $results_;
}
pb_hook_add_filter('pb_database_connection_types', "_pb_database_mysqli_add_connection_type");

?>
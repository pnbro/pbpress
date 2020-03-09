<?php

class PBDatabase_connection_mysql extends PBDatabase_connection{

	private $_connection = null;
	private $_last_error_no = 0;
	private $_last_error_message = null;

	function __construct(){
		parent::__construct("mysql");
	}

	public function connect(){
		if(!function_exists("mysql_connect")){
			die("MySQL not supported");
		}

		global $pb_config;
		$this->_connection = @mysql_connect($pb_config->db_host.":".$pb_config->db_port, $pb_config->db_username, $pb_config->db_userpass, true) Or die("Error On DB Connection : MYSQL");

		mysql_select_db($pb_config->db_name) Or die("Error On Select DB : MYSQL");

		return isset($this->_connection);
	}

	public function escape_string($str_){
		return mysql_real_escape_string($str_);
	}

	public function query($query_, $values_ = array(), $types_ = array()){
		$param_index_ = 0;
		while(($last_pos_ = strpos($query_, "?")) !== false){

			$column_value_ = $values_[$param_index_];
			$column_type_ = $types_[$param_index_];

			if($column_type_ === PBDB::TYPE_NUMBER && !strlen($column_value_)){
				$column_value_ = "NULL";
			}else if($column_type_ === PBDB::TYPE_STRING){
				$column_value_ = "'{$column_value_}'";
			}

			$query_ = preg_replace("/\?/", $column_value_, $query_, 1);
			++$param_index_;
		}

		$result_ =  mysql_query($query_, $this->_connection);

		$this->_last_error_no = mysql_errno($this->_connection);
		$this->_last_error_message = mysql_error($this->_connection);

		return $result_;
	}
	public function inserted_id(){
		return mysql_insert_id($this->_connection);
	}
	public function last_error(){
		if($this->_last_error_no === 0) return false;
		return new PBError($this->_last_error_no, 'MYSQL ERROR', $this->_last_error_message);	
	}

	public function num_rows($resource_){
		return @mysql_num_rows($resource_); 
	}
	public function fetch_array($resource_, $option_ = null){

		switch($option_){
			case PB_MYSQL_NUM :
				$option_ = MYSQL_NUM;	
				break;
			case PB_MYSQL_BOTH :
				$option_ = MYSQL_BOTH;
				break;
			case PB_MYSQL_ASSOC : 
			default : 
				$option_ = MYSQL_ASSOC;
				break;
		}

		return mysql_fetch_array($resource_, $option_);
	}

	public function autocommit($bool_){
		die("MYSQL autocommit not supported");
	}

	public function commit(){
		mysql_query("commit", $this->_connection);
	}
	public function rollback(){
		mysql_query("rollback", $this->_connection);
	}
	public function close_connection(){
		@mysql_close($this->_connection);
	}
}


function _pb_database_mysql_add_connection_type($results_){
	$results_["mysql"] = array(
		'name' => 'MySQL',
		'connection_class' => "PBDatabase_connection_mysql",
	);
	return $results_;
}
pb_hook_add_filter('pb_database_connection_types', "_pb_database_mysql_add_connection_type");


?>
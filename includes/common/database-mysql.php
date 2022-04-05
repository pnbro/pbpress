<?php

class PBDatabase_connection_mysql extends PBDatabase_connection{

	private $_connection = null;
	private $_last_error_no = 0;
	private $_last_error_message = null;
	private $_last_error_trace = null;

	private $_host = null;
	private $_port = null;
	private $_db_name = null;
	private $_charset = null;
	private $_username = null;
	private $_userpass = null;

	public function host(){
		return $this->_host;
	}
	public function port(){
		return $this->_port;
	}
	public function db_name(){
		return $this->_db_name;
	}
	public function charset(){
		return $this->_charset;
	}
	public function username(){
		return $this->_username;
	}
	public function userpass(){
		return $this->_userpass;
	}


	function __construct(){
		parent::__construct("mysql");
	}

	public function connect($options_ = array()){
		if(!function_exists("mysql_connect")){
			die("MySQL not supported");
		}

		global $pb_config;

		$this->_host = @strlen($options_['host']) ? $options_['host'] : $pb_config->db_host;
		$this->_port = @strlen($options_['port']) ? $options_['port'] : $pb_config->db_port;
		$this->_db_name = @strlen($options_['db_name']) ? $options_['db_name'] : $pb_config->db_name;
		$this->_charset = @strlen($options_['charset']) ? $options_['charset'] : $pb_config->db_charset;
		$this->_username = @strlen($options_['username']) ? $options_['username'] : $pb_config->db_username;
		$this->_userpass = @strlen($options_['userpass']) ? $options_['userpass'] : $pb_config->db_userpass;

		$this->_connection = @mysql_connect($this->_host.":".$this->_port, $this->_username, $this->_userpass, true) Or die("Error On DB Connection : MYSQL");

		mysql_select_db($this->_db_name) Or die("Error On Select DB : MYSQL");
		mysql_set_charset($this->_charset, $this->_connection);

		return isset($this->_connection);
	}

	public function escape_string($str_){
		return mysql_real_escape_string($str_);
	}

	public function query($query_, $values_ = array(), $types_ = array()){
		$this->set_last_query($query_);
		$this->set_last_query_parameters(array('values' => $values_, 'types' => $types_));

		$param_index_ = 0;
		while(($last_pos_ = strpos($query_, PBDB_PARAM_MAP_STR)) !== false){

			$column_value_ = $values_[$param_index_];
			$column_type_ = $types_[$param_index_];

			if($column_value_ === null || (!strlen($column_value_) && $column_type_ === PBDB::TYPE_NUMBER)){
				$column_value_ = "NULL";
				$column_type_ = PBDB::TYPE_NUMBER;
			}else if($column_type_ === PBDB::TYPE_STRING){
				$column_value_ = "'{$column_value_}'";
			}

			$query_ = preg_replace("/".PBDB_PARAM_MAP_STR."/", $column_value_, $query_, 1);
			++$param_index_;
		}

		$result_ =  mysql_query($query_, $this->_connection);

		$this->_last_error_no = mysql_errno($this->_connection);
		$this->_last_error_message = mysql_error($this->_connection);
		$this->_last_error_trace = $this->_last_error_no === 0 ? null : debug_backtrace();

		return $result_;
	}
	public function inserted_id(){
		return mysql_insert_id($this->_connection);
	}
	public function last_error(){
		if($this->_last_error_no === 0) return false;
		return new PBError($this->_last_error_no, 'MYSQL ERROR', $this->_last_error_message);	
	}
	public function last_error_trace(){
		if($this->_last_error_no === 0) return null;
		return $this->_last_error_trace;
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
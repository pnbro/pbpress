<?php

class PBDatabase_connection_mysqli extends PBDatabase_connection{

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
		parent::__construct("mysqli");
	}

	public function connect($options_ = array()){
		if(!function_exists("mysqli_connect")){
			die("MySQLi not supported");
		}

		global $pb_config;

		$this->_host = @strlen($options_['host']) ? $options_['host'] : $pb_config->db_host;
		$this->_port = @strlen($options_['port']) ? $options_['port'] : $pb_config->db_port;
		$this->_db_name = @strlen($options_['db_name']) ? $options_['db_name'] : $pb_config->db_name;
		$this->_charset = @strlen($options_['charset']) ? $options_['charset'] : $pb_config->db_charset;
		$this->_username = @strlen($options_['username']) ? $options_['username'] : $pb_config->db_username;
		$this->_userpass = @strlen($options_['userpass']) ? $options_['userpass'] : $pb_config->db_userpass;

		$this->_connection = @mysqli_connect($this->_host, $this->_username, $this->_userpass, $this->_db_name, $this->_port) Or die("Error On DB Connection : MYSQLI");
		mysqli_set_charset($this->_connection, $this->_charset);
		return isset($this->_connection);
	}

	public  function escape_string($str_){
		return mysqli_real_escape_string($this->_connection, $str_);
	}

	public function query($query_, $values_ = array(), $types_ = array()){
		$this->set_last_query($query_);
		$this->set_last_query_parameters(array('values' => $values_, 'types' => $types_));
		
		$param_index_ = 0;
		while(($last_pos_ = strpos($query_, PBDB_PARAM_MAP_STR)) !== false){

			$column_value_ = $values_[$param_index_];
			$column_type_ = isset($types_[$param_index_]) ? $types_[$param_index_] : PBDB::TYPE_STRING;

			if($column_value_ === null || (!strlen($column_value_) && $column_type_ === PBDB::TYPE_NUMBER)){
				$column_value_ = "NULL";
				$column_type_ = PBDB::TYPE_NUMBER;
			}else if($column_type_ === PBDB::TYPE_STRING){
				$column_value_ = "'{$column_value_}'";
			}

			$query_ = preg_replace("/".PBDB_PARAM_MAP_STR."/", $column_value_, $query_, 1);
			++$param_index_;
		}

		$result_ =  mysqli_query($this->_connection, $query_);

		$this->_last_error_no = mysqli_errno($this->_connection);
		$this->_last_error_message = mysqli_error($this->_connection);
		$this->_last_error_trace = $this->_last_error_no === 0 ? null : debug_backtrace();

		return $result_;
	}
	public  function inserted_id(){
		return mysqli_insert_id($this->_connection);
	}
	public function last_error(){
		if($this->_last_error_no === 0) return false;
		return new PBError($this->_last_error_no, 'MYSQLi ERROR',$this->_last_error_message);	
	}
	public function last_error_trace(){
		if($this->_last_error_no === 0) return null;
		return $this->_last_error_trace;
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

	public function autocommit($bool_){
		mysqli_autocommit($this->_connection, $bool_);
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
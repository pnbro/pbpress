<?php

class PBDatabase_connection_pdo extends PBDatabase_connection{
	private $_connection = null;
	private $_last_error_no = "00000";
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

	public $data_types = array(
		PBDB::TYPE_STRING => PDO::PARAM_STR,
		PBDB::TYPE_NUMBER => PDO::PARAM_INT,
		PBDB::TYPE_FLOAT => PDO::PARAM_STR,
	);
	function __construct(){
		parent::__construct("pdo");
	}

	public function connect($options_ = array()){
		global $pb_config;

		if(!class_exists("PDO")){
			die("PDO not supported");
		}

		$this->_host = @strlen($options_['host']) ? $options_['host'] : $pb_config->db_host;
		$this->_port = @strlen($options_['port']) ? $options_['port'] : $pb_config->db_port;
		$this->_db_name = @strlen($options_['db_name']) ? $options_['db_name'] : $pb_config->db_name;
		$this->_charset = @strlen($options_['charset']) ? $options_['charset'] : $pb_config->db_charset;
		$this->_username = @strlen($options_['username']) ? $options_['username'] : $pb_config->db_username;
		$this->_userpass = @strlen($options_['userpass']) ? $options_['userpass'] : $pb_config->db_userpass;

		$dsn_ = "mysql:host=".$this->_host.";port=".$this->_port.";dbname=".$this->_db_name.";charset=".$this->_charset;

		$this->_connection = new PDO($dsn_, $this->_username, $this->_userpass) Or die("Error On DB Connection : PDO");
		$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return isset($this->_connection);
	}

	public  function escape_string($str_){
		return $str_; //no escape method
	}

	public function query($query_, $values_ = array(), $types_ = array()){

		$param_index_ = 0;
		while(($last_pos_ = strpos($query_, PBDB_PARAM_MAP_STR)) !== false){			
			$query_ = preg_replace("/".PBDB_PARAM_MAP_STR."/", ":pdo_param".$param_index_, $query_, 1);
			++$param_index_;
		}

		$this->set_last_query($query_);
		$this->set_last_query_parameters(array('values' => $values_, 'types' => $types_));

		$statement_ = $this->_connection->prepare($query_);

		foreach($values_ as $index_ => $value_){
			$column_type_ = isset($types_[$index_]) ? $types_[$index_] : PBDB::TYPE_STRING;
			$column_type_ = $this->data_types[$column_type_];

			if($value_ !== null){
				$statement_->bindValue(":pdo_param{$index_}", $value_, $column_type_);	
			}else{
				$statement_->bindValue(":pdo_param{$index_}", $value_, PDO::PARAM_NULL);
			}
		}

		try{
			$this->_last_error_no = "00000";
			$this->_last_error_message = null;
			$this->_last_error_trace = null;


			$result_ = $statement_->execute();

			return $statement_;
		}catch(Exception $ex_){
			$this->_last_error_no = $ex_->getCode();
			$this->_last_error_message = $ex_->getMessage();
			$this->_last_error_trace = debug_backtrace();
			return false;
		}

		return $statement_;
	}
	public  function inserted_id(){
		return $this->_connection->lastInsertId();
	}
	public function last_error(){
		if($this->_last_error_no === "00000") return false;
		return new PBError($this->_last_error_no, 'PDO ERROR',$this->_last_error_message);	
	}
	public function last_error_trace(){
		if($this->_last_error_no === "00000") return null;
		return $this->_last_error_trace;
	}

	public function num_rows($resource_){
		return $resource_->rowCount();
	}
	public function fetch_array($resource_, $option_ = null){
		if(!$resource_) return null;
		switch($option_){
			case PB_MYSQL_NUM :
				$option_ = PDO::FETCH_NUM;	
				break;
			case PB_MYSQL_BOTH :
				$option_ = PDO::FETCH_BOTH;
				break;
			case PB_MYSQL_ASSOC : 
			default : 
				$option_ = PDO::FETCH_ASSOC;
				break;
		}

		return $resource_->fetch($option_);
	}

	public function autocommit($bool_){
		$this->_connection->setAttribute(PDO::ATTR_AUTOCOMMIT,$bool_);

		if(!$bool_){
			$this->_connection->beginTransaction();
		}else{
			$this->rollback();
		}
		
	}

	public function commit(){
		if($this->_connection->inTransaction()){
			$this->_connection->commit();
		}
	}
	public function rollback(){
		if($this->_connection->inTransaction()){
			$this->_connection->rollBack();
		}
		
	}
	public function close_connection(){
		$this->_connection = null;
	}
}

function _pb_database_pdo_add_connection_type($results_){
	$results_["pdo"] = array(
		'name' => 'PDO',
		'connection_class' => "PBDatabase_connection_pdo",
	);
	return $results_;
}
pb_hook_add_filter('pb_database_connection_types', "_pb_database_pdo_add_connection_type");

?>
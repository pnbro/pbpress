<?php

class PBDatabase_connection_pdo extends PBDatabase_connection{
	private $_connection = null;
	private $_last_error_no = "00000";
	private $_last_error_message = null;

	public $data_types = array(
		PBDB::TYPE_STRING => PDO::PARAM_STR,
		PBDB::TYPE_NUMBER => PDO::PARAM_INT,
		PBDB::TYPE_FLOAT => PDO::PARAM_STR,
	);
	function __construct(){
		parent::__construct("pdo");
	}

	public function connect(){
		global $pb_config;

		if(!class_exists("PDO")){
			die("PDO not supported");
		}

		$dsn_ = "mysql:host=".$pb_config->db_host.";port=".$pb_config->db_port.";dbname=".$pb_config->db_name.";charset=".$pb_config->db_charset;

		$this->_connection = new PDO($dsn_, $pb_config->db_username, $pb_config->db_userpass) Or die("Error On DB Connection : PDO");


		if($pb_config->is_show_database_error()){
			$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

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

		$statement_ = $this->_connection->prepare($query_);

		foreach($values_ as $index_ => $value_){
			$column_type_ = isset($types_[$index_]) ? $types_[$index_] : PBDB::TYPE_STRING;
			$column_type_ = $this->data_types[$column_type_];

			if($value_ !== null){
				$statement_->bindValue(":pdo_param{$index_}", $value_, $column_type_);	
			}else{
				$statement_->bindValue(":pdo_param{$index_}", null, PDO::PARAM_NULL);	
			}
		}

		try{
			$result_ = $statement_->execute();

			$this->_last_query = $query_;
			$this->_last_query_paramters = array('values' => $values_, 'types' => $types_);

			if(!$result_){
				$this->_last_error_no = $this->_connection->errorCode();
				$this->_last_error_message = $this->_connection->errorInfo();
				$this->_last_error_message = implode(":", $this->_last_error_message);

				return false;
			}

		}catch(PDOException $ex_){
			$this->_last_query = $query_;
			$this->_last_query_paramters = array('values' => $values_, 'types' => $types_);

			$this->_last_error_no = $ex_->getCode();
			$this->_last_error_message = $ex_->errorInfo;
			$this->_last_error_message = implode(":", $this->_last_error_message);

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
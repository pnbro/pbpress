<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define("PB_MYSQL_ASSOC", 1);
define("PB_MYSQL_NUM", 2);
define("PB_MYSQL_BOTH", 3);

abstract class PBDatabase_connection{

	private $_last_query = null;
	private $_last_query_parameters = null;

	abstract public function host();
	abstract public function port();
	abstract public function db_name();
	abstract public function charset();
	abstract public function username();
	abstract public function userpass();
	
	function __construct($connection_type_){
		$this->_connection_type = $connection_type_;
	}

	public function connection_type(){
		return $this->_connection_type;
	}

	public function set_last_query($query_){
		$this->_last_query = $query_;	
	}
	public function last_query(){
		return $this->_last_query;
	}
	
	public function set_last_query_parameters($params_){
		$this->_last_query_parameters = $params_;
	}
	public function last_query_parameters(){
		return $this->_last_query_parameters;
	}

	abstract protected function escape_string($str);

	abstract protected function connect($options_ = array());
	abstract protected function query($query_, $values_ = array(), $types_ = array());
	abstract protected function inserted_id();
	abstract protected function last_error();
	abstract protected function last_error_trace();

	abstract protected function num_rows($resource_);
	abstract protected function fetch_array($resource_);

	abstract protected function autocommit($bool_);
	abstract protected function commit();
	abstract protected function rollback();
	abstract protected function close_connection();

	public function __destruct(){
		pb_hook_do_action('pb_db_connection_before_close', $this);
		$this->close_connection();
		pb_hook_do_action('pb_db_connection_before_closed', $this);
	}
}

include(PB_DOCUMENT_PATH . "includes/common/database-mysql.php");
include(PB_DOCUMENT_PATH . "includes/common/database-mysqli.php");
include(PB_DOCUMENT_PATH . "includes/common/database-pdo.php");

define('PBDB_PARAM_MAP_STR', "::param::");
define('PBDB_P', PBDB_PARAM_MAP_STR); //shortcut

function pb_database_connection_types(){
	return pb_hook_apply_filters("pb_database_connection_types", array());
}

function pb_database_escape_string($str_){
	global $pb_db_connection;
	return $pb_db_connection->escape_string($str_);
}

global $pb_config, $pb_db_connection;

$connection_type_ = $pb_config->db_connection_type;

if(!strlen($connection_type_)){
	$connection_type_ = function_exists("mysql_connect") ? "mysql" : "mysqli";
}

$connection_type_data_ = pb_database_connection_types();
$connection_type_data_ = isset($connection_type_data_[$connection_type_]) ? $connection_type_data_[$connection_type_] : null;
	
if(!isset($connection_type_data_)){
	die("wrong DB connection method");
}
$pb_db_connection = new $connection_type_data_['connection_class']();

if(!isset($pb_db_connection) || !$pb_db_connection->connect()){
	die("Error On DB Connection");
}

$pb_db_connection->query('set names '.$pb_config->db_charset);

global $pbdb;

class PBDB{

	private $_db_connection;

	const TYPE_STRING = "%s";
	const TYPE_NUMBER = "%d";
	const TYPE_FLOAT = "%f";

	function __construct($db_connection_){
		$this->_db_connection = $db_connection_;
	}

	function db_connection(){
		return $this->_db_connection;
	}

	function query($query_, $values_ = array(), $types_ = array()){
		$result_ = pb_hook_apply_filters("pb_database_query",$this->_db_connection->query($query_, $values_, $types_));
		$last_error_ = $this->last_error();

		if(pb_is_error($last_error_)){
			pb_hook_do_action('pb_database_error_occurred', $last_error_);
		}

		return $result_;
	}

	function last_query(){
		return $this->_db_connection->last_query();
	}
	function last_query_parameters(){
		return $this->_db_connection->last_query_parameters();
	}

	function select($query_, $values_ = array(), $types_ = array()){
		$resources_ = $this->query($query_, $values_, $types_);

		if(!isset($resources_)) return null;

    	$results_ = array();
    	while($row_data_ = $this->_db_connection->fetch_array($resources_, PB_MYSQL_ASSOC)){
			$results_[] = $row_data_;
    	}

		return $results_;
	}

	function get_first_row($query_, $values_ = array(), $types_ = array()){
		$resources_ = $this->query($query_, $values_, $types_);

		if(!isset($resources_)) return null;

    	while($row_data_ = $this->_db_connection->fetch_array($resources_, PB_MYSQL_ASSOC)){
			return $row_data_;
    	}

		return null;
	}

	function get_var($query_, $values_ = array(), $types_ = array()){
		$resources_ = $this->query($query_, $values_, $types_);

		if(!isset($resources_)) return null;

    	while($row_data_ = $this->_db_connection->fetch_array($resources_, PB_MYSQL_NUM)){
			return $row_data_[0];
    	}

		return null;
	}

	function serialize_column($query_, $column_name_, $values_ = array(), $types_ = array()){
		$resources_ = $this->query($query_, $values_, $types_);

		if(!isset($resources_)) return null;

		$results_ = array();
    	while($row_data_ = $this->_db_connection->fetch_array($resources_, PB_MYSQL_ASSOC)){
			$results_[] = $row_data_[$column_name_];
    	}

		return $results_;
	}

	function insert($table_name_, $insert_data_, $insert_data_types_ = array()){
		$insert_query_ = "INSERT into {$table_name_}( ";
		$query_column_str_ = "";
		$query_type_str_ = "";

		$start_column_ = true;
		$col_index_ = 0;

		$values_ = array();
		$types_ = array();
		foreach($insert_data_ as $column_name_ => $column_value_){
			if(!$start_column_){
				$query_column_str_ .= ",";
				$query_type_str_ .= ",";
			}else{
				$start_column_ = false;
			}

			if($column_value_ === null){
				$types_[] = PBDB::TYPE_NUMBER;
				$values_[] = null;

				$query_column_str_ .= strtolower($column_name_);
				$query_type_str_ .= PBDB_PARAM_MAP_STR;
			}else{
				$values_[] = $this->_db_connection->escape_string($column_value_);

				$query_column_str_ .= strtolower($column_name_);
				if(isset($insert_data_types_[$col_index_])){
					$query_type_str_ .= PBDB_PARAM_MAP_STR;
					$types_[] = $insert_data_types_[$col_index_];
				}else{
					$query_type_str_ .= PBDB_PARAM_MAP_STR;
					$types_[] = PBDB::TYPE_STRING;
				}
			}

			++$col_index_;
		}

		$insert_query_ .= $query_column_str_;
		$insert_query_ .= ") VALUES(";
		$insert_query_ .= $query_type_str_;
		$insert_query_ .= ")";

		$result_ = $this->query($insert_query_, $values_, $types_);

		if(!$result_){
			return $result_;
		}

		return $this->_db_connection->inserted_id($this->_db_connection);
	}

	function update($table_name_, $update_data_, $key_data_, $update_data_types_ = array(), $update_key_types_ = array()){
		$update_query_ = "UPDATE {$table_name_} SET ";

		$column_value_str_ = "";

		$values_ = array();
		$types_ = array();

		$start_column_ = true;
		$col_index_ = 0;

		foreach($update_data_ as $column_name_ => $column_value_){
			if(!$start_column_){
				$column_value_str_ .= ",";
			}else{
				$start_column_ = false;
			}

			if($column_value_ === null){
				$types_[] = PBDB::TYPE_NUMBER;
				$values_[] = null;
			}else{
				$values_[] = $this->_db_connection->escape_string($column_value_);	
				$column_type_ = PBDB::TYPE_STRING;
				if(isset($update_data_types_[$col_index_])){
					$column_type_ = $update_data_types_[$col_index_];
				}
				$types_[] = $column_type_;

			}
			$column_value_str_ .= "{$column_name_} = ".PBDB_PARAM_MAP_STR." ";
			++$col_index_;
		}

		$where_value_str_ = "";

		$start_column_ = true;
		$col_index_ = 0;

		foreach($key_data_ as $column_name_ => $column_value_){
			$values_[] = $this->_db_connection->escape_string($column_value_);

			$where_value_str_ .= "AND ";

			$column_type_ = PBDB::TYPE_STRING;
			if(isset($update_key_types_[$col_index_])){
				$column_type_ = $update_key_types_[$col_index_];
			}
			$types_[] = $column_type_;

			$where_value_str_ .= "{$column_name_} = ".PBDB_PARAM_MAP_STR." ";

			++$col_index_;
		}

		$update_query_ .= $column_value_str_;
		$update_query_ .= " WHERE 1 ";
		$update_query_ .= $where_value_str_;

		$result_ = $this->query($update_query_, $values_, $types_);

		if(!$result_){
			return $result_;
		}
		return true;
	}

	function delete($table_name_, $key_data_, $delete_key_types_ = array()){
		$delete_query_ = "DELETE FROM {$table_name_} ";

		$values_ = array();
		$types_ = array();
		$where_value_str_ = "";

		$start_column_ = true;
		$col_index_ = 0;

		foreach($key_data_ as $column_name_ => $column_value_){
			$values_[] = $this->_db_connection->escape_string($column_value_);

			$where_value_str_ .= "AND ";

			$column_type_ = PBDB::TYPE_STRING;
			if(isset($delete_key_types_[$col_index_])){
				$column_type_ = $delete_key_types_[$col_index_];
			}

			$where_value_str_ .= "{$column_name_} = ".PBDB_PARAM_MAP_STR." ";
			$types_[] = $column_type_;

			++$col_index_;
		}

		$delete_query_ .= " WHERE 1 ";
		$delete_query_ .= $where_value_str_;

		$result_ = $this->query($delete_query_, $values_, $types_);

		return $result_;
	}
	function inserted_id(){
		return $this->_db_connection->inserted_id();
	}

	function last_error(){
		return $this->_db_connection->last_error();	
	}
	function last_error_trace(){
		return $this->_db_connection->last_error_trace();	
	}

	function autocommit($bool_){
		$this->_db_connection->autocommit($bool_);
	}

	function commit(){
		$this->_db_connection->commit();
	}
	function rollback(){
		$this->_db_connection->rollback();
	}

	function install_tables(){
		$query_list_ = pb_hook_apply_filters("pb_install_tables", array());

		$this->autocommit(false);

		try{
			foreach($query_list_ as $query_){
				$this->query($query_);
			}

			$this->commit();
		}catch(Exception $ex_){
			$this->rollback();

			return new PBError(503, "DB설치실패", $ex_->getMessage());
		}
		$this->autocommit(true);

		pb_hook_do_action("pb_installed_tables");
	}

	function exists_column($table_name_, $column_name_){
		global $_pb_database_ignore_print_error;
		$_pb_database_ignore_print_error = true;
		$check_ = @$this->_db_connection->query("SELECT {$column_name_} FROM {$table_name_} LIMIT 0, 1");
		$_pb_database_ignore_print_error = false;
		return ($check_ !== false);
	}

	function exists_table($table_name_){
		global $_pb_database_ignore_print_error;
		$_pb_database_ignore_print_error = true;
		$check_ = @$this->_db_connection->query("SELECT 1 FROM {$table_name_} LIMIT 0, 1");
		$_pb_database_ignore_print_error = false;
		return ($check_ !== false);
	}

	function close_connection(){
		$this->_db_connection->close_connection();	
	}

	function is_default_connection($target_){
		global $pb_db_connection;
		return $pb_db_connection === $target_;
	}
}


$pbdb = new PBDB($pb_db_connection);

if($pb_config->is_show_database_error()){
	function _pb_database_hook_print_error($last_error_){
		global $_pb_database_ignore_print_error;
		if(!!$_pb_database_ignore_print_error) return;
		echo "[".$last_error_->error_code()."] ".$last_error_->error_message()."\r\n";

		global $pbdb;
		foreach($pbdb->last_error_trace() as $index_ => $trace_data_){
			if(isset($trace_data_['file']))
				echo $trace_data_['file']." [line:".$trace_data_['line']."] - ".$trace_data_['function']."\r\n";
			else echo $trace_data_['function']."\r\n";
		}
	}
	pb_hook_add_action('pb_database_error_occurred','_pb_database_hook_print_error');
}

include(PB_DOCUMENT_PATH . "includes/common/database-select-statement.php");
include(PB_DOCUMENT_PATH . "includes/common/database-do.php");
	
?>
<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define("PB_MYSQL_ASSOC", 1);
define("PB_MYSQL_NUM", 2);
define("PB_MYSQL_BOTH", 3);

abstract class PBDatabase_connection{

	private $_connection_type;
	
	function __construct($connection_type_){
		$this->_connection_type = $connection_type_;
	}

	public function connection_type(){
		return $this->_connection_type;
	}

	abstract protected function escape_string($str);

	abstract protected function connect();
	abstract protected function query($query_);
	abstract protected function inserted_id();

	abstract protected function num_rows($resource_);
	abstract protected function fetch_array($resource_);

	abstract protected function commit();
	abstract protected function rollback();
	abstract protected function close_connection();
}

include(PB_DOCUMENT_PATH . "includes/common/database-mysql.php");
include(PB_DOCUMENT_PATH . "includes/common/database-mysqli.php");

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

	private $_last_select_row_count = 0;
	private $_last_query = null;

	function query($query_){
		global $pb_db_connection;
		return $pb_db_connection->query($query_);
	}

	function last_query(){
		global $pb_db_connection;
		return $this->_last_query;
	}

	function select($query_){
		global $pb_db_connection;

		$resources_ = $pb_db_connection->query($query_);
		$this->_last_query = $query_;

		if(!isset($resources_)) return null;

		$this->_last_select_row_count = $pb_db_connection->num_rows($resources_); 
		if(!$this->_last_select_row_count || $this->_last_select_row_count == 0) return array();

    	$results_ = array();
    	while($row_data_ = $pb_db_connection->fetch_array($resources_, PB_MYSQL_ASSOC)){
			$results_[] = $row_data_;
    	}

		return $results_;
	}

	function last_select_row_count(){
		return $this->_last_select_row_count;
	}

	function get_first_row($query_){
		global $pb_db_connection;

		$resources_ = $pb_db_connection->query($query_);
		$this->_last_query = $query_;

		if(!isset($resources_)) return null;

    	while($row_data_ = $pb_db_connection->fetch_array($resources_, PB_MYSQL_ASSOC)){
			return $row_data_;
    	}

		return null;
	}

	function get_var($query_){
		global $pb_db_connection;

		$resources_ = $pb_db_connection->query($query_);
		$this->_last_query = $query_;

		if(!isset($resources_)) return null;

    	while($row_data_ = $pb_db_connection->fetch_array($resources_, PB_MYSQL_NUM)){
			return $row_data_[0];
    	}

		return null;
	}


	function prepare($query_, $data_){
		$query_ = str_replace( "'%s'", '%s', $query_);
		$query_ = str_replace( '"%s"', '%s', $query_);
		$query_ = preg_replace( '/(?<!%)%s/', "'%s'", $query_);
		$query_ = str_replace( "##null##", '%s', $query_);

		return vsprintf($query_, $data_);
	}

	function insert($table_name_, $insert_data_, $insert_data_types_ = array()){
		global $pb_db_connection;

		$insert_query_ = "INSERT into {$table_name_}( ";
		$query_column_str_ = "";
		$query_type_str_ = "";

		$start_column_ = true;
		$col_index_ = 0;

		$field_maps_ = array();
		foreach($insert_data_ as $column_name_ => $column_value_){
			$field_maps_[] = $pb_db_connection->escape_string($column_value_);

			if(!$start_column_){
				$query_column_str_ .= ",";
				$query_type_str_ .= ",";
			}else{
				$start_column_ = false;
			}

			$query_column_str_ .= strtolower($column_name_);
			if(isset($insert_data_types_[$col_index_])){
				$query_type_str_ .= $insert_data_types_[$col_index_];
			}else{
				$query_type_str_ .= "%s";
			}

			++$col_index_;
		}

		$insert_query_ .= $query_column_str_;
		$insert_query_ .= ") VALUES(";
		$insert_query_ .= $query_type_str_;
		$insert_query_ .= ")";

		$insert_query_ = $this->prepare($insert_query_, $field_maps_);

		$result_ = $pb_db_connection->query($insert_query_);
		$this->_last_query = $insert_query_;

		if(!$result_){
			return $result_;
		}

		return $pb_db_connection->inserted_id($pb_db_connection);
	}

	function update($table_name_, $update_data_, $key_data_, $update_data_types_ = array(), $update_key_types_ = array()){
		global $pb_db_connection;

		$update_query_ = "UPDATE {$table_name_} SET ";

		$column_value_str_ = "";
		$field_maps_ = array();

		$start_column_ = true;
		$col_index_ = 0;

		foreach($update_data_ as $column_name_ => $column_value_){
			$field_maps_[] = $pb_db_connection->escape_string($column_value_);

			if(!$start_column_){
				$column_value_str_ .= ",";
			}else{
				$start_column_ = false;
			}

			$column_type_ = "%s";
			if(isset($update_data_types_[$col_index_])){
				$column_type_ = $update_data_types_[$col_index_];
			}

			$column_value_str_ .= "{$column_name_} = {$column_type_} ";

			++$col_index_;
		}

		$where_value_str_ = "";

		$start_column_ = true;
		$col_index_ = 0;

		foreach($key_data_ as $column_name_ => $column_value_){
			$field_maps_[] = $pb_db_connection->escape_string($column_value_);

			$where_value_str_ .= "AND ";

			$column_type_ = "%s";
			if(isset($update_data_types_[$col_index_])){
				$column_type_ = $update_data_types_[$col_index_];
			}

			$where_value_str_ .= "{$column_name_} = {$column_type_} ";

			++$col_index_;
		}

		$update_query_ .= $column_value_str_;
		$update_query_ .= " WHERE 1 ";
		$update_query_ .= $where_value_str_;

		$update_query_ = $this->prepare($update_query_, $field_maps_);

		

		$result_ = $pb_db_connection->query($update_query_);
		$this->_last_query = $update_query_;

		if(!$result_){
			return $result_;
		}
		return true;
	}

	function delete($table_name_, $key_data_, $delete_key_types_ = array()){
		global $pb_db_connection;

		$delete_query_ = "DELETE FROM {$table_name_} ";

		$field_maps_ = array();
		$where_value_str_ = "";

		$start_column_ = true;
		$col_index_ = 0;

		foreach($key_data_ as $column_name_ => $column_value_){
			$field_maps_[] = $pb_db_connection->escape_string($column_value_);

			$where_value_str_ .= "AND ";

			$column_type_ = "%s";
			if(isset($delete_key_types_[$col_index_])){
				$column_type_ = $delete_key_types_[$col_index_];
			}

			$where_value_str_ .= "{$column_name_} = {$column_type_} ";

			++$col_index_;
		}

		$delete_query_ .= " WHERE 1 ";
		$delete_query_ .= $where_value_str_;

		$delete_query_ = $this->prepare($delete_query_, $field_maps_);

		$result_ = $pb_db_connection->query($delete_query_, $pb_db_connection);
		$this->_last_query = $delete_query_;

		return $result_;
	}
	function inserted_id(){
		global $pb_db_connection;
		return $pb_db_connection->inserted_id();
	}

	function commit(){
		global $pb_db_connection;
		$pb_db_connection->commit();
	}
	function rollback(){
		global $pb_db_connection;
		$pb_db_connection->rollback();
	}

	function install_tables(){
		global $pb_db_connection;
		$query_list_ = pb_hook_apply_filters("pb_install_tables", array());

		foreach($query_list_ as $query_){
			$pb_db_connection->query($query_);
		}

		pb_hook_do_action("pb_installed_tables");

		$pb_db_connection->commit();
	}

	function exists_column($table_name_, $column_name_){
		global $pbdb,$pb_config, $pb_db_connection;

		$query_ = "SELECT count(*) CNT 
			FROM information_schema.COLUMNS 
			WHERE 
			    TABLE_SCHEMA = '".$pb_config->db_name."'
			AND TABLE_NAME = '".$pb_db_connection->escape_string($table_name_)."' 
			AND COLUMN_NAME = '".$pb_db_connection->escape_string($column_name_)."' ";

		return ($pbdb->get_var($query_) > 0);
	}

	function exists_table($table_name_){
		global $pbdb,$pb_config, $pb_db_connection;
		$query_ = "SELECT COUNT(*) CNT 
			FROM information_schema.tables
			WHERE table_schema = '".$pb_db_connection->escape_string($pb_config->db_name)."' 
			AND table_name = '".$pb_db_connection->escape_string($table_name_)."' ";

		return ($pbdb->get_var($query_) > 0);
	}
}


$pbdb = new PBDB();

function _pb_database_close_hook(){
	global $pbdb,$pb_db_connection;
	$pbdb->commit();
	$pb_db_connection->close_connection();
}
pb_hook_add_action('pb_ended', "_pb_database_close_hook");
	
?>
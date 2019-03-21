<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $pb_config, $pb_db_connection;

$pb_db_connection =	@mysql_connect($pb_config->db_host, $pb_config->db_username, $pb_config->db_userpass, true) Or die("Error On DB Connection");
mysql_select_db($pb_config->db_name) Or die("Error On Select DB");
mysql_query('set names '.$pb_config->db_charset);

global $pbdb;

class PBDB{

	function query($query_){
		global $pb_db_connection;
		return mysql_query($query_, $pb_db_connection);
	}

	private $_last_select_row_count = 0;
	private $_last_query = null;

	function last_query(){
		return $this->_last_query;
	}

	function select($query_){
		global $pb_db_connection;

		$resources_ = mysql_query($query_, $pb_db_connection);
		$this->_last_query = $query_;

		if(!isset($resources_)) return null;

		$this->_last_select_row_count = @mysql_num_rows($resources_); 
		if(!$this->_last_select_row_count || $this->_last_select_row_count == 0) return array();

    	$results_ = array();
    	while($row_data_ = mysql_fetch_array($resources_, MYSQL_ASSOC)){
			$results_[] = $row_data_;
    	}

		return $results_;
	}

	function last_select_row_count(){
		return $this->_last_select_row_count;
	}

	function get_first_row($query_){
		global $pb_db_connection;

		$resources_ = mysql_query($query_, $pb_db_connection);
		$this->_last_query = $query_;

		if(!isset($resources_)) return null;

    	while($row_data_ = mysql_fetch_array($resources_, MYSQL_ASSOC)){
			return $row_data_;
    	}

		return null;
	}

	function get_var($query_){
		global $pb_db_connection;

		$resources_ = mysql_query($query_, $pb_db_connection);
		$this->_last_query = $query_;

		if(!isset($resources_)) return null;

    	while($row_data_ = mysql_fetch_array($resources_, MYSQL_NUM)){
			return $row_data_[0];
    	}

		return null;
	}


	function prepare($query_, $data_){
		$query_ = str_replace( "'%s'", '%s', $query_);
		$query_ = str_replace( '"%s"', '%s', $query_);
		$query_ = preg_replace( '/(?<!%)%s/', "'%s'", $query_);

		return vsprintf($query_, $data_);
	}

	function insert($table_name_, $insert_data_, $insert_data_types_ = array()){
		$insert_query_ = "INSERT into {$table_name_}( ";
		$query_column_str_ = "";
		$query_type_str_ = "";

		$start_column_ = true;
		$col_index_ = 0;

		$field_maps_ = array();
		foreach($insert_data_ as $column_name_ => $column_value_){
			$field_maps_[] = mysql_real_escape_string($column_value_);

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

		global $pb_db_connection;

		$result_ = mysql_query($insert_query_, $pb_db_connection);
		$this->_last_query = $insert_query_;

		if(!$result_){
			return $result_;
		}

		return mysql_insert_id($pb_db_connection);
	}

	function update($table_name_, $update_data_, $key_data_, $update_data_types_ = array(), $update_key_types_ = array()){
		$update_query_ = "UPDATE {$table_name_} SET ";

		$column_value_str_ = "";
		$field_maps_ = array();

		$start_column_ = true;
		$col_index_ = 0;

		foreach($update_data_ as $column_name_ => $column_value_){
			$field_maps_[] = mysql_real_escape_string($column_value_);

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
			$field_maps_[] = mysql_real_escape_string($column_value_);

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

		global $pb_db_connection;

		$result_ = mysql_query($update_query_, $pb_db_connection);
		$this->_last_query = $update_query_;

		if(!$result_){
			return $result_;
		}

		return mysql_insert_id($pb_db_connection);
	}

	function delete($table_name_, $key_data_, $update_key_types_ = array()){
		$delete_query_ = "DELETE FROM {$table_name_} ";

		$field_maps_ = array();
		$where_value_str_ = "";

		$start_column_ = true;
		$col_index_ = 0;

		foreach($key_data_ as $column_name_ => $column_value_){
			$field_maps_[] = mysql_real_escape_string($column_value_);

			$where_value_str_ .= "AND ";

			$column_type_ = "%s";
			if(isset($update_data_types_[$col_index_])){
				$column_type_ = $update_data_types_[$col_index_];
			}

			$where_value_str_ .= "{$column_name_} = {$column_type_} ";

			++$col_index_;
		}

		$delete_query_ .= " WHERE 1 ";
		$delete_query_ .= $where_value_str_;

		$delete_query_ = $this->prepare($delete_query_, $field_maps_);

		global $pb_db_connection;

		$result_ = mysql_query($delete_query_, $pb_db_connection);
		$this->_last_query = $delete_query_;

		return $result_;
	}
	function inserted_id(){
		global $pb_db_connection;
		return mysql_insert_id($pb_db_connection);
	}

	function commit(){
		global $pb_db_connection;
		mysql_query("commit", $pb_db_connection);
	}
	function rollback(){
		global $pb_db_connection;
		mysql_query("rollback", $pb_db_connection);
	}

	function install_tables(){
		global $pbdb;
		$query_list_ = pb_hook_apply_filters("pb_install_tables", array());

		foreach($query_list_ as $query_){
			$pbdb->query($query_);
		}

		pb_hook_do_action("pb_installed_tables");

		$pbdb->commit();
	}

	function exists_column($table_name_, $column_name_){
		global $pbdb,$pb_config;

		$query_ = "SELECT count(*) CNT 
			FROM information_schema.COLUMNS 
			WHERE 
			    TABLE_SCHEMA = '".$pb_config->db_name."'
			AND TABLE_NAME = '".mysql_real_escape_string($table_name_)."' 
			AND COLUMN_NAME = '".mysql_real_escape_string($column_name_)."' ";

		return ($pbdb->get_var($query_) > 0);
	}

	function exists_table($table_name_){
		global $pbdb,$pb_config;
		$query_ = "SELECT COUNT(*) CNT 
			FROM information_schema.tables
			WHERE table_schema = '".mysql_real_escape_string($pb_config->db_name)."' 
			AND table_name = '".mysql_real_escape_string($table_name_)."' ";

		return ($pbdb->get_var($query_) > 0);
	}
}


$pbdb = new PBDB();

function _pb_database_close_hook(){
	global $pbdb,$pb_db_connection;
	$pbdb->commit();
	@mysql_close($pb_db_connection);
}
pb_hook_add_action('pb_ended', "_pb_database_close_hook");
	
?>
<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBDB_DO extends ArrayObject{

	const TYPE_CHAR = "CHAR";
	const TYPE_VARCHAR = "VARCHAR";
	const TYPE_TINYTEXT = "TINYTEXT";
	const TYPE_TEXT = "TEXT";
	const TYPE_MEDIUMTEXT = "MEDIUMTEXT";
	const TYPE_LONGTEXT = "LONGTEXT";
	const TYPE_BIT = "BIT";
	const TYPE_BOOL = "BOOL";
	const TYPE_BOOLEAN = "BOOLEAN";
	const TYPE_TINYINT = "TINYINT";
	const TYPE_SMALLINT = "SMALLINT";
	const TYPE_MEDIUMINT = "MEDIUMINT";
	const TYPE_INT = "INT";
	const TYPE_BIGINT = "BIGINT";
	const TYPE_DOUBLE = "DOUBLE";
	const TYPE_REAL = "REAL";
	const TYPE_FLOAT = "FLOAT";
	const TYPE_DECIMAL = "DECIMAL";
	const TYPE_DEC = "DEC";
	const TYPE_NUMERIC = "NUMERIC";
	const TYPE_FIXED = "FIXED";
	const TYPE_DATE = "DATE";
	const TYPE_TIME = "TIME";
	const TYPE_DATETIME = "DATETIME";
	const TYPE_TIMESTAMP = "TIMESTAMP";
	const TYPE_YEAR = "YEAR";
	const TYPE_BINARY = "BINARY";
	const TYPE_VARBINARY = "VARBINARY";
	const TYPE_TINYBLOB = "TINYBLOB";
	const TYPE_BLOB = "BLOB";
	const TYPE_MEDIUMBLOB = "MEDIUMBLOB";
	const TYPE_LONGBLOB = "LONGBLOB";

	const FK_CASCADE = "CASCADE";
	const FK_SETNULL = "SET NULL";
	const FK_NOACTION = "NO ACTION";

	public static function convert_to_pbdb_type($type_){
		switch($type_){
			case PBDB_DO::TYPE_CHAR : 
			case PBDB_DO::TYPE_VARCHAR : 
			case PBDB_DO::TYPE_TINYTEXT : 
			case PBDB_DO::TYPE_TEXT : 
			case PBDB_DO::TYPE_MEDIUMTEXT : 
			case PBDB_DO::TYPE_LONGTEXT : 
			case PBDB_DO::TYPE_DATE : 
			case PBDB_DO::TYPE_TIME : 
			case PBDB_DO::TYPE_DATETIME : 
			case PBDB_DO::TYPE_TIMESTAMP : 
			case PBDB_DO::TYPE_YEAR : 
			case PBDB_DO::TYPE_BINARY : 
			case PBDB_DO::TYPE_VARBINARY : 
			case PBDB_DO::TYPE_TINYBLOB : 
			case PBDB_DO::TYPE_BLOB : 
			case PBDB_DO::TYPE_MEDIUMBLOB : 
			case PBDB_DO::TYPE_LONGBLOB : 
				return PBDB::TYPE_STRING;

			case PBDB_DO::TYPE_BIT : 
			case PBDB_DO::TYPE_BOOL : 
			case PBDB_DO::TYPE_BOOLEAN : 
			case PBDB_DO::TYPE_TINYINT : 
			case PBDB_DO::TYPE_SMALLINT : 
			case PBDB_DO::TYPE_MEDIUMINT : 
			case PBDB_DO::TYPE_INT : 
			case PBDB_DO::TYPE_BIGINT : 
				return PBDB::TYPE_NUMBER;

			case PBDB_DO::TYPE_DOUBLE : 
			case PBDB_DO::TYPE_REAL : 
			case PBDB_DO::TYPE_FLOAT : 
			case PBDB_DO::TYPE_DECIMAL : 
			case PBDB_DO::TYPE_DEC : 
			case PBDB_DO::TYPE_NUMERIC : 
			case PBDB_DO::TYPE_FIXED : 
				return PBDB::TYPE_FLOAT;
				
			default : return PBDB::TYPE_STRING;
		}
	}

	private $_table_name;
	private $_engine;
	private $_comment;
	private $_fields = array();
	private $_keys = array();
	private $_indexes = array();

	function __construct($table_, $fields_, $comment_ = null, $engine_ = "InnoDB"){
		$this->_table_name = $table_;
		$this->_fields = $fields_;
		$this->_comment = $comment_;
		$this->_engine = $engine_;

		foreach($this->_fields as $column_name_ => $field_data_){
			if(isset($field_data_['pk']) && !!$field_data_['pk']){
				$this->_keys[] = $column_name_;
			}else if(isset($field_data_['index']) && !!$field_data_['index']){
				$this->_indexes[] = $column_name_;
			}
		}

		pb_hook_add_filter('pb_install_tables', array($this, "_install_tables"));
		pb_hook_add_action('pb_installed_tables', array($this, "_installed_tables"));
	}

	function add_field($array_){
		$this->_fields = array_merge($this->_fields, $array_);
	}

	function _install_tables($querys_){
		$query_ = "CREATE TABLE IF NOT EXISTS `{$this->_table_name}` ( \n\r";

		$field_query_ = array();
		$key_query_ = array();

		if(count($this->_keys) > 0){
			$pk_str_ = [];
			foreach($this->_keys as $column_name_){
				$pk_str_[] = "`{$column_name_}`";
			}

			$key_query_[] = "PRIMARY KEY (".implode(",", $pk_str_).")";
		}
		if(count($this->_indexes) > 0){
			$index_str_ = [];
			foreach($this->_indexes as $column_name_){
				$index_str_[] = "`{$column_name_}`";
			}

			$key_query_[] = "KEY (".implode(",", $index_str_).")";
		}

		$fk_index_ = 0;

		foreach($this->_fields as $column_name_ => $field_data_){
			$type_ = isset($field_data_['type']) ? $field_data_['type'] : PBDB_DO::TYPE_VARCHAR;
			$length_ = isset($field_data_['length']) ? $field_data_['length'] : null;
			$comment_ = isset($field_data_['comment']) ? $field_data_['comment'] : null;
			$not_null_ = isset($field_data_['nn']) ? $field_data_['nn'] : false;
			$pk_ = isset($field_data_['pk']) ? $field_data_['pk'] : false;
			$default_ = isset($field_data_['default']) ? $field_data_['default'] : null;
			$auto_increment_ = isset($field_data_['ai']) ? $field_data_['ai'] : false;

			$field_str_ = "`{$column_name_}` {$type_}".(strlen($length_) ? "({$length_})" : "")." ";

			if($not_null_ || $pk_){
				$field_str_ .= "NOT NULL ";
			}else{
				if(strlen($default_)){
					$default_ = PBDB_DO::convert_to_pbdb_type($type_) === PBDB::TYPE_STRING ? "'{$default_}'" : $default_;
				}else $default_ = "NULL";

				$field_str_ .= "DEFAULT {$default_} ";
			}

			if($auto_increment_){
				$field_str_ .= "AUTO_INCREMENT ";
			}

			if(strlen($comment_)){
				$field_str_ .= 	"COMMENT '{$comment_}' ";
			}

			if(isset($field_data_['fk'])){

				$target_table_ = $field_data_['fk']['table'];
				$target_column_ = $field_data_['fk']['column'];
				$on_delete_ = isset($field_data_['fk']['delete']) ? $field_data_['fk']['delete'] : PBDB_DO::FK_NOACTION;
				$on_update_ = isset($field_data_['fk']['update']) ? $field_data_['fk']['update'] : PBDB_DO::FK_NOACTION;

				++$fk_index_;

				$key_query_[] = "CONSTRAINT `{$this->_table_name}_fk{$fk_index_}` FOREIGN KEY (`{$column_name_}`) REFERENCES `{$target_table_}` (`{$target_column_}`) ON DELETE {$on_delete_} ON UPDATE {$on_update_}";
			}

			$field_query_[] = trim($field_str_);
		}

		$query_ .= implode(",\n\r", array_merge($field_query_, $key_query_)). " \n\r";			
		$query_ .= " ) ENGINE={$this->_engine} COMMENT='{$this->_comment}';";


		$querys_[] = $query_;

		return $querys_;
	}

	function _installed_tables(){
		global $pbdb;

		$fk_last_index_ = 0;
		foreach($this->_fields as $column_name_ => $field_data_){
			if(isset($field_data_['check_exists'])) continue;

			if(isset($field_data_['fk'])){
				++$fk_last_index_;
			}
		}

		foreach($this->_fields as $column_name_ => $field_data_){
			if(isset($field_data_['check_exists']) && !!$field_data_['check_exists']){

				if(!$this->is_column_exists($column_name_)){

					$type_ = isset($field_data_['type']) ? $field_data_['type'] : PBDB_DO::TYPE_VARCHAR;
					$length_ = isset($field_data_['length']) ? $field_data_['length'] : null;
					$comment_ = isset($field_data_['comment']) ? $field_data_['comment'] : null;
					$not_null_ = isset($field_data_['nn']) ? $field_data_['nn'] : false;
					$pk_ = isset($field_data_['pk']) ? $field_data_['pk'] : false;
					$default_ = isset($field_data_['default']) ? $field_data_['default'] : null;
					$auto_increment_ = isset($field_data_['ai']) ? $field_data_['ai'] : false;

					if(!!$pk_) continue;

					$query_ = "ALTER TABLE {$this->_table_name} ADD `{$column_name_}` {$type_}".(strlen($length_) ? "({$length_})" : "")." ";

					if($not_null_ || $pk_){
						$query_ .= "NOT NULL ";
					}else{
						if(strlen($default_)){
							$default_ = PBDB_DO::convert_to_pbdb_type($type_) === PBDB::TYPE_STRING ? "'{$default_}'" : $default_;
						}else $default_ = "NULL";

						$query_ .= "DEFAULT {$default_} ";
					}

					if($auto_increment_){
						$query_ .= "AUTO_INCREMENT ";
					}

					if(strlen($comment_)){
						$query_ .= 	"COMMENT '{$comment_}' ";
					}

					if(isset($field_data_['fk'])){
						++$fk_last_index_;

						$target_table_ = $field_data_['fk']['table'];
						$target_column_ = $field_data_['fk']['column'];
						$on_delete_ = isset($field_data_['fk']['delete']) ? $field_data_['fk']['delete'] : PBDB_DO::FK_NOACTION;
						$on_update_ = isset($field_data_['fk']['update']) ? $field_data_['fk']['update'] : PBDB_DO::FK_NOACTION;

						$query_ .= 	", ADD CONSTRAINT `{$this->_table_name}_fk{$fk_last_index_}` FOREIGN KEY (`{$column_name_}`) REFERENCES `{$target_table_}` (`{$target_column_}`) ON DELETE {$on_delete_} ON UPDATE {$on_update_}";

					}

					$pbdb->query($query_);
				}

			}
		}

	}

	function is_exists(){
		global $pbdb;
		return $pbdb->exists_table($this->_table_name);
	}
	function is_column_exists($column_value_){
		global $pbdb;
		return $pbdb->exists_column($this->_table_name, $column_value_);
	}

	function table_name(){
		return $this->_table_name;
	}

	function fields(){
		return $this->_fields;
	}
	function keys(){
		return $this->_keys;
	}

	function statement(){
		$statment_ = pbdb_ss($this->_table_name);

		foreach($this->_fields as $column_name_ => $field_data_){
			$statment_->add_field($column_name_);
		}

		return $statment_;
	}

	private $_legacy_field_filters = array();

	function add_legacy_field_filter(){
		$this->_legacy_field_filters[] = func_get_args();
	}

	function insert($data_){
		global $pbdb;

		$insert_values_ = array();
		$insert_types_ = array();

		foreach($data_ as $column_name_ => $column_value_){
			if(!isset($this->_fields[$column_name_])) continue;

			$field_data_ = $this->_fields[$column_name_];

			if(isset($field_data_['ai']) && !!$field_data_['ai']) continue;

			$type_ = isset($field_data_['type']) ? $field_data_['type'] : PBDB_DO::TYPE_STRING;

			$insert_values_[$column_name_] = $column_value_;
			$insert_types_[] = PBDB_DO::convert_to_pbdb_type($type_);
		}

		$legacy_fields_ = array();
		foreach($this->_legacy_field_filters as $args_){
			$legacy_fields_ = array_merge($legacy_fields_, call_user_func_array("pb_hook_apply_filters", $args_));
		}
		if(count($legacy_fields_) > 0){
			foreach($data_ as $column_name_ => $column_value_){
				if(!isset($legacy_fields_[$column_name_])) continue;

				$insert_values_[$column_name_] = $column_value_;
				$insert_types_[] = $legacy_fields_[$column_name_];
			}
		}

		$inserted_id_ = $pbdb->insert($this->_table_name, $insert_values_, $insert_types_);

		pb_hook_do_action('pbdb_do_{$this->_table_name}_inserted', $inserted_id_);

		return $inserted_id_;
	}

	function update(){
		global $pbdb;
		$arg_count_ = func_num_args();

		$keys_ = array();
		$update_data_ = null;

		for($index_=0;$index_<$arg_count_ - 1;++$index_){
			$keys_[] = func_get_arg($index_);
		}
		if(count($this->_keys) != count($keys_)){
			return new PBError(403, "잘못된 요청", "키갯수가 일치하지 않습니다.");
		}

		$update_data_ = func_get_arg(($arg_count_ - 1));

		$update_values_ = array();
		$update_types_ = array();

		$key_values_ = array();
		$key_types_ = array();

		foreach($update_data_ as $column_name_ => $column_value_){
			if(!isset($this->_fields[$column_name_]) || in_array($column_name_, $this->_keys) !== false) continue;

			$field_data_ = $this->_fields[$column_name_];

			$type_ = isset($field_data_['type']) ? $field_data_['type'] : PBDB_DO::TYPE_STRING;

			$update_values_[$column_name_] = $column_value_;
			$update_types_[] = PBDB_DO::convert_to_pbdb_type($type_);
		}

		$legacy_fields_ = array();
		foreach($this->_legacy_field_filters as $args_){
			$legacy_fields_ = array_merge($legacy_fields_, call_user_func_array("pb_hook_apply_filters", $args_));
		}
		if(count($legacy_fields_) > 0){
			foreach($update_data_ as $column_name_ => $column_value_){
				if(!isset($legacy_fields_[$column_name_])) continue;

				$update_values_[$column_name_] = $column_value_;
				$update_types_[] = $legacy_fields_[$column_name_];
			}
		}

		foreach($this->_keys as $index_ => $column_name_){
			$field_data_ = $this->_fields[$column_name_];

			$type_ = isset($field_data_['type']) ? $field_data_['type'] : PBDB_DO::TYPE_STRING;

			$key_values_[$column_name_] = $keys_[$index_];
			$key_types_[] = PBDB_DO::convert_to_pbdb_type($type_);
		}

		$result_ = $pbdb->update($this->_table_name, $update_values_, $key_values_, $update_types_, $key_types_);
		
		$hook_params_ = array_merge(array("pbdb_do_{$this->_table_name}_updated"), $keys_);
		call_user_func_array('pb_hook_do_action', $hook_params_);

		return $result_;

	}
	function delete(){
		global $pbdb;
		$arg_count_ = func_num_args();

		$keys_ = array();

		for($index_=0;$index_<$arg_count_;++$index_){
			$keys_[] = func_get_arg($index_);
		}

		if(count($this->_keys) != count($keys_)){
			return new PBError(403, "잘못된 요청", "키갯수가 일치하지 않습니다.");
		}

		$key_values_ = array();
		$key_types_ = array();

		foreach($this->_keys as $index_ => $column_name_){
			$field_data_ = $this->_fields[$column_name_];

			$type_ = isset($field_data_['type']) ? $field_data_['type'] : PBDB_DO::TYPE_STRING;

			$key_values_[$column_name_] = $keys_[$index_];
			$key_types_[] = PBDB_DO::convert_to_pbdb_type($type_);
		}

		$pbdb->delete($this->_table_name, $key_values_, $key_types_);

		$hook_params_ = array_merge(array("pbdb_do_{$this->_table_name}_delete"), $keys_);
		call_user_func_array('pb_hook_do_action', $hook_params_);
	}
}

function pbdb_data_object($table_, $fields_, $comment_ = null, $engine_ = "InnoDB"){
	return new PBDB_DO($table_, $fields_, $comment_, $engine_);
}

?>
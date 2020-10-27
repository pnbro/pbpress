<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

abstract class PBDB_SS{
	const COND_COMPARE = 1;
	const COND_IN = 3;
	const COND_NOT_IN = 4;
	const COND_ISNOTNULL = 5;
	const COND_ISNULL = 7;
	const COND_LIKE = 8;
	const COND_CUSTOM = 9;
}

//shortcut
abstract class SS_COND{
	const COMPARE = PBDB_SS::COND_COMPARE;
	const IN = PBDB_SS::COND_IN;
	const NOT_IN = PBDB_SS::COND_NOT_IN;
	const ISNOTNULL = PBDB_SS::COND_ISNOTNULL;
	const ISNULL = PBDB_SS::COND_ISNULL;
	const LIKE = PBDB_SS::COND_LIKE;
	const CUSTOM = PBDB_SS::COND_CUSTOM;
}

class PBDB_select_statement_conditions extends ArrayObject{
	
	function add_compare($a_, $b_, $compare_ = "=", $b_type_ = null){
		$this[] = array(
			'type' => PBDB_SS::COND_COMPARE,
			'a' => $a_,
			'b' => $b_,
			'b_type' => $b_type_,
			'compare' => $compare_,
		);
	}
	function add_is_null($a_){
		$this[] = array(
			'type' => PBDB_SS::COND_ISNULL,
			'a' => $a_,
		);
	}
	function add_is_not_null($a_){
		$this[] = array(
			'type' => PBDB_SS::COND_ISNOTNULL,
			'a' => $a_,
		);
	}

	function add_in($a_, $array_, $array_types_ = null){
		if(gettype($array_) !== "array") $array_ = array($array_);

		$this[] = array(
			'type' => PBDB_SS::COND_IN,
			'a' => $a_,
			'b' => $array_,
			'b_types' => $array_types_,
		);
	}
	function add_not_in($a_, $array_, $array_types_ = null){
		if(gettype($array_) !== "array") $array_ = array($array_);

		$this[] = array(
			'type' => PBDB_SS::COND_NOT_IN,
			'a' => $a_,
			'b' => $array_,
			'b_types' => $array_types_,
		);
	}

	function add_like($a_ = array(), $keyword_, $full_search_ = false){
		if(gettype($a_) !== "array") $a_ = array($a_);

		$this[] = array(
			'type' => PBDB_SS::COND_LIKE,
			'a' => $a_,
			'keyword' => $keyword_,
			'full' => $full_search_,
		);
	}
	function add_custom($text_, $values_ = null, $types_ = array()){
		$this[] = array(
			'type' => PBDB_SS::COND_CUSTOM,
			'custom' => $text_,
			'values' => $values_,
			'types' => $types_,
		);
	}
	function add(){
		$condition_ = func_get_args();
		$type_ = array_splice($condition_,0, 1);
		$type_ = isset($type_[0]) ? $type_[0] : null;

		switch($type_){
			case PBDB_SS::COND_COMPARE :
				$a_ = $condition_[0];
				$b_ = $condition_[1];
				$compare_ = isset($condition_[2]) ? $condition_[2] : "=";
				$type_ = isset($condition_[3]) ? $condition_[3] : null;

				call_user_func_array(array($this, 'add_compare'), array($a_, $b_, $compare_, $type_));
			break;
			case PBDB_SS::COND_IN :
				$a_ = $condition_[0];
				$array_ = isset($condition_[1]) ? $condition_[1] : array();
				$array_types_ = isset($condition_[2]) ? $condition_[2] : null;

				call_user_func_array(array($this, 'add_in'), array($a_, $array_, $array_types_));
			break;
			case PBDB_SS::COND_NOT_IN :
				$a_ = $condition_[0];
				$array_ = isset($condition_[1]) ? $condition_[1] : array();
				$array_types_ = isset($condition_[2]) ? $condition_[2] : null;

				call_user_func_array(array($this, 'add_not_in'), array($a_, $array_, $array_types_));
			break;
			case PBDB_SS::COND_LIKE :
				$a_ = $condition_[0];
				$b_ = isset($condition_[1]) ? $condition_[1] : "";
				$full_search_ = isset($condition_[2]) ? $condition_[2] : false;

				call_user_func_array(array($this, 'add_like'), array($a_, $b_, $full_search_));
			break;
			case PBDB_SS::COND_ISNOTNULL :
				$a_ = $condition_[0];
				
				call_user_func_array(array($this, 'add_is_not_null'), array($a_));
			break;
			case PBDB_SS::COND_ISNULL :
				$a_ = $condition_[0];
				
				call_user_func_array(array($this, 'add_is_null'), array($a_));
			break;
			case PBDB_SS::COND_CUSTOM :
				$text_ = $condition_[0];
				$values_ = isset($condition_[1]) ? $condition_[1] : null;
				$types_ = isset($condition_[2]) ? $condition_[2] : array();
				
				call_user_func_array(array($this, 'add_custom'), array($text_, $values_, $types_));
			break;
		}
	}

	public function build(){
		$query_ = array();

		$param_values_ = array();
		$param_types_ = array();

		foreach($this as $param_index_ => $data_){
			$cond_type_ = isset($data_['type']) ? $data_['type'] : null;
			if(!strlen($cond_type_)) return $query_;

			switch($cond_type_){
				case PBDB_SS::COND_COMPARE :

					$a_ = $data_['a'];
					$b_ = $data_['b'];
					$b_type_ = $data_['b_type'];
					$compare_ = $data_['compare'];

					if(strlen($b_type_)){
						$param_values_[] = pb_database_escape_string($b_);
						$param_types_[] = $b_type_;

						$query_[] = "{$a_} {$compare_} ".PBDB_PARAM_MAP_STR." \n\r";

					}else{
						$query_[] = "{$a_} {$compare_} {$b_} \n\r";
					}

					

				break;

				case PBDB_SS::COND_IN :

					$a_ = $data_['a'];
					$b_ = $data_['b'];
						
					if(count($b_) > 0){
						$b_types_ = $data_['b_types'];
						$in_str_array_ = array();

						foreach($b_ as $bi_ => $bv_){
							$param_values_[] = pb_database_escape_string($bv_);
							$param_types_[] = isset($b_types_[$bi_]) ? $b_types_[$bi_] : PBDB::TYPE_STRING;
							$in_str_array_[] = PBDB_PARAM_MAP_STR;
						}

						$query_[] = "{$a_} IN (".implode(",", $in_str_array_).") \n\r";
					}

				break;

				case PBDB_SS::COND_NOT_IN :

					$a_ = $data_['a'];
					$b_ = $data_['b'];
						
					if(count($b_) > 0){
						$b_types_ = $data_['b_types'];
						$in_str_array_ = array();

						foreach($b_ as $bi_ => $bv_){
							$param_values_[] = pb_database_escape_string($bv_);
							$param_types_[] = isset($b_types_[$bi_]) ? $b_types_[$bi_] : PBDB::TYPE_STRING;
							$in_str_array_[] = PBDB_PARAM_MAP_STR;
						}

						$query_[] = "{$a_} NOT IN (".implode(",", $in_str_array_).") \n\r";
					}

				break;

				case PBDB_SS::COND_ISNOTNULL :
					$a_ = $data_['a'];

					$query_[] = "{$a_} IS NOT NULL \n\r";

				break;

				case PBDB_SS::COND_ISNULL :
					$a_ = $data_['a'];

					$query_[] = "{$a_} IS NULL \n\r";

				break;

				case PBDB_SS::COND_LIKE :

					$query_[] = pb_query_keyword_search($data_['a'], $data_['keyword'], $data_['full']);

				break;


				case PBDB_SS::COND_CUSTOM :
					$values_ = $data_['values'];
					$types_ = $data_['types'];

					$query_[] = $data_['custom']." \n\r";

					if(isset($values_)){
						foreach($values_ as $vi_ => $value_){
							$param_values_[] = pb_database_escape_string($value_);
							$param_types_[] = isset($types_[$vi_]) ? $types_[$vi_] : PBDB::TYPE_STRING;
						}
					}
						
				break;

				default : 
					$query_[] = pb_hook_apply_filters('pb_database_select_statement_build_condition', $query_, $data_)." \n\r";

				break;
			}

		}

		$results_ = array(
			'query' => implode(" AND ", $query_),
			'values' => $param_values_,
			'types' => $param_types_,
		);

		return $results_;
	}

	function add_from_data($data_ = array(), $conditions_ = array()){
		foreach($conditions_ as $key_ => $condition_){
			if(!isset($data_[$key_])) continue;
			if(!isset($condition_[0])) continue;

			$type_ = array_splice($condition_,0, 1);
			$type_ = isset($type_[0]) ? $type_[0] : null;

			switch($type_){
				case PBDB_SS::COND_COMPARE :
					$a_ = $condition_[0];
					$compare_ = isset($condition_[1]) ? $condition_[1] : "=";
					$type_ = isset($condition_[2]) ? $condition_[2] : PBDB_SS::TYPE_STRING;

					call_user_func_array(array($this, 'add_compare'), array($a_, $data_[$key_], $compare_, $type_));
				break;
				case PBDB_SS::COND_IN :
					$a_ = $condition_[0];
					$array_types_ = isset($condition_[1]) ? $condition_[1] : null;

					call_user_func_array(array($this, 'add_in'), array($a_, $data_[$key_], $array_types_));
				break;
				case PBDB_SS::COND_NOT_IN :
					$a_ = $condition_[0];
					$array_types_ = isset($condition_[1]) ? $condition_[1] : null;

					call_user_func_array(array($this, 'add_not_in'), array($a_, $data_[$key_], $array_types_));
				break;
				case PBDB_SS::COND_LIKE :
					$a_ = $condition_[0];
					$full_search_ = isset($condition_[2]) ? $condition_[1] : false;

					call_user_func_array(array($this, 'add_like'), array($a_, $data_[$key_], $full_search_));
				break;
			}
		}
	}
}

function pbdb_ss_conditions(){
	return new PBDB_select_statement_conditions();
}

class PBDB_select_statement{

	private $_from_table;
	private $_from_table_alias;

	private $_field_list = array();
	private $_join_list = array();
	private $_cond_list = null;

	static function is_statement($obj_){
		return @get_class($obj_) === "PBDB_select_statement";
	}

	function __construct($from_table_, $from_table_alias_ = null){
		$this->_from_table = $from_table_;
		$this->_from_table_alias = $from_table_alias_;
		$this->_cond_list = new PBDB_select_statement_conditions();
	}

	function from_table(){
		return $this->_from_table;
	}
	function from_table_alias(){
		return $this->_from_table_alias;
	}
	function fields(){
		return $this->_field_list;
	}
	function joins(){
		return $this->_join_list;
	}
	function conditions(){
		return $this->_cond_list;
	}

	function add_field(){
		$fields_count_ = func_num_args();

		for($fi_=0;$fi_<$fields_count_;++$fi_){
			$this->_field_list[] = func_get_arg($fi_);
		}
	}

	function &add_join($join_type_, $obj_, $alias_ = null, $on_ = null, $fields_ = null, $column_prefix_ = ""){
		if(!isset($on_)){
			$on_ = new PBDB_select_statement_conditions();
		}

		$this->_join_list[] = array(
			'type' => $join_type_,
			'obj' => $obj_,
			'alias' => $alias_,
			'on' => $on_,
			'fields' => $fields_,
			'prefix' => $column_prefix_,
		);

		return $on_;
	}
	function &add_join_statement($join_type_, $statement_, $alias_ = null, $on_ = null, $fields_ = null, $column_prefix_ = ""){
		return $this->add_join($join_type_, $statement_, $alias_, $on_, $fields_, $column_prefix_);
	}

	function add_compare_condition($a_, $b_, $compare_ = "=", $b_type_ = PBDB::TYPE_STRING){
		$this->_cond_list->add_compare($a_, $b_, $compare_, $b_type_);
	}
	function add_in_condition($a_, $array_, $array_types_ = null){
		$this->_cond_list->add_in($a_, $array_, $array_types_);
	}
	function add_not_in_condition($a_, $array_, $array_types_ = null){
		$this->_cond_list->add_not_in($a_, $array_, $array_types_);
	}
	function add_custom_condition($text_, $value_ = null, $types_ = array()){
		$this->_cond_list->add_custom($text_, $value_, $types_);
	}
	function add_is_not_null_condition($a_){
		$this->_cond_list->add_is_not_null($a_);
	}
	function add_is_null_condition($a_){
		$this->_cond_list->add_is_null($a_);
	}
	function add_like_condition($a_, $keyword_, $full_search_ = false){
		$this->_cond_list->add_like($a_, $keyword_, $full_search_);
	}
	function add_conditions_from_data($data_ = array(), $conditions_ = array()){
		$this->_cond_list->add_from_data($data_, $conditions_);
	}
	
	private $_legacy_field_filters = array();
	private $_legacy_join_fileds = array();
	private $_legacy_where_fileds = array();

	function add_legacy_field_filter(){
		$this->_legacy_field_filters[] = func_get_args();
	}
	function add_legacy_join_filter(){
		$this->_legacy_join_fileds[] = func_get_args();
	}
	function add_legacy_where_filter(){
		$this->_legacy_where_fileds[] = func_get_args();
	}

	private $_column_name_pattern = "/^([A-Za-z\_0-9])+$/";

	function build($order_by_ = null, $limit_ = null, $group_by_key_ = "", $group_by_fields_ = array()){
		$from_table_ = $this->_from_table;
		$from_table_alias_ = isset($this->_from_table_alias) ? $this->_from_table_alias : $from_table_;

		$has_group_by_ = strlen($group_by_key_);
		
		$query_ = "SELECT \n\r";

		$fields_array_ = array();
		$param_values_ = array();
		$param_types_ = array();

		$filed_list_ = $has_group_by_ ? $group_by_fields_ : $this->_field_list;

		foreach($filed_list_ as $column_name_){
			$func_check_ = explode(" ", $column_name_);
			$func_check_ = preg_match($this->_column_name_pattern, $func_check_[0]);
			if(!$func_check_){
				$fields_array_[] = "{$column_name_}";

			}else{
				$fields_array_[] = "{$from_table_alias_}.{$column_name_}";	
			}
			
		}		

		foreach($this->_join_list as $join_data_){

			$join_obj_ = $join_data_['obj'];

			if(pb_database_is_statement($join_obj_)){
				$join_table_statement_ = $join_obj_;
				$join_table_name_ = $join_table_statement_->from_table();
				$join_table_alias_ = isset($join_data_['alias']) ? $join_data_['alias'] : $join_table_statement_->from_table_alias();
				$join_table_alias_ = strlen($join_table_alias_) ? $join_table_alias_ : $join_table_name_;
				$join_table_prefix_ = $join_data_['prefix'];
				$join_table_fields_ = $join_data_['fields'];

				$join_real_fields_ = array();
				$join_subquery_fields_ = array();

				foreach($join_table_fields_ as $column_name_){
					$column_name_array_ = explode(" ", $column_name_);
					$real_column_name_ = $column_name_;

					if(count($column_name_array_) > 1){
						$real_column_name_ = $column_name_array_[0];

						if(!preg_match($this->_column_name_pattern, $real_column_name_)){
							$join_subquery_fields_[] = $column_name_;
							continue;
						}

					}else if(!preg_match($this->_column_name_pattern, $real_column_name_)){
						$join_subquery_fields_[] = $column_name_;
						continue;
					}

					$join_real_fields_[] = $column_name_;
				}

				$join_table_statement_fields_ = $join_table_statement_->fields();
				$join_fields_keys_ = array_keys($join_data_['fields']);


				if(!$has_group_by_){
					foreach($join_real_fields_ as $column_index_ => $column_name_){

						$column_name_array_ = explode(" ", $column_name_);
						$column_alias_ = end($column_name_array_);

						if(count($column_name_array_) > 1){
							if(!preg_match($this->_column_name_pattern, $column_alias_)){
								$column_alias_ = null;
							}
							
							if(strlen($column_alias_)){
								array_splice($column_name_array_, count($column_name_array_) - 1);	
							}
						}
							
						
						$column_oname_ = implode(' ', $column_name_array_);
						$column_alias_ = strlen($column_alias_) ? $column_alias_ : $column_oname_;
						$column_alias_ = $join_table_prefix_.$column_alias_;

						if(!preg_match($this->_column_name_pattern, $column_alias_)){
							$column_alias_ = "";
						}

						$fields_array_[] = "{$join_table_alias_}.{$column_oname_} {$column_alias_}";
					}
					foreach($join_subquery_fields_ as $column_index_ => $t_column_name_){
						$fields_array_[] = $t_column_name_;	
					}
				}

					

			}
		}
		$query_ .= implode(",\n\r", $fields_array_) ." \n\r"; 

		if(!$has_group_by_){
			foreach($this->_legacy_field_filters as $filter_){
				$query_ .= call_user_func_array("pb_hook_apply_filters", $filter_)." \n\r";
			}
		}

		$query_ .= " FROM {$from_table_} {$from_table_alias_} \n\r";

		foreach($this->_join_list as $join_data_){
			$join_obj_ = $join_data_['obj'];

			if(pb_database_is_statement($join_obj_)){
				$join_table_statement_ = $join_obj_;
				$join_table_alias_ = isset($join_data_['alias']) ? $join_data_['alias'] : $join_table_statement_->from_table_alias();
				$join_table_alias_ = strlen($join_table_alias_) ? $join_table_alias_ : $join_table_name_;
				$join_table_name_ = $join_table_statement_->from_table();
				$query_ .= " {$join_data_['type']} {$join_table_name_} {$join_table_alias_} \n\r";
			}else{
				$join_table_ = $join_obj_;
				$join_table_alias_ = isset($join_data_['alias']) ? $join_data_['alias'] : $join_table_;
				$query_ .= " {$join_data_['type']} {$join_table_} {$join_table_alias_} \n\r";
			}
			
			$query_ .= " ON 1 \n\r";

			$join_on_ = $join_data_['on'];
			$join_cond_ = null;

			if(gettype($join_on_) === "array"){
				$join_cond_ = pbdb_ss_conditions();

				foreach($join_on_ as $join_args_){
					call_user_func_array(array($join_cond_, "add"), $join_args_);
				}
					
			}else{
				$join_cond_ = $join_on_;
			}

			$join_cond_ = $join_cond_->build();
			foreach($join_cond_['values'] as $jv_index_ => $jv_){
				$param_values_[] = $jv_;
				$param_types_[] = $join_cond_['types'][$jv_index_];
			}

			$query_ .= " AND ".$join_cond_['query']." \n\r";
		}

		foreach($this->_legacy_join_fileds as $filter_){
			$query_ .= call_user_func_array("pb_hook_apply_filters", $filter_)." \n\r";
		}

		$query_ .= ' WHERE 1 ';

		if(count($this->_cond_list) > 0){
			$where_cond_ = $this->_cond_list->build();
			foreach($where_cond_['values'] as $wv_index_ => $wv_){
				$param_values_[] = $wv_;
				$param_types_[] = $where_cond_['types'][$wv_index_];
			}

			$query_ .= " AND ".$where_cond_['query']." \n\r";	
		}

		foreach($this->_legacy_where_fileds as $filter_){
			$query_ .= call_user_func_array("pb_hook_apply_filters", $filter_)." \n\r";
		}

		if($has_group_by_){
			$query_ .= ' GROUP BY '.$group_by_key_." \n\r";
		}

		if(strlen($order_by_)){

			if(stripos($order_by_, "order by") !== FALSE){
				$query_ .= $order_by_." \n\r";				
			}else{
				$query_ .= ' ORDER BY '.$order_by_." \n\r";	
			}
		}

		if(isset($limit_)){
			$query_ .= " LIMIT {$limit_[0]}, {$limit_[1]}";
		}

		return array(
			'query' => $query_,
			'values' => $param_values_,
			'types' => $param_types_,
		);
	}

	function count(){
		$result_ = $this->build();
		global $pbdb;
		return $pbdb->get_var("SELECT COUNT(*) FROM (".$result_['query'].") TMP", $result_['values'], $result_['types']);
	}
	function select($order_by_ = null, $limit_ = null){
		$result_ = $this->build($order_by_, $limit_);
		global $pbdb;
		return $pbdb->select($result_['query'], $result_['values'], $result_['types']);
	}
	function serialize_column($column_name_, $order_by_ = null, $limit_ = null){
		$temp_ = $this->select($order_by_, $limit_);
		$results_ = array();

		foreach($temp_ as $row_data_){
			$results_[] = $row_data_[$column_name_];
		}
		return $results_;
	}
	function get_var($order_by_ = null, $limit_ = null){
		$result_ = $this->build($order_by_, $limit_);
		global $pbdb;
		return $pbdb->get_var($result_['query'], $result_['values'], $result_['types']);
	}
	function get_first_row($order_by_ = null, $limit_ = null){
		$result_ = $this->build($order_by_, $limit_);
		global $pbdb;
		return $pbdb->get_first_row($result_['query'], $result_['values'], $result_['types']);
	}

	function build_group_by($key_, $fields_, $order_by_ = null, $limit_ = null){
		return $this->build($order_by_, $limit_, $key_, $fields_);
	}
	function group_by($key_, $fields_, $order_by_ = null, $limit_ = null){
		$result_ = $this->build_group_by($key_, $fields_, $order_by_, $limit_);
		global $pbdb;
		return $pbdb->select($result_['query'], $result_['values'], $result_['types']);
	}
}

function pb_database_select_statement($table_, $alias_ = null){
	return new PBDB_select_statement($table_, $alias_);
}
function pbdb_ss($table_, $alias_ = null){
	return pb_database_select_statement($table_, $alias_);
}
function pb_database_is_statement($statement_){
	return PBDB_select_statement::is_statement($statement_);
}
	
?>
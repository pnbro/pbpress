<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBDB_SS{
	const COND_COMPARE = 1;
	const COND_IN = 3;
	const COND_CUSTOM = 5;
}

class PBDB_select_statement_conditions extends ArrayObject{
	private $_prepared_values = array();
	private $_prepared_types = array();

	function add_compare($a_, $b_, $compare_ = "=", $b_type_ = null){
		$this[] = array(
			'type' => PBDB_SS::COND_COMPARE,
			'a' => $a_,
			'b' => $b_,
			'b_type' => $b_type_,
			'compare' => $compare_,
		);
	}
	function add_in($a_, $array_, $array_types_ = null){
		$this[] = array(
			'type' => PBDB_SS::COND_IN,
			'a' => $a_,
			'b' => $array_,
			'b_types' => $array_types_,
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

	public function build(){
		$query_ = array();

		foreach($this as $data_){
			$cond_type_ = isset($data_['type']) ? $data_['type'] : null;
			if(!strlen($cond_type_)) return $query_;

			switch($cond_type_){
				case PBDB_SS::COND_COMPARE :

					$a_ = $data_['a'];
					$b_ = $data_['b'];
					$b_type_ = $data_['b_type'];
					$compare_ = $data_['compare'];

					if(strlen($b_type_)){
						$this->_prepared_values[] = pb_database_escape_string($b_);
						$this->_prepared_types[] = $b_type_;

						$query_[] = "{$a_} {$compare_} ? \n\r";

					}else{
						$query_[] = "{$a_} {$compare_} {$b_} \n\r";
					}

					

				break;

				case PBDB_SS::COND_IN :

					$a_ = $data_['a'];
					$b_ = $data_['b'];

					if(gettype($b_) !== "array") $b_ = array($b_);
						
					if(count($b_) > 0){
						$b_types_ = $data_['b_types'];
						$in_str_array_ = array();

						foreach($b_ as $bi_ => $bv_){
							$this->_prepared_values[] = pb_database_escape_string($bv_);
							$this->_prepared_types[] = isset($b_types_[$bi_]) ? $b_types_[$bi_] : PBDB::TYPE_STRING;
							$in_str_array_[] = "?";
						}

						$query_[] = "{$a_} IN (".implode(",", $in_str_array_).") \n\r";
					}

				break;

				case PBDB_SS::COND_CUSTOM :
					$values_ = $data_['values'];
					$types_ = $data_['types'];

					$query_[] = $data_['custom']." \n\r";

					if(isset($values_)){
						foreach($values_ as $vi_ => $value_){
							$this->_prepared_values[] = pb_database_escape_string($value_);
							$this->_prepared_types[] = isset($types_[$vi_]) ? $types_[$vi_] : PBDB::TYPE_STRING;
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
			'values' => $this->_prepared_values,
			'types' => $this->_prepared_types,
		);

		return $results_;
	}
}

class PBDB_select_statement{

	private $_from_table;
	private $_from_table_alias;

	private $_prepared_params = array();

	private $_field_list = array();
	private $_join_list = array();
	private $_cond_list = null;

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

	function &add_join($join_type_, $table_, $alias_ = null){
		$join_cond_ = new PBDB_select_statement_conditions();

		$this->_join_list[] = array(
			'type' => $join_type_,
			'table' => $table_,
			'alias' => $alias_,
			'on' => $join_cond_,
		);

		return $join_cond_;
	}
	function &add_join_statement($join_type_, $statement_, $alias_ = null, $column_prefix_ = "", $fields_ = null){
		$join_cond_ = new PBDB_select_statement_conditions();

		$this->_join_list[] = array(
			'type' => $join_type_,
			'statement' => $statement_,
			'prefix' => $alias_,
			'on' => $join_cond_,
			'fields' => $fields_,
		);	

		return $join_cond_;
	}

	function add_compare_condition($a_, $b_, $compare_ = "=", $b_type_ = PBDB::TYPE_STRING){
		$this->_cond_list->add_compare($a_, $b_, $compare_, $b_type_);
	}
	function add_in_condition($a_, $array_, $array_types_ = null){
		$this->_cond_list->add_in($a_, $array_, $array_types_);
	}
	function add_custom_condition($text_, $value_ = null, $types_ = array()){
		$this->_cond_list->add_custom($text_, $value_, $types_);
	}

	function add_param($value_, $type_ = PBDB::TYPE_STRING){
		$this->_prepared_params[] = array(
			'type' => $type_,
			'value' => $value_,
		);
	}

	function build($order_by_ = null, $limit_ = null){

		$from_table_ = $this->_from_table;
		$from_table_alias_ = isset($this->_from_table_alias) ? $this->_from_table_alias : $from_table_;
		
		$query_ = "SELECT \n\r";

		$fields_array_ = array();

		foreach($this->_field_list as $column_name_){
			$fields_array_[] = "{$from_table_alias_}.{$column_name_}";
		}

		foreach($this->_join_list as $join_data_){
			if(isset($join_data_['statement'])){
				$join_table_statement_ = $join_data_['statement'];
				$join_table_name_ = $join_table_statement_->from_table();
				$join_table_alias_ = isset($join_data_['alias']) ? $join_data_['alias'] : $join_table_statement_->from_table_alias();
				$join_table_alias_ = strlen($join_table_alias_) ? $join_table_alias_ : $join_table_name_;
				$join_table_prefix_ = $join_data_['prefix'];
				$join_table_fields_ = $join_data_['fields'];

				foreach($join_table_statement_->fields() as $column_name_){
					if(isset($join_table_fields_) && in_array($column_name_, $join_table_fields_) === false) continue;

					$column_name_ = trim($column_name_);
					$column_name_array_ = explode(" ", $column_name_);

					$column_oname_ = $column_name_array_[0];
					$column_alias_ = isset($column_name_array_[1]) ? $column_name_array_[1] : $column_oname_;
					$column_alias_ = $join_table_prefix_.$column_alias_;

					$fields_array_[] = "{$join_table_alias_}.{$column_oname_} {$column_alias_}";
				}
			}
		}
		$query_ .= implode(",\n\r", $fields_array_) ." \n\r"; 

		$query_ .= " FROM {$from_table_} {$from_table_alias_} \n\r";


		foreach($this->_join_list as $join_data_){
			if(isset($join_data_['statement'])){
				$join_table_statement_ = $join_data_['statement'];
				$join_table_alias_ = isset($join_data_['alias']) ? $join_data_['alias'] : $join_table_statement_->from_table_alias();
				$join_table_alias_ = strlen($join_table_alias_) ? $join_table_alias_ : $join_table_name_;
				$join_table_name_ = $join_table_statement_->from_table();
				$query_ .= " {$join_data_['type']} {$join_table_name_} {$join_table_alias_} \n\r";

			}else{
				$join_table_ = $join_data_['table'];
				$join_table_alias_ = isset($join_data_['alias']) ? $join_data_['alias'] : $join_table_;
				$query_ .= " {$join_data_['type']} {$join_table_} {$join_table_alias_} \n\r";
			}
			
			$query_ .= " ON 1 \n\r";

			$join_cond_ = $join_data_['on']->build();
			foreach($join_cond_['values'] as $jv_index_ => $jv_){
				$this->add_param($jv_, $join_cond_['types'][$jv_index_]);
			}

			$query_ .= " AND ".$join_cond_['query']." \n\r";
		}

		if(count($this->_cond_list) > 0){
			$where_cond_ = $this->_cond_list->build();
			foreach($where_cond_['values'] as $wv_index_ => $wv_){
				$this->add_param($wv_, $where_cond_['types'][$wv_index_]);
			}

			$query_ .= " WHERE ".$where_cond_['query']." \n\r";	
		}

			

		if(strlen($order_by_)){
			$query_ .= ' ORDER BY '.$order_by_." \n\r";
		}

		if(isset($limit_)){
			$query_ .= " LIMIT {$limit_[0]}, {$limit_[1]}";
		}

		$values_ = array();
		$types_ = array();

		foreach($this->_prepared_params as $param_data_){
			$types_[]  = $param_data_['type'];
			$values_[]  = $param_data_['value'];
		}

		return array(
			'query' => $query_,
			'values' => $values_,
			'types' => $types_,
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
}

function pb_database_select_statement($table_, $alias_ = null){
	return new PBDB_select_statement($table_, $alias_);
}
function pbdb_ss($table_, $alias_ = null){
	return pb_database_select_statement($table_, $alias_);
}
	
?>
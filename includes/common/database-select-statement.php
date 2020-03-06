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


	function add_compare($a_, $b_, $compare_ = "="){
		$this[] = array(
			'type' => PBDB_SS::COND_COMPARE,
			'a' => $a_,
			'b' => $b_,
			'compare' => $compare_,
		);
	}
	function add_in($a_, $array_){
		$this[] = array(
			'type' => PBDB_SS::COND_IN,
			'a' => $a_,
			'b' => $array_,
		);
	}
	function add_custom($text_){
		$this[] = array(
			'type' => PBDB_SS::COND_CUSTOM,
			'custom' => $text_,
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
					$compare_ = $data_['compare'];
					$query_[] = "{$a_} {$compare_} {$b_} \n\r";

				break;

				case PBDB_SS::COND_IN :

					$a_ = $data_['a'];
					$b_ = $data_['b'];
					$query_[] = pb_query_in_fields($b_, $a_)." \n\r";
				break;

				case PBDB_SS::COND_CUSTOM :
					$query_[] = $data_['custom']." \n\r";
				break;

				default : 
					$query_[] = pb_hook_apply_filters('pb_database_select_statement_build_condition', $query_, $data_)." \n\r";

				break;
			}

		}


		return implode(" AND ", $query_);
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

	

	function add_field($text_){
		$this->_field_list[] = $text_;
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

	function add_compare_condition($a_, $b_, $compare_ = "="){
		$this->_cond_list->add_compare($a_, $b_, $compare_);
	}
	function add_in_condition($a_, $array_){
		$this->_cond_list->add_in($a_, $array_);
	}
	function add_custom_condition($text_){
		$this->_cond_list->add_custom($text_);
	}

	function build($order_by_ = null, $limit_ = null){

		$from_table_ = $this->_from_table;
		$from_table_alias_ = isset($this->_from_table_alias) ? $this->_from_table_alias : $from_table_;
		
		$query_ = "SELECT \n\r";

		$fields_array_ = array();

		foreach($this->_field_list as $field_text_){
			$query_ .= $field_text_ ." \n\r"; 
		}


		$query_ .= " FROM {$from_table_} {$from_table_alias_} \n\r";


		foreach($this->_join_list as $join_data_){
			$join_table_ = $join_data_['table'];
			$join_table_alias_ = isset($join_data_['alias']) ? $join_data_['alias'] : $join_table_;
			$query_ .= " {$join_data_['type']} {$join_table_} {$join_table_alias_} \n\r";
			$query_ .= " ON 1 \n\r";
			$query_ .= " AND ".$join_data_['on']->build()." \n\r";
		}

		$query_ .= " WHERE 1 AND ".$this->_cond_list->build()." \n\r";	

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

	function add_param($value_, $type_ = PBDB::TYPE_STRING){
		$this->_prepared_params[] = array(
			'type' => $type_,
			'value' => $value_,
		);
	}
}

function pb_database_select_statement($table_, $alias_ = null){
	return new PBDB_select_statement($table_, $alias_);
}
	
?>
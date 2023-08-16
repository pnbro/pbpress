<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

abstract class PBConstClass{
	static public function constants(){
		$refl_class_ = new ReflectionClass(get_called_class());
		return $refl_class_->getConstants();
	}
	static public function codes(){
		$constants_ = static::constants();
		$results_ = array();

		foreach($constants_ as $key_ => $value_){
			if(strrpos($key_, "_NAME") === false){
				$results_[$key_] = $value_;
			}
		}

		return $results_;
	}
	static public function names(){
		$constants_ = static::constants();
		$results_ = array();

		foreach($constants_ as $key_ => $value_){
			if(strrpos($key_, "_NAME") !== false){
				$results_[$constants_[str_replace("_NAME", '', $key_)]] = $value_;
			}
		}
		return $results_;
	}

	static public function name($code_, $default_ = ""){
		$constant_names_ = static::names();
		if(!isset($constant_names_[$code_])) return $default_;
		return $constant_names_[$code_];
	}

	static public function make_options($value_ = "", $excludes_ = array()){
		$each_data_ = static::names();

		$option_el_ = "";
		foreach ($each_data_ as $key_ => $value_data_) {
			if(in_array($key_, $excludes_)) continue;

			$option_el_ .= '<option value="' . $key_ . '" ' . pb_selected($value_, $key_) . ' >' . $value_data_ . '</option>';
		}
		return $option_el_;
	}

	static public function subquery($column_, $alias_, $excludes_ = array()){
		$const_names_ = static::names();
	
		$query_ = " (CASE \r\n";

		foreach($const_names_ as $code_ => $name_){
			if(in_array($code_, $excludes_)) continue;

			$query_ .= " WHEN {$column_} = '{$code_}' THEN '".addslashes($name_)."' \r\n";
		}
		$query_ .= " WHEN FALSE THEN NULL \r\n";
		$query_ .= " ELSE NULL \r\n";
		$query_ .= " END) {$alias_} ";
		return $query_;
	}
}

?>
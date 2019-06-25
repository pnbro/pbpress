<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_query_keyword_search($fields_, $keyword_, $full_search_ = true){
	$query_where_keyword_ = "";
	$keyword_first_ = true;
	foreach($fields_ as $field_){
		if(!$keyword_first_){
			$query_where_keyword_ .= " OR ";
		}

		$query_where_keyword_ .= " {$field_} LIKE '".($full_search_ ? "%" : "").pb_database_escape_string($keyword_)."%'  ";
		$keyword_first_ = false;
	}

	return " ({$query_where_keyword_}) ";
}


function pb_format_mapping($map_, $data_){
	$converted_data_ = array();
	$converted_format_ = array();
	foreach($data_ as $key_ => $value_){
		if(isset($map_[$key_])){
			
			if($value_ !== null){
				$converted_data_[$key_] = $value_;
				$converted_format_[] = $map_[$key_];
			}else{
				$converted_data_[$key_] = "NULL";
				$converted_format_[] = "##null##";
			}
		}
	}
	
	return array('data' => $converted_data_, 'format' => $converted_format_);
}

function pb_query_in_fields($data_, $column_name_, $empty_replace_ = "1", $is_str_ = true, $trim_ = true){
	$query_ = "";
	$cover_char_ = ($is_str_ ? "'" : "");

	if(gettype($data_) !== "array"){

		if($trim_){
			$data_ = trim($data_);
		}

		if(strlen($data_) > 0){
			$data_ = array($data_);	
		}else $data_ = array();
	}

	if(count($data_) == 1){
		return " {$column_name_} = {$cover_char_}".pb_database_escape_string($data_[0])."{$cover_char_} ";
	}

	for($index_=1;$index_ < count($data_); ++$index_){
		$query_ .= ", {$cover_char_}".pb_database_escape_string($data_[$index_])."{$cover_char_} ";
	}

	if(count($data_) > 0){
		return " {$column_name_} IN ( {$cover_char_}".pb_database_escape_string($data_[0])."{$cover_char_} ".$query_.") ";
	}else return " {$empty_replace_} ";
}

?>
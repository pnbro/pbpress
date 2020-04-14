<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $pb_config;

define('PB_RANDOM_STRING_ALL', "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
define('PB_RANDOM_STRING_NUMLOWER', "0123456789abcdefghijklmnopqrstuvwxyz");
define('PB_RANDOM_STRING_NUMUPPER', "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ");
define('PB_RANDOM_STRING_NUM', "0123456789");
define('PB_RANDOM_STRING_ALPHABET', "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
define('PB_RANDOM_STRING_LOWER', "abcdefghijklmnopqrstuvwxyz");
define('PB_RANDOM_STRING_UPPER', "ABCDEFGHIJKLMNOPQRSTUVWXYZ");

function pb_random_string($length_ = 20, $characters_ = PB_RANDOM_STRING_ALL){
	$characters_length_ = iconv_strlen($characters_, 'UTF-8');
	$random_string_ = "";
	for($i_ = 0; $i_ < $length_; $i_++){
		$random_string_ .= mb_substr($characters_, rand(0, $characters_length_ - 1), 1, 'UTF-8');
	}
	return $random_string_;
}


function pb_append_url($base_url_, $path_){

	$check_path_ = strpos($path_, "/");
	if($check_path_ !== false && $check_path_ == 0){
		$path_ = substr($path_, 1);
	}

	if((strrpos($base_url_, "/") + 1) >= iconv_strlen($base_url_)){
		$base_url_ .= $path_;
	}else{
		$base_url_ .= "/".$path_;
	}
	
	return $base_url_;
}
function pb_make_url($base_url_, $params_ = array()){
	$concat_char_ = "?";

	foreach($params_ as $key_ => $value_){
		if(strpos($base_url_, "?") > 0)
			$concat_char_ = "&";
		else $concat_char_ = "?";

		$base_url_ .= $concat_char_.$key_."=".urlencode($value_);			
	}

	return $base_url_;
}

function pb_current_time( $type = "mysql", $gmt = 0 ) {
	$gmt_offset_ = date('Z');
	switch ( $type ) {
		case 'mysql':
			return ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ( $gmt_offset_ ) ) );
		case 'timestamp':
			return ( $gmt ) ? time() : time() + ( $gmt_offset_ );
		default:
			return ( $gmt ) ? date( $type ) : date( $type, time() + ( $gmt_offset_ ) );
	}
}

function pb_selected($selected_, $current_ = true, $echo_ = true){
	if($selected_ !== $current_) return;
	if($echo_) echo "selected='selected'";
	return "selected='selected'";
}
function pb_checked($checked_, $current_ = true, $echo_ = true ){
	if($checked_ !== $current_) return;
	if($echo_) echo "checked='checked'";
	return "checked='checked'";
}
function pb_disabled($val_, $current_ = true, $echo_ = true ){
	if($val_ !== $current_) return;
	if($echo_) echo "disabled='disabled'";
	return "disabled='disabled'";
}

function pb_alphabet_sequence($length_, $sequence_){
	$alphabet_length_ = strlen(PB_RANDOM_STRING_LOWER);

	$temp_val_ = $length_;
	$digit_ = 1;

	while(true){
		$check_ = $temp_val_ - pow($alphabet_length_, $digit_) - ($alphabet_length_);
		
		if($check_ < 1.0){
			break;
		}
		
		++$digit_;
	}

	$count_ = 0;
	$result_ = null;
	while($count_ <= $sequence_){
		if($result_ === null) $result_ = "a";
		else ++$result_;
		++$count_;
	}

	return str_pad($result_, $digit_, "a", STR_PAD_LEFT);
}

function pb_slugify($slug_){
	$search_ = array('Ș', 'Ț', 'ş', 'ţ', 'Ş', 'Ţ', 'ș', 'ț', 'î', 'â', 'ă', 'Î', 'Â', 'Ă', 'ë', 'Ë');
	$replace_ = array('s', 't', 's', 't', 's', 't', 's', 't', 'i', 'a', 'a', 'i', 'a', 'a', 'e', 'E');
	$slug_ = str_ireplace($search_, $replace_, strtolower(trim($slug_)));

	$slug_ = str_replace(' ', '-', $slug_);
	$slug_ = preg_replace('/\-{2,}/', '-', $slug_);
	$slug_ = preg_replace("/[ #\&\+%@=\/\\\:;,\.'\"\^`~|\!\?\*$#<>()\[\]\{\}]/i", "", $slug_);
	return $slug_;
}

function pb_is_https(){
	$https_ = false;
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
		$https_ = true;
	}elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'){
		$https_ = true;
	}
	return $https_;
}

global $_pb_safe_substr_func, $_pb_safe_strlen;

if(function_exists("mb_substr")){
	mb_internal_encoding($pb_config->charset);
	$_pb_safe_substr_func = "mb_substr";
	$_pb_safe_strlen = "mb_strlen";	
}else if(function_exists("iconv_substr")){
	$_pb_safe_substr_func = "iconv_substr";
	$_pb_safe_strlen = "iconv_strlen";
}else{
	$_pb_safe_substr_func = "substr";
	$_pb_safe_strlen = "strlen";
}

function pb_substr(){
	global $_pb_safe_substr_func;
	return call_user_func_array($_pb_safe_substr_func, func_get_args());
}
function pb_strlen(){
	global $_pb_safe_strlen;
	return call_user_func_array($_pb_safe_strlen, func_get_args());
}

define('PB_PARAM_PLAIN', 1);
define('PB_PARAM_INT', 3);
define('PB_PARAM_FLOAT', 5);
define('PB_PARAM_ARRAY', 7);
define('PB_PARAM_JSON', 9);

function _pb_param_from_($map_, $key_, $default_ = null, $type_ = PB_PARAM_PLAIN){
	if(!isset($map_[$key_])) return $default_;

	$param_value_ = $map_[$key_];

	switch($type_){
		case PB_PARAM_INT : 
			if(!strlen($param_value_)) return $default_;
			return (int)$param_value_;

		break;
		case PB_PARAM_FLOAT : 
			if(!strlen($param_value_)) return $default_;
			return (float)$param_value_; 

		break;
		case PB_PARAM_ARRAY : 
			if(gettype($param_value_) === "array") return $param_value_;
			if(!strlen($param_value_)) return $default_;
			return explode(",", $param_value_);
		break;
		case PB_PARAM_JSON : 
			if(gettype($param_value_) !== "array"){
				return json_decode($param_value_, true);
			}

			return $param_value_;
		break;
		case PB_PARAM_PLAIN : 
		default : 
			if(gettype($param_value_) === "array" || gettype($param_value_) === "object") return $param_value_;
			
			if(!strlen($param_value_)) return $default_;
			return $param_value_;
		break;
	}
}

function _GET($key_, $default_ = null, $type_ = PB_PARAM_PLAIN){
	return _pb_param_from_($_GET, $key_, $default_, $type_);
}
function _POST($key_, $default_ = null, $type_ = PB_PARAM_PLAIN){
	return _pb_param_from_($_POST, $key_, $default_, $type_);	
}
	
?>
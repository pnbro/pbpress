<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define("PB_OPTION_THEME_NAME", "theme");

function pb_theme_list(){
	global $pb_theme_list;

	if(isset($pb_theme_list)) return $pb_theme_list;

	$pb_theme_list = array();

	foreach(glob(PB_DOCUMENT_PATH . "themes/**") as $theme_){
		if(!is_dir($theme_)) continue;
		$theme_slug_ = basename($theme_);

		$theme_info_ = null;

		if(file_exists($theme_."/theme-info.json")){
			$theme_info_ = json_decode(file_get_contents($theme_."/theme-info.json"), true);
		}

		if(!isset($theme_info_)){
			$theme_info_ = array(
				'name' => $theme_slug_,
				'desc' => $theme_slug_,
			);
		}

		$theme_info_['author'] = isset($theme_info_['author']) ? $theme_info_['author'] : null;
		$theme_info_['version'] = isset($theme_info_['version']) ? $theme_info_['version'] : null;

    	$pb_theme_list[$theme_slug_] = $theme_info_;
	}
	return $pb_theme_list;
}

function pb_switch_theme($theme_){
	pb_hook_do_action('pb_switch_theme_before', $theme_);
	$before_theme_ = pb_option_value(PB_OPTION_THEME_NAME);

	global $_pb_current_theme;
	$_pb_current_theme = null;

	pb_option_update(PB_OPTION_THEME_NAME, null);

	$switch_theme_url_ = PB_DOCUMENT_URL . 'includes/common/_switch_theme.php';

	$request_token_ = pb_random_string(20);
	pb_option_update("_theme_switch_key_",$request_token_);

	$curl_instance_ = curl_init();
	curl_setopt($curl_instance_,CURLOPT_URL, $switch_theme_url_);
	curl_setopt($curl_instance_,CURLOPT_HTTPHEADER, array(
		// 'Content-Type: application/json',
	));
	
	curl_setopt($curl_instance_,CURLOPT_POST, true);
	curl_setopt($curl_instance_,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_instance_,CURLOPT_POSTFIELDS, array(
		'theme' => $theme_,
		'request_token' => $request_token_,
	));

	$result_message_ = curl_exec($curl_instance_);
	$result_code_ = curl_getinfo($curl_instance_, CURLINFO_HTTP_CODE);

	$result_ = @json_decode($result_message_, true);

	if(!$result_['success']){
		pb_option_update(PB_OPTION_THEME_NAME, $before_theme_);
		pb_option_update("_theme_switch_key_",null);
		return new PBError(503, $result_['error_title'], $result_['error_message']);
	}

	pb_option_update("_theme_switch_key_",null);

	pb_hook_do_action('pb_switch_theme_after', $theme_);
	return true;
}

function pb_current_theme(){
	global $_pb_current_theme;

	if(strlen($_pb_current_theme)) return $_pb_current_theme;

	$theme_ = pb_option_value(PB_OPTION_THEME_NAME);
	$_pb_current_theme = $theme_;
	return $_pb_current_theme;
}

function pb_current_theme_path(){
	return PB_DOCUMENT_PATH."themes/".pb_current_theme()."/";
}

function pb_current_theme_url(){
	return PB_DOCUMENT_URL."themes/".pb_current_theme()."/";	
}

function pb_theme_part($part_name_){
	$part_path_ = PB_DOCUMENT_PATH."themes/".pb_current_theme()."/{$part_name_}.php";
	$part_path_ = pb_hook_apply_filters('pb_theme_part', $part_path_, $part_name_);
	include($part_path_);
}

function pb_theme_header($header_name_ = null){
	if(strlen($header_name_)){
		$header_name_ = "header-".$header_name_;
	}else{
		$header_name_ = "header";
	}
	pb_theme_part($header_name_);
}

function pb_theme_footer($footer_name_ = null){
	if(strlen($footer_name_)){
		$footer_name_ = "footer-".$footer_name_;
	}else{
		$footer_name_ = "footer";
	}
	pb_theme_part($footer_name_);
}

include(PB_DOCUMENT_PATH . 'includes/common/theme-builtin.php');

?>
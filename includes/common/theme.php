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
	pb_option_update(PB_OPTION_THEME_NAME, $theme_);

	global $_pb_current_theme;
	$_pb_current_theme = null;

	pb_hook_do_action('pb_switch_theme_after', $theme_);
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

function pb_theme_header($header_name_ = null){
	if(strlen($header_name_)){
		$header_name_ = "header-".$header_name_;
	}else{
		$header_name_ = "header";
	}

	include(PB_DOCUMENT_PATH."themes/".pb_current_theme()."/{$header_name_}.php");
}

function pb_theme_footer($footer_name_ = null){
	if(strlen($footer_name_)){
		$footer_name_ = "footer-".$footer_name_;
	}else{
		$footer_name_ = "footer";
	}
	include(PB_DOCUMENT_PATH."themes/".pb_current_theme()."/{$footer_name_}.php");	
}

include(PB_DOCUMENT_PATH . 'includes/common/theme-builtin.php');


?>
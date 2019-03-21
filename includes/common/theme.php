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
    	$pb_theme_list[basename($theme_)] = array(
    		'name' => basename($theme_),
    	);
	}
	return $pb_theme_list;
}

function pb_switch_theme($theme_){
	pb_option_update(PB_OPTION_THEME_NAME, $theme_);
}

function pb_current_theme(){
	return pb_option_value(PB_OPTION_THEME_NAME);
}

function pb_current_theme_path(){
	return PB_DOCUMENT_PATH."themes/".pb_current_theme()."/";
}

function pb_current_theme_url(){
	return PB_DOCUMENT_URL."themes/".pb_current_theme()."/";	
}

function pb_theme_header(){
	include(PB_DOCUMENT_PATH."themes/".pb_current_theme()."/header.php");
}

function pb_theme_footer(){
	include(PB_DOCUMENT_PATH."themes/".pb_current_theme()."/footer.php");	
}

function pb_theme_install_tables(){
	global $pbdb;
	$query_list_ = pb_hook_apply_filters("pb_install_theme_tables", array());

	foreach($query_list_ as $query_){
		$pbdb->query($query_);
	}

	pb_hook_do_action("pb_installed_theme_tables");
	$pbdb->commit();
}
pb_hook_add_action('pb_installed_tables', "pb_theme_install_tables");
	
?>
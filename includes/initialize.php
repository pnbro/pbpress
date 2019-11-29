<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

//load common modules
require(PB_DOCUMENT_PATH . 'includes/includes.php');

//check install
if(!$pbdb->exists_table("options")){
	pb_redirect(PB_DOCUMENT_URL."admin/install.php");
	pb_hook_do_action('pb_ended');
	exit;
}

//check install pbress
global $pbdb, $pb_config;

//check rewrite rule
if(!pb_exists_rewrite()){
	pb_install_rewrite();
	header('Location: '.pb_home_url());
	pb_hook_do_action('pb_ended');
	exit;
}

//check https config
if((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") && $pb_config->use_https()){
	$https_location_ = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $https_location_);
	pb_hook_do_action('pb_ended');
	exit;
}

//set timzone
$timezone_ = pb_option_value("timezone");
if(!strlen($timezone_)){
	$timezone_ = @date_default_timezone_get();
	$timezone_ = strlen($timezone_) ? $timezone_ : "Asia/Seoul";
}
date_default_timezone_set($timezone_);

$current_theme_path_ = pb_current_theme_path();

if(file_exists($current_theme_path_."functions.php")){
	include($current_theme_path_."functions.php");
}

header("Content-Type: text/html; CharSet=".$pb_config->charset);
pb_hook_do_action("pb_init");

?>
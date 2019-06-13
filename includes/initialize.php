<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

//load common modules
require(PB_DOCUMENT_PATH . 'includes/includes.php');

//check install pbress
global $pbdb, $pb_config;

if(!$pbdb->exists_table("options")){
	pb_redirect(PB_DOCUMENT_URL."admin/install.php");
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


//check rewrite rule
if(!pb_exists_rewrite()){
	pb_install_rewrite();
	header('Location: '.pb_home_url());
	pb_hook_do_action('pb_ended');
	exit;
}

//check admin rewrite rule
if(!pb_exists_admin_rewrite()){
	pb_install_admin_rewrite();
	header('Location: '.pb_admin_url());
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

header("Content-Type: text/html; CharSet=".$pb_config->charset);

?>
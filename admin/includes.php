<?php

require(dirname( __FILE__ ) . '/../defined.php');
require(PB_DOCUMENT_PATH . 'includes/includes.php');
require(dirname( __FILE__ ) . '/admin-hook.php');
require(dirname( __FILE__ ) . '/function.php');

global $pbdb, $pb_config;

pb_hook_do_action("pb_before_admin_init");

//check install
if(!$pbdb->exists_table("options")){
	pb_redirect(PB_DOCUMENT_URL."admin/install.php");
	pb_hook_do_action('pb_ended');
	exit;
}

ini_set('default_charset', $pb_config->charset);

//check rewrite rule
if(!pb_exists_rewrite()){
	pb_install_rewrite();
	header('Location: '.pb_admin_url());
	pb_hook_do_action('pb_ended');
	exit;
}

//check https config
if(!pb_is_https() && $pb_config->use_https()){
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

pb_hook_do_action("pb_admin_init");
pb_hook_do_action("pb_after_admin_init");

?>
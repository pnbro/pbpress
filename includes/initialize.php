<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

//load common modules
require(PB_DOCUMENT_PATH . 'includes/includes.php');

//check install pbress
global $pbdb;

if(!$pbdb->exists_table("OPTIONS")){
	pb_redirect(PB_DOCUMENT_URL."admin/install.php");
	pb_hook_do_action('pb_ended');
	exit;
}

if(!pb_exists_rewrite()){
	pb_install_rewrite();
	header('Location: '.pb_home_url());
	pb_hook_do_action('pb_ended');
	exit;
}
if(!pb_exists_admin_rewrite()){
	pb_install_admin_rewrite();
	header('Location: '.pb_admin_url());
	pb_hook_do_action('pb_ended');
	exit;
}

header("Content-Type: text/html; CharSet=utf-8");

?>
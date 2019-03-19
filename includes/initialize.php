<?

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

function pb_head(){
	$pbvar_ = pb_hook_apply_filters('pb-head-pbvar', array());

	global $pb_config;
?>
<script type="text/javascript">window.PBVAR = <?=json_encode($pbvar_)?>;</script>

<?php 
	pb_hook_do_action('pb_head');
}

function pb_foot(){
	pb_hook_do_action('pb_foot');
}

function pb_end(){
	pb_hook_do_action('pb_ended');
	exit;	
}

header("Content-Type: text/html; CharSet=utf-8");

?>
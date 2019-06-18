<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $pb_config;

header("Content-Type:application/json; charset=".$pb_config->charset);

set_error_handler('_pb_exceptions_ajax_error_handler');

function _pb_exceptions_ajax_error_handler($severity_, $message_, $filename_, $lineno_){
	if(error_reporting() == 0){
		return;
	}
	global $pb_config;
	header("Content-Type:application/json; charset=".$pb_config->charset);
	echo json_encode(array(
		'success' => false,
		'error_message' => "[".$filename_.", line : ".$lineno_."]".$message_, "AJAX ERROR",
	));
	pb_end();
}

$rewrite_slug_ = pb_current_slug();
$rewrite_path_ = pb_rewrite_path();

if($rewrite_slug_ !== "ajax" || count($rewrite_path_) < 2){
	pb_redirect_404();
	pb_end();
}

pb_hook_do_action('pb_ajax_' . $rewrite_path_[1]);
pb_end();
	
?>
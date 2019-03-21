<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_fileupload_url($params_ = array()){
	return pb_make_url(pb_home_url("fileupload"), $params_);
}

function _pb_fileupload_add_to_rewrite($results_){
	$results_['fileupload'] = array(
		'page' => PB_DOCUMENT_PATH."includes/common/_fileupload.php",
	);

	return $results_;
};
pb_hook_add_filter('pb_rewrite_list', "_pb_fileupload_add_to_rewrite");

function _pb_fileupload_add_to_header_pbvar($results_){
	$results_['fileupload_url'] = pb_fileupload_url();
	return $results_;
};
pb_hook_add_filter('pb-admin-head-pbvar', "_pb_fileupload_add_to_header_pbvar");
pb_hook_add_filter('pb-head-pbvar', "_pb_fileupload_add_to_header_pbvar");

?>
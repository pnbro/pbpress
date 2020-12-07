<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_fileupload_url($params_ = array()){
	return pb_make_url(pb_home_url("fileupload"), $params_);
}
function pb_filebase_url($file_path_ = null, $params_ = array(), $handler_ = null){
	$file_upload_handler_ = pb_fileupload_handler($handler_);
	if(!isset($file_upload_handler_) || pb_is_error($file_upload_handler_)) return null;
	return pb_hook_apply_filters('pb_filebase_url', $file_upload_handler_->filebase_url($file_path_, $params_), $file_path_, $params_);
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
	$results_['filebase_url'] = pb_filebase_url();
	return $results_;
};
pb_hook_add_filter('pb-admin-head-pbvar', "_pb_fileupload_add_to_header_pbvar");
pb_hook_add_filter('pb-head-pbvar', "_pb_fileupload_add_to_header_pbvar");

define('PB_FILE_UPLOAD_HANDLER_DEFAULT', "PBPressFileUPloadDefaultHandler");

function pb_fileupload_handler($handler_ = null){
	global $pb_config, $pb_fileupload_handler;

	if(!strlen($handler_)){
		$handler_ = $pb_config->file_upload_handler;
	}
	
	if(isset($pb_fileupload_handler[$handler_])){
		return $pb_fileupload_handler[$handler_];
	}

	$file_upload_handler_ = pb_hook_apply_filters('pb_fileupload_handler', $handler_);

	switch($file_upload_handler_){
		case "default" : $file_upload_handler_ = PB_FILE_UPLOAD_HANDLER_DEFAULT;
			require(PB_DOCUMENT_PATH . 'includes/common/fileuploadhandler.default.php');
			break;
	}

	if(!class_exists($file_upload_handler_)) return pb_error(500, __("잘못된 요청"), __("파일핸들러가 존재하지 않습니다."));

	$pb_fileupload_handler[$handler_] = new $file_upload_handler_;
	$pb_fileupload_handler[$handler_]->initialize();
	return $pb_fileupload_handler[$handler_];
}

function pb_fileupload_handle($files_, $options_ = array()){
	$handler_ = pb_fileupload_handler((isset($options_['handler']) ? $options_['handler'] : null));
	if(pb_is_error($handler_)) return $handler_;

	global $pb_config;
	pb_hook_do_action('pb_fileupload_before_handle', $files_, $options_, $handler_);
	
	return pb_hook_apply_filters('pb_fileupload_handle_results', $handler_->handle($files_, $options_));
}


abstract class PBPressFileUPloadHandler{
	abstract function initialize();
	abstract function filebase_url($file_path_ = null, $params_ = array());
	abstract function handle($files_, $options_ = array());
}

?>
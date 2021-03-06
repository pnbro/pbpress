<?php

require(dirname( __FILE__ ) . '/../../defined.php');
require(PB_DOCUMENT_PATH . 'includes/initialize.php');

global $pb_config;

header("Content-Type:application/json; charset=".$pb_config->charset);

set_error_handler('_pb_exceptions_switch_theme_error_handler');
function _pb_exceptions_switch_theme_error_handler($severity_, $message_, $filename_, $lineno_){
	if(error_reporting() == 0){
		return;
	}
	global $pb_config;
	header("Content-Type:application/json; charset=".$pb_config->charset);
	echo json_encode(array(
		'success' => false,
		'error_title' => __("에러발생"),
		'error_message' => "[".$filename_.", line : ".$lineno_."]".$message_,
	));
	pb_option_update(PB_OPTION_THEME_NAME, null);
	pb_end();
}

$theme_ = _POST('theme');
$request_token_ = _POST('request_token');

if(!strlen($theme_) || (pb_option_value("_theme_switch_key_") !== $request_token_)){
	echo json_encode(array(
		'success' => false,
		'error_title' => __("에러발생"),
		'error_message' => __("잘못된 요청입니다."),
		
	));
	pb_end();
}

pb_option_update(PB_OPTION_THEME_NAME, $theme_);

if(file_exists($current_theme_path_."functions.php")){
	include_once $current_theme_path_."functions.php";
}

_pb_theme_install_tables();

echo json_encode(array(
	'success' => true,
));
pb_end();
	
?>
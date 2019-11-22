<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_add_ajax('sample-theme-ajax-test', "_sample_theme_ajax_test");

function _sample_theme_ajax_test(){
	echo json_encode(array(
		"success" => true, 
		"message" => "Welcome to PBPress!",
	));
}

?>
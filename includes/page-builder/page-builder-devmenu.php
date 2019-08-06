<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


function _pb_page_builder_register_adminpage($results_){
	$results_['test-page-builder'] = array(
		'name' => '페이지빌더(테스트)',
		'type' => 'menu',
		'directory' => 'page',
		'page' => PB_DOCUMENT_PATH."includes/page-builder/views/dev.php",
		'authority_task' => 'manage_page',
		'subpath' => null,
		'sort' => 99,
	);
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', '_pb_page_builder_register_adminpage');


?>
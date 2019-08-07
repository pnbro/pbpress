<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_menu_register_adminpage($results_){

	$results_['manage-menu'] = array(
		'name' => '메뉴관리',
		'type' => 'menu',
		'directory' => 'common',
		'page' => PB_DOCUMENT_PATH."includes/menu/views/edit.php",
		'authority_task' => 'manage_page',
		'subpath' => null,
		'sort' => 7,
	);
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', '_pb_menu_register_adminpage');

?>
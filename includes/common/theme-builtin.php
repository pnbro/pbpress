<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_theme_install_tables(){
	global $pbdb;
	$query_list_ = pb_hook_apply_filters("pb_install_theme_tables", array());

	foreach($query_list_ as $query_){
		$pbdb->query($query_);
	}

	pb_hook_do_action("pb_installed_theme_tables");
	$pbdb->commit();
}
pb_hook_add_action('pb_installed_tables', "_pb_theme_install_tables");

function _pb_theme_hook_register_adminpage_list($results_){
	$results_['manage-theme'] = array(
		'name' => '테마설정',
		'type' => 'menu',
		'directory' => 'common',
		'page' => PB_DOCUMENT_PATH."includes/common/views/manage-theme.php",
		'authority_task' => 'manage_site',
		'subpath' => null,
		'sort' => 3,
	);
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', "_pb_theme_hook_register_adminpage_list");

function _pb_theme_ajax_do_update(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_site")){
		echo json_encode(array(
			'success' => false,
			'error_title' => '권한없음',
			'error_message' => '사이트관리 권한이 없습니다.',
		));
		pb_end();	
	}


	global $pb_config;

	$theme_data_ = isset($_POST['theme_data']) ? $_POST['theme_data'] : null;

	if(!isset($theme_data_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => '에러발생',
			'error_message' => '요청값이 잘못되었습니다.',
		));
		pb_end();	
	}

	if(!pb_verify_request_token("pbpress_manage_theme", $theme_data_['_request_chip'])){
		echo json_encode(array(
			'success' => false,
			'error_title' => '에러발생',
			'error_message' => '요청토큰이 잘못되었습니다.',
		));
		pb_end();	
	}

	$before_theme_ = pb_current_theme();
	$target_theme_ = $theme_data_['theme'];

	pb_option_update("theme", $target_theme_);

	if($before_theme_ !== $target_theme_){
		pb_switch_theme($target_theme_);	
	}

	echo json_encode(array(
		'success' => true,
		'redirect_url' => pb_admin_url("manage-theme"),
	));
	pb_end();
}
pb_add_ajax('admin-update-theme', '_pb_theme_ajax_do_update');

?>
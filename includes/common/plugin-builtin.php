<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_plugin_register_authority_task_types($results_){
	$results_['manage_plugins'] = array(
		'name' => __('플러그인관리'),
		'selectable' => true,
	);

	return $results_;
}
pb_hook_add_filter('pb_authority_task_types', "_pb_plugin_register_authority_task_types");

function _pb_authority_register_task_for_plugin(){
	$check_ = pb_authority_task_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_plugins");
	if(isset($check_)) return;

	$auth_data_ = pb_authority_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR);

	pb_authority_task_add(array(
		'auth_id' => $auth_data_['id'],
		'slug' => "manage_plugins",
		'reg_date' => pb_current_time(),
	));
}
pb_hook_add_action('pb_installed_tables', "_pb_authority_register_task_for_plugin");

function _pb_plugin_hook_register_adminpage_list($results_){
	$results_['manage-plugin'] = array(
		'name' => __('플러그인설정'),
		'type' => 'menu',
		'directory' => 'common',
		'page' => PB_DOCUMENT_PATH."includes/common/views/manage-plugins.php",
		'authority_task' => 'manage_plugins',
		'subpath' => null,
		'sort' => 4,
	);
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', "_pb_plugin_hook_register_adminpage_list");

function _pb_plugin_hook_for_execute_activated(){
	$activated_plugins_ = pb_activated_plugins();
	foreach($activated_plugins_ as $slug_){
		$func_path_ = PB_PLUGINS_PATH."{$slug_}/functions.php";
		if(!file_exists($func_path_)) continue;
		include_once $func_path_;
	}
}
pb_hook_add_action('pb_init', '_pb_plugin_hook_for_execute_activated');
pb_hook_add_action('pb_admin_init', '_pb_plugin_hook_for_execute_activated');

function _pb_plugin_ajax_active(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_plugins")){
		echo json_encode(array(
			'success' => false,
			'error_title' => __('권한없음'),
			'error_message' => __('플러그인관리 권한이 없습니다.'),
		));
		pb_end();	
	}


	global $pb_config;

	$slugs_ = _POST('slugs');
	$request_chip_ = _POST('request_chip');

	if(!isset($slugs_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => __('에러발생'),
			'error_message' => __('요청값이 잘못되었습니다.'),
		));
		pb_end();	
	}

	if(!pb_verify_request_token("pbpress_manage_plugins", $request_chip_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => __('에러발생'),
			'error_message' => __('요청토큰이 잘못되었습니다.'),
		));
		pb_end();	
	}
	foreach($slugs_ as $slug_){
		pb_active_plugin($slug_);	
	}

	echo json_encode(array(
		'success' => true,
	));
	pb_end();	
}
pb_add_ajax('admin-active-plugins', '_pb_plugin_ajax_active');

function _pb_plugin_ajax_deactive(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_plugins")){
		echo json_encode(array(
			'success' => false,
			'error_title' => __('권한없음'),
			'error_message' => __('플러그인관리 권한이 없습니다.'),
		));
		pb_end();	
	}


	global $pb_config;

	$slugs_ = _POST('slugs');
	$request_chip_ = _POST('request_chip');

	if(!isset($slugs_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => __('에러발생'),
			'error_message' => __('요청값이 잘못되었습니다.'),
		));
		pb_end();	
	}

	if(!pb_verify_request_token("pbpress_manage_plugins", $request_chip_)){
		echo json_encode(array(
			'success' => false,
			'error_title' => __('에러발생'),
			'error_message' => __('요청토큰이 잘못되었습니다.'),
		));
		pb_end();	
	}
	foreach($slugs_ as $slug_){
		pb_deactive_plugin($slug_);	
	}

	echo json_encode(array(
		'success' => true,
	));
	pb_end();	
}
pb_add_ajax('admin-deactive-plugins', '_pb_plugin_ajax_deactive');

?>
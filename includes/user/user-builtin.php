<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_user_register_authority_task_types($results_){
	$results_['manage_user'] = array(
		'name' => '사용자관리'
	);

	return $results_;
}
pb_hook_add_filter('pb_authority_task_types', "_pb_user_register_authority_task_types");

function _pb_user_installed_tables(){
	$check_ = pb_authority_task_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_user");
	if(isset($check_)) return;

	$auth_data_ = pb_authority_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR);

	pb_authority_task_add(array(
		'auth_id' => $auth_data_['id'],
		'slug' => "manage_user",
		'reg_date' => pb_current_time(),
	));

}
pb_hook_add_action('pb_installed_tables', "_pb_user_installed_tables");

function _pb_user_initialize_gcode_list($gcode_list_){
	$gcode_list_['U0001'] = array(
		'name' => '사용자상태',
		'data' => array(
			'00003' => '정상등록',
			'00009' => '사용불가',
		),
	);

	return $gcode_list_;
}
pb_hook_add_filter("pb_intialize_gcode_list", "_pb_user_initialize_gcode_list");

?>
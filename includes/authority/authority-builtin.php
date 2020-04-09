<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define("PB_AUTHORITY_SLUG_ADMINISTRATOR", "administrator");

function _pb_authority_insert_defaults(){
	global $pbdb;

	$check_data_ = pb_authority_list(array("limit" => array(0,1)));
	if(count($check_data_) > 0) return;

	$auth_id_ = pb_authority_add(array(
		'slug' => PB_AUTHORITY_SLUG_ADMINISTRATOR,
		'auth_name' => '관리자',
		'auth_desc' => '사이트를 관리할 수 있는 권한',
		'reg_date' => pb_current_time(),
	));
	
	foreach(pb_authority_task_types() as $task_slug_ => $task_data_){
		pb_authority_task_add(array(
			'auth_id' => $auth_id_,
			'slug' => $task_slug_,
		));
	}
}
pb_hook_add_action("pb_installed_tables", "_pb_authority_insert_defaults");

function _pb_installed_tables_for_initial_authority(){
	global $_pb_authority_initial_list;

	foreach($_pb_authority_initial_list as $authority_slug_ => $task_list_){
		$auth_data_ = pb_authority_by_slug($authority_slug_);

		if(!isset($auth_data_)) continue;

		foreach($task_list_ as $task_){
			$check_ = pb_authority_task_by_slug($authority_slug_, $task_);
			if(isset($check_)) continue;

			pb_authority_task_add(array(
				'auth_id' => $auth_data_['id'],
				'slug' => $task_,
				'reg_date' => pb_current_time(),
			));
		}
			
	}
}
pb_hook_add_action('pb_installed_tables', '_pb_installed_tables_for_initial_authority');

pb_authority_task_add_type('manage_authority', array(
	'name' => '권한관리',
	'selectable' => false,
));
pb_authority_initial_register(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_authority");

?>
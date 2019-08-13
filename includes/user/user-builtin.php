<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_user_install_table($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `users` (
		
		`id` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
		
		`user_login` varchar(50) NOT NULL COMMENT '로그인ID',
		`user_pass` varchar(100) NOT NULL COMMENT '패스워드',
		`user_email` varchar(100) NOT NULL COMMENT '로그인ID',
		`user_name` varchar(50) NOT NULL COMMENT '사용자명',
		`status` varchar(5) NOT NULL COMMENT '사용여부',

		`findpass_vkey` varchar(20) NOT NULL COMMENT '암호찾기 - 인증키',
		`findpass_vkey_exp_date` datetime NOT NULL COMMENT '암호찾기 - 키만료일자',

		`reg_date` datetime DEFAULT NULL COMMENT '등록일자',
		`mod_date` datetime DEFAULT NULL COMMENT '수정일자',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB COMMENT='사용자';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "_pb_user_install_table");

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
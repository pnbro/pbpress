<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_user_install_table($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `USERS` (
		
		`ID` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
		
		`USER_LOGIN` varchar(50) NOT NULL COMMENT '로그인ID',
		`USER_PASS` varchar(100) NOT NULL COMMENT '패스워드',
		`USER_EMAIL` varchar(100) NOT NULL COMMENT '로그인ID',
		`USER_NAME` varchar(50) NOT NULL COMMENT '사용자명',
		`STATUS` varchar(5) NOT NULL COMMENT '사용여부',

		`FINDPASS_VKEY` varchar(20) NOT NULL COMMENT '암호찾기 - 인증키',
		`FINDPASS_VKEY_EXP_DATE` datetime NOT NULL COMMENT '암호찾기 - 키만료일자',

		`REG_DATE` datetime DEFAULT NULL COMMENT '등록일자',
		`MOD_DATE` datetime DEFAULT NULL COMMENT '수정일자',
		PRIMARY KEY (`ID`)
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
		'AUTH_ID' => $auth_data_['ID'],
		'SLUG' => "manage_user",
		'TASK_NAME' => "사용자관리",
		'REG_DATE' => pb_current_time(),
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
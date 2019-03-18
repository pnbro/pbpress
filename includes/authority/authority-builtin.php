<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define("PB_AUTHORITY_SLUG_ADMINISTRATOR", "administrator");

function _pb_authority_install_table($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `AUTH` (
		
		`ID` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
		`SLUG` varchar(100) NOT NULL COMMENT '슬러그',
		`AUTH_NAME` varchar(50) NOT NULL COMMENT '권한명',
		`AUTH_DESC` varchar(100) DEFAULT NULL COMMENT '권한설명',
		
		`REG_DATE` datetime DEFAULT NULL COMMENT '등록일자',
		`MOD_DATE` datetime DEFAULT NULL COMMENT '수정일자',
		PRIMARY KEY (`ID`)
	) ENGINE=InnoDB COMMENT='권한';";

	$args_[] = "CREATE TABLE IF NOT EXISTS `AUTH_TASK` (
		
		`ID` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
		
		`AUTH_ID` BIGINT(11) NOT NULL COMMENT '슬러그',
		`SLUG` varchar(100) NOT NULL COMMENT '슬러그',
		`TASK_NAME` varchar(50) NOT NULL COMMENT '작업명',
		
		`REG_DATE` datetime DEFAULT NULL COMMENT '등록일자',
		`MOD_DATE` datetime DEFAULT NULL COMMENT '수정일자',
		PRIMARY KEY (`ID`),
		CONSTRAINT `AUTH_TASK_FK1` FOREIGN KEY (`AUTH_ID`) REFERENCES `AUTH` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB COMMENT='권한별 작업범위';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "_pb_authority_install_table");

function _pb_authority_insert_defaults(){
	global $pbdb;

	$check_data_ = pb_authority_list(array("limit" => array(0,1)));
	if(count($check_data_) > 0) return;

	$auth_id_ = pb_authority_add(array(
		'SLUG' => PB_AUTHORITY_SLUG_ADMINISTRATOR,
		'AUTH_NAME' => '관리자',
		'AUTH_DESC' => '사이트를 관리할 수 있는 권한',
		'REG_DATE' => pb_current_time(),
	));
	
	foreach(pb_authority_task_types() as $task_slug_ => $task_data_){
		pb_authority_task_add(array(
			'AUTH_ID' => $auth_id_,
			'SLUG' => $task_slug_,
			'TASK_NAME' => $task_data_['name'],
		));
	}
}
pb_hook_add_action("pb_installed_tables", "_pb_authority_insert_defaults");

function _pb_authority_register_authority_task_types($results_){
	$results_['manage_authority'] = array(
		'name' => '권한관리'
	);

	return $results_;
}
pb_hook_add_filter('pb_authority_task_types', "_pb_authority_register_authority_task_types");

function _pb_authority_register_task(){
	$check_ = pb_authority_task_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_authority");
	if(isset($check_)) return;

	$auth_data_ = pb_authority_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR);

	pb_authority_task_add(array(
		'AUTH_ID' => $auth_data_['ID'],
		'SLUG' => "manage_authority",
		'TASK_NAME' => "권한관리",
		'REG_DATE' => pb_current_time(),
	));
}
pb_hook_add_action('pb_installed_tables', "_pb_authority_register_task");



?>
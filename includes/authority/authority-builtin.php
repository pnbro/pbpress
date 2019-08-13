<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define("PB_AUTHORITY_SLUG_ADMINISTRATOR", "administrator");

function _pb_authority_install_table($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `auth` (
		
		`id` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
		`slug` varchar(100) NOT NULL COMMENT '슬러그',
		`auth_name` varchar(50) NOT NULL COMMENT '권한명',
		`auth_desc` varchar(100) DEFAULT NULL COMMENT '권한설명',
		
		`reg_date` datetime DEFAULT NULL COMMENT '등록일자',
		`mod_date` datetime DEFAULT NULL COMMENT '수정일자',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB COMMENT='권한';";

	$args_[] = "CREATE TABLE IF NOT EXISTS `auth_task` (
		
		`id` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
		
		`auth_id` BIGINT(11) NOT NULL COMMENT '슬러그',
		`slug` varchar(100) NOT NULL COMMENT '슬러그',
		
		`reg_date` datetime DEFAULT NULL COMMENT '등록일자',
		`mod_date` datetime DEFAULT NULL COMMENT '수정일자',
		PRIMARY KEY (`id`),
		CONSTRAINT `auth_task_fk1` FOREIGN KEY (`auth_id`) REFERENCES `auth` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB COMMENT='권한별 작업범위';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "_pb_authority_install_table");

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
		'auth_id' => $auth_data_['id'],
		'slug' => "manage_authority",
		'reg_date' => pb_current_time(),
	));
}
pb_hook_add_action('pb_installed_tables', "_pb_authority_register_task");



?>
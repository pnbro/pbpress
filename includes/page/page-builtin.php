<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_page_install_tables($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `pages` (
		`id` bigint(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',

		`slug` varchar(100) NOT NULL COMMENT '슬러그',

		`page_title` varchar(200) NOT NULL COMMENT '페이지명',
		`page_html` longtext NOT NULL COMMENT '페이지HTML',
		`status` varchar(5) NOT NULL COMMENT '페이지상태(PA001)',

		`wrt_id` BIGINT(11) NOT NULL COMMENT '작성자ID',
		
		`reg_date` datetime DEFAULT null COMMENT '등록일자',
		`mod_date` datetime DEFAULT null COMMENT '수정일자',
		PRIMARY KEY (`id`),
		CONSTRAINT `pages_fk1` FOREIGN KEY (`wrt_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB COMMENT='페이지';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "pb_page_install_tables");

define('PB_PAGE_STATUS_WRITING', '00001');
define('PB_PAGE_STATUS_PUBLISHED', '00003');
define('PB_PAGE_STATUS_UNPUBLISHED', '00009');

function pb_campus_payment_initialize_gcode_list($gcode_list_){

	$gcode_list_['PAG01'] = array(
		'name' => '페이지등록상태',
		'data' => array(
			PB_PAGE_STATUS_WRITING => "작성중",
			PB_PAGE_STATUS_PUBLISHED => "공개",
			PB_PAGE_STATUS_UNPUBLISHED => "비공개",
		),
	);

	return $gcode_list_;
}
pb_hook_add_filter("pb_intialize_gcode_list", "pb_campus_payment_initialize_gcode_list");

?>
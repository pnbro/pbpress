<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_page_meta_install_tables($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `pages_meta` (
		`id` bigint(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
		`page_id` bigint(11) NOT NULL COMMENT 'ID',

		`meta_name` varchar(100) NOT NULL COMMENT '메타명',
		`meta_value` varchar(500) NOT NULL COMMENT '메타값',

		`reg_date` datetime DEFAULT null COMMENT '등록일자',
		`mod_date` datetime DEFAULT null COMMENT '수정일자',
		PRIMARY KEY (`id`),
		CONSTRAINT `pages_meta_fk1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB COMMENT='페이지 - 메타';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "pb_page_meta_install_tables");

?>
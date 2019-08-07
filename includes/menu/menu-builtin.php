<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_menu_install_tables($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `menus` (
		`id` bigint(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',

		`title` varchar(50) NOT NULL COMMENT '메뉴명',
		`slug` varchar(100) NOT NULL COMMENT '슬러그',
		`use_yn` varchar(1) NOT NULL COMMENT '사용여부',
		
		`reg_date` datetime DEFAULT null COMMENT '등록일자',
		`mod_date` datetime DEFAULT null COMMENT '수정일자',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB COMMENT='메뉴';";

	$args_[] = "CREATE TABLE IF NOT EXISTS `menus_dtl` (
		`id` bigint(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
		`parent_id` bigint(11) DEFAULT NULL COMMENT '상위ID',
		`menu_id` bigint(11) NOT NULL COMMENT '메뉴ID',
	
		`title` varchar(100) NOT NULL COMMENT '항목명',
		`ref_slug` varchar(100) NOT NULL COMMENT '참조슬러그',
		`sort_char` int(3) NOT NULL COMMENT '정렬순서',

		`reg_date` datetime DEFAULT null COMMENT '등록일자',
		`mod_date` datetime DEFAULT null COMMENT '수정일자',
		PRIMARY KEY (`id`),
		CONSTRAINT `menus_dtl_fk1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `menus_dtl_fk2` FOREIGN KEY (`parent_id`) REFERENCES `menus_dtl` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
	) ENGINE=InnoDB COMMENT='메뉴 - 항목';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "pb_menu_install_tables");

?>
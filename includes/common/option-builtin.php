<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_options_install_tables($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `options` (

		`id` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'id',

		`option_name` VARCHAR(100) NOT NULL COMMENT '옵션명',
		`option_value` VARCHAR(500) NOT NULL COMMENT '옵션값',
		
		`reg_date` DATETIME DEFAULT NULL COMMENT '등록일자',
		`mod_date` DATETIME DEFAULT NULL COMMENT '수정일자',
		
		PRIMARY KEY (`id`),
		KEY `lat_quest_met_idx1` (`option_name`)
	) ENGINE=InnoDB COMMENT='옵션';";
	
	
	return $args_;
}
pb_hook_add_filter('pb_install_tables', "_pb_options_install_tables");

?>
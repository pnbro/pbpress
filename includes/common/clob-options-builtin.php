<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_clob_options_install_tables($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `clob_options` (

		`id` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'id',

		`option_name` VARCHAR(100) NOT NULL COMMENT '옵션명',
		`option_value` longtext NOT NULL COMMENT '옵션값',
		
		`reg_date` DATETIME DEFAULT NULL COMMENT '등록일자',
		`mod_date` DATETIME DEFAULT NULL COMMENT '수정일자',
		
		PRIMARY KEY (`id`),
		KEY `clob_options_idx1` (`option_name`)
	) ENGINE=InnoDB COMMENT='clob 옵션';";
	
	
	return $args_;
}
pb_hook_add_filter('pb_install_tables', "_pb_clob_options_install_tables");

?>
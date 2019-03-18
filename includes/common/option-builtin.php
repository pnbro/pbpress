<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_options_install_tables($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `OPTIONS` (

		`ID` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',

		`OPTION_NAME` VARCHAR(100) NOT NULL COMMENT '옵션명',
		`OPTION_VALUE` VARCHAR(500) NOT NULL COMMENT '옵션값',
		
		`REG_DATE` DATETIME DEFAULT NULL COMMENT '등록일자',
		`MOD_DATE` DATETIME DEFAULT NULL COMMENT '수정일자',
		
		PRIMARY KEY (`ID`),
		KEY `LAT_QUEST_MET_IDX1` (`OPTION_NAME`)
	) ENGINE=InnoDB COMMENT='옵션';";
	
	
	return $args_;
}
pb_hook_add_filter('pb_install_tables', "_pb_options_install_tables");

?>
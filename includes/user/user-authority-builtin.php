<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_user_authority_install_table($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `USERS_AUTH` (
		
		`ID` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
		
		`USER_ID` BIGINT(11) NOT NULL COMMENT '사용자ID',
		`AUTH_ID` BIGINT(11) NOT NULL COMMENT '권한ID',
		
		`REG_DATE` datetime DEFAULT NULL COMMENT '등록일자',
		`MOD_DATE` datetime DEFAULT NULL COMMENT '수정일자',

		PRIMARY KEY (`ID`),
		CONSTRAINT `USERS_AUTH_FK1` FOREIGN KEY (`USER_ID`) REFERENCES `USERS` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `USERS_AUTH_FK2` FOREIGN KEY (`AUTH_ID`) REFERENCES `AUTH` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB COMMENT='사용자권한';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "_pb_user_authority_install_table");

?>
<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_user_authority_install_table($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `users_auth` (
		
		`id` BIGINT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
		
		`user_id` BIGINT(11) NOT NULL COMMENT '사용자ID',
		`auth_id` BIGINT(11) NOT NULL COMMENT '권한ID',
		
		`reg_date` datetime DEFAULT NULL COMMENT '등록일자',
		`mod_date` datetime DEFAULT NULL COMMENT '수정일자',

		PRIMARY KEY (`id`),
		CONSTRAINT `users_auth_fk1` FOREIGN KEY (`user_id`) REFERENCES `USERS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `users_auth_fk2` FOREIGN KEY (`auth_id`) REFERENCES `AUTH` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB COMMENT='사용자권한';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "_pb_user_authority_install_table");

?>
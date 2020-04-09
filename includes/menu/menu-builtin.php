<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_authority_task_add_type('manage_menu', array(
	'name' => '메뉴관리',
));
pb_authority_initial_register(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_menu");

?>
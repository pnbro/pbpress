<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_authority_task_add_type('manage_user', array(
	'name' => '사용자관리',
));
pb_authority_initial_register(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_user");

pb_gcode_initial_register('U0001', array(
	'name' => '사용자상태',
	'data' => array(
		'00003' => '정상등록',
		'00009' => '사용불가',
	),
));

?>
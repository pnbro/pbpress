<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_authority_task_add_type('manage_user', array(
	'name' => __('사용자관리'),
));
pb_authority_initial_register(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_user");

abstract class PB_USER_STATUS extends PBConstClass{
	const WAIT = "00001";
	const NORMAL = "00003";
	const UNAVAILABLE = "00009";

	static public function names(){

		global $_pb_user_status_names;
		if(isset($_pb_user_status_names)) return $_pb_user_status_names;

		$_pb_user_status_names = array(
			PB_USER_STATUS::WAIT => __('등록대기'),
			PB_USER_STATUS::NORMAL => __('정상등록'),
			PB_USER_STATUS::UNAVAILABLE => __('사용불가'),
		);

		return $_pb_user_status_names;
	}
}

//사용중지
pb_gcode_initial_register('U0001', array(
	'name' => __('사용자상태'),
	'data' => array(
		PB_USER_STATUS::NORMAL => __('정상등록'),
		PB_USER_STATUS::UNAVAILABLE => __('사용불가'),
	),
));

?>
<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

pb_authority_task_add_type('manage_user', array(
	'name' => __('사용자관리'),
));
pb_authority_initial_register(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_user");

abstract class __PB_USER_STATUS extends PBConstClass{
	const WAIT = "00001";
	const NORMAL = "00003";
	const UNAVAILABLE = "00009";

	static public function names(){

		global $_pb_user_status_names;
		if(isset($_pb_user_status_names)) return $_pb_user_status_names;

		$_pb_user_status_names = array(
			__PB_USER_STATUS::WAIT => __('등록대기'),
			__PB_USER_STATUS::NORMAL => __('정상등록'),
			__PB_USER_STATUS::UNAVAILABLE => __('사용불가'),
		);

		return $_pb_user_status_names;
	}
}

abstract class PB_USER_STATUS extends PBConstClass{

	const WAIT = "00001";
	const NORMAL = "00003";
	const UNAVAILABLE = "00009";

	static $_binded_const = '__PB_USER_STATUS';

	static public function bind($const_){
		static::$_binded_const = $const_;
	}
	static public function binded_const($const_){
		return static::$_binded_const;
	}

	static public function codes(){
		return call_user_func(array(static::$_binded_const, "codes"));
	}
	
	static public function names(){
		return call_user_func(array(static::$_binded_const, "names"));
	}
}

?>
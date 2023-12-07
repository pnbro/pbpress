<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

abstract class PB_FILE_RESOURCE_STATUS extends PBConstClass{
	const TEMP_SAVE = "temp_save";
	const PUBLISHED = "published";
	const UNPUBLISHED = "unpublished";

	static public function names(){

		global $_pb_page_status_names;
		if(isset($_pb_page_status_names)) return $_pb_page_status_names;

		$_pb_page_status_names = array(
			PB_FILE_RESOURCE_STATUS::TEMP_SAVE => __('임시저장'),
			PB_FILE_RESOURCE_STATUS::PUBLISHED => __('공개'),
			PB_FILE_RESOURCE_STATUS::UNPUBLISHED => __('비공개'),
		);

		return $_pb_page_status_names;
	}
}

?>
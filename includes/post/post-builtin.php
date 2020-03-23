<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_POST_STATUS_WRITING', '00001');
define('PB_POST_STATUS_PUBLISHED', '00003');
define('PB_POST_STATUS_UNPUBLISHED', '00009');

function pb_post_initialize_gcode_list($gcode_list_){

	$gcode_list_['PST01'] = array(
		'name' => '글등록상태',
		'data' => array(
			PB_POST_STATUS_WRITING => "작성중",
			PB_POST_STATUS_PUBLISHED => "공개",
			PB_POST_STATUS_UNPUBLISHED => "비공개",
		),
	);

	return $gcode_list_;
}
pb_hook_add_filter("pb_intialize_gcode_list", "pb_post_initialize_gcode_list");

?>
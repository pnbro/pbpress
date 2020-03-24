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

pb_hook_add_action('pb_post_inserted', '_pb_post_update_post_short');
pb_hook_add_action('pb_post_updated', '_pb_post_update_post_short');
function _pb_post_update_post_short($id_){
	$post_data_ = pb_post($id_);

	$post_short_ = strip_tags($post_data_['post_html']);
	$post_short_ = pb_hook_apply_filters('pb_post_short', $post_short_, $post_data_);

	if(pb_strlen($post_short_) > 50){
		$post_short_ = pb_substr($post_short_,0, 50);
	}

	global $posts_do;

	$posts_do->update($post_data_['id'], array("post_short" => $post_short_));
}

?>
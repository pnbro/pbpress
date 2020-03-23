<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


function _pb_post_hook_for_rewrite_handler(){
	
	$current_rewrite_ = pb_current_rewrite();
	$post_type_ = urldecode(pb_current_slug());

	$post_data_ = null;
	$post_data_ = pb_post_by_slug($post_type_, $current_slug_);

	if(!isset($post_data_)){
		return new PBError(404, "글을 찾을 수 없습니다.", "404");
	}

	global $pbpost, $pbpost_meta_map;
	$pbpost = $post_data_;

	if($pbpost['status'] !== PB_POST_STATUS_PUBLISHED && ((int)$pbpost['wrt_id']) !== (int)pb_current_user_id()){
		return new PBError(404, "글을 찾을 수 없습니다.", "404");
	}

	if($pbpost['id'] === pb_front_post_id()){
		pb_redirect(pb_home_url());
		pb_end();
	}

	$pbpost_meta_map = pb_post_meta_map($pbpost['id']);

	$post_path_ = pb_current_theme_path()."post.php";

	if(!file_exists($post_path_)){
		$post_path_ = PB_DOCUMENT_PATH . 'includes/post/views/post.php';
	}

	pb_rewrite_register(urlencode($current_slug_), array(
		'post' => $post_path_,
	));		
}
pb_hook_add_action("pb_started", "_pb_post_hook_for_rewrite_handler");


pb_hook_add_action('pb_init', "_pb_post_types_to_rewrite_handler");
function _pb_post_types_to_rewrite(){
	$post_types_ = pb_post_types();

	foreach($post_types_ as $key_ => $type_data_){
		pb_rewrite_register($key_, array(
			'rewrite_handler' => '_pb_post_hook_for_rewrite_handler'
		));	
	}
}


function _pb_front_post_hook_for_rewrite_handler(){
	if(!pb_is_home()) return;
	
	$post_data_ = pb_front_post();

	if(!isset($post_data_)) return;

	global $pbpost, $pbpost_meta_map;
	$pbpost = $post_data_;

	if($pbpost['status'] !== PB_POST_STATUS_PUBLISHED && ((int)$pbpost['wrt_id']) !== (int)pb_current_user_id()){
		return new PBError(404, "글를 찾을 수 없습니다.", "404");
	}

	$pbpost_meta_map = pb_post_meta_map($pbpost['id']);
}
pb_hook_add_action("pb_started", "_pb_front_post_hook_for_rewrite_handler");

function _pb_front_post_hook_for_index_path($path_){
	global $pbpost, $pbpost_meta_map;
	
	if(!isset($pbpost)) return $path_;

	$post_path_ = pb_current_theme_path()."post.php";

	if(!file_exists($post_path_)){
		$post_path_ = PB_DOCUMENT_PATH . 'includes/post/views/post.php';
	}

	return $post_path_;
}
pb_hook_add_filter("pb_home_path", "_pb_front_post_hook_for_index_path");

?>
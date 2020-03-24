<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


function _pb_post_hook_for_rewrite_handler($rewrite_path_){
	
	$current_rewrite_ = pb_current_rewrite();
	$post_type_ = urldecode(pb_current_slug());

	if(count($rewrite_path_) < 2){
		return new PBError(404, "요청 글이 없습니다.", "404");
	}

	$current_slug_ = urldecode($rewrite_path_[1]);

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

	$pbpost_meta_map = pb_post_meta_map($pbpost['id']);

	$post_path_ = pb_current_theme_path()."post-{$pbpost['type']}.php";

	if(!file_exists($post_path_)){
		$post_path_ = pb_current_theme_path()."post.php";
	}

	if(!file_exists($post_path_)){
		$post_path_ = PB_DOCUMENT_PATH . 'includes/post/views/post.php';
	}

	pb_hook_do_action('pb_post_setup', $pbpost, $pbpost_meta_map);

	return $post_path_;
}


pb_hook_add_action('pb_init', "_pb_post_types_to_rewrite");
function _pb_post_types_to_rewrite(){
	$post_types_ = pb_post_types();

	foreach($post_types_ as $key_ => $type_data_){
		pb_rewrite_register($key_, array(
			'rewrite_handler' => '_pb_post_hook_for_rewrite_handler'
		));	
	}
}

?>
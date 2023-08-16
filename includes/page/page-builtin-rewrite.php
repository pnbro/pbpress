<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_page_hook_for_rewrite_handler(){
	if(pb_is_home()) return;

	$current_rewrite_ = pb_current_rewrite();
	$current_slug_ = urldecode(pb_current_slug());
	$page_data_ = null;

	$page_data_ = pb_page_by_slug($current_slug_);

	if(!isset($page_data_)) return;

	global $pbpage, $pbpage_meta_map;
	$pbpage = $page_data_;

	if($pbpage['status'] !== PB_PAGE_STATUS::PUBLISHED && ((int)$pbpage['wrt_id']) != (int)pb_current_user_id()){
		return new PBError(404, __("페이지를 찾을 수 없습니다."), __("404"));
	}

	if($pbpage['id'] === pb_front_page_id()){
		pb_redirect(pb_home_url());
		pb_end();
	}

	$pbpage_meta_map = pb_page_meta_map($pbpage['id']);

	$page_path_ = pb_current_theme_path()."page.php";

	if(!file_exists($page_path_)){
		$page_path_ = PB_DOCUMENT_PATH . 'includes/page/views/page.php';
	}

	pb_rewrite_register(urlencode($current_slug_), array(
		'page' => $page_path_,
	));		
}
pb_hook_add_action("pb_started", "_pb_page_hook_for_rewrite_handler");

function _pb_front_page_hook_for_rewrite_handler(){
	if(!pb_is_home()) return;
	
	$page_data_ = pb_front_page();

	if(!isset($page_data_)) return;

	global $pbpage, $pbpage_meta_map;
	$pbpage = $page_data_;

	if($pbpage['status'] !== PB_PAGE_STATUS::PUBLISHED 
		&& ((int)$pbpage['wrt_id']) !== (int)pb_current_user_id()
		&& !pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		return new PBError(404, __("페이지를 찾을 수 없습니다."), __("404"));
	}

	$pbpage_meta_map = pb_page_meta_map($pbpage['id']);
}
pb_hook_add_action("pb_started", "_pb_front_page_hook_for_rewrite_handler");

function _pb_front_page_hook_for_index_path($path_){
	global $pbpage, $pbpage_meta_map;
	
	if(!isset($pbpage)) return $path_;

	$page_path_ = pb_current_theme_path()."page.php";

	if(!file_exists($page_path_)){
		$page_path_ = PB_DOCUMENT_PATH . 'includes/page/views/page.php';
	}

	return $page_path_;
}
pb_hook_add_filter("pb_home_path", "_pb_front_page_hook_for_index_path");

?>
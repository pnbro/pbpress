<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_page_hook_for_rewrite_handler(){
	$current_rewrite_ = pb_current_rewrite();

	if(isset($current_rewrite_)) return;

	$slug_ = urldecode(pb_current_slug());
	$page_data_ = pb_page_by_slug($slug_);

	if(!isset($page_data_)) return;

	global $pbpage, $pbpage_meta_map;
	$pbpage = $page_data_;

	if($pbpage['status'] !== PB_PAGE_STATUS_PUBLISHED && ((int)$pbpage['wrt_id']) !== (int)pb_current_user_id()){
		return new PBError(404, "페이지를 찾을 수 없습니다.", "404");
	}

	$pbpage_meta_map = pb_page_meta_map($pbpage['id']);

	$page_path_ = null;

	if(file_exists(pb_current_theme_path()."page.php")){
		$page_path_ = pb_current_theme_path()."page.php";
	}else{
		$page_path_ = PB_DOCUMENT_PATH . 'includes/page/views/page.php';
	}

	pb_rewrite_register(urlencode($slug_), array(
		'page' => $page_path_,
	));
}
pb_hook_add_action("pb_started", "_pb_page_hook_for_rewrite_handler");

?>
<?php
/**
 * 저장서식 엘리먼트 피커 확장
 * 요소 추가 창에 저장서식 리스트 주입
 */

if(!defined('PB_DOCUMENT_PATH')){ die('-1'); }

/**
 * 저장서식 리스트 로드 (엘리먼트 피커용 AJAX)
 */
pb_add_ajax('admin-page-builder-load-saved-layouts', function(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한이 없습니다."));
	}

	global $pb_saved_layouts_do;
	$list_ = $pb_saved_layouts_do->statement()
		->select("reg_date DESC");

	pb_ajax_success(array('list' => $list_));
});


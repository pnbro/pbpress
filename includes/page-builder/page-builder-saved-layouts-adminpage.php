<?php
/**
 * 저장서식 관리자 페이지 (Controller)
 */

if(!defined('PB_DOCUMENT_PATH')){ die('-1'); }

// EasyTable 정의 로드
__iinclude(r_path("views.admin/saved-layouts-tables.php"));

// ============================================================
// 관리자 메뉴 등록
// ============================================================
pb_hook_add_filter('pb_adminpage_list', function($results_){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")) return $results_;

	$results_['manage-saved-layouts'] = array(
		'name' => __('저장서식 관리'),
		'type' => 'menu',
		'directory' => 'manage-page',
		'sort' => 10,
		'rewrite_handler' => function(){
			$path_ = pb_rewrite_path();
			if(count($path_) <= 2) return r_path("views.admin/saved-layouts-list.php");

			switch($path_[2]){
				case "edit":
					global $pb_layout_data;
					$id_ = isset($path_[3]) ? intval($path_[3]) : 0;
					$pb_layout_data = pb_saved_layout($id_);
					return r_path("views.admin/saved-layouts-edit.php");
				case "add":
					return r_path("views.admin/saved-layouts-edit.php");
			}
			pb_redirect_404(); pb_end();
		},
	);

	return $results_;
});

// ============================================================
// AJAX 핸들러
// ============================================================

// 목록 조회
pb_add_ajax('admin-saved-layouts-load-list', function(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한이 없습니다."));
	}

	$keyword_ = _POST('keyword');
	$page_index_ = intval(_POST('page_index'));
	$per_page_ = 20;

	global $pb_saved_layouts_do;
	$statement_ = $pb_saved_layouts_do->statement();

	if(strlen($keyword_)){
		$statement_->add_search_condition(array('title', 'slug', 'category'), $keyword_);
	}

	$list_ = $statement_->select("reg_date DESC", array($page_index_ * $per_page_, $per_page_));
	$count_ = $statement_->count();

	pb_ajax_success(array('list' => $list_, 'count' => $count_));
});

// 서식 삭제
pb_add_ajax('admin-saved-layouts-delete', function(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한이 없습니다."));
	}

	pb_check_ajax_referer('admin-saved-layouts-delete');
	$id_ = intval(_POST('id'));
	if(!$id_) pb_ajax_error(__("서식 ID가 필요합니다."));

	global $pb_saved_layouts_do;
	$pb_saved_layouts_do->delete($id_);
	pb_ajax_success();
});

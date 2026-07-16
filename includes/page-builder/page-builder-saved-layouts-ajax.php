<?php
/**
 * 저장서식 AJAX 핸들러
 */

if(!defined('PB_DOCUMENT_PATH')){ die('-1'); }

/**
 * 신규 저장서식 등록
 */
pb_add_ajax('admin-page-builder-save-layout', function(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한이 없습니다."));
	}

	$title_ = _POST('title');
	$category_ = _POST('category');
	$layout_json_ = _POST('layout_json');

	if(!strlen($title_)) pb_ajax_error(__("서식 이름을 입력해주세요."));
	if(!strlen($layout_json_)) pb_ajax_error(__("저장할 데이터가 없습니다."));

	json_decode($layout_json_, true);
	if(json_last_error() !== JSON_ERROR_NONE){
		pb_ajax_error(__("잘못된 JSON 형식입니다."));
	}

	global $pb_saved_layouts_do;

	// 슬러그 자동 생성 (한글 제목일 경우 랜덤 문자열 조합)
	$slug_ = pb_slugify($title_);
	if(!strlen($slug_)) $slug_ = "layout-".pb_random_string(5);

	$result_ = $pb_saved_layouts_do->insert(array(
		'title' => $title_,
		'slug' => $slug_,
		'category' => $category_,
		'layout_json' => $layout_json_,
		'reg_date' => pb_current_time()
	));

	if(pb_is_error($result_)){
		pb_ajax_error($result_->get_error_message());
	}

	pb_ajax_success(array('id' => intval($result_)));
});

/**
 * 저장서식 수정
 */
pb_add_ajax('admin-page-builder-update-layout', function(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한이 없습니다."));
	}

	$id_ = intval(_POST('id'));
	$title_ = _POST('title');
	$category_ = _POST('category');
	$layout_json_ = _POST('layout_json');

	if(!$id_) pb_ajax_error(__("서식 ID가 필요합니다."));
	if(!strlen($title_)) pb_ajax_error(__("서식 이름을 입력해주세요."));

	global $pb_saved_layouts_do;

	$update_ = array(
		'title' => $title_,
		'category' => $category_,
		'mod_date' => pb_current_time(),
	);

	if(strlen($layout_json_)){
		json_decode($layout_json_, true);
		if(json_last_error() !== JSON_ERROR_NONE){
			pb_ajax_error(__("잘못된 JSON 형식입니다."));
		}
		$update_['layout_json'] = $layout_json_;
	}

	$result_ = $pb_saved_layouts_do->update($id_, $update_);

	if(pb_is_error($result_)){
		pb_ajax_error($result_->get_error_message());
	}

	pb_ajax_success(array('id' => $id_));
});

/**
 * 저장서식 JSON 데이터 조회 (피커 복사모드용)
 */
pb_add_ajax('admin-page-builder-get-layout-json', function(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_page")){
		pb_ajax_error(__("권한이 없습니다."));
	}

	$id_ = intval(_POST('id'));
	if(!$id_) pb_ajax_error(__("서식 ID가 필요합니다."));

	$layout_ = pb_saved_layout($id_);
	if(!$layout_) pb_ajax_error(__("서식을 찾을 수 없습니다."));

	pb_ajax_success(array('layout_json' => $layout_['layout_json']));
});

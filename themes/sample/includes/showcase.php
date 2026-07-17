<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan002 part009 — 파일·이미지 갤러리 & AJAX 인터랙션 데모
 * 신규 AJAX 핸들러 전용 파일 (기존 includes/ajax-test.php 는 편집하지 않음, 재사용만 함).
 * functions.php 통합 시 아래 1줄 추가 필요:
 *   include pb_current_theme_path()."includes/showcase.php";
 * ============================================================ */

/* ------------------------------------------------------------
 * 무한스크롤 데모 — 인터랙션 카탈로그(part009 §4)에서 사용.
 * devplan004 part002 — 더미 42건 for-loop를 제거하고 실제
 * sample_guestbook 테이블(includes/demo-backend.php)을 offset/limit
 * 페이징으로 조회한다. total도 실제 count(). 렌더 필드는 기존
 * items(id/title/desc) 구조를 유지하되 실 컬럼(writer/content/reg_date)도
 * 함께 노출한다.
 * SampleUI.infiniteScroll({el, action, limit, params, listEl, renderItem}) 이 이 액션을 호출한다.
 * ------------------------------------------------------------ */
pb_add_ajax('sample-theme-showcase-infinite-demo', "_sample_theme_showcase_infinite_demo");

function _sample_theme_showcase_infinite_demo(){
	sample_guestbook_ensure_seed();

	$offset_ = (int)_REQUEST('offset', 0, PB_PARAM_INT);
	$offset_ = max(0, $offset_);

	$limit_ = (int)_REQUEST('limit', 10, PB_PARAM_INT);
	$limit_ = max(1, min(50, $limit_));

	$do_ = sample_guestbook_do();
	$total_ = $do_->statement()->count();
	$rows_ = $do_->statement()->select("id DESC", array($offset_, $limit_));

	$items_ = array();
	foreach($rows_ as $row_){
		$items_[] = array(
			'id' => (int)$row_['id'],
			'title' => $row_['writer'],
			'desc' => $row_['content'],
			'writer' => $row_['writer'],
			'content' => $row_['content'],
			'reg_date' => $row_['reg_date'],
		);
	}

	pb_ajax_success(array(
		'items' => $items_,
		'total' => (int)$total_,
		'offset' => $offset_,
		'limit' => $limit_,
	));
}

/* ------------------------------------------------------------
 * AJAX 에러 처리 데모 — 항상 실패 응답을 반환해 클라이언트 에러 토스트/처리 경로를 시연한다.
 * (미등록 액션을 호출해 빈 응답을 유도하는 방식은 콘솔 파싱 에러를 유발할 수 있어 사용하지 않음.)
 * ------------------------------------------------------------ */
pb_add_ajax('sample-theme-showcase-error-demo', "_sample_theme_showcase_error_demo");

function _sample_theme_showcase_error_demo(){
	pb_ajax_error(__("의도된 에러 데모", PB_THEME_DOMAIN), __("AJAX 에러 처리 흐름을 시연하기 위해 서버가 의도적으로 실패 응답을 반환했습니다.", PB_THEME_DOMAIN));
}

?>

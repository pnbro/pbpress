<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan004 part002 — 고정 문자열 응답을 실 DB 조회로 교체.
 * 액션명(sample-theme-ajax-test)은 호출부 회귀 방지를 위해 그대로 유지.
 * includes/demo-backend.php 의 sample_guestbook_do()/sample_guestbook_ensure_seed()
 * 를 재사용해 실제 sample_guestbook 테이블에서 총건수 + 최근 3건을 조회한다.
 * ============================================================ */
pb_add_ajax('sample-theme-ajax-test', "_sample_theme_ajax_test");

function _sample_theme_ajax_test(){
	sample_guestbook_ensure_seed();

	$do_ = sample_guestbook_do();
	$total_ = $do_->statement()->count();
	$recent_rows_ = $do_->statement()->select("id DESC", array(0, 3));

	$recent_ = array();
	foreach($recent_rows_ as $row_){
		$recent_[] = array(
			'id' => (int)$row_['id'],
			'writer' => $row_['writer'],
			'content' => $row_['content'],
			'reg_date' => $row_['reg_date'],
		);
	}

	return pb_ajax_success(array(
		'total' => (int)$total_,
		'recent' => $recent_,
	));
}

?>
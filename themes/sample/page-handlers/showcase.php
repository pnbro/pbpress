<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* ============================================================
 * devplan004 part004 — 코어 API 라이브 카탈로그
 * Rewrite: /showcase (public)
 *   - /showcase, /showcase/ajax-demo : 코어 API 라이브 카탈로그(DO·훅·rewrite·
 *                                       AJAX+CSRF·페이지빌더·권한) + 축소된 UI 목업 부속
 *   (devplan004 part006 후속: /showcase/gallery 이미지 갤러리 데모는 제거됨)
 * functions.php 통합 시 아래 1줄 추가 필요(본 파일은 신규이며 include만 되면 동작):
 *   include pb_current_theme_path()."page-handlers/showcase.php";
 * ============================================================ */

pb_rewrite_register('showcase', array(
	'title' => "코어 API 라이브 카탈로그",
	'public' => true,
	'rewrite_handler' => "_sample_theme_showcase_rewrite_handler",
));

function _sample_theme_showcase_rewrite_handler($rewrite_path_, $rewrite_data_){

	if(count($rewrite_path_) > 2){
		return new PBError(404, __("잘못된 접근"), __("요청값이 잘못되었습니다."));
	}

	$sub_view_ = (isset($rewrite_path_[1]) && strlen($rewrite_path_[1])) ? urldecode($rewrite_path_[1]) : "ajax-demo";

	$view_map_ = array(
		"ajax-demo" => "ajax-demo.php",
	);

	if(!isset($view_map_[$sub_view_])){
		return new PBError(404, __("페이지를 찾을 수 없습니다."), "404");
	}

	$view_path_ = pb_current_theme_path()."pages/showcase/".$view_map_[$sub_view_];

	if(!file_exists($view_path_)){
		return new PBError(404, __("페이지를 찾을 수 없습니다."), "404");
	}

	return $view_path_;
}

?>

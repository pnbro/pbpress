<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* devplan002 part005 — 찜/북마크 목록
 * 코어에 찜/북마크 데이터소스가 없어(search_api 확인 결과 0건) 뷰에서는
 * 빈 배열 기준 빈상태를 시연한다. 실데이터 연동 시 이 서브핸들러에서
 * 조회 로직을 추가하고 뷰(pages/mypage/bookmarks.php)에 데이터를 전달하면 된다.
 */
pb_mypage_add_menu('bookmarks', array(
	'title' => __('찜 목록', PB_THEME_DOMAIN),
	'page' => PB_THEME_PATH.'pages/mypage/bookmarks.php',
), 106);

?>

<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* devplan002 part005 — 마이페이지 홈(대시보드) */
pb_mypage_add_menu('home', array(
	'title' => __('홈', PB_THEME_DOMAIN),
	'page' => PB_THEME_PATH.'pages/mypage/home.php',
), 100);

?>

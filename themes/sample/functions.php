<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1');
}

define('PB_THEME_VERSION', "1.0.0");
define('PB_THEME_PATH', pb_current_theme_path());
define('PB_THEME_URL', pb_current_theme_url());

pb_rewrite_register('other-page', array(
	'title' => '다른페이지',
	'public' => true,
	'page' => pb_current_theme_path()."other-page.php",
));


pb_hook_add_filter('pb_post_types', function($results_){
	$results_['blog'] = array(
		'name' => '블로그',
		'label' => array(
			'list' => "블로그내역",
			'add' => "블로그추가",
			'update' => "블로그수정",
			'delete' => "블로그삭제",
			'button_add' => "블로그추가",
			'button_update' => "블로그수정",
			'before_delete' => "해당 블로그을 삭제합니다. 계속하시겠습니까?",
			'after_delete' => "블로그가 삭제되었습니다.",
			'no_results' => "검색된 글이 없습니다.",
		),
		'adminpage_sort' => 10,
	);

	return $results_;
});

include pb_current_theme_path()."includes/menu-render.php";
include pb_current_theme_path()."includes/manage-site.php";
include pb_current_theme_path()."includes/ajax-test.php";
include pb_current_theme_path()."includes/page-builder-element-test.php";

?>
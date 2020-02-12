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

include pb_current_theme_path()."includes/menu-render.php";
include pb_current_theme_path()."includes/manage-site.php";
include pb_current_theme_path()."includes/ajax-test.php";
include pb_current_theme_path()."includes/page-builder-element-test.php";

?>
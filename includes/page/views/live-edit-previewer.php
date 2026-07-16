<?php
$preview_html_ = pb_session_get('_preview_page_html');

if(!strlen($preview_html_)){
	echo __("미리보기 데이터가 없습니다.");
	return;
}

pb_session_remove('_preview_page_html');

pb_theme_header();
echo pb_hook_apply_filters('pb_page_html', $preview_html_, array());
pb_theme_footer();
?>
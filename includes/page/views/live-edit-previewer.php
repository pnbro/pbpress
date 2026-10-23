<?php
if(!defined('PB_DOCUMENT_PATH')) die('-1');
header('Cache-Control: private, no-store');
header('X-Robots-Tag: noindex, nofollow');

$preview_token_ = _GET('preview_id');
$preview_ = _pb_page_preview_snapshot($preview_token_);
$preview_html_ = $preview_ ? $preview_['page_html'] : null;
if(!is_string($preview_html_) || !strlen($preview_html_)){
	http_response_code(410);
	?><!doctype html><html lang="ko"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=__('미리보기 만료')?></title><body style="font-family:sans-serif;padding:32px;color:#283743"><h1 style="font-size:20px"><?=__('미리보기 보관 시간이 지났습니다.')?></h1><p><?=__('편집 화면으로 돌아가 미리보기를 다시 눌러 주세요.')?></p><a href="<?=htmlspecialchars(pb_admin_url('manage-page'))?>"><?=__('페이지 목록으로')?></a></body></html><?php
	return;
}

global $pbpage, $pbpage_meta_map;
$pbpage = $preview_ && !empty($preview_['page_id']) ? pb_page($preview_['page_id']) : null;
if(!is_array($pbpage)) $pbpage = array('id' => null, 'page_title' => '', 'slug' => '', 'status' => PB_PAGE_STATUS::UNPUBLISHED);
$pbpage['page_html'] = $preview_html_;
if(isset($preview_['page_title'])) $pbpage['page_title'] = $preview_['page_title'];
if(isset($preview_['page_slug'])) $pbpage['slug'] = $preview_['page_slug'];
$pbpage_meta_map = $pbpage['id'] ? pb_page_meta_map($pbpage['id']) : array();
$header_name_ = null;
$footer_name_ = null;
$setup_path_ = pb_current_theme_path().'page-setup.php';
if(file_exists($setup_path_)) include($setup_path_);
pb_theme_header($header_name_);
echo pb_page_html();
pb_theme_footer($footer_name_);

<?php
global $pbpage, $pbpage_meta_map;

$header_name_ = null;
$footer_name_ = null;
include(pb_current_theme_path() . "page-setup.php");

pb_theme_header($header_name_);
echo pb_page_html();
pb_theme_footer($footer_name_);
?>
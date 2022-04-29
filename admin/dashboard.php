<?php
	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}
	
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/dashboard.css">
<div class="dashboard-container">
	<div class="logo-frame">
		<img src="<?=PB_LIBRARY_URL?>img/dashboard-logo.png" class="logo">
	</div>
	<h1 class="title"><?=__('PBPress 관리자페이지')?><br/>
	<small><?=__('사이드메뉴를 클릭하여 각 관리화면으로 접근할 수 있습니다.')?></small></h1>
</div>
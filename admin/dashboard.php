<?php
	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}
	
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/dashboard.css">
<?php
	$filebase_probe_url_ = pb_filebase_route_probe_url();
	if(strlen($filebase_probe_url_ ?? "")){
?>
<div class="dashboard-filebase-warning alert alert-danger" id="pb-dashboard-filebase-warning" style="display:none;">
	<strong><?=__('업로드 파일 보호 규칙이 웹서버에 적용되지 않았습니다.')?></strong>
	<p><?=__('첨부파일이 로그인·권한 확인 없이 열릴 수 있습니다. Apache는 .htaccess(mod_rewrite, AllowOverride)를, nginx는 nginx.conf.sample의 uploads 규칙이 서버 설정에 들어갔는지 확인하세요.')?></p>
</div>
<script type="text/javascript">
jQuery(function($){
	//PBPress 를 거친 /uploads 응답에는 X-PB-Filebase 헤더가 붙는다. 헤더 없이 200 이면 웹서버가 파일을 직접 내려준 것
	$.ajax({
		url : <?=json_encode($filebase_probe_url_)?>,
		type : "GET",
		cache : false,
		dataType : "text",
		complete : function(xhr_){
			if(xhr_.status === 200 && !xhr_.getResponseHeader("X-PB-Filebase")){
				$("#pb-dashboard-filebase-warning").show();
			}
		}
	});
});
</script>
<?php } ?>
<div class="dashboard-container">
	<div class="logo-frame">
		<img src="<?=PB_LIBRARY_URL?>img/dashboard-logo.png" class="logo">
	</div>
	<h1 class="title"><?=__('PBPress 관리자페이지')?><br/>
	<small><?=__('사이드메뉴를 클릭하여 각 관리화면으로 접근할 수 있습니다.')?></small></h1>
</div>
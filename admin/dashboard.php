<?php
	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}
	
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/dashboard.css">
<?php if(pb_filebase_public_storage_warning()){ ?>
<div class="dashboard-filebase-warning alert alert-danger">
	<strong><?=__('업로드 파일이 공개 폴더에 저장되고 있습니다.')?></strong>
	<p><?=__('nginx에서는 첨부파일이 로그인·권한 확인 없이 열릴 수 있습니다. pb-config.php에 PB_FILE_UPLOAD_STORAGE_PATH를 웹 폴더 밖 경로로 지정하세요.')?></p>
</div>
<?php }else{
	$filebase_probe_url_ = pb_filebase_route_probe_url();
	if(strlen($filebase_probe_url_ ?? "")){
?>
<div class="dashboard-filebase-warning alert alert-danger" id="pb-dashboard-filebase-warning" style="display:none;">
	<strong><?=__('웹서버가 업로드 파일을 PBPress를 거치지 않고 내려주고 있습니다.')?></strong>
	<p><?=__('uploads 폴더의 첨부파일이 로그인·권한 확인 없이 열릴 수 있습니다. pb-config.php의 PB_FILE_UPLOAD_STORAGE_PATH를 웹 폴더 밖 경로로 지정하고 기존 uploads 파일을 옮기거나, Apache라면 .htaccess(mod_rewrite, AllowOverride)를 확인하세요.')?></p>
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
<?php }
} ?>
<div class="dashboard-container">
	<div class="logo-frame">
		<img src="<?=PB_LIBRARY_URL?>img/dashboard-logo.png" class="logo">
	</div>
	<h1 class="title"><?=__('PBPress 관리자페이지')?><br/>
	<small><?=__('사이드메뉴를 클릭하여 각 관리화면으로 접근할 수 있습니다.')?></small></h1>
</div>
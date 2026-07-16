<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $pbpage, $pbpage_meta_map;
$is_new_ = !isset($pbpage);

if($is_new_){
	$pbpage = array(
		'id' => null,
		'page_title' => null,
		'page_html' => null,
		'status' => PB_PAGE_STATUS::PUBLISHED,
		'slug' => null,
	);
	$pbpage_meta_map = array();
}

$element_map_ = pb_page_builder_elements();
$builder_data_ = pb_page_builder_parse($pbpage['page_html']);
if(pb_is_error($builder_data_)){
	$builder_data_ = array('settings' => array('style'=>'','script'=>''), 'elementcontent' => array());
}
$settings_ = $builder_data_['settings'];

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?=isset($pbpage['page_title']) ? htmlspecialchars($pbpage['page_title']) : __('새 페이지')?> - <?=__('라이브에디터')?></title>
	<?php
	// pb_admin_head()와 동일한 리소스 로드 + 관리자 훅 실행
	$pbvar_ = pb_hook_apply_filters('pb-admin-head-pbvar', array());
	?>
	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pb-admin.css?v=<?=PB_SCRIPT_VERSION?>">
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/jquery.js?v=<?=PB_SCRIPT_VERSION?>"></script>
	<script type="text/javascript">window.PBVAR = <?=json_encode($pbvar_)?>;</script>
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>comp-lib/all-admin.js?v=<?=PB_SCRIPT_VERSION?>"></script>
	<?php
	// pb_admin_head 훅 실행: pblang(다국어), crypt(암호화키) 등 관리자 head 스크립트 로드
	pb_hook_do_action("pb_admin_head");
	?>
<script type="text/javascript">
window.pb_page_builder_version = "<?=PB_PAGE_BUILDER_VERSION?>";
window.pbpage_builder_element_map = <?=json_encode($element_map_)?>;
window.pb_live_editor_config = {
	page_id: <?=json_encode($pbpage['id'])?>,
	page_title: <?=json_encode($pbpage['page_title'])?>,
	page_status: <?=json_encode($pbpage['status'])?>,
	page_slug: <?=json_encode(isset($pbpage['slug']) ? $pbpage['slug'] : null)?>,
	builder_json: <?=json_encode($builder_data_)?>,
	element_map: <?=json_encode($element_map_)?>,
	is_new: <?=$is_new_ ? 'true' : 'false'?>,
	admin_url: <?=json_encode(pb_admin_url(''))?>,
	home_url: <?=json_encode(pb_home_url(''))?>
};
</script>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/editor/pb.page-builder.css?version=<?=PB_PAGE_BUILDER_VERSION?>">
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/page-builder/editor/pb.page-live-builder.css?version=<?=PB_PAGE_BUILDER_VERSION?>">
</head>
<body class="pb-live-editor-body">

<!-- ===== 전체 래퍼 ===== -->
<div class="pb-live-editor" id="pb-live-editor">

	<!-- ===== ① 상단 네비바 ===== -->
	<div class="live-editor-navbar" data-live-editor-navbar>
		<div class="navbar-left">
			<img src="<?=PB_LIBRARY_URL?>img/page-builder/icon.png" class="navbar-logo" alt="">
			<a href="<?=pb_admin_url('manage-page')?>" class="navbar-back-btn" title="<?=__('페이지 목록')?>">
				<i class="material-icons">arrow_back</i>
			</a>
			<span class="navbar-page-title" data-page-title><?=htmlspecialchars($pbpage['page_title'] ?: __('새 페이지'))?></span>
		</div>
		<div class="navbar-center">
			<div class="device-toggle" data-device-toggle>
				<button type="button" class="device-btn active" data-device="desktop" title="<?=__('데스크톱')?>"><i class="material-icons">desktop_windows</i></button>
				<button type="button" class="device-btn" data-device="tablet" title="<?=__('태블릿')?>"><i class="material-icons">tablet_mac</i></button>
				<button type="button" class="device-btn" data-device="mobile" title="<?=__('모바일')?>"><i class="material-icons">phone_iphone</i></button>
			</div>
		</div>
		<div class="navbar-right">
			<button type="button" class="nav-btn undo-btn" data-undo-btn disabled title="<?=__('실행취소')?>"><i class="material-icons">undo</i></button>
			<button type="button" class="nav-btn redo-btn" data-redo-btn disabled title="<?=__('다시실행')?>"><i class="material-icons">redo</i></button>
			<button type="button" class="nav-btn preview-btn" data-preview-btn title="<?=__('미리보기')?>"><i class="material-icons">visibility</i> <?=__('미리보기')?></button>
			<select class="nav-status-select" data-page-status><?=PB_PAGE_STATUS::make_options($pbpage['status'])?></select>
			<button type="button" class="nav-btn save-btn" data-save-btn title="<?=__('저장')?>"><i class="material-icons">save</i> <?=__('저장')?></button>
		</div>
	</div>

	<!-- ===== ② 좌측 아이콘 패널 ===== -->
	<div class="live-editor-icon-panel" data-live-editor-icon-panel>
		<button type="button" class="panel-btn active" data-panel="elements" title="<?=__('요소추가')?>">
			<i class="material-icons">add_circle</i>
			<span><?=__('요소')?></span>
		</button>
		<button type="button" class="panel-btn" data-panel="layers" title="<?=__('레이어')?>">
			<i class="material-icons">layers</i>
			<span><?=__('레이어')?></span>
		</button>
		<button type="button" class="panel-btn" data-panel="saved-layouts" title="<?=__('저장서식')?>">
			<i class="material-icons">bookmark</i>
			<span><?=__('저장서식')?></span>
		</button>
		<div class="panel-divider"></div>
		<button type="button" class="panel-btn" data-panel="settings" title="<?=__('설정')?>">
			<i class="material-icons">tune</i>
			<span><?=__('설정')?></span>
		</button>
		<div class="panel-divider"></div>
		<button type="button" class="panel-btn" data-panel="history" title="<?=__('히스토리')?>">
			<i class="material-icons">history</i>
			<span><?=__('히스토리')?></span>
		</button>
	</div>

	<!-- ===== ③ 사이드패널 ===== -->
	<div class="live-editor-side-panel" data-live-editor-side-panel>
		<div class="side-panel-header" data-side-panel-header>
			<h3 class="side-panel-title" data-side-panel-title></h3>
			<button type="button" class="side-panel-close-btn" data-side-panel-close><i class="material-icons">close</i></button>
		</div>
		<div class="side-panel-body" data-side-panel-body>
			<!-- 패널 콘텐츠가 동적으로 로드됨 -->
		</div>
		<div class="side-panel-footer" data-side-panel-footer style="display:none;">
			<!-- 적용/취소 버튼 영역 (편집 모드에서 사용) -->
		</div>
	</div>

	<!-- ===== ④ 캔버스 영역 ===== -->
	<div class="live-editor-canvas-area" data-live-editor-canvas-area>
		<div class="canvas-device-frame" data-canvas-device-frame>
			<iframe id="live-editor-canvas-iframe" data-live-editor-canvas-iframe frameborder="0"></iframe>
		</div>
		<!-- 오버레이 레이어 (iframe 위에 절대위치) -->
		<div class="canvas-overlay-layer" data-canvas-overlay-layer></div>
	</div>

	<!-- ===== ⑤ 하단 상태바 ===== -->
	<div class="live-editor-status-bar" data-live-editor-status-bar>
		<div class="status-breadcrumb" data-status-breadcrumb>
			<span class="breadcrumb-item">Body</span>
		</div>
		<div class="status-zoom" data-status-zoom>
			<button type="button" class="zoom-btn" data-zoom-out><i class="material-icons">remove</i></button>
			<span class="zoom-value" data-zoom-value>100%</span>
			<button type="button" class="zoom-btn" data-zoom-in><i class="material-icons">add</i></button>
			<button type="button" class="zoom-btn" data-zoom-fit title="<?=__('화면맞춤')?>"><i class="material-icons">fit_screen</i></button>
		</div>
	</div>

</div>

<!-- ===== JS 로드 ===== -->
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/page-builder/editor/pb.page-live-builder.js?version=<?=PB_PAGE_BUILDER_VERSION?>"></script>
<script type="text/javascript">
jQuery(function($){
	window._pb_live_editor = new pb_page_live_builder($("#pb-live-editor"), window.pb_live_editor_config);
});
</script>
<?php
// pb_admin_foot 훅 실행: 공유 모달 등 관리자 footer 스크립트/HTML 로드
pb_hook_do_action("pb_admin_foot");
?>

</body>
</html>

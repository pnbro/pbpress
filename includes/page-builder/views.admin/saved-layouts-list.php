<?php

if(!defined('PB_THEME_PATH')){ die('-1'); }

$saved_layouts_table_ = pb_easytable("admin-saved-layouts-table");

$page_index_ = _GET('page_index', 0, PB_PARAM_INT);
$keyword_ = _GET('keyword');

pb_adminpage_register_back_url();
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/aside-view.css?v=<?=PB_SCRIPT_VERSION?>">
<h3><?=__('저장서식 관리')?></h3>

<form method="GET" class="pb-easytable-group" id="pb-easytable-table-form">

	<div class="pb-easytable-conditions">
		<div class="left-frame">
			<a href="<?=pb_admin_url("manage-saved-layouts/add")?>" class="btn btn-default"><?=__('서식 추가')?></a>
		</div>
		<div class="right-frame">
			<div class="input-group">
				<input type="text" class="form-control" placeholder="<?=__('통합검색')?>" name="keyword" value="<?=$keyword_?>">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default"><?=__('검색하기')?></button>
				</span>
			</div>
		</div>
	</div>

	<?php $saved_layouts_table_->display($page_index_); ?>

</form>

<script type="text/javascript">
function pb_layout_delete(id_){
	PB.confirm({
		title : "<?=__('삭제확인')?>",
		content : "<?=__('해당 서식을 삭제하시겠습니까? 이 서식을 사용중인 페이지에 영향이 있을 수 있습니다.')?>",
		button1 : "<?=__('삭제하기')?>"
	}, function(c_){
		if(!c_) return;
		PB.post("admin-saved-layouts-delete", { id : id_ }, function(r_, j_){
			if(!r_ || !j_.success){ PB.alert_error(j_); return; }
			location.reload();
		}, true);
	});
}
</script>

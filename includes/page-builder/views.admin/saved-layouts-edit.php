<?php

if(!defined('PB_THEME_PATH')){ die('-1'); }

global $pb_layout_data;
$is_new_ = !isset($pb_layout_data) || !$pb_layout_data;

if($is_new_){
	$pb_layout_data = array(
		'id' => null,
		'title' => '',
		'slug' => '',
		'category' => '',
		'layout_json' => '',
	);
}
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/aside-view.css?v=<?=PB_SCRIPT_VERSION?>">
<h3><?=($is_new_ ? __("서식 추가") : __("서식 수정"))?> <a class="btn btn-default btn-sm" href="<?=pb_adminpage_back_url("manage-saved-layouts")?>"><?=__('목록으로')?></a></h3>

<form id="pb-saved-layout-edit-form" method="POST">
	<input type="hidden" name="id" value="<?=$pb_layout_data['id']?>">

	<div class="aside-view-frame">
		<div class="col-content">
			<div class="form-group">
				<input type="text" name="title" placeholder="<?=__('서식 제목 입력')?>"
					value="<?=htmlspecialchars($pb_layout_data['title'])?>"
					class="form-control input-lg" required>
			</div>

			<?php
				pb_page_builder($pb_layout_data['layout_json'], array(
					'id' => 'pb-saved-layout-builder',
				));
			?>
		</div>
		<div class="col-control-panel">
			<div class="panel panel-default">
				<div class="panel-heading"><h4 class="panel-title"><?=__('기본정보')?></h4></div>
				<div class="panel-body">
					<div class="form-group">
						<label><?=__('카테고리')?></label>
						<input type="text" name="category" class="form-control"
							placeholder="<?=__('카테고리 입력')?>"
							value="<?=htmlspecialchars($pb_layout_data['category'])?>">
					</div>
					<?php if(!$is_new_){ ?>
					<div class="form-group">
						<label><?=__('슬러그')?></label>
						<p class="form-control-static"><?=htmlspecialchars($pb_layout_data['slug'])?></p>
					</div>
					<?php } ?>
				</div>
				<div class="panel-footer">
					<div class="button-area">
						<div class="col-left">
							<button type="submit" class="btn btn-primary btn-block btn-lg">
								<?=($is_new_ ? __("서식 등록") : __("서식 수정"))?>
							</button>
						</div>
						<?php if(!$is_new_){ ?>
						<div class="col-right">
							<a href="javascript:pb_saved_layout_delete(<?=$pb_layout_data['id']?>)"
							   class="btn btn-block btn-dark delete-btn">
								<i class="icon material-icons">delete_forever</i>
							</a>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

</form>

<script type="text/javascript">
jQuery(document).ready(function($){
	var is_new_ = <?=$is_new_ ? "true" : "false"?>;
	var layout_id_ = <?=$pb_layout_data['id'] ? intval($pb_layout_data['id']) : "null"?>;

	$("#pb-saved-layout-edit-form").submit_handler(function(){
		var form_data_ = $(this).serialize_object();
		var builder_ = $("#pb-saved-layout-builder").pb_page_builder();

		// v4 export_json 우선, 없으면 to_xml 폴백
		var layout_json_ = builder_.export_json ? builder_.export_json() : builder_.to_xml();

		var ajax_action_ = is_new_ ? "admin-page-builder-save-layout" : "admin-page-builder-update-layout";
		var post_data_ = {
			title : form_data_['title'],
			category : form_data_['category'],
			layout_json : layout_json_
		};

		if(!is_new_) post_data_['id'] = layout_id_;

		PB.post(ajax_action_, post_data_, function(r_, j_){
			if(!r_ || !j_.success){ PB.alert_error(j_); return; }

			if(is_new_){
				location.href = "<?=pb_admin_url('manage-saved-layouts/edit/')?>"+j_.id;
			}else{
				PB.alert("<?=__('저장되었습니다.')?>");
			}
		}, true);
	});
});

function pb_saved_layout_delete(id_){
	PB.confirm({
		title : "<?=__('삭제확인')?>",
		content : "<?=__('이 서식을 삭제하시겠습니까?')?>",
		button1 : "<?=__('삭제하기')?>"
	}, function(c_){
		if(!c_) return;
		PB.post("admin-saved-layouts-delete", { id : id_ }, function(r_, j_){
			if(!r_ || !j_.success){ PB.alert_error(j_); return; }
			location.href = "<?=pb_admin_url('manage-saved-layouts')?>";
		}, true);
	});
}
</script>

<?php 	
	
	global $pbpage;
	$is_new_ = !isset($pbpage);

	if($is_new_){
		$pbpage = array(
			'id' => null,
			'page_title' => null,
			'page_html' => null,
			'status' => PB_PAGE_STATUS_WRITING,
			'slug' => null,
		);	
	}

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-page/edit.css">
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/page/manage-page/edit.js"></script>

<h3><?=($is_new_ ? "페이지추가" : "페이지수정")?></h3>

<form id="pb-page-edit-form" method="POST">
	<?php pb_hook_do_action("pb_page_edit_form_before", $pbpage) ?>
	<input type="hidden" name="id" value="<?=$pbpage['id']?>">

	<div class="page-edit-frame">
		<div class="col-content">
			<div class="form-group">
				<input type="text" name="page_title" placeholder="<?=pb_hook_apply_filters('pb_page_edit_form_placeholder', "페이지제목 입력")?>" value="<?=$pbpage['page_title']?>" class="form-control input-lg" required data-error="페이지제목을 입력하세요">
				<?php if(!$is_new_){ ?>
					<p class="page-url-info">
						<a href="<?=pb_home_url($pbpage['slug'])?>" target="_blank"><?=pb_home_url($pbpage['slug'])?></a>
					</p>
					
				<?php }?>
				<div class="help-block with-errors"></div>
				<div class="clearfix"></div>
			</div>

			<?php pb_hook_do_action("pb_page_edit_form_page_content_before", $pbpage)?>
			<div class="form-group">
				<textarea name="page_html" id="pb-page-edit-form-page-html"><?=($pbpage['page_html'])?></textarea>
				<div class="help-block with-errors"></div>
				<div class="clearfix"></div>
			</div>

			<?php pb_hook_do_action("pb_page_edit_form_page_content_after", $pbpage)?>
			<hr>
		</div>
		<div class="col-control-panel">
			
			<?=pb_hook_do_action("pb_page_edit_form_control_panel_before", $pbpage)?>

			<div class="panel panel-default" id="pb-page-edit-form-page-common-panel">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" href="#pb-page-edit-form-page-common-panel-body" aria-expanded="true" aria-controls="collapseOne">기본정보</a>
					</h4>
				</div>
				<div id="pb-page-edit-form-page-common-panel-body" class="panel-collapse collapse in" role="tabpanel">
					<div class="panel-body">
						<div class="form-group">
							<label>페이지상태</label>
							<select class="form-control" name="status" required data-error="상태를 선택하세요">
								<?=pb_gcode_make_options(array("code_id" => "PAG01"), $pbpage['status'])?>
							</select>
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>

						<div class="form-group">
							<label>URL슬러그</label>
							<input type="text" name="slug" class="form-control" placeholder="URL슬러그 입력" value="<?=$pbpage['slug']?>">
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>
						
						<?php if(!$is_new_){ ?>
						<div class="form-group">
							<label>마지막수정일자</label>
							<p class="form-control-static"><?=strlen($pbpage['mod_date_ymdhi']) ? $pbpage['mod_date_ymdhi'] : $pbpage['reg_date_ymdhi']?></p>
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>
						<?php } ?>
	
					</div>
					<div class="panel-footer">
						<div class="button-area">

							<div class="col-left">
								<button type="submit" class="btn btn-primary btn-block btn-lg"><?=($is_new_ ? "페이지추가" : "페이지수정")?></button>
							</div>
							<?php if(!$is_new_){ ?>
								<div class="col-right">
									<a href="javascript:pb_page_edit_form_delete(<?=$pbpage['id']?>)" class="btn btn-block btn-dark delete-btn">
										<i class="icon material-icons">delete_forever</i>
									</a>
								</div>
							<?php } ?>
							
						</div>
					</div>
				</div>
			</div>

			<?=pb_hook_do_action("pb_page_edit_form_control_panel_after", $pbpage)?>
		</div>
	</div>
	<?php pb_hook_do_action("pb_page_edit_form_after", $pbpage) ?>
</form>
<!-- <script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/page/manage-page/edit.js"></script> -->


<script type="text/javascript">
jQuery(document).ready(function(){
	var page_edit_form_ = $("#pb-page-edit-form");

	page_edit_form_.find("[name='page_html']").init_summernote_for_pb({
		minHeight: 400,
		fullscreen : true,
	});

	page_edit_form_.validator();
	page_edit_form_.submit_handler(function(){
		pb_page_edit_form_submit(page_edit_form_.serialize_object());
	});
});
function pb_page_edit_form_submit(page_data_){
	PB.post("edit-page", {
		page_data : page_data_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "페이지수정 중, 에러가 발생했습니다.",
			});
			return;
		}

		document.location = response_json_.redirect_url;

	}, true);
}
function pb_page_edit_form_delete(page_id_){
	PB.confirm({
		title : "삭제확인",
		content : "해당 페이지를 삭제합니다. 계속하시겠습니까?",
		button1 : "삭제하기",
	}, function(c_){
		if(!c_) return;
		_pb_page_edit_form_delete(page_id_);
	});
}

function _pb_page_edit_form_delete(page_id_){
	PB.post("delete-page", {
		page_id : page_id_
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "페이지 삭제 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "삭제완료",
			content : "페이지가 삭제되었습니다. 페이지내역으로 돌아갑니다.",
		}, function(){
			document.location = response_json_.redirect_url;
		});

	}, true);
}
</script>
<?php 	
	
	global $pbpage, $pbpage_meta_map;
	$is_new_ = !isset($pbpage);

	if($is_new_){
		$pbpage = array(
			'id' => null,
			'page_title' => null,
			'page_html' => null,
			'status' => PB_PAGE_STATUS_WRITING,
			'slug' => null,
		);
		$pbpage_meta_map = array();
	}



?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-page/edit.css">
<h3><?=($is_new_ ? "페이지추가" : "페이지수정")?></h3>

<form id="pb-page-edit-form" method="POST">
	<?php pb_hook_do_action("pb_page_edit_form_before", $pbpage) ?>
	<input type="hidden" name="id" value="<?=$pbpage['id']?>">

	<div class="page-edit-frame">
		<div class="col-content">
			<div class="form-group page-title-form-group">
				<input type="text" name="page_title" placeholder="<?=pb_hook_apply_filters('pb_page_edit_form_placeholder', "페이지제목 입력")?>" value="<?=$pbpage['page_title']?>" class="form-control input-lg" required data-error="페이지제목을 입력하세요">

				<div class="url-slug-group <?=$is_new_ ? "only-editing" : ""?>" id="pb-page-edit-form-url-slug-group">
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><?=pb_home_url()?></span>
						<input type="text" name="slug" class="form-control" placeholder="URL슬러그 입력" value="<?=$pbpage['slug']?>" data-original-slug="<?=$pbpage['slug']?>">

						<?php if(!$is_new_){ ?>
						<span class="input-group-btn">
							<button class="btn btn-primary" type="button" data-slug-edit-update-btn>수정</button>
						</span>
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" data-slug-edit-cancel-btn>취소</button>
						</span>
						<?php } ?>
					</div>	
					<?php if(!$is_new_){
						// $page_url_ = pb_page_url($pbpage['id']);
					?>
					<p class="page-url-info">
						<a href="<?=pb_home_url($pbpage['slug'])?>" target="_blank" data-page-link>
							<?=pb_home_url()?><strong class="slug"><?=$pbpage['slug']?></strong>
						</a> <a href="" class="btn btn-sm btn-default" data-slug-edit-btn>수정</a>
					</p>
						
					<?php } ?>
				</div>

				

				<?php if(!$is_new_){
					$page_url_ = pb_page_url($pbpage['id']);
				?>
					
					
				<?php }?>
				<div class="help-block with-errors"></div>
				<div class="clearfix"></div>
			</div>
			

			<?php
				pb_hook_do_action("pb_page_edit_form_page_html_before", $pbpage);
				pb_editor("page_html", $pbpage['page_html'], array(
					"min_height" => 400,
					"id" => "pb-page-html-editor",
					"editor" => isset($pbpage_meta_map['actived_editor_id']) ? $pbpage_meta_map['actived_editor_id'] : null,
				));
				pb_hook_do_action("pb_page_edit_form_page_html_after", $pbpage);
			?>
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
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-page/edit.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function(){
		var url_slug_group_ = $("#pb-page-edit-form-url-slug-group");
		var url_slug_input_ = url_slug_group_.find("[name='slug']");
		$("[data-slug-edit-btn]").click(function(){
			url_slug_group_.toggleClass("editing", true);
			url_slug_input_.focus().select();
			return false;
		});

		url_slug_input_.keydown(function(event_){
			if(event_.keyCode === 13){
				pb_page_update_slug();
				return false;
			}
		});

		$("[data-slug-edit-update-btn]").click(function(){
			pb_page_update_slug();
			return false;
		});
		// $(data-slug-edit-cancel-btn
		$("[data-slug-edit-cancel-btn]").click(function(){
			url_slug_group_.toggleClass("editing", false);
			url_slug_input_.val(url_slug_input_.attr("data-original-slug"));
			return false;
		});
	});

	function pb_page_update_slug(){
		var page_edit_form_ = $("#pb-page-edit-form");
		var url_slug_group_ = $("#pb-page-edit-form-url-slug-group");
		var url_slug_input_ = url_slug_group_.find("[name='slug']");
		var page_data_ = page_edit_form_.serialize_object();

		url_slug_group_.find(":input,button").prop("disabled", true);

		var slug_ = url_slug_input_.val();

		PB.post("update-page-slug", {
			page_id : page_data_['id'],
			slug : slug_
		}, function(result_, response_json_){
			if(!result_ || response_json_.success !== true){
				PB.alert({
					title : response_json_.error_title || "에러발생",
					content : response_json_.error_message || "슬러그 수정중, 에러가 발생했습니다.",
				});
				return;
			}

			var updated_slug_ = response_json_.slug;

			url_slug_group_.find(":input,button").prop("disabled", false);
			url_slug_input_.attr("data-original-slug", updated_slug_);
			url_slug_input_.val(updated_slug_);

			var page_link_ = $("[data-page-link]");
			page_link_.find(".slug").text(updated_slug_);
			page_link_.attr("href", PBVAR['home_url']+updated_slug_);
			url_slug_group_.toggleClass("editing", false);

		});
	}
</script>
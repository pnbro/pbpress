<?php 	
	
	global $pbpost_type, $pbpost_type_data, $pbpost, $pbpost_meta_map;
	$is_new_ = !isset($pbpost);

	if($is_new_){
		$pbpost = array(
			'id' => null,
			'featured_image_path' => null,
			'post_title' => null,
			'post_html' => null,
			'status' => PB_POST_STATUS::PUBLISHED,
			'slug' => null,
			'reg_date' => null,
		);
		$pbpost_meta_map = array();
	}

	$editors_ = isset($pbpost_type_data['editors']) ? $pbpost_type_data['editors'] : array("text", "editor");

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-post/edit.css?v=<?=PB_SCRIPT_VERSION?>">
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/aside-view.css?v=<?=PB_SCRIPT_VERSION?>">
<h3><?=($is_new_ ? $pbpost_type_data['label']['add'] : $pbpost_type_data['label']['update'])?> <a class="btn btn-default btn-sm" href="<?=pb_adminpage_back_url("manage-{$pbpost_type}")?>"><?=__('목록으로')?></a></h3>

<form id="pb-post-edit-form" method="POST">
	<?php pb_hook_do_action("pb_post_edit_form_before", $pbpost) ?>
	<?php pb_hook_do_action("pb_post_{$pbpost_type}_edit_form_before", $pbpost) ?>
	<input type="hidden" name="id" value="<?=$pbpost['id']?>">
	<input type="hidden" name="_request_chip" value="<?=pb_request_token('edit_post')?>">

	<div class="post-edit-frame aside-view-frame">
		<div class="col-content">
			<div class="form-group post-title-form-group">
				<input type="text" name="post_title" placeholder="<?=pb_hook_apply_filters('pb_post_edit_form_placeholder', __("제목 입력"))?>" value="<?=$pbpost['post_title']?>" class="form-control input-lg" required data-error="<?=__('제목을 입력하세요')?>">

				<div class="url-slug-group <?=$is_new_ ? "only-editing" : ""?> <?=!$pbpost_type_data['use_single_post'] ? "hidden" : ""?>" id="pb-post-edit-form-url-slug-group">
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><?=pb_hook_apply_filters("pb_post_slug_base_url", pb_home_url($pbpost_type.'/'), $pbpost_type)?></span>
						<input type="text" name="slug" class="form-control" placeholder="<?=__('URL슬러그 입력')?>" value="<?=$pbpost['slug']?>" data-original-slug="<?=$pbpost['slug']?>">
		
						<?php if(!$is_new_){ ?>
						<span class="input-group-btn">
							<button class="btn btn-primary" type="button" data-slug-edit-update-btn><?=__('수정')?></button>
						</span>
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" data-slug-edit-cancel-btn><?=__('취소')?></button>
						</span>
						<?php } ?>
					</div>	
					<?php if(!$is_new_){
						// $post_url_ = pb_post_url($pbpost['id']);
					?>
					<p class="post-url-info">
						<a href="<?=pb_post_url($pbpost['id'])?>" target="_blank" data-post-link>
							<?=pb_hook_apply_filters("pb_post_slug_base_url", pb_home_url($pbpost_type.'/'), $pbpost_type)?><strong class="slug"><?=$pbpost['slug']?></strong>
						</a> <a href="" class="btn btn-sm btn-default" data-slug-edit-btn><?=__('수정')?></a>
						

						
					</p>
						
					<?php } ?>
				</div>

				

				<?php if(!$is_new_){
					$post_url_ = pb_post_url($pbpost['id']);
				?>
					
					
				<?php }?>
				<div class="help-block with-errors"></div>
				<div class="clearfix"></div>
			</div>
			

			<?php
				pb_hook_do_action("pb_post_edit_form_post_html_before", $pbpost);
				pb_hook_do_action("pb_post_{$pbpost_type}_edit_form_post_html_before", $pbpost);
				pb_editor("post_html", $pbpost['post_html'], array(
					"min_height" => 400,
					"id" => "pb-post-html-editor",
					"editor" => pb_user_meta_value(pb_current_user_id(), 'post_actived_editor_id_'.$pbpost['id'], "editor"),
					"editors" => pb_hook_apply_filters('pb_post_{$pbpost_type}_editors', pb_hook_apply_filters('pb_post_editors', $editors_)),
				));
				pb_hook_do_action("pb_post_edit_form_post_html_after", $pbpost);
				pb_hook_do_action("pb_post_{$pbpost_type}_edit_form_post_html_after", $pbpost);
			?>
			<hr>
		</div>
		<div class="col-control-panel">
			
			<?php pb_hook_do_action("pb_post_edit_form_control_panel_before", $pbpost)?>
			<?php pb_hook_do_action("pb_post_{$pbpost_type}_edit_form_control_panel_before", $pbpost) ?>

			<div class="panel panel-default" id="pb-post-edit-form-post-common-panel">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" href="#pb-post-edit-form-post-common-panel-body" aria-expanded="true" ><?=__('기본정보')?></a>
					</h4>
				</div>
				<div id="pb-post-edit-form-post-common-panel-body" class="panel-collapse collapse in" role="tabpanel">
					<div class="panel-body">
						<div class="form-group">
							<label><?=__('글상태')?></label>
							<select class="form-control" name="status" required data-error="<?=__('상태를 선택하세요')?>">
								<?=PB_POST_STATUS::make_options($pbpost['status'])?>
							</select>
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>

						<div class="form-group">
							<label><?=__('대표이미지')?></label>
							<input type="hidden" name="featured_image_path" data-upload-path="/" id="pb-post-featured-image-picker" value="<?=$pbpost['featured_image_path']?>" data-single="Y" data-wrapper-class="simple">
						</div>
						
						<div class="form-group">
							<label><?=__('작성일자')?></label>
							<input type="text" name="reg_date" class="form-control" value="<?=$pbpost['reg_date']?>" id="pb-post-reg-date-picker" placeholder="<?=__('작성일자 선택')?>">
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>

						<?php if(!$is_new_){ ?>
						<div class="form-group">
							<label><?=__('마지막수정일자')?></label>
							<p class="form-control-static"><?=$pbpost['mod_date_ymdhi']?></p>
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>
						<?php } ?>
	
					</div>
					<div class="panel-footer">
						<div class="button-area">

							<div class="col-left">
								<button type="submit" class="btn btn-primary btn-block btn-lg"><?=($is_new_ ? $pbpost_type_data['label']['button_add'] : $pbpost_type_data['label']['button_update'])?></button>
							</div>
							<?php if(!$is_new_){ ?>
								<div class="col-right">
									<a href="javascript:pb_post_edit_form_delete(<?=$pbpost['id']?>)" class="btn btn-block btn-dark delete-btn">
										<i class="icon material-icons">delete_forever</i>
									</a>
								</div>
							<?php } ?>
							
						</div>
					</div>
				</div>
			</div>

			<?php pb_hook_do_action("pb_post_edit_form_control_panel_after", $pbpost)?>
			<?php pb_hook_do_action("pb_post_{$pbpost_type}_edit_form_control_panel_after", $pbpost) ?>
		</div>
	</div>
	<?php pb_hook_do_action("pb_post_edit_form_after", $pbpost) ?>
	<?php pb_hook_do_action("pb_post_{$pbpost_type}_edit_form_after", $pbpost) ?>
</form>
<script type="text/javascript">
window._pbpost_type = "<?=$pbpost_type?>";
window._pbpost_type_data = <?=json_encode($pbpost_type_data)?>;
</script>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-post/edit.js"></script>
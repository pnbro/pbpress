<?php 	
	
	global $pbpost, $pbpost_meta_map;
	$is_new_ = !isset($pbpost);

	if($is_new_){
		$pbpost = array(
			'id' => null,
			'post_title' => null,
			'post_html' => null,
			'status' => PB_POST_STATUS_PUBLISHED,
			'slug' => null,
		);
		$pbpost_meta_map = array();
	}

	$is_front_post_ = !$is_new_ && pb_front_post_id() === (string)$pbpost['id'];

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/posts/admin/manage-post/edit.css">
<h3><?=($is_new_ ? "글추가" : "글수정")?></h3>

<form id="pb-post-edit-form" method="POST">
	<?php pb_hook_do_action("pb_post_edit_form_before", $pbpost) ?>
	<input type="hidden" name="id" value="<?=$pbpost['id']?>">
	<input type="hidden" name="_request_chip" value="<?=pb_request_token('edit_post')?>">

	<div class="post-edit-frame">
		<div class="col-content">
			<div class="form-group post-title-form-group">
				<input type="text" name="post_title" placeholder="<?=pb_hook_apply_filters('pb_post_edit_form_placeholder', "글제목 입력")?>" value="<?=$pbpost['post_title']?>" class="form-control input-lg" required data-error="글제목을 입력하세요">

				<div class="url-slug-group <?=$is_new_ ? "only-editing" : ""?>" id="pb-post-edit-form-url-slug-group">
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><?=pb_home_url()?></span>
						<input type="text" name="slug" class="form-control" placeholder="URL슬러그 입력" value="<?=$pbpost['slug']?>" data-original-slug="<?=$pbpost['slug']?>">
		
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
						// $post_url_ = pb_post_url($pbpost['id']);
					?>
					<p class="post-url-info">
						<?php if($is_front_post_){ ?>
							<a href="<?=pb_post_url($pbpost['id'])?>" target="_blank" data-post-link>
							<?=pb_post_url($pbpost['id'])?>
						</a>
						<?php }else{ ?>
							<a href="<?=pb_home_url($pbpost['slug'])?>" target="_blank" data-post-link>
							<?=pb_home_url()?><strong class="slug"><?=$pbpost['slug']?></strong>
						</a> <a href="" class="btn btn-sm btn-default" data-slug-edit-btn>수정</a>
						<?php } ?>
						

						
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
				pb_editor("post_html", $pbpost['post_html'], array(
					"min_height" => 400,
					"id" => "pb-post-html-editor",
					"editor" => isset($pbpost_meta_map['actived_editor_id']) ? $pbpost_meta_map['actived_editor_id'] : null,
				));
				pb_hook_do_action("pb_post_edit_form_post_html_after", $pbpost);
			?>
			<hr>
		</div>
		<div class="col-control-panel">
			
			<?=pb_hook_do_action("pb_post_edit_form_control_panel_before", $pbpost)?>

			<div class="panel panel-default" id="pb-post-edit-form-post-common-panel">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a role="button" data-toggle="collapse" href="#pb-post-edit-form-post-common-panel-body" aria-expanded="true" aria-controls="collapseOne">기본정보</a>
					</h4>
				</div>
				<div id="pb-post-edit-form-post-common-panel-body" class="panel-collapse collapse in" role="tabpanel">
					<div class="panel-body">
						<div class="form-group">
							<label>글상태</label>
							<select class="form-control" name="status" required data-error="상태를 선택하세요">
								<?=pb_gcode_make_options(array("code_id" => "PST01"), $pbpost['status'])?>
							</select>
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>
						
						<?php if(!$is_new_){ ?>
						<div class="form-group">
							<label>마지막수정일자</label>
							<p class="form-control-static"><?=strlen($pbpost['mod_date_ymdhi']) ? $pbpost['mod_date_ymdhi'] : $pbpost['reg_date_ymdhi']?></p>
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>
						<?php } ?>
	
					</div>
					<div class="panel-footer">
						<div class="button-area">

							<div class="col-left">
								<button type="submit" class="btn btn-primary btn-block btn-lg"><?=($is_new_ ? "글추가" : "글수정")?></button>
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

			<?=pb_hook_do_action("pb_post_edit_form_control_panel_after", $pbpost)?>
		</div>
	</div>
	<?php pb_hook_do_action("pb_post_edit_form_after", $pbpost) ?>
</form>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/posts/admin/manage-post/edit.js"></script>
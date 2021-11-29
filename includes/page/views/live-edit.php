<?php 	
	
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

	$is_front_page_ = !$is_new_ && pb_front_page_id() === (string)$pbpage['id'];

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-page/edit.css">
<h3><?=($is_new_ ? "페이지추가" : "페이지수정")?></h3>

<form id="pb-page-edit-form" method="POST">
	<?php pb_hook_do_action("pb_page_edit_form_before", $pbpage) ?>
	<input type="hidden" name="id" value="<?=$pbpage['id']?>">
	<input type="hidden" name="_request_chip" value="<?=pb_request_token('edit_page')?>">

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
						<?php if($is_front_page_){ ?>
							<a href="<?=pb_page_url($pbpage['id'])?>" target="_blank" data-page-link>
							<?=pb_page_url($pbpage['id'])?>
						</a>
						<?php }else{ ?>
							<a href="<?=pb_home_url($pbpage['slug'])?>" target="_blank" data-page-link>
							<?=pb_home_url()?><strong class="slug"><?=$pbpage['slug']?></strong>
						</a> <a href="" class="btn btn-sm btn-default" data-slug-edit-btn>수정</a>
						<?php } ?>
						

						
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
								<?=PB_PAGE_STATUS::make_options($pbpage['status'])?>
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
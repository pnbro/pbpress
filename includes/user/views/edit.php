<?php 

	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}

	global $user_data;
	$is_root_admin_ = @$user_data['id'] === "1";
	$user_authority_slugs_ = array();

	$is_new_ = !@strlen($user_data['id']);

	global $pbdb;

	if(!$is_new_){
		$temp_user_authority_data_ = pb_user_authority_list(array("user_id" => $user_data['id']));

		foreach($temp_user_authority_data_ as $row_data_){
			$user_authority_slugs_[] = $row_data_['auth_slug'];
		}
	}

	

?>
<?php pb_hook_do_action('pb_admin_manage_user_edit_page_before')?>
<h3><?=__('사용자정보')?> <a class="btn btn-default btn-sm" href="<?=pb_adminpage_back_url('manage-user')?>"><?=__('목록으로')?></a></h3>
<form method="POST" id="pb-manage-user-edit-form" autocomplete="off">
	<input type="hidden" name="id" value="<?=@$user_data['id']?>">

	<div class="admin-flex-aside-frame" data-flex-aside-frame>
		<div class="col-content">
			
			<?php pb_hook_do_action('pb_admin_manage_user_edit_form_before')?>

			<input type="hidden" name="_request_chip", value="<?=pb_request_token("pbpress_manage_user")?>">

			<table class="pb-form-table" >
				<tbody>
					
					<tr>
						
						<th><?=__('사용자ID')?></th>
						<td>
							<div class="form-group">
								<input type="text" name="user_login" placeholder="<?=__('사용자ID 입력')?>" id="pb-manage-user-add-form-user_login" class="form-control" required data-error="<?=__('사용자ID를 입력하세요')?>" data-remote="<?=pb_ajax_url("pb-admin-manage-user-check-login", array('user_id' => @$user_data['id']))?>" data-remote-error="<?=__('이미 사용하고 있는 ID입니다.')?>" value="<?=@$user_data['user_login']?>">
								<div class="help-block with-errors"></div>
								<div class="clearfix"></div>
							</div>
						</td>
						<th><?=__('사용자이메일')?></th>
						<td >
							<div class="form-group">
								<input type="email" name="user_email" placeholder="<?=__('이메일 입력')?>" id="pb-manage-user-edit-form-user_email" class="form-control" required data-error="<?=__('이메일을 입력하세요')?>" value="<?=@$user_data['user_email']?>" data-remote="<?=pb_ajax_url("pb-admin-manage-user-check-email", array("user_id" => @$user_data['id']))?>" data-remote-error="<?=__('이미 사용하고 있는 이메일입니다.')?>">
								<div class="help-block with-errors"></div>
								<div class="clearfix"></div>
							</div>
						</td>
						
					</tr>
					<tr>
						
						<th><?=__('사용자명')?></th>
						<td>
							<div class="form-group">
								<input type="text" name="user_name" placeholder="<?=__('사용자명 입력')?>" id="pb-manage-user-add-form-user_name" class="form-control" required data-error="<?=__('사용자명을 입력하세요')?>" value="<?=@$user_data['user_name']?>">
								<div class="help-block with-errors"></div>
								<div class="clearfix"></div>
							</div>
						</td>
						<th><?=__('비밀번호')?></th>
						<td >
							<?php if($is_new_){ ?>
								<div class="form-group">
									<input type="password" name="user_pass" class="form-control" placeholder="<?=__('비밀번호 입력')?>" value="" required data-required-error="<?=__('비밀번호를 입력하세요.')?>" placeholder="" id="pb-manage-user-edit-form-user_pass" autocomplete="new-password">
									<div class="form-margin-xs"></div>
									<input type="password" name="user_pass_c" class="form-control" placeholder="<?=__('비밀번호 확인')?>" value="" required data-required-error="<?=__('비밀번호를 한번 더 입력하세요.')?>" placeholder="" data-match="#pb-manage-user-edit-form-user_pass" autocomplete="new-password" data-match-error="<?=__('비밀번호가 일치하지 않습니다.')?>">
									<div class="help-block with-errors"></div>
									<div class="clearfix"></div>
								</div>
							<?php }else{ ?>

								<div class="form-group">
									<input type="password" name="user_pass" class="form-control" placeholder="<?=__('비밀번호 입력')?>" value="" id="pb-manage-user-edit-form-user_pass" autocomplete="new-password">
									<div class="help-block with-errors"></div>
									<div class="help-block hint">*<?=__('비밀번호 변경 시에만 입력하여 주세요')?></div>
									<div class="clearfix"></div>
									
								</div>
							<?php } ?>
						</td>
						
					</tr>

					
				
					
				</tbody>
			</table>

			<?php pb_hook_do_action('pb_admin_manage_user_edit_form_after')?>


			<div class="form-margin-xs"></div>
		</div>

		
		<div class="col-control">
			<?php pb_hook_do_action('pb_admin_manage_user_edit_form_control_before')?>
			<div class="panel panel-default control-panel" data-spy="affix" data-offset-top="50">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<?=__('기본정보')?>
					</h4>
				</div>
			
				<div class="panel-body">
					

					<div class="form-group">
						<label><?=__('등록상태')?></label>
						<select class="form-control" name="user_status" required data-error="<?=__('등록상태를 선택하세요')?>">
							<?= PB_USER_STATUS::make_options(@$user_data['status']);?>
						</select>

					</div>

					<div class="form-group">
						<label><?=__('권한')?></label>
						<?php if($is_root_admin_){ ?>
							<p class="form-control-static"><?=__('관리자')?> <small>(<?=__('최초관리자의 권한은 변경불가')?>)</small></p>
						<?php }else{ ?>
							<select class="form-control" name="user_authority" multiple>
								<?php 

									$authority_list_ = pb_authority_list();
									

									foreach($authority_list_ as $auth_data_){
										$in_authority_ = in_array($auth_data_['slug'], $user_authority_slugs_);
									?>
										<option value="<?=$auth_data_['slug']?>" <?=$in_authority_ ? "selected" : ""?> ><?=$auth_data_['auth_name']?></option>
									<?php }
								?>
							</select>
						<?php } ?>

					</div>

					


					<ul class="subinfo-list">	

		
						<?php if(!$is_new_){ ?>

							<li>
								<div class="subject"><?=__('등록일자')?></div>
								<div class="content"><?= @$user_data['reg_date']; ?></div>
							</li>
					
							
							
						<?php } ?>
					</ul>
					
				</div>
				<div class="panel-footer">

					<?php if($is_new_){ ?>
						<button type="submit" class="btn btn-default btn-block btn-lg"><?=__('사용자 등록')?></button>
					<?php }else{ ?>
						<button type="submit" class="btn btn-default btn-block btn-lg"><?=__('변경사항 저장')?></button>
					<?php } ?>


				</div>
				
			</div>
			<?php pb_hook_do_action('pb_admin_manage_user_edit_form_control_after')?>
		</div>
	</div>



</form>
<?php if($is_new_){ ?>
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/user/add.js?v=<?=PB_SCRIPT_VERSION?>"></script>
<?php }else{ ?>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/user/edit.js?v=<?=PB_SCRIPT_VERSION?>"></script>
<?php } ?>
<?php pb_hook_do_action('pb_admin_manage_user_edit_page_after')?>
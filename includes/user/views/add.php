<?php 	

	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/user/add.css">


	<div class="manage-user-add-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?=__('사용자추가')?></h3>
		</div>
		<div class="panel-body">
			<form id="pb-manage-user-add-form" method="POST">
				<?php pb_hook_do_action('pb_admin_manage_user_edit_form_before')?>

				<input type="hidden" name="_request_chip", value="<?=pb_request_token("pbpress_manage_user")?>">
				<input type="hidden" name="id", value="">
				<div class="form-group">
					<label for="pb-manage-user-add-form-user_login"><?=__('사용자ID')?></label>
					<input type="text" name="user_login" placeholder="<?=__('사용자ID 입력')?>" id="pb-manage-user-add-form-user_login" class="form-control" required data-error="<?=__('사용자명을 입력하세요')?>" data-remote="<?=pb_ajax_url("pb-admin-manage-user-check-login")?>" data-remote-error="<?=__('이미 사용하고 있는 ID입니다.')?>">
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_pass"><?=__('암호')?></label>
					<input type="password" name="user_pass" placeholder="<?=__('암호 입력')?>" id="pb-manage-user-add-form-user_pass" class="form-control" data-error="<?=__('암호를 입력하세요')?>" value="">

					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_name"><?=__('사용자명')?> <sup class="text-primary">*</sup></label>
					<input type="text" name="user_name" placeholder="<?=__('사용자명 입력')?>" id="pb-manage-user-add-form-user_name" class="form-control" required data-error="<?=__('사용자명을 입력하세요')?>" >
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_email"><?=__('사용자이메일')?> <sup class="text-primary">*</sup></label>
					<input type="email" name="user_email" placeholder="<?=__('이메일 입력')?>" id="pb-manage-user-add-form-user_email" class="form-control" required data-error="<?=__('이메일을 입력하세요')?>" data-remote="<?=pb_ajax_url("pb-admin-manage-user-check-email")?>" data-remote-error="<?=__('이미 사용하고 있는 이메일입니다.')?>">
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_status"><?=__('상태')?> <sup class="text-primary">*</sup></label>

					<select class="form-control" name="user_status" required data-error="<?=__('상태를 선택하세요')?>">
						<?= pb_gcode_make_options(array("code_id" => "U0001"), "00001");?>
					</select>

					
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

					

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_authority"><?=__('권한')?> <sup class="text-primary">*</sup></label>

					<select class="form-control" name="user_authority" multiple>
						<?php 

							$authority_list_ = pb_authority_list();

							foreach($authority_list_ as $auth_data_){ ?>
								<option value="<?=$auth_data_['slug']?>" ><?=$auth_data_['auth_name']?></option>
							<?php }
						?>
					</select>
				
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<?php pb_hook_do_action('pb_admin_manage_user_edit_form_after')?>

				<hr>
				<button type="submit" class="btn btn-primary btn-block btn-lg"><?=__('사용자 추가')?></button>
				<a href="<?=pb_admin_url("manage-user")?>" class="btn btn-block btn-default "><?=__('내역으로')?></a>
			</form>
		</div>
	</div>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/user/add.js"></script>
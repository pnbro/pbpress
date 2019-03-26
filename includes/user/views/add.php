<?php 	

	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/user/add.css">


	<div class="manage-user-add-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">사용자추가</h3>
		</div>
		<div class="panel-body">
			<form id="pb-manage-user-add-form" method="POST">
				<?php pb_hook_do_action('pb_admin_manage_user_edit_form_before')?>

				<input type="hidden" name="_request_chip", value="<?=pb_session_instance_token("pbpress_manage-user")?>">
				<input type="hidden" name="id", value="<?=$user_data['id']?>">
				<div class="form-group">
					<label for="pb-manage-user-add-form-user_login">사용자ID</label>
					<input type="text" name="user_login" placeholder="사용자ID 입력" id="pb-manage-user-add-form-user_login" class="form-control" required data-error="사용자명을 입력하세요" data-remote="<?=pb_ajax_url("pb-admin-manage-user-check-login")?>" data-remote-error="이미 사용하고 있는 ID입니다.">
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_pass">암호</label>
					<input type="password" name="user_pass" placeholder="암호 입력" id="pb-manage-user-add-form-user_pass" class="form-control" data-error="암호를 입력하세요" value="">

					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_name">사용자명 <sup class="text-primary">*</sup></label>
					<input type="text" name="user_name" placeholder="사용자명 입력" id="pb-manage-user-add-form-user_name" class="form-control" required data-error="사용자명을 입력하세요" >
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_email">사용자이메일 <sup class="text-primary">*</sup></label>
					<input type="email" name="user_email" placeholder="이메일 입력" id="pb-manage-user-add-form-user_email" class="form-control" required data-error="이메일을 입력하세요" data-remote="<?=pb_ajax_url("pb-admin-manage-user-check-email")?>" data-remote-error="이미 사용하고 있는 이메일입니다.">
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_status">상태 <sup class="text-primary">*</sup></label>

					<select class="form-control" name="user_status" required data-error="상태를 선택하세요">
						<?= pb_gcode_make_options(array("code_id" => "U0001"), "00001");?>
					</select>

					
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

					

				<div class="form-group">
					<label for="pb-manage-user-add-form-user_authority">권한 <sup class="text-primary">*</sup></label>

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
				<button type="submit" class="btn btn-primary btn-block btn-lg">사용자 추가</button>
				<a href="<?=pb_admin_url("manage-user")?>" class="btn btn-block btn-default ">내역으로</a>
			</form>
		</div>
	</div>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/user/add.js"></script>
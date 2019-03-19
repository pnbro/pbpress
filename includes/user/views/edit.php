<?php 	

	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}

	global $user_data;
	$is_root_admin_ = $user_data['ID'] === "1";
	$temp_user_authority_data_ = pb_user_authority_list(array("user_id" => $user_data['ID']));
	$user_authority_slugs_ = array();

	foreach($temp_user_authority_data_ as $row_data_){
		$user_authority_slugs_[] = $row_data_['AUTH_SLUG'];
	}
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/user/edit.css">


	<div class="manage-user-edit-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">사용자정보수정</h3>
		</div>
		<div class="panel-body">
			<form id="pb-manage-user-edit-form" method="POST">
				<?php pb_hook_do_action('pb_admin_manage_user_edit_form_before')?>

				<input type="hidden" name="_request_chip", value="<?=pb_session_instance_token("pbpress_manage-user")?>">
				<input type="hidden" name="ID", value="<?=$user_data['ID']?>">
				<div class="form-group">
					<label >사용자ID <sup class="text-danger">변경불가</sup></label>
					<p class="form-control-static"><?=$user_data['USER_LOGIN']?></p>
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-edit-form-user_pass">암호변경</label>
					<input type="password" name="user_pass" placeholder="변경할 암호 입력" id="pb-manage-user-edit-form-user_pass" class="form-control" data-error="암호를 입력하세요" value="">

					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-edit-form-user_name">사용자명 <sup class="text-primary">*</sup></label>
					<input type="text" name="user_name" placeholder="사용자명 입력" id="pb-manage-user-edit-form-user_name" class="form-control" required data-error="사용자명을 입력하세요" value="<?=$user_data['USER_NAME']?>">
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-user-edit-form-user_email">사용자이메일 <sup class="text-primary">*</sup></label>
					<input type="email" name="user_email" placeholder="이메일 입력" id="pb-manage-user-edit-form-user_email" class="form-control" required data-error="이메일을 입력하세요" value="<?=$user_data['USER_EMAIL']?>" data-remote="<?=pb_ajax_url("pb-admin-manage-user-check-email", array("user_id" => $user_data['ID']))?>" data-remote-error="이미 사용하고 있는 이메일입니다.">
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<?php if(!$is_root_admin_){ ?>

					<div class="form-group">
						<label for="pb-manage-user-edit-form-user_status">상태 <sup class="text-primary">*</sup></label>

						<select class="form-control" name="user_status" required data-error="상태를 선택하세요">
							<?= pb_gcode_make_options(array("code_id" => "U0001"), $user_data['STATUS']);?>
						</select>

						
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				<?php } ?>

					

				<div class="form-group">
					<label for="pb-manage-user-edit-form-user_authority">권한 <sup class="text-primary">*</sup></label>

					<?php if($is_root_admin_){ ?>
						<p class="form-control-static">관리자 <small>(최초관리자의 권한은 변경불가)</small></p>
					<?php }else{ ?>
						<select class="form-control" name="user_authority" multiple>
							<?php 

								$authority_list_ = pb_authority_list();
								

								foreach($authority_list_ as $auth_data_){
									$in_authority_ = in_array($auth_data_['SLUG'], $user_authority_slugs_);
								?>
									<option value="<?=$auth_data_['SLUG']?>" <?=$in_authority_ ? "selected" : ""?> ><?=$auth_data_['AUTH_NAME']?></option>
								<?php }
							?>
						</select>
					<?php } ?>
					
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<?php pb_hook_do_action('pb_admin_manage_user_edit_form_after')?>

				<hr>
				<button type="submit" class="btn btn-primary btn-block btn-lg">변경사항 저장</button>
				<a href="<?=pb_admin_url("manage-user")?>" class="btn btn-block btn-default ">내역으로</a>
			</form>
		</div>
	</div>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/user/edit.js"></script>
<?php
	
include(dirname( __FILE__ ) . "/includes.php");

$user_email_ = isset($_GET["user_email"]) ? $_GET["user_email"] : null;
$vkey_ = isset($_GET["vkey"]) ? $_GET["vkey"] : null;

if(!strlen($user_email_) || !strlen($vkey_)){
	pb_redirect(pb_home_url());
	pb_admin_end();
}

$user_data_ = pb_user_by_user_email($user_email_);

$check_vkey_ = pb_user_check_findpass_validation_key($user_data_['id'], $vkey_);

if(pb_is_error($check_vkey_)){
	pb_redirect_error(403, $check_vkey_->error_message(), $check_vkey_->error_title());
	pb_admin_end();
}

?><!DOCTYPE html>
<html>
<head>
	<title>PBPress 비밀번호 재설정</title>

	<meta charset="utf8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="format-detection" content="telephone=no">

	<?php pb_admin_head(); ?>
	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/resetpass.css">
</head>

<body class="page-pbpress-resetpass">

	<div class="container">
	
		<div class="pb-logo-frame">
			<img src="<?=PB_LIBRARY_URL?>img/symbol.jpg" class="logo">
		</div>

		<div class="resetpass-form-panel panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">PBPress 비밀번호 재설정</h3>
			</div>
			<div class="panel-body">
				<form id="pb-resetpass-form" method="POST">
					<input type="hidden" name="user_email" value="<?=$user_email_?>">
					<input type="hidden" name="vkey" value="<?=$vkey_?>">
					<input type="hidden" name="_request_chip", value="<?=pb_session_instance_token("pbpress_admin_resetpass")?>">

					<div class="form-group">
						<label for="pb-resetpass-form-user_pass">변경할 비밀번호 입력</label>
						<input type="password" name="user_pass" placeholder="비밀번호 입력" id="pb-resetpass-form-user_pass" class="form-control" required data-error="비밀번호를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<hr>
					<button type="submit" class="btn btn-primary btn-block btn-lg">비밀번호 재설정</button>
				</form>
			</div>
		</div>
	</div>

	<div class="copyrights">© 2019 Paul&Bro Company All Rights Reserved.</div>

	<?php pb_admin_foot(); ?>
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/resetpass.js"></script>

</body>
</html>
<?php pb_admin_end(); ?>
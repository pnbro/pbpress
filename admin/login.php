<?php

include(dirname( __FILE__ ) . "/includes.php");

$redirect_url_ = _GET('redirect_url', pb_admin_url());

$admin_login_title_ = pb_hook_apply_filters('adminpage_login_title', __("PBPress 로그인"));

?><!DOCTYPE html>
<html>
<head>
	<title><?=$admin_login_title_?></title>

	<meta charset="utf8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="format-detection" content="telephone=no">

	<?php pb_admin_head(); ?>
	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/login.css">
</head>

<body class="page-pbpress-login">

	<div class="container">
	
		<div class="pb-logo-frame">
			<img src="<?=pb_hook_apply_filters('adminpage_login_logo_image', PB_LIBRARY_URL."img/symbol.jpg")?>" class="logo">
		</div>

		<div class="login-form-panel panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?=$admin_login_title_?></h3>
			</div>
			<div class="panel-body">
				<form id="pb-login-form" method="POST">
					<input type="hidden" name="redirect_url" value="<?=$redirect_url_?>">
					<input type="hidden" name="_request_chip", value="<?=pb_request_token("pbpress_admin_login")?>">

					<div class="form-group">
						<label for="pb-login-form-user_login"><?=__('ID 또는 이메일')?> <sup class="text-primary">*</sup></label>
						<input type="text" name="user_login" placeholder="<?=__('사용자ID 입력')?>" id="pb-login-form-site_login" class="form-control" required data-error="<?=__('사용자ID를 입력하세요')?>">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<div class="form-group">
						<label for="pb-login-form-user_pass"><?=__('비밀번호')?> <sup class="text-primary">*</sup></label>
						<input type="password" name="user_pass" placeholder="<?=__('비밀번호 입력')?>" id="pb-login-form-user_pass" class="form-control" required data-error="<?=__('비밀번호 입력하세요')?>">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<hr>
					<button type="submit" class="btn btn-primary btn-block btn-lg"><?=__('로그인')?></button>
					<div class="bottom-frame text-center">
						<a href="" data-toggle="modal" data-target="#pb-admin-login-findpass-modal"><?=__('비밀번호 찾기')?></a>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade " tabindex="-1" role="dialog" id="pb-admin-login-findpass-modal">
		<div class="modal-dialog " role="document"><div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?=__('비밀번호 찾기')?></h4>
			</div>
			<div class="modal-body"><form id="pb-admin-login-findpass-form" method="POST" >
				
				<div class="form-group">

					<input type="mail" class="form-control" id="pb-admin-login-findpass-form-user-email" placeholder="<?=__('가입하신 이메일 입력')?>" name="user_email" required >
					<div class="help-block with-errors"></div>
					<p class="help-block text-center"><?=__('가입하신 이메일로 암호를 재설정할 수 있습니다.')?></p>
				
				</div>

				<hr>

				<div class="form-margin-xs"></div>
				<button type="submit" class="btn btn-primary btn-block btn-lg"><?=__('전송하기')?></button>
				</div>
			</form></div>
			
		</div></div>
	</div>

	<div class="copyrights"><?=pb_hook_apply_filters('adminpage_footer_copyrights', '© 2019 Paul&Bro Company All Rights Reserved.')?></div>

	<?php pb_admin_foot(); ?>
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/login.js"></script>
</body>
</html>
<?php pb_admin_end(); ?>
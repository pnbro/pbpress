<?php
	
include(dirname( __FILE__ ) . "/includes.php");

$redirect_url_ = (isset($_GET["redirect_url"]) && strlen($_GET["redirect_url"])) ? $_GET["redirect_url"] : pb_admin_url();

?><!DOCTYPE html>
<html>
<head>
	<title>PBPress 관리자 로그인</title>

	<meta charset="utf8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="format-detection" content="telephone=no">

	<?php pb_admin_head(); ?>
	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>/css/pages/admin/login.css">
</head>

<body class="page-pbpress-login">

	<div class="container">
	
		<div class="pb-logo-frame">
			<img src="<?=PB_LIBRARY_URL?>img/symbol.jpg" class="logo">
		</div>

		<div class="login-form-panel panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">PBPress 로그인</h3>
			</div>
			<div class="panel-body">
				<form id="pb-login-form" method="POST">
					<input type="hidden" name="redirect_url" value="<?=$redirect_url_?>">
					<input type="hidden" name="_request_chip", value="<?=pb_session_instance_token("pbpress_admin_login")?>">

					<div class="form-group">
						<label for="pb-login-form-user_login">ID 또는 이메일 <sup class="text-primary">*</sup></label>
						<input type="text" name="user_login" placeholder="사용자ID 입력" id="pb-login-form-site_login" class="form-control" required data-error="사용자ID를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<div class="form-group">
						<label for="pb-login-form-user_pass">비밀번호 <sup class="text-primary">*</sup></label>
						<input type="password" name="user_pass" placeholder="비밀번호 입력" id="pb-login-form-user_pass" class="form-control" required data-error="비밀번호 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<hr>
					<button type="submit" class="btn btn-primary btn-block btn-lg">로그인</button>
				</form>
			</div>
		</div>
	</div>

	<div class="copyrights">© 2019 Paul&Bro Company All Rights Reserved.</div>

	<?php pb_admin_foot(); ?>
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>/js/pages/admin/login.js"></script>

</body>
</html>
<?php pb_admin_end(); ?>
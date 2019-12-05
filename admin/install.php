<?php 		

		
	require(dirname( __FILE__ ) . '/../defined.php');
	require(PB_DOCUMENT_PATH . 'includes/includes.php');
	require(dirname( __FILE__ ) . '/admin-hook.php');
	require(dirname( __FILE__ ) . '/function.php');

	//check rewrite rule
	if(!pb_exists_rewrite()){
		pb_install_rewrite();
	}

	//check https config
	if((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") && $pb_config->use_https()){
		$https_location_ = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $https_location_);
		pb_hook_do_action('pb_ended');
		exit;
	}

	global $pbdb;
	if($pbdb->exists_table("options")){
		echo "PBPress already installed.";
		exit;
	}


?><!DOCTYPE html>
<html lang="ko">
<head>
   
    <title>PBPress 설치</title>

    <meta charset="utf8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="format-detection" content="telephone=no">

	<?php pb_admin_head(); ?>
	<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/install.css">
</head>

<body class="page-pbpress-install">

	<div class="container">
	
		<div class="pb-logo-frame">
			<img src="<?=PB_LIBRARY_URL?>img/symbol.jpg" class="logo">
		</div>

		<div class="install-form-panel panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">PBPress 설치하기</h3>
			</div>
			<div class="panel-body">
				<form id="pb-install-form" method="POST">
					<input type="hidden" name="_request_chip", value="<?=pb_request_token("pbpress_install")?>">
					<div class="form-group">
						<label for="pb-install-form-site_name">사이트명 <sup class="text-primary">*</sup></label>
						<input type="text" name="site_name" placeholder="사이트명 입력" id="pb-install-form-site_name" class="form-control" required data-error="사이트명을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<div class="form-group">
						<label for="pb-install-form-site_desc">사이트한줄설명</label>
						<input type="text" name="site_desc" placeholder="사이트이름 입력" id="pb-install-form-site_desc" class="form-control">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<div class="form-group">
						<label for="pb-install-form-timezone">시간대</label>
						<select class="form-control" name="timezone" required data-error="시간대를 선택하세요">
						</select>
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<hr>

					<div class="form-group">
						<label for="pb-install-form-user_login">사용자ID <sup class="text-primary">*</sup></label>
						<input type="text" name="user_login" placeholder="사용자ID 입력" id="pb-install-form-site_login" class="form-control" required data-error="사용자ID를 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<div class="form-group"><div class="row">
						<div class="col-xs-12 col-sm-6">
							<label for="pb-install-form-user_pass">비밀번호 <sup class="text-primary">*</sup></label>
							<input type="password" name="user_pass" placeholder="비밀번호 입력" id="pb-install-form-user_pass" class="form-control" required data-error="비밀번호 입력하세요">
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>
						<div class="col-xs-12 col-sm-6">
							<label for="pb-install-form-user_pass_c">비밀번호확인 <sup class="text-primary">*</sup></label>
							<input type="password" name="user_pass_c" placeholder="비밀번호 확인" id="pb-install-form-user_pass_c" class="form-control" required data-error="비밀번호가 정확하지 않습니다." data-match="#pb-install-form-user_pass">
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>
							
					</div></div>

					<div class="form-group">
						<label for="pb-install-for-user_email">사용자이메일 <sup class="text-primary">*</sup></label>
						<input type="email" name="user_email" placeholder="사용자ID 입력" id="pb-install-form-user_email" class="form-control" required data-error="사용자이메일을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<div class="form-group">
						<label for="pb-install-for-user_name">사용자명 <sup class="text-primary">*</sup></label>
						<input type="text" name="user_name" placeholder="사용자명 입력" id="pb-install-form-user_name" class="form-control" required data-error="사용자명을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>

					<hr>
					<button type="submit" class="btn btn-primary btn-block btn-lg">PBPress 설치하기</button>
				</form>
			</div>
		</div>
	</div>

	<div class="copyrights">© 2019 Paul&Bro Company All Rights Reserved.</div>

	<?php pb_admin_foot(); ?>
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/moment-timezone-with-data.js"></script>
	<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/install.js"></script>
	
</body>
</html>
<?php pb_admin_end(); ?>
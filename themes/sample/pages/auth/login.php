<?php pb_theme_header(); ?>

<!-- ============================================================
     devplan002 part004 — 로그인
     part001 .field/.input/.btn-primary/.card + 신규 .auth-shell(features/auth.css)
     ============================================================ -->

<div class="container">
	<div class="auth-shell">
		<div class="card auth-shell__card">
			<div class="card-header text-center">
				<h1 class="auth-shell__title"><?=__('로그인')?></h1>
				<p class="text-muted text-sm"><?=__('이메일로 로그인합니다.', PB_THEME_DOMAIN)?></p>
			</div>

			<div class="card-body">
				<form id="sample-auth-login-form" class="auth-shell__form" data-validate novalidate>
					<input type="hidden" name="_request_chip" value="<?=pb_request_token('sample-theme-auth-login')?>">

					<div class="field">
						<label class="label label-required" for="sample-auth-login-email"><?=__('이메일')?></label>
						<input type="email" id="sample-auth-login-email" name="user_email" class="input" autocomplete="email" placeholder="you@example.com" data-required data-required-message="<?=__('이메일을 입력하세요.', PB_THEME_DOMAIN)?>" data-email data-email-message="<?=__('이메일 형식이 올바르지 않습니다.', PB_THEME_DOMAIN)?>">
					</div>

					<div class="field">
						<label class="label label-required" for="sample-auth-login-pass"><?=__('비밀번호')?></label>
						<input type="password" id="sample-auth-login-pass" name="user_pass" class="input" autocomplete="current-password" placeholder="••••••••" data-required data-required-message="<?=__('비밀번호를 입력하세요.')?>">
					</div>

					<button type="submit" class="btn btn-primary btn-block" data-default-label="<?=__('로그인')?>" data-loading-label="<?=__('로그인 중...', PB_THEME_DOMAIN)?>"><?=__('로그인')?></button>
				</form>
			</div>

			<div class="card-footer auth-shell__links">
				<a href="<?=pb_resetpass_url()?>"><?=__('비밀번호를 잊으셨나요?', PB_THEME_DOMAIN)?></a>
				<a href="<?=pb_signup_url()?>"><?=__('회원가입', PB_THEME_DOMAIN)?></a>
			</div>
		</div>
	</div>
</div>

<?php pb_theme_footer(); ?>

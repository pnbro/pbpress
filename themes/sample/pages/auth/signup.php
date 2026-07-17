<?php pb_theme_header(); ?>

<!-- ============================================================
     devplan002 part004 — 회원가입
     이메일 로그인 전용 테마: 별도 아이디 없이 이메일을 로그인ID로 사용.
     ============================================================ -->

<div class="container">
	<div class="auth-shell">
		<div class="card auth-shell__card">
			<div class="card-header text-center">
				<h1 class="auth-shell__title"><?=__('회원가입')?></h1>
				<p class="text-muted text-sm"><?=__('이메일 하나로 가입하고 바로 로그인합니다.')?></p>
			</div>

			<div class="card-body">
				<form id="sample-auth-signup-form" class="auth-shell__form" data-validate novalidate>
					<input type="hidden" name="_request_chip" value="<?=pb_request_token('sample-theme-auth-signup')?>">

					<div class="field">
						<label class="label label-required" for="sample-auth-signup-email"><?=__('이메일')?></label>
						<input type="email" id="sample-auth-signup-email" name="user_email" class="input" autocomplete="email" placeholder="you@example.com" data-required data-required-message="<?=__('이메일을 입력하세요.')?>" data-email data-email-message="<?=__('이메일 형식이 올바르지 않습니다.')?>">
						<p class="field-hint"><?=__('가입 후 로그인 아이디로 사용됩니다.')?></p>
					</div>

					<div class="field">
						<label class="label label-required" for="sample-auth-signup-name"><?=__('이름')?></label>
						<input type="text" id="sample-auth-signup-name" name="user_name" class="input" autocomplete="name" placeholder="<?=__('홍길동')?>" data-required data-required-message="<?=__('이름을 입력하세요.')?>" data-max="50">
					</div>

					<div class="field">
						<label class="label label-required" for="sample-auth-signup-pass"><?=__('비밀번호')?></label>
						<input type="password" id="sample-auth-signup-pass" name="user_pass" class="input" autocomplete="new-password" placeholder="<?=__('4자 이상')?>" data-required data-required-message="<?=__('비밀번호를 입력하세요.')?>" data-min="4" data-min-message="<?=__('비밀번호는 4자 이상이어야 합니다.')?>">
					</div>

					<div class="field">
						<label class="label label-required" for="sample-auth-signup-pass-confirm"><?=__('비밀번호 확인')?></label>
						<input type="password" id="sample-auth-signup-pass-confirm" name="user_pass_confirm" class="input" autocomplete="new-password" placeholder="<?=__('비밀번호 재입력')?>" data-required data-required-message="<?=__('비밀번호를 다시 입력하세요.')?>" data-match="#sample-auth-signup-pass" data-match-message="<?=__('비밀번호가 일치하지 않습니다.')?>">
					</div>

					<button type="submit" class="btn btn-primary btn-block" data-default-label="<?=__('회원가입')?>" data-loading-label="<?=__('가입 처리 중...')?>"><?=__('회원가입')?></button>
				</form>
			</div>

			<div class="card-footer auth-shell__links">
				<a href="<?=pb_login_url()?>"><?=__('이미 계정이 있으신가요? 로그인')?></a>
			</div>
		</div>
	</div>
</div>

<?php pb_theme_footer(); ?>

<?php
pb_theme_header();

/* rewrite_handler(_sample_theme_auth_rewrite_handler_resetpass)에서 설정한 상태.
 * step: request(이메일 입력) | form(새 비밀번호 입력, vkey 검증완료) | invalid(vkey 불량/만료) */
global $sample_auth_resetpass_state_;
$sample_auth_resetpass_view_state_ = isset($sample_auth_resetpass_state_) ? $sample_auth_resetpass_state_ : array('step' => 'request');
?>

<!-- ============================================================
     devplan002 part004 — 비밀번호 재설정 (2단계: 이메일 발송 → vkey 검증 후 변경)
     ============================================================ -->

<div class="container">
	<div class="auth-shell">
		<div class="card auth-shell__card">
			<div class="card-header text-center">
				<h1 class="auth-shell__title"><?=__('비밀번호 재설정')?></h1>
			</div>

			<div class="card-body">

				<?php if($sample_auth_resetpass_view_state_['step'] === 'invalid'){ ?>

					<div class="alert alert-danger"><?=htmlspecialchars($sample_auth_resetpass_view_state_['error'])?></div>
					<p class="text-center mt-4">
						<a href="<?=pb_resetpass_url()?>" class="btn btn-ghost"><?=__('다시 요청하기', PB_THEME_DOMAIN)?></a>
					</p>

				<?php }else if($sample_auth_resetpass_view_state_['step'] === 'form'){ ?>

					<p class="text-muted text-sm mb-4"><?=__('새로운 비밀번호를 입력하세요.', PB_THEME_DOMAIN)?></p>

					<form id="sample-auth-resetpass-change-form" class="auth-shell__form" data-validate novalidate>
						<input type="hidden" name="_request_chip" value="<?=pb_request_token('sample-theme-auth-resetpass')?>">
						<input type="hidden" name="user_email" value="<?=htmlspecialchars($sample_auth_resetpass_view_state_['user_email'])?>">
						<input type="hidden" name="vkey" value="<?=htmlspecialchars($sample_auth_resetpass_view_state_['vkey'])?>">

						<div class="field">
							<label class="label label-required" for="sample-auth-resetpass-pass"><?=__('새 비밀번호', PB_THEME_DOMAIN)?></label>
							<input type="password" id="sample-auth-resetpass-pass" name="user_pass" class="input" autocomplete="new-password" placeholder="<?=__('4자 이상', PB_THEME_DOMAIN)?>" data-required data-required-message="<?=__('비밀번호를 입력하세요.')?>" data-min="4" data-min-message="<?=__('비밀번호는 4자 이상이어야 합니다.', PB_THEME_DOMAIN)?>">
						</div>

						<div class="field">
							<label class="label label-required" for="sample-auth-resetpass-pass-confirm"><?=__('비밀번호 확인')?></label>
							<input type="password" id="sample-auth-resetpass-pass-confirm" name="user_pass_confirm" class="input" autocomplete="new-password" placeholder="<?=__('비밀번호 재입력', PB_THEME_DOMAIN)?>" data-required data-required-message="<?=__('비밀번호를 다시 입력하세요.', PB_THEME_DOMAIN)?>" data-match="#sample-auth-resetpass-pass" data-match-message="<?=__('비밀번호가 일치하지 않습니다.')?>">
						</div>

						<button type="submit" class="btn btn-primary btn-block" data-default-label="<?=__('비밀번호 변경', PB_THEME_DOMAIN)?>" data-loading-label="<?=__('처리 중...', PB_THEME_DOMAIN)?>"><?=__('비밀번호 변경', PB_THEME_DOMAIN)?></button>
					</form>

				<?php }else{ ?>

					<p class="text-muted text-sm mb-4"><?=__('가입한 이메일로 재설정 링크를 보내드립니다.', PB_THEME_DOMAIN)?></p>

					<form id="sample-auth-resetpass-request-form" class="auth-shell__form" data-validate novalidate>
						<div class="field">
							<label class="label label-required" for="sample-auth-resetpass-email"><?=__('이메일')?></label>
							<input type="email" id="sample-auth-resetpass-email" name="user_email" class="input" autocomplete="email" placeholder="you@example.com" data-required data-required-message="<?=__('이메일을 입력하세요.', PB_THEME_DOMAIN)?>" data-email data-email-message="<?=__('이메일 형식이 올바르지 않습니다.', PB_THEME_DOMAIN)?>">
						</div>

						<button type="submit" class="btn btn-primary btn-block" data-default-label="<?=__('재설정 메일 보내기', PB_THEME_DOMAIN)?>" data-loading-label="<?=__('발송 중...', PB_THEME_DOMAIN)?>"><?=__('재설정 메일 보내기', PB_THEME_DOMAIN)?></button>
					</form>

				<?php } ?>

			</div>

			<div class="card-footer auth-shell__links">
				<a href="<?=pb_login_url()?>"><?=__('로그인으로 돌아가기', PB_THEME_DOMAIN)?></a>
			</div>
		</div>
	</div>
</div>

<?php pb_theme_footer(); ?>

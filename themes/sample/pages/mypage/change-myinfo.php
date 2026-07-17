<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* devplan002 part005 — 회원정보 수정
 * 폼검증: part001 SampleUI.formValidate(data-required/data-email/data-min/data-match).
 * 저장: lib/js/features/mypage.js 가 PB.post("user-mypage-update-myinfo", ...)로 제출,
 * 코어 pb_user_update AJAX(핸들러: page-handlers/mypage/change-myinfo.php). */

$pb_mypage_user_ = pb_current_user();

pb_theme_header();
?>
<div class="container mypage-shell">
	<div class="grid">
		<div class="col-12 col-md-3">
			<?php include PB_THEME_PATH.'pages/mypage/aside.php'; ?>
		</div>
		<div class="col-12 col-md-9">
			<div class="card">
				<div class="card-header">회원정보 수정</div>
				<div class="card-body">
					<form id="mypage-change-myinfo-form" data-validate novalidate>
						<div class="field">
							<label class="label label-required" for="mypage-user-name">이름</label>
							<input type="text" id="mypage-user-name" name="user_name" class="input" maxlength="50"
								data-required data-required-message="이름을 입력하세요."
								value="<?=htmlspecialchars((string)@$pb_mypage_user_['user_name'])?>">
						</div>
						<div class="field">
							<label class="label label-required" for="mypage-user-email">이메일</label>
							<input type="email" id="mypage-user-email" name="user_email" class="input" maxlength="50"
								data-required data-required-message="이메일을 입력하세요."
								data-email data-email-message="올바른 이메일 형식이 아닙니다."
								value="<?=htmlspecialchars((string)@$pb_mypage_user_['user_email'])?>">
						</div>

						<hr>

						<p class="text-muted text-sm mb-4">비밀번호를 변경하려는 경우에만 아래 두 항목을 입력하세요.</p>

						<div class="field">
							<label class="label" for="mypage-new-password">새 비밀번호</label>
							<input type="password" id="mypage-new-password" name="new_password" class="input"
								autocomplete="new-password"
								data-min="6" data-min-message="비밀번호는 6자 이상이어야 합니다.">
							<p class="field-hint">변경하지 않으려면 비워두세요.</p>
						</div>
						<div class="field">
							<label class="label" for="mypage-new-password-confirm">새 비밀번호 확인</label>
							<input type="password" id="mypage-new-password-confirm" name="new_password_confirm" class="input"
								autocomplete="new-password"
								data-match="#mypage-new-password" data-match-message="새 비밀번호가 일치하지 않습니다.">
						</div>

						<button type="submit" class="btn btn-primary" id="mypage-change-myinfo-submit">저장</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<?php pb_theme_footer(); ?>

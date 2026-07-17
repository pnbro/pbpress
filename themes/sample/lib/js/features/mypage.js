/* ==========================================================================
   Sample Theme — Mypage (devplan002 part005)
   순수 바닐라 JS. 전역 오염 없이 IIFE. jQuery 미사용.
   코어 PB.post AJAX + part001 SampleUI.toast/formValidate 만 재사용한다.
   ui.js(part001)는 편집하지 않으며, 이 파일은 /mypage 라우트에서만 로드된다
   (page-handlers/mypage.php의 pb_foot 훅 참고).
   ========================================================================== */
(function () {
	"use strict";

	function bindChangeMyinfoForm() {
		var form = document.getElementById("mypage-change-myinfo-form");
		if (!form) {
			return;
		}

		form.addEventListener("submit", function (e) {
			e.preventDefault();

			if (window.SampleUI && typeof window.SampleUI.formValidate === "function" && !window.SampleUI.formValidate(form)) {
				return;
			}

			if (typeof window.PB === "undefined" || !window.PB.post) {
				return;
			}

			var submitBtn = document.getElementById("mypage-change-myinfo-submit");
			var originalText = submitBtn ? submitBtn.textContent : "";
			if (submitBtn) {
				submitBtn.disabled = true;
				submitBtn.classList.add("is-disabled");
				submitBtn.textContent = "저장 중...";
			}

			var nameInput = form.querySelector("[name=user_name]");
			var emailInput = form.querySelector("[name=user_email]");
			var profileBioInput = form.querySelector("[name=profile_bio]");
			var newPasswordInput = form.querySelector("[name=new_password]");
			var newPasswordConfirmInput = form.querySelector("[name=new_password_confirm]");

			var payload = {
				user_name: nameInput ? nameInput.value : "",
				user_email: emailInput ? emailInput.value : "",
				profile_bio: profileBioInput ? profileBioInput.value : "",
				new_password: newPasswordInput ? newPasswordInput.value : "",
				new_password_confirm: newPasswordConfirmInput ? newPasswordConfirmInput.value : ""
			};

			window.PB.post("user-mypage-update-myinfo", payload, function (result_, response_json_) {
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.classList.remove("is-disabled");
					submitBtn.textContent = originalText;
				}

				if (!result_ || !response_json_ || response_json_.success !== true) {
					var message = (response_json_ && response_json_.error_message) || "저장에 실패했습니다.";
					if (window.SampleUI) {
						window.SampleUI.toast(message, "danger");
					}
					return;
				}

				if (newPasswordInput) {
					newPasswordInput.value = "";
				}
				if (newPasswordConfirmInput) {
					newPasswordConfirmInput.value = "";
				}

				if (window.SampleUI) {
					window.SampleUI.toast(response_json_.message || "저장되었습니다.", "success");
				}
			});
		});
	}

	function init() {
		bindChangeMyinfoForm();
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})();

/* ==========================================================================
   Sample Theme — Auth pages 인터랙션 (devplan002 part004)
   순수 바닐라 JS. jQuery 직접 사용 금지(코어 PB.post/PB.crypt는 내부적으로
   jQuery를 쓰지만 여기서는 함수 호출만 한다).
   로드: login/signup/resetpass 페이지에서만(includes/auth-pages.php의 pb_foot 훅).
   SampleUI(ui.js)의 formValidate/toast를 재사용한다.
   ========================================================================== */
(function (global) {
	"use strict";

	var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	function toast(msg, type) {
		if (global.SampleUI && typeof global.SampleUI.toast === "function") {
			global.SampleUI.toast(msg, type || "default");
		}
	}

	function formValidate(form) {
		if (global.SampleUI && typeof global.SampleUI.formValidate === "function") {
			return global.SampleUI.formValidate(form);
		}
		return true;
	}

	function isCryptReady() {
		return !!(global.PB && global.PB.crypt && typeof global.PB.crypt.encrypt === "function" && global.PB.crypt._crypt_module);
	}

	function encryptOrNull(plain) {
		if (!isCryptReady()) {
			return null;
		}
		var encrypted = global.PB.crypt.encrypt(plain);
		return encrypted === false ? null : encrypted;
	}

	function setFieldError(input, message) {
		var field = input.closest(".field") || input.parentElement;
		if (!field) {
			return;
		}
		field.classList.add("is-error");
		var errorEl = field.querySelector(".field-error");
		if (!errorEl) {
			errorEl = document.createElement("p");
			errorEl.className = "field-error";
			field.appendChild(errorEl);
		}
		errorEl.textContent = message;
	}

	function clearFieldError(input) {
		var field = input.closest(".field") || input.parentElement;
		if (!field) {
			return;
		}
		field.classList.remove("is-error");
		var errorEl = field.querySelector(".field-error");
		if (errorEl) {
			errorEl.parentNode.removeChild(errorEl);
		}
	}

	function setSubmitLoading(button, loading) {
		if (!button) {
			return;
		}
		button.disabled = !!loading;
		button.classList.toggle("is-disabled", !!loading);
		if (loading) {
			var defaultLabel = button.getAttribute("data-default-label");
			if (defaultLabel === null) {
				button.setAttribute("data-default-label", button.textContent);
			}
			button.textContent = button.getAttribute("data-loading-label") || button.textContent;
		} else {
			var label = button.getAttribute("data-default-label");
			if (label !== null) {
				button.textContent = label;
			}
		}
	}

	function postAjax(action, payload, onSuccess, onError) {
		if (!global.PB || typeof global.PB.post !== "function") {
			toast("페이지가 아직 준비되지 않았습니다. 새로고침 후 다시 시도하세요.", "danger");
			return;
		}

		global.PB.post(action, payload, function (result, responseJson) {
			if (!result || !responseJson || responseJson.success !== true) {
				var message = (responseJson && (responseJson.error_message || responseJson.error_title)) || "요청 처리 중 오류가 발생했습니다.";
				if (typeof onError === "function") {
					onError(message, responseJson);
				} else {
					toast(message, "danger");
				}
				return;
			}

			onSuccess(responseJson);
		});
	}

	/* ======================================================================
	   로그인
	   PB.crypt.encrypt(pw) → user-do-login → (서버)pb_crypt_decrypt→pb_user_login_by_both
	   ====================================================================== */
	function bindLoginForm() {
		var form = document.getElementById("sample-auth-login-form");
		if (!form) {
			return;
		}

		form.addEventListener("submit", function (e) {
			e.preventDefault();

			if (!formValidate(form)) {
				return;
			}

			var emailInput = form.querySelector("[name='user_email']");
			var passInput = form.querySelector("[name='user_pass']");
			var chipInput = form.querySelector("[name='_request_chip']");
			var submitBtn = form.querySelector("button[type='submit']");

			var encryptedPass = encryptOrNull(passInput.value);
			if (encryptedPass === null) {
				toast("보안 모듈을 불러오는 중입니다. 잠시 후 다시 시도하세요.", "danger");
				return;
			}

			setSubmitLoading(submitBtn, true);

			postAjax("user-do-login", {
				login_data: {
					user_email: emailInput.value.trim(),
					user_pass: encryptedPass,
					_request_chip: chipInput ? chipInput.value : ""
				}
			}, function (responseJson) {
				toast("로그인되었습니다.", "success");
				global.location.href = responseJson.redirect_url || "/";
			}, function (message) {
				setSubmitLoading(submitBtn, false);
				toast(message, "danger");
			});
		});
	}

	/* ======================================================================
	   회원가입
	   이메일 blur → user-check-email-exists(중복확인)
	   submit → PB.crypt.encrypt(pw) → user-do-signup
	   ====================================================================== */
	function bindSignupForm() {
		var form = document.getElementById("sample-auth-signup-form");
		if (!form) {
			return;
		}

		var emailInput = form.querySelector("[name='user_email']");
		var emailExists = false;

		if (emailInput) {
			emailInput.addEventListener("blur", function () {
				var value = emailInput.value.trim();
				if (!value || !EMAIL_RE.test(value)) {
					return;
				}

				postAjax("user-check-email-exists", { user_email: value }, function (responseJson) {
					emailExists = !!responseJson.exists;
					if (emailExists) {
						setFieldError(emailInput, "이미 가입된 이메일입니다.");
					} else {
						clearFieldError(emailInput);
					}
				}, function () {
					/* 중복확인 실패는 최종 제출 시 서버에서 다시 걸러진다 */
				});
			});
		}

		form.addEventListener("submit", function (e) {
			e.preventDefault();

			if (!formValidate(form)) {
				return;
			}

			if (emailExists) {
				setFieldError(emailInput, "이미 가입된 이메일입니다.");
				toast("이미 가입된 이메일입니다.", "danger");
				return;
			}

			var nameInput = form.querySelector("[name='user_name']");
			var passInput = form.querySelector("[name='user_pass']");
			var chipInput = form.querySelector("[name='_request_chip']");
			var submitBtn = form.querySelector("button[type='submit']");

			var encryptedPass = encryptOrNull(passInput.value);
			if (encryptedPass === null) {
				toast("보안 모듈을 불러오는 중입니다. 잠시 후 다시 시도하세요.", "danger");
				return;
			}

			setSubmitLoading(submitBtn, true);

			postAjax("user-do-signup", {
				signup_data: {
					user_email: emailInput.value.trim(),
					user_name: nameInput.value.trim(),
					user_pass: encryptedPass,
					_request_chip: chipInput ? chipInput.value : ""
				}
			}, function (responseJson) {
				toast("가입이 완료되었습니다.", "success");
				global.location.href = responseJson.redirect_url || "/";
			}, function (message) {
				setSubmitLoading(submitBtn, false);
				toast(message, "danger");
			});
		});
	}

	/* ======================================================================
	   비밀번호 재설정 — 1단계(이메일 발송 요청)
	   ====================================================================== */
	function bindResetpassRequestForm() {
		var form = document.getElementById("sample-auth-resetpass-request-form");
		if (!form) {
			return;
		}

		form.addEventListener("submit", function (e) {
			e.preventDefault();

			if (!formValidate(form)) {
				return;
			}

			var emailInput = form.querySelector("[name='user_email']");
			var submitBtn = form.querySelector("button[type='submit']");

			setSubmitLoading(submitBtn, true);

			postAjax("user-request-resetpass", {
				user_email: emailInput.value.trim()
			}, function () {
				setSubmitLoading(submitBtn, false);
				toast("재설정 메일을 발송했습니다. 메일함을 확인하세요.", "success");
				form.reset();
			}, function (message) {
				setSubmitLoading(submitBtn, false);
				toast(message, "danger");
			});
		});
	}

	/* ======================================================================
	   비밀번호 재설정 — 2단계(vkey 검증 후 새 비밀번호 저장)
	   ====================================================================== */
	function bindResetpassChangeForm() {
		var form = document.getElementById("sample-auth-resetpass-change-form");
		if (!form) {
			return;
		}

		form.addEventListener("submit", function (e) {
			e.preventDefault();

			if (!formValidate(form)) {
				return;
			}

			var passInput = form.querySelector("[name='user_pass']");
			var emailInput = form.querySelector("[name='user_email']");
			var vkeyInput = form.querySelector("[name='vkey']");
			var chipInput = form.querySelector("[name='_request_chip']");
			var submitBtn = form.querySelector("button[type='submit']");

			var encryptedPass = encryptOrNull(passInput.value);
			if (encryptedPass === null) {
				toast("보안 모듈을 불러오는 중입니다. 잠시 후 다시 시도하세요.", "danger");
				return;
			}

			setSubmitLoading(submitBtn, true);

			postAjax("user-do-resetpass", {
				resetpass_data: {
					user_email: emailInput.value,
					vkey: vkeyInput.value,
					user_pass: encryptedPass,
					_request_chip: chipInput ? chipInput.value : ""
				}
			}, function (responseJson) {
				toast("비밀번호가 변경되었습니다. 다시 로그인하세요.", "success");
				global.location.href = responseJson.redirect_url || "/login";
			}, function (message) {
				setSubmitLoading(submitBtn, false);
				toast(message, "danger");
			});
		});
	}

	ready(function () {
		bindLoginForm();
		bindSignupForm();
		bindResetpassRequestForm();
		bindResetpassChangeForm();
	});
})(window);

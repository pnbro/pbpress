/* ==========================================================================
   Sample Theme — Showcase Feature JS (devplan002 part009 → devplan004 part004)
   순수 바닐라 JS. jQuery 미사용(코어 PB.* API 호출 제외).
   ui.js(SampleUI)가 먼저 로드된 뒤, 이 파일은 pages/showcase/ajax-demo.php 에서
   개별적으로 <script>로 로드된다(header.php/footer.php 전역 로드 아님).
   각 섹션은 실제 엘리먼트가 있을 때만 동작하도록 존재 여부를 먼저 확인한다.

   devplan004 part004 — "UI킷 카탈로그"를 "코어 API 라이브 카탈로그"로
   재구성하면서 무한스크롤 데모(구 initInfiniteScrollDemo)는 제거했다.
   devplan004 part006 후속 — 이미지 갤러리/업로드 데모(gallery.php)는 제거했다.
   신규 라이브 데모는 기존 검증된 엔드포인트만 재사용한다:
     - sample-guestbook-list (① DO+쿼리빌더 검색)
     - sample-theme-ajax-test (④ 정상 AJAX)
     - sample-guestbook-add, 토큰 없이 호출 (④ CSRF 리젝 확인 — 삽입 안 됨, 안전)
   ========================================================================== */
(function (global) {
	"use strict";

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	function escapeHtml(value) {
		var div = document.createElement("div");
		div.textContent = value == null ? "" : String(value);
		return div.innerHTML;
	}

	/* ======================================================================
	   1) ① 선언형 DO + 쿼리빌더 — 방명록 검색 라이브 데모
	   sample-guestbook-list 를 코어 PB.post로 호출한다(읽기전용, 안전).
	   ====================================================================== */
	function initGuestbookSearchDemo() {
		var listEl = document.getElementById("showcase-guestbook-list");

		if (!listEl) {
			return;
		}

		var input = document.getElementById("showcase-guestbook-search-input");
		var searchBtn = document.getElementById("showcase-guestbook-search-btn");

		function renderItems(items) {
			if (!items || !items.length) {
				listEl.setAttribute("data-state", "empty");
				listEl.innerHTML = '<span class="text-sm text-muted">' + escapeHtml("검색 결과가 없습니다.") + "</span>";
				return;
			}

			listEl.setAttribute("data-state", "success");

			var html = "";
			for (var i = 0; i < items.length; i++) {
				var item = items[i];
				html +=
					'<div class="showcase-guestbook-item">' +
					'<div class="showcase-guestbook-item__head">' +
					'<span class="fw-medium">' + escapeHtml(item.writer) + "</span>" +
					'<span class="text-xs text-muted">' + escapeHtml(item.reg_date) + "</span>" +
					"</div>" +
					'<div class="text-sm">' + escapeHtml(item.content) + "</div>" +
					"</div>";
			}
			listEl.innerHTML = html;
		}

		function load(keyword) {
			if (typeof global.PB === "undefined" || !global.PB.post) {
				listEl.setAttribute("data-state", "error");
				listEl.innerHTML = '<span class="text-sm text-muted">' + escapeHtml("PB.post 코어 스크립트를 찾을 수 없습니다.") + "</span>";
				return;
			}

			listEl.setAttribute("data-state", "loading");
			listEl.innerHTML = '<span class="text-sm text-muted">' + escapeHtml("불러오는 중...") + "</span>";

			global.PB.post("sample-guestbook-list", { keyword: keyword || "", limit: 5 }, function (result_, response_json_) {
				if (!result_ || !response_json_ || response_json_.success !== true) {
					var errorMessage = (response_json_ && response_json_.error_message) || "요청이 실패했습니다.";
					listEl.setAttribute("data-state", "error");
					listEl.innerHTML = '<span class="text-sm text-muted">' + escapeHtml(errorMessage) + "</span>";
					return;
				}

				renderItems(response_json_.items || []);
			});
		}

		if (searchBtn) {
			searchBtn.addEventListener("click", function () {
				load(input ? input.value.trim() : "");
			});
		}

		if (input) {
			input.addEventListener("keydown", function (e) {
				if (e.key === "Enter") {
					e.preventDefault();
					load(input.value.trim());
				}
			});
		}

		/* 초기 5건 자동 로드 */
		load("");
	}

	/* ======================================================================
	   2) ④ AJAX + CSRF — 정상 요청 / 토큰 없는 위조 요청 차단 확인
	   두 데모 모두 DB에 부작용이 없다(정상 요청은 읽기전용, 위조 요청은
	   토큰 검증에서 거부되어 삽입되지 않는다).
	   ====================================================================== */
	function initAjaxCsrfDemo() {
		var okBtn = document.getElementById("showcase-ajax-ok-btn");
		var okResult = document.getElementById("showcase-ajax-ok-result");
		var csrfBtn = document.getElementById("showcase-ajax-csrf-btn");
		var csrfResult = document.getElementById("showcase-ajax-csrf-result");

		function setState(el, state, message) {
			if (!el) {
				return;
			}
			el.setAttribute("data-state", state);
			el.innerHTML = "";

			if (state === "loading") {
				var spinner = document.createElement("span");
				spinner.className = "showcase-spinner";
				el.appendChild(spinner);
			}

			var text = document.createElement("span");
			text.textContent = message;
			el.appendChild(text);
		}

		if (okBtn && okResult) {
			okBtn.addEventListener("click", function () {
				if (typeof global.PB === "undefined" || !global.PB.post) {
					setState(okResult, "error", "PB.post 코어 스크립트를 찾을 수 없습니다.");
					return;
				}

				okBtn.disabled = true;
				setState(okResult, "loading", "요청 전송 중...");

				global.PB.post("sample-theme-ajax-test", {}, function (result_, response_json_) {
					okBtn.disabled = false;

					if (!result_ || !response_json_ || response_json_.success !== true) {
						var errorMessage = (response_json_ && response_json_.error_message) || "요청이 실패했습니다.";
						setState(okResult, "error", errorMessage);
						return;
					}

					var total = response_json_.total;
					var message = "성공 — 실제 방명록 총 " + total + "건 조회됨";
					setState(okResult, "success", message);
					if (global.SampleUI && global.SampleUI.toast) {
						global.SampleUI.toast(message, "success");
					}
				});
			});
		}

		if (csrfBtn && csrfResult) {
			csrfBtn.addEventListener("click", function () {
				if (typeof global.PB === "undefined" || !global.PB.post) {
					setState(csrfResult, "error", "PB.post 코어 스크립트를 찾을 수 없습니다.");
					return;
				}

				csrfBtn.disabled = true;
				setState(csrfResult, "loading", "위조 요청 전송 중...");

				global.PB.post(
					"sample-guestbook-add",
					{ writer: "x", content: "토큰없는 시도", _request_chip: "" },
					function (result_, response_json_) {
						csrfBtn.disabled = false;

						if (response_json_ && response_json_.success === true) {
							/* 정상 흐름에서는 발생하지 않아야 함 — 토큰 검증이 통과된 예외 상황 */
							setState(csrfResult, "error", "예상과 다르게 요청이 통과되었습니다.");
							return;
						}

						var errorMessage = (response_json_ && response_json_.error_message) || "요청이 거부되었습니다.";
						setState(csrfResult, "success", "차단됨 — " + errorMessage);
						if (global.SampleUI && global.SampleUI.toast) {
							global.SampleUI.toast("CSRF 토큰이 없어 서버가 거부했습니다.", "info");
						}
					}
				);
			});
		}
	}


	ready(function () {
		initGuestbookSearchDemo();
		initAjaxCsrfDemo();
	});
})(window);

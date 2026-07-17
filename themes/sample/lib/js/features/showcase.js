/* ==========================================================================
   Sample Theme — Showcase Feature JS (devplan002 part009 → devplan004 part004)
   순수 바닐라 JS. jQuery 미사용(코어 PB.* API 호출 제외).
   ui.js(SampleUI)가 먼저 로드된 뒤, 이 파일은 pages/showcase/*.php 에서만
   개별적으로 <script>로 로드된다(header.php/footer.php 전역 로드 아님).
   ajax-demo.php / gallery.php 양쪽에 공통 로드되므로, 각 섹션은 해당 페이지에
   실제 엘리먼트가 있을 때만 동작하도록 존재 여부를 먼저 확인한다.

   devplan004 part004 — "UI킷 카탈로그"를 "코어 API 라이브 카탈로그"로
   재구성하면서 무한스크롤 데모(구 initInfiniteScrollDemo)는 제거했다.
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

	/* 업로드 데모(initUploadDemo) → 갤러리(initGalleryLightbox) 간 모듈 내부 연결.
	   전역(window) 오염 없이 IIFE 스코프 변수로만 공유한다. */
	var addGalleryItem = null;

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

	/* ======================================================================
	   3) 이미지 갤러리 — 라이트박스(모달 확장, 키보드 좌우 이동)
	   ====================================================================== */
	function initGalleryLightbox() {
		var grid = document.getElementById("showcase-gallery-grid");
		var lightbox = document.getElementById("showcase-lightbox");

		if (!grid || !lightbox) {
			return;
		}

		var mediaEl = document.getElementById("showcase-lightbox-media");
		var captionEl = document.getElementById("showcase-lightbox-caption");
		var prevBtn = document.getElementById("showcase-lightbox-prev");
		var nextBtn = document.getElementById("showcase-lightbox-next");

		var currentIndex = -1;

		function items() {
			return Array.prototype.slice.call(grid.querySelectorAll(".showcase-gallery__item"));
		}

		function showItem(index) {
			var list = items();
			if (!list.length) {
				return;
			}

			currentIndex = (index + list.length) % list.length;
			var target = list[currentIndex];
			var thumb = target.querySelector(".showcase-gallery__thumb");

			if (mediaEl && thumb) {
				mediaEl.innerHTML = "";
				var clone = thumb.firstElementChild ? thumb.firstElementChild.cloneNode(true) : thumb.cloneNode(true);
				clone.classList.add("showcase-lightbox__image");
				mediaEl.appendChild(clone);
			}

			if (captionEl) {
				captionEl.textContent = target.getAttribute("data-gallery-caption") || "";
			}
		}

		grid.addEventListener("click", function (e) {
			var item = e.target.closest ? e.target.closest(".showcase-gallery__item") : null;
			if (!item) {
				return;
			}

			var list = items();
			var index = list.indexOf(item);
			showItem(index);

			if (global.SampleUI && global.SampleUI.modal) {
				global.SampleUI.modal.open(lightbox);
			}
		});

		if (prevBtn) {
			prevBtn.addEventListener("click", function () {
				showItem(currentIndex - 1);
			});
		}
		if (nextBtn) {
			nextBtn.addEventListener("click", function () {
				showItem(currentIndex + 1);
			});
		}

		document.addEventListener("keydown", function (e) {
			if (!lightbox.classList.contains("is-open")) {
				return;
			}
			if (e.key === "ArrowLeft") {
				showItem(currentIndex - 1);
			} else if (e.key === "ArrowRight") {
				showItem(currentIndex + 1);
			}
		});

		/* 업로드 데모에서 새 아이템을 그리드에 동적으로 추가할 때 사용 */
		addGalleryItem = function (thumbHtml, caption) {
			var button = document.createElement("button");
			button.type = "button";
			button.className = "showcase-gallery__item";
			button.setAttribute("data-gallery-caption", caption || "");
			button.innerHTML =
				'<span class="showcase-gallery__thumb">' + thumbHtml + "</span>" +
				'<span class="showcase-gallery__caption">' + escapeHtml(caption || "") + "</span>";
			grid.appendChild(button);
		};
	}

	/* ======================================================================
	   4) 업로드 데모 — FileReader 미리보기 + 코어 PB.file.upload 연동
	   확장지점: 업로드 성공 후 file_resources 테이블에 실제 경로를 영구 저장하려면
	   file_resources DO(includes/common/fileupload.resource.const.php)에 파일
	   경로 컬럼을 추가하는 스키마 확장이 필요하다(본 파트 범위 밖, 코어 변경 필요).
	   현재는 업로드 결과(r_name/thumbnail)를 세션 내 갤러리에 즉시 반영만 한다.
	   ====================================================================== */
	function initUploadDemo() {
		var input = document.getElementById("showcase-upload-input");
		var submitBtn = document.getElementById("showcase-upload-submit");

		if (!input || !submitBtn) {
			return;
		}

		var previewWrap = document.getElementById("showcase-upload-preview");
		var previewImg = document.getElementById("showcase-upload-preview-img");
		var progressBar = document.getElementById("showcase-upload-progress-bar");
		var resultEl = document.getElementById("showcase-upload-result");

		function resetProgress() {
			if (progressBar) {
				progressBar.style.width = "0%";
			}
		}

		input.addEventListener("change", function () {
			var file = input.files && input.files[0];
			submitBtn.disabled = !file;
			resetProgress();

			if (resultEl) {
				resultEl.textContent = "";
			}

			if (!file) {
				if (previewWrap) {
					previewWrap.classList.add("hidden");
				}
				return;
			}

			if (!/^image\//.test(file.type)) {
				submitBtn.disabled = true;
				if (global.SampleUI && global.SampleUI.toast) {
					global.SampleUI.toast("이미지 파일만 선택할 수 있습니다.", "warning");
				}
				return;
			}

			if (typeof global.FileReader === "undefined") {
				return;
			}

			var reader = new global.FileReader();
			reader.onload = function (e) {
				if (previewImg) {
					previewImg.src = e.target.result;
				}
				if (previewWrap) {
					previewWrap.classList.remove("hidden");
				}
			};
			reader.readAsDataURL(file);
		});

		submitBtn.addEventListener("click", function () {
			var file = input.files && input.files[0];
			if (!file) {
				return;
			}

			if (typeof global.PB === "undefined" || !global.PB.file || !global.PB.file.upload) {
				if (global.SampleUI && global.SampleUI.toast) {
					global.SampleUI.toast("코어 업로드 API(PB.file)를 찾을 수 없습니다.", "danger");
				}
				return;
			}

			submitBtn.disabled = true;

			global.PB.file.upload([file], {
				progress: function (percent) {
					if (progressBar) {
						progressBar.style.width = Math.max(0, Math.min(100, percent)) + "%";
					}
				}
			}, function (result_, response_json_) {
				submitBtn.disabled = false;

				if (!response_json_ || response_json_.success !== true || !response_json_.files || !response_json_.files.length) {
					var message = (response_json_ && response_json_.error_message) || "업로드에 실패했습니다.";
					if (resultEl) {
						resultEl.textContent = message;
					}
					if (global.SampleUI && global.SampleUI.toast) {
						global.SampleUI.toast(message, "danger");
					}
					return;
				}

				var uploaded = response_json_.files[0];
				var path = uploaded.thumbnail || uploaded.r_name;
				var url = (global.PB.filebase_url ? global.PB.filebase_url(path) : null);

				if (resultEl) {
					resultEl.textContent = "업로드 완료: " + (uploaded.o_name || file.name);
				}
				if (global.SampleUI && global.SampleUI.toast) {
					global.SampleUI.toast("업로드가 완료되었습니다.", "success");
				}

				if (url && addGalleryItem) {
					addGalleryItem(
						'<img src="' + url + '" alt="' + escapeHtml(uploaded.o_name || "") + '">',
						uploaded.o_name || "업로드된 이미지"
					);
				}

				input.value = "";
				submitBtn.disabled = true;
			});
		});
	}

	ready(function () {
		initGuestbookSearchDemo();
		initAjaxCsrfDemo();
		initGalleryLightbox();
		initUploadDemo();
	});
})(window);

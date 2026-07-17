/* ============================================================
 * devplan002 part006 — 블로그 목록 무한스크롤(더보기)
 * 재사용: window.SampleUI.infiniteScroll (lib/js/ui.js, part001)
 *
 * 계약: 서버 AJAX(user-load-blog-list, includes/blog-components.php)는
 * response.items 를 "이미 ob_start로 렌더링된 카드 HTML 문자열 배열"로 반환한다.
 * SampleUI.infiniteScroll은 items[]를 renderItem()에 그대로 전달하는 구조이므로,
 * 여기서는 renderItem을 항등함수로 두어 서버 렌더 HTML을 그대로 삽입한다.
 *
 * 페이지네이션 폴백(#sample-blog-pagination-fallback)은 SSR로 항상 먼저 렌더되며
 * (점진적 향상 원칙), JS + IntersectionObserver 사용 가능 환경에서만 숨기고
 * 무한스크롤로 대체한다.
 * ============================================================ */
(function () {
	"use strict";

	function ready(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn);
		} else {
			fn();
		}
	}

	ready(function () {
		var sentinel = document.querySelector("#sample-blog-sentinel");
		if (!sentinel) {
			return;
		}
		if (typeof window.SampleUI === "undefined" || typeof window.SampleUI.infiniteScroll !== "function") {
			return;
		}

		var listEl = document.querySelector("#sample-blog-list-items");
		var skeleton = document.querySelector("#sample-blog-skeleton");
		var paginationFallback = document.querySelector("#sample-blog-pagination-fallback");
		var keywordInput = document.querySelector("#sample-blog-keyword");

		var limit = parseInt(sentinel.getAttribute("data-limit"), 10) || 12;
		var params = {
			keyword: keywordInput ? keywordInput.value : "",
			category: sentinel.getAttribute("data-category") || ""
		};

		SampleUI.infiniteScroll({
			el: sentinel,
			action: "user-load-blog-list",
			limit: limit,
			params: params,
			listEl: listEl,
			renderItem: function (item) {
				// 서버가 카드 HTML을 미리 만들어 보내므로 그대로 삽입(항등함수)
				return item;
			}
		});

		// IntersectionObserver 사용 가능한 JS 환경에서는 폴백 페이지네이션 숨김
		if ("IntersectionObserver" in window && paginationFallback) {
			paginationFallback.setAttribute("hidden", "");
		}

		// sentinel의 is-loading 클래스(part001 infiniteScroll이 로딩중 토글)를 관찰해
		// 스켈레톤 표시/숨김을 동기화한다.
		if (skeleton && "MutationObserver" in window) {
			var observer = new MutationObserver(function () {
				if (sentinel.classList.contains("is-loading")) {
					skeleton.removeAttribute("hidden");
				} else {
					skeleton.setAttribute("hidden", "");
				}
			});
			observer.observe(sentinel, { attributes: true, attributeFilter: ["class"] });
		}
	});
})();

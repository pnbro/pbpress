/* ==========================================================================
   Sample Theme — Nav feature (devplan002 part003)
   순수 바닐라 JS. jQuery 미사용. 전역 오염 없이 IIFE.
   part001 SampleUI.drawer(모바일 오프캔버스)는 그대로 재사용하고,
   여기서는 SampleUI.dropdown(클릭 전용)으로는 부족한
   "데스크탑 하위메뉴 hover + 키보드 focus 드롭다운"만 보강한다.
   ========================================================================== */
(function (global) {
	"use strict";

	var NAV_SELECTOR = ".pb-header__nav .nav-item.has-children";

	function isDesktop() {
		return typeof global.matchMedia === "function"
			? global.matchMedia("(min-width: 768px)").matches
			: global.innerWidth >= 768;
	}

	function getCaret(item) {
		return item.querySelector(":scope > .nav-caret") || item.querySelector(".nav-caret");
	}

	function openItem(item) {
		item.classList.add("is-open");
		var caret = getCaret(item);
		if (caret) {
			caret.setAttribute("aria-expanded", "true");
		}
	}

	function closeItem(item) {
		item.classList.remove("is-open");
		var caret = getCaret(item);
		if (caret) {
			caret.setAttribute("aria-expanded", "false");
		}
	}

	function closeAllItems(except) {
		var items = document.querySelectorAll(NAV_SELECTOR + ".is-open");
		for (var i = 0; i < items.length; i++) {
			if (items[i] !== except) {
				closeItem(items[i]);
			}
		}
	}

	function bindHoverAndFocus() {
		var items = document.querySelectorAll(NAV_SELECTOR);

		for (var i = 0; i < items.length; i++) {
			(function (item) {
				item.addEventListener("mouseenter", function () {
					if (!isDesktop()) {
						return;
					}
					closeAllItems(item);
					openItem(item);
				});

				item.addEventListener("mouseleave", function () {
					if (!isDesktop()) {
						return;
					}
					closeItem(item);
				});

				item.addEventListener("focusin", function () {
					closeAllItems(item);
					openItem(item);
				});

				item.addEventListener("focusout", function (e) {
					if (e.relatedTarget && item.contains(e.relatedTarget)) {
						return;
					}
					closeItem(item);
				});

				var caret = getCaret(item);
				if (caret) {
					caret.addEventListener("click", function (e) {
						e.preventDefault();
						if (item.classList.contains("is-open")) {
							closeItem(item);
						} else {
							closeAllItems(item);
							openItem(item);
						}
					});
				}
			})(items[i]);
		}
	}

	function bindOutsideClick() {
		document.addEventListener("click", function (e) {
			var insideNavItem = e.target.closest ? e.target.closest(NAV_SELECTOR) : null;
			if (!insideNavItem) {
				closeAllItems();
			}
		});
	}

	function bindEscape() {
		document.addEventListener("keydown", function (e) {
			if (e.key !== "Escape") {
				return;
			}
			var openItem_ = document.querySelector(NAV_SELECTOR + ".is-open");
			if (!openItem_) {
				return;
			}
			closeAllItems();
			var link = openItem_.querySelector(".nav-link");
			if (link) {
				link.focus();
			}
		});
	}

	/* 모바일 드로어에서 실제 링크(하위메뉴 토글 버튼 제외)를 클릭하면 드로어를 닫는다 */
	function bindMobileNavLinkClose() {
		var nav = document.getElementById("pb-main-nav");
		if (!nav) {
			return;
		}

		nav.addEventListener("click", function (e) {
			if (isDesktop()) {
				return;
			}
			var link = e.target.closest ? e.target.closest(".nav-link") : null;
			if (!link) {
				return;
			}
			if (global.SampleUI && global.SampleUI.drawer) {
				global.SampleUI.drawer.closeAll();
			}
		});
	}

	function init() {
		bindHoverAndFocus();
		bindOutsideClick();
		bindEscape();
		bindMobileNavLinkClose();
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})(window);

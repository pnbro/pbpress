/* ==========================================================================
   Sample Theme — UI Utilities (devplan002 part001)
   순수 바닐라 JS. jQuery 미사용. 전역 네임스페이스: window.SampleUI
   코어 AJAX(PB.post)는 래핑만 하고 손대지 않는다.
   data-attribute 자동 바인딩은 DOMContentLoaded에서 스캔한다.
   ========================================================================== */
(function (global) {
	"use strict";

	var SampleUI = {};

	/* ======================================================================
	   Toast
	   ====================================================================== */
	function ensureToastRegion() {
		var region = document.querySelector(".toast-region");
		if (!region) {
			region = document.createElement("div");
			region.className = "toast-region";
			document.body.appendChild(region);
		}
		return region;
	}

	SampleUI.toast = function (msg, type, duration) {
		type = type || "default";
		duration = duration || 3000;

		var region = ensureToastRegion();
		var el = document.createElement("div");
		el.className = "toast" + (type && type !== "default" ? " toast-" + type : "");
		el.setAttribute("role", "status");
		el.textContent = msg;
		region.appendChild(el);

		global.requestAnimationFrame(function () {
			el.classList.add("is-visible");
		});

		global.setTimeout(function () {
			el.classList.remove("is-visible");
			global.setTimeout(function () {
				if (el.parentNode) {
					el.parentNode.removeChild(el);
				}
			}, 220);
		}, duration);

		return el;
	};

	function bindToastTriggers() {
		document.addEventListener("click", function (e) {
			var trigger = e.target.closest ? e.target.closest("[data-toast]") : null;
			if (!trigger) {
				return;
			}
			e.preventDefault();
			SampleUI.toast(trigger.getAttribute("data-toast"), trigger.getAttribute("data-toast-type") || "default");
		});
	}

	/* ======================================================================
	   Modal
	   ====================================================================== */
	SampleUI.modal = {
		open: function (id) {
			var el = typeof id === "string" ? document.getElementById(id) : id;
			if (!el) {
				return;
			}
			el.classList.add("is-open");
			el.setAttribute("aria-hidden", "false");
			document.body.classList.add("is-modal-open");
		},
		close: function (id) {
			var el = typeof id === "string" ? document.getElementById(id) : id;
			if (!el) {
				return;
			}
			el.classList.remove("is-open");
			el.setAttribute("aria-hidden", "true");
			document.body.classList.remove("is-modal-open");
		},
		closeAll: function () {
			var openModals = document.querySelectorAll(".modal.is-open");
			for (var i = 0; i < openModals.length; i++) {
				SampleUI.modal.close(openModals[i]);
			}
		}
	};

	function bindModal() {
		document.addEventListener("click", function (e) {
			var openTrigger = e.target.closest ? e.target.closest("[data-modal-open]") : null;
			if (openTrigger) {
				e.preventDefault();
				SampleUI.modal.open(openTrigger.getAttribute("data-modal-open"));
				return;
			}

			var closeTrigger = e.target.closest ? e.target.closest("[data-modal-close]") : null;
			if (closeTrigger) {
				e.preventDefault();
				var modal = closeTrigger.closest(".modal");
				SampleUI.modal.close(modal);
				return;
			}

			if (e.target.classList && e.target.classList.contains("modal") && e.target.classList.contains("is-open")) {
				SampleUI.modal.close(e.target);
			}
		});

		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape") {
				SampleUI.modal.closeAll();
				SampleUI.drawer.closeAll();
				SampleUI.dropdown.closeAll();
			}
		});
	}

	/* ======================================================================
	   Drawer (모바일 메뉴 등 오프캔버스 공용)
	   ====================================================================== */
	function getOrCreateDrawerBackdrop() {
		var backdrop = document.querySelector(".drawer-backdrop");
		if (!backdrop) {
			backdrop = document.createElement("div");
			backdrop.className = "drawer-backdrop";
			document.body.appendChild(backdrop);
			backdrop.addEventListener("click", function () {
				SampleUI.drawer.closeAll();
			});
		}
		return backdrop;
	}

	SampleUI.drawer = {
		open: function (id) {
			var el = typeof id === "string" ? document.getElementById(id) : id;
			if (!el) {
				return;
			}
			el.classList.add("is-open");
			el.setAttribute("aria-hidden", "false");
			getOrCreateDrawerBackdrop().classList.add("is-open");
		},
		close: function (id) {
			var el = typeof id === "string" ? document.getElementById(id) : id;
			if (!el) {
				return;
			}
			el.classList.remove("is-open");
			el.setAttribute("aria-hidden", "true");
			var backdrop = document.querySelector(".drawer-backdrop");
			if (backdrop) {
				backdrop.classList.remove("is-open");
			}
		},
		toggle: function (id) {
			var el = typeof id === "string" ? document.getElementById(id) : id;
			if (!el) {
				return;
			}
			if (el.classList.contains("is-open")) {
				SampleUI.drawer.close(el);
			} else {
				SampleUI.drawer.open(el);
			}
		},
		closeAll: function () {
			var openDrawers = document.querySelectorAll(".drawer.is-open");
			for (var i = 0; i < openDrawers.length; i++) {
				SampleUI.drawer.close(openDrawers[i]);
			}
		}
	};

	function bindDrawer() {
		document.addEventListener("click", function (e) {
			var toggle = e.target.closest ? e.target.closest("[data-drawer-toggle]") : null;
			if (toggle) {
				e.preventDefault();
				SampleUI.drawer.toggle(toggle.getAttribute("data-drawer-toggle"));
				return;
			}

			var close = e.target.closest ? e.target.closest("[data-drawer-close]") : null;
			if (close) {
				e.preventDefault();
				var drawer = close.closest(".drawer");
				SampleUI.drawer.close(drawer);
			}
		});
	}

	/* ======================================================================
	   Tabs
	   ====================================================================== */
	SampleUI.tabs = {
		activate: function (group, target) {
			if (!group) {
				return;
			}
			var buttons = group.querySelectorAll("[data-tab]");
			for (var i = 0; i < buttons.length; i++) {
				var isActive = buttons[i].getAttribute("data-tab") === target;
				buttons[i].classList.toggle("is-active", isActive);
				buttons[i].setAttribute("aria-selected", isActive ? "true" : "false");
			}

			var root = group.closest("[data-tabs-root]") || document;
			var panels = root.querySelectorAll("[data-tab-panel]");
			for (var j = 0; j < panels.length; j++) {
				panels[j].classList.toggle("is-active", panels[j].getAttribute("data-tab-panel") === target);
			}
		}
	};

	function bindTabs() {
		document.addEventListener("click", function (e) {
			var btn = e.target.closest ? e.target.closest("[data-tab]") : null;
			if (!btn) {
				return;
			}
			e.preventDefault();
			var group = btn.closest(".tabs") || btn.parentElement;
			SampleUI.tabs.activate(group, btn.getAttribute("data-tab"));
		});
	}

	function initTabs() {
		var roots = document.querySelectorAll("[data-tabs-root]");
		for (var i = 0; i < roots.length; i++) {
			var root = roots[i];
			var activeBtn = root.querySelector("[data-tab].is-active") || root.querySelector("[data-tab]");
			if (activeBtn) {
				SampleUI.tabs.activate(activeBtn.closest(".tabs") || activeBtn.parentElement, activeBtn.getAttribute("data-tab"));
			}
		}
	}

	/* ======================================================================
	   Dropdown
	   ====================================================================== */
	function closeAllDropdowns(except) {
		var openDropdowns = document.querySelectorAll(".dropdown.is-open");
		for (var i = 0; i < openDropdowns.length; i++) {
			if (openDropdowns[i] !== except) {
				openDropdowns[i].classList.remove("is-open");
			}
		}
	}

	SampleUI.dropdown = {
		toggle: function (id) {
			var el = document.getElementById(id) || document.querySelector('[data-dropdown="' + id + '"]');
			if (!el) {
				return;
			}
			var willOpen = !el.classList.contains("is-open");
			closeAllDropdowns();
			el.classList.toggle("is-open", willOpen);
		},
		closeAll: closeAllDropdowns
	};

	function bindDropdown() {
		document.addEventListener("click", function (e) {
			var toggle = e.target.closest ? e.target.closest("[data-dropdown-toggle]") : null;
			if (toggle) {
				e.preventDefault();
				e.stopPropagation();
				SampleUI.dropdown.toggle(toggle.getAttribute("data-dropdown-toggle"));
				return;
			}
			if (!(e.target.closest && e.target.closest(".dropdown"))) {
				closeAllDropdowns();
			}
		});
	}

	/* ======================================================================
	   Form Validate
	   ====================================================================== */
	var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

	function validateField(input) {
		var value = (input.value || "").trim();
		var field = input.closest(".field") || input.parentElement;
		var error = "";

		if (input.hasAttribute("data-required") && !value) {
			error = input.getAttribute("data-required-message") || "필수 입력값입니다.";
		} else if (value && input.hasAttribute("data-email") && !EMAIL_RE.test(value)) {
			error = input.getAttribute("data-email-message") || "이메일 형식이 올바르지 않습니다.";
		} else if (value && input.hasAttribute("data-min") && value.length < parseInt(input.getAttribute("data-min"), 10)) {
			error = input.getAttribute("data-min-message") || ("최소 " + input.getAttribute("data-min") + "자 이상 입력하세요.");
		} else if (value && input.hasAttribute("data-max") && value.length > parseInt(input.getAttribute("data-max"), 10)) {
			error = input.getAttribute("data-max-message") || ("최대 " + input.getAttribute("data-max") + "자까지 입력 가능합니다.");
		} else if (value && input.hasAttribute("data-pattern") && !(new RegExp(input.getAttribute("data-pattern"))).test(value)) {
			error = input.getAttribute("data-pattern-message") || "형식이 올바르지 않습니다.";
		} else if (input.hasAttribute("data-match")) {
			var matchTarget = document.querySelector(input.getAttribute("data-match"));
			if (matchTarget && matchTarget.value !== value) {
				error = input.getAttribute("data-match-message") || "값이 일치하지 않습니다.";
			}
		}

		if (field) {
			var errorEl = field.querySelector(".field-error");
			if (error) {
				field.classList.add("is-error");
				if (!errorEl) {
					errorEl = document.createElement("p");
					errorEl.className = "field-error";
					field.appendChild(errorEl);
				}
				errorEl.textContent = error;
			} else {
				field.classList.remove("is-error");
				if (errorEl) {
					errorEl.parentNode.removeChild(errorEl);
				}
			}
		}

		return !error;
	}

	SampleUI.formValidate = function (form) {
		if (typeof form === "string") {
			form = document.querySelector(form);
		}
		if (!form) {
			return true;
		}

		var inputs = form.querySelectorAll("[data-required],[data-email],[data-min],[data-max],[data-pattern],[data-match]");
		var valid = true;
		for (var i = 0; i < inputs.length; i++) {
			if (!validateField(inputs[i])) {
				valid = false;
			}
		}
		return valid;
	};

	function bindFormValidate() {
		var forms = document.querySelectorAll("form[data-validate]");
		for (var i = 0; i < forms.length; i++) {
			(function (form) {
				if (form.getAttribute("data-validate-bound") === "1") {
					return;
				}
				form.setAttribute("data-validate-bound", "1");

				form.addEventListener("submit", function (e) {
					if (!SampleUI.formValidate(form)) {
						e.preventDefault();
						var firstError = form.querySelector(".is-error .input, .is-error .select, .is-error .textarea");
						if (firstError) {
							firstError.focus();
						}
					}
				});

				var inputs = form.querySelectorAll("[data-required],[data-email],[data-min],[data-max],[data-pattern],[data-match]");
				for (var j = 0; j < inputs.length; j++) {
					inputs[j].addEventListener("blur", function () {
						validateField(this);
					});
				}
			})(forms[i]);
		}
	}

	/* ======================================================================
	   Infinite Scroll — 코어 PB.post 래핑
	   ====================================================================== */
	SampleUI.infiniteScroll = function (options) {
		options = options || {};
		var el = typeof options.el === "string" ? document.querySelector(options.el) : options.el;
		if (!el) {
			return null;
		}

		var action = options.action;
		var limit = options.limit || 20;
		var params = options.params || {};
		var renderItem = typeof options.renderItem === "function" ? options.renderItem : null;
		var listEl = options.listEl
			? (typeof options.listEl === "string" ? document.querySelector(options.listEl) : options.listEl)
			: el;

		var state = { offset: 0, loading: false, done: false };

		function loadMore() {
			if (state.loading || state.done) {
				return;
			}
			if (typeof global.PB === "undefined" || !global.PB.post) {
				return;
			}

			state.loading = true;
			el.classList.add("is-loading");

			var payload = {};
			for (var k in params) {
				if (Object.prototype.hasOwnProperty.call(params, k)) {
					payload[k] = params[k];
				}
			}
			payload.offset = state.offset;
			payload.limit = limit;

			global.PB.post(action, payload, function (result_, response_json_) {
				state.loading = false;
				el.classList.remove("is-loading");

				if (!result_ || !response_json_ || response_json_.success !== true) {
					return;
				}

				var items = response_json_.items || response_json_.list || [];
				if (!items.length) {
					state.done = true;
					return;
				}

				if (renderItem && listEl) {
					for (var i = 0; i < items.length; i++) {
						listEl.insertAdjacentHTML("beforeend", renderItem(items[i]));
					}
				}

				state.offset += items.length;
				if (items.length < limit) {
					state.done = true;
				}
			});
		}

		var observer = null;
		if ("IntersectionObserver" in global) {
			observer = new IntersectionObserver(function (entries) {
				for (var i = 0; i < entries.length; i++) {
					if (entries[i].isIntersecting) {
						loadMore();
					}
				}
			});
			observer.observe(el);
		}

		return {
			loadMore: loadMore,
			state: state,
			destroy: function () {
				if (observer) {
					observer.disconnect();
				}
			}
		};
	};

	function initInfiniteScroll() {
		var nodes = document.querySelectorAll("[data-infinite-scroll]");
		for (var i = 0; i < nodes.length; i++) {
			var el = nodes[i];
			if (el.getAttribute("data-infinite-bound") === "1") {
				continue;
			}
			el.setAttribute("data-infinite-bound", "1");

			SampleUI.infiniteScroll({
				el: el,
				action: el.getAttribute("data-action"),
				limit: parseInt(el.getAttribute("data-limit"), 10) || 20,
				listEl: el.getAttribute("data-list-target") ? document.querySelector(el.getAttribute("data-list-target")) : el
			});
		}
	}

	/* ======================================================================
	   Init — DOMContentLoaded에서 data-attribute 스캔 자동 바인딩
	   ====================================================================== */
	function init() {
		bindModal();
		bindDrawer();
		bindTabs();
		bindDropdown();
		bindFormValidate();
		bindToastTriggers();
		initTabs();
		initInfiniteScroll();
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}

	global.SampleUI = SampleUI;
})(window);

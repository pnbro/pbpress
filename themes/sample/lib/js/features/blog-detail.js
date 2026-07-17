/* ==========================================================================
   devplan002 part007 — 블로그 상세: 댓글 작성/삭제/재조회
   순수 바닐라 JS. jQuery 미사용. 코어 AJAX(PB.post) + SampleUI(part001) 재사용.
   AJAX 액션: user-blog-comment-add / user-blog-comment-list / user-blog-comment-delete
   ========================================================================== */
(function (global) {
	"use strict";

	function qs(sel, ctx) {
		return (ctx || document).querySelector(sel);
	}

	function notify(message, type) {
		if (global.SampleUI && typeof global.SampleUI.toast === "function") {
			global.SampleUI.toast(message, type);
		}
	}

	function errorMessage(response, fallback) {
		if (response && (response.error_message || response.error_title)) {
			return response.error_message || response.error_title;
		}
		return fallback;
	}

	function refreshCommentList(section) {
		if (typeof global.PB === "undefined" || !global.PB.post) {
			return;
		}

		var postId = section.getAttribute("data-post-id");
		var listEl = qs("[data-blog-comment-list]", section);
		if (!listEl) {
			return;
		}

		global.PB.post("user-blog-comment-list", { post_id: postId }, function (result, response) {
			if (!result || !response || response.success !== true) {
				return;
			}

			listEl.innerHTML = response.html || "";

			var countEl = qs("[data-comment-count]", section);
			if (countEl && typeof response.count !== "undefined") {
				countEl.textContent = response.count;
			}
		});
	}

	function bindCommentForm(section) {
		var form = qs("[data-blog-comment-form]", section);
		if (!form || form.getAttribute("data-blog-comment-bound") === "1") {
			return;
		}
		form.setAttribute("data-blog-comment-bound", "1");

		form.addEventListener("submit", function (e) {
			e.preventDefault();

			if (global.SampleUI && typeof global.SampleUI.formValidate === "function" && !global.SampleUI.formValidate(form)) {
				return;
			}
			if (typeof global.PB === "undefined" || !global.PB.post) {
				return;
			}

			var postId = section.getAttribute("data-post-id");
			var textarea = form.querySelector("textarea[name=\"content\"]");
			var content = textarea ? textarea.value.trim() : "";
			var submitBtn = form.querySelector("button[type=\"submit\"]");

			if (!content) {
				return;
			}
			if (submitBtn) {
				submitBtn.disabled = true;
			}

			global.PB.post("user-blog-comment-add", { post_id: postId, content: content }, function (result, response) {
				if (submitBtn) {
					submitBtn.disabled = false;
				}

				if (!result || !response || response.success !== true) {
					notify(errorMessage(response, "댓글 등록에 실패했습니다."), "danger");
					return;
				}

				if (textarea) {
					textarea.value = "";
				}

				refreshCommentList(section);
				notify("댓글이 등록되었습니다.", "success");
			});
		});
	}

	function bindCommentDelete(section) {
		if (section.getAttribute("data-blog-delete-bound") === "1") {
			return;
		}
		section.setAttribute("data-blog-delete-bound", "1");

		section.addEventListener("click", function (e) {
			var trigger = e.target.closest ? e.target.closest("[data-blog-comment-delete]") : null;
			if (!trigger) {
				return;
			}
			e.preventDefault();

			if (typeof global.PB === "undefined" || !global.PB.post) {
				return;
			}
			if (!global.confirm("댓글을 삭제하시겠습니까?")) {
				return;
			}

			var commentId = trigger.getAttribute("data-blog-comment-delete");

			global.PB.post("user-blog-comment-delete", { comment_id: commentId }, function (result, response) {
				if (!result || !response || response.success !== true) {
					notify(errorMessage(response, "댓글 삭제에 실패했습니다."), "danger");
					return;
				}

				refreshCommentList(section);
				notify("댓글이 삭제되었습니다.", "success");
			});
		});
	}

	function init() {
		var sections = document.querySelectorAll("#blog-comments[data-post-id]");
		for (var i = 0; i < sections.length; i++) {
			bindCommentForm(sections[i]);
			bindCommentDelete(sections[i]);
		}
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})(window);

<?php

if(!defined('PB_DOCUMENT_PATH')){
	die('-1');
}

/* ============================================================
 * devplan004 part002 — 방명록 데모 프론트 렌더(정의만)
 * ------------------------------------------------------------
 * sample_guestbook_demo_render() : 작성 폼 + 목록 컨테이너 마크업을
 * 출력하는 재사용 함수. 실제 홈/showcase 배치는 part003/part004가
 * 담당하며, 이 파일에서는 함수 정의와 최소 동작(인라인 JS)만 제공한다.
 * 데이터 왕복은 includes/demo-backend.php 의 AJAX 3종을 코어 PB.post로
 * 호출한다. jQuery 미사용(바닐라 JS), 마크업은 README-tokens.md의
 * 재사용 클래스(.card .field .label .input .textarea .btn .btn-primary)만
 * 사용한다.
 * ============================================================ */

function sample_guestbook_demo_render($parameter_ = array()){
	$id_ = isset($parameter_['id']) && strlen($parameter_['id']) ? $parameter_['id'] : 'sample-guestbook-demo';
	$request_token_ = pb_request_token('sample-guestbook');
	?>
<div class="card sample-guestbook" id="<?=htmlspecialchars($id_)?>" data-guestbook-root>
	<div class="card-body">
		<form class="sample-guestbook__form" data-guestbook-form>
			<input type="hidden" name="_request_chip" value="<?=htmlspecialchars($request_token_)?>" data-guestbook-token>
			<div class="field">
				<label class="label" for="<?=htmlspecialchars($id_)?>-writer"><?=__('작성자', PB_THEME_DOMAIN)?></label>
				<input type="text" class="input" id="<?=htmlspecialchars($id_)?>-writer" name="writer" maxlength="60" placeholder="<?=__('이름을 입력하세요', PB_THEME_DOMAIN)?>" data-required>
			</div>
			<div class="field">
				<label class="label" for="<?=htmlspecialchars($id_)?>-content"><?=__('내용', PB_THEME_DOMAIN)?></label>
				<textarea class="textarea" id="<?=htmlspecialchars($id_)?>-content" name="content" maxlength="500" rows="3" placeholder="<?=__('방명록 내용을 입력하세요 (최대 500자)', PB_THEME_DOMAIN)?>" data-required></textarea>
			</div>
			<button type="submit" class="btn btn-primary"><?=__('등록', PB_THEME_DOMAIN)?></button>
		</form>
		<ul class="sample-guestbook__list" data-guestbook-list>
			<li class="text-muted text-sm" data-guestbook-loading><?=__('불러오는 중...', PB_THEME_DOMAIN)?></li>
		</ul>
	</div>
</div>
<script>
(function(){
	"use strict";

	function qs(sel, ctx){ return (ctx || document).querySelector(sel); }
	function qsa(sel, ctx){ return (ctx || document).querySelectorAll(sel); }

	function notify(message, type){
		if(window.SampleUI && typeof window.SampleUI.toast === "function"){
			window.SampleUI.toast(message, type);
		}
	}
	function errorMessage(response, fallback){
		if(response && (response.error_message || response.error_title)){
			return response.error_message || response.error_title;
		}
		return fallback;
	}
	function escapeHtml(str){
		return String(str == null ? "" : str).replace(/[&<>"']/g, function(ch){
			return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[ch];
		});
	}

	function renderItem(item){
		var deleteLabel = "<?=__('삭제', PB_THEME_DOMAIN)?>";
		var deleteBtn = item.can_delete
			? '<button type="button" class="btn btn-link btn-sm" data-guestbook-delete="' + item.id + '">' + deleteLabel + '</button>'
			: '';

		return '<li class="sample-guestbook__item" data-guestbook-item="' + item.id + '">' +
			'<div class="sample-guestbook__item-meta">' +
				'<span class="fw-medium">' + escapeHtml(item.writer) + '</span>' +
				'<span class="text-muted text-sm">' + escapeHtml(item.reg_date) + '</span>' +
				deleteBtn +
			'</div>' +
			'<p class="sample-guestbook__item-content">' + escapeHtml(item.content) + '</p>' +
		'</li>';
	}

	function loadList(root){
		var listEl = qs("[data-guestbook-list]", root);
		if(!listEl || typeof window.PB === "undefined" || !window.PB.post) return;

		window.PB.post("sample-guestbook-list", { limit: 20 }, function(result, response){
			if(!result || !response || response.success !== true){
				notify(errorMessage(response, "<?=__('목록을 불러오지 못했습니다.', PB_THEME_DOMAIN)?>"), "danger");
				return;
			}

			var items = response.items || [];
			if(items.length <= 0){
				listEl.innerHTML = '<li class="text-muted text-sm">' + "<?=__('아직 등록된 방명록이 없습니다.', PB_THEME_DOMAIN)?>" + '</li>';
				return;
			}

			var html = "";
			for(var i = 0; i < items.length; i++){
				html += renderItem(items[i]);
			}
			listEl.innerHTML = html;
		});
	}

	function bindForm(root){
		var form = qs("[data-guestbook-form]", root);
		if(!form || form.getAttribute("data-guestbook-bound") === "1") return;
		form.setAttribute("data-guestbook-bound", "1");

		form.addEventListener("submit", function(e){
			e.preventDefault();

			if(window.SampleUI && typeof window.SampleUI.formValidate === "function" && !window.SampleUI.formValidate(form)) return;
			if(typeof window.PB === "undefined" || !window.PB.post) return;

			var writerEl = form.querySelector('[name="writer"]');
			var contentEl = form.querySelector('[name="content"]');
			var tokenEl = form.querySelector('[data-guestbook-token]');
			var submitBtn = form.querySelector('button[type="submit"]');

			var payload = {
				writer: writerEl ? writerEl.value.trim() : "",
				content: contentEl ? contentEl.value.trim() : "",
				_request_chip: tokenEl ? tokenEl.value : ""
			};

			if(!payload.content) return;

			if(submitBtn) submitBtn.disabled = true;

			window.PB.post("sample-guestbook-add", payload, function(result, response){
				if(submitBtn) submitBtn.disabled = false;

				if(!result || !response || response.success !== true){
					notify(errorMessage(response, "<?=__('등록에 실패했습니다.', PB_THEME_DOMAIN)?>"), "danger");
					return;
				}

				if(contentEl) contentEl.value = "";
				notify("<?=__('방명록이 등록되었습니다.', PB_THEME_DOMAIN)?>", "success");
				loadList(root);
			});
		});
	}

	function bindDelete(root){
		if(root.getAttribute("data-guestbook-delete-bound") === "1") return;
		root.setAttribute("data-guestbook-delete-bound", "1");

		root.addEventListener("click", function(e){
			var trigger = e.target.closest ? e.target.closest("[data-guestbook-delete]") : null;
			if(!trigger) return;
			e.preventDefault();

			if(typeof window.PB === "undefined" || !window.PB.post) return;
			if(!window.confirm("<?=__('방명록을 삭제하시겠습니까?', PB_THEME_DOMAIN)?>")) return;

			var tokenEl = qs('[data-guestbook-token]', root);

			window.PB.post("sample-guestbook-delete", {
				id: trigger.getAttribute("data-guestbook-delete"),
				_request_chip: tokenEl ? tokenEl.value : ""
			}, function(result, response){
				if(!result || !response || response.success !== true){
					notify(errorMessage(response, "<?=__('삭제에 실패했습니다.', PB_THEME_DOMAIN)?>"), "danger");
					return;
				}
				notify("<?=__('삭제되었습니다.', PB_THEME_DOMAIN)?>", "success");
				loadList(root);
			});
		});
	}

	function init(){
		var roots = qsa("[data-guestbook-root]");
		for(var i = 0; i < roots.length; i++){
			bindForm(roots[i]);
			bindDelete(roots[i]);
			loadList(roots[i]);
		}
	}

	if(document.readyState === "loading"){
		document.addEventListener("DOMContentLoaded", init);
	}else{
		init();
	}
})();
</script>
	<?php
}

?>

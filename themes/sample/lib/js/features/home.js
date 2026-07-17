/* ==========================================================================
   Sample Theme — Home scroll reveal (devplan002 part002)
   순수 바닐라(jQuery 미사용), 전역 오염 없이 IIFE로 스코프 격리.
   lib/js/ui.js(SampleUI, 공유파일)는 병렬 작업 충돌 방지를 위해 건드리지
   않고, 이 파트 전용 신규 파일로 분리했다.

   대상: [data-reveal] 속성이 붙은 요소. 뷰포트에 들어오면 .is-revealed를
   추가해 lib/css/features/home.css의 트랜지션으로 페이드인 처리한다.
   IntersectionObserver 미지원 브라우저/reduced-motion 환경에서는 즉시 표시.
   ========================================================================== */
(function(){
	"use strict";

	function revealAll(targets_){
		for(var i = 0; i < targets_.length; i++){
			targets_[i].classList.add("is-revealed");
		}
	}

	function init(){
		var targets_ = document.querySelectorAll("[data-reveal]");
		if(!targets_.length){
			return;
		}

		var prefersReducedMotion_ = window.matchMedia
			&& window.matchMedia("(prefers-reduced-motion: reduce)").matches;

		if(prefersReducedMotion_ || !("IntersectionObserver" in window)){
			revealAll(targets_);
			return;
		}

		var observer_ = new IntersectionObserver(function(entries_){
			for(var i = 0; i < entries_.length; i++){
				var entry_ = entries_[i];
				if(entry_.isIntersecting){
					entry_.target.classList.add("is-revealed");
					observer_.unobserve(entry_.target);
				}
			}
		}, {
			threshold: 0.15,
			rootMargin: "0px 0px -40px 0px"
		});

		for(var j = 0; j < targets_.length; j++){
			observer_.observe(targets_[j]);
		}
	}

	if(document.readyState === "loading"){
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})();

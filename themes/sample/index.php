<?php pb_theme_header(); ?>

<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/home.css">

<!-- ============================================================
     devplan004 part003 — 홈 (히어로 + 코어 실지표 + 백엔드 셀링포인트
     + 라이브 방명록 위젯 + 기능 그리드 + CTA)
     "pbpress = 프론트 UI킷"으로 오해되는 배너/문구를 걷어내고
     첫 화면에서 백엔드 실기능이 바로 읽히도록 재작성했다.
     part001 클래스(.card,.btn-primary,.grid/.col-*)만 사용, 아이콘은 인라인 SVG.
     ============================================================ -->

<div class="container">

	<!-- 히어로 -->
	<?php
		/* devplan002 part008 — 관리자 옵션값 있으면 사용, 없으면 기본 문구(다국어 __() 적용) 폴백 */
		$sample_theme_hero_title_ = pb_option_value('sample_theme_hero_title');
		$sample_theme_hero_subtitle_ = pb_option_value('sample_theme_hero_subtitle');
		if(!strlen($sample_theme_hero_title_)){
			$sample_theme_hero_title_ = __('관리자 화면과 데이터 모델을<br>코드 몇 줄로 완성', PB_THEME_DOMAIN);
		}
		if(!strlen($sample_theme_hero_subtitle_)){
			$sample_theme_hero_subtitle_ = __('회원·콘텐츠·페이지빌더·권한을 실제로 동작하는 데모로 확인하세요. 이 페이지의 방명록도 진짜 DB로 돌아갑니다.', PB_THEME_DOMAIN);
		}
	?>
	<section class="pb-hero text-center" data-reveal>
		<h1 class="pb-hero__title"><?=$sample_theme_hero_title_?></h1>
		<p class="pb-hero__lead"><?=$sample_theme_hero_subtitle_?></p>
		<div class="flex justify-center gap-3 flex-wrap mt-4">
			<a class="btn btn-primary btn-lg" href="#sample-guestbook-live"><?=__('라이브 데모 보기', PB_THEME_DOMAIN)?></a>
			<a class="btn btn-ghost btn-lg" href="<?=pb_home_url("showcase")?>"><?=__('코어 기능 둘러보기', PB_THEME_DOMAIN)?></a>
		</div>
	</section>

	<!-- 코어 실지표 -->
	<section id="sample-stats" class="sample-stats mt-9 p-6" data-reveal>
		<p class="text-center text-muted text-sm mb-4"><?=__('이 테마가 pbpress 코어에 실제로 얹은 백엔드 확장 규모입니다. 숫자는 테마 소스에서 실시간 집계됩니다.', PB_THEME_DOMAIN)?></p>
		<div class="grid">
			<?php
			$m_ = sample_theme_metrics();
			$stats_ = array(
				array('value' => (string)$m_['hooks'],     'label' => __('등록한 훅 포인트', PB_THEME_DOMAIN)),
				array('value' => (string)$m_['routes'],    'label' => __('URL 라우트', PB_THEME_DOMAIN)),
				array('value' => (string)$m_['ajax'],      'label' => __('AJAX 엔드포인트', PB_THEME_DOMAIN)),
				array('value' => (string)$m_['do_tables'], 'label' => __('자동생성 DB 테이블', PB_THEME_DOMAIN)),
			);
			foreach($stats_ as $stat_){
				sample_stat_col($stat_);
			}
			?>
		</div>
	</section>

	<!-- 왜 프론트가 아니라 백엔드인가 (코어 API 카드) -->
	<section class="mt-9">
		<h2 class="text-center"><?=__('왜 프론트가 아니라 백엔드인가', PB_THEME_DOMAIN)?></h2>
		<p class="text-center text-muted mb-6"><?=__('pbpress 코어가 제공하는 API를 테마 코드 몇 줄로 얹은 결과입니다.', PB_THEME_DOMAIN)?></p>

		<div class="grid">
			<?php
			$core_cards_ = array(
				array(
					'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M14 4v5h5"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>',
					'title' => __('선언형 DO — 표 선언 한 번으로 테이블·CRUD 자동', PB_THEME_DOMAIN),
					'desc' => __('표를 선언하면 코어가 테이블 생성과 CRUD를 대신 처리합니다.', PB_THEME_DOMAIN).'<br><code class="sample-code-inline">pbdb_data_object("guestbook", [...])</code>',
					'href' => pb_home_url("showcase"),
				),
				array(
					'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"/></svg>',
					'title' => __('훅 확장 — 코어 수정 0줄로 기능 얹기', PB_THEME_DOMAIN),
					'desc' => __('필터/액션 훅에 콜백을 걸어 코어 동작을 코드 수정 없이 확장합니다.', PB_THEME_DOMAIN).'<br><code class="sample-code-inline">pb_hook_add_filter(&#39;pb_post_statement&#39;, ...)</code>',
					'href' => pb_home_url("showcase"),
				),
				array(
					'icon' => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v6c0 5 4 8.5 9 9 5-.5 9-4 9-9V7l-9-5Z"/><path d="m9 12 2 2 4-4"/></svg>',
					'title' => __('권한·보안 — CSRF·RSA·브루트포스 방지 내장', PB_THEME_DOMAIN),
					'desc' => __('요청 위조 방지·암호화·반복 시도 차단이 코어에 이미 들어 있습니다.', PB_THEME_DOMAIN).'<br><code class="sample-code-inline">pb_verify_request_token(...)</code>',
					'href' => pb_home_url("showcase"),
				),
			);
			foreach($core_cards_ as $core_card_){
				sample_card_col($core_card_);
			}
			?>
		</div>
	</section>

	<!-- 라이브 데모 — 방명록 (실 DB 연동) -->
	<section id="sample-guestbook-live" class="mt-9">
		<h2 class="text-center"><?=__('라이브 데모 — 방명록', PB_THEME_DOMAIN)?></h2>
		<p class="text-center text-muted mb-6"><?=__('아래 방명록은 sample_guestbook 테이블과 실제로 주고받습니다. 직접 남겨보세요.', PB_THEME_DOMAIN)?></p>
		<?php sample_guestbook_demo_render(); ?>
	</section>

	<!-- 기능 그리드 -->
	<section id="sample-features" class="mt-9">
		<h2 class="text-center"><?=__('핵심 기능', PB_THEME_DOMAIN)?></h2>
		<p class="text-center text-muted mb-6"><?=__('pbpress 코어가 제공하는 대표 기능을 카드로 정리했습니다.', PB_THEME_DOMAIN)?></p>

		<div class="grid">
			<?php
			$features_ = array(
				array(
					'icon' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"/></svg>',
					'title' => __('AJAX · 라우팅', PB_THEME_DOMAIN),
					'desc' => __('비동기 요청 처리와 사용자 정의 URL 라우팅을 코어 기능만으로 구성합니다. 이 페이지의 방명록도 같은 방식으로 동작합니다.', PB_THEME_DOMAIN),
				),
				array(
					'icon' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
					'title' => __('페이지빌더', PB_THEME_DOMAIN),
					'desc' => __('관리자가 드래그 앤 드롭으로 배치하는 요소(엘리먼트)를 테마가 직접 등록·확장합니다.', PB_THEME_DOMAIN),
				),
				array(
					'icon' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v6c0 5 4 8.5 9 9 5-.5 9-4 9-9V7l-9-5Z"/><path d="m9 12 2 2 4-4"/></svg>',
					'title' => __('회원 시스템', PB_THEME_DOMAIN),
					'desc' => __('로그인/가입/비밀번호 재설정과 마이페이지까지 기본 흐름을 갖추고 있으며, 방명록 삭제 권한도 이 시스템으로 검증합니다.', PB_THEME_DOMAIN),
				),
				array(
					'icon' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M14 4v5h5"/></svg>',
					'title' => __('콘텐츠·게시판', PB_THEME_DOMAIN),
					'desc' => __('블로그 목록/카테고리/검색과 상세·댓글까지 콘텐츠 타입 기반으로 동작합니다.', PB_THEME_DOMAIN),
				),
			);
			foreach($features_ as $feature_){
				sample_feature_col($feature_);
			}
			?>
		</div>
	</section>

	<!-- CTA 배너 -->
	<section class="sample-cta-banner text-center mt-9 p-7" data-reveal>
		<h2 class="sample-cta-banner__title"><?=__('지금 바로 pbpress 백엔드를 경험해보세요', PB_THEME_DOMAIN)?></h2>
		<p class="sample-cta-banner__desc"><?=__('회원가입 없이도 방명록에 직접 글을 남기고, 코어 기능 데모를 둘러볼 수 있습니다.', PB_THEME_DOMAIN)?></p>
		<div class="flex justify-center gap-3 flex-wrap mt-3">
			<a class="btn btn-primary btn-lg" href="#sample-guestbook-live"><?=__('방명록 남기기', PB_THEME_DOMAIN)?></a>
			<a class="btn btn-ghost btn-lg" href="<?=pb_home_url("showcase")?>"><?=__('코어 기능 데모 보기', PB_THEME_DOMAIN)?></a>
		</div>
	</section>

</div>

<script src="<?=pb_current_theme_url()?>lib/js/features/home.js"></script>

<?php pb_theme_footer(); ?>

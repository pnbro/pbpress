<!DOCTYPE html>
<html lang="ko">

<head>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>PBPress Sample Theme</title>

	<!-- Design System (devplan002 part001) — bootstrap.min.css 참조 제거됨 -->
	<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/tokens.css">
	<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/base.css">
	<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/components.css">
	<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/theme.css">
	<!-- 메뉴 네비게이션 전용 스타일 (devplan002 part003) -->
	<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/nav.css">
	<!-- 사이트운영(테마설정/다국어) 전용 스타일 (devplan002 part008) -->
	<link rel="stylesheet" type="text/css" href="<?=pb_current_theme_url()?>lib/css/features/site.css">

	<?php
		/* devplan002 part008 — 관리자 포인트 컬러 옵션을 디자인 토큰(--c-primary)에 인라인 오버라이드로 주입 */
		$sample_theme_primary_color_ = pb_option_value('sample_theme_primary_color');
		if(strlen($sample_theme_primary_color_)){
	?>
	<style>:root{--c-primary: <?=htmlspecialchars($sample_theme_primary_color_)?>;}</style>
	<?php } ?>

	<?php pb_head(); ?>

</head>

<body>

	<header class="pb-header">
		<div class="container pb-header__inner">
			<?php
				/* devplan002 part008 — 로고(텍스트/이미지) 옵션 반영, 값 없으면 기존 문구 폴백 */
				$sample_theme_logo_text_ = pb_option_value('sample_theme_logo_text');
				$sample_theme_logo_image_ = pb_option_value('sample_theme_logo_image');
				$sample_theme_logo_alt_ = strlen($sample_theme_logo_text_) ? $sample_theme_logo_text_ : 'PBPress Sample';
			?>
			<a class="pb-header__brand" href="<?=pb_home_url()?>">
				<?php if(strlen($sample_theme_logo_image_)){ ?>
					<img class="pb-header__logo-img" src="<?=htmlspecialchars($sample_theme_logo_image_)?>" alt="<?=htmlspecialchars($sample_theme_logo_alt_)?>">
				<?php }else{ ?>
					<?=htmlspecialchars($sample_theme_logo_alt_)?>
				<?php } ?>
			</a>

			<button type="button" class="pb-header__toggle" data-drawer-toggle="pb-main-nav" aria-controls="pb-main-nav" aria-expanded="false" aria-label="<?=__('메뉴 열기', PB_THEME_DOMAIN)?>">
				<span class="pb-header__toggle-bar"></span>
				<span class="pb-header__toggle-bar"></span>
				<span class="pb-header__toggle-bar"></span>
			</button>

			<!-- 데스크탑: 수평 메뉴 + 하위 드롭다운(hover/키보드 focus, lib/css/features/nav.css + lib/js/features/nav.js)
			     모바일: 햄버거 → part001 드로어 셸(SampleUI.drawer) 재사용, devplan002 part003 -->
			<nav id="pb-main-nav" class="pb-header__nav drawer" aria-label="<?=__('주 메뉴', PB_THEME_DOMAIN)?>">
				<div class="drawer__header only-mobile">
					<span class="pb-header__brand"><?=__('메뉴', PB_THEME_DOMAIN)?></span>
					<button type="button" class="btn btn-ghost btn-sm" data-drawer-close aria-label="<?=__('메뉴 닫기', PB_THEME_DOMAIN)?>"><?=__('닫기', PB_THEME_DOMAIN)?></button>
				</div>

				<?php
					$main_menu_id_ = pb_option_value("sample_theme_menu_id");
					pb_menu_render(array(
						'menu_slug' => $main_menu_id_,
						'walker' => 'PBMenuWalker_sample_mainmenu',
					)); ?>

				<?php
					/*
					 * 로그인/마이페이지 링크 슬롯 — part004(로그인/가입)·part005(마이페이지) 연동 자리.
					 * pb_is_logined() 등은 아직 코어에 없을 수 있어 function_exists 가드로
					 * 안전하게 폴백하고, 실제 라우트가 생기면 이 블록만 자연히 살아난다.
					 */
					$pb_nav_is_logined_ = false;
					if(function_exists('pb_is_logined')){
						$pb_nav_is_logined_ = pb_is_logined();
					}else if(function_exists('pb_current_user_id')){
						$pb_nav_is_logined_ = (bool) pb_current_user_id();
					}
				?>
				<div class="pb-header__auth">
					<?php if($pb_nav_is_logined_){ ?>
						<a href="<?=function_exists('pb_mypage_url') ? pb_mypage_url() : pb_home_url()?>" class="nav-link"><?=__('마이페이지', PB_THEME_DOMAIN)?></a>
					<?php }else{ ?>
						<a href="<?=function_exists('pb_login_url') ? pb_login_url() : pb_home_url()?>" class="nav-link"><?=__('로그인', PB_THEME_DOMAIN)?></a>
					<?php } ?>
				</div>

				<?php
					/* devplan002 part008 — 언어 전환 UI(바닐라, ui.js 드롭다운 컴포넌트 재사용)
					 * 코어에 언어전환 API가 없어 functions.php의 sample-lang-switch 라우트(자체 확장)로 처리.
					 * 전환 링크 자체는 통상 <a href> 이동(새로고침으로도 전환 완료), 드롭다운 열림/닫힘 토글만
					 * SampleUI.dropdown(ui.js, 기존 modal/drawer와 동일하게 JS 의존) 재사용. */
					$sample_theme_lang_list_ = pb_sample_theme_lang_list();
					$sample_theme_current_locale_ = pb_current_locale();
					$sample_theme_redirect_to_ = ltrim(str_replace(PB_REWRITE_BASE, "", strtok($_SERVER['REQUEST_URI'], "?")), '/');
				?>
				<div class="dropdown pb-header__lang" id="pb-lang-menu">
					<button type="button" class="btn btn-ghost btn-sm" data-dropdown-toggle="pb-lang-menu" aria-haspopup="true" aria-expanded="false">
						<?=htmlspecialchars(isset($sample_theme_lang_list_[$sample_theme_current_locale_]) ? $sample_theme_lang_list_[$sample_theme_current_locale_] : reset($sample_theme_lang_list_))?>
					</button>
					<div class="dropdown-menu dropdown-menu-right">
						<?php foreach($sample_theme_lang_list_ as $sample_theme_locale_code_ => $sample_theme_locale_label_){ ?>
							<a class="dropdown-item<?=($sample_theme_locale_code_ === $sample_theme_current_locale_) ? ' is-active' : ''?>" href="<?=pb_home_url(PB_SAMPLE_THEME_LANG_SWITCH_SLUG, array('to' => $sample_theme_locale_code_, 'redirect_to' => $sample_theme_redirect_to_))?>"><?=htmlspecialchars($sample_theme_locale_label_)?></a>
						<?php } ?>
					</div>
				</div>
			</nav>
		</div>
	</header>

	<!-- 메뉴 네비게이션 전용 스크립트 (devplan002 part003) -->
	<script src="<?=pb_current_theme_url()?>lib/js/features/nav.js"></script>

	<main class="pb-main">

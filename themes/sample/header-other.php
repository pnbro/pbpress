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

	<?php pb_head(); ?>

</head>

<body>

	<header class="pb-header">
		<div class="container pb-header__inner">
			<a class="pb-header__brand" href="<?=pb_home_url()?>">PBPress Sample</a>

			<button type="button" class="pb-header__toggle" data-drawer-toggle="pb-main-nav" aria-controls="pb-main-nav" aria-expanded="false" aria-label="메뉴 열기">
				<span class="pb-header__toggle-bar"></span>
				<span class="pb-header__toggle-bar"></span>
				<span class="pb-header__toggle-bar"></span>
			</button>

			<!-- nav 내부 마크업(메뉴 walker)은 part003에서 전면 교체 예정.
			     part001은 헤더 바 뼈대 + 콘텐츠 슬롯까지만 담당(드로어 겸용 셸). -->
			<nav id="pb-main-nav" class="pb-header__nav drawer" aria-label="주 메뉴">
				<div class="drawer__header only-mobile">
					<span class="pb-header__brand">메뉴</span>
					<button type="button" class="btn btn-ghost btn-sm" data-drawer-close aria-label="메뉴 닫기">닫기</button>
				</div>

				<?php
					$main_menu_id_ = pb_option_value("sample_theme_menu_id");
					pb_menu_render(array(
						'menu_slug' => $main_menu_id_,
						'walker' => 'PBMenuWalker_sample_mainmenu',
					)); ?>
			</nav>
		</div>
	</header>

	<main class="pb-main">

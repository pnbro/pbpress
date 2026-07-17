<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/* devplan002 part005 — 마이페이지 좌측 aside 메뉴
 * pages/mypage/{home,change-myinfo,bookmarks}.php 에서 공통으로 include 한다.
 * part001 컴포넌트(card/avatar/user-card) + 자체 mypage-menu 클래스(mypage.css) 사용. */

$pb_mypage_menu_items_ = pb_mypage_menu_data();
$pb_mypage_active_key_ = pb_mypage_current_key();
$pb_mypage_current_user_ = pb_current_user();
?>
<aside class="card mypage-aside" aria-label="마이페이지 메뉴">
	<div class="card-body">
		<div class="user-card mb-6">
			<span class="avatar avatar-lg" aria-hidden="true"><?=htmlspecialchars(pb_mypage_avatar_letter(@$pb_mypage_current_user_['user_name']))?></span>
			<div>
				<div class="user-card-name"><?=htmlspecialchars((string)@$pb_mypage_current_user_['user_name'])?></div>
				<div class="user-card-meta"><?=htmlspecialchars((string)@$pb_mypage_current_user_['user_email'])?></div>
			</div>
		</div>

		<nav aria-label="마이페이지 서브메뉴">
			<ul class="mypage-menu">
				<?php foreach($pb_mypage_menu_items_ as $menu_key_ => $menu_data_): ?>
				<li class="mypage-menu__item">
					<a class="mypage-menu__link<?=($menu_key_ === $pb_mypage_active_key_ ? ' is-active' : '')?>" href="<?=pb_mypage_url($menu_key_)?>"<?=($menu_key_ === $pb_mypage_active_key_ ? ' aria-current="page"' : '')?>>
						<?=htmlspecialchars((string)@$menu_data_['title'])?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>
</aside>

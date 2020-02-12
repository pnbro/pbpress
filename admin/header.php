<?php
	$user_data_ = pb_current_user();
?><header id="pb-admin-header"><div class="wrap">
	<div class="col-left">
		<?php pb_hook_do_action('pb-admin-header-left-before') ?>
		<a class="admin-home-link" href="<?=pb_admin_url()?>">
			<img alt="PBPress Header Logo" src="<?=pb_hook_apply_filters("pb_admin_header_logo_image_url", PB_LIBRARY_URL."/img/admin-header-logo.png")?>" class="logo">
		</a>
		<?php pb_hook_do_action('pb-admin-header-left-after') ?>
	</div>
	<div class="col-right">
		<?php pb_hook_do_action('pb-admin-header-right-before') ?>
		<div class="dropdown mypage-menu">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
				<?=pb_hook_apply_filters('pb-admin-header-user-name', strlen($user_data_['user_name']) ? $user_data_['user_name'] : "-")?>
			</a>
			<ul class="dropdown-menu dropdown-menu-right">
				<li><a href="<?=pb_home_url()?>">사이트홈</a></li>
				<?php pb_hook_do_action('pb-admin-header-mypage-menu'); ?>
				<li><a href="<?=pb_admin_lgout_url(pb_admin_url())?>">로그아웃</a></li>
			</ul>
		</div>

		<a href="" class="aside-menu-toggle-link" data-mobile-aside-toggle-link>
			<i class="icon material-icons">menu</i>
		</a>
		<?php pb_hook_do_action('pb-admin-header-right-after') ?>
	</div>
</div></header>
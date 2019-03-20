<?
	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}

	$adminpage_tree_ = pb_adminpage_tree();
	$current_adminpage_ = pb_current_adminpage();
?><aside id="pb-admin-aside" class="in out end"><div class="wrap">
	
	<ul class="pb-adminpage-list" id="pb-adminpage-list">
		<?php foreach($adminpage_tree_ as $slug_ => $menu_data_){ ?>
			
				
				<?php if(isset($menu_data_['type']) && $menu_data_['type'] === "directory"){
					$submenu_list_ = $menu_data_['children'];

					if(count($submenu_list_) <= 0) continue;
				?>

				<li class="adminpage-menu-item adminpage-menu-item-<?=$slug_?> <?=$menu_data_['active'] ? "active opened" : ""?>">

					<a href="#" data-adminpage-aside-directory="pb-adminpage-sub-list-<?=$slug_?>"><?=$menu_data_['name']?></a>
					<ul class="pb-adminpage-sub-list" id="pb-adminpage-sub-list-<?=$slug_?>">
						<?php foreach($submenu_list_ as $sub_slug_ => $sub_menu_data_){ ?>
							<li class="adminpage-menu-item adminpage-menu-item-<?=$sub_slug_?> <?=$sub_menu_data_['active'] ? "active" : ""?>">
								<a href="<?=pb_admin_url("{$sub_slug_}")?>"><?=$sub_menu_data_['name']?></a>
							</li>
						<?php } ?>
					</ul>
				</li>
				<?php }else{ ?>
					<li>
						<a href="<?=pb_admin_url("pages/{$slug_}")?>"><?=$menu_data_['name']?></a>
					</li>
				<?php } ?>
			
		<?php } ?>
	</ul>

</div></aside>
<div id="pb-admin-aside-overlay" class="in out end" data-mobile-aside-toggle-link></div>
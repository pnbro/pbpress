<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/*
 * PBMenuWalker_sample_mainmenu (devplan002 part003)
 * 순수 바닐라 반응형 네비게이션 마크업 출력용 walker.
 * - 데스크탑: 수평 .nav + 하위 .submenu 드롭다운(lib/css/features/nav.css, lib/js/features/nav.js)
 * - 모바일: part001 .drawer 셸 안에서 세로 목록 + 하위 .submenu 상시 펼침
 * Bootstrap navbar-nav/nav-item(active만 유지)/collapse 클래스는 사용하지 않는다.
 */
class PBMenuWalker_sample_mainmenu extends PBMenuWalker{
	function menu_start($level_){
		?>
		<ul class="nav" role="menubar">
		<?php
	}
	function menu_end($level_){
		?></ul><?php
	}

	function submenu_start($parent_item_data_, $level_){
		$parent_common_data_ = $parent_item_data_['item_data'];
		$submenu_id_ = "pb-submenu-" . $parent_common_data_['id'];

		?>
		<ul class="submenu" id="<?=$submenu_id_?>" role="menu">
		<?php
	}
	function submenu_end($parent_item_data_, $level_){
		?>
		</ul>
		<?php
	}

	function item_start($parent_item_data_,$item_data_, $level_){
		$common_data_ = $item_data_['item_data'];
		$item_meta_data_ = $item_data_['item_meta_data'];
		$children_ = $item_data_['children'];
		$has_children_ = isset($children_) && count($children_) > 0;

		$target_url_ = null;
		$open_new_window_ = false;

		$current_slug_ = pb_current_slug();
		$current_slug_ = urldecode($current_slug_);

		if($common_data_['category'] === "ext-link"){
			$target_url_ = $item_meta_data_['ext_link_url'];
			$open_new_window_ = isset($item_meta_data_['open_new_window']) ? $item_meta_data_['open_new_window'] === "Y" : false;
		}else if($common_data_['category'] === "page"){
			$page_data_ = pb_page($item_meta_data_['page_id']);

			if(isset($page_data_)){
				$target_url_ = pb_home_url($page_data_['slug']);
			}else{
				$target_url_ = pb_home_url();
			}
		}else{
			if(isset($item_meta_data_['slug'])){
				$target_url_ = pb_home_url($item_meta_data_['slug']);
			}else{
				$target_url_ = pb_home_url();
			}
		}

		$is_active_ = ($item_data_['active'] || ($item_data_['child_active'] && $level_ === 1));

		$li_classes_ = array("nav-item");
		if($has_children_){
			$li_classes_[] = "has-children";
		}
		if($is_active_){
			$li_classes_[] = "active";
		}

		$submenu_id_ = "pb-submenu-" . $common_data_['id'];

		?>

		<li class="<?=implode(" ", $li_classes_)?>" role="none">
			<a href="<?=$target_url_?>" <?=$open_new_window_ ? 'target="_blank"' : ""?> class="nav-link" role="menuitem"<?=$is_active_ ? ' aria-current="page"' : ""?>><?=$common_data_['title']?></a>
			<?php if($has_children_){ ?>
			<button type="button" class="nav-caret" aria-haspopup="true" aria-expanded="false" aria-controls="<?=$submenu_id_?>" aria-label="<?=$common_data_['title']?> 하위메뉴 열기">
				<svg viewBox="0 0 12 8" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path d="M1 1l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<?php } ?>

		<?php
	}
	function item_end($parent_item_data_, $item_, $level_){
		?></li><?php
	}
}

?>

<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBMenuWalker_sample_mainmenu extends PBMenuWalker{
	function menu_start($level_){
		?>
		<ul class="navbar-nav ml-auto">
		<?php
	}
	function menu_end($level_){
		?></ul><?php
	}

	function submenu_start($parent_item_data_, $level_){

		?>
		<ul>
			
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
				$active_ = $page_data_['slug'] === $current_slug_;
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

		?>

		<li class="nav-item <?=$item_data_['active'] || $item_data_['child_active'] && $level_ === 1 ? "active" : ""?>">
			<a href="<?=$target_url_?>" <?=$open_new_window_ ? 'target="_blank"' : ""?> class="nav-link"><?=$common_data_['title']?></a>
		
		<?php
	}
	function item_end($parent_item_data_, $item_, $level_){
		?></li><?php
	}
}

?>
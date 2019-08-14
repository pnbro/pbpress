<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

abstract class PBMenuWalker{

	private $_menu_data;
	private $_menu_tree = array();

	function __construct($menu_data_, $menu_tree_){
		$this->_menu_data = $menu_data_;
		$this->_menu_tree = $menu_tree_;
	}

	function menu_data(){
		return $this->_menu_data;
	}
	function menu_tree(){
		return $this->_menu_tree;
	}

	function menu_start($level_){
		?><ul><?php
	}
	function menu_end($level_){
		?></ul><?php
	}

	function submenu_start($parent_item_data_, $level_){
		$this->menu_start($level_);
	}
	function submenu_end($parent_item_data_, $level_){
		$this->menu_end($level_);
	}

	function item_start($menu_data_, $parent_item_data_,$item_data_, $level_){
		$item_data_ = $tree_data_['item_data'];
		$item_meta_data_ = $tree_data_['item_meta_data'];
		$children_ = $tree_data_['children'];

		$target_url_ = null;
		$open_new_window_ = false;
	
		if($item_data_['category'] === "ext-link"){
			$target_url_ = $item_meta_data_['ext_link_url'];
			$open_new_window_ = isset($item_meta_data_['open_new_window']) ? $item_meta_data_['open_new_window'] === "Y" : false;
		}else{
			$page_data_ = pb_page($item_meta_data_['page_id']);

			if(isset($page_data_)){
				$active_ = $page_data_['slug'] === $current_slug_;
				$target_url_ = pb_home_url($page_data_['slug']);
			}else{
				$target_url_ = pb_home_url();
			}
		}

		?>
		<li class=">">
			<a href="<?=$target_url_?>" <?=$open_new_window_ ? 'target="_blank"' : ""?>><?=$item_data_['title']?></a>
		<?php
	}
	function item_end($menu_data_, $parent_item_data_, $item_, $level_){
		?></li><?php
	}
}

?>
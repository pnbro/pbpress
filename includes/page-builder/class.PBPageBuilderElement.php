<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

abstract class PBPageBuilderElement{

	private $element_id = null;
	private $edit_form_func_map = array();
	
	function __construct($element_id_){
		$this->element_id = $element_id_;
		$this->initialize();
	}

	public function element_id(){
		return $this->element_id;
	}

	public function add_edit_form($edit_category_, $func_, $priority_ = 10){
		if(!isset($this->edit_form_func_map[$edit_category_])){
			$this->edit_form_func_map[$edit_category_] = array();
		}

		$map_count_ = count($this->edit_form_func_map[$edit_category_]);
		$insert_index_ = $map_count_;
		for($row_index_=0;$row_index_<$map_count_;++$row_index_){
			$target_item_ = $this->edit_form_func_map[$edit_category_][$row_index_];

			if($target_item_['priority'] > $priority_){
				$insert_index_ = $row_index_;
				break;
			}
		}

		array_splice($this->edit_form_func_map[$edit_category_], $insert_index_, 0, array($func_));
	}
	public function edit_forms($edit_category_){
		if(!isset($this->edit_form_func_map[$edit_category_])){
			return array();
		}
		return $this->edit_form_func_map[$edit_category_];
	}

	abstract function initialize();
	abstract public function render($content_ = null, $element_data_ = array());
}

?>
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

		array_splice($this->edit_form_func_map[$edit_category_], $insert_index_, 0, array(
			array(
				'render' => $func_,
				'priority' => $priority_,
			)
		));
	}
	public function edit_forms($edit_category_){
		if(!isset($this->edit_form_func_map[$edit_category_])){
			return array();
		}
		return $this->edit_form_func_map[$edit_category_];
	}

	public function render_content($elementcontent_ = array()){
		$element_map_ = pb_page_builder_elements();
		global $pb_page_builder_element_classes;

		if(isset($element_map_[$this->element_id]['loadable']) && $element_map_[$this->element_id]['loadable']){
			foreach($elementcontent_ as $element_data_){
				$element_class_ = $pb_page_builder_element_classes[$element_data_['name']];
				call_user_func_array(array($element_class_, "render"), array($element_data_, $element_data_['elementcontent']));	
			} 
			
		}else{
			echo $elementcontent_;
		}
	}

	abstract function initialize();
	abstract public function render($data_ = array(), $content_ = null);
}

?>
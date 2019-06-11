<?php

define("PB_LISTTABLE_MAPS", "_pb_listtable_maps");

$listtable_maps_ = pb_session_get(PB_LISTTABLE_MAPS);
if(!isset($listtable_maps_)){
	pb_session_put(PB_LISTTABLE_MAPS, array());
}

class PBListTable{

	private $global_id = "";

	private $html_class = "";
	private $html_id = "";

	private $ajax = false;

	private $current_row = -1;
	private $display_row_number = -1;
	private $display_r_row_number = -1;

	function global_id(){
		return $this->global_id;
	}

	function current_row(){
		return $this->current_row;
	}

	function display_row_number(){
		return $this->display_row_number;
	}
	function display_reverse_row_number(){
		return $this->display_r_row_number;
	}
	function set_ajax($bool_){
		$this->ajax = $bool_;
	}
	function is_ajax(){
		return $this->ajax;
	}

	function __construct($id_ = "",$class_ = ""){
		$this->html_id = $id_;
		$this->html_class = $class_;

		$listtable_maps_ = pb_session_get(PB_LISTTABLE_MAPS);
		
		$global_id_ = pb_random_string(20);
		$this->global_id = $global_id_;

		$listtable_maps_[$global_id_] = serialize($this);
		pb_session_put(PB_LISTTABLE_MAPS, $listtable_maps_);
		pb_hook_add_action('pb_admin_foot', array($this, "_hook_for_initialize"));
		pb_hook_add_action('pb_foot', array($this, "_hook_for_initialize"));
	}

	function offset($page_index_, $per_page_){
		return ($page_index_) * $per_page_;
	}

	function columns(){
		return array();
	}
	function column_value($item_, $column_name_){
		return $item_[$column_name_];
	}

	function row_attributes($items_, $row_index_){
		return array();
	}
	function before_row($items_, $row_index_){
		return "";
	}
	function after_row($items_, $row_index_){
		return "";
	}

	function column_header_classes($column_name_){
		return "";
	}
	function column_body_classes($column_name_, $items_){
		return "";
	}

	function norowdata(){
		return __("Data not found");
	}

	function first_row_display(){
		return $this->norowdata();
	}

	function prepare(){
		_e("must override PBListTable::prepare()");
	}

	function items($args_){
		_e("must override PBListTable::items()");
	}

	function rander_header(){
		$html_ = '';
		$html_ .= '<tr>';

		$columns_ = $this->columns();
		foreach($columns_ as $key_ => $title_){
			$html_ .= '<th class="'.$key_.' '.$this->column_header_classes($key_).'">'.$title_.'</th>';
		}

		$html_ .= '</tr>';

		return $html_;
	}
	function rander_body($args_, $items_){
		$is_first_ = isset($args_["first"]) ? $args_["first"] : false;
		$columns_ = $this->columns();
		$is_norowdata_ = (count($items_) == 0);

		$html_ = '';
		
		$this->current_row = 0;
		$this->display_row_number = ($is_first_ ? 0 : ($args_["page_index"] * $args_["per_page"])) + 1;
		$this->display_r_row_number = ($is_first_ ? 0 : ($args_["total_count"] - ($args_["page_index"] * $args_["per_page"])) );
		foreach($items_ as $row_index_ => $row_data_){
			$tr_attributes_ = $this->row_attributes($row_data_, $row_index_);
			$html_ .= $this->before_row($row_data_, $row_index_);
			$html_ .= '<tr ';
			foreach($tr_attributes_ as $key_ => $value_){
				$html_ .= $key_.'="'.$value_.'"';
			}
			$html_ .= ' >';

			foreach($columns_ as $key_ => $title_){
				$html_ .= '<td class="'.$key_.' '.$this->column_body_classes($key_, $row_data_).'" >'.$this->column_value($row_data_, $key_).'</td>';
			}

			$html_ .= '</tr>'.$this->after_row($row_data_, $row_index_);
			$this->current_row += 1;
			$this->display_row_number += 1;
			$this->display_r_row_number -= 1;
		}

		if($is_first_){
			$html_ .= '<tr><td class="no-rowdata first" colspan="'.(count($columns_)).'">'.$this->first_row_display().'</td></tr>';
		}else if($is_norowdata_){
			$html_ .= '<tr><td class="no-rowdata" colspan="'.(count($columns_)).'">'.$this->norowdata().'</td></tr>';
		}

		return $html_;
	}
	function rander_pagenav($args_){
		$per_page_ = $args_["per_page"];
		$total_count_ = $args_["total_count"];
		$total_page_count_ = ceil($total_count_ / $per_page_);
		$page_index_ = $args_["page_index"];
		$pagenav_count_ = isset($args_["pagenav_count"]) ? $args_["pagenav_count"] : 10;
		$is_pagenav_number_ = isset($args_['pagenav_number']) ? $args_['pagenav_number'] : true;
		
		$pagenav_offset_ = floor(($page_index_) / $pagenav_count_);
	/*	$pagenav_start_index_ = ($pagenav_offset_ * $pagenav_count_);
		$pagenav_end_offset_ = ($pagenav_count_*($pagenav_offset_+1));

		if($total_page_count_ <= $pagenav_end_offset_){
			$pagenav_end_offset_= $total_page_count_;
		}*/

		$html_ = '';

		if($is_pagenav_number_){

			$pagenav_start_index_ = ($pagenav_offset_ * $pagenav_count_);
			$pagenav_end_offset_ = ($pagenav_count_*($pagenav_offset_+1));

			if($total_page_count_ <= $pagenav_end_offset_){
				$pagenav_end_offset_= $total_page_count_;
			}

			if($pagenav_start_index_ > 0){
				$html_ .= '<a href="javascript:void(0);" class="pagenav-left pagenav-btn" data-page-index="'.($pagenav_start_index_-1).'"><i class="icon material-icons">keyboard_arrow_left</i></a>';
			}else{
				$html_ .= '<span class="pagenav-left pagenav-btn"></span>';
			}

			for($pagenav_index_ = $pagenav_start_index_; $pagenav_index_ < $pagenav_end_offset_; ++$pagenav_index_){
				$html_ .= '<a href="javascript:void(0);" data-page-index="'.($pagenav_index_).'" class="'.($pagenav_index_ == $page_index_ ? "active" : "").' page-numbers">'.($pagenav_index_ + 1).'</a>';
			}

			if($total_page_count_ > $pagenav_end_offset_){
				$html_ .= '<a href="javascript:void(0);" class="pagenav-right pagenav-btn" data-page-index="'.($pagenav_end_offset_).'"><i class="icon material-icons">keyboard_arrow_right</i></a>';
			}else{
				$html_ .= '<span class="pagenav-left pagenav-btn"></span>';
			}
		}else{
			if(($page_index_-1) >= 0){
				$html_ .= '<a href="javascript:void(0);" class="pagenav-left pagenav-btn" data-page-index="'.($page_index_-1).'"><i class="icon material-icons">keyboard_arrow_left</i></a>';
			}else{
				$html_ .= '<span class="pagenav-left pagenav-btn"></span>';
			}

			
			if($total_count_ > 0){
				$html_ .= '<span class="page-monitor">'.($page_index_+1).'/'.$total_page_count_.'</span>';	
			}	
			
			if($total_page_count_ > ($page_index_+1)){
				$html_ .= '<a href="javascript:void(0);" class="pagenav-right pagenav-btn" data-page-index="'.($page_index_+1).'"><i class="icon material-icons">keyboard_arrow_right</i></a>';
			}else{
				$html_ .= '<span class="pagenav-left pagenav-btn"></span>';
			}

		}

		$html_ .= '<div class="clearfix"></div>';

		return $html_;
	}

	function html(){
		$args_ = $this->prepare();
		
		$page_index_ = $args_["page_index"];
		$per_page_ = $args_["per_page"];
		$total_count_ = $args_["total_count"];

		$hide_pagenav_ = (isset($args_["hide_pagenav"]) ? $args_["hide_pagenav"] : false);
		$hide_header_ = (isset($args_["hide_header"]) ? $args_["hide_header"] : false);

		$html_ = '';
		$html_ .= '<input type="hidden" name="page_index" value="'.$page_index_.'">';
		$html_ .= '<table class="table table-hover table-striped pb-listtable '.$this->html_class.'" '.(strlen($this->html_id) ? 'id="'.$this->html_id.'"' : '').' data-pb-listtable-id="'.$this->global_id.'" ';

		$html_ .= '>';

		if(!$hide_header_){
			$html_ .= '<thead>';
			$html_ .= $this->rander_header();
			$html_ .= '</thead>';	
		}
		
		$html_ .= '<tbody>';
		if(!$this->is_ajax()){
			$html_ .= $this->rander_body($args_, $this->items($args_));
		}else $html_ .= $this->rander_body(array('first' => true), array());
		$html_ .= '</tbody>';

		$html_ .= '</table>';

		ob_start();
		pb_hook_apply_filters('pb-listtable-before-pagenav-'.$this->html_id, '');
		$html_ .= ob_get_clean();

		$html_ .= '<div class="pb-list-pagenav '.($hide_pagenav_ ? "hidden" : "").'" '.(strlen($this->html_id) ? 'id="'.$this->html_id.'-pagenav"' : '').'  data-pb-listtable-pagenav-id="'.$this->global_id.'">';
		if(!$hide_pagenav_){
			$html_ .= $this->rander_pagenav($args_);
		}
		$html_ .= '</div>';

		ob_start();
		pb_hook_apply_filters('pb-listtable-after-pagenav-'.$this->html_id, '');
		$html_ .= ob_get_clean();

		return $html_;
	}

	function _hook_for_initialize(){
		?>
		<script type="text/javascript">
		_pb_list_table_initialize("<?=$this->global_id?>",<?=($this->is_ajax() === true ? "true" : "false")?>);
		</script>
		<?php
	}
}

function _pb_ajax_listtable_load_html(){
	$global_id_ = $_REQUEST["global_id"];
	$listtable_maps_ = pb_session_get(PB_LISTTABLE_MAPS);
	$listtable_ = unserialize($listtable_maps_[$global_id_]);
	
	$args_ = $listtable_->prepare();
	$body_html_ = $listtable_->rander_body($args_, $listtable_->items($args_));
	$pagenav_html_ = $listtable_->rander_pagenav($args_);	

	echo json_encode(array(
		"success" => true,
		"body_html" => $body_html_,
		"pagenav_html" => $pagenav_html_,
		"orgdata" => $args_,
	));
	pb_end();
}
pb_add_ajax('pb-listtable-load-html', "_pb_ajax_listtable_load_html");

?>
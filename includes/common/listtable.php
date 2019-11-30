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

		ob_start();

		?>
		<tr>
			<?php

				$columns_ = $this->columns();
				foreach($columns_ as $key_ => $title_){ ?>

					<th class="<?=$key_?> <?=$this->column_header_classes($key_)?>"><?=$title_?></th>
					
				<?php }
			?>
			
		</tr>

		<?php

		return ob_get_clean();
	}

	function rander_body($args_, $items_){
		$is_first_ = isset($args_["first"]) ? $args_["first"] : false;
		$columns_ = $this->columns();
		$is_norowdata_ = (count($items_) == 0);

		ob_start();
		
		$this->current_row = 0;
		$this->display_row_number = ($is_first_ ? 0 : ($args_["page_index"] * $args_["per_page"])) + 1;
		$this->display_r_row_number = ($is_first_ ? 0 : ($args_["total_count"] - ($args_["page_index"] * $args_["per_page"])) );
		foreach($items_ as $row_index_ => $row_data_){
			echo $this->before_row($row_data_, $row_index_);

			$tr_attributes_ = $this->row_attributes($row_data_, $row_index_);
			$tr_attributes_html_ = "";
			foreach($tr_attributes_ as $key_ => $value_){
				$tr_attributes_html_ .= $key_.'="'.$value_.'"';
			}

			?>

			<tr <?=$tr_attributes_html_?>>

			<?php 

			foreach($columns_ as $key_ => $title_){ ?>
				<td class="<?=$key_?> <?=$this->column_body_classes($key_, $row_data_)?>" ><?=$this->column_value($row_data_, $key_)?></td>
			<?php }

			?>

			</tr>

			<?php

			echo $this->after_row($row_data_, $row_index_);

			$this->current_row += 1;
			$this->display_row_number += 1;
			$this->display_r_row_number -= 1;
		}

		if($is_first_){ ?>
			<tr><td class="no-rowdata first" colspan="<?=(count($columns_))?>"><?=$this->first_row_display()?></td></tr>
		<?php }else if($is_norowdata_){ ?>
			<tr><td class="no-rowdata" colspan="<?=(count($columns_))?>"><?=$this->norowdata()?></td></tr>
		<?php }

		return ob_get_clean();
	}
	function rander_pagenav($args_){
		$per_page_ = $args_["per_page"];
		$total_count_ = $args_["total_count"];
		$total_page_count_ = ceil($total_count_ / $per_page_);
		$page_index_ = $args_["page_index"];
		$pagenav_count_ = isset($args_["pagenav_count"]) ? $args_["pagenav_count"] : 10;
		$is_pagenav_number_ = isset($args_['pagenav_number']) ? $args_['pagenav_number'] : true;
		
		$pagenav_offset_ = floor(($page_index_) / $pagenav_count_);

		ob_start();

		if($is_pagenav_number_){

			$pagenav_start_index_ = ($pagenav_offset_ * $pagenav_count_);
			$pagenav_end_offset_ = ($pagenav_count_*($pagenav_offset_+1));

			if($total_page_count_ <= $pagenav_end_offset_){
				$pagenav_end_offset_= $total_page_count_;
			}

			if($pagenav_start_index_ > 0){ ?>
				<a href="javascript:void(0);" class="pagenav-left pagenav-btn" data-page-index="<?=$pagenav_start_index_-1?>"><i class="icon material-icons">keyboard_arrow_left</i></a>
			<?php }else{ ?>
				<span class="pagenav-left pagenav-btn"></span>
			<?php }

			for($pagenav_index_ = $pagenav_start_index_; $pagenav_index_ < $pagenav_end_offset_; ++$pagenav_index_){ ?>

				<a href="javascript:void(0);" data-page-index="<?=$pagenav_index_?>" class="<?=$pagenav_index_ == $page_index_ ? "active" : ""?> page-numbers"><?=$pagenav_index_ + 1?></a>
				
			<?php }

			if($total_page_count_ > $pagenav_end_offset_){ ?>

				<a href="javascript:void(0);" class="pagenav-right pagenav-btn" data-page-index="<?=$pagenav_end_offset_?>"><i class="icon material-icons">keyboard_arrow_right</i></a>
			<?php }else{ ?>

				<span class="pagenav-left pagenav-btn"></span>
				
			<?php }
		}else{
			if(($page_index_-1) >= 0){ ?>

				<a href="javascript:void(0);" class="pagenav-left pagenav-btn" data-page-index="<?=($page_index_-1)?>"><i class="icon material-icons">keyboard_arrow_left</i></a>
				
			<?php }else{ ?>

				<span class="pagenav-left pagenav-btn"></span>
			<?php }

			
			if($total_count_ > 0){ ?>
				<span class="page-monitor"><?=($page_index_+1)?>/<?=$total_page_count_?></span>
			<?php }	
			
			if($total_page_count_ > ($page_index_+1)){ ?>
				<a href="javascript:void(0);" class="pagenav-right pagenav-btn" data-page-index="<?=$page_index_+1?>"><i class="icon material-icons">keyboard_arrow_right</i></a>
			<?php }else{ ?>
				<span class="pagenav-left pagenav-btn"></span>
			<?php }

		}

		?>
			<div class="clearfix"></div>
		<?php

		return ob_get_clean();
	}

	function html(){
		$args_ = $this->prepare();
		
		$page_index_ = $args_["page_index"];
		$per_page_ = $args_["per_page"];
		$total_count_ = $args_["total_count"];

		$hide_pagenav_ = (isset($args_["hide_pagenav"]) ? $args_["hide_pagenav"] : false);
		$hide_header_ = (isset($args_["hide_header"]) ? $args_["hide_header"] : false);

		$args_['table_class'] = isset($args_['table_class']) ? $args_['table_class'] : "table table-hover table-striped pb-listtable";

		ob_start();

		?>

		<input type="hidden" name="page_index" value="<?=$page_index_?>">
			
		<table class="<?=$args_['table_class']?> <?=$this->html_class?>" <?=(strlen($this->html_id) ? 'id="'.$this->html_id.'"' : '')?> data-pb-listtable-id="<?=$this->global_id?>" >
		<?php

		if(!$hide_header_){
			?>

			<thead><?=$this->rander_header()?></thead>

			<?php
		}

		?>

		<tbody>
			<?php
				if(!$this->is_ajax()){
					echo $this->rander_body($args_, $this->items($args_));
				}else{
					echo $this->rander_body(array('first' => true), array());
				}
			?>

		</tbody>

	</table>

		<?php
	
		pb_hook_do_action('pb-listtable-before-pagenav-'.$this->html_id, '');

		?>

		<div class="pb-list-pagenav <?=($hide_pagenav_ ? "hidden" : "")?>" <?=(strlen($this->html_id) ? 'id="'.$this->html_id.'-pagenav"' : '')?>  data-pb-listtable-pagenav-id="<?=$this->global_id?>">

		<?php
		
		if(!$hide_pagenav_){
			echo $this->rander_pagenav($args_);
		}

		?>
	</div>
	<script type="text/javascript">
	_pb_list_table_initialize("<?=$this->global_id?>",<?=($this->is_ajax() === true ? "true" : "false")?>);
	</script>
		<?php

		pb_hook_do_action('pb-listtable-after-pagenav-'.$this->html_id, '');

		return ob_get_clean();
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
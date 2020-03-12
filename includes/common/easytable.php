<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PB_easytable{

	private $_loader;
	private $_data;
	private $_options;
	private $_orderby;

	function __construct($id_, $loader_, $data_, $options_ = array()){
		$this->_id = $id_;
		$this->_loader = $loader_;
		$this->_data = $data_;
		$this->_options = $options_;
	}

	function loader(){
		return $this->_loader;
	}
	function statement(){
		return call_user_func($this->_loader);
	}
	function options(){
		return $this->_options;
	}

	function set_orderby($val_){
		$this->_orderby = $val_;
	}
	function orderby(){
		return $this->_orderby;
	}


	function display($page_index_){		
		$options_ = $this->_options;
		$hide_pagenav_ = (isset($options_["hide_pagenav"]) ? $options_["hide_pagenav"] : false);

		$table_class_ = isset($options_['class']) ? $options_['class'] : "table";
		$is_ajax_ = isset($options_['ajax']) ? $options_['ajax'] : false;

		$loading_indicator_ = isset($options_['loading_indicator']) ? $options_['loading_indicator'] : "<div class='pb-indicator-frame small'>
			<div class='lds-spin'><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div></div>
		</div>";
		
		?>
		<input type="hidden" name="page_index" value="<?=$page_index_?>">
		<table class="table <?=$table_class_?>" id="<?=$this->_id?>" data-ajax="<?=$is_ajax_ ? "Y" : "N"?>" data-loading-indicator="<?=htmlentities($loading_indicator_)?>" data-hide-pagenav="<?=$hide_pagenav_ ? "Y" : "N" ?>">
			<thead>
				<?php foreach($this->_data as $key_ => $column_data_){ 
					$class_ = isset($column_data_['head_class']) ? $column_data_['head_class'] : " ";
					$class_ .= (isset($column_data_['class']) ? $column_data_['class'] : " ");
					$class_ = trim($class_);

				?>
					<th class="<?=$key_?> <?=$class_?>"><?=isset($column_data_['name']) ? $column_data_['name'] : ""?></th>
				<?php } ?>
			</thead>
			<tbody>

				<?php if($is_ajax_){ ?>
					<tr>
						<td class="no-rowdata first" colspan="<?=count($this->_data)?>"><?=isset($options_['no_rowdata']) ? $options_['no_rowdata'] : null?></td>
					</tr>
				<?php }else{
					$this->render_body($page_index_);
				} ?>
				
			</tbody>

		</table>
		<?php if(!$hide_pagenav_){

			$this->render_pagenav($page_index_);

		} ?>

		<script type="text/javascript">$("#<?=$this->_id?>").pbeasytable();</script>

		<?php
	}

	function render_body($page_index_){
		$options_ = $this->_options;

		$per_page_ = isset($options_['per_page']) ? $options_['per_page'] : 15;
		$offset_ = ($page_index_) * $per_page_;

		$statement_ = $this->statement();		
		$result_list_ = $statement_->select($this->_orderby, array($offset_, $per_page_));


		foreach($result_list_ as $row_data_){ ?>
			<tr>

				<?php 

				foreach($this->_data as $key_ => $column_data_){

					$class_ = isset($column_data_['body_class']) ? $column_data_['body_class'] : " ";
					$class_ .= (isset($column_data_['class']) ? $column_data_['class'] : " ");
					$class_ = trim($class_);

					$column_value_ = null;
					if(isset($column_data_['render'])){
						ob_start();
						call_user_func_array($column_data_['render'], array($this, $row_data_, $page_index_));
						$column_value_ = ob_get_clean();
					}else{
						$column_value_ = isset($row_data_[$key_]) ? $row_data_[$key_] : null;
					}

				?>
					<td class="<?=$key_?> <?=$class_?>">
						<?=$column_value_?>
					</td>
				<?php }

				?>
				
			</tr>
		<?php }

		if(count($result_list_) <= 0){ ?>

			<tr>
				<td class="no-rowdata" colspan="<?=count($this->_data)?>"><?=isset($options_['no_rowdata']) ? $options_['no_rowdata'] : null?></td>
			</tr>

		<?php }
	}

	function render_pagenav($page_index_){
		$statement_ = $this->statement();
		$options_ = $this->_options;

		?>

	<div class="pb-easytable-pagenav" id="<?=$this->_id?>-pagenav"  data-easytable-pagenav-id="<?=$this->_id?>">

		<?php
		
			$pagenav_count_ = isset($options_["pagenav_count"]) ? $options_["pagenav_count"] : 10;
			$hide_pagenav_number_ = isset($options_['hide_pagenav_number']) ? $options_['hide_pagenav_number'] : false;

			$per_page_ = isset($options_['per_page']) ? $options_['per_page'] : 15;
			$statement_ = $this->statement();
			$total_count_ = $statement_->count();

			$total_page_count_ = ceil($total_count_ / $per_page_);
			$pagenav_offset_ = floor(($page_index_) / $pagenav_count_);

			if(!$hide_pagenav_number_){

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
	</div>

		<?php 
	}
}

global $pb_easytable_map;
$pb_easytable_map = array();

pb_add_ajax('pb-easytable-load-html', '_pb_ajax_easytable_load_html');
function _pb_ajax_easytable_load_html(){
	global $pb_easytable_map;

	$table_id_ = isset($_GET['table_id']) ? $_GET['table_id'] : -1;

	if(!isset($pb_easytable_map[$table_id_])){
		pb_ajax_error('잘못된 접근','잘못된 접근입니다.');
	}

	$easytable_ = $pb_easytable_map[$table_id_];

	ob_start();
	$easytable_->render_body($page_index_);
	$body_html_ = ob_get_clean();

	$pagenav_html_ = null;

	$options_ = $easytable_->options();

	$hide_pagenav_ = (isset($options_['hide_pagenav']) ? $options_['hide_pagenav'] : false);
	$pagenav_html_ = null;
	if(!$hide_pagenav_){
		ob_start();
		$easytable_->render_pagenav($page_index_);
		$pagenav_html_ = ob_get_clean();
	}
	
	pb_ajax_success(array(
		'body_html' => $body_html_,
		'pagenav_html' => $pagenav_html_,
	));
}

function pb_easytable($id_, $loader_, $data_, $options_ = array()){
	global $pb_easytable_map;
	return ($pb_easytable_map[$id_] = new PB_easytable($id_, $loader_, $data_, $options_));
}
	
?>
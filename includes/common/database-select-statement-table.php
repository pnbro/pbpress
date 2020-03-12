<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $PBDB_SS_TABLES;
$PBDB_SS_TABLES = array();

class PBDB_select_statement_table{

	private $_statment;
	private $_data;
	private $_options;
	private $_orderby;

	function __construct($id_, $statement_, $data_, $options_ = array()){
		$this->_id = $id_;
		$this->_statment = $statement_;
		$this->_data = $data_;
		$this->_options = $options_;

		global $PBDB_SS_TABLES;
		$PBDB_SS_TABLES[$this->_id] = $this;
	}

	function statement(){
		return $this->_statement;
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

		
		?>
		<table class="table table-hover table-striped pb-listtable">
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

				<?php 

					if((isset($options_['ajax']) ?  $options_['ajax'] : false)){ ?>

						<tr>
							<td class="no-rowdata first"><?=isset($options_['no-rowdata']) ? $options_['no-rowdata'] : null?></td>
						</tr>
						
					<?php }else{
						$this->render_body($this->_orderby, $page_index_);
					}

				?>
				
			</tbody>

		</table>
		<?php if(!$hide_pagenav_){ ?>
		<div class="pb-list-pagenav <?=($hide_pagenav_ ? "hidden" : "")?>" id="<?=$this->_id?>-pagenav"  data-ss-table-pagenav-id="<?=$this->_id?>">

			<?php
			
				$pagenav_count_ = isset($options_["pagenav_count"]) ? $options_["pagenav_count"] : 10;

				$per_page_ = isset($options_['per_page']) ? $options_['per_page'] : 15;
				$total_count_ = $statement_->count();

				$total_page_count_ = ceil($total_count_ / $per_page_);
				$pagenav_offset_ = floor(($page_index_) / $pagenav_count_);

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
		</div>
		<?php } 
	}

	function render_body($page_index_){
		$statement_ = $this->_statment;
		$options_ = $this->_options;

		$per_page_ = isset($options_['per_page']) ? $options_['per_page'] : 15;
		$offset_ = ($page_index_) * $per_page_;
		$result_list_ = $statement_->select($order_by_, array($offset_, $per_page_));

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
	}
}

pb_add_ajax('pb-database-ss-table-load-html', '_pb_ajax_database_ss_table_load_html');
function _pb_ajax_database_ss_table_load_html(){
	global $PBDB_SS_TABLES;
		
	$id_ = isset($_POST['table_id']) ? $_POST['table_id'] : null;
	$page_index_ = isset($_POST['page_index']) ? $_POST['page_index'] : 0;

	if(!strlen($id_) || !isset($PBDB_SS_TABLES[$id_])){
		pb_ajax_error("잘못된 접근", "잘못된 접근입니다.");
	}

	$stable_ = $PBDB_SS_TABLES[$id_];
	$options_ = $stable_->options();

	if(!isset($options_['ajax'])){
		pb_ajax_error("잘못된 접근", "잘못된 접근입니다.");	
	}

	call_user_func_array($options_['ajax'], array($listtable_, $page_index_));

	ob_start();

	$listtable_->render_body($page_index_);
	
	$body_html_ = ob_get_clean();

	pb_ajax_success(array(
		'body_html' => $body_html_,
	));
}
	
?>
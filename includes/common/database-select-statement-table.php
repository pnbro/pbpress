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

		$table_class_ = isset($options_['class']) ? $options_['class'] : "table table-hover table-striped pb-listtable";
		
		?>
		<input type="hidden" name="page_index" value="<?=$page_index_?>">
		<table class="table table-hover table-striped pb-listtable <?=$table_class_?>" id="<?=$this->_id?>">
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

				<?php $this->render_body($this->_orderby, $page_index_); ?>
				
			</tbody>

		</table>
		<?php if(!$hide_pagenav_){

			$this->render_pagenav($page_index_);

		} ?>

		<script type="text/javascript">$("#<?=$this->_id?>").pbsstable();</script>

		<?php
	}

	function render_body($page_index_){
		$statement_ = $this->_statment;
		$options_ = $this->_options;

		$per_page_ = isset($options_['per_page']) ? $options_['per_page'] : 15;
		$offset_ = ($page_index_) * $per_page_;

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
				<td class="no-rowdata first"><?=isset($options_['no_rowdata']) ? $options_['no_rowdata'] : null?></td>
			</tr>

		<?php }
	}

	function render_pagenav($page_index_){
		$statement_ = $this->_statment;
		$options_ = $this->_options;

		?>

	<div class="pb-list-pagenav <?=($hide_pagenav_ ? "hidden" : "")?>" id="<?=$this->_id?>-pagenav"  data-sstable-pagenav-id="<?=$this->_id?>">

		<?php
		
			$pagenav_count_ = isset($options_["pagenav_count"]) ? $options_["pagenav_count"] : 10;
			$is_pagenav_number_ = isset($options_['pagenav_number']) ? $options_['pagenav_number'] : true;

			$per_page_ = isset($options_['per_page']) ? $options_['per_page'] : 15;
			$total_count_ = $this->_statment->count();

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

		<?php 
	}
}

function pb_database_ss_table($id_, $statement_, $data_, $options_ = array()){
	return new PBDB_select_statement_table($id_, $statement_, $data_, $options_);
}
	
?>
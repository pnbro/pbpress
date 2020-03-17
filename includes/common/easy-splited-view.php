<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_easy_splited_view($id_, $master_table_id_, $detail_table_id_, $options_){
	$master_options_ = $options_['master'];
	$detail_options_ = $options_['detail'];
	$placeholder_ = $options_['placeholder'];

	$master_loader_ = isset($master_options_['loader']) ? $master_options_['loader'] : null;

	$master_table_ = pb_easytable($master_table_id_);
	$master_table_->update_option_value("ajax", true);

	$detail_table_ = null; 

	if(strlen($detail_table_id_)){
		$detail_table_ = pb_easytable($detail_table_id_);
		$detail_table_->update_option_value("ajax", true);
	}	
?>

	<div class="splitted-view-frame" id="<?=$id_?>" data-master-table-id="#<?=$master_table_id_?>"
	 <?php if(strlen($detail_table_id_)){ ?>
			data-detail-table-id="#<?=$detail_table_id_?>"
	 <?php } ?>
	  data-master-loader="<?=$master_loader_?>"><div class="wrap">

		<div class="col-master">
			<form method="GET" id="<?=$master_table_id_?>-form" class="pb-easytable-group" data-master-table-form>
				<?php 

					$master_header_ = $master_options_['header'];
					if(is_callable($master_header_)){
						call_user_func($master_header_);
					}else{
						echo $master_header_;
					}

				?>
				<?php $master_table_->display(0); ?>
			</form>
		</div>
		<div class="col-detail">
			<div class="notfound-overlay">
				<?=$placeholder_?>
			</div>
			<form method="GET" id="<?=$detail_table_id_?>-form" class="pb-easytable-group" data-detail-table-form>
				<div class="master-info-fram master-info-frame" data-master-info-group>
					<?php 

					$master_preview_html_ = isset($master_options_['preview_html']) ? $master_options_['preview_html'] : null;
					if(is_callable($master_preview_html_)){
						call_user_func($master_preview_html_);
					}else{
						echo $master_preview_html_;
					}

					?>
				</div>
				<?php 

					$detail_header_ = $detail_options_['header'];
					if(is_callable($detail_header_)){
						call_user_func($detail_header_);
					}else{
						echo $detail_header_;
					}


				?>
				<?php if(isset($detail_table_)){
					$detail_table_->display(0);
				} ?>
			</form>

		</div>
	</div>

</div>

<?php 

}
	
?>
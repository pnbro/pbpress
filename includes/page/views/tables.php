<?php

class PB_page_list_table extends PBListTable{

	function prepare(){

		global $pbdb;

		$page_index_ = isset($_GET["page_index"]) ? (int)$_GET["page_index"] : 0;
		$keyword_ = isset($_GET["keyword"]) ? $_GET["keyword"] : null;
		$status_ = isset($_GET["status"]) ? $_GET["status"] : null;
		$per_page_ = 15;
		$offset_ = $this->offset($page_index_, $per_page_);

		$page_list_ = pb_page_list(array(
			'keyword' => $keyword_,
			"status" => $status_,
			"limit" => array($offset_, $per_page_)
		));

		$page_list_count_ = pb_page_list(array(
			"justcount" => true,
			"status" => $status_,
			"keyword" => $keyword_
		));

		return array(
			"page_index" => $page_index_,
			"per_page" => $per_page_,
			'total_count' => $page_list_count_,
			'items' => $page_list_,
		);
	}

	function items($args_){
		return $args_['items'];
	}

	function columns(){
		return array(
			'seq' => 'NO',
			'page_title' => '페이지명',
			'status_name' => '상태',
			'reg_date_ymdhi' => '등록일자',
		
		
		);
	}

	function column_header_classes($column_name_){

		switch($column_name_){
		
			case "seq" :
				return "col-seq text-center";
			case "page_title" :
				 return "col-8";
			case "status_name" :
				 return "col-1 text-center hidden-xs";
			case "reg_date_ymdhi" :
				 return "col-2 text-center hidden-xs";
		
			default : 
				return '';
			break;
		}
	}
	function column_body_classes($key_, $item_){
		return $this->column_header_classes($key_);
	}

	function column_value($item_, $column_name_){

		$row_index_ = $this->display_row_number();
		$is_front_page_ = pb_front_page_id() === (string)$item_['id'];

		$page_url_ = pb_page_url($item_['id']);

		switch($column_name_){
		
			case "seq" :
				return $row_index_;

			case "page_title" :
			ob_start();

			?>
			<div class="page-title-frame"><a href="<?=pb_admin_url("manage-page/edit/".$item_['id'])?>" ><?=$item_['page_title']?></a>
				<?php if($is_front_page_){ ?>
					<small class="fontpage-text"> - 홈화면</small>
				<?php } ?>
			</div>
			<div class="url-link"><a href="<?=$page_url_?>" target="_blank"><?=$page_url_?></a></div>
			<div class="subaction-frame">
				<a href="<?=pb_admin_url("manage-page/edit/".$item_['id'])?>">수정</a>
				<?php if(!$is_front_page_){ ?>
					<a href="javascript:pb_manage_page_register_front_page('<?=$item_['id']?>');" class="">홈화면 지정</a>
				<?php }else{ ?>
					<a href="javascript:pb_manage_page_unregister_front_page();" class="">홈화면 지정해제</a>
				<?php } ?>
				<?php pb_hook_do_action("pb_manage_page_listtable_subaction", $item_) ?>
				<a href="javascript:pb_manage_page_remove('<?=$item_['id']?>');" class="text-danger">삭제</a>
			
				
			</div>

			<div class="xs-visiable-info">
				<div class="subinfo"><i class="icon material-icons">access_time</i> <span class="text"><?=$item_['reg_date_ymdhi']?></span></div>
				<div class="subinfo">
					<select class="form-control input-sm display-inline" name="status" data-page-status="<?=$item_['id']?>">
						<?= pb_gcode_make_options(array("code_id" => "PAG01"), $item_['status']); ?>
					</select>
				</div>
			</div>

			<?php

			return ob_get_clean();
			case "status_name" :

				ob_start();
				?>

				<select class="form-control input-sm" name="status" data-page-status="<?=$item_['id']?>">
					<?= pb_gcode_make_options(array("code_id" => "PAG01"), $item_['status']); ?>
				</select>

				<?php

				return ob_get_clean();
			case "page_title" :
			case "reg_date_ymdhi" :
				 return $item_[$column_name_];

			default : 
				return '';
			break;
		}
	}

	function norowdata(){
		return "검색된 페이지가 없습니다.";	
	}
	
}

?>
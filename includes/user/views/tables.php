<?php

class PB_user_list_table extends PBListTable{

	function prepare(){

		global $pbdb;

		$page_index_ = isset($_GET["page_index"]) ? (int)$_GET["page_index"] : 0;
		$keyword_ = isset($_GET["keyword"]) ? $_GET["keyword"] : null;
		$per_page_ = 15;
		$offset_ = $this->offset($page_index_, $per_page_);

		return array(
			"page_index" => $page_index_,
			"per_page" => $per_page_,
			'total_count' => pb_user_list(array("justcount" => true)),
			'items' => pb_user_list(array(
				'keyword' => $keyword_,
				"limit" => array($offset_, $per_page_)
			)),
		);
	}

	function items($args_){
		return $args_['items'];
	}

	function columns(){
		return array(
			'seq' => 'seq',
			'user_name' => '사용자명',
			'user_login' => 'id',
			'user_email' => '이메일',
			'status' => '상태',
		
			'button_area' => '',
		
		);
	}

	function column_header_classes($column_name_){
		

		switch($column_name_){
		
			case "seq" :
				return "col-seq text-center";
			case "user_name" :
				 return "col-2";
			case "user_login" :
				 return "col-2 text-center";
			case "user_email" :
				 return "col-2 text-center hidden-xs";
			case "status" :
				 return "col-2 text-center";
		
			case "button_area" :
				 return "col-4 text-right";
			
			default : 
				return '';
			break;
		}
	}
	function column_body_classes($key_, $item_){
		return $this->column_header_classes($key_);
	}

	function column_value($item_, $column_name_){

		$seq_ = $this->display_row_number();

		switch($column_name_){
		
			case "seq" :
				return $seq_;
			case "user_name" :
			case "user_login" :
			case "user_email" :
				return $item_[$column_name_];
			case "status" :
				return $item_['status_name'];

			
			case "button_area" :
				 ob_start();
				 ?>

				 <a href="<?=pb_admin_url("manage-user/edit/".$item_['id'])?>" class="btn btn-default">수정</a>
				
				 <?php


				 return ob_get_clean();

			

			default : 
				return '';
			break;
		}
	}

	function norowdata(){
		return "검색된 사용자가 없습니다.";	
	}
	
}

?>
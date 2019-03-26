<?php

class PB_authority_list_table extends PBListTable{

	function prepare(){

		global $pbdb;

		$page_index_ = isset($_GET["page_index"]) ? (int)$_GET["page_index"] : 0;
		$keyword_ = isset($_GET["keyword"]) ? $_GET["keyword"] : null;
		$per_page_ = 15;
		$offset_ = $this->offset($page_index_, $per_page_);

		return array(
			"page_index" => $page_index_,
			"per_page" => $per_page_,
			'total_count' => pb_authority_list(array("justcount" => true)),
			'items' => pb_authority_list(array(
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
			'seq' => 'SEQ',
			'auth_name' => '권한명',
			'slug' => '슬러그',
		
			'button_area' => '',
		
		);
	}

	function column_header_classes($column_name_){
		

		switch($column_name_){
		
			case "seq" :
				return "col-seq text-center";
			case "auth_name" :
				 return "col-4";
			case "slug" :
				 return "col-3 text-center";
		
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

		$row_index_ = $this->display_row_number();

		switch($column_name_){
		
			case "seq" :
				return $row_index_;

			case "auth_name" :
			ob_start();

			?>
			<a href="" data-master-id="<?=$item_['id']?>"><?=$item_['auth_name']?></a>

			<?php

			return ob_get_clean();
			case "slug" :
				 return $item_['slug'];

			case "button_area" :

				if($item_["slug"] === PB_AUTHORITY_SLUG_ADMINISTRATOR) return "";

				 ob_start();
				 ?>

				 <a href="javascript:_pb_authority_edit('<?=$item_['id']?>');" class="btn btn-default">수정</a>
				 <a href="javascript:_pb_authority_remove('<?=$item_['id']?>');" class="btn btn-black">삭제</a>


				 <?php


				 return ob_get_clean();

			

			default : 
				return '';
			break;
		}
	}

	function norowdata(){
		return "검색된 권한이 없습니다.";	
	}
	
}

class PB_authority_task_list_table extends PBListTable{

	function prepare(){
		global $pbdb;

		$auth_id_ = isset($_GET["master_id"]) ? $_GET["master_id"] : null;


		if(!strlen($auth_id_) || $auth_id_ < 0){
			return array(
				"page_index" => 0,
				"per_page" => 1,
				'hide_pagenav' => true,
				'total_count' => 0,
				'items' => array(),
			);
		}

		$task_types_ = pb_authority_task_types();

		foreach($task_types_ as $key_ => &$data_){
			$data_['slug'] = $key_;
		}

		$page_index_ = isset($_GET["page_index"]) ? (int)$_GET["page_index"] : 0;
		// $keyword_ = isset($_GET["keyword"]) ? $_GET["keyword"] : null;
		$per_page_ = count($task_types_);
		$offset_ = $this->offset($page_index_, $per_page_);

		global $_cached_authority_map, $_cached_auth_data;
		$_cached_authority_map = pb_authority_map($auth_id_);
		$_cached_auth_data = pb_authority($auth_id_);
		
		
		return array(
			"page_index" => $page_index_,
			"per_page" => $per_page_,
			'hide_pagenav' => true,
			'total_count' => $page_index_,
			'items' => $task_types_,
		);
	}

	function items($args_){
		return $args_['items'];
	}

	function columns(){
		return array(
			"task_name" => "작업명",
			"slug" => "슬러그",
			"grant_yn" => "권한부여",
			
		);
	}

	function column_header_classes($column_name_){
		$row_index_ = $this->current_row();

		switch($column_name_){
		
			case "task_name" :
				return "col-2 text-center";
			case "slug" :
				 return "col-4";
			case "grant_yn" :
				 return "col-2 text-center";
			
			default : 
				return '';
			break;
		}
	}
	function column_body_classes($key_, $item_){
		return $this->column_header_classes($key_);
	}

	function column_value($item_, $column_name_){
		$auth_id_ = isset($_GET["master_id"]) ? $_GET["master_id"] : null;
		$row_index_ = $this->current_row();


		switch($column_name_){
		
			case "task_name" :
				return $item_["name"];
			case "slug" :
				return $item_["slug"];

		
			case "grant_yn" :

				global $_cached_authority_map, $_cached_auth_data;


				$task_checked_ = isset($_cached_authority_map[$item_['slug']]);
				$task_disabled_ = $_cached_auth_data['slug'] === PB_AUTHORITY_SLUG_ADMINISTRATOR;

			ob_start();



			?>

			<input type="checkbox" name="grant_yn" value="Y" data-auth-id="<?=$auth_id_?>" data-auth-task="<?=$item_["slug"]?>" <?=$task_checked_? "checked" : "" ?> <?=$task_disabled_ ? "disabled" : ""?>>

			<?php

			return ob_get_clean();
				 
			

			default : 
				return '';
			break;
		}
	}

	function norowdata(){
		return "검색된 작업이 없습니다.";	
	}
	
}

?>
<?php

class PB_gcode_list_table extends PBListTable{

	function prepare(){

		global $pbdb;

		$page_index_ = isset($_GET["page_index"]) ? (int)$_GET["page_index"] : 0;
		$keyword_ = isset($_GET["keyword"]) ? $_GET["keyword"] : null;
		$per_page_ = 15;
		$offset_ = $this->offset($page_index_, $per_page_);

		return array(
			"page_index" => $page_index_,
			"per_page" => $per_page_,
			'total_count' => pb_gcode_list(array("justcount" => true)),
			'items' => pb_gcode_list(array(
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
			'code_id' => 'id',
			'code_nm' => '코드명',
			'use_yn' => '사용여부',
			
			
			'button_area' => '',
		
		);
	}

	function column_header_classes($column_name_){
		$row_index_ = $this->current_row();

		switch($column_name_){
		
			case "code_id" :
				return "col-2 text-center";
			case "code_nm" :
				 return "col-4";
			case "use_yn" :
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

		$row_index_ = $this->current_row();

		switch($column_name_){
		
			case "code_id" :
				return $item_['code_id'];

			case "code_nm" :
			ob_start();

			?>
			<a href="" data-master-id="<?=$item_['code_id']?>"><?=$item_['code_nm']?></a>

			<?php

			return ob_get_clean();
			case "use_yn" :
				 return $item_['use_yn'];

			case "button_area" :
				 ob_start();
				 ?>

				 <a href="javascript:_pb_gcode_edit('<?=$item_['code_id']?>');" class="btn btn-default">수정</a>
				 <a href="javascript:_pb_gcode_remove('<?=$item_['code_id']?>');" class="btn btn-black">삭제</a>


				 <?php


				 return ob_get_clean();

			

			default : 
				return '';
			break;
		}
	}

	function norowdata(){
		return "검색된 공통코드가 없습니다.";	
	}
	
}

class PB_gcode_dtl_list_table extends PBListTable{

	function prepare(){

		global $pbdb;

		$page_index_ = isset($_GET["page_index"]) ? (int)$_GET["page_index"] : 0;
		$keyword_ = isset($_GET["keyword"]) ? $_GET["keyword"] : null;
		$per_page_ = 15;
		$offset_ = $this->offset($page_index_, $per_page_);

		$code_id_ = isset($_GET["master_id"]) ? $_GET["master_id"] : null;
		
		return array(
			"page_index" => $page_index_,
			"per_page" => $per_page_,
			'total_count' => pb_gcode_dtl_list(array(
				'code_id' => $code_id_,
				'only_use' => false,
				'keyword' => $keyword_,
				'justcount' => true,
			)),
			'items' => pb_gcode_dtl_list(array(
				'code_id' => $code_id_,
				'only_use' => false,
				'keyword' => $keyword_,
				'limit' => array($offset_, $per_page_),
			)),
		);
	}

	function items($args_){
		return $args_['items'];
	}

	function columns(){
		return array(
			"code_did" => "did",
			"code_dnm" => "상세코드명",
			"use_yn" => "사용여부",
			'sort_char' => '정렬순서',
			"button_area" => "",
			
		);
	}

	function column_header_classes($column_name_){
		$row_index_ = $this->current_row();

		switch($column_name_){
		
			case "code_did" :
				return "col-2 text-center";
			case "code_dnm" :
				 return "col-4";
			case "use_yn" :
				 return "col-2 text-center";
			case "sort_char" :
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

		$row_index_ = $this->current_row();

		switch($column_name_){
		
			case "code_did" :
				return $item_['code_did'];

			case "code_dnm" :
				return $item_['code_dnm'];
			case "use_yn" :
				 return $item_['use_yn'];
			case "sort_char" :
				 return $item_['sort_char'];

			
			case "button_area" :
				 ob_start();
				 ?>

				 <a href="javascript:_pb_gcode_dtl_edit('<?=$item_['code_id']?>', '<?=$item_['code_did']?>');" class="btn btn-default">수정</a>
				 <a href="javascript:_pb_gcode_dtl_remove('<?=$item_['code_id']?>', '<?=$item_['code_did']?>');" class="btn btn-black">삭제</a>


				 <?php


				 return ob_get_clean();

			

			default : 
				return '';
			break;
		}
	}

	function norowdata(){
		return "검색된 상세코드가 없습니다.";	
	}
	
}

?>
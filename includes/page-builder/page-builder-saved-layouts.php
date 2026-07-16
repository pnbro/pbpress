<?php
/**
 * 저장서식 모듈 — DO 정의 + 헬퍼 함수 + 렌더러
 */

if(!defined('PB_DOCUMENT_PATH')){ die('-1'); }

// Data Object 정의
global $pb_saved_layouts_do;
$pb_saved_layouts_do = pbdb_data_object("pb_saved_layouts", array(
	"id"          => array("type" => PBDB_DO::TYPE_INT, "length" => 11, "ai" => true, "pk" => true),
	"slug"        => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "서식 슬러그"),
	"title"       => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 255, "comment" => "서식 제목"),
	"category"    => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "comment" => "카테고리"),
	"layout_json" => array("type" => PBDB_DO::TYPE_LONGTEXT, "comment" => "JSON 데이터"),
	"reg_date"    => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일"),
	"mod_date"    => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일"),
), "저장서식");

/**
 * 저장서식 데이터 조회 (ID 또는 슬러그)
 */
function pb_saved_layout($id_or_slug_){
	global $pb_saved_layouts_do;
	$statement_ = $pb_saved_layouts_do->statement();

	if(is_numeric($id_or_slug_)){
		$statement_->add_compare_condition("id", $id_or_slug_);
	}else{
		$statement_->add_compare_condition("slug", $id_or_slug_);
	}

	return $statement_->get_first_row();
}

/**
 * 저장서식 엘리먼트 렌더러
 * 페이지 빌더에서 saved-layout 엘리먼트 렌더링 시 호출
 */
function pb_page_builder_render_saved_layout($layout_id_){
	$layout_ = pb_saved_layout($layout_id_);
	if(!$layout_) return;

	$data_ = json_decode($layout_['layout_json'], true);
	if(!$data_ || !isset($data_['elementcontent'])) return;

	foreach($data_['elementcontent'] as $element_data_){
		if(!isset($element_data_['name'])) continue;

		$element_class_ = pb_page_builder_element_class($element_data_['name']);
		if(!$element_class_) continue;

		call_user_func_array(
			array($element_class_, "_render"),
			array($element_data_, $element_data_['elementcontent'])
		);
	}
}


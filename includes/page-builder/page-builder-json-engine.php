<?php
/**
 * PBPress Page Builder JSON Engine
 * XML 하위 호환성 유지 + JSON 네이티브 파서/렌더러
 */

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

/**
 * 통합 데이터 파서 (XML/JSON 자동 감지)
 */
function pb_page_builder_parse($content_){
	$content_ = trim($content_);
	if(empty($content_)) return array('settings' => array('style'=>'','script'=>''), 'elementcontent' => array());

	// JSON 시그니처 감지
	if(strpos($content_, '{') === 0){
		return pb_page_builder_parse_json($content_);
	}
	
	// 하위 호환: XML 파서 호출
	return pb_page_builder_parse_xml($content_);
}

/**
 * 신규 JSON 데이터 파서
 */
function pb_page_builder_parse_json($json_string_){
	$data_ = json_decode($json_string_, true);

	if(json_last_error() !== JSON_ERROR_NONE){
		return new PBError(-10, __("잘못된 JSON 형식입니다."), __("문서형식오류"));
	}

	// 데이터 보정: XML 파서 출력 구조와 동기화
	if(!isset($data_['settings'])) $data_['settings'] = array('style' => '', 'script' => '');
	if(!isset($data_['elementcontent'])) $data_['elementcontent'] = array();

	return $data_;
}

/**
 * 통합 렌더링 (파서 자동 감지 후 렌더링)
 */
function pb_page_builder_render_by_content($content_){
	$builder_data_ = pb_page_builder_parse($content_);
	if(pb_is_error($builder_data_)) return $builder_data_->display();

	pb_page_builder_render($builder_data_);
}

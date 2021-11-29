<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

abstract class PB_PAGE_STATUS extends PBConstClass{
	const WRITING = "00001";
	const PUBLISHED = "00003";
	const UNPUBLISHED = "00009";

	static public function names(){

		global $_pb_page_status_names;
		if(isset($_pb_page_status_names)) return $_pb_page_status_names;

		$_pb_page_status_names = array(
			PB_PAGE_STATUS::WRITING => __('작성중'),
			PB_PAGE_STATUS::PUBLISHED => __('공개'),
			PB_PAGE_STATUS::UNPUBLISHED => __('비공개'),
		);

		return $_pb_page_status_names;
	}
}

pb_authority_task_add_type('manage_page', array(
	'name' => '페이지관리',
));
pb_authority_initial_register(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_page");

function _pb_rewrite_unique_slug_for_page($result_, $original_slug_, $retry_count_ = 0, $extra_data_){
	$check_data_ = pb_page_by_slug($result_);
	
	if(!isset($check_data_)){
		return $result_;
	}

	$excluded_page_id_ = isset($extra_data_['excluded_page_id']) ? $extra_data_['excluded_page_id'] : null;
	if(strlen($excluded_page_id_) && $check_data_['id'] === $excluded_page_id_) return $result_;

	return pb_rewrite_unique_slug($original_slug_, ++$retry_count_, $extra_data_);
}
pb_hook_add_filter("pb_rewrite_unique_slug", "_pb_rewrite_unique_slug_for_page");

if(function_exists("pb_page_builder")){
	function _pb_page_render_page_html_for_page_builder($page_html_, $page_data_){
		$temp_ = pb_page_builder_parse_xml($page_html_);

		if(!$temp_ || pb_is_error($temp_)){
			return $page_html_;
		}
		
		ob_start();
		pb_page_builder_render($temp_);
		return ob_get_clean();
	}
	pb_hook_add_filter('pb_page_html', "_pb_page_render_page_html_for_page_builder");
}

?>
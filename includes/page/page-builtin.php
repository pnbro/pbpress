<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_page_install_tables($args_){
	global $pbdb;

	$args_[] = "CREATE TABLE IF NOT EXISTS `pages` (
		`id` bigint(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',

		`slug` varchar(100) NOT NULL COMMENT '슬러그',

		`page_title` varchar(200) NOT NULL COMMENT '페이지명',
		`page_html` longtext NOT NULL COMMENT '페이지HTML',
		`status` varchar(5) NOT NULL COMMENT '페이지상태(PA001)',

		`wrt_id` BIGINT(11) NOT NULL COMMENT '작성자ID',
		
		`reg_date` datetime DEFAULT null COMMENT '등록일자',
		`mod_date` datetime DEFAULT null COMMENT '수정일자',
		PRIMARY KEY (`id`),
		CONSTRAINT `pages_fk1` FOREIGN KEY (`wrt_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB COMMENT='페이지';";

	return $args_;
}
pb_hook_add_filter('pb_install_tables', "pb_page_install_tables");

define('PB_PAGE_STATUS_WRITING', '00001');
define('PB_PAGE_STATUS_PUBLISHED', '00003');
define('PB_PAGE_STATUS_UNPUBLISHED', '00009');

function pb_page_initialize_gcode_list($gcode_list_){

	$gcode_list_['PAG01'] = array(
		'name' => '페이지등록상태',
		'data' => array(
			PB_PAGE_STATUS_WRITING => "작성중",
			PB_PAGE_STATUS_PUBLISHED => "공개",
			PB_PAGE_STATUS_UNPUBLISHED => "비공개",
		),
	);

	return $gcode_list_;
}
pb_hook_add_filter("pb_intialize_gcode_list", "pb_page_initialize_gcode_list");

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
		$temp_ = @pb_page_builder_parse_xml($page_html_);

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
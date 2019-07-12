<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_page_list($conditions_ = array()){
	global $pbdb;

	$query_ = "SELECT 

					 pages.id id
					,pages.slug slug
					,pages.page_title page_title
					,pages.page_html page_html

					,pages.wrt_id wrt_id
					,pages.status status
					,".pb_query_gcode_dtl_name("PAG01", "pages.status")." status_name

					,users.user_login wrt_login
					,users.user_email wrt_email
					,users.user_name wrt_name
					
					,pages.reg_date reg_date
					,DATE_FORMAT(pages.reg_date, '%Y.%m.%d') reg_date_ymd
					,DATE_FORMAT(pages.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi
					,DATE_FORMAT(pages.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis
					
					,pages.mod_date mod_date
					,DATE_FORMAT(pages.mod_date, '%Y.%m.%d') mod_date_ymd
					,DATE_FORMAT(pages.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi
					,DATE_FORMAT(pages.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis

					 ".pb_hook_apply_filters('pb_page_list_fields', "", $conditions_)." 
	FROM pages

	LEFT OUTER JOIN users
	ON   users.id = pages.wrt_id

	".pb_hook_apply_filters('pb_page_list_join', "", $conditions_)." 

	WHERE 1 

	 ".pb_hook_apply_filters('pb_page_list_where', "", $conditions_)."  

	";

	if(isset($conditions_['id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['id'], "pages.id")." ";
	}
	if(isset($conditions_['slug'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['slug'], "pages.slug")." ";
	}
	if(isset($conditions_['status'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['status'], "pages.status")." ";
	}
	if(isset($conditions_['wrt_id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['wrt_id'], "pages.wrt_id")." ";
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND ".pb_query_keyword_search(pb_hook_apply_filters('pb_page_list_keyword', array(
			"pages.slug",
			"users.user_login",
			"users.user_email",
			"users.user_name",
		)), $conditions_['keyword'])." ";
	}

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $pbdb->get_var("SELECT COUNT(*) FROM (".$query_.") TEMP");
	}

	if(isset($conditions_['orderby']) && strlen($conditions_['orderby'])){
		$query_ .= " ".$conditions_['orderby']." ";
	}else{
		$query_ .= " ORDER BY id DESC ";
	}

	if(isset($conditions_['limit'])){
        $query_ .= " LIMIT ".$conditions_['limit'][0].",".$conditions_['limit'][1]." ";
    }

	return pb_hook_apply_filters("pb_page_list", $pbdb->select($query_));
}

function pb_page($id_){
	$page_ = pb_page_list(array("id" => $id_));
	if(!isset($page_) || count($page_) <= 0) return null;
	return $page_[0];
}

function pb_page_by_slug($slug_){
	$page_ = pb_page_list(array("slug" => $slug_));
	if(!isset($page_) || count($page_) <= 0) return null;
	return $page_[0];
}

function _pb_page_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_page_parse_fields",array(

		'slug' => '%s',
		'page_title' => '%s',
		'page_html' => '%s',
		'status' => '%s',
		'wrt_id' => '%d',
		'reg_date' => '%s',
		'mod_date' => '%s',
		
	)), $data_);
}


function pb_page_insert($raw_data_){
	global $pbdb;

	$raw_data_ = _pb_page_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$insert_id_ = $pbdb->insert("pages", $data_, $format_);
	pb_hook_do_action("pb_page_inserted", pb_page($insert_id_));
	return $insert_id_;
}

function pb_page_update($id_, $raw_data_){
	global $pbdb;

	$raw_data_ = _pb_page_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$result_ = $pbdb->update("pages", $data_, array("id" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_page_updated", pb_page($id_));

	return $result_;
}

function pb_page_delete($id_){
	global $pbdb;

	$result_ = $pbdb->delete("pages", array("id" => $id_), array("%d"));
	pb_hook_do_action("pb_page_deleted", pb_page($id_));
	return $result_;
}

function pb_page_rewrite_slug($slug_, $excluded_page_id_ = null, $retry_count_ = 0){
	$temp_slug_ = $slug_;
	if($retry_count_ > 0){
		$temp_slug_ .= "_".$retry_count_;
	}

	$check_data1_ = pb_page_by_slug($temp_slug_);
	$check_data2_ = pb_rewrite_data($temp_slug_);
	if(!isset($check_data1_) && !isset($check_data2_)){
		return $temp_slug_;
	}

	if(strlen($excluded_page_id_) && $check_data1_['id'] === $excluded_page_id_) return $temp_slug_;

	return pb_page_rewrite_slug($slug_, $excluded_page_id_, ++$retry_count_);
}

function pb_page_write($data_){
	$page_title_ = isset($data_['page_title']) ? $data_['page_title'] : null;
	$page_html_ = isset($data_['page_html']) ? $data_['page_html'] : null;
	$status_ = isset($data_['status']) ? $data_['status'] : PB_PAGE_STATUS_WRITING;
	$slug_ = isset($data_['slug']) ? $data_['slug'] : null;
	$slug_ = strlen($slug_) ? $slug_ : $page_title_;
	$slug_ = pb_slugify($slug_);
	
	if(!strlen($slug_)) return new PBError(403, "페이지 슬러그가 잘못되었습니다.", "잘못된 슬러그");

	$insert_data_ = array(
		'page_title' => $page_title_,
		'page_html' => $page_html_,
		'status' => $status_,
		'slug' => pb_page_rewrite_slug($slug_),
		'wrt_id' => pb_current_user_id(),
		'reg_date' => pb_current_time(),
	);

	$inserted_id_ = pb_page_insert($insert_data_);
	pb_hook_do_action('pb_page_writed', pb_page($inserted_id_));
	return $inserted_id_;
}

function pb_page_edit($id_, $data_){
	$update_data_ = array(
		'mod_date' => pb_current_time(),
	);

	if(isset($data_['page_title'])){
		$update_data_['page_title'] = $data_['page_title'];
	}

	if(isset($data_['page_html'])){
		$update_data_['page_html'] = $data_['page_html'];
	}

	if(isset($data_['status'])){
		$update_data_['status'] = $data_['status'];
	}

	if(isset($data_['slug'])){
		$page_title_ = isset($update_data_['page_title']) ? $update_data_['page_title'] : null;
		$update_data_['slug'] = pb_slugify((strlen($data_['slug']) ? $data_['slug'] : $page_title_));
		$update_data_['slug'] = pb_page_rewrite_slug($update_data_['slug'], $id_);

		if(!strlen($update_data_['slug'])) return new PBError(403, "페이지 슬러그가 잘못되었습니다.", "잘못된 슬러그");
	}

	pb_page_update($id_, $update_data_);
	pb_hook_do_action('pb_page_edited', pb_page($id_));	
	return $id_;
}

function pb_current_page(){
	global $pbpage;
	return $pbpage;
}

function pb_page_title($id_ = null){
	if(!strlen($id_)){
		$current_page_data_ = pb_current_page();
		if(!isset($current_page_data_)) return null;

		return pb_hook_apply_filters('pb_page_title', $current_page_data_['page_title'], $current_page_data_);
	}

	$page_data_ = pb_page($id_);
	if(!isset($page_data_)) return null;
	return pb_hook_apply_filters('pb_page_title', $page_data_['page_title'], $page_data_);
}
function pb_page_html($id_ = null){
	if(!strlen($id_)){
		$current_page_data_ = pb_current_page();
		if(!isset($current_page_data_)) return null;

		return pb_hook_apply_filters('pb_page_html', $current_page_data_['page_html'], $current_page_data_);
	}

	$page_data_ = pb_page($id_);
	if(!isset($page_data_)) return null;
	return pb_hook_apply_filters('pb_page_html', $page_data_['page_html'], $page_data_);
}

include(PB_DOCUMENT_PATH . 'includes/page/page-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/page/page-builtin-rewrite.php');
include(PB_DOCUMENT_PATH . 'includes/page/page-meta.php');
include(PB_DOCUMENT_PATH . 'includes/page/page-adminpage.php');

?>
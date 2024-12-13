<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $pages_do;
$pages_do = pbdb_data_object("pages", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'slug'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, 'nn' => true, 'index' => true, "comment" => "슬러그"),
	'page_title'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 200, "nn" => true, "comment" => "페이지명"),
	'page_html'		 => array("type" => PBDB_DO::TYPE_LONGTEXT, "comment" => "페이지HTML"),

	'status'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 5, "nn" => true, "index" => true, "comment" => "페이지상태(PA001)"),
	'wrt_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'users',
		'column' => 'id',
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), 'nn' => true, "comment" => "사용자ID"),
		
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"페이지");
$pages_do->add_legacy_field_filter('pb_page_parse_fields', array());

function pb_page_statement($conditions_ = array()){
	global $pages_do, $users_do;

	$statement_ = $pages_do->statement();
	$statement_->add_field(
		PB_PAGE_STATUS::subquery("pages.status", 'status_name'),
		"DATE_FORMAT(pages.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(pages.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(pages.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(pages.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(pages.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(pages.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	$statement_->add_legacy_field_filter('pb_page_list_fields', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_page_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_page_list_where', '', $conditions_);

	$users_join_cond_ = PBDB_SS_conditions();
	$users_join_cond_->add_compare("users.id", "pages.wrt_id");

	$statement_->add_join_statement("LEFT OUTER JOIN", $users_do->statement(), "users", $users_join_cond_, array(
		"user_login wrt_login",
		"user_email wrt_email",
		"user_name wrt_name",
	));

	if(isset($conditions_['id'])){
		$statement_->add_in_condition("pages.id", $conditions_['id']);
	}
	if(isset($conditions_['slug'])){
		$statement_->add_in_condition("pages.slug", $conditions_['slug']);
	}
	if(isset($conditions_['status'])){
		$statement_->add_in_condition("pages.status", $conditions_['status']);
	}
	if(isset($conditions_['wrt_id'])){
		$statement_->add_in_condition("pages.wrt_id", $conditions_['wrt_id']);
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$statement_->add_like_condition(pb_hook_apply_filters('pb_page_list_keyword', array(
			"pages.slug",
			"pages.page_title",
			"users.user_login",
			"users.user_email",
			"users.user_name",

		)), $conditions_['keyword'], true, true);
	}

	return pb_hook_apply_filters('pb_page_statement', $statement_, $conditions_);
}

function pb_page_list($conditions_ = array()){
	$statement_ = pb_page_statement($conditions_);

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
	}

	return pb_hook_apply_filters("pb_page_list", $statement_->select($orderby_, $limit_));
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

function pb_page_insert($raw_data_){
	global $pages_do;
	$insert_id_ = $pages_do->insert($raw_data_);
	pb_hook_do_action("pb_page_inserted", $insert_id_);
	return $insert_id_;
}

function pb_page_update($id_, $raw_data_){
	global $pages_do;

	pb_hook_do_action("pb_page_update", $id_, $raw_data_);
	$result_ = $pages_do->update($id_, $raw_data_);
	pb_hook_do_action("pb_page_updated", $id_);

	return $result_;
}

function pb_page_delete($id_){
	global $pages_do;
	pb_hook_do_action("pb_page_delete", $id_);
	$pages_do->delete($id_);
	pb_hook_do_action("pb_page_deleted", $id_);
}

function pb_page_rewrite_slug($slug_, $excluded_page_id_ = null){
	return pb_rewrite_unique_slug($slug_, 0, array("excluded_page_id" => $excluded_page_id_));
}

function pb_page_write($data_){
	$page_title_ = isset($data_['page_title']) ? $data_['page_title'] : null;
	$page_html_ = isset($data_['page_html']) ? $data_['page_html'] : null;
	$status_ = isset($data_['status']) ? $data_['status'] : PB_PAGE_STATUS::WRITING;
	$slug_ = isset($data_['slug']) ? $data_['slug'] : null;
	$slug_ = strlen($slug_) ? $slug_ : $page_title_;
	$slug_ = pb_slugify($slug_);
	
	if(!strlen($slug_)) return new PBError(403, __("페이지 슬러그가 잘못되었습니다."), __("잘못된 슬러그"));

	$insert_data_ = array(
		'page_title' => $page_title_,
		'page_html' => $page_html_,
		'status' => $status_,
		'slug' => pb_page_rewrite_slug($slug_),
		'wrt_id' => pb_current_user_id(),
		'reg_date' => pb_current_time(),
	);

	$inserted_id_ = pb_page_insert(pb_hook_apply_filters('pb_page_before_write', $insert_data_));
	pb_hook_do_action('pb_page_writed', $inserted_id_);
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

		if(!strlen($update_data_['slug'])) return new PBError(403, __("페이지 슬러그가 잘못되었습니다."), __("잘못된 슬러그"));
	}

	pb_page_update($id_, pb_hook_apply_filters('pb_page_before_edit', $update_data_, $id_, $data_));
	pb_hook_do_action('pb_page_edited', $id_);	
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

function pb_page_url($id_ = null){
	if(!strlen($id_)){
		$current_page_data_ = pb_current_page();
		if(!isset($current_page_data_)) return null;

		$page_url_ = pb_home_url($current_page_data_['slug']);
		return pb_hook_apply_filters('pb_page_url', $page_url_, $current_page_data_);
	}

	$page_data_ = pb_page($id_);
	if(!isset($page_data_)) return null;
	$page_url_ = pb_home_url($page_data_['slug']);
	return pb_hook_apply_filters('pb_page_url', $page_url_, $page_data_);
}

function pb_front_page_id(){
	return pb_option_value("pb_front_page_id");
}
function pb_change_front_page($page_id_){
	pb_option_update("pb_front_page_id", $page_id_);
}
function pb_front_page(){
	$front_page_id_ = pb_front_page_id();
	if(!strlen($front_page_id_)) return null;
	return pb_page($front_page_id_);
}
function _pb_page_url_hook_for_front_page($result_, $page_data_){
	if($page_data_['id'] === pb_front_page_id()){
		return pb_home_url();
	}
	return $result_;
}
pb_hook_add_filter('pb_page_url', '_pb_page_url_hook_for_front_page');

include(PB_DOCUMENT_PATH . 'includes/page/page-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/page/page-builtin-rewrite.php');
include(PB_DOCUMENT_PATH . 'includes/page/page-meta.php');
include(PB_DOCUMENT_PATH . 'includes/page/page-revision.php');
__iinclude(PB_DOCUMENT_PATH . 'includes/page/page-adminpage.php');

?>
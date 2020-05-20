<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_POST_SHORT_LENGTH', 100);

global $_pb_post_type_label_defaults;

$_pb_post_type_label_defaults = array(
	'list' => "글내역",
	'add' => "글추가",
	'update' => "글수정",
	'delete' => "글삭제",
	'button_add' => "글추가",
	'button_update' => "글수정",
	'before_delete' => "해당 글을 삭제합니다. 계속하시겠습니까?",
	'after_delete' => "글이 삭제되었습니다.",
	'no_results' => "검색된 글이 없습니다.",
);

function pb_post_types(){
	global $_pb_post_types;
	if(isset($_pb_post_types)) return $_pb_post_types;

	$_pb_post_types = pb_hook_apply_filters('pb_post_types', array(
		'post' => array(
			'name' => '글',
			'label' => array(),
			'adminpage_sort' => 8,
		),
	));

	global $_pb_post_type_label_defaults;

	foreach($_pb_post_types as $key_ => &$type_data_){
		if(!isset($type_data_['label'])) $type_data_['label'] = array();

		$type_data_['label'] = array_merge($_pb_post_type_label_defaults, $type_data_['label']);
	}

	return $_pb_post_types;
}

global $posts_do;
$posts_do = pbdb_data_object("posts", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'type'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "nn" => true, "index" => true, "comment" => "글형식"),
	'slug'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, 'nn' => true, 'index' => true, "comment" => "슬러그"),
	'post_title'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 200, "nn" => true, "comment" => "글제목"),
	'post_html'		 => array("type" => PBDB_DO::TYPE_LONGTEXT, "comment" => "글HTML"),
	'post_short'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => PB_POST_SHORT_LENGTH, "comment" => "글(줄임)"),
	'featured_image_path'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 500, "comment" => "대표이미지"),

	'status'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 5, "nn" => true, "comment" => "글상태(PA001)"),
	'wrt_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'users',
		'column' => 'id',
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), 'nn' => true, "comment" => "사용자ID"),
		
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"글");
$posts_do->add_legacy_field_filter('pb_post_parse_fields', array());

function pb_post_statement($conditions_ = array()){
	global $posts_do, $users_do;

	$statement_ = $posts_do->statement();
	$statement_->add_field(
		pb_query_gcode_dtl_name("PST01", "posts.status")." status_name",
		"DATE_FORMAT(posts.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(posts.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(posts.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(posts.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(posts.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(posts.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	$statement_->add_legacy_field_filter('pb_post_list_fields', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_post_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_post_list_where', '', $conditions_);

	$users_join_cond_ = PBDB_SS_conditions();
	$users_join_cond_->add_compare("users.id", "posts.wrt_id");

	$statement_->add_join_statement("LEFT OUTER JOIN", $users_do->statement(), "users", $users_join_cond_, array(
		"user_login wrt_login",
		"user_email wrt_email",
		"user_name wrt_name",
	));

	if(isset($conditions_['id'])){
		$statement_->add_in_condition("posts.id", $conditions_['id']);
	}
	if(isset($conditions_['type'])){
		$statement_->add_in_condition("posts.type", $conditions_['type']);
	}
	if(isset($conditions_['slug'])){
		$statement_->add_in_condition("posts.slug", $conditions_['slug']);
	}
	if(isset($conditions_['status'])){
		$statement_->add_in_condition("posts.status", $conditions_['status']);
	}
	if(isset($conditions_['wrt_id'])){
		$statement_->add_in_condition("posts.wrt_id", $conditions_['wrt_id']);
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$statement_->add_like_condition(pb_hook_apply_filters('pb_post_list_keyword', array(
			"posts.slug",
			"users.user_login",
			"users.user_email",
			"users.user_name",
		)), $conditions_['keyword']);
	}

	if(isset($conditions_['_prev_sibling_from_id'])){
		$statement_->add_custom_condition(PBDB_P.".".PBDB_P." < ".PBDB_P." AND posts.id != ".PBDB_P, array($conditions_['_prev_sibling_from_prefix'], $conditions_['_prev_sibling_from_column'], $conditions_['_prev_sibling_from_column_value'], $conditions_['_prev_sibling_from_id']), array("%d", "%d", "%s", "%d"));
	}
	if(isset($conditions_['_next_sibling_from_id'])){
		$statement_->add_custom_condition(PBDB_P.".".PBDB_P." > ".PBDB_P." AND posts.id != ".PBDB_P, array($conditions_['_next_sibling_from_prefix'], $conditions_['_next_sibling_from_column'], $conditions_['_next_sibling_from_column_value'], $conditions_['_next_sibling_from_id']), array("%d", "%d", "%s", "%d"));
	}

	return pb_hook_apply_filters('pb_post_statement', $statement_, $conditions_);
}

function pb_post_list($conditions_ = array()){
	$statement_ = pb_post_statement($conditions_);

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
	}

	return pb_hook_apply_filters("pb_post_list", $statement_->select($orderby_, $limit_));
}

function pb_post($id_){
	$post_ = pb_post_list(array("id" => $id_));
	if(!isset($post_) || count($post_) <= 0) return null;
	return $post_[0];
}

function pb_post_by_slug($type_, $slug_){
	$post_ = pb_post_list(array("type" => $type_, "slug" => $slug_));
	if(!isset($post_) || count($post_) <= 0) return null;
	return $post_[0];
}

function pb_sibling_post($id_, $column_ = "id", $prefix_ = "posts", $sibling_ = "next"){
	if(!strlen($id_)){
		$id_ = pb_current_post_id();
		if(!strlen($id_)) return null;
	}

	$post_data_ = pb_post($id_);
	if(!isset($post_data_)) return null;

	$conditions_ = array(
		'type' => $post_data_['type'],
		'status' => PB_POST_STATUS_PUBLISHED,
	);
	$conditions_['_'.$sibling_.'_sibling_from_id'] = $id_;
	$conditions_['_'.$sibling_.'_sibling_from_prefix'] = $prefix_;
	$conditions_['_'.$sibling_.'_sibling_from_column'] = $column_;
	$conditions_['_'.$sibling_.'_sibling_from_column_value'] = $post_data_[$column_];
	$statement_ = pb_post_statement(pb_hook_apply_filters('pb_'.$sibling_.'_post_conditions', $conditions_));
	$results_ = $statement_->select(pb_hook_apply_filters('pb_'.$sibling_.'_post_orderby', "{$column_} ".($sibling_ === "next" ? "ASC" : "DESC")), array(0, 1));

	if(count($results_) <= 0) return null;
	return pb_hook_apply_filters('pb_'.$sibling_.'_post', $results_[0]);
}

function pb_prev_post($id_ = null, $column_ = "id", $prefix_ = "posts"){
	if(!strlen($id_)){
		$id_ = pb_current_post_id();
		if(!strlen($id_)) return null;
	}

	global $_pb_prev_post,
		$_pb_prev_post_from_id,
		$_pb_prev_post_from_column,
		$_pb_prev_post_from_prefix;

	if(isset($_pb_prev_post)
		&& $_pb_prev_post_from_id === $id_
		&& $_pb_prev_post_from_column === $column_
		&& $_pb_prev_post_from_prefix === $prefix_){
		return $_pb_prev_post;
	}

	$_pb_prev_post_from_id = $id_;
	$_pb_prev_post_from_column = $column_;
	$_pb_prev_post_from_prefix = $prefix_;
	$_pb_prev_post = pb_sibling_post($id_, $column_, $prefix_, "prev");

	return $_pb_prev_post;
}
function pb_next_post($id_ = null, $column_ = "id", $prefix_ = "posts"){
	if(!strlen($id_)){
		$id_ = pb_current_post_id();
		if(!strlen($id_)) return null;
	}

	global $_pb_next_post,
		$_pb_next_post_from_id,
		$_pb_next_post_from_column,
		$_pb_next_post_from_prefix;

	if(isset($_pb_next_post)
		&& $_pb_next_post_from_id === $id_
		&& $_pb_next_post_from_column === $column_
		&& $_pb_next_post_from_prefix === $prefix_){
		return $_pb_next_post;
	}

	$_pb_next_post_from_id = $id_;
	$_pb_next_post_from_column = $column_;
	$_pb_next_post_from_prefix = $prefix_;
	$_pb_next_post = pb_sibling_post($id_, $column_, $prefix_, "next");

	return $_pb_next_post;
}

function pb_exists_prev_post($id_ = null){
	$check_ = pb_prev_post($id_);
	return isset($check_);
}
function pb_exists_next_post($id_ = null){
	$check_ = pb_next_post($id_);
	return isset($check_);
}

function pb_prev_post_url($id_ = null){
	$prev_post_data_ = pb_prev_post($id_);
	if(!isset($prev_post_data_)) return null;
	return pb_post_url($prev_post_data_['id']);
}
function pb_next_post_url($id_ = null){
	$next_post_data_ = pb_next_post($id_);
	if(!isset($next_post_data_)) return null;
	return pb_post_url($next_post_data_['id']);
}

function pb_prev_post_title($id_ = null){
	$prev_post_data_ = pb_prev_post($id_);
	if(!isset($prev_post_data_)) return null;
	return $prev_post_data_['post_title'];
}
function pb_next_post_title($id_ = null){
	$next_post_data_ = pb_next_post($id_);
	if(!isset($next_post_data_)) return null;
	return $next_post_data_['post_title'];
}


function pb_post_insert($raw_data_){
	global $posts_do;
	$insert_id_ = $posts_do->insert($raw_data_);
	pb_hook_do_action("pb_post_inserted", $insert_id_);
	return $insert_id_;
}

function pb_post_update($id_, $raw_data_){
	global $posts_do;

	pb_hook_do_action("pb_post_update", $id_, $raw_data_);
	$result_ = $posts_do->update($id_, $raw_data_);
	pb_hook_do_action("pb_post_updated", $id_);

	return $result_;
}

function pb_post_delete($id_){
	global $posts_do;
	pb_hook_do_action("pb_post_delete", $id_);
	$posts_do->delete($id_);
	pb_hook_do_action("pb_post_deleted", $id_);
}

function pb_post_rewrite_slug($type_, $slug_, $excluded_post_id_ = null, $retry_count_ = 0){
	$temp_slug_ = $slug_;
	if($retry_count_ > 0){
		$temp_slug_ .= "-".$retry_count_;
	}

	$check_data_ = pb_post_list(array(
		'type' => $type_,
		'slug' => $temp_slug_,
	));
	if(count($check_data_) <= 0 || $check_data_[0]['id'] === $excluded_post_id_){
		return $temp_slug_;
	}

	return pb_post_rewrite_slug($type_, $slug_, $excluded_post_id_, ++$retry_count_);
}

function pb_post_write($data_){
	$type_ = isset($data_['type']) ? $data_['type'] : null;
	$post_title_ = isset($data_['post_title']) ? $data_['post_title'] : null;
	$post_html_ = isset($data_['post_html']) ? $data_['post_html'] : null;
	$status_ = isset($data_['status']) ? $data_['status'] : PB_POST_STATUS_WRITING;
	$featured_image_path_ = isset($data_['featured_image_path']) ? $data_['featured_image_path'] : null;
	$reg_date_ = @strlen($data_['reg_date']) ? $data_['reg_date'] : pb_current_time();
	$slug_ = isset($data_['slug']) ? $data_['slug'] : null;
	$slug_ = strlen($slug_) ? $slug_ : $post_title_;
	$slug_ = pb_slugify($slug_);

	$post_types_ = pb_post_types();
	
	if(!strlen($type_) || !isset($post_types_[$type_])) return new PBError(403, "글형식이 잘못되었습니다.", "잘못된 글형식");
	if(!strlen($slug_)) return new PBError(403, "글 슬러그가 잘못되었습니다.", "잘못된 슬러그");

	$insert_data_ = array(
		'post_title' => $post_title_,
		'post_html' => $post_html_,
		'type' => $type_,
		'status' => $status_,
		'featured_image_path' => $featured_image_path_,
		'slug' => pb_post_rewrite_slug($type_, $slug_),
		'wrt_id' => pb_current_user_id(),
		'reg_date' => $reg_date_,
		'mod_date' => pb_current_time(),
	);

	$inserted_id_ = pb_post_insert($insert_data_);
	pb_hook_do_action('pb_post_writed', $inserted_id_);
	return $inserted_id_;
}

function pb_post_edit($id_, $data_){
	$update_data_ = array(
		'mod_date' => pb_current_time(),
	);

	if(isset($data_['post_title'])){
		$update_data_['post_title'] = $data_['post_title'];
	}

	if(isset($data_['featured_image_path'])){
		$update_data_['featured_image_path'] = $data_['featured_image_path'];
	}

	if(isset($data_['post_html'])){
		$update_data_['post_html'] = $data_['post_html'];
	}

	if(isset($data_['status'])){
		$update_data_['status'] = $data_['status'];
	}
	if(isset($data_['reg_date'])){
		$update_data_['reg_date'] = $data_['reg_date'];
	}

	if(isset($data_['slug'])){
		$before_data_ = pb_post($id_);
		$post_title_ = isset($update_data_['post_title']) ? $update_data_['post_title'] : null;
		$update_data_['slug'] = pb_slugify((strlen($data_['slug']) ? $data_['slug'] : $post_title_));
		$update_data_['slug'] = pb_post_rewrite_slug($before_data_['type'], $update_data_['slug'], $id_);

		if(!strlen($update_data_['slug'])) return new PBError(403, "글 슬러그가 잘못되었습니다.", "잘못된 슬러그");
	}

	pb_post_update($id_, $update_data_);
	pb_hook_do_action('pb_post_edited', $id_);	
	return $id_;
}

function pb_current_post(){
	global $pbpost;
	return $pbpost;
}
function pb_current_post_id(){
	$post_data_ = pb_current_post();
	if(!isset($post_data_)) return null;
	return $post_data_['id'];
}

function pb_post_title($id_ = null){
	if(!strlen($id_)){
		$current_post_data_ = pb_current_post();
		if(!isset($current_post_data_)) return null;

		return pb_hook_apply_filters('pb_post_title', $current_post_data_['post_title'], $current_post_data_);
	}

	$post_data_ = pb_post($id_);
	if(!isset($post_data_)) return null;
	return pb_hook_apply_filters('pb_post_title', $post_data_['post_title'], $post_data_);
}
function pb_post_html($id_ = null){
	if(!strlen($id_)){
		$current_post_data_ = pb_current_post();
		if(!isset($current_post_data_)) return null;

		return pb_hook_apply_filters('pb_post_html', $current_post_data_['post_html'], $current_post_data_);
	}

	$post_data_ = pb_post($id_);
	if(!isset($post_data_)) return null;
	return pb_hook_apply_filters('pb_post_html', $post_data_['post_html'], $post_data_);
}
function pb_post_featured_image_url($id_ = null){
	if(!strlen($id_)){
		$current_post_data_ = pb_current_post();
		if(!isset($current_post_data_)) return null;
		if(!strlen($current_post_data_['featured_image_path'])) return null;

		return pb_hook_apply_filters('pb_post_featured_image_url', pb_filebase_url($current_post_data_['featured_image_path']), $current_post_data_);
	}

	$post_data_ = pb_post($id_);
	if(!isset($post_data_)) return null;
	if(!strlen($post_data_['featured_image_path'])) return null;
	return pb_hook_apply_filters('pb_post_featured_image_url', pb_filebase_url($post_data_['featured_image_path']), $post_data_);
}

function pb_post_url($id_ = null){
	if(!strlen($id_)){
		$current_post_data_ = pb_current_post();
		if(!isset($current_post_data_)) return null;

		$post_url_ = pb_home_url($current_post_data_['type'].'/'.$current_post_data_['slug']);
		return pb_hook_apply_filters('pb_post_url', $post_url_, $current_post_data_);
	}

	$post_data_ = pb_post($id_);
	if(!isset($post_data_)) return null;
	$post_url_ = pb_home_url($post_data_['type'].'/'.$post_data_['slug']);
	return pb_hook_apply_filters('pb_post_url', $post_url_, $post_data_);
}

include(PB_DOCUMENT_PATH . 'includes/post/post-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/post/post-builtin-rewrite.php');
include(PB_DOCUMENT_PATH . 'includes/post/post-meta.php');
include(PB_DOCUMENT_PATH . 'includes/post/post-revision.php');
include(PB_DOCUMENT_PATH . 'includes/post/post-adminpage.php');

?>
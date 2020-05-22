<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $post_categories_do;
$post_categories_do = pbdb_data_object("post_categories", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'type'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, 'nn' => true, 'index' => true, "comment" => "글구분"),
	'slug'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, 'nn' => true, 'index' => true, "comment" => "슬러그"),
	'title'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 200, "nn" => true, "comment" => "분류명"),
	
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"글");

function pb_post_category_statement($conditions_ = array()){
	global $post_categories_do, $users_do;

	$statement_ = $post_categories_do->statement();
	$statement_->add_field(
		"DATE_FORMAT(post_categories.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(post_categories.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(post_categories.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(post_categories.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(post_categories.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(post_categories.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	if(isset($conditions_['id'])){
		$statement_->add_in_condition("post_categories.id", $conditions_['id']);
	}
	if(isset($conditions_['type'])){
		$statement_->add_in_condition("post_categories.type", $conditions_['type']);
	}
	if(isset($conditions_['slug'])){
		$statement_->add_in_condition("post_categories.slug", $conditions_['slug']);
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$statement_->add_like_condition(pb_hook_apply_filters('pb_post_category_list_keyword', array(
			"post_categories.slug",
			"post_categories.title",
		)), $conditions_['keyword']);
	}

	return pb_hook_apply_filters('pb_post_category_statement', $statement_, $conditions_);
}

function pb_post_category_list($conditions_ = array()){
	$statement_ = pb_post_category_statement($conditions_);

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
	}

	return pb_hook_apply_filters("pb_post_category_list", $statement_->select($orderby_, $limit_));
}

function pb_post_category($id_){
	$post_ = pb_post_category_list(array("id" => $id_));
	if(!isset($post_) || count($post_) <= 0) return null;
	return $post_[0];
}

function pb_post_category_by_slug($type_, $slug_){
	$post_ = pb_post_category_list(array("type" => $type_, "slug" => $slug_));
	if(!isset($post_) || count($post_) <= 0) return null;
	return $post_[0];
}

function pb_post_category_insert($raw_data_){
	global $post_categories_do;
	$insert_id_ = $post_categories_do->insert($raw_data_);
	pb_hook_do_action("pb_post_category_inserted", $insert_id_);
	return $insert_id_;
}

function pb_post_category_update($id_, $raw_data_){
	global $post_categories_do;

	pb_hook_do_action("pb_post_category_update", $id_, $raw_data_);
	$result_ = $post_categories_do->update($id_, $raw_data_);
	pb_hook_do_action("pb_post_category_updated", $id_);

	return $result_;
}

function pb_post_category_delete($id_){
	global $post_categories_do;
	pb_hook_do_action("pb_post_category_delete", $id_);
	$post_categories_do->delete($id_);
	pb_hook_do_action("pb_post_category_deleted", $id_);
}


function pb_post_category_rewrite_slug($type_, $slug_, $excluded_category_id_ = null, $retry_count_ = 0){
	$temp_slug_ = $slug_;
	if($retry_count_ > 0){
		$temp_slug_ .= "-".$retry_count_;
	}

	$check_data_ = pb_post_category_list(array(
		'type' => $type_,
		'slug' => $temp_slug_,
	));
	if(count($check_data_) <= 0 || $check_data_[0]['id'] === $excluded_category_id_){
		return $temp_slug_;
	}

	return pb_post_category_rewrite_slug($type_, $slug_, $excluded_category_id_, ++$retry_count_);
}


function pb_post_category_write($data_){
	$type_ = isset($data_['type']) ? $data_['type'] : null;
	$title_ = isset($data_['title']) ? $data_['title'] : null;
	$reg_date_ = @strlen($data_['reg_date']) ? $data_['reg_date'] : pb_current_time();
	$slug_ = isset($data_['slug']) ? $data_['slug'] : null;
	$slug_ = strlen($slug_) ? $slug_ : $title_;
	$slug_ = pb_slugify($slug_);

	$post_types_ = pb_post_types();
	
	if(!strlen($type_) || !isset($post_types_[$type_])) return new PBError(403, "글형식이 잘못되었습니다.", "잘못된 글형식");
	if(!strlen($slug_)) return new PBError(403, "슬러그가 잘못되었습니다.", "잘못된 슬러그");

	$insert_data_ = array(
		'title' => $title_,
		'type' => $type_,
		'slug' => pb_post_category_rewrite_slug($type_, $slug_),
		'reg_date' => $reg_date_,
	);

	$inserted_id_ = pb_post_category_insert($insert_data_);
	pb_hook_do_action('pb_post_category_writed', $inserted_id_);
	return $inserted_id_;
}

function pb_post_category_edit($id_, $data_){
	$update_data_ = array(
		'mod_date' => pb_current_time(),
	);

	if(isset($data_['title'])){
		$update_data_['title'] = $data_['title'];
	}

	if(isset($data_['slug'])){
		$before_data_ = pb_post_category($id_);
		$title_ = isset($update_data_['title']) ? $update_data_['title'] : null;
		$update_data_['slug'] = pb_slugify((strlen($data_['slug']) ? $data_['slug'] : $title_));
		$update_data_['slug'] = pb_post_category_rewrite_slug($before_data_['type'], $update_data_['slug'], $id_);

		if(!strlen($update_data_['slug'])) return new PBError(403, "슬러그가 잘못되었습니다.", "잘못된 슬러그");
	}

	pb_post_category_update($id_, $update_data_);
	pb_hook_do_action('pb_post_category_edited', $id_);	
	return $id_;
}

function pb_post_category_map($type_){
	global $pb_post_category_map;
	if(!isset($pb_post_category_map)) $pb_post_category_map = array();
	if(isset($pb_post_category_map[$type_])) return $pb_post_category_map[$type_];

	$pb_post_category_map[$type_] = array();
	$post_categories_ = pb_post_category_statement(array("type" => $type_));

	foreach($post_categories_ as $category_data_){
		$pb_post_category_map[$type_][$category_data_['id']] = array(
			'slug' => $category_data_['slug'],
			'title' => $category_data_['title'],
		);
	}

	return $pb_post_category_map[$type_];
}

include(PB_DOCUMENT_PATH . 'includes/post-category/post-category-adminpage.php');

?>
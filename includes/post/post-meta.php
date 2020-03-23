<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}


global $posts_meta_do;
$posts_meta_do = pbdb_data_object("posts_meta", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'post_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'posts',
		'column' => 'id',
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), 'nn' => true, "comment" => "글ID"),

	'meta_name'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, 'index' => true, "comment" => "메타키"),
	'meta_value'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 500, "comment" => "메카값"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"글 - 메타");


function pb_post_meta_statement($conditions_ = array()){
	global $posts_meta_do;

	$statement_ = $posts_meta_do->statement();

	$statement_->add_field(
		"DATE_FORMAT(posts_meta.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis",
		"DATE_FORMAT(posts_meta.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi",
		"DATE_FORMAT(posts_meta.reg_date, '%Y.%m.%d') reg_date_ymd",
		"DATE_FORMAT(posts_meta.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis",
		"DATE_FORMAT(posts_meta.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi",
		"DATE_FORMAT(posts_meta.mod_date, '%Y.%m.%d') mod_date_ymd"
	);

	$statement_->add_legacy_field_filter('pb_post_meta_list_fields', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_post_meta_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_post_meta_list_where', '', $conditions_);

	if(isset($conditions_['id'])){
		$statement_->add_in_condition("posts_meta.id", $conditions_['id']);
	}
	if(isset($conditions_['post_id'])){
		$statement_->add_in_condition("posts_meta.post_id", $conditions_['post_id']);
	}
	if(isset($conditions_['meta_name'])){
		$statement_->add_in_condition("posts_meta.meta_name", $conditions_['meta_name']);
	}
	if(isset($conditions_['meta_value'])){
		$statement_->add_in_condition("posts_meta.meta_value", $conditions_['meta_value']);
	}

	return pb_hook_apply_filters('pb_post_meta_statement', $statement_, $conditions_);
}
function pb_post_meta_list($conditions_ = array()){
	$statement_ = pb_post_meta_statement($conditions_);

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
	}

	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	return pb_hook_apply_filters('pb_post_meta_list', $statement_->select($orderby_, $limit_));
}

function pb_post_meta_data($id_){
	$data_ = pb_post_meta_list(array("id" => $id_));
	if(count($data_) > 0) return $data_[0];
	return null;
}
function pb_post_meta_data_by($post_id_, $meta_name_){
	$data_ = pb_post_meta_list(array("post_id" => $post_id_, "meta_name" => $meta_name_));
	if(count($data_) > 0) return $data_[0];
	return null;
}

function pb_post_meta_map($post_id_, $cache_ = true){
	global $_pb_post_meta_map;

	if($cache_ && isset($_pb_post_meta_map) && isset($_pb_post_meta_map[$post_id_])){
		return $_pb_post_meta_map[$post_id_];
	}

	$temp_ = pb_post_meta_list(array(
		"post_id" => $post_id_,
		// "meta_name" => $meta_name_,
	));

	$results_ = array();

	foreach($temp_ as $row_data_){
		if(!isset($results_[$row_data_['meta_name']])) $results_[$row_data_['meta_name']] = $row_data_['meta_value'];
		else{
			if(gettype($results_[$row_data_['meta_name']]) !== "array"){
				$results_[$row_data_['meta_name']] = array($results_[$row_data_['meta_name']]);
			}

			$results_[$row_data_['meta_name']][] = $row_data_['meta_value'];
		}
		
		
	}

	$_pb_post_meta_map[$post_id_] = $results_;

	return $results_;
}

function pb_post_meta_value($post_id_, $meta_name_, $default_ = null, $cache_ = true){
	$meta_data_ = pb_post_meta_map($post_id_, $cache_);
	if(count($meta_data_) <= 0 || !isset($meta_data_[$meta_name_])) return $default_;
	return $meta_data_[$meta_name_];
}

function pb_post_meta_update($post_id_, $meta_name_, $meta_value_, $unique_ = true){
	global $pbdb;

	$meta_data_ = pb_post_meta_data_by($post_id_, $meta_name_);

	if(!$unique_ || !isset($meta_data_)){
		return $pbdb->insert("posts_meta", array(
			'post_id' => $post_id_,
			'meta_name' => $meta_name_,
			'meta_value' => $meta_value_,
			'reg_date' => pb_current_time(),
		));
	}else{
		$pbdb->update("posts_meta", array(
			'meta_value' => $meta_value_,
			'mod_date' => pb_current_time(),
		), array("id" => $meta_data_['id']));

		return $meta_data_['id'];
	}
}

include(PB_DOCUMENT_PATH . 'includes/post/post-meta-builtin.php');

?>
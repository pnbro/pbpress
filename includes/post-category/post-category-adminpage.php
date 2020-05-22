<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_post_category_register_adminpage($results_){

	$post_types_ = pb_post_types();

	foreach($post_types_ as $key_ => $type_data_){
		if($type_data_['use_category']){
			$results_["{$key_}"] = array(
				'name' => "{$type_data_['name']} 관리",
				'type' => 'directory',
				'sort' => isset($type_data_['adminpage_sort']) ? $type_data_['adminpage_sort'] : 99,
			);

			$results_["manage-{$key_}-categories"] = array(
				'name' => "{$type_data_['name']} 분류 관리",
				'type' => 'menu',
				'directory' => $key_,
				'post_type' => $key_,
				'rewrite_handler' => '_pb_post_category_rewrite_handler_for_adminpage',
				'authority_task' => "manage_{$key_}",
				'subpath' => null,
				'sort' => isset($type_data_['adminpage_sort']) ? $type_data_['adminpage_sort'] : 10,
			);
		}	
	}

	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', '_pb_post_category_register_adminpage');

include(PB_DOCUMENT_PATH . "includes/post-category/views/tables.php");


function _pb_post_category_rewrite_handler_for_adminpage($rewrite_path_){

	$adminpage_data_ = pb_current_adminpage();
	$post_type_ = $adminpage_data_['post_type'];

	global $pbpost_type, $pbpost_type_data;

	$post_types_ = pb_post_types();

	$pbpost_type = $post_type_;
	$pbpost_type_data = isset($post_types_[$post_type_]) ? $post_types_[$post_type_] : null;

	if(!isset($pbpost_type_data)){
		return new PBError(503, "잘못된 접근", "존재하지 않는 글형식입니다.");
	}
	
	if(count($rewrite_path_) < 2){
		return PB_DOCUMENT_PATH."includes/post-category/views/list.php";
	}

	return new PBError(503, "잘못된 접근", "요청정보가 잘못됬습니다.");
}

pb_add_ajax('pb-admin-post-category-load', function(){
	$ctg_id_ = _POST("key");
	$target_data_ = pb_post_category($ctg_id_);

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_".$target_data_['type'])){
		pb_ajax_error("권한없음", "접근권한이 없습니다.");
	}
	
	pb_ajax_success(array(
		"results" => $target_data_,
	));
});

pb_add_ajax('pb-admin-post-category-insert', function(){
	$target_data_ = _POST("target_data");
	
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_".$target_data_['type'])){
		pb_ajax_error("권한없음", "접근권한이 없습니다.");
	}

	$target_data_['reg_date'] = pb_current_time();
	$inserted_id_ = pb_post_category_write($target_data_);
	
	pb_ajax_success(array(
		'key' => $inserted_id_,
		'added_data' => pb_post_category($inserted_id_),
	));
});

pb_add_ajax('pb-admin-post-category-update', function(){
	$ctg_id_ = _POST("key");
	$target_data_ = _POST("target_data");

	$before_data_ = pb_post_category($ctg_id_);

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_".$before_data_['type'])){
		pb_ajax_error("권한없음", "접근권한이 없습니다.");
	}

	pb_post_category_edit($ctg_id_, $target_data_);
	pb_ajax_success(array(
		'key' => $ctg_id_,
	));
});

pb_add_ajax('pb-admin-post-category-delete', function(){
	$ctg_id_ = _POST("key");

	$before_data_ = pb_post_category($ctg_id_);

	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_".$before_data_['type'])){
		pb_ajax_error("권한없음", "접근권한이 없습니다.");
	}

	pb_post_category_delete($ctg_id_);
	pb_ajax_success();
});

?>
<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

global $menus_item_do;
$menus_item_do = pbdb_data_object("menus_item", array(
	'id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "ai" => true, "pk" => true, "comment" => "ID"),
	'parent_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'menus_item',
		'column' => 'id',
		'delete' => PBDB_DO::FK_SETNULL,
		'update' => PBDB_DO::FK_SETNULL,
	), "comment" => "상위ID"),
	'menu_id'		 => array("type" => PBDB_DO::TYPE_BIGINT, "length" => 11, "fk" => array(
		'table' => 'menus',
		'column' => 'id',
		'delete' => PBDB_DO::FK_CASCADE,
		'update' => PBDB_DO::FK_CASCADE,
	), 'nn' => true, "comment" => "메뉴ID"),
		
	'category'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "comment" => "제목"),
	'title'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 100, "comment" => "제목"),
	'sort_char'		 => array("type" => PBDB_DO::TYPE_INT, "length" => 3, "comment" => "정렬순서"),
	'reg_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "등록일자"),
	'mod_date'	 => array("type" => PBDB_DO::TYPE_DATETIME, "comment" => "수정일자"),
),"메뉴 - 아이템");
$menus_item_do->add_legacy_field_filter('pb_menu_item_parse_fields', array());

function pb_menu_item_statement($conditions_ = array()){
	global $menus_do, $menus_item_do;

	$statement_ = $menus_item_do->statement();
	

	$statement_->add_legacy_field_filter('pb_menu_item_list_fields', '', $conditions_);
	$statement_->add_legacy_join_filter('pb_menu_item_list_join', '', $conditions_);
	$statement_->add_legacy_where_filter('pb_menu_item_list_where', '', $conditions_);

	$statement_->add_join_statement("LEFT OUTER JOIN", $menus_do->statement(), "menus", array(
		array(PBDB_SS::COND_COMPARE, "menus.id", "menus_item.menu_id", "=")
	), array(
		'slug',
	));

	if(isset($conditions_['id'])){
		$statement_->add_compare_condition("menus_item.id", $conditions_['id'], "=", PBDB::TYPE_NUMBER);
	}

	if(isset($conditions_['parent_id'])){
		$statement_->add_compare_condition("menus_item.parent_id", $conditions_['parent_id'], "=", PBDB::TYPE_NUMBER);
	}
	if(isset($conditions_['root_only']) && $conditions_['root_only'] == true){
		$statement_->add_is_null_condition("menus_item.parent_id");
	}
		
	if(isset($conditions_['category'])){
		$statement_->add_in_condition("menus_item.category", $conditions_['category']);
	}
	if(isset($conditions_['menu_id'])){
		$statement_->add_in_condition("menus_item.menu_id", $conditions_['menu_id']);
	}
	if(isset($conditions_['menu_slug'])){
		$statement_->add_in_condition("menus.slug", $conditions_['menu_slug']);
	}

	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$statement_->add_like_condition(pb_hook_apply_filters('pb_menu_item_list_keyword', array(
			"menus_item.title",
		)), $conditions_['keyword']);
	}

	return pb_hook_apply_filters('pb_menu_item_statement', $statement_);
}
function pb_menu_item_list($conditions_ = array()){
	$statement_ = pb_menu_item_statement($conditions_);
	$orderby_ = isset($conditions_['orderby']) ? $conditions_['orderby'] : null;
	$limit_ = isset($conditions_['limit']) ? $conditions_['limit'] : null;

	if(isset($conditions_['justcount']) && $conditions_['justcount'] === true){
		return $statement_->count();
	}

	return pb_hook_apply_filters("pb_menu_item_list", $statement_->select($orderby_, $limit_));
}


function pb_menu_item($id_){
	$menu_item_ = pb_menu_item_list(array("id" => $id_));
	if(!isset($menu_item_) || count($menu_item_) <= 0) return null;
	return $menu_item_[0];
}

function pb_menu_item_insert($raw_data_){
	global $menus_item_do, $pbdb;

	$insert_id_ = $menus_item_do->insert($raw_data_);
	global $pbdb;
	pb_hook_do_action("pb_menu_item_inserted", $insert_id_);

	return $insert_id_;
}

function pb_menu_item_update($id_, $raw_data_){
	global $menus_item_do, $pbdb;
	$result_ = $menus_item_do->update($id_, $raw_data_);

	pb_hook_do_action("pb_menu_item_updated", $id_);

	return $result_;
}

function pb_menu_item_delete($id_){
	global $menus_item_do;
	pb_hook_do_action("pb_menu_item_delete", $id_);
	$result_ = $menus_item_do->delete($id_);
	pb_hook_do_action("pb_menu_item_deleted", $id_);
	return $result_;
}

?>
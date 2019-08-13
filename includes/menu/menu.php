<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_menu_list($conditions_ = array()){
	global $pbdb;

	$query_ = "SELECT 

					 menus.id id
						
					,menus.title title
					,menus.slug slug
					
					,menus.reg_date reg_date
					,DATE_FORMAT(menus.reg_date, '%Y.%m.%d') reg_date_ymd
					,DATE_FORMAT(menus.reg_date, '%Y.%m.%d %H:%i') reg_date_ymdhi
					,DATE_FORMAT(menus.reg_date, '%Y.%m.%d %H:%i:%S') reg_date_ymdhis
					
					,menus.mod_date mod_date
					,DATE_FORMAT(menus.mod_date, '%Y.%m.%d') mod_date_ymd
					,DATE_FORMAT(menus.mod_date, '%Y.%m.%d %H:%i') mod_date_ymdhi
					,DATE_FORMAT(menus.mod_date, '%Y.%m.%d %H:%i:%S') mod_date_ymdhis

					 ".pb_hook_apply_filters('pb_menu_list_fields', "", $conditions_)." 
	FROM menus

	".pb_hook_apply_filters('pb_menu_list_join', "", $conditions_)." 

	WHERE 1 

	 ".pb_hook_apply_filters('pb_menu_list_where', "", $conditions_)."  

	";

	if(isset($conditions_['id'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['id'], "menus.id")." ";
	}
	if(isset($conditions_['slug'])){
		$query_ .= " AND ".pb_query_in_fields($conditions_['slug'], "menus.slug")." ";
	}
	if(isset($conditions_['keyword']) && strlen($conditions_['keyword'])){
		$query_ .= " AND ".pb_query_keyword_search(pb_hook_apply_filters('pb_menu_list_keyword', array(
			"menus.title",
			"menus.slug",
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

	return pb_hook_apply_filters("pb_menu_list", $pbdb->select($query_));
}

function pb_menu($id_){
	$menu_ = pb_menu_list(array("id" => $id_));
	if(!isset($menu_) || count($menu_) <= 0) return null;
	return $menu_[0];
}

function pb_menu_by_slug($slug_){
	$menu_ = pb_menu_list(array("slug" => $slug_));
	if(!isset($menu_) || count($menu_) <= 0) return null;
	return $menu_[0];
}

function _pb_menu_parse_fields($data_){
	return pb_format_mapping(pb_hook_apply_filters("pb_menu_parse_fields",array(

		'title' => '%s',
		'slug' => '%s',

		'reg_date' => '%s',
		'mod_date' => '%s',
		
	)), $data_);
}


function pb_menu_insert($raw_data_){
	global $pbdb;

	$raw_data_ = _pb_menu_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$insert_id_ = $pbdb->insert("menus", $data_, $format_);
	pb_hook_do_action("pb_menu_inserted", pb_menu($insert_id_));
	return $insert_id_;
}

function pb_menu_update($id_, $raw_data_){
	global $pbdb;

	$raw_data_ = _pb_menu_parse_fields($raw_data_);
	$data_ = $raw_data_['data'];
	$format_ = $raw_data_['format'];

	$result_ = $pbdb->update("menus", $data_, array("id" => $id_), $format_, array("%d"));
	pb_hook_do_action("pb_menu_updated", pb_menu($id_));

	return $result_;
}

function pb_menu_delete($id_){
	global $pbdb;

	$result_ = $pbdb->delete("menus", array("id" => $id_), array("%d"));
	pb_hook_do_action("pb_menu_deleted", pb_menu($id_));
	return $result_;
}


function pb_menu_delete_rewrite_slug($slug_, $retry_count_ = 0, $excluded_menu_id_ = null){
	$temp_slug_ = $slug_;
	if($retry_count_ > 0){
		$temp_slug_ .= "_".$retry_count_;
	}

	$check_data_ = pb_menu_by_slug($slug_);

	if(!isset($check_data_)){
		return $temp_slug_;
	}

	if(strlen($excluded_menu_id_) && $excluded_menu_id_ === $check_data_['id']){
		return $temp_slug_;	
	}


	return pb_menu_delete_rewrite_slug($slug_, ++$retry_count_, $excluded_menu_id_);
}


function pb_menu_categories(){
	global $_pb_menu_categories;

	if(!isset($_pb_menu_categories)){
		$_pb_menu_categories = pb_hook_apply_filters('pb_menu_categories', array(
			'common' => '기본',
			'page' => '페이지',
			'ext-link' => array(
				'title' => '외부링크',
				'render' => "_pb_menu_category_list_redner_for_ext_link",
				'edit_categories' => array("ext-link"),
			),
		));
	}
	return $_pb_menu_categories;
}

function _pb_menu_category_list_default_render($category_, $menu_target_list_){
	?>
	<ul class="pb-menu-target-list" data-menu-target-list>
	<?php
	foreach($menu_target_list_ as $menu_target_data_){ ?>
		<li>
			<div class="checkbox">
				<label><input type="checkbox" value="<?=$menu_target_data_['slug']?>" data-menu-target-item="<?=$menu_target_data_['slug']?>" data-menu-target-item-title="<?=$menu_target_data_['title']?>"> <?=$menu_target_data_['title']?></label>
			</div>
		</a>

		</li>
	<?php }

		if(count($menu_target_list_) <= 0){ ?>
			<li class="help-block text-center no-row">추가할 메뉴항목이 없습니다.</li>
		<?php  }

	?>

	</ul>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		window.pb_menu_editor.register_add_handler("<?=$category_?>", function(target_form_el_){

			var menu_target_items_ = target_form_el_.find(":input[data-menu-target-item]")
			var add_list_ = [];
			menu_target_items_.each(function(){
				var target_el_ = $(this);
				if(target_el_.prop("checked")){
					add_list_.push({
						item_data : {
							id : null,
							category : "<?=$category_?>",
							slug : target_el_.attr("data-menu-target-item"),
							title : target_el_.attr("data-menu-target-item-title"),
						},
						item_meta_data : {}
					});
				}
			});
			

			if(add_list_.length <= 0){
				PB.alert({
					title : "선택확인",
					content : "메뉴에 추가할 항목을 선택하세요",
				});
				return false;
			}

			menu_target_items_.prop("checked", false);

			return add_list_;
		});
	});
	</script>
	<?php


	
}
function _pb_menu_category_list_redner_for_ext_link($category_, $menu_target_list_){
	?>

		<div class="form-group">
			<label>메뉴항목명</label>
			<input type="text" name="title" placeholder="메뉴항목명 입력" required data-error="메뉴항목명을 입력하세요" class="form-control">
			<div class="help-block with-errors"></div>
			<div class="clearfix"></div>
		</div>
		<div class="form-group">
			<label>외부링크주소</label>
			<input type="text" name="ext_link_url" placeholder="URL 입력" required data-error="URL을 입력하세요" class="form-control">
			<div class="help-block with-errors"></div>
			<div class="clearfix"></div>
		</div>

		<div class="form-group">
			<label>새창으로 열기</label>
			<div>
				<label class="radio-inline"><input type="radio" name="open_new_window" value="Y"> 예</label>
				<label class="radio-inline"><input type="radio" name="open_new_window" value="N"> 아니오</label>
			</div>
			<div class="help-block with-errors"></div>
			<div class="clearfix"></div>
		</div>
	
<script type="text/javascript">
jQuery(document).ready(function(){


	window.pb_menu_editor.register_add_handler("<?=$category_?>", function(target_form_el_){
		var ext_data_ = target_form_el_.serialize_object();
			ext_data_['category'] = "<?=$category_?>";
		return [{
			item_data : {
				id : null,
				category : "<?=$category_?>",
				slug : null,
				title : ext_data_['title'],
			},
			item_meta_data : {
				ext_link_url : ext_data_['ext_link_url'],
				open_new_window : ext_data_['open_new_window'],
			}
		}];
	});
});

</script>


	<?php
}
function pb_menu_category_add($key_, $title_){
	$pb_menu_categories_ = pb_menu_categories();
	$pb_menu_categories_[$key_] = $title_;
	global $_pb_menu_categories;
	$_pb_menu_categories = $pb_menu_categories_;
}

function pb_menu_target_list(){
	global $_pb_menu_target_list;

	if(!isset($_pb_menu_target_list)){
		$pb_menu_categories_ = pb_menu_categories();

		$_pb_menu_target_list = array();
		foreach($pb_menu_categories_ as $key_ => $title_){
			$_pb_menu_target_list[$key_] = array();
		}

		$rewrite_list_ = pb_rewrite_list();
		$page_list_ = pb_page_list();

		foreach($rewrite_list_ as $rewrite_key_ => $rewrite_data_){
			if(!isset($rewrite_data_['public']) || !$rewrite_data_['public']){
				continue;
			}

			$_pb_menu_target_list['common'][] = array(
				'slug' => $rewrite_key_,
				'title' => isset($rewrite_data_['title']) ? $rewrite_data_['title'] : $rewrite_key_,
			);
		}

		foreach($page_list_ as $page_data_){
			if($page_data_['status'] !== PB_PAGE_STATUS_PUBLISHED){
				continue;
			} 

			$_pb_menu_target_list['page'][] = array(
				'slug' => $page_data_['slug'],
				'title' => $page_data_['page_title'],
			);
		}

		$_pb_menu_target_list = pb_hook_apply_filters('pb_menu_target_list', $_pb_menu_target_list);
	}

	return $_pb_menu_target_list;
}


function pb_menu_category_edit_forms(){
	global $_pb_menu_category_edit_forms;

	if(!isset($_pb_menu_category_edit_forms)){
		$_pb_menu_category_edit_forms = pb_hook_apply_filters('pb_menu_category_edit_forms', array());
	}
	return $_pb_menu_category_edit_forms;
}
function pb_menu_category_edit_form_add($key_, $func_){
	$pb_menu_category_edit_forms_ = pb_menu_category_edit_forms();
	$pb_menu_category_edit_forms_[$key_] = $func_;
	global $_pb_menu_category_edit_forms;
	$_pb_menu_category_edit_forms = $pb_menu_category_edit_forms_;
}

function _pb_menu_category_edit_form_add_ext_link($data_, $meta_data_){
	?>

	<h3>외부링크</h3>
	<table class="table pb-form-table">
		<tbody>
			<tr>
				<th>외부링크주소</th>
				<td>
					<div class="form-group">
						<input type="text" name="ext_link_url" value="<?=isset($meta_data_['ext_link_url']) ? $meta_data_['ext_link_url'] : ""?>" required data-error="URL을 입력하세요" class="form-control" placeholder="URL 입력">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>

		</tbody>
	</table>
	
	<?php
}
pb_menu_category_edit_form_add("ext-link", '_pb_menu_category_edit_form_add_ext_link');

function _pb_menu_tree_recv_children($parent_id_, $menu_list_, $cache_ = true){
	$results_ = array();

	foreach($menu_list_ as $menu_item_data_){
		if($menu_item_data_['parent_id'] !== $parent_id_) continue;

		$row_data_ = array(
			'item_data' => $menu_item_data_,
			'item_meta_data' => pb_menu_item_meta_map($menu_item_data_['id'], $cache_),
			'children' => _pb_menu_tree_recv_children($menu_item_data_['id'], $menu_list_, $cache_),
		);

		$results_[] = $row_data_;
	}

	return $results_;
}

function pb_menu_tree($menu_data_, $cache_ = true){
	global $_pb_menu_tree;

	if($cache_ && isset($_pb_menu_tree[$menu_data_['id']])){
		return $_pb_menu_tree[$menu_data_['id']];
	}

	$results_ = array();

	$temp_menu_list_ = pb_menu_item_list(array(
		'menu_id' => $menu_data_['id'],
		'orderby' => "ORDER BY sort_char asc",
	));

	foreach($temp_menu_list_ as $menu_item_data_){
		if(strlen($menu_item_data_['parent_id'])) continue;

		$row_data_ = array(
			'item_data' => $menu_item_data_,
			'item_meta_data' => pb_menu_item_meta_map($menu_item_data_['id'], $cache_),
			'children' => _pb_menu_tree_recv_children($menu_item_data_['id'], $temp_menu_list_, $cache_),
		);

		$results_[] = $row_data_;
	}

	$_pb_menu_tree[$menu_data_['id']] = $results_;

	return $_pb_menu_tree[$menu_data_['id']];
}
function pb_menu_tree_by_id($menu_id_, $cache_ = true){
	$menu_data_ = pb_menu($menu_id_);
	return pb_menu_tree($menu_data_);
}
function pb_menu_tree_by_slug($slug_, $cache_ = true){
	$menu_data_ = pb_menu_by_slug($slug_);
	return pb_menu_tree($menu_data_);
}

include(PB_DOCUMENT_PATH . 'includes/menu/menu-item.php');
include(PB_DOCUMENT_PATH . 'includes/menu/menu-item-meta.php');
include(PB_DOCUMENT_PATH . 'includes/menu/menu-builtin.php');
include(PB_DOCUMENT_PATH . 'includes/menu/menu-adminpage.php');

?>
<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_menu_register_adminpage($results_){

	$results_['manage-menu'] = array(
		'name' => '메뉴관리',
		'type' => 'menu',
		'directory' => 'common',
		'page' => PB_DOCUMENT_PATH."includes/menu/views/edit.php",
		'authority_task' => 'manage_menu',
		'subpath' => null,
		'sort' => 7,
	);
	return $results_;
}
pb_hook_add_filter('pb_adminpage_list', '_pb_menu_register_adminpage');

function pb_menu_register_authority_task_types($results_){
	$results_['manage_menu'] = array(
		'name' => '메뉴관리'
	);

	return $results_;
}
pb_hook_add_filter('pb_authority_task_types', "pb_menu_register_authority_task_types");

function _pb_menu_installed_tables(){
	$check_ = pb_authority_task_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR, "manage_menu");
	if(isset($check_)) return;

	$auth_data_ = pb_authority_by_slug(PB_AUTHORITY_SLUG_ADMINISTRATOR);

	pb_authority_task_add(array(
		'auth_id' => $auth_data_['id'],
		'slug' => "manage_menu",
		'reg_date' => pb_current_time(),
	));

}
pb_hook_add_action('pb_installed_tables', "_pb_menu_installed_tables");

function _pb_ajax_menu_editor_load_edit_form(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_menu")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$item_data_ = isset($_POST['item_data']) ? $_POST['item_data'] : null;
	$category_ = isset($item_data_['category']) ? $item_data_['category'] : null;

	$item_meta_data_ = isset($_POST['item_meta_data']) ? $_POST['item_meta_data'] : null;

	$menu_categories_ = pb_menu_categories();
	$menu_categories_ = isset($menu_categories_[$category_]) ? $menu_categories_[$category_] : $menu_categories_['common'];
	$edit_categories_ = gettype($menu_categories_) === "string" ? array() : $menu_categories_['edit_categories'];

	$menu_category_edit_form_ = pb_menu_category_edit_forms();

	$open_new_window_ = isset($item_meta_data_['open_new_window']) ? $item_meta_data_['open_new_window'] : null; 

	ob_start();
?>

<h3>기본정보</h3>
<input type="hidden" name="category" value="<?=$category_?>">
<input type="hidden" name="id" value="<?=isset($item_data_['id']) ? $item_data_['id'] : null?>">
<table class="table pb-form-table">
	<tbody>
		<tr>
			<th>메뉴항목명</th>
			<td>
				<div class="form-group">
					<input type="text" name="title" value="<?=isset($item_data_['title']) ? $item_data_['title'] : ""?>" required data-error="메뉴항목명을 입력하세요" class="form-control" placeholder="메뉴명입력">
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>
			</td>
		</tr>

	</tbody>
</table>

<?php

	$form_html_ = ob_get_clean();

	ob_start();

	?>
<h3>추가정보</h3>
<table class="table pb-form-table"><tbody>
	<tr>
		<th>ID</th>
		<td>
			<div class="form-group">
				<input type="text" name="item_id" value="<?=isset($item_meta_data_['item_id']) ? $item_meta_data_['item_id'] : null?>" class="form-control">
				<div class="help-block with-errors"></div>
				<div class="clearfix"></div>
			</div>
		</td>
	</tr>
	<tr>
		<th>클래스</th>
		<td>
			<div class="form-group">
				<input type="text" name="item_class" value="<?=isset($item_meta_data_['item_class']) ? $item_meta_data_['item_class'] : null?>" class="form-control">
				<div class="help-block with-errors"></div>
				<div class="clearfix"></div>
			</div>
		</td>
	</tr>
	<tr>
		<th>새창으로 열기</th>
		<td>
			<div>
				<label class="radio-inline"><input type="radio" name="open_new_window" value="Y" <?=pb_checked($open_new_window_, "Y")?>> 예</label>
				<label class="radio-inline"><input type="radio" name="open_new_window" value="N" <?=pb_checked($open_new_window_, "N")?>> 아니오</label>
			</div>

		</td>
	</tr>
</tbody></table>

	<?php

	foreach($edit_categories_ as $edit_category_){
		call_user_func_array($menu_category_edit_form_[$edit_category_], array($item_data_, $item_meta_data_));
	}

	$meta_form_html_ = ob_get_clean();

	echo json_encode(array(
		'success' => true,
		'form_html' => $form_html_,
		'meta_form_html' => $meta_form_html_,
	));
	pb_end();
}
pb_add_ajax('menu-editor-load-edit-item-form', '_pb_ajax_menu_editor_load_edit_form');

function _pb_ajax_menu_editor_update_recv_menu_tree($menu_id_, $parent_id_, $data_, $sort_char_){
	$item_id_ = isset($data_['item_data']['id']) ? $data_['item_data']['id'] : null;
	$item_data_ = $data_['item_data'];
	$children_ = isset($data_['children']) ? $data_['children'] : array();

	if(strlen($item_id_)){ //update
		pb_menu_item_update($item_id_, array(
			'menu_id' => $menu_id_,
			'parent_id' => $parent_id_,
			'sort_char' => $sort_char_,
			'category' => $item_data_['category'],
			'title' => $item_data_['title'],
			'mod_date' => pb_current_time(),
		));

	}else{ //insert
		$item_id_ = pb_menu_item_insert(array(
			'menu_id' => $menu_id_,
			'parent_id' => $parent_id_,
			'sort_char' => $sort_char_,
			'category' => $item_data_['category'],
			'title' => $item_data_['title'],
			'reg_date' => pb_current_time(),
		));
	}

	if(isset($data_['item_meta_data'])){
		foreach($data_['item_meta_data'] as $meta_key_ => $meta_value_){
			pb_menu_item_meta_update($item_id_, $meta_key_, $meta_value_);
		}
	}

	$c_sort_char_ = 0;
	foreach($children_ as $child_data_){
		_pb_ajax_menu_editor_update_recv_menu_tree($menu_id_, $item_id_, $child_data_, ++$c_sort_char_);
	}
}

function _pb_ajax_menu_editor_do_update(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_menu")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$menu_data_ = isset($_POST['menu_data']) ? $_POST['menu_data'] : null;
	$target_menu_ids_ = isset($_POST['target_menu_ids']) ? $_POST['target_menu_ids'] : array();


	$menu_id_ = isset($menu_data_['menu_id']) ? $menu_data_['menu_id'] : null;
	$menu_title_ = isset($menu_data_['menu_title']) ? $menu_data_['menu_title'] : null;
	$menu_slug_ = isset($menu_data_['menu_slug']) ? $menu_data_['menu_slug'] : null;
	$menu_list_ = isset($menu_data_['children']) ? $menu_data_['children'] : array();
	$menu_slug_ = pb_slugify((strlen($menu_slug_) ? $menu_slug_ : $menu_title_));

	if(strlen($menu_id_)){ //update
		$check_data_ = pb_menu($menu_id_);
		if(!isset($check_data_)){
			echo json_encode(array(
				"success" => false,
				"error_title" => "잘못된 요청",
				"error_message" => "메뉴정보가 존재하지 않습니다.",
			));
			pb_end();
		}
		pb_menu_update($menu_id_, array(
			'title' => $menu_title_,
			'slug' => pb_menu_delete_rewrite_slug($menu_slug_, 0, $menu_id_),
			'mod_date' => pb_current_time(),
		));

		$delete_item_ids_ = array();
		$before_menu_list_ = pb_menu_item_list(array(
			'menu_id' => $menu_id_,
		));		

		foreach($before_menu_list_ as $before_data_){
			$before_id_ = (string)$before_data_['id'];
			if(in_array($before_id_, $target_menu_ids_) === false){
				$delete_item_ids_[] = $before_id_;
			}
		}

		foreach($delete_item_ids_ as $menu_item_id_){
			pb_menu_item_delete($menu_item_id_);
		}

	}else{ //insert
		$menu_id_ = pb_menu_insert(array(
			'title' => $menu_title_,
			'slug' => pb_menu_delete_rewrite_slug($menu_slug_, 0),
			'reg_date' => pb_current_time(),
		));
	}

	$sort_char_ = 0;
	foreach($menu_list_ as $item_data_){
		_pb_ajax_menu_editor_update_recv_menu_tree($menu_id_, null, $item_data_, ++$sort_char_);
	}

	echo json_encode(array(
		'success' => true,
		'menu_id' => $menu_id_,
	));
	pb_end();
}
pb_add_ajax('menu-editor-do-update', "_pb_ajax_menu_editor_do_update");


function _pb_ajax_menu_editor_load_menu(){
	if(!pb_user_has_authority_task(pb_current_user_id(), "manage_menu")){
		echo json_encode(array(
			"success" => false,
			"error_title" => "권한없음",
			"error_message" => "접근권한이 없습니다.",
		));
		pb_end();
	}

	$menu_id_ = isset($_POST['menu_id']) ? $_POST['menu_id'] : null;
	$check_data_ = pb_menu($menu_id_);
	if(!isset($check_data_)){
		echo json_encode(array(
			"success" => false,
			"error_title" => "잘못된 요청",
			"error_message" => "메뉴정보가 존재하지 않습니다.",
		));
		pb_end();
	}

	$menu_data_ = pb_menu($menu_id_);
	$menu_tree_ = pb_menu_tree($menu_data_);

	echo json_encode(array(
		'success' => true,
		'menu_data' => $menu_data_,
		'menu_tree' => $menu_tree_,
	));
	pb_end();
}
pb_add_ajax('menu-editor-load-menu', "_pb_ajax_menu_editor_load_menu");

?>
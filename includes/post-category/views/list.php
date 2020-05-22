<?php 	

	global $pbpost_type, $pbpost_type_data;

	$post_category_table_ = pb_easytable("pb-admin-post-category-table");

	$page_index_ = _GET('page_index', 0, PB_PARAM_INT);
	$keyword_ = _GET('keyword');

?>

<h3><?=$pbpost_type_data['name']?> 분류 관리</h3>
<form method="GET" class="pb-easytable-group" id="pb-admin-post-category-table-form">

	<div class="pb-easytable-conditions">
		<div class="left-frame">
			<?php pb_hook_do_action('pb_admin_post_category_list_conditions_group_left_before', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_category_{$pbpost_type}_list_conditions_group_left_before", $pbpost_type, $pbpost_type_data); ?>
			<a href="javascript:pb_manage_post_category_add();" class="btn btn-default">분류 추가</a>
			<?php pb_hook_do_action('pb_admin_post_category_list_conditions_group_left_after', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_category_{$pbpost_type}_list_conditions_group_left_after", $pbpost_type, $pbpost_type_data); ?>
		</div>
		<div class="right-frame">
			<?php pb_hook_do_action('pb_admin_post_category_list_conditions_group_right_before', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_category_{$pbpost_type}_list_conditions_group_right_before", $pbpost_type, $pbpost_type_data); ?>
			<div class="input-group">
				<input type="text" class="form-control" placeholder="통합검색" name="keyword" value="<?=$keyword_?>">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default" type="button">검색하기</button>
				</span>
			</div>
			<?php pb_hook_do_action('pb_admin_post_category_list_conditions_group_right_after', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_category_{$pbpost_type}_list_conditions_group_right_after", $pbpost_type, $pbpost_type_data); ?>

		</div>
	</div>

	<?php 
		$post_category_table_->display($page_index_);
	?>
		
</form>
<form id="pb-post-category-edit-form">
	<table class="table pb-form-table">
		<tbody>
			<tr>
				<th>분류명</th>
				<td>
					<div class="form-group">
						<input type="text" name="title" class="form-control" placeholder="분류명 입력" required data-error="분류명을 입력하세요">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr>
				<th>슬러그</th>
				<td>
					<div class="form-group">
						<input type="text" name="slug" class="form-control" placeholder="슬러그 입력">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	
</form>

<script type="text/javascript">
window._pbpost_type = "<?=$pbpost_type?>";
window._pbpost_type_data = <?=json_encode($pbpost_type_data)?>;
</script>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-post-category/list.js"></script>
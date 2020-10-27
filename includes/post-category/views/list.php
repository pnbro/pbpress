<?php 	

	global $pbpost_type, $pbpost_type_data;

	$post_category_table_ = pb_easytable("pb-admin-post-category-table");

	$page_index_ = _GET('page_index', 0, PB_PARAM_INT);
	$keyword_ = _GET('keyword');


?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-post-category/list.css">
<h3><?=sprintf(__('%s 분류 관리'), $pbpost_type_data['name'])?></h3>
<form method="GET" class="pb-easytable-group" id="pb-admin-post-category-table-form">

	<div class="pb-easytable-conditions">
		<div class="left-frame">
			<?php pb_hook_do_action('pb_admin_post_category_list_conditions_group_left_before', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_category_{$pbpost_type}_list_conditions_group_left_before", $pbpost_type, $pbpost_type_data); ?>
			<a href="javascript:pb_manage_post_category_add();" class="btn btn-default"><?=__('분류 추가')?></a>
			<?php pb_hook_do_action('pb_admin_post_category_list_conditions_group_left_after', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_category_{$pbpost_type}_list_conditions_group_left_after", $pbpost_type, $pbpost_type_data); ?>
		</div>
		<div class="right-frame">
			<?php pb_hook_do_action('pb_admin_post_category_list_conditions_group_right_before', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_category_{$pbpost_type}_list_conditions_group_right_before", $pbpost_type, $pbpost_type_data); ?>
			<div class="input-group">
				<input type="text" class="form-control" placeholder="<?=__('통합검색')?>" name="keyword" value="<?=$keyword_?>">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default" type="button"><?=__('검색하기')?></button>
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
				<th><?=__('분류명')?></th>
				<td>
					<div class="form-group">
						<input type="text" name="title" class="form-control" placeholder="<?=__('분류명 입력')?>" required data-error="<?=__('분류명을 입력하세요')?>">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr>
				<th><?=__('슬러그')?></th>
				<td>
					<div class="form-group">
						<input type="text" name="slug" class="form-control" placeholder="<?=__('슬러그 입력')?>">
						<div class="help-block with-errors"></div>
						<div class="clearfix"></div>
					</div>
				</td>
			</tr>
			<tr>
				<th><?=__('상위분류')?></th>
				<td>
					<div class="form-group">
						<select class="form-control" name="parent_id">
							<option value=""><?=__('-상위없음-')?></option>
						<?php 

						$post_category_tree_ = pb_post_category_tree(array(
							'type' => $pbpost_type,
						));


						foreach($post_category_tree_ as $category_data_){
							?><option value="<?=$category_data_['id']?>">
								<?php for($pad_index_=0;$pad_index_<$category_data_['level']-1;++$pad_index_){ ?>&nbsp;&nbsp;<?php } ?>
								<?=$category_data_['title']?>
							</option><?php
						} ?>
						</select>
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
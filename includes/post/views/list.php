<?php 	

	global $pbpost_type, $pbpost_type_data;

	$post_table_ = pb_easytable("pb-admin-post-table");

	$post_table_->update_option_value('no_rowdata', $pbpost_type_data['label']['no_results']);

	$post_index_ = isset($_GET['post_index']) ? $_GET['post_index'] : 0;
	$keyword_ = isset($_GET['keyword']) ? $_GET['keyword'] : null;
	$status_ = isset($_GET['search_status']) ? $_GET['search_status'] : null;

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-post/list.css">
<h3><?=$pbpost_type_data['label']['list']?></h3>

<form method="GET" class="pb-easytable-group" id="pb-easytable-table-form">

	<div class="pb-easytable-conditions">
		<div class="left-frame">
			<?php pb_hook_do_action('pb_admin_post_list_conditions_group_left_before', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_{$pbpost_type}_list_conditions_group_left_before", $pbpost_type, $pbpost_type_data); ?>
			<a href="<?=pb_admin_url("manage-{$pbpost_type}/add")?>" class="btn btn-default"><?=$pbpost_type_data['label']['add']?></a>
			<?php pb_hook_do_action('pb_admin_post_list_conditions_group_left_after', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_{$pbpost_type}_list_conditions_group_left_after", $pbpost_type, $pbpost_type_data); ?>
			
		</div>
		<div class="right-frame">
			<?php pb_hook_do_action('pb_admin_post_list_conditions_group_right_before', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_{$pbpost_type}_list_conditions_group_right_before", $pbpost_type, $pbpost_type_data); ?>
			<div class="form-group">
				<select class="form-control" name="search_status">
					<option value="">-등록상태-</option>
					<?= pb_gcode_make_options(array("code_id" => "PST01"), $status_); ?>
				</select>
			</div>
			<div class="input-group">
				<input type="text" class="form-control" placeholder="통합검색" name="keyword" value="<?=$keyword_?>">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default" type="button">검색하기</button>
				</span>
			</div>
			<?php pb_hook_do_action('pb_admin_post_list_conditions_group_right_after', $pbpost_type, $pbpost_type_data); ?>
			<?php pb_hook_do_action("pb_admin_post_{$pbpost_type}_list_conditions_group_right_after", $pbpost_type, $pbpost_type_data); ?>
		</div>
	</div>

	<?php 
		$post_table_->display($post_index_);
	?>
		
</form>
<script type="text/javascript">
window._pbpost_type = "<?=$pbpost_type?>";
window._pbpost_type_data = <?=json_encode($pbpost_type_data)?>;
</script>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-post/list.js"></script>
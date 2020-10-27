<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_POST_STATUS_WRITING', '00001');
define('PB_POST_STATUS_PUBLISHED', '00003');
define('PB_POST_STATUS_UNPUBLISHED', '00009');

pb_gcode_initial_register('PST01', array(
	'name' => '글등록상태',
		'data' => array(
			PB_POST_STATUS_WRITING => "작성중",
			PB_POST_STATUS_PUBLISHED => "공개",
			PB_POST_STATUS_UNPUBLISHED => "비공개",
		),
));

pb_hook_add_action('pb_post_inserted', '_pb_post_update_for_post_short');
pb_hook_add_action('pb_post_updated', '_pb_post_update_for_post_short');
function _pb_post_update_for_post_short($id_){
	$post_data_ = pb_post($id_);

	$post_short_ = strip_tags($post_data_['post_html']);
	$post_short_ = pb_hook_apply_filters('pb_post_short', $post_short_, $post_data_);

	if(pb_strlen($post_short_) > PB_POST_SHORT_LENGTH){
		$post_short_ = pb_substr($post_short_,0, PB_POST_SHORT_LENGTH);
	}

	global $posts_do;

	$posts_do->update($post_data_['id'], array("post_short" => $post_short_));
}


pb_hook_add_action("pb_post_edit_form_control_panel_after", function($pbpost){
	global $pbpost_type, $pbpost_type_data;
	if(!$pbpost_type_data['use_category']) return;

	$post_categories_ = pb_post_category_tree(array("type" => $pbpost_type));	
	$post_category_values_ = pb_post_category_values($pbpost['id'], true);
?>

<div class="panel panel-default" id="pb-post-edit-form-post-categories">
	<div class="panel-heading" role="tab">
		<h4 class="panel-title">
			<a role="button" data-toggle="collapse" href="#pb-post-edit-form-post-categories-body" aria-expanded="true" aria-controls="collapseOne">분류</a>
		</h4>
	</div>
	<div id="pb-post-edit-form-post-categories-body" class="panel-collapse collapse in" role="tabpanel">
		<div class="panel-body">
			<div class="form-group category-list-group" data-post-category-frame>
				<?php foreach($post_categories_ as $category_data_){
					$level_ = $category_data_['level'];
				?>
				<div class="checkbox" style="padding-left: <?=($level_-1) * 10?>px;">
					<label><input type="checkbox" name="category_id" value="<?=$category_data_['id']?>" <?=in_array($category_data_['id'], $post_category_values_) ? "checked" : ""?>><?=$category_data_['title']?></label>
				</div>
				<?php } ?>
				<?php if(count($post_categories_) <= 0 ){ ?>
					<div class="help-block" data-no-category-label>등록된 분류가 없습니다.</div>
				<?php } ?>
			</div>
			<hr>
			<div class="form-group">
				<label>새로운 분류 추가</label>
				<p><input type="text" name="new_category_name" class="form-control" placeholder="카테고리명 입력"></p>
				<div class="text-right">
					<a href="javascript:pb_post_add_new_category();" class="btn btn-default">분류추가</a>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
});


pb_hook_add_action('pb_admin_post_list_conditions_group_right_before', function($pbpost_type, $pbpost_type_data){
	if(!$pbpost_type_data['use_category']) return;
	$post_categories_ = pb_post_category_list(array("type" => $pbpost_type));	

?>
	<div class="form-group">
		<select class="form-control" name="category_id">
			<option value="">-분류-</option>
			<?php foreach($post_categories_ as $category_data_){ ?>
				<option value="<?=$category_data_['id']?>" <?=pb_selected(_GET("category_id"), $category_data_['id'])?>><?=$category_data_['title']?></option>
			<?php } ?>			
		</select>
	</div>

<?php

});

pb_hook_add_filter('pb_admin_post_table_conditions', function($conditions_){
	global $pbpost_type, $pbpost_type_data;
	if(!$pbpost_type_data['use_category']) return $conditions_;
	$conditions_['category_id'] = _GET("category_id");
	return $conditions_;
});

pb_hook_add_filter('pb_post_statement', function($statement_, $conditions_){
	if(isset($conditions_['category_id'])){
		$statement_->add_custom_condition("
			posts.id IN (
				SELECT posts_category_values.post_id
				FROM   posts_category_values
				WHERE  ".pb_query_in_fields($conditions_['category_id'], "posts_category_values.category_id")."
			)
		");
	}
	return $statement_;
});

?>
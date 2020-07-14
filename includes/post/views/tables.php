<?php

pb_easytable_register("pb-admin-post-table", function($offset_, $per_post_){
	global $pbpost_type, $pbpost_type_data;
	
	$keyword_ = _GET('keyword', null);
	$status_ = _GET('search_status', null);

	$statement_ = pb_post_statement(pb_hook_apply_filters("pb_admin_post_table_conditions", array(
		'keyword' => $keyword_,
		'type' => $pbpost_type,
		'status' => $status_,
	)));

	$post_list_ = $statement_->select("posts.id desc", array($offset_, $per_post_));
	foreach($post_list_ as &$row_data_){
		$category_values_ = pb_post_category_values($row_data_['id']);
		$category_names_ = array();

		foreach($category_values_ as $value_data_){
			$category_names_[] = $value_data_['category_title'];
		}

		$row_data_['category_names'] = implode(",", $category_names_);
		$row_data_['category_names'] = strlen($row_data_['category_names']) ? $row_data_['category_names'] : "분류없음";
	}

	return array(
		'count' => $statement_->count(),
		'list' => $post_list_,
	);

}, function($table_){

	global $pbpost_type, $pbpost_type_data;

	$data_ = array(
		"seq" => array(
			'name' => '',
			'class' => 'col-seq text-center',
			'seq' => true,
		),
		'category_names' => array(
			'name' => '분류',
			'class' => 'col-2 text-center hidden-xs',
		),
		"post_title" => array(
			'name' => '제목',
			'class' => 'col-6 link-action',
			'render' => function($table_, $item_, $post_index_){
				$post_url_ = pb_post_url($item_['id']);
				$post_type_ = $item_['type'];

				$post_title_ = pb_hook_apply_filters('pb_manage_post_listtable_post_title', $item_['post_title']);
				$post_title_ = pb_hook_apply_filters('pb_manage_post_{$post_type_}_listtable_post_title', $post_title_);

				global $pbpost_type_data;
				
				?>
				<div class="title-frame post-title-frame">
					<?php pb_hook_do_action("pb_manage_post_listtable_post_title_before", $item_) ?>
					<?php pb_hook_do_action("pb_manage_post_{$post_type_}_listtable_post_title_before", $item_) ?>

					<a href="<?=pb_admin_url("manage-{$post_type_}/edit/".$item_['id'])?>" ><?=$post_title_?></a>

					<?php pb_hook_do_action("pb_manage_post_listtable_post_title_after", $item_) ?>
					<?php pb_hook_do_action("pb_manage_post_{$post_type_}_listtable_post_title_after", $item_) ?>
				</div>
				<div class="url-link"><a href="<?=$post_url_?>" target="_blank"><?=$post_url_?></a></div>
				<div class="subaction-frame">
					<a href="<?=pb_admin_url("manage-{$post_type_}/edit/".$item_['id'])?>">수정</a>
					<?php pb_hook_do_action("pb_manage_post_listtable_subaction", $item_) ?>
					<a href="javascript:pb_manage_post_remove('<?=$item_['id']?>');" class="text-danger">삭제</a>
				</div>

				<div class="xs-visiable-info">
					<?php if($pbpost_type_data['use_category']){ ?>
						<div class="subinfo"><span class="text"><?=$item_['category_names']?></span></div>
					<?php } ?>
					<div class="subinfo"><i class="icon material-icons">access_time</i> <span class="text"><?=$item_['reg_date_ymdhi']?></span></div>
					<div class="subinfo">
						<select class="form-control input-sm display-inline" data-post-status="<?=$item_['id']?>">
							<?= pb_gcode_make_options(array("code_id" => "PST01"), $item_['status']); ?>
						</select>
					</div>
				</div>

				<?php

			}
		),
		"status_name" => array(
			'name' => '상태',
			'class' => 'col-1 text-center hidden-xs',
			'render' => function($table_, $item_, $post_index_){
				?>

				<select class="form-control input-sm" data-post-status="<?=$item_['id']?>">
					<?= pb_gcode_make_options(array("code_id" => "PST01"), $item_['status']); ?>
				</select>

				<?php
			}
		),
		"reg_date_ymdhi" => array(
			'name' => '',
			'class' => 'col-2 text-center hidden-xs',
		),
	);

	if(!$pbpost_type_data['use_category']){
		unset($data_['category_names']);
	}

	return $data_;

}, array(
	'class' => 'pb-admin-post-table',
	"no_rowdata" => "검색된 항목이 없습니다.",
	'per_post' => 15,
));


?>
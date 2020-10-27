<?php

pb_easytable_register("pb-admin-post-category-table", function($offset_, $per_post_){
	global $pbpost_type;
	
	$keyword_ = _GET('keyword', null);

	$pb_post_category_tree_ = pb_post_category_tree(array(
		'keyword' => $keyword_,
		'type' => $pbpost_type,
	), $offset_, $per_post_);

	$statement_ = pb_post_category_statement(array(
		'keyword' => $keyword_,
		'type' => $pbpost_type,
	));

	return array(
		'count' => $statement_->count(),
		'list' => $pb_post_category_tree_,
	);

}, array(
	"seq" => array(
		'name' => '',
		'class' => 'col-seq text-center',
		'seq' => true,
	),
	"title" => array(
		'name' => '분류명',
		'class' => 'col-6 link-action',
		'render' => function($table_, $item_, $post_index_){
			$post_type_ = $item_['type'];
			$level_ = $item_['level'];

			$ctg_title_ = pb_hook_apply_filters('pb_manage_post_category_listtable_title', $item_['title']);
			$ctg_title_ = pb_hook_apply_filters('pb_manage_post_category_{$post_type_}_listtable_title', $ctg_title_);
			
			?>
			<div class="title-frame post-category-title-frame">
				<?php for($pad_index_=0;$pad_index_<$level_-1;++$pad_index_){ ?>
					<span class="level-pad"></span>
				<?php } ?>
				<a href="javascript:pb_manage_post_category_edit('<?=$item_['id']?>');" >
					<?=$ctg_title_?>
				</a>
			</div>
			<div class="subaction-frame">
				<a href="javascript:pb_manage_post_category_edit('<?=$item_['id']?>');"><?=__('수정')?></a>
				<?php pb_hook_do_action("pb_manage_post_category_listtable_subaction", $item_) ?>
				<a href="javascript:pb_manage_post_category_remove('<?=$item_['id']?>');" class="text-danger"><?=__('삭제')?></a>
			</div>

			<div class="xs-visiable-info">
				<div class="subinfo"><i class="icon material-icons">access_time</i> <span class="text"><?=$item_['reg_date_ymdhi']?></span></div>
				<div class="subinfo"><i class="icon material-icons">link</i> <span class="text"><?=$item_['slug']?></span></div>
			</div>

			<?php

		}
	),
	"slug" => array(
		'name' => '슬러그',
		'class' => 'col-4 text-center hidden-xs',
	),
	"reg_date_ymdhi" => array(
		'name' => '등록일자',
		'class' => 'col-2 text-center hidden-xs',
	),
), array(
	'class' => 'pb-admin-post-category-table',
	"no_rowdata" => "검색된 분류가 없습니다.",
	'per_page' => 15,
));


?>
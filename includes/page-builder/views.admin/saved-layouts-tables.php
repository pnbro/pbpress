<?php
if(!defined('PB_THEME_PATH')){ die('-1'); }

pb_easytable_register("admin-saved-layouts-table", function($offset_, $per_page_){
	global $pb_saved_layouts_do;
	$statement_ = $pb_saved_layouts_do->statement();
	$results_ = $statement_->select("reg_date DESC", array($offset_, $per_page_));

	return array(
		'count' => $statement_->count(),
		'list' => $results_,
	);
}, function(){
	return array(
		'title' => array(
			'name' => __('서식 제목'),
			'class' => 'col-5',
			'render' => function($table_, $item_, $page_index_){
				?>
				<div class="title-frame">
					<strong><?=htmlspecialchars($item_['title'])?></strong>
					<div class="subinfo-list small-theme">
						<span>Slug: <?=htmlspecialchars($item_['slug'])?></span>
					</div>
					<div class="subaction-frame">
						<a href="<?=pb_admin_url('manage-saved-layouts/edit/'.$item_['id'])?>"><?=__('디자인 수정')?></a> |
						<a href="javascript:pb_layout_delete(<?=$item_['id']?>);" class="text-danger"><?=__('삭제')?></a>
					</div>
				</div>
				<?php
			}
		),
		'category' => array(
			'name' => __('카테고리'),
			'class' => 'col-2 text-center',
		),
		'reg_date' => array(
			'name' => __('등록일'),
			'class' => 'col-3 text-center',
		),
	);
}, array(
	'per_page' => 20,
));

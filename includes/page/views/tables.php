<?php

pb_easytable_register("pb-admin-page-table", function($offset_, $per_page_){
	$keyword_ = isset($_GET["keyword"]) ? $_GET["keyword"] : null;
	$status_ = isset($_GET["search_status"]) && strlen($_GET["search_status"]) ? $_GET["search_status"] : null;

	$statement_ = pb_page_statement(array(
		'keyword' => $keyword_,
		'status' => $status_,
	));

	return array(
		'count' => $statement_->count(),
		'list' => $statement_->select(null, array($offset_, $per_page_)),
	);

}, array(
	"seq" => array(
		'name' => '',
		'class' => 'col-seq text-center',
		'seq' => true,
	),
	"page_title" => array(
		'name' => '페이지명',
		'class' => 'col-8 link-action',
		'render' => function($table_, $item_, $page_index_){
			$is_front_page_ = pb_front_page_id() === (string)$item_['id'];
			$page_url_ = pb_page_url($item_['id']);
			
			?>
			<div class="title-frame page-title-frame">
				<?php pb_hook_do_action("pb_manage_page_listtable_page_title_before", $item_) ?>
				<a href="<?=pb_admin_url("manage-page/edit/".$item_['id'])?>" ><?=pb_hook_apply_filters('pb_manage_page_listtable_page_title', $item_['page_title'])?></a>
				<?php pb_hook_do_action("pb_manage_page_listtable_page_title_after", $item_) ?>
				<?php if($is_front_page_){ ?>
					<small class="fontpage-text"> - 홈화면</small>
				<?php } ?>
			</div>
			<div class="url-link"><a href="<?=$page_url_?>" target="_blank"><?=$page_url_?></a></div>
			<div class="subaction-frame">
				<a href="<?=pb_admin_url("manage-page/edit/".$item_['id'])?>">수정</a>
				<?php if(!$is_front_page_){ ?>
					<a href="javascript:pb_manage_page_register_front_page('<?=$item_['id']?>');" class="">홈화면 지정</a>
				<?php }else{ ?>
					<a href="javascript:pb_manage_page_unregister_front_page();" class="">홈화면 지정해제</a>
				<?php } ?>
				<?php pb_hook_do_action("pb_manage_page_listtable_subaction", $item_) ?>
				<a href="javascript:pb_manage_page_remove('<?=$item_['id']?>');" class="text-danger">삭제</a>
			
				
			</div>

			<div class="xs-visiable-info">
				<div class="subinfo"><i class="icon material-icons">access_time</i> <span class="text"><?=$item_['reg_date_ymdhi']?></span></div>
				<div class="subinfo">
					<select class="form-control input-sm display-inline" data-page-status="<?=$item_['id']?>">
						<?= pb_gcode_make_options(array("code_id" => "PAG01"), $item_['status']); ?>
					</select>
				</div>
			</div>

			<?php

		}
	),
	"status_name" => array(
		'name' => '상태',
		'class' => 'col-1 text-center hidden-xs',
		'render' => function($table_, $item_, $page_index_){
			?>

			<select class="form-control input-sm" data-page-status="<?=$item_['id']?>">
				<?= pb_gcode_make_options(array("code_id" => "PAG01"), $item_['status']); ?>
			</select>

			<?php
		}
	),
	"reg_date_ymdhi" => array(
		'name' => '',
		'class' => 'col-2 text-center hidden-xs',
	),
), array(
	'class' => 'pb-admin-page-table',
	"no_rowdata" => "검색된 페이지가 없습니다.",
	'per_page' => 15,
));


?>
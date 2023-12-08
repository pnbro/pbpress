<?php

pb_easytable_register("pb-user-listtable", function($offset_, $per_page_){
	$keyword_ = _GET('keyword', null);
	$statement_ = pb_user_statement(array(
		"keyword" => $keyword_,
	));

	return array(
		'count' => $statement_->count(),
		'list' => $statement_->select("reg_date DESC", array($offset_, $per_page_)),
	);
}, pb_hook_apply_filters("pb_adminpage_user_listtable_data", array(
	'seq' => array(
		'name' => "",
		'class' => "col-seq text-center",
		'seq' => true,
	),

	'user_name' => array(
		'name' => __("사용자명"),
		'class' => "col-3 link-action",
		'render' => function($table_, $item_, $page_index_){
			
			?>

			<div class="title-frame ">
				<?=strlen($item_['user_name']) ? $item_['user_name'] : "<span class='no-title'>".__("(사용자명 없음)")."</span>"?>
			</div>
		
			<div class="subaction-frame">
				<a href="<?=pb_admin_url("manage-user/edit/".$item_['id']."")?>" class=""><?=__('관리')?></a>
				<?php pb_hook_do_action('adminpage-manage-user-table-subaction', $item_) ?>
			</div>


			<div class="xs-visiable-info">
				<ul class="subinfo-list small-theme">
		
		
					<li>
						<div class="subject">이메일</div>
						<div class="content"><?=$item_['user_email'] ?></div>
					</li>
					<li>
						<div class="subject">상태</div>
						<div class="content"><?=$item_['status_name'] ?></div>
					</li>
			
				</ul>
			</div>
			<div class="xs-visiable-info">
				<div class="subinfo"><i class="icon material-icons">access_time</i> <span class="text"><?=$item_['reg_date_ymdhi']?></span></div>
			</div>


			<?php

		}
	),


	'user_login' => array(
		'name' => __("ID"),
		'class' => "col-2 text-center hidden-xs",
	),
	'user_email' => array(
		'name' => __("이메일"),
		'class' => "col-2 text-center hidden-xs",
	),
	'status_name' => array(
		'name' => __("상태"),
		'class' => "col-2 text-center hidden-xs",
	
	),
	'reg_date_ymdhi' => array(
		'name' => __("등록일자"),
		'class' => "col-2 text-center hidden-xs",
	
	),

)), pb_hook_apply_filters("pb_adminpage_user_listtable_options", array(
	"no_rowdata" => __("조회된 사용자가 없습니다."),
	'per_page' => 15,
	'class' => 'table pb-easytable pb-user-listtable',
	// 'ajax' => true,
)));




?>
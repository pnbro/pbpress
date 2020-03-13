<?php

pb_easytable_register("pb-user-listtable", function($offset_, $per_page_){
	$keyword_ = isset($_GET['keyword']) ? $_GET['keyword'] : null;
	$statement_ = pb_user_statement(array(
		"keyword" => $keyword_,
	));

	return array(
		'count' => $statement_->count(),
		'list' => $statement_->select(null, array($offset_, $per_page_)),
	);
}, pb_hook_apply_filters("pb_adminpage_user_listtable_data", array(
	'seq' => array(
		'name' => "",
		'class' => "col-seq text-center",
		'seq' => true,
	),
	'user_name' => array(
		'name' => "사용자명",
		'class' => "col-2",
	),
	'user_login' => array(
		'name' => "id",
		'class' => "col-2 text-center",
	),
	'user_email' => array(
		'name' => "이메일",
		'class' => "col-2 text-center hidden-xs",
	),
	'status' => array(
		'name' => "상태",
		'class' => "col-2 text-center",
		'render' => function($listtable_, $item_, $page_index_){
			echo $item_['status_name'];
		}
	),
	'button_area' => array(
		'name' => "",
		'class' => "col-4 text-right",
		'render' => function($listtable_, $item_, $page_index_){
			?>

			<a href="<?=pb_admin_url("manage-user/edit/".$item_['id'])?>" class="btn btn-default">수정</a>

			<?php
		}
	),
)), pb_hook_apply_filters("pb_adminpage_user_listtable_options", array(
	"no_rowdata" => "조회된 사용자가 없습니다.",
	'per_page' => 15,
	'class' => 'table pb-easytable pb-user-listtable',
	// 'ajax' => true,
)));




?>
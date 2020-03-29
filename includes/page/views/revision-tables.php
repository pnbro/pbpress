<?php

pb_easytable_register("pb-admin-page-revision-table", function($offset_, $per_page_){
	$page_id_ = _GET('page_id', -1);

	$statement_ = pb_page_revision_statement(array(
		'page_id' => $page_id_,
	));

	return array(
		'count' => $statement_->count(),
		'list' => $statement_->select("ID DESC", array($offset_, $per_page_)),
	);

}, array(
	"seq" => array(
		'name' => '',
		'class' => 'col-1 text-center',
		'rseq' => true,
	),
	"reg_date_ymdhi" => array(
		'name' => '리비젼일자',
		'class' => 'col-8',
		'render' => function($table_, $item_, $page_index_){
			?>
			<a href="" data-master-id="<?=$item_['id']?>"><?=$item_['reg_date_ymdhi']?></a>
			<?php
		}
	),
	"button_area" => array(
		'name' => '',
		'class' => 'col-2',
		'render' => function($table_, $item_, $page_index_){
			?>
			<a href="javascript:pb_admin_revision_delete(<?=$item_['id']?>)" class="btn btn-default btn-sm">삭제</a>
			<?php
		}
	),
), array(
	'class' => 'pb-admin-page-revision-table',
	"no_rowdata" => "등록된 리비젼이 없습니다.",
	'per_page' => 15,
));


?>
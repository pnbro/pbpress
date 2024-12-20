<?php

pb_easytable_register("pb-admin-gcode-table", function($offset_, $per_page_, $order_by_){
	$keyword_ = _GET('keyword');
	$statement_ = pb_gcode_statement(array(
		"keyword" => $keyword_,
	));

	$order_by_ = strlen($order_by_) ? $order_by_ : "code_id ASC";

	return array(
		'count' => $statement_->count(),
		'list' => $statement_->select($order_by_, array($offset_, $per_page_)),
	);
},array(
	"code_id" => array(
		'name' => __('코드'),
		'class' => 'col-2 text-center',
	),
	"code_nm" => array(
		'name' => __('코드명'),
		'class' => 'col-4',
		'render' => function($table_, $item_, $row_index_){
			?>
			<a href="" data-master-id="<?=$item_['code_id']?>"><?=$item_['code_nm']?></a>

			<?php

		}
	),
	"use_yn" => array(
		'name' => __('사용'),
		'class' => 'col-2 text-center',
	),
	"button_area" => array(
		'name' => '',
		'class' => 'col-4 text-center',
		'sort' => array(
			"code_id" => array(
				'title' => __("코드순"),
				'column' => "code_id",
			),

			"code_nm" => array(
				'title' => __("코드명순"),
				'column' => "code_nm",
			),
		),
		'render' => function($table_, $item_, $row_index_){
			?>
			<?php pb_hook_do_action('pb-admin-gcode-table-button-before'); ?>
			<a href="javascript:_pb_gcode_edit('<?=$item_['code_id']?>');" class="btn btn-default"><?=__('수정')?></a>
			<a href="javascript:_pb_gcode_remove	('<?=$item_['code_id']?>');" class="btn btn-black"><?=__('삭제')?></a>
			<?php pb_hook_do_action('pb-admin-gcode-table-button-after'); ?>
			<?php

		}
	)
), array(
	'class' => 'pb-gcode-table',
	"no_rowdata" => __("조회된 공통코드가 없습니다."),
	'per_page' => 15,
	'ajax' => true,
));

pb_easytable_register("pb-admin-gcode-dtl-table", function($offset_, $per_page_){
	$keyword_ = _GET('keyword');
	$code_id_ = _GET('master_id');

	$statement_ = pb_gcode_dtl_statement(array(
		'code_id' => $code_id_,
		'only_use' => false,
		"keyword" => $keyword_,
	));

	return array(
		'count' => $statement_->count(),
		'list' => $statement_->select("gcode_dtl.sort_char ASC", array($offset_, $per_page_)),
	);
},array(
	"code_did" => array(
		'name' => __('코드'),
		'class' => 'col-2 text-center',
	),
	"code_dnm" => array(
		'name' => __('코드명'),
		'class' => 'col-2',
	),
	"col1" => array(
		'name' => '',
		'class' => "col-1 text-center extra-col1",
	),
	"col2" => array(
		'name' => '',
		'class' => "col-1 text-center extra-col2",
	),
	"col3" => array(
		'name' => '',
		'class' => "col-1 text-center extra-col3",
	),
	"col4" => array(
		'name' => '',
		'class' => "col-1 text-center extra-col4",
	),
	"use_yn" => array(
		'name' => __('사용'),
		'class' => 'col-1 text-center',
	),
	"button_area" => array(
		'name' => '',
		'class' => 'col-2 text-center',
		'render' => function($table_, $item_, $row_index_){
			?>
			<?php pb_hook_do_action('pb-admin-gcode-dtl-table-button-before'); ?>
			<a href="javascript:_pb_gcode_dtl_edit('<?=$item_['code_id']?>', '<?=$item_['code_did']?>');" class="btn btn-default"><?=__('수정')?></a>
			<a href="javascript:_pb_gcode_dtl_remove('<?=$item_['code_id']?>', '<?=$item_['code_did']?>');" class="btn btn-black"><?=__('삭제')?></a>
			<?php pb_hook_do_action('pb-admin-gcode-dtl-table-button-after'); ?>
			<?php

		}
	)
), array(
	'class' => 'pb-gcode-dtl-table',
	"no_rowdata" => __("조회된 상세코드가 없습니다."),
	'per_page' => 15,
	'ajax' => true,
));

?>
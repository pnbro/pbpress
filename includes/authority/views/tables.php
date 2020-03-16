<?php

pb_easytable_register("pb-admin-authority-table", function($offset_, $per_page_){
	$keyword_ = isset($_GET['keyword']) ? $_GET['keyword'] : null;
	$statement_ = pb_authority_statement(array(
		"keyword" => $keyword_,
	));

	return array(
		'count' => $statement_->count(),
		'list' => $statement_->select(null, array($offset_, $per_page_)),
	);
}, array(
	"auth_name" => array(
		'name' => '권한명',
		'class' => 'col-4',
		'render' => function($table_, $item_, $row_index_){
			?>
			<a href="" data-master-id="<?=$item_['id']?>"><?=$item_['auth_name']?></a>
			<?php

		}
	),
	"slug" => array(
		'name' => '슬러그',
		'class' => 'col-4',
	),
	
	"button_area" => array(
		'name' => '',
		'class' => 'col-3 text-right',
		'render' => function($table_, $item_, $row_index_){
			?>
			<a href="javascript:_pb_authority_edit('<?=$item_['id']?>');" class="btn btn-default">수정</a>
			<a href="javascript:_pb_authority_remove('<?=$item_['id']?>');" class="btn btn-black">삭제</a>
			<?php

		}
	)
), array(
	'class' => 'pb-admin-authority-table',
	"no_rowdata" => "조회된 권한이 없습니다.",
	'per_page' => 15,
	'ajax' => true,
));

pb_easytable_register("pb-admin-authority-task-table", function($offset_, $per_page_){
	$auth_id_ = isset($_GET["master_id"]) ? $_GET["master_id"] : null;

	if(!strlen($auth_id_) || $auth_id_ < 0){
		return array(
			'count' => 0,
			'list' => array(),
		);

	}

	$task_types_ = pb_authority_task_types();

	foreach($task_types_ as $key_ => &$data_){
		$data_['slug'] = $key_;
	}

	$page_index_ = isset($_GET["page_index"]) ? (int)$_GET["page_index"] : 0;
	$per_page_ = count($task_types_);

	global $_cached_authority_map, $_cached_auth_data;
	$_cached_authority_map = pb_authority_map($auth_id_);
	$_cached_auth_data = pb_authority($auth_id_);
	

	return array(
		'count' => 1,
		'list' => $task_types_,
	);
}, array(
	"task_name" => array(
		'name' => '권한명',
		'class' => 'col-4',
		'render' => function($table_, $item_, $row_index_){

			$authority_task_types_ = pb_authority_task_types();
			$authority_task_type_data_ = isset($authority_task_types_[$item_["slug"]]) ? $authority_task_types_[$item_["slug"]] : null;

			echo isset($authority_task_type_data_) ? $authority_task_type_data_['name'] : "-";

		}
	),
	"slug" => array(
		'name' => '슬러그',
		'class' => 'col-4',
	),
	
	"grant_yn" => array(
		'name' => '권한부여',
		'class' => 'col-3 text-center',
		'render' => function($table_, $item_, $row_index_){
			$auth_id_ = isset($_GET["master_id"]) ? $_GET["master_id"] : null;

			$authority_task_types_ = pb_authority_task_types();
			$authority_task_type_data_ = isset($authority_task_types_[$item_["slug"]]) ? $authority_task_types_[$item_["slug"]] : null;

			global $_cached_authority_map, $_cached_auth_data;

			$selectable_ = isset($authority_task_type_data_['selectable']) ? $authority_task_type_data_['selectable'] : true;

			$task_checked_ = isset($_cached_authority_map[$item_['slug']]);
			$task_disabled_ = $_cached_auth_data['slug'] === PB_AUTHORITY_SLUG_ADMINISTRATOR && !$selectable_;


			?>

			<input type="checkbox" name="grant_yn" value="Y" data-auth-id="<?=$auth_id_?>" data-auth-task="<?=$item_["slug"]?>" <?=$task_checked_? "checked" : "" ?> <?=$task_disabled_ ? "disabled" : ""?>>

			<?php

		}
	)
), array(
	'class' => 'pb-admin-authority-table',
	'hide_pagenav' => true,
	"no_rowdata" => "조회된 권한이 없습니다.",
	'per_page' => 999,
	'ajax' => true,
));

?>
<?php 	
	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}

	$plugin_list_ = pb_plugin_list();

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-plugins.css">
<!-- <script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-plugins.js"></script> -->
	
<div class="manage-theme-frame"><form id="pb-manage-plugins-form" method="POST">

	<h3>플러그인설정</h3>

	<div class="pb-listtable-cond-form">
		<div class="left-frame">
			<select class="form-control" name="task_type" id="pb-manage-plugins-form-task-type-selector">
				<option value="">선택한 플러그인을...</option>
				<option value="active">활성화</option>
				<option value="deactive">비활성화</option>
			</select>
			<a href="javascript:pb_manage_plugins_batch();" class="btn btn-default">적용하기</a>
		</div>
		<div class="right-frame"></div>
	</div>	
	
	<input type="hidden" name="_request_chip", value="<?=pb_request_token("pbpress_manage_plugins")?>" id="pb-manage-plugins-form-request-chip">

	<table class="table table-hover table-striped pb-listtable pb-plugins-table" id="pb-manage-plugins-form-table">
		<thead>
			<tr>
				<th class="cb">
					<input type="checkbox" name="all_cb" data-all-cb>
				</th>
				<th class="plugin-name">플러그인명</th>
				<th class="author">제작자</th>
			</tr>
		</thead>

		<tbody>
			<?php foreach($plugin_list_ as $slug_ => $plugin_data_){ ?>
			<tr class="<?=$plugin_data_['activated'] ? "active" : "" ?>">
				<td class="cb">
					<input type="checkbox" name="cb" value="<?=$slug_?>" data-plugin-name="<?=$plugin_data_['name']?>">
				</td>
				<td class="plugin-name link-action">

					<div class="title-frame">
						<div class="name">
							<div class="text"><?=$plugin_data_['name']?></div>
							<?php if(strlen($plugin_data_['version'])){ ?>
								<small class="version">v<?=$plugin_data_['version']?></small>
							<?php } ?>
								
						</div>
						<div class="desc"><?=$plugin_data_['desc']?></div>
					</div>
					<div class="subaction-frame always">
						<?php if(!$plugin_data_['activated']){ ?>
							<a href="#" data-active-plugin-link="<?=$slug_?>" data-active-plugin-name="<?=$plugin_data_['name']?>">활성화</a>
						<?php }else{ ?>
							<a href="#" data-deactive-plugin-link="<?=$slug_?>" data-deactive-plugin-name="<?=$plugin_data_['name']?>">비활성화</a>
						<?php } ?>
					</div>
					<div class="xs-visiable-info">
						<?php if(strlen($plugin_data_['author'])){ ?>
							<div class="subinfo"><i class="icon material-icons">person</i> <span class="text"><?=$plugin_data_['author']?></span></div>
						<?php } ?>
					</div>

				</td>
				<td class="author">
					<?=strlen($plugin_data_['author']) ? $plugin_data_['author'] : "-"?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

</form></div>

<script type="text/javascript">
jQuery(document).ready(function(){
	$(document).on("click", "[data-active-plugin-link]",function(){
		var slug_ = $(this).attr("data-active-plugin-link");
		var name_ = $(this).attr("data-active-plugin-name");

		pb_manage_plugins_active([slug_], [name_]);

		return false;
	});

	$(document).on("click", "[data-deactive-plugin-link]",function(){
		var slug_ = $(this).attr("data-deactive-plugin-link");
		var name_ = $(this).attr("data-deactive-plugin-name");
		
		pb_manage_plugins_deactive([slug_], [name_]);

		return false;
	});

	$(document).on("click", ":input[name='all_cb']", function(){
		var toggled_ = !!$(this).prop("checked");
		$("#pb-manage-plugins-form-table :input[name='cb']").prop("checked", toggled_);
	});
	$(document).on("click", ":input[name='cb']", function(){
		var all_cbs_ = $("#pb-manage-plugins-form-table :input[name='cb']");
		var all_toggled_ = all_cbs_.length <= all_cbs_.filter(":checked").length;
		$("#pb-manage-plugins-form-table :input[name='all_cb']").prop("checked", all_toggled_);
	});
});

function pb_manage_plugins_active(slugs_, names_){
	names_ = names_.join(",");
	
	PB.confirm({
		title : "작업확인",
		content : "<p class='text-center'>"+names_+"</p>플러그인을 활성화 하시겠습니까?",
		button1 : "활성화하기"
	}, function(c_){
		if(!c_) return;
		_pb_manage_plugins_active(slugs_);
	});
}
function _pb_manage_plugins_active(slugs_){
	PB.post("admin-active-plugins",{
		slugs : slugs_,
		request_chip : $("#pb-manage-plugins-form-request-chip").val(),
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "플러그인 활성화 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "작업완료",
			content : "플러그인을 활성화 하였습니다."
		}, function(){
			location.reload();	
		});
	}, true);
}

function pb_manage_plugins_deactive(slugs_, names_){
	names_ = names_.join(",");
	
	PB.confirm({
		title : "작업확인",
		content : "<p class='text-center'>"+names_+"</p>플러그인을 비활성화 하시겠습니까?",
		button1 : "비활성화하기"
	}, function(c_){
		if(!c_) return;
		_pb_manage_plugins_deactive(slugs_);
	});
}
function _pb_manage_plugins_deactive(slugs_){
	PB.post("admin-deactive-plugins",{
		slugs : slugs_,
		request_chip : $("#pb-manage-plugins-form-request-chip").val(),
	}, function(result_, response_json_){
		if(!result_ || response_json_.success !== true){
			PB.alert({
				title : response_json_.error_title || "에러발생",
				content : response_json_.error_message || "플러그인 비활성화 중, 에러가 발생했습니다.",
			});
			return;
		}

		PB.alert({
			title : "작업완료",
			content : "플러그인을 비활성화 하였습니다."
		}, function(){
			location.reload();	
		});

		
	}, true);
}

function pb_manage_plugins_batch(){
	var task_type_ = $("#pb-manage-plugins-form-task-type-selector").val();
	var selected_slugs_ = [];
	var selected_names_ = [];

	var selected_cbs_ = $("#pb-manage-plugins-form-table :input[name='cb']:checked");
		selected_cbs_.each(function(){
			var cb_el_ = $(this);
			selected_slugs_.push(cb_el_.val());
			selected_names_.push(cb_el_.attr("data-plugin-name"));
		});

	if(selected_slugs_.length <= 0){
		PB.alert({
			title : "확인필요",
			content : "선택된 플러그인이 없습니다."
		});
		return;
	}

	switch(task_type_){
		case  "active" : 
			pb_manage_plugins_active(selected_slugs_, selected_names_);
			break;
		case  "deactive" : 
			pb_manage_plugins_deactive(selected_slugs_, selected_names_);
			break;
		default : 

			PB.alert({
				title : "확인필요",
				content : "선택된 작업이 없습니다."
			});
			return;

		break;
	}
}
</script>
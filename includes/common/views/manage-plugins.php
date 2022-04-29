<?php 	
	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}

	$plugin_list_ = pb_plugin_list();

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-plugins.css">
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-plugins.js"></script>
	
<div class="manage-theme-frame"><form id="pb-manage-plugins-form" method="POST">

	<h3><?=__('플러그인설정')?></h3>

	<div class="pb-listtable-cond-form">
		<div class="left-frame">
			<select class="form-control" name="task_type" id="pb-manage-plugins-form-task-type-selector">
				<option value=""><?=__('선택한 플러그인을...')?></option>
				<option value="active"><?=__('활성화')?></option>
				<option value="deactive"><?=__('비활성화')?></option>
			</select>
			<a href="javascript:pb_manage_plugins_batch();" class="btn btn-default"><?=__('적용하기')?></a>
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
				<th class="plugin-name"><?=__('플러그인명')?></th>
				<th class="author"><?=__('제작자')?></th>
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
							<a href="#" data-active-plugin-link="<?=$slug_?>" data-active-plugin-name="<?=$plugin_data_['name']?>"><?=__('활성화')?></a>
						<?php }else{ ?>
							<a href="#" data-deactive-plugin-link="<?=$slug_?>" data-deactive-plugin-name="<?=$plugin_data_['name']?>"><?=__('비활성화')?></a>
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
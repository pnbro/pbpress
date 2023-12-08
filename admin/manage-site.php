<?php 	
	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}

	$menu_list_ = pb_hook_apply_filters('pb-admin-manage-site-menu-list', array());


?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-site.css?v=<?=PB_SCRIPT_VERSION?>">
	

<div class="manage-site-tab"><form id="pb-manage-site-form" method="POST">

	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#manage-site-tab-basic" role="tab" data-toggle="tab"><?=__('기본설정')?></a></li>
		<?php foreach($menu_list_ as $menu_id_ => $menu_data_){ ?>
			<li role="presentation" ><a href="#manage-site-subtab-<?=$menu_id_?>" role="tab" data-toggle="tab"><?=$menu_data_['name']?></a></li>
		<?php }?>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="manage-site-tab-basic">
			<div class="manage-site-form-panel panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?=__('사이트정보')?></h3>
				</div>
				<div class="panel-body">
					
						<?php pb_hook_do_action('pb_admin_manage_site_form_before')?>

						<input type="hidden" name="_request_chip", value="<?=pb_request_token("pbpress_manage_site")?>">
						<div class="form-group">
							<label for="pb-manage-site-form-site_name"><?=__('사이트명')?> <sup class="text-primary">*</sup></label>
							<input type="text" name="site_name" placeholder="<?=__('사이트명 입력')?>" id="pb-manage-site-form-site_name" class="form-control" required data-error="<?=__('사이트명을 입력하세요')?>" value="<?=pb_option_value("site_name")?>">
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>

						<div class="form-group">
							<label for="pb-manage-site-form-site_desc"><?=__('사이트한줄설명')?></label>
							<input type="text" name="site_desc" placeholder="<?=__('사이트이름 입력')?>" id="pb-manage-site-form-site_desc" class="form-control" value="<?=pb_option_value("site_desc")?>">
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>

						<div class="form-group">
							<label for="pb-manage-site-form-site_theme"><?=__('테이블 재설치')?></label>
							<div class="row">
								<div class="col-xs-12 col-sm-4">
									<button type="button" class="btn btn-default btn-block " onclick="pb_manage_site_reinstall_tables();"><?=__('테이블 재설치')?></button>
								</div>
							</div>
							<div class="help-block hint"><?=__('*테이블 재설치가 필요할 경우에만 수행')?></div>
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>

						<?php pb_hook_do_action('pb_admin_manage_site_form_after')?>					

				</div>
			</div>
		</div>
		<?php foreach($menu_list_ as $menu_id_ => $menu_data_){ ?>
			<div role="tabpanel" class="tab-pane" id="manage-site-subtab-<?=$menu_id_?>">
				<?php call_user_func_array($menu_data_['renderer'], array($menu_data_))?>
			</div>
		<?php }?>
		
	</div>

	<hr>
	<div class="text-center">
		<button type="submit" class="btn btn-primary btn-lg"><?=__('변경사항 저장')?></button>
		<div><small class="help-block">PBPress v<?=PB_VERSION?></small></div>
	</div>


	

</form></div>
	
				
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-site.js?v=<?=PB_SCRIPT_VERSION?>"></script>
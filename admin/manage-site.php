<?php 	
	if(!defined('PB_DOCUMENT_PATH')){
		die( '-1' );
	}
?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-site.css">


	<div class="manage-site-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">사이트정보</h3>
		</div>
		<div class="panel-body">
			<form id="pb-manage-site-form" method="POST">
				<?php pb_hook_do_action('pb_admin_manage_site_form_before')?>

				<input type="hidden" name="_request_chip", value="<?=pb_session_instance_token("pbpress_manage-site")?>">
				<div class="form-group">
					<label for="pb-manage-site-form-site_name">사이트명 <sup class="text-primary">*</sup></label>
					<input type="text" name="site_name" placeholder="사이트명 입력" id="pb-manage-site-form-site_name" class="form-control" required data-error="사이트명을 입력하세요" value="<?=pb_option_value("site_name")?>">
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-site-form-site_desc">사이트한줄설명</label>
					<input type="text" name="site_desc" placeholder="사이트이름 입력" id="pb-manage-site-form-site_desc" class="form-control" value="<?=pb_option_value("site_desc")?>">
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-site-form-site_theme">사이트테마</label>
					<select class="form-control" name="site_theme" required data-error="테마를 선택하세요.">
						<option value="">-테마선택-</option>
						<?php foreach(pb_theme_list() as $theme_key_ => $theme_data_){ ?>
							<option value="<?=$theme_key_?>" <?=pb_selected(pb_current_theme(), $theme_key_)?>><?=$theme_data_['name']?></option>
						<?php } ?>
					</select>
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-site-form-site_theme">테이블 재설치</label>
					<button type="button" class="btn btn-default btn-block " onclick="pb_manage_site_reinstall_tables();">테이블 재설치</button>
					<div class="help-block">*테이블 재설치가 필요할 경우에만 수행</div>
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<?php pb_hook_do_action('pb_admin_manage_site_form_after')?>

				<hr>
				<button type="submit" class="btn btn-primary btn-block btn-lg">변경사항 저장</button>
			</form>
		</div>
	</div>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-site.js"></script>
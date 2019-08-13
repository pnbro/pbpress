<?php 	
	
	$menu_categories_ = pb_menu_categories();	
	$menu_target_list_ = pb_menu_target_list();

	$menu_categories_info_ = array();
	$menu_list_ = pb_menu_list();

	foreach($menu_categories_ as $key_ => $data_){
		$menu_categories_info_[$key_] = gettype($data_) === "string" ? $data_ : $data_['title'];
	}

?>
<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/pages/admin/manage-menu/edit.css">
<script type="text/javascript">
window._pb_menu_editor_categories = <?=json_encode($menu_categories_info_)?>;
</script>
<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/pages/admin/manage-menu/edit.js"></script>
<h3>메뉴 수정하기</h3>


<div class="pb-menu-item-edit-modal modal fade" tabindex="-1" role="dialog" id="pb-menu-item-edit-modal"><div class="modal-dialog" role="document">
	
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">메뉴항목수정하기</h4>
		</div>
		<div class="modal-body" data-menu-item-edit-body>
			<form id="pb-menu-item-edit-modal-form" method="POST"></form>
			<form id="pb-menu-item-edit-modal-meta-form" method="POST"></form>

		</div>
		<div class="modal-footer">
			<a href="" class="btn btn-default" data-dismiss="modal">취소</a>
			<button type="button" class="btn btn-primary" data-submit-btn>변경사항 저장</button>
		</div>
	</div>
</div></div>

<div id="pb-menu-editor">
	
	<div class="menu-edit-frame"><div class="wrap">
		
		<div class="col-menu-editor"><form id="pb-menu-editor-form" method="POST">
			
			<div class="panel panel-default">
				<div class="panel-heading">
					<select class="form-control input-lg" name="menu_id" data-menu-selector>
						<optgroup label="등록된 메뉴">
							<?php foreach($menu_list_ as $menu_data_){ ?>
									<option value="<?=$menu_data_['id']?>"><?=$menu_data_['title']?></option>
							<?php } ?>
						</optgroup>
						<optgroup label="새로운 메뉴 등록">
							<option value="-9" data-add-new-menu-option="Y">새로운 메뉴 등록하기</option>
						</optgroup>
						
						
						
					</select>
				</div>
				<div class="panel-body">
					
					<div class="edit-frame" data-menu-edit-frame>
						<div class="row">
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<label>메뉴명</label>
									<input type="text" name="menu_title" placeholder="메뉴명 입력" class="form-control" required data-error="메뉴명을 입력하세요">

									<div class="help-block with-errors"></div>
									<div class="clearfix"></div>
								</div>
							</div>
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<label>메뉴슬러그</label>
									<input type="text" name="menu_slug" placeholder="슬러그 입력" class="form-control">

									<div class="help-block with-errors"></div>
									<div class="clearfix"></div>
								</div>
							</div>
						</div>
								
							
	

						<label>메뉴항목</label>
						<div class="menu-list-frame">
							<div class="empty-text">우측에서 항목을 선택하여 추가하세요</div>
							<ul class="pb-menu-list" data-menu-list></ul>

							
						</div>
					</div>
					<div class="empty-frame">
						위에서 수정할 메뉴를 먼저 선택하세요
					</div>
					<div class="loading-frame">
						<div class="pb-loading-indicator"></div>
					</div>
				</div>
			</div>
		

			<div class="button-area text-right" data-submit-btn-frame>
				<button type="submit" class="btn btn-primary btn-lg">변경사항 저장</button>
			</div>

				
		</form></div>
		<div class="col-menu-target-list">
			<div class="menu-target-list-panel panel panel-default">
				<div class="panel-heading">
					<ul class="nav nav-tabs" role="tablist">
						<?php 
							$is_first_ = true;
							foreach($menu_categories_ as $category_ => $data_){
								$title_ = null;
								
								if(gettype($data_) === "string") $title_ = $data_;
								else $title_ = $data_['title'];

							?>
							<li role="presentation " class="<?=$is_first_ ? "active" : ""?>" ><a href="#pb-menu-target-list-tab-<?=$category_?>" role="tab" data-toggle="tab"><?=$title_?></a></li>
						<?php 
							$is_first_ = false;
						} ?>
					</ul>
				</div>
				<div class="panel-body">

					<div class="tab-content" id="pb-menu-target-tab-content">
						<?php 
							$is_first_ = true;
							foreach($menu_categories_ as $category_ => $data_){

								$title_ = null;
								$render_func_ = "_pb_menu_category_list_default_render";

								if(gettype($data_) === "string") $title_ = $data_;
								else{
									$title_ = $data_['title'];
									$render_func_ = $data_['render'];
								}

								

							?>
							<div role="tabpanel" class="tab-pane <?=$is_first_ ? "active" : ""?>" id="pb-menu-target-list-tab-<?=$category_?>" data-menu-target-tab="<?=$category_?>">
								<form id="pb-menu-target-list-tab-form-<?=$category_?>" data-menu-target-tab-form="<?=$category_?>">
								<?php 
									call_user_func_array($render_func_, array($category_, $menu_target_list_[$category_]));
								?>
								</form>
							</div>
						<?php 
								$is_first_ = false;
							}	?>
					</div>
					<div class="button-area text-right">
						<a href="" class="btn btn-default btn-block" data-add-menu-item-btn>메뉴항목 추가</a>
					</div>
				</div>
			</div>
	

		</div>
	</div></div>
</div>
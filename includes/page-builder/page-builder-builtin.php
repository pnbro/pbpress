<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_page_builder_add_shared_modal_to_footer(){
	?>

<div class="pb-page-builder-element-edit-modal modal fade" tabindex="-1" role="dialog" id="pb-page-builder-element-edit-modal"><div class="modal-dialog" role="document"><form id="pb-page-builder-element-edit-modal-form" method="POST">
	
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">요소수정</h4>
		</div>
		<div class="modal-body">
			
		</div>
		<div class="modal-footer">
			<a href="" class="btn btn-default" data-dismiss="modal">취소</a>
			<button type="submit" class="btn btn-primary">변경사항 저장</button>
		</div>
	</div>
</form></div></div>
<div class="pb-page-builder-element-picker-modal modal fade" tabindex="-1" role="dialog" id="pb-page-builder-element-picker-modal"><div class="modal-dialog" role="document">
	
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">요소추가</h4>
		</div>
		<div class="modal-body">
			<form id="pb-page-builder-element-picker-cond-form" class="form-inline text-right" data-element-search-form>
				<input type="hidden" name="parent">
				<div class="input-group input-lg">
					<input type="text" class="form-control search-input" placeholder="요소 검색..." name="keyword">
					<span class="input-group-btn">
						<button class="btn btn-default" type="submit">검색</button>
					</span>
				</div>
			</form>
			<div class="loading-frame">
				<div class="pb-loading-indicator loading-indicator"></div>
			</div>
			<div class="element-list" data-element-list></div>
		</div>
	</div>
</div></div>
<div class="pb-page-builder-page-settings-modal modal fade" tabindex="-1" role="dialog" id="pb-page-builder-page-settings-modal"><div class="modal-dialog" role="document">
	
	<div class="modal-content"><form id="pb-page-builder-page-settings-modal-form" method="POST">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">페이지설정</h4>
		</div>
		<div class="modal-body">

			<div class="form-group">
				<label>공통 StyleSheet</label>
				<textarea data-style-sheet-editor></textarea>
			</div>

			<div class="form-group">
				<label>공통 JavaScript</label>
				<textarea data-javascript-editor></textarea>
			</div>
			
		</div>
		<div class="modal-footer">
			<a href="" class="btn btn-default" data-dismiss="modal">취소</a>
			<button type="submit" class="btn btn-primary">변경사항저장</button>
		</div>
	</form></div>
</div></div>


	<?php
}
pb_hook_add_action("pb_admin_foot","_pb_page_builder_add_shared_modal_to_footer");
	
function _pb_editor_register_page_builder($results_){
	$results_['pbpagebuilder'] = array(
		'title' => "빌더",
		'rendering' => "_pb_editor_render_page_builder",
	);

	$results_['text'] = array(
		'title' => "텍스트",
		'rendering' => "_pb_editor_editor_text",
	);

	return $results_;
}
pb_hook_add_filter('pb_editor_list', '_pb_editor_register_page_builder');

function _pb_editor_render_page_builder($content_, $data_){
	$editor_id_ = $data_['id'];

	pb_page_builder($content_, array(
		'id' => $editor_id_."-page-builder",
	));
	?>
	
	<script type="text/javascript">
		jQuery(document).ready(function(){
			pb_add_editor("pbpagebuilder", {
				initialize : $.noop,
				html : function(html_){
					if(html_ !== undefined){
						$("#<?=$editor_id_?>-page-builder").pb_page_builder().apply_xml(html_);
					}

					return $("#<?=$editor_id_?>-page-builder").pb_page_builder().to_xml();
				}
			});
		});
	</script>
	<?php
}

?>
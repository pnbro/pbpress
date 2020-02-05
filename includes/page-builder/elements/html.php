<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPageBuilderElement_html extends PBPageBuilderElement{

	function initialize(){
		$this->add_edit_form("common", array($this, "render_admin_form"));
	}

	public function render($data_ = array(), $element_content_ = null){
		$element_data_ = $data_['properties'];
		$id_ = isset($element_data_['id']) ? $element_data_['id'] : null;
		$class_ = isset($element_data_['class']) ? $element_data_['class'] : null;
		$unique_class_name_ = isset($element_data_['unique_class_name']) ? $element_data_['unique_class_name'] : null;

		?>
		<div class="html <?=$class_?> <?=$unique_class_name_?>" <?=strlen($id_) ? "id='".$id_."'" : "" ?>><?=$this->render_content($element_content_)?></div>
		<?php
	}

	function render_admin_form($element_data_ = array(), $content_ = null){
		$temp_form_id_ = "html-input-".pb_random_string(5);

		?>
		<?php pb_editor_load_trumbowyg_library(); ?>

		<div class="form-group">
			<label>HTML에디터</label>
			<textarea id="<?=$temp_form_id_?>" name="content"><?=stripslashes($content_)?></textarea>
			<div class="clearfix"></div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				var target_textarea_ = $("#<?=$temp_form_id_?>");
				var target_module_ = CodeMirror.fromTextArea(target_textarea_[0], {
					lineNumbers: true,
					selectionPointer: true,
					styleActiveLine: true,
					matchBrackets: true, 
					autoCloseBrackets : true,
					continueComments : true,
					selectionPointer: true,
					mode: "text/html",
					extraKeys: {"Ctrl-Space": "autocomplete"},
				});

				target_module_.on("change", $.proxy(function(instance_){
					this.val(instance_.getValue());
				}, target_textarea_));

				setTimeout($.proxy(function() {
					target_module_.refresh();
				}, target_module_),100);
			});
		</script>


		<?php
	}
}

pb_page_builder_add_element("html", array(
	'name' => "HTML",
	'desc' => "",
	'icon' => PB_LIBRARY_URL."img/page-builder/html.jpg",
	'element_object' => "PBPageBuilderElement_html",
	'edit_categories' => array("common", "styles"),
	'loadable' => false,
	'preview_fields' => array(
		array(
			'name' => 'content',
			'type' => 'html',
			'display' => 'block',
		),
	),
	'category' => "기본",
));

?>
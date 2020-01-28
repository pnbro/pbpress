<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_editor_list_sort($a_, $b_){
	$a_['sort'] = isset($a_['sort']) ? $a_['sort'] : 99;
	$b_['sort'] = isset($b_['sort']) ? $b_['sort'] : 99;

	if($a_['sort'] == $b_['sort']){
		return 0;
	}
	return ($a_['sort'] < $b_['sort']) ? -1 : 1;
}

function pb_editor_list(){
	$results_ = pb_hook_apply_filters('pb_editor_list', array());
	uasort($results_, '_pb_editor_list_sort');
	return $results_;
}

function pb_editor($name_, $content_ = null, $data_ = array()){
	$base_editor_list_ = pb_editor_list();
	$editor_list_ = array();

	global $pb_config,$pb_editor_script_loaded;

	if(!$pb_editor_script_loaded){

		foreach($base_editor_list_ as $key_ => $editor_data_){
			if(isset($editor_data_['library'])){
				call_user_func($editor_data_['library']);
			}
		}

		$pb_editor_script_loaded = true;
	}

	if(isset($data_['editors'])){
		foreach($data_['editors'] as $editor_id_){
			$editor_list_[$editor_id_] = $base_editor_list_[$editor_id_];
		}
	}else{
		$editor_list_ = $base_editor_list_;
	}

	$first_editor_id_ = null;
	foreach($editor_list_ as $key_ => $editor_data_){
		$first_editor_id_ = $key_;
		break;
	}

	$editor_id_ = isset($data_['id']) ? $data_['id'] : "pb-editor-".pb_random_string(5, PB_RANDOM_STRING_NUMLOWER);
	$editor_ = isset($data_['editor']) && strlen($data_['editor']) ? $data_['editor'] : $first_editor_id_;
	$script_options_ = array();

	$script_options_['input'] = "#{$editor_id_}-input";
	if(isset($data_['min_height'])) $script_options_['min_height'] = $data_['min_height'];
	if(isset($data_['max_height'])) $script_options_['max_height'] = $data_['max_height'];
	if(isset($data_['height'])) $script_options_['height'] = $data_['height'];
	$data_['id'] = $editor_id_;

	?>
	<input type="hidden" name="<?=$name_?>" value="<?=htmlentities($content_, null, $pb_config->charset)?>" id="<?=$editor_id_?>-input">
	<div id="<?=$editor_id_?>" class="pb-editor">
		<ul class="nav nav-tabs tab-right" role="tablist">
			<?php foreach($editor_list_ as $key_ => $editor_data_){ ?>
				<li role="presentation" class="" data-key="<?=$key_?>"><a href="#<?=$editor_id_?>-nav-tab-<?=$key_?>" role="tab" data-toggle="tab"><?=$editor_data_['title']?></a></li>
			<?php } ?>
		</ul>
		<div class="clearfix"></div>
		<div class="tab-content">
		<?php foreach($editor_list_ as $key_ => $editor_data_){ ?>
			<div role="tabpanel" class="tab-pane" id="<?=$editor_id_?>-nav-tab-<?=$key_?>" data-key="<?=$key_?>">
				<?php call_user_func_array($editor_data_['rendering'], array($content_, $data_)) ?>
			</div>
		<?php } ?>
		</div>

	</div>

	<script type="text/javascript">jQuery(document).ready(function(){
		var editor_module_ = $("#<?=$editor_id_?>").pb_editors(<?=json_encode($script_options_)?>);
			editor_module_.toggle_editor("<?=$editor_?>");
	});</script>


	<?php 
}
	
function _pb_editor_register_defaults($results_){

	$results_['text'] = array(
		'title' => "텍스트",
		'rendering' => "_pb_editor_rendering_for_text",
		'sort' => 1,
	);

	$results_['editor'] = array(
		'title' => "에디터",
		'rendering' => "_pb_editor_rendering_for_editor",
		'sort' => 11,
	);

	$results_['html'] = array(
		'title' => "HTML",
		'rendering' => "_pb_editor_rendering_for_html",
		'sort' => 13,
	);

	return $results_;
}
pb_hook_add_filter('pb_editor_list', '_pb_editor_register_defaults');

function _pb_editor_rendering_for_editor($content_, $data_){
	$editor_id_ = $data_['id'];
	$placeholder_ = isset($data_['placeholder']) ? $data_['placeholder'] : null;
	$min_height_ = isset($data_['min_height']) ? $data_['min_height'] : 300;
	$max_height_ = isset($data_['max_height']) ? $data_['max_height'] : 800;
	$height_ = isset($data_['height']) ? $data_['height'] : null;
	?>
	<?php pb_editor_load_trumbowyg_library(); ?>
	<div id="<?=$editor_id_?>-editor"><?=$content_?></div>
	<style type="text/css">
	#<?=$editor_id_?>-editor{
		max-height: <?=$max_height_?>px;
		min-height: <?=$min_height_?>px;
	}
	</style>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			pb_add_editor("editor", {
				initialize : function(){
					var editor_el_ = $("#<?=$editor_id_?>-editor");
					var editor_module_ = editor_el_.trumbowyg({
						lang : "<?=pb_current_locale(true)?>",
					});

					editor_el_.data("editor-module", editor_module_);
				},
				html : function(html_){
					var editor_el_ = $("#<?=$editor_id_?>-editor");
					var editor_module_ = $("#<?=$editor_id_?>-editor").data("editor-module");
					if(html_ !== undefined){
						editor_module_.html(html_);
					}

					return editor_module_.html();
				}
			});
		});


	</script>
	<?php
}

function pb_editor_load_trumbowyg_library(){
	global $_pb_editor_trumbowyg_library_loaded;
	if(!$_pb_editor_trumbowyg_library_loaded){
		$current_locale_ = pb_current_locale(true);
		?>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/trumbowyg/trumbowyg.js?version=2.21.0"></script>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/trumbowyg/langs/<?=$current_locale_?>.js?version=2.21.0"></script>

		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/trumbowyg/plugins/resizimg/resizable-resolveconflict.js"></script>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/trumbowyg/plugins/resizimg/jquery-resizable.js"></script>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/trumbowyg/plugins/resizimg/trumbowyg.resizimg.js"></script>

		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>js/trumbowyg/trumbowyg.pb.extends.js"></script>

		<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/trumbowyg/trumbowyg.css?version=2.21.0">

		<?php

		$_pb_editor_trumbowyg_library_loaded = true;
	}
}

function _pb_editor_rendering_for_text($content_, $data_){
	$editor_id_ = $data_['id'];
	$placeholder_ = isset($data_['placeholder']) ? $data_['placeholder'] : null;
	$min_height_ = isset($data_['min_height']) ? $data_['min_height'] : 300;
	$max_height_ = isset($data_['max_height']) ? $data_['max_height'] : 800;
	$height_ = isset($data_['height']) ? $data_['height'] : null;
	?>
	<textarea class="form-control" placeholder="<?=$placeholder_?>" id="<?=$editor_id_?>-text-textarea" style="min-height: <?=$min_height_?>px; max-height: <?=$max_height_?>px; height: <?=$height_?>px"><?=$content_?></textarea>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			pb_add_editor("text", {
				initialize : function(){
					$("#<?=$editor_id_?>-text-textarea").change($.proxy(function(){
						this.sync_input();
					}, this));
				},
				html : function(html_){
					if(html_ !== undefined){
						$("#<?=$editor_id_?>-text-textarea").val(html_);
					}

					return $("#<?=$editor_id_?>-text-textarea").val();
				}
			});
		});
	</script>
	<?php
}

function _pb_editor_rendering_for_html($content_, $data_){
	$editor_id_ = $data_['id'];
	$placeholder_ = isset($data_['placeholder']) ? $data_['placeholder'] : null;
	$min_height_ = isset($data_['min_height']) ? $data_['min_height'] : 300;
	$max_height_ = isset($data_['max_height']) ? $data_['max_height'] : 800;
	$height_ = isset($data_['height']) ? $data_['height'] : null;
	?>
	<textarea placeholder="<?=$placeholder_?>" id="<?=$editor_id_?>-html-textarea" style="min-height: <?=$min_height_?>px; max-height: <?=$max_height_?>px; height: <?=$height_?>px"><?=$content_?></textarea>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			pb_add_editor("html", {
				initialize : function(){
					var target_textarea_ = $("#<?=$editor_id_?>-html-textarea");

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

					target_textarea_.data("code-instance", target_module_);
				},
				html : function(html_){
					var target_textarea_ = $("#<?=$editor_id_?>-html-textarea");
					var target_module_ = target_textarea_.data("code-instance");

					if(html_ !== undefined){
						target_module_.setValue(html_);
					}

					return target_module_.getValue();
				}
			});
		});
	</script>
	<?php
}

?>
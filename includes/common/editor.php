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

function pb_wysiwyg_editor_renderers(){
	global $_pb_wysiwyg_editor_renderers;
	if(isset($_pb_wysiwyg_editor_renderers)) return $_pb_wysiwyg_editor_renderers;

	$_pb_wysiwyg_editor_renderers = pb_hook_apply_filters('pb_wysiwyg_editor_renderers', array(
		'trumbowyg' => '_pb_wysiwyg_editor_for_trumbowyg',
		'summernote' => '_pb_wysiwyg_editor_for_summernote',
	));

	return $_pb_wysiwyg_editor_renderers;
}
function pb_wysiwyg_editor($name_, $content_, $data_){
	global $pb_config;
	$wysiwyg_editor_renderers_ = pb_wysiwyg_editor_renderers();

	if(!isset($wysiwyg_editor_renderers_[$pb_config->wysiwyg_editor])){
		echo "{$pb_config->wysiwyg_editor} 에디터가 존재하지 않습니다.";
		return false;
	}

	$render_ = $wysiwyg_editor_renderers_[$pb_config->wysiwyg_editor];

	if(!is_callable($render_)){
		echo "{$pb_config->wysiwyg_editor} 에디터 렌더러가 존재하지 않습니다.";
		return false;
	}

	call_user_func_array($render_, array($name_, $content_, $data_));
	return true;
}


function _pb_wysiwyg_editor_for_trumbowyg($name_, $content_, $data_){
	global $pb_config;
	$editor_id_ = $data_['id'];
	$placeholder_ = isset($data_['placeholder']) ? $data_['placeholder'] : null;
	$min_height_ = isset($data_['min_height']) ? $data_['min_height'] : 300;
	$max_height_ = isset($data_['max_height']) ? $data_['max_height'] : 800;
	$height_ = isset($data_['height']) ? $data_['height'] : 200;
	$lang_ = isset($data_['lang']) ? $data_['lang'] : $pb_config->default_locale();
	$placeholder_ = isset($data_['placeholder']) ? $data_['placeholder'] : null;


	?>
	<?php pb_editor_load_trumbowyg_library(); ?>
	<input type="hidden" name="<?=$name_?>" id="<?=$editor_id_?>-input" value="<?=htmlentities($content_)?>">
	<div id="<?=$editor_id_?>" placeholder="<?=$placeholder_?>"><?=$content_?></div>
	<style type="text/css">
	#<?=$editor_id_?>{
		max-height: <?=$max_height_?>px;
		min-height: <?=$min_height_?>px;
	}
	</style>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		$("#<?=$editor_id_?>").pb_wysiwyg_editor_trumbowyg({
			lang : "<?=$lang_?>",
			input : "#<?=$editor_id_?>-input"
		});
	});
	</script>
	<?php
}

function pb_editor_load_trumbowyg_library(){
	global $pb_config, $_pb_editor_trumbowyg_library_loaded;
	if(!$_pb_editor_trumbowyg_library_loaded){
		$default_locale_ = $pb_config->default_locale();
		$default_locale_ = substr($default_locale_, 0, strpos($default_locale_, "_"));
		$default_locale_ = strtolower($default_locale_);
		$current_locale_ = pb_current_locale(true);
		?>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/trumbowyg/trumbowyg.js?version=2.21.0"></script>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/trumbowyg/langs/<?=$default_locale_?>.js?version=2.21.0"></script>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/trumbowyg/langs/<?=$current_locale_?>.js?version=2.21.0"></script>

		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/trumbowyg/plugins/resizimg/resizable-resolveconflict.js"></script>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/trumbowyg/plugins/resizimg/jquery-resizable.js"></script>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/trumbowyg/plugins/resizimg/trumbowyg.resizimg.js"></script>

		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/trumbowyg/trumbowyg.pb.extends.js"></script>

		<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/trumbowyg/trumbowyg.css?version=2.21.0">

		<?php

		pb_hook_do_action('pb_editor_load_trumbowyg_library');

		$_pb_editor_trumbowyg_library_loaded = true;
	}
}

function _pb_wysiwyg_editor_for_summernote($name_, $content_, $data_){
	global $pb_config;
	$editor_id_ = $data_['id'];
	$placeholder_ = isset($data_['placeholder']) ? $data_['placeholder'] : null;
	$min_height_ = isset($data_['min_height']) ? $data_['min_height'] : 300;
	$max_height_ = isset($data_['max_height']) ? $data_['max_height'] : 800;
	$height_ = isset($data_['height']) ? $data_['height'] : 200;
	$lang_ = isset($data_['lang']) ? $data_['lang'] : $pb_config->default_locale();

	?>
	<?php pb_editor_load_summernote_library(); ?>
	<textarea name="<?=$name_?>" id="<?=$editor_id_?>" placeholder="<?=$placeholder_?>"><?=stripslashes($content_)?></textarea>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		$("#<?=$editor_id_?>").pb_wysiwyg_editor_summernote({
			lang : "<?=$lang_?>",
			min_height : <?=$min_height_?>,
			max_height : <?=$max_height_?>,
			height : <?=$height_?>,
			placeholder : "<?=$placeholder_?>",
		});
	});
	</script>
	<?php
}

function pb_editor_load_summernote_library(){
	global $pb_config, $_pb_editor_load_summernote_library;
	if(!$_pb_editor_load_summernote_library){
		$default_locale_ = $pb_config->default_locale();
		$current_locale_ = pb_current_locale();
		?>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/summernote/summernote.js?version=0.8.16"></script>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/summernote/lang/summernote_<?=$default_locale_?>.js?version=0.8.16"></script>
		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/summernote/lang/summernote_<?=$current_locale_?>.js?version=0.8.16"></script>

		<script type="text/javascript" src="<?=PB_LIBRARY_URL?>editors/summernote/summernote.pb.extends.js"></script>

		<link rel="stylesheet" type="text/css" href="<?=PB_LIBRARY_URL?>css/summernote/summernote.css?version=0.8.16">

		<?php

		pb_hook_do_action('pb_editor_load_summernote_library');

		$_pb_editor_load_summernote_library = true;
	}
}

function _pb_editor_rendering_for_editor($content_, $data_){

	$editor_id_ = $data_['id'].'-editor';
	$data_['id'] = $editor_id_;
	$temp_editor_name_ = "pb_editor_".pb_random_string(5);
	$results_ = pb_wysiwyg_editor($temp_editor_name_, $content_, $data_);
	if(!$results_) return;
	?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			pb_add_editor("editor", {
				initialize : function(){
					$("#<?=$editor_id_?>").pb_wysiwyg_editor().options({
						"sync" : $.proxy(function(){
							this.sync_input();
						}, this)
					});
				},
				html : function(html_){
					return $("#<?=$editor_id_?>").pb_wysiwyg_editor().content(html_);
				}
			});
		});


	</script>
	<?php
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
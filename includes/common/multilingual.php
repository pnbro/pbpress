<?php

define('PB_SESSION_CURRENT_LOCALE', '_PB_SESSION_CURRENT_LOCALE_');

function pb_locale_update($locale_){
	setlocale(LC_ALL, $locale_);
	pb_session_put(PB_SESSION_CURRENT_LOCALE, $locale_);
	pb_hook_do_action('pb_locale_updated', $locale_);
}

function pb_current_locale($short_ = false){
	$current_locale_ = pb_session_get(PB_SESSION_CURRENT_LOCALE);
	if(!isset($current_locale_)){
		$current_locale_ = setlocale(LC_ALL, 0);	
	}
	global $pb_config;
	
	if($short_) return substr($current_locale_, 0, strpos($current_locale_, "_"));
	return $current_locale_;
}

define('PBDOMAIN', 'pbpress');

global $pb_lang_domain_maps;
$pb_lang_domain_maps = array();

function pb_lang_load_translations($domain_, $json_dir_path_){
	global $pb_lang_domain_maps;
	if(!isset($pb_lang_domain_maps[$domain_])){
		$pb_lang_domain_maps[$domain_] = array(
			'path' => rtrim($json_dir_path_, '/').'/',
			'locales' => array(),
		);
	}

	if(!file_exists($json_dir_path_)) return false;

	foreach(glob($pb_lang_domain_maps[$domain_]['path'].'*.php') as $filename_){
		
		$locale_name_ = basename($filename_, ".php");
		$pb_lang_domain_maps[$domain_]['locales'][$locale_name_] = array(
			'loaded' => false,
			'translations' => array(),
		);
	}

	pb_hook_do_action('pb_lang_translations_loaded', $domain_);
	return true;
}
function pb_lang_is_loaded_translations_json($domain_, $locale_){
	global $pb_lang_domain_maps;
	if(!isset($pb_lang_domain_maps[$domain_])) return false;
	if(!isset($pb_lang_domain_maps[$domain_]['locales'][$locale_])) return false;
	return $pb_lang_domain_maps[$domain_]['locales'][$locale_]['loaded'];
}

function pb_lang_load_translations_json($domain_, $locale_){
	global $pb_lang_domain_maps;
	if(!isset($pb_lang_domain_maps[$domain_])) return false;
	if(!isset($pb_lang_domain_maps[$domain_]['locales'][$locale_])) return false;

	if(!$pb_lang_domain_maps[$domain_]['locales'][$locale_]['loaded']){
		$translations_data_ = @include($pb_lang_domain_maps[$domain_]['path'].$locale_.'.php');
		
		if(!isset($translations_data_)){
			return false;
		}

		$pb_lang_domain_maps[$domain_]['locales'][$locale_]['translations'] = $translations_data_;
		pb_hook_do_action('pb_lang_translations_json_loaded', $domain_, $locale_, $translations_data_);
		return true;
	}

	return false;
}

function __($text_, $domain_ = PBDOMAIN){
	global $pb_lang_domain_maps;
	if(!isset($pb_lang_domain_maps[$domain_])) return $text_;

	$current_locale_ = pb_current_locale();
	if(!isset($pb_lang_domain_maps[$domain_]['locales'][$current_locale_])) return $text_;

	if(!$pb_lang_domain_maps[$domain_]['locales'][$current_locale_]['loaded']){
		if(!pb_lang_load_translations_json($domain_, $current_locale_)) return false;
	}

	$translations_ = $pb_lang_domain_maps[$domain_]['locales'][$current_locale_]['translations'];

	if(isset($translations_[$text_])) return $translations_[$text_];
	else return $text_;
}
function _e($text_, $domain_ = PBDOMAIN){
	echo __($text_, $domain_);
}

define('PB_LANG_PBLANG_SLUG', "_pblang.js");

pb_rewrite_register(PB_LANG_PBLANG_SLUG, array(
	"rewrite_handler" => "pb_register_rewrite_handler_lang_pblang_script",
));
function pb_register_rewrite_handler_lang_pblang_script(){
	global $pb_lang_domain_maps;
	$current_locale_ = pb_current_locale();
	header('Content-Type: application/javascript', true);

	$results_ = array();

	foreach($pb_lang_domain_maps as $domain_ => $map_data_){
		pb_lang_load_translations_json($domain_, $current_locale_);
	}

	foreach($pb_lang_domain_maps as $domain_ => $map_data_){
		$results_[$domain_] = isset($map_data_['locales'][$current_locale_]) ? $map_data_['locales'][$current_locale_]['translations'] : null;
	}
?>window.PBLANG = <?=json_encode((object)$results_)?>;<?php
	pb_end();
}

function pb_lang_pblang_url(){
	return pb_hook_apply_filters('pb_lang_pblang_url', pb_home_url(PB_LANG_PBLANG_SLUG));
}

function _pb_setup_pblang_script_to_head(){
?><script type="text/javascript" src="<?=pb_lang_pblang_url()?>"></script><?php
}

pb_hook_add_action('pb_head', '_pb_setup_pblang_script_to_head');
pb_hook_add_action('pb_admin_head', '_pb_setup_pblang_script_to_head');

pb_lang_load_translations(PBDOMAIN, PB_DOCUMENT_PATH . 'lang');

$current_locale_ = pb_current_locale();
if($current_locale_ === "C"){
	global $pb_config;
	pb_locale_update($pb_config->default_locale());	
}

?>
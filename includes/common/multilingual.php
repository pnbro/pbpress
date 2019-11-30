<?php

function pb_locale_update($locale_){
	global $pb_locale;
	$pb_locale = $locale_;

	putenv('LC_MESSAGES='.$locale_);
	setlocale(LC_MESSAGES,  $locale_);
	pb_hook_do_action('pb_locale_updated', $locale_);
}
function pb_current_locale(){
	global $pb_locale, $pb_config;
	if(!strlen($pb_locale)) return $pb_config->default_locale();
	return $pb_locale;
}

function pb_multilingual_load_translations($domain_, $translations_dir_path_){
	global $pb_config;

	bindtextdomain($domain_, $translations_dir_path_);
	bind_textdomain_codeset($domain_, $pb_config->charset);
	textdomain($domain_);

	pb_hook_do_action('pb_multilingual_loaded_translations', $domain_);
}

function pb_multilingual_load_theme_domain($domain_){
	$translations_dir_path_ = pb_current_theme_path()."translations";

	if(!is_dir($translations_dir_path_)) return false;

	pb_multilingual_load_translations($domain_, $translations_dir_path_);
}

function _pb_multilingual_initialize(){
	global $pb_config;

	if(!$pb_config->is_multilingual_theme()){
		return;
	}
	pb_locale_update(pb_current_locale());
	pb_hook_do_action('pb_multilingual_initialized');
	
}
pb_hook_add_action('pb_started', '_pb_multilingual_initialize');

function _pb_multilingual_load_translations_for_theme(){
	$theme_key_ = pb_current_theme();
	pb_multilingual_load_theme_domain($theme_key_);
}
pb_hook_add_action('pb_multilingual_initialized', '_pb_multilingual_load_translations_for_theme');


function __($text_, $domain_ = null){
	if(!strlen($domain_)){
		$domain_ = pb_current_theme();
	}
	return dgettext($domain_, $text_);
}
function _e($text_, $domain_ = null){
	if(!strlen($domain_)){
		$domain_ = pb_current_theme();
	}
	echo dgettext($domain_, $text_);
}

?>
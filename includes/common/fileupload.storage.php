<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_upload_path_is_absolute($path_){
	$path_ = (string)$path_;
	if(!strlen($path_)) return false;
	if($path_[0] === '/' || $path_[0] === '\\') return true;
	return preg_match('/\A[A-Za-z]:[\\\\\/]/D', $path_) === 1;
}

function pb_upload_path_is_same_or_inside($root_path_, $target_path_){
	$root_path_ = realpath($root_path_);
	$target_path_ = realpath($target_path_);
	if($root_path_ === false || $target_path_ === false) return false;

	$root_path_ = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $root_path_), DIRECTORY_SEPARATOR);
	$target_path_ = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $target_path_), DIRECTORY_SEPARATOR);
	if(DIRECTORY_SEPARATOR === '\\'){
		$root_path_ = strtolower($root_path_);
		$target_path_ = strtolower($target_path_);
	}

	return $target_path_ === $root_path_ || strpos($target_path_, $root_path_.DIRECTORY_SEPARATOR) === 0;
}

function pb_upload_path_string_is_same_or_inside($root_path_, $target_path_){
	$root_path_ = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $root_path_), DIRECTORY_SEPARATOR);
	$target_path_ = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $target_path_), DIRECTORY_SEPARATOR);
	if(DIRECTORY_SEPARATOR === '\\'){
		$root_path_ = strtolower($root_path_);
		$target_path_ = strtolower($target_path_);
	}

	return $target_path_ === $root_path_ || strpos($target_path_, $root_path_.DIRECTORY_SEPARATOR) === 0;
}

function pb_upload_resolve_candidate_path($target_path_){
	$target_path_ = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, (string)$target_path_);
	$missing_parts_ = array();
	$existing_path_ = $target_path_;

	while(!file_exists($existing_path_) && !is_link($existing_path_)){
		$parent_path_ = dirname($existing_path_);
		if($parent_path_ === $existing_path_) return false;
		array_unshift($missing_parts_, basename($existing_path_));
		$existing_path_ = $parent_path_;
	}

	$existing_path_ = realpath($existing_path_);
	if($existing_path_ === false) return false;
	foreach($missing_parts_ as $missing_part_){
		if(!strlen($missing_part_) || $missing_part_ === '.' || $missing_part_ === '..') return false;
		$existing_path_ .= DIRECTORY_SEPARATOR.$missing_part_;
	}

	return $existing_path_;
}

function pb_upload_configured_storage_path(){
	global $pb_config;
	$configured_path_ = isset($pb_config) && isset($pb_config->file_upload_storage_path) ? $pb_config->file_upload_storage_path : null;
	if(!isset($configured_path_) || !strlen(trim((string)$configured_path_))) return false;

	$configured_path_ = trim((string)$configured_path_);
	if(!strlen($configured_path_) || strpos($configured_path_, "\0") !== false || preg_match('/[\x00-\x1F\x7F]/', $configured_path_)) return false;
	if(preg_match('/\A[a-zA-Z][a-zA-Z0-9+.-]*:\/\//D', $configured_path_)) return false;

	if(!pb_upload_path_is_absolute($configured_path_)){
		$configured_path_ = rtrim(PB_DOCUMENT_PATH, '/\\').DIRECTORY_SEPARATOR.$configured_path_;
	}

	return pb_hook_apply_filters('pb_fileupload_storage_path', $configured_path_);
}

function pb_upload_root_path($create_ = true){
	$upload_path_ = pb_upload_configured_storage_path();
	if($upload_path_ === false) return false;
	$upload_path_ = pb_upload_resolve_candidate_path($upload_path_);
	if($upload_path_ === false) return false;

	$document_path_ = realpath(PB_DOCUMENT_PATH);
	$document_root_ = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
	if($document_path_ === false) return false;
	if(pb_upload_path_string_is_same_or_inside($document_path_, $upload_path_)) return false;
	if($document_root_ !== false && pb_upload_path_string_is_same_or_inside($document_root_, $upload_path_)) return false;

	if(!file_exists($upload_path_)){
		if(!$create_ || !@mkdir($upload_path_, 0750, true)) return false;
	}
	if(!is_dir($upload_path_) || is_link($upload_path_)) return false;

	$upload_path_ = realpath($upload_path_);
	if($upload_path_ === false || $document_path_ === false) return false;
	if(pb_upload_path_is_same_or_inside($document_path_, $upload_path_)) return false;
	if($document_root_ !== false && pb_upload_path_is_same_or_inside($document_root_, $upload_path_)) return false;

	return $upload_path_;
}

function pb_upload_legacy_root_path(){
	$legacy_path_ = realpath(rtrim(PB_DOCUMENT_PATH, '/\\').DIRECTORY_SEPARATOR.'uploads');
	if($legacy_path_ === false || !is_dir($legacy_path_) || is_link($legacy_path_)) return false;
	return pb_upload_path_is_same_or_inside(PB_DOCUMENT_PATH, $legacy_path_) ? $legacy_path_ : false;
}

function pb_upload_legacy_fallback_enabled(){
	global $pb_config;
	$enabled_ = isset($pb_config) && isset($pb_config->file_upload_legacy_fallback) ? $pb_config->file_upload_legacy_fallback : true;
	return pb_hook_apply_filters('pb_fileupload_legacy_fallback', $enabled_ === true);
}

function pb_upload_path_is_inside($upload_root_, $target_path_){
	$upload_root_ = realpath($upload_root_);
	if($upload_root_ === false) return false;

	if(file_exists($target_path_) || is_link($target_path_)){
		$target_path_ = realpath($target_path_);
		if($target_path_ === false) return false;
	}else{
		$parent_path_ = realpath(dirname($target_path_));
		if($parent_path_ === false) return false;
		$target_path_ = $parent_path_.DIRECTORY_SEPARATOR.basename($target_path_);
	}

	$upload_prefix_ = rtrim($upload_root_, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	return $target_path_ === $upload_root_ || strpos($target_path_, $upload_prefix_) === 0;
}

function pb_upload_directory($upload_root_, $relative_path_, $mode_ = 0750){
	$upload_root_ = realpath($upload_root_);
	if($upload_root_ === false) return false;

	$current_path_ = $upload_root_;
	$path_parts_ = strlen($relative_path_) ? explode('/', trim($relative_path_, '/')) : array();
	foreach($path_parts_ as $path_part_){
		if(!preg_match('/\A[a-zA-Z0-9_-]+\z/D', $path_part_)) return false;

		$next_path_ = $current_path_.DIRECTORY_SEPARATOR.$path_part_;
		if(!file_exists($next_path_) && !@mkdir($next_path_, $mode_)) return false;
		if(!is_dir($next_path_) || is_link($next_path_)) return false;

		$next_path_ = realpath($next_path_);
		if($next_path_ === false || !pb_upload_path_is_inside($upload_root_, $next_path_)) return false;
		$current_path_ = $next_path_;
	}

	return $current_path_;
}

function pb_filebase_normalize_relative_path($file_path_){
	if(!is_string($file_path_)) return false;
	$file_path_ = ltrim($file_path_, '/');
	if(!strlen($file_path_) || strpos($file_path_, "\0") !== false || strpos($file_path_, '\\') !== false) return false;
	if(preg_match('/[\x00-\x1F\x7F]/', $file_path_)) return false;

	$check_path_ = $file_path_;
	for($decode_index_ = 0; $decode_index_ < 3; $decode_index_++){
		$path_parts_ = explode('/', $check_path_);
		foreach($path_parts_ as $path_part_){
			if(!strlen($path_part_) || $path_part_ === '.' || $path_part_ === '..' || $path_part_[0] === '.') return false;
			$decoded_part_ = rawurldecode($path_part_);
			if(strpos($decoded_part_, '/') !== false || strpos($decoded_part_, '\\') !== false) return false;
		}
		if(strpos($check_path_, '\\') !== false || preg_match('/[\x00-\x1F\x7F]/', $check_path_)) return false;

		$decoded_path_ = rawurldecode($check_path_);
		if($decoded_path_ === $check_path_) break;
		$check_path_ = $decoded_path_;
	}

	$basename_ = basename($file_path_);
	if(preg_match('/(?:\.part[0-9]+|\.upload|\.assembling_[a-f0-9]+)\z/D', $basename_)) return false;
	if(strpos('/'.$file_path_.'/', '/_chunk_text_temp/') !== false || strpos('/'.$file_path_.'/', '/_temp/') !== false) return false;

	return $file_path_;
}

function pb_filebase_default_resolve_path($file_path_, $allow_legacy_ = true){
	$file_path_ = pb_filebase_normalize_relative_path($file_path_);
	if($file_path_ === false) return false;
	$native_path_ = str_replace('/', DIRECTORY_SEPARATOR, $file_path_);

	$upload_root_ = pb_upload_root_path(false);
	if($upload_root_ !== false){
		$target_path_ = $upload_root_.DIRECTORY_SEPARATOR.$native_path_;
		if(is_file($target_path_) && !is_link($target_path_) && pb_upload_path_is_inside($upload_root_, $target_path_)) return realpath($target_path_);
	}

	if($allow_legacy_ && pb_upload_legacy_fallback_enabled()){
		$legacy_root_ = pb_upload_legacy_root_path();
		$legacy_path_ = $legacy_root_ !== false ? $legacy_root_.DIRECTORY_SEPARATOR.$native_path_ : false;
		if($legacy_path_ !== false && is_file($legacy_path_) && !is_link($legacy_path_) && pb_upload_path_is_inside($legacy_root_, $legacy_path_)) return realpath($legacy_path_);
	}

	return false;
}

function _pb_filebase_default_read_handler($file_path_, $options_ = array()){
	$file_path_ = pb_filebase_default_resolve_path($file_path_, !isset($options_['allow_legacy']) || $options_['allow_legacy'] === true);
	if($file_path_ === false) return new PBError(404, __('파일없음'), __('파일을 찾을 수 없습니다.'));

	$contents_ = @file_get_contents($file_path_);
	return $contents_ === false ? new PBError(500, __('파일읽기실패'), __('파일을 읽을 수 없습니다.')) : $contents_;
}

function pb_filebase_read($file_path_, $options_ = array()){
	$file_path_ = pb_filebase_normalize_relative_path((string)$file_path_);
	if($file_path_ === false) return new PBError(400, __('잘못된 요청'), __('파일 경로가 올바르지 않습니다.'));

	$handler_ = pb_hook_apply_filters('pb_filebase_read_handler', '_pb_filebase_default_read_handler', $file_path_, $options_);
	if(!is_callable($handler_)) return new PBError(500, __('파일읽기실패'), __('파일 읽기 처리기가 올바르지 않습니다.'));
	return call_user_func($handler_, $file_path_, $options_);
}

?>

<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function _pb_filebase_request_context(){
	return array(
		'method' => isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET',
		'range' => isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null,
		'query' => isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null,
	);
}

function _pb_filebase_rewrite_handler($rewrite_path_, $rewrite_data_){
	if(!is_array($rewrite_path_) || count($rewrite_path_) <= 1){
		return new PBError(404, __('파일없음'), __('파일을 찾을 수 없습니다.'));
	}

	array_shift($rewrite_path_);
	$file_path_ = pb_filebase_normalize_relative_path(implode('/', $rewrite_path_));
	if($file_path_ === false){
		return new PBError(404, __('파일없음'), __('파일을 찾을 수 없습니다.'));
	}

	$context_ = _pb_filebase_request_context();
	if(!in_array($context_['method'], array('GET', 'HEAD'), true)){
		header('Allow: GET, HEAD');
		return new PBError(405, __('허용되지 않은 요청'), __('이 파일은 GET 또는 HEAD 방식으로만 요청할 수 있습니다.'));
	}

	$handler_ = pb_hook_apply_filters('pb_filebase_rewrite_handler', '_pb_filebase_default_rewrite_handler', $file_path_, $rewrite_data_, $context_);
	if(!is_callable($handler_)){
		pb_hook_do_action('pb_filebase_accessed', 'error', $file_path_, $context_);
		return new PBError(500, __('파일전달실패'), __('파일 전달 처리기가 올바르지 않습니다.'));
	}

	$can_access_ = pb_hook_apply_filters('pb_filebase_can_access', true, $file_path_, $handler_, $context_);
	if($can_access_ !== true){
		pb_hook_do_action('pb_filebase_accessed', 'denied', $file_path_, $context_);
		return new PBError(403, __('접근불가'), __('접근권한이 없습니다.'));
	}

	pb_hook_do_action('pb_filebase_accessed', 'allowed', $file_path_, $context_);
	return call_user_func($handler_, $file_path_, $rewrite_data_, $context_);
}

function _pb_filebase_detect_mime_type($file_path_){
	$mime_type_ = false;
	if(function_exists('finfo_open')){
		$finfo_ = @finfo_open(FILEINFO_MIME_TYPE);
		if($finfo_ !== false){
			$mime_type_ = @finfo_file($finfo_, $file_path_);
			finfo_close($finfo_);
		}
	}
	if($mime_type_ === false && function_exists('mime_content_type')) $mime_type_ = @mime_content_type($file_path_);
	return is_string($mime_type_) && strlen($mime_type_) ? $mime_type_ : 'application/octet-stream';
}

function _pb_filebase_inline_mime_types(){
	return pb_hook_apply_filters('pb_filebase_inline_mime_types', array(
		'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif',
		'video/mp4', 'video/webm', 'video/ogg',
		'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/webm',
	));
}

function _pb_filebase_parse_range($range_header_, $file_size_){
	if(!is_string($range_header_) || !strlen($range_header_)) return null;
	if($file_size_ <= 0) return false;
	if(strpos($range_header_, ',') !== false || preg_match('/\Abytes=([0-9]*)-([0-9]*)\z/D', trim($range_header_), $matches_) !== 1) return false;

	$start_raw_ = $matches_[1];
	$end_raw_ = $matches_[2];
	if(!strlen($start_raw_) && !strlen($end_raw_)) return false;

	if(!strlen($start_raw_)){
		$suffix_length_ = (int)$end_raw_;
		if($suffix_length_ <= 0) return false;
		$suffix_length_ = min($suffix_length_, $file_size_);
		$start_ = $file_size_ - $suffix_length_;
		$end_ = $file_size_ - 1;
	}else{
		$start_ = (int)$start_raw_;
		$end_ = strlen($end_raw_) ? (int)$end_raw_ : $file_size_ - 1;
		if($start_ < 0 || $start_ >= $file_size_ || $end_ < $start_) return false;
		$end_ = min($end_, $file_size_ - 1);
	}

	return array('start' => $start_, 'end' => $end_, 'length' => $end_ - $start_ + 1);
}

function _pb_filebase_send_headers($headers_){
	foreach($headers_ as $header_name_ => $header_value_){
		if(!preg_match('/\A[A-Za-z0-9-]+\z/D', $header_name_)) continue;
		if(!is_scalar($header_value_) || preg_match('/[\r\n]/', (string)$header_value_)) continue;
		header($header_name_.': '.$header_value_, true);
	}
}

function _pb_filebase_default_stream_handler($file_path_, $start_, $length_, $context_){
	$file_instance_ = @fopen($file_path_, 'rb');
	if($file_instance_ === false) return false;
	if($start_ > 0 && fseek($file_instance_, $start_) !== 0){
		fclose($file_instance_);
		return false;
	}

	$remaining_ = $length_;
	while($remaining_ > 0 && !feof($file_instance_)){
		$read_length_ = min(65536, $remaining_);
		$buffer_ = fread($file_instance_, $read_length_);
		if($buffer_ === false) break;
		$buffer_length_ = strlen($buffer_);
		if($buffer_length_ <= 0) break;
		echo $buffer_;
		$remaining_ -= $buffer_length_;
		if(function_exists('fastcgi_finish_request')){
			// Keep buffering behavior under FastCGI; completion is handled after the loop.
		}else{
			flush();
		}
		if(connection_aborted()) break;
	}

	fclose($file_instance_);
	return $remaining_ === 0;
}

function _pb_filebase_default_rewrite_handler($relative_path_, $rewrite_data_, $context_){
	$file_path_ = pb_filebase_default_resolve_path($relative_path_, true);
	if($file_path_ === false){
		pb_hook_do_action('pb_filebase_accessed', 'not_found', $relative_path_, $context_);
		return new PBError(404, __('파일없음'), __('파일을 찾을 수 없습니다.'));
	}

	$file_size_ = @filesize($file_path_);
	$file_mtime_ = @filemtime($file_path_);
	if($file_size_ === false || $file_mtime_ === false){
		pb_hook_do_action('pb_filebase_accessed', 'error', $relative_path_, $context_);
		return new PBError(500, __('파일전달실패'), __('파일 정보를 읽을 수 없습니다.'));
	}

	$mime_type_ = _pb_filebase_detect_mime_type($file_path_);
	$disposition_ = in_array($mime_type_, _pb_filebase_inline_mime_types(), true) ? 'inline' : 'attachment';
	$file_name_ = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($relative_path_));
	$etag_ = '"'.hash('sha256', $file_size_.':'.$file_mtime_.':'.$relative_path_).'"';
	$last_modified_ = gmdate('D, d M Y H:i:s', $file_mtime_).' GMT';

	$headers_ = array(
		'Content-Type' => $mime_type_,
		'Content-Disposition' => $disposition_.'; filename="'.$file_name_.'"',
		'X-Content-Type-Options' => 'nosniff',
		'Accept-Ranges' => 'bytes',
		'ETag' => $etag_,
		'Last-Modified' => $last_modified_,
		'Cache-Control' => 'public, max-age=31536000, immutable',
	);
	$file_info_ = array('path' => $file_path_, 'size' => $file_size_, 'mtime' => $file_mtime_, 'mime_type' => $mime_type_, 'disposition' => $disposition_);
	$headers_ = pb_hook_apply_filters('pb_filebase_response_headers', $headers_, $relative_path_, $file_info_, $context_);
	if(!is_array($headers_)) $headers_ = array();

	$if_none_match_ = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : null;
	$if_modified_since_ = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;
	if($if_none_match_ === $etag_ || ($if_none_match_ === null && $if_modified_since_ !== false && $if_modified_since_ >= $file_mtime_)){
		header_remove('Expires');
		header_remove('Pragma');
		header_remove('Cache-Control');
		if(isset($headers_['Cache-Control']) && strpos($headers_['Cache-Control'], 'public') !== false) header_remove('Set-Cookie');
		if(function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) session_write_close();
		http_response_code(304);
		_pb_filebase_send_headers($headers_);
		pb_hook_do_action('pb_filebase_accessed', 'completed', $relative_path_, array_merge($context_, array('status' => 304, 'bytes' => 0)));
		pb_end();
	}

	$range_header_ = $context_['range'];
	$if_range_ = isset($_SERVER['HTTP_IF_RANGE']) ? trim($_SERVER['HTTP_IF_RANGE']) : null;
	if(isset($range_header_) && isset($if_range_)){
		$if_range_time_ = strtotime($if_range_);
		if($if_range_ !== $etag_ && ($if_range_time_ === false || $if_range_time_ < $file_mtime_)) $range_header_ = null;
	}
	$range_ = _pb_filebase_parse_range($range_header_, $file_size_);
	if($range_ === false){
		header('Content-Range: bytes */'.$file_size_);
		return new PBError(416, __('잘못된 범위'), __('요청한 파일 범위가 올바르지 않습니다.'));
	}

	$start_ = $range_ === null ? 0 : $range_['start'];
	$length_ = $range_ === null ? $file_size_ : $range_['length'];
	$status_ = $range_ === null ? 200 : 206;
	if($range_ !== null) $headers_['Content-Range'] = 'bytes '.$range_['start'].'-'.$range_['end'].'/'.$file_size_;
	$headers_['Content-Length'] = $length_;

	header_remove('Expires');
	header_remove('Pragma');
	header_remove('Cache-Control');
	if(isset($headers_['Cache-Control']) && strpos($headers_['Cache-Control'], 'public') !== false) header_remove('Set-Cookie');
	http_response_code($status_);
	_pb_filebase_send_headers($headers_);

	if($context_['method'] === 'HEAD'){
		pb_hook_do_action('pb_filebase_accessed', 'completed', $relative_path_, array_merge($context_, array('status' => $status_, 'bytes' => 0)));
		pb_end();
	}

	if(function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) session_write_close();
	while(ob_get_level() > 0) @ob_end_clean();
	@set_time_limit(0);

	$stream_handler_ = pb_hook_apply_filters('pb_filebase_stream_handler', '_pb_filebase_default_stream_handler', $relative_path_, $file_info_, $context_);
	$stream_result_ = is_callable($stream_handler_) ? call_user_func($stream_handler_, $file_path_, $start_, $length_, $context_) : false;
	if($stream_result_ !== true){
		pb_hook_do_action('pb_filebase_accessed', 'error', $relative_path_, array_merge($context_, array('status' => $status_)));
		pb_end();
	}

	pb_hook_do_action('pb_filebase_accessed', 'completed', $relative_path_, array_merge($context_, array('status' => $status_, 'bytes' => $length_)));
	pb_end();
}

?>

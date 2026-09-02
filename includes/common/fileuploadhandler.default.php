<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

if(!function_exists('pb_upload_denied_extensions')){
	function pb_upload_denied_extensions(){
		return pb_hook_apply_filters('pb_upload_denied_extensions', array(
			'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 'phps', 'pht', 'phtm', 'phtml', 'phar',
			'inc', 'cgi', 'pl', 'py', 'rb', 'sh', 'bash', 'zsh', 'ksh', 'csh', 'fish', 'jsp', 'jspx', 'asp', 'aspx', 'asa', 'asax', 'cer', 'shtml',
			'js', 'mjs', 'cjs', 'vbs', 'vbe', 'wsf', 'ps1', 'jar', 'war', 'exe', 'com', 'bat', 'cmd', 'msi', 'msp', 'scr', 'dll', 'so', 'dylib',
			'htaccess', 'htpasswd',
		));
	}
}

if(!function_exists('pb_upload_decoded_name_variants')){
	function pb_upload_decoded_name_variants($name_){
		$name_ = (string)$name_;
		$results_ = array($name_);

		for($decode_index_ = 0; $decode_index_ < 3; $decode_index_++){
			$decoded_name_ = rawurldecode($name_);
			if($decoded_name_ === $name_) break;
			$results_[] = $decoded_name_;
			$name_ = $decoded_name_;
		}

		return array_values(array_unique($results_));
	}
}

if(!function_exists('pb_upload_has_unsafe_path_name')){
	function pb_upload_has_unsafe_path_name($name_){
		$name_ = (string)$name_;
		if(!strlen($name_)) return false;

		foreach(pb_upload_decoded_name_variants($name_) as $check_name_){
			if(basename($check_name_) !== $check_name_) return true;
			if(strpos($check_name_, '/') !== false || strpos($check_name_, '\\') !== false || strpos($check_name_, '..') !== false) return true;
			if(strpos($check_name_, "\0") !== false || preg_match('/[\x00-\x1F\x7F]/', $check_name_)) return true;
			if(preg_match('~^(?:[A-Za-z]:|[\\\\/]{1,2})~', $check_name_)) return true;
		}

		return false;
	}
}

if(!function_exists('pb_upload_sanitize_name')){
	function pb_upload_sanitize_name($name_){
		$name_ = (string)$name_;
		if(!strlen($name_)) return $name_;

		$name_variants_ = pb_upload_decoded_name_variants($name_);
		$name_ = end($name_variants_);
		$name_ = str_replace(array("\0", "\\"), array('', '/'), $name_);
		$name_ = basename($name_);
		$name_ = preg_replace('/[^\p{L}\p{N}._\-\(\)\[\] ]/u', '_', $name_);
		if(!is_string($name_)) return '';

		return trim(ltrim($name_, '.'), " .\t\n\r\0\x0B");
	}
}

if(!function_exists('pb_upload_is_denied_name')){
	function pb_upload_is_denied_name($name_){
		$denied_extensions_ = (array)pb_upload_denied_extensions();
		$denied_extensions_ = array_map('strtolower', $denied_extensions_);

		foreach(pb_upload_decoded_name_variants($name_) as $check_name_){
			$check_name_ = strtolower(trim((string)$check_name_, " .\t\n\r\0\x0B"));
			if(!strlen($check_name_)) continue;

			$check_name_ = preg_replace('/[^a-z0-9]+/', '.', $check_name_);
			foreach(explode('.', $check_name_) as $name_part_){
				if(strlen($name_part_) && in_array($name_part_, $denied_extensions_, true)) return true;
			}
		}

		return false;
	}
}

if(!function_exists('pb_upload_safe_extension')){
	function pb_upload_safe_extension($name_){
		$extension_ = strtolower((string)pathinfo((string)$name_, PATHINFO_EXTENSION));
		if(!strlen($extension_)) return '';
		if(!preg_match('/\A[a-z0-9]{1,20}\z/D', $extension_)) return false;

		return $extension_;
	}
}

if(!function_exists('pb_upload_validate_file_name')){
	function pb_upload_validate_file_name($name_){
		if(!is_string($name_) || !strlen($name_)) return false;
		if(pb_upload_has_unsafe_path_name($name_) || pb_upload_is_denied_name($name_)) return false;

		$sanitized_name_ = pb_upload_sanitize_name($name_);
		if(!strlen($sanitized_name_) || pb_upload_is_denied_name($sanitized_name_)) return false;
		if(pb_upload_safe_extension($sanitized_name_) === false) return false;

		return $sanitized_name_;
	}
}

if(!function_exists('pb_upload_generate_server_name')){
	function pb_upload_generate_server_name($extension_ = ''){
		try{
			$random_name_ = bin2hex(random_bytes(16));
		}catch(Exception $exception_){
			return false;
		}

		return 'pbup_'.date('Ymd').'_'.$random_name_.(strlen($extension_) ? '.'.$extension_ : '');
	}
}

if(!function_exists('pb_upload_is_server_name')){
	function pb_upload_is_server_name($name_){
		if(!is_string($name_) || !strlen($name_)) return false;
		if(basename($name_) !== $name_ || pb_upload_has_unsafe_path_name($name_) || pb_upload_is_denied_name($name_)) return false;

		if(preg_match('/\Apbup_([0-9]{4})([0-9]{2})([0-9]{2})_[a-f0-9]{32}(?:\.[a-z0-9]{1,20})?\z/D', $name_, $matches_) !== 1) return false;

		return checkdate((int)$matches_[2], (int)$matches_[3], (int)$matches_[1]);
	}
}

if(!function_exists('pb_upload_integer')){
	function pb_upload_integer($value_, $minimum_, $maximum_){
		if(is_int($value_)) $value_ = (string)$value_;
		if(!is_string($value_) || !preg_match('/\A(?:0|[1-9][0-9]*)\z/D', $value_)) return false;

		$value_ = (int)$value_;
		if($value_ < $minimum_ || $value_ > $maximum_) return false;

		return $value_;
	}
}

if(!function_exists('pb_upload_max_chunk_count')){
	function pb_upload_max_chunk_count(){
		$max_count_ = defined('PB_UPLOAD_MAX_CHUNK_COUNT') ? (int)PB_UPLOAD_MAX_CHUNK_COUNT : 10000;
		return max(1, (int)pb_hook_apply_filters('pb_upload_max_chunk_count', $max_count_));
	}
}

if(!function_exists('pb_upload_max_chunk_size')){
	function pb_upload_max_chunk_size(){
		global $pb_config;
		$default_size_ = isset($pb_config) && isset($pb_config->file_chunksize) ? (int)$pb_config->file_chunksize : (1024 * 1024);
		$max_size_ = defined('PB_UPLOAD_MAX_CHUNK_SIZE') ? (int)PB_UPLOAD_MAX_CHUNK_SIZE : $default_size_;
		return max(1, (int)pb_hook_apply_filters('pb_upload_max_chunk_size', $max_size_));
	}
}

if(!function_exists('pb_upload_unlink_inside')){
	function pb_upload_unlink_inside($upload_root_, $target_path_){
		if((file_exists($target_path_) || is_link($target_path_)) && pb_upload_path_is_inside($upload_root_, $target_path_)){
			return @unlink($target_path_);
		}

		return true;
	}
}

if(!function_exists('pb_upload_store_exclusive')){
	function pb_upload_store_exclusive($source_path_, $target_path_, $upload_root_, $expected_size_){
		if(!is_uploaded_file($source_path_) || !pb_upload_path_is_inside($upload_root_, $target_path_)) return false;
		if(file_exists($target_path_) || is_link($target_path_)) return false;

		$source_instance_ = @fopen($source_path_, 'rb');
		$target_instance_ = @fopen($target_path_, 'x+b');
		if($source_instance_ === false || $target_instance_ === false){
			if(is_resource($source_instance_)) fclose($source_instance_);
			if(is_resource($target_instance_)){
				fclose($target_instance_);
				pb_upload_unlink_inside($upload_root_, $target_path_);
			}
			return false;
		}

		$written_size_ = stream_copy_to_stream($source_instance_, $target_instance_);
		fflush($target_instance_);
		fclose($source_instance_);
		fclose($target_instance_);

		if($written_size_ === false || (int)$written_size_ !== (int)$expected_size_){
			pb_upload_unlink_inside($upload_root_, $target_path_);
			return false;
		}

		return true;
	}
}

if(!function_exists('pb_upload_cleanup_chunks')){
	function pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_, $assembly_path_ = null){
		if(!pb_upload_is_server_name($r_name_) || !pb_upload_path_is_inside($upload_root_, $upload_directory_)) return;

		for($chunk_index_ = 0; $chunk_index_ < $chunk_length_; $chunk_index_++){
			pb_upload_unlink_inside($upload_root_, $upload_directory_.DIRECTORY_SEPARATOR.$r_name_.'.part'.$chunk_index_);
		}
		$discovered_parts_ = glob($upload_directory_.DIRECTORY_SEPARATOR.$r_name_.'.part*');
		if(is_array($discovered_parts_)){
			foreach($discovered_parts_ as $discovered_part_){
				if(preg_match('/\A'.preg_quote($r_name_, '/').'\.part[0-9]+\z/D', basename($discovered_part_))){
					pb_upload_unlink_inside($upload_root_, $discovered_part_);
				}
			}
		}

		pb_upload_unlink_inside($upload_root_, $upload_directory_.DIRECTORY_SEPARATOR.$r_name_.'.upload');
		if(isset($assembly_path_)) pb_upload_unlink_inside($upload_root_, $assembly_path_);
	}
}

if(!function_exists('pb_upload_cleanup_server_upload')){
	function pb_upload_cleanup_server_upload($r_name_){
		if(!pb_upload_is_server_name($r_name_)) return;

		$upload_root_ = pb_upload_root_path();
		if($upload_root_ === false) return;

		$upload_directory_ = $upload_root_.DIRECTORY_SEPARATOR.substr($r_name_, 5, 8);
		if(!is_dir($upload_directory_) || is_link($upload_directory_) || !pb_upload_path_is_inside($upload_root_, $upload_directory_)) return;

		pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, 0);
	}
}

class PBPressFileUPloadDefaultHandler extends PBPressFileUPloadHandler{
	function supports_chunk_upload(){
		return true;
	}

	function initialize(){
		$upload_root_ = pb_upload_root_path();
		if($upload_root_ === false){
			return new PBError(-1, __("에러발생"), __("업로드 경로가 올바르지 않습니다."));
		}

		//prevent injection
		$rewrite_path_ = $upload_root_.DIRECTORY_SEPARATOR.'.htaccess';
		if(!file_exists($rewrite_path_)){
			$rewrite_file_ = @fopen($rewrite_path_, "x+b");

			if($rewrite_file_ === false){
				return new PBError(-1, __("에러발생"), __("Rewrite를 생성할 수 없습니다. 파일권한을 확인하세요."));
			}

			fwrite($rewrite_file_, "RemoveType .phtml .php3 .htm .html .php .asp .jsp\n\rRemoveHandler .phtml .php3 .htm .html .php .asp .jsp");
			fclose($rewrite_file_);
		}

		return true;
	}
	function filebase_url($file_path_ = null, $params_ = array()){
		return pb_make_url(pb_home_url("uploads/".$file_path_), $params_);
	}
	
	function handle($files_, $options_ = array()){
		$upload_dir_ = isset($options_['upload_dir']) ? (string)$options_['upload_dir'] : '';
		if(strlen($upload_dir_) && !preg_match('/\A[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*\z/D', $upload_dir_)){
			return new PBError(-400, __("업로드실패"), __("업로드 경로가 올바르지 않습니다."));
		}

		$upload_root_ = pb_upload_root_path();
		$yyymmdd_ = date('Ymd');
		$relative_directory_ = strlen($upload_dir_) ? $upload_dir_.'/'.$yyymmdd_ : $yyymmdd_;
		$upload_directory_ = $upload_root_ !== false ? pb_upload_directory($upload_root_, $relative_directory_) : false;
		if($upload_root_ === false || $upload_directory_ === false){
			return new PBError(-500, __("업로드실패"), __("업로드 경로를 준비할 수 없습니다."));
		}

		if(!isset($files_['name'])){
			return new PBError(-400, __("업로드실패"), __("업로드 파일이 올바르지 않습니다."));
		}
		if(!is_array($files_['name'])){
			foreach(array('name', 'type', 'tmp_name', 'error', 'size') as $file_key_){
				$files_[$file_key_] = array(isset($files_[$file_key_]) ? $files_[$file_key_] : null);
			}
		}

		$total_file_count_ = count($files_['name']);

		$results_ = array();

		for($file_index_= 0; $file_index_ < $total_file_count_; ++$file_index_){
			$original_file_name_ = pb_upload_validate_file_name($files_['name'][$file_index_]);
			if($original_file_name_ === false){
				return new PBError(-403, __("업로드실패"), __("업로드할 수 없는 파일명 또는 파일형식입니다."));
			}

			$original_file_path_ = isset($files_['tmp_name'][$file_index_]) ? $files_['tmp_name'][$file_index_] : null;
			$file_error_ = isset($files_['error'][$file_index_]) ? $files_['error'][$file_index_] : UPLOAD_ERR_NO_FILE;

			switch($file_error_){
				case UPLOAD_ERR_INI_SIZE : 
				case UPLOAD_ERR_FORM_SIZE : 
					return new PBError($file_error_, __("업로드실패"), __("파일 사이즈가 너무 큽니다."));

				break;
				case UPLOAD_ERR_PARTIAL : 
					return new PBError($file_error_, __("업로드실패"), __("파일의 일부만 업로드 되었습니다."));
					
				break;

				case UPLOAD_ERR_NO_FILE : 
					return new PBError($file_error_, __("업로드실패"), __("업로드할 파일이 비어있습니다."));
				
				break;

				case UPLOAD_ERR_CANT_WRITE :
					return new PBError($file_error_, __("업로드실패"), __("파일을 쓸 수 있는 권한이 없습니다."));

				break;	

				case UPLOAD_ERR_NO_TMP_DIR : 
				case UPLOAD_ERR_EXTENSION :
					return new PBError($file_error_, __("업로드실패"), __("파일 업로드가 불가한 환경입니다."));
				break;
			}

			if($file_error_ !== UPLOAD_ERR_OK || !is_uploaded_file($original_file_path_)){
				return new PBError(-400, __("업로드실패"), __("업로드 파일이 올바르지 않습니다."));
			}

			$file_extension_ = pb_upload_safe_extension($original_file_name_);
			$renamed_file_name_ = false;
			$final_file_path_ = false;
			for($name_attempt_ = 0; $name_attempt_ < 100; $name_attempt_++){
				$renamed_file_name_ = pb_upload_generate_server_name($file_extension_);
				if($renamed_file_name_ === false) break;
				$final_file_path_ = $upload_directory_.DIRECTORY_SEPARATOR.$renamed_file_name_;
				if(!file_exists($final_file_path_) && !is_link($final_file_path_)) break;
			}

			if($renamed_file_name_ === false || $final_file_path_ === false || file_exists($final_file_path_) || is_link($final_file_path_)){
				return new PBError(-500, __("업로드실패"), __("안전한 저장명을 만들 수 없습니다."));
			}

			$actual_file_size_ = filesize($original_file_path_);
			if($actual_file_size_ === false || !pb_upload_store_exclusive($original_file_path_, $final_file_path_, $upload_root_, $actual_file_size_)){
				return new PBError(-500, __("업로드실패"), __("파일을 안전하게 저장할 수 없습니다."));
			}

			$post_process_result_ = $this->post_process(array(
				'size' => $actual_file_size_,
				'type' => mime_content_type($final_file_path_),

				'o_name' => $original_file_name_,
				'r_name' => $relative_directory_.'/'.$renamed_file_name_,

			));
			if($post_process_result_ instanceof PBError){
				pb_upload_unlink_inside($upload_root_, $final_file_path_);
				return $post_process_result_;
			}
			$results_[] = $post_process_result_;
		}

		return $results_;
	}

	function handle_chunk($chunk_, $data_, $options_ = array()){
		$r_name_ = isset($data_['r_name']) ? (string)$data_['r_name'] : '';
		$chunk_error_ = isset($chunk_['error']) ? $chunk_['error'] : UPLOAD_ERR_NO_FILE;
		switch($chunk_error_){
			case UPLOAD_ERR_INI_SIZE : 
			case UPLOAD_ERR_FORM_SIZE : 
				pb_upload_cleanup_server_upload($r_name_);
				return new PBError($chunk_error_, __("업로드실패"), __("파일 사이즈가 너무 큽니다."));

			break;
			case UPLOAD_ERR_PARTIAL : 
				pb_upload_cleanup_server_upload($r_name_);
				return new PBError($chunk_error_, __("업로드실패"), __("파일의 일부만 업로드 되었습니다."));
				
			break;

			case UPLOAD_ERR_NO_FILE : 
				pb_upload_cleanup_server_upload($r_name_);
				return new PBError($chunk_error_, __("업로드실패"), __("업로드할 파일이 비어있습니다."));
			
			break;

			case UPLOAD_ERR_CANT_WRITE :
				pb_upload_cleanup_server_upload($r_name_);
				return new PBError($chunk_error_, __("업로드실패"), __("파일을 쓸 수 있는 권한이 없습니다."));

			break;	

			case UPLOAD_ERR_NO_TMP_DIR : 
			case UPLOAD_ERR_EXTENSION :
				pb_upload_cleanup_server_upload($r_name_);
				return new PBError($chunk_error_, __("업로드실패"), __("파일 업로드가 불가한 환경입니다."));
			break;
		}

		$max_chunk_count_ = pb_upload_max_chunk_count();
		$max_chunk_size_ = pb_upload_max_chunk_size();
		$chunk_current_ = pb_upload_integer(isset($data_['chunk_current']) ? $data_['chunk_current'] : null, 0, $max_chunk_count_ - 1);
		$chunk_length_ = pb_upload_integer(isset($data_['chunk_length']) ? $data_['chunk_length'] : null, 1, $max_chunk_count_);
		$chunk_size_ = pb_upload_integer(isset($data_['chunk_size']) ? $data_['chunk_size'] : null, 1, $max_chunk_size_);
		if($chunk_current_ === false || $chunk_length_ === false || $chunk_size_ === false || $chunk_current_ >= $chunk_length_){
			pb_upload_cleanup_server_upload($r_name_);
			return new PBError(-400, __("업로드실패"), __("청크 정보가 올바르지 않습니다."));
		}

		if($chunk_error_ !== UPLOAD_ERR_OK || !isset($chunk_['tmp_name']) || !is_uploaded_file($chunk_['tmp_name'])){
			pb_upload_cleanup_server_upload($r_name_);
			return new PBError(-400, __("업로드실패"), __("업로드 청크가 올바르지 않습니다."));
		}

		$actual_chunk_size_ = filesize($chunk_['tmp_name']);
		$chunk_size_is_valid_ = !($actual_chunk_size_ === false || $actual_chunk_size_ < 1 || $actual_chunk_size_ > $chunk_size_ || $actual_chunk_size_ > $max_chunk_size_);
		if($chunk_current_ < $chunk_length_ - 1 && $actual_chunk_size_ !== $chunk_size_) $chunk_size_is_valid_ = false;

		$o_name_ = isset($data_['o_name']) ? $data_['o_name'] : null;
		$upload_root_ = pb_upload_root_path();
		if($upload_root_ === false){
			return new PBError(-500, __("업로드실패"), __("업로드 경로를 준비할 수 없습니다."));
		}

		if($chunk_current_ === 0){
			if(strlen($r_name_)){
				return new PBError(-403, __("업로드실패"), __("첫 청크에는 저장명을 지정할 수 없습니다."));
			}

			$o_name_ = pb_upload_validate_file_name($o_name_);
			if($o_name_ === false){
				return new PBError(-403, __("업로드실패"), __("업로드할 수 없는 파일명 또는 파일형식입니다."));
			}

			$file_extension_ = pb_upload_safe_extension($o_name_);
			$r_name_ = pb_upload_generate_server_name($file_extension_);
			if($r_name_ === false){
				return new PBError(-500, __("업로드실패"), __("안전한 저장명을 만들 수 없습니다."));
			}
		}else if(!pb_upload_is_server_name($r_name_)){
			return new PBError(-403, __("업로드실패"), __("서버가 발급한 저장명이 아닙니다."));
		}

		$yyymmdd_ = substr($r_name_, 5, 8);
		if($chunk_current_ === 0){
			$upload_directory_ = pb_upload_directory($upload_root_, $yyymmdd_);
		}else{
			$upload_directory_ = $upload_root_.DIRECTORY_SEPARATOR.$yyymmdd_;
			if(!is_dir($upload_directory_) || is_link($upload_directory_) || !pb_upload_path_is_inside($upload_root_, $upload_directory_)){
				return new PBError(-403, __("업로드실패"), __("앞선 청크가 존재하지 않습니다."));
			}
		}
		if($upload_directory_ === false){
			return new PBError(-500, __("업로드실패"), __("업로드 경로를 준비할 수 없습니다."));
		}

		$manifest_path_ = $upload_directory_.DIRECTORY_SEPARATOR.$r_name_.'.upload';
		$final_file_path_ = $upload_directory_.DIRECTORY_SEPARATOR.$r_name_;
		$chunk_file_path_ = $upload_directory_.DIRECTORY_SEPARATOR.$r_name_.'.part'.$chunk_current_;
		if(!pb_upload_path_is_inside($upload_root_, $manifest_path_) || !pb_upload_path_is_inside($upload_root_, $final_file_path_) || !pb_upload_path_is_inside($upload_root_, $chunk_file_path_)){
			return new PBError(-403, __("업로드실패"), __("업로드 경로가 올바르지 않습니다."));
		}

		if($chunk_current_ === 0){
			$manifest_instance_ = @fopen($manifest_path_, 'x+b');
			if($manifest_instance_ === false){
				return new PBError(-409, __("업로드실패"), __("이미 사용 중인 저장명입니다."));
			}
			$manifest_data_ = json_encode(array(
				'chunk_length' => $chunk_length_,
				'chunk_size' => $chunk_size_,
				'o_name' => $o_name_,
			));
			$manifest_written_ = fwrite($manifest_instance_, $manifest_data_);
			fclose($manifest_instance_);
			if($manifest_written_ !== strlen($manifest_data_)){
				pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
				return new PBError(-500, __("업로드실패"), __("업로드 상태를 저장할 수 없습니다."));
			}
		}else{
			if(!is_file($manifest_path_) || is_link($manifest_path_)){
				return new PBError(-403, __("업로드실패"), __("앞선 청크가 존재하지 않습니다."));
			}

			$manifest_data_ = json_decode(file_get_contents($manifest_path_), true);
			if(!is_array($manifest_data_) || (int)@$manifest_data_['chunk_length'] !== $chunk_length_ || (int)@$manifest_data_['chunk_size'] !== $chunk_size_){
				pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
				return new PBError(-400, __("업로드실패"), __("청크 정보가 첫 요청과 일치하지 않습니다."));
			}

			for($previous_index_ = 0; $previous_index_ < $chunk_current_; $previous_index_++){
				$previous_path_ = $upload_directory_.DIRECTORY_SEPARATOR.$r_name_.'.part'.$previous_index_;
				if(!is_file($previous_path_) || is_link($previous_path_) || !pb_upload_path_is_inside($upload_root_, $previous_path_)){
					pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
					return new PBError(-409, __("업로드실패"), __("청크 순서가 올바르지 않습니다."));
				}
			}

			if(file_exists($final_file_path_) || is_link($final_file_path_)){
				pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
				return new PBError(-409, __("업로드실패"), __("같은 저장명의 파일이 이미 존재합니다."));
			}
		}

		if(!$chunk_size_is_valid_){
			pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
			return new PBError(-400, __("업로드실패"), __("청크 크기가 올바르지 않습니다."));
		}

		if(file_exists($chunk_file_path_) || is_link($chunk_file_path_) || !pb_upload_store_exclusive($chunk_['tmp_name'], $chunk_file_path_, $upload_root_, $actual_chunk_size_)){
			pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
			return new PBError(-409, __("업로드실패"), __("청크를 안전하게 저장할 수 없습니다."));
		}

		if($chunk_current_ < $chunk_length_ - 1) return array('r_name' => $r_name_);

		if(file_exists($final_file_path_) || is_link($final_file_path_)){
			pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
			return new PBError(-409, __("업로드실패"), __("같은 저장명의 파일이 이미 존재합니다."));
		}

		try{
			$assembly_token_ = bin2hex(random_bytes(8));
		}catch(Exception $exception_){
			pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
			return new PBError(-500, __("업로드실패"), __("파일을 조립할 수 없습니다."));
		}
		$assembly_name_ = $r_name_.'.assembling_'.$assembly_token_;
		$assembly_path_ = $upload_directory_.DIRECTORY_SEPARATOR.$assembly_name_;
		if(!pb_upload_path_is_inside($upload_root_, $assembly_path_)){
			pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
			return new PBError(-403, __("업로드실패"), __("업로드 경로가 올바르지 않습니다."));
		}

		$assembly_instance_ = @fopen($assembly_path_, 'x+b');
		if($assembly_instance_ === false){
			pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_);
			return new PBError(-500, __("업로드실패"), __("파일을 조립할 수 없습니다."));
		}

		$assembled_size_ = 0;
		$assembly_failed_ = false;
		for($chunk_index_ = 0; $chunk_index_ < $chunk_length_; $chunk_index_++){
			$part_path_ = $upload_directory_.DIRECTORY_SEPARATOR.$r_name_.'.part'.$chunk_index_;
			$part_size_ = is_file($part_path_) && !is_link($part_path_) ? filesize($part_path_) : false;
			if($part_size_ === false || $part_size_ < 1 || $part_size_ > $chunk_size_ || ($chunk_index_ < $chunk_length_ - 1 && $part_size_ !== $chunk_size_)){
				$assembly_failed_ = true;
				break;
			}

			$part_instance_ = @fopen($part_path_, 'rb');
			$copied_size_ = $part_instance_ !== false ? stream_copy_to_stream($part_instance_, $assembly_instance_) : false;
			if(is_resource($part_instance_)) fclose($part_instance_);
			if($copied_size_ === false || (int)$copied_size_ !== (int)$part_size_){
				$assembly_failed_ = true;
				break;
			}
			$assembled_size_ += $copied_size_;
		}
		fflush($assembly_instance_);
		fclose($assembly_instance_);

		if($assembly_failed_ || $assembled_size_ < 1){
			pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_, $assembly_path_);
			return new PBError(-500, __("업로드실패"), __("파일을 조립할 수 없습니다."));
		}

		$published_ = @link($assembly_path_, $final_file_path_);
		if(!$published_ && !file_exists($final_file_path_) && !is_link($final_file_path_)){
			$assembly_read_instance_ = @fopen($assembly_path_, 'rb');
			$final_instance_ = @fopen($final_file_path_, 'x+b');
			if($assembly_read_instance_ !== false && $final_instance_ !== false){
				$published_size_ = stream_copy_to_stream($assembly_read_instance_, $final_instance_);
				fflush($final_instance_);
				$published_ = $published_size_ !== false && (int)$published_size_ === (int)$assembled_size_;
			}
			if(is_resource($assembly_read_instance_)) fclose($assembly_read_instance_);
			if(is_resource($final_instance_)) fclose($final_instance_);
			if(!$published_) pb_upload_unlink_inside($upload_root_, $final_file_path_);
		}

		if(!$published_){
			pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_, $assembly_path_);
			return new PBError(-409, __("업로드실패"), __("같은 저장명의 파일이 이미 존재합니다."));
		}

		pb_upload_cleanup_chunks($upload_root_, $upload_directory_, $r_name_, $chunk_length_, $assembly_path_);

		$post_process_result_ = $this->post_process(array(
			'size' => filesize($final_file_path_),
			'type' => mime_content_type($final_file_path_),
			'r_name' => $yyymmdd_.'/'.$r_name_,
		), $options_);
		if($post_process_result_ instanceof PBError){
			pb_upload_unlink_inside($upload_root_, $final_file_path_);
			return $post_process_result_;
		}

		return $post_process_result_;
	}

	function post_process($result_, $options_ = array()){
		$upload_root_ = pb_upload_root_path();
		$r_name_ = isset($result_['r_name']) ? str_replace('\\', '/', (string)$result_['r_name']) : '';
		$r_file_path_ = $upload_root_ !== false ? $upload_root_.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $r_name_) : false;
		if($upload_root_ === false || $r_file_path_ === false || !is_file($r_file_path_) || is_link($r_file_path_) || !pb_upload_path_is_inside($upload_root_, $r_file_path_)){
			return new PBError(-403, __("업로드실패"), __("업로드 파일 경로가 올바르지 않습니다."));
		}

		$image_size_info_ = @getimagesize($r_file_path_);
		$is_image_ = is_array($image_size_info_);

		if($is_image_){
			$relative_directory_ = str_replace('\\', '/', dirname($r_name_));
			if(!preg_match('/\A[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*\z/D', $relative_directory_)){
				return new PBError(-403, __("업로드실패"), __("업로드 파일 경로가 올바르지 않습니다."));
			}

			$max_image_width_ = isset($options_['max_image_width']) ? $options_['max_image_width'] : (defined("PB_UPLOAD_MAX_IMAGE_WIDTH") ? PB_UPLOAD_MAX_IMAGE_WIDTH : null);
			$max_image_height_ = isset($options_['max_image_height']) ? $options_['max_image_height'] : (defined("PB_UPLOAD_MAX_IMAGE_HEIGHT") ? PB_UPLOAD_MAX_IMAGE_HEIGHT : null);

			$exif_data_ = @exif_read_data($r_file_path_);
			$image_type_ = exif_imagetype($r_file_path_);

			$can_rotate_ = ($image_type_ === IMAGETYPE_JPEG || $image_type_ === IMAGETYPE_PNG);

			$image_resource_ = null;

			switch($image_type_){
				case IMAGETYPE_JPEG : 
					$image_resource_ = imagecreatefromjpeg($r_file_path_);
				break;
				case IMAGETYPE_PNG : 
					$image_resource_ = imagecreatefrompng($r_file_path_);
					imageAlphaBlending($image_resource_, true);
					imageSaveAlpha($image_resource_, true);
				break;
				case IMAGETYPE_GIF : 
					$image_resource_ = imagecreatefromgif($r_file_path_);
				break;
			}
			if($image_resource_ === null || $image_resource_ === false) return $result_;


			if($can_rotate_ && !empty($exif_data_['Orientation'])){
				switch($exif_data_['Orientation']) {
					case 3:
					$temp_resource_ = imagerotate($image_resource_, 180, 0);
					imagedestroy($image_resource_);
					break;
					case 6:
					$temp_resource_ = imagerotate($image_resource_, -90, 0);
					imagedestroy($image_resource_);
					break;
					case 8:
					$temp_resource_ = imagerotate($image_resource_, 90, 0);
					imagedestroy($image_resource_);
					break;
					default:
					$temp_resource_ = $image_resource_;
				}

				
				$image_resource_ = $temp_resource_;
			}


			$original_width_ = imagesx($image_resource_);
			$original_height_ = imagesY($image_resource_);
			$original_rate_ = $original_width_ / $original_height_;

			//image resizing
			$did_resized_ = false;
			if((isset($max_image_width_) && $max_image_width_ < $original_width_)
				|| (isset($max_image_height_) && $max_image_height_ < $original_height_)){

				$image_dst_width_ = $original_width_;
				$image_dst_height_ = $original_height_;

				if(isset($max_image_width_)){
					$image_dst_width_ = $image_dst_width_ > $max_image_width_ ? $max_image_width_ : $image_dst_width_;
					$image_dst_height_ = $image_dst_width_ / $original_rate_;
				}

				if(isset($max_image_height_)){
					$image_dst_height_ = $image_dst_height_ > $max_image_height_ ? $max_image_height_ : $image_dst_height_;
					$image_dst_width_ = $image_dst_height_ * ($original_rate_);
				}

				$resize_dst_instance_ = imagecreatetruecolor($image_dst_width_, $image_dst_height_);
				imagealphablending($resize_dst_instance_, false);
				imagesavealpha($resize_dst_instance_,true);
				$transparent_ = imagecolorallocatealpha($resize_dst_instance_, 255, 255, 255, 127);
				imagefilledrectangle($resize_dst_instance_, 0, 0, $image_dst_width_, $image_dst_height_, $transparent_);
				imagecopyresampled($resize_dst_instance_, $image_resource_, 0, 0, 0, 0, $image_dst_width_, $image_dst_height_, $original_width_, $original_height_);

				imagedestroy($image_resource_);
				$image_resource_ = $resize_dst_instance_;
				$did_resized_ = true;
			}


			$thumbnail_directory_ = pb_upload_directory($upload_root_, $relative_directory_.'/thumbnail');
			if($thumbnail_directory_ === false) return new PBError(-500, __("업로드실패"), __("썸네일 경로를 준비할 수 없습니다."));

			$thumbnail_file_name_ = pathinfo($r_file_path_, PATHINFO_FILENAME).".jpg";
			$thumbnail_file_path_ = $thumbnail_directory_.DIRECTORY_SEPARATOR.$thumbnail_file_name_;
			if(!pb_upload_path_is_inside($upload_root_, $thumbnail_file_path_)) return new PBError(-403, __("업로드실패"), __("썸네일 경로가 올바르지 않습니다."));

			//make thumbnail
			$thumbnail_dst_width_ = isset($options_['thumbnail_size']) ? (int)$options_['thumbnail_size'] : 500;
			$thumbnail_dst_width_ = max(1, min(4096, $thumbnail_dst_width_));
			$thumbnail_dst_height_ = $thumbnail_dst_width_ / $original_rate_;
			
			$thumbnail_dst_instance_ = imagecreatetruecolor($thumbnail_dst_width_, $thumbnail_dst_height_);
			imagecopyresampled($thumbnail_dst_instance_, $image_resource_, 0, 0, 0, 0, $thumbnail_dst_width_, $thumbnail_dst_height_, $original_width_, $original_height_);

			imagejpeg($thumbnail_dst_instance_, $thumbnail_file_path_);
			imagedestroy($thumbnail_dst_instance_);

			$result_['thumbnail'] = $relative_directory_."/thumbnail/".$thumbnail_file_name_;

			if($did_resized_){
				switch($image_type_){
					case IMAGETYPE_JPEG : 
						$move_result_ = imagejpeg($image_resource_, $r_file_path_);
					break;
					case IMAGETYPE_PNG : 
						$move_result_ = imagepng($image_resource_, $r_file_path_);
					break;
					case IMAGETYPE_GIF : 
						$move_result_ = imagegif($image_resource_, $r_file_path_);
					break;
				}
			}

			imagedestroy($image_resource_);
		}

		return $result_;


	}
}
?>

<?php

if(!defined('PB_DOCUMENT_PATH')){
	die('-1');
}

/**
 * pb_chunk_text_handle
 *
 * 텍스트 청크를 수신하고, 마지막 청크에서 병합 후 콜백을 실행합니다.
 * 권한 체크는 호출자가 사전에 수행해야 합니다.
 *
 * @param callable $complete_callback_  function($merged_text_, $extra_params_) → array|PBError
 * @param array    $options_            선택적 설정 ('temp_dir' 등)
 */
function pb_chunk_text_handle($complete_callback_, $options_ = array()){

	$upload_key_    = _POST('_chunk_upload_key');
	$chunk_current_ = _POST('_chunk_current', null, PB_PARAM_INT);
	$chunk_length_  = _POST('_chunk_length', null, PB_PARAM_INT);
	$chunk_data_    = _POST('_chunk_data');

	if(!strlen($upload_key_) || !isset($chunk_current_) || !isset($chunk_length_) || !isset($chunk_data_)){
		pb_ajax_error(__("잘못된 요청"), __("청크 파라미터가 누락되었습니다."));
	}

	if($chunk_current_ < 0 || $chunk_current_ >= $chunk_length_){
		pb_ajax_error(__("잘못된 요청"), __("청크 인덱스가 범위를 벗어났습니다."));
	}

	$upload_key_ = preg_replace('/[^a-zA-Z0-9]/', '', $upload_key_);
	$upload_key_ = substr($upload_key_, 0, 20);

	if(!strlen($upload_key_)){
		pb_ajax_error(__("잘못된 요청"), __("업로드 키가 유효하지 않습니다."));
	}

	$temp_dir_ = isset($options_['temp_dir']) ? $options_['temp_dir'] : PB_DOCUMENT_PATH . "uploads/_chunk_text_temp/";

	if(!is_dir($temp_dir_)){
		mkdir($temp_dir_, 0755, true);
	}

	if(!file_exists($temp_dir_ . ".htaccess")){
		$htaccess_ = @fopen($temp_dir_ . ".htaccess", "w+b");
		if($htaccess_){
			fwrite($htaccess_, "Deny from all");
			fclose($htaccess_);
		}
	}

	$chunk_file_path_ = $temp_dir_ . $upload_key_ . ".part" . $chunk_current_;
	$written_ = file_put_contents($chunk_file_path_, $chunk_data_);

	if($written_ === false){
		pb_ajax_error(__("저장 실패"), __("임시 청크를 저장할 수 없습니다."));
	}

	if($chunk_current_ < $chunk_length_ - 1){
		pb_ajax_success(array(
			'chunk_current' => $chunk_current_,
			'chunk_length'  => $chunk_length_,
			'progress'      => round(($chunk_current_ + 1) / $chunk_length_ * 100, 1),
		));
		return;
	}

	$merged_text_ = "";
	for($i_ = 0; $i_ < $chunk_length_; $i_++){
		$part_path_ = $temp_dir_ . $upload_key_ . ".part" . $i_;

		if(!file_exists($part_path_)){
			_pb_chunk_text_cleanup($temp_dir_, $upload_key_, $chunk_length_);
			pb_ajax_error(__("병합 실패"), __("청크 파일이 누락되었습니다: part" . $i_));
		}

		$merged_text_ .= file_get_contents($part_path_);
	}

	_pb_chunk_text_cleanup($temp_dir_, $upload_key_, $chunk_length_);

	$extra_params_ = array();
	foreach($_POST as $key_ => $value_){
		if(strpos($key_, '_chunk_') !== 0){
			$extra_params_[$key_] = $value_;
		}
	}

	$result_ = call_user_func($complete_callback_, $merged_text_, $extra_params_);

	if(pb_is_error($result_)){
		pb_ajax_error($result_->error_title(), $result_->error_message());
	}

	if(is_array($result_)){
		pb_ajax_success($result_);
	}

	pb_ajax_success();
}

/**
 * 임시 청크 파일 정리
 */
function _pb_chunk_text_cleanup($temp_dir_, $upload_key_, $chunk_length_){
	for($i_ = 0; $i_ < $chunk_length_; $i_++){
		$part_path_ = $temp_dir_ . $upload_key_ . ".part" . $i_;
		if(file_exists($part_path_)){
			@unlink($part_path_);
		}
	}
}

/**
 * pb_chunk_text_register
 *
 * 청크 텍스트 지원 AJAX 액션을 한번에 등록합니다.
 *
 * @param string   $action_name_        AJAX 액션명
 * @param callable $authority_check_    권한 체크 함수 (실패 시 pb_ajax_error 호출)
 * @param callable $complete_callback_  function($merged_text_, $extra_params_) → array|PBError
 * @param array    $options_            pb_chunk_text_handle에 전달할 옵션
 */
function pb_chunk_text_register($action_name_, $authority_check_, $complete_callback_, $options_ = array()){
	pb_add_ajax($action_name_, function() use ($authority_check_, $complete_callback_, $options_){
		call_user_func($authority_check_);
		pb_chunk_text_handle($complete_callback_, $options_);
	});
}

/**
 * 오래된 임시 청크 파일 정리 (1시간 이상)
 */
function _pb_chunk_text_cleanup_stale(){
	$temp_dir_ = PB_DOCUMENT_PATH . "uploads/_chunk_text_temp/";
	if(!is_dir($temp_dir_)) return;

	$expire_time_ = time() - 3600;
	$files_ = glob($temp_dir_ . "*.part*");

	if(!is_array($files_)) return;

	foreach($files_ as $file_){
		if(filemtime($file_) < $expire_time_){
			@unlink($file_);
		}
	}
}

if(is_dir(PB_DOCUMENT_PATH . "uploads/_chunk_text_temp/")){
	pb_hook_add_action('pb_init', '_pb_chunk_text_cleanup_stale', 99);
}

?>

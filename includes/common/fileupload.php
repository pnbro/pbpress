<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

function pb_fileupload_url($params_ = array()){
	return pb_make_url(pb_home_url("fileupload"), $params_);
}
function pb_filebase_url($file_path_ = null, $params_ = array()){
	return pb_make_url(pb_home_url("uploads/".$file_path_), $params_);	
}

function _pb_fileupload_add_to_rewrite($results_){
	$results_['fileupload'] = array(
		'page' => PB_DOCUMENT_PATH."includes/common/_fileupload.php",
	);
	return $results_;
};
pb_hook_add_filter('pb_rewrite_list', "_pb_fileupload_add_to_rewrite");

function _pb_fileupload_add_to_header_pbvar($results_){
	$results_['fileupload_url'] = pb_fileupload_url();
	$results_['filebase_url'] = pb_filebase_url();
	return $results_;
};
pb_hook_add_filter('pb-admin-head-pbvar', "_pb_fileupload_add_to_header_pbvar");
pb_hook_add_filter('pb-head-pbvar', "_pb_fileupload_add_to_header_pbvar");

function _pb_install_rewrite_for_upload_directory(){
	$rewrite_base_ = str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH."uploads/");
	$rewrite_file_ = @fopen(PB_DOCUMENT_PATH."uploads/.htaccess", "w");

	if(!isset($rewrite_file_)){
		return new PBError(-1, "에러발생", "Rewrite를 생성할 수 없습니다. 파일권한을 확인하세요.");
	}

	fwrite($rewrite_file_, "RemoveType .phtml .php3 .htm .html .php .asp .jsp\n\rRemoveHandler .phtml .php3 .htm .html .php .asp .jsp");
	fclose($rewrite_file_);

	return true;
}

function pb_fileupload_handle($files_, $options_ = array()){
	$options_ = array_merge(array(
		'upload_dir' => null,
		'thumbnail_size' => 500,
	), $options_);

	$upload_dir_ = (isset($options_['upload_dir']) && strlen($options_['upload_dir'])) ? $options_['upload_dir'] : "";
	$upload_dir_ = '/'.trim($upload_dir_, '/');
	$upload_dir_ = rtrim($upload_dir_, '/') . '/';	

	$yyymmdd = new DateTime();
	$yyymmdd_ = date_format($yyymmdd,"Ymd")."/";

	$upload_path_ = PB_DOCUMENT_PATH."uploads".$upload_dir_;
	$upload_url_ = PB_DOCUMENT_URL."uploads".$upload_dir_;

	$max_image_width_ = defined("PB_UPLOAD_MAX_IMAGE_WIDTH") ? PB_UPLOAD_MAX_IMAGE_WIDTH : null;
	$max_image_height_ = defined("PB_UPLOAD_MAX_IMAGE_HEIGHT") ? PB_UPLOAD_MAX_IMAGE_HEIGHT : null;
	$image_file_types_exp_ = '/\.(gif|jpe?g|png)$/i';

	$total_file_count_ = count($files_['name']);

	for($file_index_= 0; $file_index_ < $total_file_count_; ++$file_index_){
		$original_file_name_ = basename($files_['name'][$file_index_]);
		$origianl_file_path_ = $files_['tmp_name'][$file_index_];

		switch($files_['error'][$file_index_]){
			case UPLOAD_ERR_INI_SIZE : 
			case UPLOAD_ERR_FORM_SIZE : 
				return new PBError($files_['error'][$file_index_], "업로드실패", "파일 사이즈가 너무 큽니다.");

			break;
			case UPLOAD_ERR_PARTIAL : 
				return new PBError($files_['error'][$file_index_], "업로드실패", "파일의 일부만 업로드 되었습니다.");
				
			break;

			case UPLOAD_ERR_NO_FILE : 
				return new PBError($files_['error'][$file_index_], "업로드실패", "업로드할 파일이 비어있습니다.");
			
			break;

			case UPLOAD_ERR_CANT_WRITE :
				return new PBError($files_['error'][$file_index_], "업로드실패", "파일을 쓸 수 있는 권한이 없습니다.");

			break;	

			case UPLOAD_ERR_NO_TMP_DIR : 
			case UPLOAD_ERR_EXTENSION :
				return new PBError($files_['error'][$file_index_], "업로드실패", "파일 업로드가 불가한 환경입니다.");
			break;
		}

		$renamed_file_name_ = pb_random_string(15);

		while(file_exists($upload_path_.$yyymmdd_.$renamed_file_name_)){
			$renamed_file_name_ = pb_random_string(15); 
		}

		$file_extension_ = pathinfo($original_file_name_, PATHINFO_EXTENSION);

		if(strlen($file_extension_)){
			$renamed_file_name_ .= ".".$file_extension_;
		}

		$image_size_info_ = @getimagesize($origianl_file_path_);
		$is_image_ = is_array($image_size_info_);

		if(!file_exists($upload_path_.$yyymmdd_)){
			mkdir($upload_path_.$yyymmdd_, 0755, true);
		}

		$move_result_ = move_uploaded_file($origianl_file_path_, $upload_path_.$yyymmdd_.$renamed_file_name_);

		if(!$move_result_){
			return new PBError(UPLOAD_ERR_CANT_WRITE, "업로드실패", "파일을 쓸 수 있는 권한이 없습니다.");
		}

		$row_data_ = array(
			'size' => $files_['size'][$file_index_],
			'type' => $files_['type'][$file_index_],

			'upload_path' => $upload_dir_,
			
			'o_name' => $yyymmdd_.$original_file_name_,
			'r_name' => $yyymmdd_.$renamed_file_name_,
		);	

		if($is_image_){

			$original_src_instance_ = null;

			if($file_extension_ === "gif"){
				$original_src_instance_ = imagecreatefromgif($upload_path_.$yyymmdd_.$renamed_file_name_);
			}else if($file_extension_ === "png"){
				$original_src_instance_ = imagecreatefrompng($upload_path_.$yyymmdd_.$renamed_file_name_);
			}else{
				$original_src_instance_ = imagecreatefromjpeg($upload_path_.$yyymmdd_.$renamed_file_name_);
			}

			if(isset($original_src_instance_)){

				$original_width_ = imagesx($original_src_instance_);
				$original_height_ = imagesY($original_src_instance_);
				$original_rate_ = $original_width_ / $original_height_;

				//image resizing
				if(isset($max_image_width_) || isset($max_image_height_)){

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
					imagecopyresampled($resize_dst_instance_, $original_src_instance_, 0, 0, 0, 0, $image_dst_width_, $image_dst_height_, $original_width_, $original_height_);

					if($file_extension_ === "gif"){
						$resize_dst_func_ = "imagegif";
					}else if($file_extension_ === "png"){
						$resize_dst_func_ = "imagepng";
					}else{
						$resize_dst_func_ = "imagejpeg";
					}

					call_user_func_array($resize_dst_func_, array($resize_dst_instance_, $upload_path_.$yyymmdd_.$renamed_file_name_));
					imagedestroy($resize_dst_instance_);
					
				}

				if(!file_exists($upload_path_.$yyymmdd_."thumbnail/")){
					mkdir($upload_path_.$yyymmdd_."thumbnail/", 0755, true);
				}

				$thumbnail_file_name_ = pathinfo($renamed_file_name_, PATHINFO_FILENAME).".jpg";

				//make thumbnail
				$thumbnail_dst_width_ = $options_['thumbnail_size'];
				$thumbnail_dst_height_ = $thumbnail_dst_width_ / $original_rate_;
				
				$thumbnail_dst_instance_ = imagecreatetruecolor($thumbnail_dst_width_, $thumbnail_dst_height_);
				imagecopyresampled($thumbnail_dst_instance_, $original_src_instance_, 0, 0, 0, 0, $thumbnail_dst_width_, $thumbnail_dst_height_, $original_width_, $original_height_);

				imagejpeg($thumbnail_dst_instance_, $upload_path_.$yyymmdd_."thumbnail/".$thumbnail_file_name_);

				imagedestroy($original_src_instance_);
				imagedestroy($thumbnail_dst_instance_);

				$row_data_['thumbnail'] = $yyymmdd_."thumbnail/".$thumbnail_file_name_;
			}
		}

		$results_[] = $row_data_;
	}

	return $results_;

}

?>
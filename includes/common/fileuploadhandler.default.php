<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBPressFileUPloadDefaultHandler extends PBPressFileUPloadHandler{

	function initialize(){
		//prevent injection
		if(!file_exists(PB_DOCUMENT_PATH."uploads/.htaccess")){
			$rewrite_base_ = str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH."uploads/");

			if(!is_dir($rewrite_base_)){
				mkdir(PB_DOCUMENT_PATH."uploads", 0755, true);
			}

			$rewrite_file_ = @fopen(PB_DOCUMENT_PATH."uploads/.htaccess", "w+b");

			if(!isset($rewrite_file_)){
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
		
		$upload_dir_ = (isset($options_['upload_dir']) && strlen($options_['upload_dir'])) ? $options_['upload_dir'] : "";
		$upload_dir_ = '/'.trim($upload_dir_, '/');
		$upload_dir_ = rtrim($upload_dir_, '/') . '/';	

		$yyymmdd_ = date("Ymd")."/";

		$upload_path_ = PB_DOCUMENT_PATH."uploads".$upload_dir_;
		$upload_url_ = PB_DOCUMENT_URL."uploads".$upload_dir_;

		$total_file_count_ = count($files_['name']);

		$results_ = array();

		for($file_index_= 0; $file_index_ < $total_file_count_; ++$file_index_){
			$original_file_name_ = basename(urlencode($files_['name'][$file_index_]));
			$original_file_name_ = urldecode($original_file_name_);
			$origianl_file_path_ = $files_['tmp_name'][$file_index_];

			switch($files_['error'][$file_index_]){
				case UPLOAD_ERR_INI_SIZE : 
				case UPLOAD_ERR_FORM_SIZE : 
					return new PBError($files_['error'][$file_index_], __("업로드실패"), __("파일 사이즈가 너무 큽니다."));

				break;
				case UPLOAD_ERR_PARTIAL : 
					return new PBError($files_['error'][$file_index_], __("업로드실패"), __("파일의 일부만 업로드 되었습니다."));
					
				break;

				case UPLOAD_ERR_NO_FILE : 
					return new PBError($files_['error'][$file_index_], __("업로드실패"), __("업로드할 파일이 비어있습니다."));
				
				break;

				case UPLOAD_ERR_CANT_WRITE :
					return new PBError($files_['error'][$file_index_], __("업로드실패"), __("파일을 쓸 수 있는 권한이 없습니다."));

				break;	

				case UPLOAD_ERR_NO_TMP_DIR : 
				case UPLOAD_ERR_EXTENSION :
					return new PBError($files_['error'][$file_index_], __("업로드실패"), __("파일 업로드가 불가한 환경입니다."));
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

			if(!file_exists($upload_path_.$yyymmdd_)){
				mkdir($upload_path_.$yyymmdd_, 0755, true);
			}

			$results_[] = $this->post_process(array(
				'size' => $files_['size'][$file_index_],
				'type' => $files_['type'][$file_index_],

				'o_name' => $original_file_name_,
				'r_name' => $yyymmdd_.$r_name_,

			));			
		}

		return $results_;
	}

	function handle_chunk($chunk_, $data_, $options_ = array()){

		$chunk_error_ = @$chunk_['error'];
		switch($chunk_error_){
			case UPLOAD_ERR_INI_SIZE : 
			case UPLOAD_ERR_FORM_SIZE : 
				return new PBError($chunk_error_, __("업로드실패"), __("파일 사이즈가 너무 큽니다."));

			break;
			case UPLOAD_ERR_PARTIAL : 
				return new PBError($chunk_error_, __("업로드실패"), __("파일의 일부만 업로드 되었습니다."));
				
			break;

			case UPLOAD_ERR_NO_FILE : 
				return new PBError($chunk_error_, __("업로드실패"), __("업로드할 파일이 비어있습니다."));
			
			break;

			case UPLOAD_ERR_CANT_WRITE :
				return new PBError($chunk_error_, __("업로드실패"), __("파일을 쓸 수 있는 권한이 없습니다."));

			break;	

			case UPLOAD_ERR_NO_TMP_DIR : 
			case UPLOAD_ERR_EXTENSION :
				return new PBError($chunk_error_, __("업로드실패"), __("파일 업로드가 불가한 환경입니다."));
			break;
		}

		$chunk_current_ = $data_['chunk_current'];
		$chunk_length_ = $data_['chunk_length'];
		$chunk_size_ = $data_['chunk_size'];
		$r_name_ = @$data_['r_name'];
		$o_name_ = @$data_['o_name'];

		$yyymmdd_ = date("Ymd")."/";

		$upload_path_ = PB_DOCUMENT_PATH."uploads/";
		$upload_url_ = PB_DOCUMENT_URL."uploads/";

		$file_extension_ = pathinfo($o_name_, PATHINFO_EXTENSION);

		if(!strlen($r_name_)){
			$r_name_ = pb_random_string(15);
			
			if(strlen($file_extension_)){
				$r_name_ .= ".".$file_extension_;
			}

			while(file_exists($upload_path_.$yyymmdd_.$r_name_)){
				$r_name_ = pb_random_string(15); 
			}

			if(!file_exists($upload_path_.$yyymmdd_)){
				mkdir($upload_path_.$yyymmdd_, 0755, true);
			}
		}


		$chunk_file_path_ = $upload_path_.$yyymmdd_.$r_name_. '.part'.$chunk_current_;
		move_uploaded_file($chunk_['tmp_name'], $chunk_file_path_);

		if($chunk_current_ >= $chunk_length_ - 1){
    		$final_file_path_ = $upload_path_.$yyymmdd_.$r_name_;
			$fp_ = fopen($final_file_path_, 'w');

			for($chunk_index_=0; $chunk_index_ < $chunk_length_; $chunk_index_++) {
				$chunk_file_path_ = $upload_path_.$yyymmdd_.$r_name_. '.part'.$chunk_index_;
				$chunk_instance_ = fopen($chunk_file_path_, 'r');
				while($chunk_line_ = fread($chunk_instance_, 1024)) {
					fwrite($fp_, $chunk_line_);
				}
				fclose($chunk_instance_);
				unlink($chunk_file_path_);
			}
			fclose($fp_);


			return $this->post_process(array(
				'size' => filesize($final_file_path_),
				'type' => mime_content_type($final_file_path_),

				'r_name' => $yyymmdd_.$r_name_,

			));
		}

		return array('r_name' => $r_name_);
	}

	function post_process($result_, $options_ = array()){

		$upload_path_ = PB_DOCUMENT_PATH."uploads/";
		$r_file_path_ = $upload_path_.$result_['r_name'];

		$image_size_info_ = @getimagesize($r_file_path_);
		$is_image_ = is_array($image_size_info_);
		$thumbnail_ = null;

		if($is_image_){

			$yyymmdd_ = date("Ymd")."/";

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


			if(!file_exists($upload_path_.$yyymmdd_."thumbnail/")){
				mkdir($upload_path_.$yyymmdd_."thumbnail/", 0755, true);
			}

			$thumbnail_file_name_ = pathinfo($r_file_path_, PATHINFO_FILENAME).".jpg";

			//make thumbnail
			$thumbnail_dst_width_ = isset($options_['thumbnail_size']) ? $options_['thumbnail_size'] : 500;
			$thumbnail_dst_height_ = $thumbnail_dst_width_ / $original_rate_;
			
			$thumbnail_dst_instance_ = imagecreatetruecolor($thumbnail_dst_width_, $thumbnail_dst_height_);
			imagecopyresampled($thumbnail_dst_instance_, $image_resource_, 0, 0, 0, 0, $thumbnail_dst_width_, $thumbnail_dst_height_, $original_width_, $original_height_);

			imagejpeg($thumbnail_dst_instance_, $upload_path_.$yyymmdd_."thumbnail/".$thumbnail_file_name_);
			imagedestroy($thumbnail_dst_instance_);

			$result_['thumbnail'] = $yyymmdd_."thumbnail/".$thumbnail_file_name_;

			if($did_resized_){
				switch($image_type_){
					case IMAGETYPE_JPEG : 
						$move_result_ = imagejpeg($image_resource_, $upload_path_.$yyymmdd_.$r_name_);
					break;
					case IMAGETYPE_PNG : 
						$move_result_ = imagepng($image_resource_, $upload_path_.$yyymmdd_.$r_name_);
					break;
					case IMAGETYPE_GIF : 
						$move_result_ = imagegif($image_resource_, $upload_path_.$yyymmdd_.$r_name_);
					break;
				}
			}

			imagedestroy($image_resource_);
		}

		return $result_;


	}
}
?>
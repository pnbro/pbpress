<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

if(!defined('PB_FILE_UPLOAD_AWS_CLIENT_KEY')
 | !defined('PB_FILE_UPLOAD_AWS_CLIENT_SECRET')
 | !defined('PB_FILE_UPLOAD_AWS_CLIENT_VERSION')
 | !defined('PB_FILE_UPLOAD_AWS_CLIENT_REGION')
 | !defined('PB_FILE_UPLOAD_AWS_BUCKET')){
	die("aws entry not found at pb-config.php.");
}

require(PB_DOCUMENT_PATH . 'includes/common/lib/aws/aws-autoloader.php');

use Aws\Exception\UnresolvedApiException;
use Aws\Common\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;

class PBPressFileUPloadAWSHandler extends PBPressFileUPloadHandler{

	private $_client = null;
	
	function initialize(){
		$this->_client = new Aws\S3\S3Client([
			'version' => PB_FILE_UPLOAD_AWS_CLIENT_VERSION,
			'region' => PB_FILE_UPLOAD_AWS_CLIENT_REGION,
			'credentials' => array(
				'key'    => PB_FILE_UPLOAD_AWS_CLIENT_KEY,
				'secret' => PB_FILE_UPLOAD_AWS_CLIENT_SECRET,
			),
		]);
	}

	function filebase_url($file_path_ = null, $params_ = array()){
		if(defined("PB_FILE_UPLOAD_AWS_FILEBASE_URL")){
			return pb_append_url(PB_FILE_UPLOAD_AWS_FILEBASE_URL, $file_path_);
		}else{
			return "//".PB_FILE_UPLOAD_AWS_BUCKET.".s3.".PB_FILE_UPLOAD_AWS_CLIENT_REGION.".amazonaws.com/{$file_path_}";	
		}
		
	}

	function handle($files_, $options_ = array()){
		$total_file_count_ = count($files_['name']);

		$upload_path_ = isset($options_['upload_dir']) ? $options_['upload_dir'] : "";

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

			$image_size_info_ = @getimagesize($origianl_file_path_);
			$is_image_ = is_array($image_size_info_);
			$renamed_file_name_ = pb_random_string(15);

			$yyymmdd = new DateTime();
			$yyymmdd_ = date_format($yyymmdd,"Ymd")."/";

			while(file_exists($upload_path_.$yyymmdd_.$renamed_file_name_)){
				$renamed_file_name_ = pb_random_string(15); 
			}

			$file_extension_ = pathinfo($original_file_name_, PATHINFO_EXTENSION);

			if(strlen($file_extension_)){
				$renamed_file_name_ .= ".".$file_extension_;
			}

			$row_data_ = array(
				'size' => $files_['size'][$file_index_],
				'type' => $files_['type'][$file_index_],

				'upload_path' => "",
				
				'o_name' => $upload_path_.$yyymmdd_.$original_file_name_,
				'r_name' => $upload_path_.$yyymmdd_.$renamed_file_name_,
			);	

			if($is_image_){
				$row_data_['thumbnail'] = $upload_path_.$yyymmdd_.$renamed_file_name_;
			}

			$uploader_ = new MultipartUploader($this->_client, $origianl_file_path_, [
				'bucket' => PB_FILE_UPLOAD_AWS_BUCKET,
				'Key' => $upload_path_.$yyymmdd_.$renamed_file_name_,
				'acl' => 'public-read',
			]);

			try {
				$aws_result_ = $uploader_->upload();
			}catch(MultipartUploadException $ex_){
				return new PBError(500, __("업로드실패"), "AWS ERROR - ".$ex_->getMessage());
			}


			$results_[] = $row_data_;
		}

		

		return $results_;
	}
}
?>
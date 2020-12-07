<?php

if(!defined('PB_DOCUMENT_PATH')){
    die( '-1' );
}

class PBSessionHandlerFile implements SessionHandlerInterface{

	private $_session_save_path;

	function __construct(){}
	
	function open($save_path_, $id_){
		$this->_session_save_path = rtrim($save_path_)."/";

		if(!is_dir($this->_session_save_path)){
			mkdir($this->_session_save_path, 0777, true);
		}

		return true;
	}
	function close(){
		return true;
	}
	function read($id_){
		return (string) @file_get_contents($this->_session_save_path.$id_);
	}
	function write($id_, $data_){
		$file_resource_ = @fopen($this->_session_save_path.$id_, "w+b");

		if($file_resource_) {
			$result_ = fwrite($file_resource_, $data_);
			fclose($file_resource_);
			return (bool)$result_;
		}else{
			return false;
		}
	}
	function destroy($id_){
		return @unlink($this->_session_save_path.$id_);
	}
	function gc($timeout_){
		foreach(glob($this->_session_save_path."session_*") as $file_path_){
			if(filemtime($file_path_) + $timeout_ < time()){
				@unlink($file_path_);
			}
		}
		return true;
	}
}

?>
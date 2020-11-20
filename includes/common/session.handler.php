<?php

if(!defined('PB_DOCUMENT_PATH')){
    die( '-1' );
}

abstract class PBSessionHandler{
	abstract function open($save_path_, $id_);
	abstract function close();
	abstract function read($id_);
	abstract function write($id_, $data_);
	abstract function destroy($id_);
	abstract function clean_up($timeout_);
}

class PBSessionHandlerFile extends PBSessionHandler{

	private $_session_save_path;

	function __construct(){}
	
	function open($save_path_, $id_){
		$this->_session_save_path = rtrim($save_path_)."/";
		$this->_session_file_path = $this->_session_save_path."session_".$id_;

		if(!is_dir($this->_session_save_path)){
			mkdir($this->_session_save_path, 0777, true);
		}

		return true;
	}
	function close(){
		return true;
	}
	function read($id_){
		return (string) @file_get_contents($this->_session_file_path);
	}
	function write($id_, $data_){
		$file_resource_ = @fopen($this->_session_file_path, "w+b");

		if($file_resource_) {
			$result_ = fwrite($file_resource_, $data_);
			fclose($file_resource_);
			return (bool)$result_;
		}else{
			return false;
		}
	}
	function destroy($id_){
		return @unlink($this->_session_file_path);
	}
	function clean_up($timeout_){
		foreach(glob($this->_session_save_path."session_*") as $file_path_){
			if(filemtime($file_path_) + $timeout_ < time()){
				@unlink($file_path_);
			}
		}
		return true;
	}
}

?>
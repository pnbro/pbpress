<?php

if(!defined('PB_DOCUMENT_PATH')){
    die( '-1' );
}

global $sessions_do;
$sessions_do = pbdb_data_object("sessions", array(
	'id'		 => array("type" => PBDB_DO::TYPE_VARCHAR, "length" => 50, "pk" => true, "comment" => "ID"),
	'session_data' => array("type" => PBDB_DO::TYPE_LONGTEXT, "comment" => "세션데이타"),
	'ip_addr' => array("type" => PBDB_DO::TYPE_VARCHAR, 'length' => 30, "index" => true, "comment" => "세션데이타"),
	'expire_time' => array("type" => PBDB_DO::TYPE_INT, "length" => 10, "comment" => "마지막활성일자"),
),"세션");

class PBSessionHandlerDatabase extends PBSessionHandler{

	function __construct(){
		pb_hook_add_action('pb_db_connection_before_close', array($this, '_hook_for_close_database'));
	}

	function _hook_for_close_database($connection_){
		global $pbdb;

		if($pbdb->is_default_connection($connection_)){
			@session_write_close();	
		}
		
	}
	
	function open($save_path_, $id_){
		global $sessions_do;

		if(!$sessions_do->is_exists()){
			global $pbdb;
			$query_ = $sessions_do->_install_tables(array());
			$query_ = $query_[0];

			$pbdb->query($query_);
		}

		return true;
	}
	function close(){
		return true;
	}
	
	private function session_data($id_){
		global $sessions_do;
		$session_statement_ = $sessions_do->statement();
		$session_statement_->add_compare_condition("sessions.id", $id_);

		return $session_statement_->get_first_row();
	}

	function read($id_){
		$session_data_ = $this->session_data($id_);
		if(!isset($session_data_) || $session_data_['expire_time'] < time()) return "";
		return (string) $session_data_['session_data'];
	}
	function write($id_, $data_){
		$check_data_ = $this->session_data($id_);

		global $pb_config, $sessions_do;

		$ipaddress_ = null;

		if(isset($_SERVER['HTTP_CLIENT_IP'])){
			$ipaddress_ = $_SERVER['HTTP_CLIENT_IP'];
		}else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ipaddress_ = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else if(isset($_SERVER['HTTP_X_FORWARDED'])){
			$ipaddress_ = $_SERVER['HTTP_X_FORWARDED'];
		}else if(isset($_SERVER['HTTP_FORWARDED_FOR'])){
			$ipaddress_ = $_SERVER['HTTP_FORWARDED_FOR'];
		}else if(isset($_SERVER['HTTP_FORWARDED'])){
			$ipaddress_ = $_SERVER['HTTP_FORWARDED'];
		}else if(isset($_SERVER['REMOTE_ADDR'])){
			$ipaddress_ = $_SERVER['REMOTE_ADDR'];
		}else{
			$ipaddress_ = "unknown";
		}  

		$result_ = false;

		if(!isset($check_data_)){
			$result_ = $sessions_do->insert(array(
				'id' => $id_,
				'session_data' => $data_,
				'ip_addr' => $ipaddress_,
				'expire_time' => time() + $pb_config->session_max_time(),
			));
		}else{
			$result_ = $sessions_do->update($id_, array(
				'session_data' => $data_,
				'ip_addr' => $ipaddress_,
				'expire_time' => time() + $pb_config->session_max_time(),
			));
		}

		return $result_;
	}
	function destroy($id_){
		global $sessions_do;
		$sessions_do->delete($id_);
		return true;
	}
	function clean_up($timeout_){
		global $pbdb;
		$pbdb->query("DELETE FROM sessions WHERE sessions.expire_time < ".time());
		return true;
	}
}

?>
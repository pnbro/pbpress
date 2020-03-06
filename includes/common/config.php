<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

class PBConfig{

	private $devmode = false;
	private $show_database_error = false;

	public $charset = "UTF-8";
	
	public $db_connection_type = "mysqli";
	public $db_host = "";
	public $db_port = "";
	public $db_username = "";
	public $db_userpass = "";
	public $db_name = "";
	public $db_charset = "utf8";

	public $crypt_password = null;
	public $crypt_algorithm = "sha256";
	public $crypt_bits = 2048;

	private $default_locale = "ko_KR";

	private $use_https = false;

	function __construct(){
		$this->devmode = (defined("PB_DEV") && PB_DEV === true);

		if($this->devmode){
			error_reporting(E_ALL);
			ini_set("display_errors", 1);
		}

		$this->show_database_error = (defined("PB_SHOW_DATABASE_ERROR") ? PB_SHOW_DATABASE_ERROR === true : $this->devmode);

		$this->charset = (defined("PB_CHARSET")) ? PB_CHARSET : "UTF-8";

		$this->db_connection_type = (defined("PB_DB_CONNECTION_TYPE")) ?  PB_DB_CONNECTION_TYPE : "mysqli";
		$this->db_host = (defined("PB_DB_HOST")) ?  PB_DB_HOST : null;
		$this->db_port = (defined("PB_DB_PORT")) ?  PB_DB_PORT : "3306";
		$this->db_username = (defined("PB_DB_USERNAME")) ? PB_DB_USERNAME : null;
		$this->db_userpass = (defined("PB_DB_USERPASS")) ? PB_DB_USERPASS : null;
		$this->db_name = (defined("PB_DB_NAME")) ? PB_DB_NAME : null;
		$this->db_charset = (defined("PB_DB_CHARSET")) ? PB_DB_CHARSET : "utf8";

		$this->crypt_password = (defined("PB_CRYPT_PASSWORD")) ? PB_CRYPT_PASSWORD : null;
		$this->crypt_algorithm = (defined("PB_CRYPT_ALGORITHM")) ? PB_CRYPT_ALGORITHM : "sha256";
		$this->crypt_bits = (defined("PB_CRYPT_BITS")) ? PB_CRYPT_BITS : 2048;

		$this->default_locale = (defined("PB_DEFAULT_LOCALE")) ? PB_DEFAULT_LOCALE : "ko_KR";

		$this->use_https = (defined("PB_HTTPS")) ? PB_HTTPS : false;
		
	}

	public function is_devmode(){
		return $this->devmode;
	}
	public function is_show_database_error(){
		return $this->show_database_error;
	}

	public function default_locale(){
		return $this->default_locale;
	}

	public function use_https(){
		return $this->use_https;
	}
}

global $pb_config;
$pb_config = new PBConfig();
	
?>
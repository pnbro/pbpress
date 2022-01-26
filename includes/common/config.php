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

	public $crypt_static_key_size = 32;
	public $crypt_static_iv_size = 16;
	public $crypt_static_cipher_mode = 'AES-256-CBC';

	public $wysiwyg_editor;
	public $file_upload_handler = "default";

	private $default_locale = "ko_KR";
	private $use_https = false;

	private $session_manager = null;
	private $session_cookie_domain = null;
	private $session_cookie_samesite;
	private $session_cookie_secure;
	private $session_cookie_httponly;

	private $session_save_path = null;
	private $session_max_time = null;

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

		$this->pb_crypt_static_key_size = (defined("PB_CRYPT_STATIC_KEY_SIZE")) ? PB_CRYPT_STATIC_KEY_SIZE : 32;
		$this->pb_crypt_static_iv_size = (defined("PB_CRYPT_STATIC_IV_SIZE")) ? PB_CRYPT_STATIC_IV_SIZE : 16;
		$this->pb_crypt_static_cipher_mode = (defined("PB_CRYPT_STATIC_CIPHER_MODE")) ? PB_CRYPT_STATIC_CIPHER_MODE : 'AES-256-CBC';

		$this->wysiwyg_editor = (defined("PB_WYSIWYG_EDITOR")) ? PB_WYSIWYG_EDITOR : "summernote";
		$this->file_upload_handler = (defined("PB_FILE_UPLOAD_HANDLER")) ? PB_FILE_UPLOAD_HANDLER : "default";

		$this->default_locale = (defined("PB_DEFAULT_LOCALE")) ? PB_DEFAULT_LOCALE : "ko_KR";

		$this->use_https = (defined("PB_HTTPS")) ? PB_HTTPS : false;
		
		$this->session_manager = (defined("PB_SESSION_MANAGER")) ? PB_SESSION_MANAGER : "default";
		$this->session_cookie_domain = (defined("PB_SESSION_COOKIE_DOMAIN")) ? PB_SESSION_COOKIE_DOMAIN : null;

		if(!strlen($this->session_cookie_domain)){
			$document_url_ = parse_url(PB_DOCUMENT_URL);
			$document_url_ = $document_url_['host'];

			$cookie_domain_ = (strpos($document_url_,"www.") !==false)? ".".preg_replace("/www./i","",$document_url_):".".$document_url_;
			$cookie_domain_ = preg_replace("/:[0-9]+$/i","",$cookie_domain_);
			$cookie_domain_ = trim($cookie_domain_, ".");

			$this->session_cookie_domain = $cookie_domain_;
		}

		$this->session_cookie_domain = trim($this->session_cookie_domain,"/");

		$this->session_cookie_samesite = (defined("PB_SESSION_COOKIE_SAMESITE")) ? PB_SESSION_COOKIE_SAMESITE : 'Lax';
		$this->session_cookie_secure = (defined("PB_SESSION_COOKIE_SECURE")) ? PB_SESSION_COOKIE_SECURE : null;
		$this->session_cookie_httponly = (defined("PB_SESSION_COOKIE_HTTPONLY")) ? PB_SESSION_COOKIE_HTTPONLY : null;

		$this->session_save_path = (defined("PB_SESSION_SAVE_PATH")) ? PB_SESSION_SAVE_PATH : session_save_path();
		$this->session_save_path = rtrim($this->session_save_path,"/")."/";
		$this->session_max_time = (defined("PB_SESSION_MAX_TIME")) ? PB_SESSION_MAX_TIME : 60 * 60 * 60 * 3;
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

	public function session_manager(){
		return $this->session_manager;
	}

	public function session_cookie_domain(){
		return $this->session_cookie_domain;
	}

	public function session_cookie_samesite(){
		return $this->session_cookie_samesite;
	}
	public function session_cookie_secure(){
		return $this->session_cookie_secure;
	}
	public function session_cookie_httponly(){
		return $this->session_cookie_httponly;
	}

	public function session_save_path(){
		return $this->session_save_path;
	}

	public function session_max_time(){
		return $this->session_max_time;
	}
}

global $pb_config;
$pb_config = new PBConfig();
	
?>
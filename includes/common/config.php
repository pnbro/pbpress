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

	public $crypt_static_iv_size = 16;
	public $crypt_static_cipher_mode = 'AES-256-CBC';

	public $wysiwyg_editor;
	public $file_upload_handler = "default";

	private $default_locale = "ko_KR";

	private $session_manager = null;
	
	private $session_cookie_max_time;
	private $session_cookie_domain;
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

		$this->crypt_static_iv_size = (defined("PB_CRYPT_STATIC_IV_SIZE")) ? PB_CRYPT_STATIC_IV_SIZE : 16;
		$this->crypt_static_cipher_mode = (defined("PB_CRYPT_STATIC_CIPHER_MODE")) ? PB_CRYPT_STATIC_CIPHER_MODE : 'AES-256-CBC';

		$this->wysiwyg_editor = (defined("PB_WYSIWYG_EDITOR")) ? PB_WYSIWYG_EDITOR : "summernote";
		$this->file_upload_handler = (defined("PB_FILE_UPLOAD_HANDLER")) ? PB_FILE_UPLOAD_HANDLER : "default";

		$this->default_locale = (defined("PB_DEFAULT_LOCALE")) ? PB_DEFAULT_LOCALE : "ko_KR";
		
		$this->session_manager = (defined("PB_SESSION_MANAGER")) ? PB_SESSION_MANAGER : "default";
		$this->session_cookie_domain = (defined("PB_SESSION_COOKIE_DOMAIN")) ? PB_SESSION_COOKIE_DOMAIN : null;

		if(!strlen($this->session_cookie_domain)){
			$this->session_cookie_domain = PB_DOCUMENT_DOMAIN;
		}

		$this->session_cookie_domain = trim($this->session_cookie_domain,"/");

		$this->session_cookie_max_time = (defined("PB_SESSION_COOKIE_MAX_TIME")) ? PB_SESSION_COOKIE_MAX_TIME : 60 * 60 * 60 * 3;
		$this->session_cookie_samesite = (defined("PB_SESSION_COOKIE_SAMESITE")) ? PB_SESSION_COOKIE_SAMESITE : 'Lax';
		$this->session_cookie_secure = (defined("PB_SESSION_COOKIE_SECURE")) ? PB_SESSION_COOKIE_SECURE : null;
		$this->session_cookie_httponly = (defined("PB_SESSION_COOKIE_HTTPONLY")) ? PB_SESSION_COOKIE_HTTPONLY : null;

		$this->session_save_path = (defined("PB_SESSION_SAVE_PATH")) ? PB_SESSION_SAVE_PATH : session_save_path();
		$this->session_save_path = rtrim($this->session_save_path,"/")."/";
		$this->session_max_time = (defined("PB_SESSION_MAX_TIME")) ? PB_SESSION_MAX_TIME : 60 * 60 * 60 * 3;
	}

	public function is_devmode(){
		return pb_hook_apply_filters('pb_config_is_devmode', $this->devmode);
	}
	public function is_show_database_error(){
		return pb_hook_apply_filters('pb_config_is_show_database_error', $this->show_database_error);
	}

	public function default_locale(){
		return pb_hook_apply_filters('pb_config_default_locale', $this->default_locale);
	}

	public function session_manager(){
		return pb_hook_apply_filters('pb_config_session_manager', $this->session_manager);
	}

	public function session_cookie_max_time(){
		return pb_hook_apply_filters('pb_config_session_cookie_max_time', $this->session_cookie_max_time);
	}
	public function session_cookie_domain(){
		return pb_hook_apply_filters('pb_config_session_cookie_domain', $this->session_cookie_domain);
	}
	public function session_cookie_samesite(){
		return pb_hook_apply_filters('pb_config_session_cookie_samesite', $this->session_cookie_samesite);
	}
	public function session_cookie_secure(){
		return pb_hook_apply_filters('pb_config_session_cookie_secure', $this->session_cookie_secure);
	}
	public function session_cookie_httponly(){
		return pb_hook_apply_filters('pb_config_session_cookie_httponly', $this->session_cookie_httponly);
	}

	public function session_save_path(){
		return pb_hook_apply_filters('pb_config_session_save_path', $this->session_save_path);
	}

	public function session_max_time(){
		return pb_hook_apply_filters('pb_config_session_max_time', $this->session_max_time);
	}
}

global $pb_config;
$pb_config = new PBConfig();
	
?>
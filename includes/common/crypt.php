<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_CRYPT_SHARED_KEY',"_PB_CRYPT_SHARED_KEY_");

//RAS 키생성
function pb_crypt_shared_keys(){
	$shared_key_ = pb_session_get(PB_CRYPT_SHARED_KEY);

	if(isset($shared_key_)){
		return $shared_key_;
	}

	global $pb_config;

	$key_ = openssl_pkey_new(array(
		'digest_alg' => $pb_config->crypt_algorithm,
		'private_key_bits' => $pb_config->crypt_bits,
		'private_key_type' => OPENSSL_KEYTYPE_RSA,
	));

	$private_key_ = null;
	openssl_pkey_export($key_, $private_key_, $pb_config->crypt_password);

	$public_key_ = openssl_pkey_get_details($key_);
	$public_key_ = $public_key_['key'];
	
	$matches_ = array();
    // preg_match('~^-----BEGIN ([A-Z ]+)-----\s*?([A-Za-z0-9+=/\r\n]+)\s*?-----END \1-----\s*$~D', $public_key_, $matches_);
    // $public_key_ = str_replace(array("\r", "\n"), array('', ''), $matches_[2]);
	
	$shared_key_ = array(
		'private_key' => $private_key_,
		'public_key' => $public_key_,
	);

	pb_session_put(PB_CRYPT_SHARED_KEY, $shared_key_);
	return $shared_key_;
}

// RSA 공개키를 사용하여 문자열을 암호화
function pb_crypt_encrypt($plaintext_, $public_key_ = null){
	$shared_key_ = pb_crypt_shared_keys();
	if(!strlen($public_key_)){
		$public_key_ = $shared_key_['public_key'];
	}
	

	$public_decoded_ = openssl_pkey_get_public($public_key_);
	if($public_decoded_ === false) return false;
	
	$ciphertext_ = false;
	$status_ = @openssl_public_encrypt($plaintext_, $ciphertext_, $public_decoded_);
	if(!$status_ || $ciphertext_ === false) return false;
	
	return base64_encode($ciphertext_);
}

// RSA 개인키를 사용하여 문자열을 복호화
function pb_crypt_decrypt($ciphertext_, $private_key_ = null, $password_ = null){
	$shared_key_ = pb_crypt_shared_keys();
	global $pb_config;

	if(!strlen($private_key_)){
		$private_key_ = $shared_key_['private_key'];
	}

	if(!strlen($password_)){
		$password_ = $pb_config->crypt_password;
	}

	$shared_key_ = pb_crypt_shared_keys();

	$ciphertext_ = @base64_decode($ciphertext_, true);
	if($ciphertext_ === false) return false;

	$privkey_decoded_ = @openssl_pkey_get_private($private_key_, $password_);
	if($privkey_decoded_ === false) return false;

	$plaintext_ = false;
	$status_ = @openssl_private_decrypt($ciphertext_, $plaintext_, $privkey_decoded_);
	if(!$status_ || $plaintext_ === false) return false;
	
	return $plaintext_;
}


function _pb_crypt_load_scripts(){
	$shared_key_ = pb_crypt_shared_keys();
	
	?>
	<script type="text/plain" id="pb-crypt-public-key"><?=$shared_key_['public_key']?></script>

	<?php
}
pb_hook_add_action("pb_header","_pb_crypt_load_scripts");

function _pb_crypt_decrypt_hook($params_, $add_){
	if(!isset($add_) || count($add_) <= 0) return $params_;

	foreach($add_ as $param_name_){
		$params_[$param_name_] = pb_crypt_decrypt($params_[$param_name_]);
	}

	return $params_;
}
pb_hook_add_filter("pb_decrypt", "_pb_crypt_decrypt_hook");

function pb_crypt_hash($plain_){
	return hash("sha256", $plain_, false);
}

?>
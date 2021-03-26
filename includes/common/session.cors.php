<?php

if(!defined('PB_DOCUMENT_PATH')){
    die( '-1' );
}

if(!function_exists('setcookie_samesite')) {
	function setcookie_samesite($name, $value = '', $expires = 0, $path = '', $domain = '', $secure = false, $httponly = false, $samesite = ''){
		if(is_array($expires)) {
			$e = $expires;
			foreach(['expires', 'path', 'domain', 'secure', 'httponly', 'samesite'] as $key) {
				if(isset($e[$key])) $$key = $e[$key];
			}
		}
		if (preg_match('~[=,; \t\r\n\x0b\x0c]~', $name)) {
			trigger_error('Cookie names cannot contain any of the following \'=,; \t\r\n\013\014\'', E_USER_WARNING);
			return false;
		}
		if (preg_match('~[,; \t\r\n\x0b\x0c]~', $path)) {
			trigger_error('Cookie paths cannot contain any of the following \',; \t\r\n\013\014\'', E_USER_WARNING);
			return false;
		}
		if (preg_match('~[,; \t\r\n\x0b\x0c]~', $domain)) {
			trigger_error('Cookie domains cannot contain any of the following \',; \t\r\n\013\014\'', E_USER_WARNING);
			return false;
		}
		$values = [];
		if (empty($value)) {
			$values[] = $name . '=delete';
			$values[] = 'expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0';
		} else {
			$values[] = $name . '=' . urlencode($value);
			if ($expires != 0) {
				$values[] = 'expires=' . substr(gmdate('r', $expires), 0, -5) . 'GMT';
				$values[] = 'Max-Age=' . ($expires - time());
			}
		}
		if ($path) $values[] = 'path=' . $path;
		if ($domain) $values[] = 'domain=' . $domain;
		if ($secure) $values[] = 'secure';
		if ($httponly) $values[] = 'HttpOnly';
		if ($samesite) $values[] = 'SameSite=' . $samesite;
		header('Set-Cookie: ' . implode('; ', $values), false);
		return true;
	}
}
?>
<?php

define("PB_DOCUMENT_PATH", dirname( __FILE__ )."/");
define("PB_DOCUMENT_URL", (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . str_replace($_SERVER["DOCUMENT_ROOT"], "", PB_DOCUMENT_PATH));

define("PB_LIBRARY_PATH", PB_DOCUMENT_PATH."lib/dev/");
define("PB_LIBRARY_URL", PB_DOCUMENT_URL."lib/dev/");

?>
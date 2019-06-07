<?php

require(PB_DOCUMENT_PATH . 'includes/common/config.php');
include(PB_DOCUMENT_PATH . "includes/common/utils.php");
include(PB_DOCUMENT_PATH . "includes/common/error.php");
include(PB_DOCUMENT_PATH . "includes/common/hook.php");
include(PB_DOCUMENT_PATH . "includes/common/session.php");
include(PB_DOCUMENT_PATH . "includes/common/database-utils.php");
include(PB_DOCUMENT_PATH . "includes/common/database.php");
include(PB_DOCUMENT_PATH . "includes/common/crypt.php");
include(PB_DOCUMENT_PATH . "includes/common/functions.php");
include(PB_DOCUMENT_PATH . "includes/common/rewrite.php");
include(PB_DOCUMENT_PATH . "includes/common/option.php");
include(PB_DOCUMENT_PATH . "includes/common/ajax.php");
include(PB_DOCUMENT_PATH . "includes/common/fileupload.php");
include(PB_DOCUMENT_PATH . "includes/common/theme.php");
include(PB_DOCUMENT_PATH . "includes/common/mail.php");
include(PB_DOCUMENT_PATH . "includes/common/multilingual.php");

include(PB_DOCUMENT_PATH . "includes/common/listtable.php");
include(PB_DOCUMENT_PATH . "includes/common/adminpage.php");

include(PB_DOCUMENT_PATH . "includes/gcode/gcode.php");
include(PB_DOCUMENT_PATH . "includes/authority/authority.php");
include(PB_DOCUMENT_PATH . "includes/user/user.php");

$current_theme_path_ = pb_current_theme_path();

if(file_exists($current_theme_path_."functions.php")){
	include($current_theme_path_."functions.php");
}


?>
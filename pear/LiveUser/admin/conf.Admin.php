<?php
require_once('../conf/conf.global.php');

$PHP_SELF = $_SERVER['PHP_SELF'];

$APP_TEMPLATE ='admin.html';
$CHANGE_RIGHT_TEMPLATE = 'change_right.html';

$APPLICATION_NAME = 'Admin';
$DEFAULT_LANGUAGE = 'US';

//$APP_DSN = $AUTH_DB_URL;

$TEMPLATE_DIR = $APP_ROOT . '/admin';
$REL_TEMPLATE_DIR = $REL_MASTER_TEMPLATE_DIR;
// TODO: check --> $REL_TEMPLATE_DIR = $USER_DIR . $PROJECT_NAME . '/login';

$ON = TRUE;
$OFF = FALSE;


require_once 'class.PHPApplication.php';
require_once 'class.Admin.php';

define('ERROR_FILE', 'errors.Admin.php');
define('MESSAGE_FILE', 'messages.Admin.php');
define('LABEL_FILE', 'labels.Admin.php');

?>
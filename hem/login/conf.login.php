<?php

error_reporting(E_ALL);


$DOC_ROOT = $_SERVER['DOCUMENT_ROOT'];

$USER_DIR = '/martin';
$PROJECT_NAME = '/hem';

$APP_ROOT = $DOC_ROOT . $USER_DIR . $PROJECT_NAME;

$PEAR_DIR = $APP_ROOT . '/pear';
$APP_FRAMEWORK_DIR = $APP_ROOT . '/framework';


$PATH = $PEAR_DIR.":".
  $APP_FRAMEWORK_DIR;


ini_set( 'include_path' , ':' . 
	 $PATH . ':' .
	 ini_get( 'include_path' ));

$PHP_SELF = $_SERVER['PHP_SELF'];

$LOGIN_TEMPLATE ='login.html';

$APPLICATION_NAME = 'LOGIN';
$DEFAULT_LANGUAGE = 'US';

$AUTH_DB_URL = 'mysql://test:test@localhost/testlu';

$MIN_USERNAME_SIZE = 1;
$MIN_PASSWORD_SIZE = 1;

$MAX_ATTEMPTS = 5;

$FORGOTTEN_PASSWORD_APP = 'user_manager/run.ForgottenPassword.php';

$APP_MENU = '/';

$TEMPLATE_DIR = $APP_ROOT . '/login';
// TODO: check --> $REL_TEMPLATE_DIR = $USER_DIR . $PROJECT_NAME . '/login';

$WARNING_URL = $USER_DIR . $PROJECT_NAME . '/login/warn.html';

$ON = TRUE;
$OFF = FALSE;


require_once 'class.PHPApplication.php';
require_once 'class.Authentication.php';
require_once 'class.login.php';

define('ERROR_FILE', 'errors.login.php');
define('MESSAGE_FILE', 'messages.login.php');
define('LABEL_FILE', 'labels.login.php');



?>
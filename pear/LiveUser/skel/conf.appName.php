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

$APP_TEMPLATE ='login.html';

$APPLICATION_NAME = 'appName';
$DEFAULT_LANGUAGE = 'US';

$AUTH_DB_URL = 'mysql://test:test@localhost/test';

$TEMPLATE_DIR = $APP_ROOT . '/login';
// TODO: check --> $REL_TEMPLATE_DIR = $USER_DIR . $PROJECT_NAME . '/login';

$ON = TRUE;
$OFF = FALSE;


require_once 'class.PHPApplication.php';
require_once 'class.appName.php';

require_once 'errors.appName.php';
require_once 'messages.appName.php';
require_once 'labels.appName.php';

?>
<?php
error_reporting(E_ALL);

$DOC_ROOT = $_SERVER['DOCUMENT_ROOT'];

$USER_DIR = '/martin';
$PROJECT_NAME = '/hem';

$APP_ROOT = $DOC_ROOT . $USER_DIR . $PROJECT_NAME;

$PEAR_DIR = $APP_ROOT . '/pear';
$APP_FRAMEWORK_DIR = $APP_ROOT . '/framework';


$PATH = $PEAR_DIR.
  $APP_FRAMEWORK_DIR;

ini_set( ' include_path' , ':' . 
	 $PATH . ':' .
	 ini_get( 'include_path' ));


define('ERROR_FILE', 'errors.sampleApp.php');
define('MESSAGE_FILE', 'messages.sampleApp.php');
define('LABEL_FILE', 'labels.sampleApp.php');


$ON = TRUE;
$OFF = FALSE;

$DEFAULT_LANGUAGE='US';

$AUTHENTICATION_URL='login/login.php';

$REL_TEMPLATE_DIR = 
  $USER_DIR.
  $PROJECT_NAME.
  "/framework";

$GLOBALS['SAMPLE_DB_URI'] = 'mysql://test:test@localhost/test';

require_once('class.PHPApplication.php');
require_once('class.sampleApp.php');



?>
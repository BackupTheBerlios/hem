<?php
error_reporting(E_ALL);

$DOC_ROOT = $_SERVER['DOCUMENT_ROOT'];

$USER_DIR = '/martin';
$PROJECT_NAME = '/hem';

$APP_ROOT = $DOC_ROOT . $USER_DIR . $PROJECT_NAME;
$REL_APP_ROOT =  $USER_DIR . $PROJECT_NAME;

$PEAR_DIR = $APP_ROOT . '/pear';
$APP_FRAMEWORK_DIR = $APP_ROOT . '/framework';


$PATH = $PEAR_DIR.":".
  $APP_FRAMEWORK_DIR;

ini_set( 'include_path' , ':' . 
	 $PATH . ':' .
	 ini_get( 'include_path' ));


define('ERROR_FILE', 'errors.sampleApp.php');
define('MESSAGE_FILE', 'messages.sampleApp.php');
define('LABEL_FILE', 'labels.sampleApp.php');


$LOGO_URL = $REL_APP_ROOT . '/templates/img/logo40.gif';

$MASTER_TEMPLATE = "index.html";
$MASTER_TEMPLATE_DIR = $APP_ROOT . "/templates";
$DEFAULT_CSS = $REL_APP_ROOT . '/templates/default.css';

$AUTH_DB_URL = 'mysql://test:test@localhost/testlu';
$DB_PREFIX = "test_";
$USER_PREF_TBL = $DB_PREFIX . "user_pref";
$TEMPLATE_PREF_ID = "1";


$ON = TRUE;
$OFF = FALSE;

$TEMPLATE = 'sampleApp.html';

$DEFAULT_LANGUAGE='US';

$APP_AUTH_DSN = "mysql://test:test@localhost/testlu";

$AUTHENTICATION_URL=$REL_APP_ROOT . "/" . 'login/run.login.php';

$TEMPLATE_DIR = $APP_ROOT . "/framework";
$REL_TEMPLATE_DIR = 
  $USER_DIR.
  $PROJECT_NAME.
  "/templates";

$GLOBALS['SAMPLE_DB_URI'] = 'mysql://test:test@localhost/test';

require_once('class.PHPApplication.php');
require_once('class.sampleApp.php');



?>
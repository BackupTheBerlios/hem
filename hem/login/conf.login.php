<?php

error_reporting(E_ALL);

// TODO: Recheck projetc wide settings
// --- begin proj wide ---
$DOC_ROOT = $_SERVER['DOCUMENT_ROOT'];

$USER_DIR = '/martin';
$PROJECT_NAME = '/hem';

$APP_ROOT = $DOC_ROOT . $USER_DIR . $PROJECT_NAME;
$REL_APP_ROOT = $USER_DIR . $PROJECT_NAME;

$PEAR_DIR = $APP_ROOT . '/pear';
$APP_FRAMEWORK_DIR = $APP_ROOT . '/framework';


$PATH = $PEAR_DIR.":".
  $APP_FRAMEWORK_DIR;

ini_set( 'include_path' , ':' . 
	 $PATH . ':' .
	 ini_get( 'include_path' ));

$DEFAULT_LANGUAGE = 'US';

$AUTH_DB_URL = 'mysql://test:test@localhost/testlu';
$DB_PREFIX = "test_";
$USER_PREF_TBL = $DB_PREFIX . "user_pref";
$TEMPLATE_PREF_ID = "1";


$MASTER_TEMPLATE = "index.html";
$MASTER_TEMPLATE_DIR = $APP_ROOT . "/templates";
$DEFAULT_CSS = $REL_APP_ROOT . '/templates/default.css';

$ON = TRUE;
$OFF = FALSE;


// --- end proj wide ---

$PHP_SELF = $_SERVER['PHP_SELF'];

$LOGIN_TEMPLATE ='login.html';
$WARNING_URL = $REL_APP_ROOT. '/login/warn.html';

$APPLICATION_NAME = 'LOGIN';
$APP_DIR = '/login';

$REL_APP_PATH = $REL_APP_ROOT . $APP_DIR;

$MIN_USERNAME_SIZE = 1;
$MIN_PASSWORD_SIZE = 1;

$MAX_ATTEMPTS = 5;

$FORGOTTEN_PASSWORD_APP = 'user_manager/run.ForgottenPassword.php';

//$APP_MENU = '/';

$TEMPLATE_DIR = $APP_ROOT . $APP_DIR;
$REL_TEMPLATE_DIR = $REL_APP_ROOT . $APP_DIR;
// TODO: check --> $REL_TEMPLATE_DIR = $USER_DIR . $PROJECT_NAME . '/login';

require_once 'class.PHPApplication.php';
require_once 'class.login.php';

define('ERROR_FILE', 'errors.login.php');
define('MESSAGE_FILE', 'messages.login.php');
define('LABEL_FILE', 'labels.login.php');



?>
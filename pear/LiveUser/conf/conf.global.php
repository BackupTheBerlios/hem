<?php

require_once('passwords.php');
require_once('constants.groups.php');
require_once('constants.rights.php');

error_reporting(E_ALL);

// TODO: Recheck project wide settings
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

$AUTHENTICATION_URL=$REL_APP_ROOT . "/" . 'login/run.login.php';

$AUTH_DB_HOST = 'localhost';
$AUTH_DB_NAME = 'testlu';

$AUTH_DB_URL = "mysql://$AUTH_DB_USER:$AUTH_DB_PASS@$AUTH_DB_HOST/$AUTH_DB_NAME";

$APP_DB_HOST = '';
$APP_DB_NAME = '';

//$APP_DB_URL = "mysql://$APP_DB_USER:$APP_DB_PASS@$APP_DB_HOST/$APP_DB_NAME";
$APP_DB_URL = $AUTH_DB_URL;

$DB_PREFIX = "test_";
$USER_PREF_TBL = $DB_PREFIX . "user_pref";
$USER_ATTR_TBL = $DB_PREFIX . "user_attributes";
$TEMPLATE_PREF_ID = "1";

$LOGO_URL = $REL_APP_ROOT . '/templates/img/logo40.gif';
$MASTER_TEMPLATE = "index.html";
$MASTER_TEMPLATE_DIR = $APP_ROOT . "/templates";
$REL_MASTER_TEMPLATE_DIR = $REL_APP_ROOT . "/templates";
$DEFAULT_CSS = $REL_APP_ROOT . '/templates/default.css';

$ON = TRUE;
$OFF = FALSE;

$PHP_SELF = $_SERVER['PHP_SELF'];

?>
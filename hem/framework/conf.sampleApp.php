<?php
require_once('../conf/global_conf.php');

define('ERROR_FILE', 'errors.sampleApp.php');
define('MESSAGE_FILE', 'messages.sampleApp.php');
define('LABEL_FILE', 'labels.sampleApp.php');


$LOGO_URL = $REL_APP_ROOT . '/templates/img/logo40.gif';

$TEMPLATE = 'sampleApp.html';

//$APP_AUTH_DSN = "mysql://test:test@localhost/testlu";
$APP_AUTH_DSN = $AUTH_DB_URL;

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
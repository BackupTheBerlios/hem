<?php
require_once('../conf/conf.global.php');

$PHP_SELF = $_SERVER['PHP_SELF'];

$LOGIN_TEMPLATE ='login.html';
$WARNING_URL = $REL_APP_ROOT. '/login/warn.html';

$APPLICATION_NAME = 'LOGIN';
$APP_DIR = '/login';

$REL_APP_PATH = $REL_APP_ROOT . $APP_DIR;

$TEMPLATE_DIR = $APP_ROOT . $APP_DIR;
$REL_TEMPLATE_DIR = $REL_APP_ROOT . $APP_DIR;
// TODO: check --> $REL_TEMPLATE_DIR = $USER_DIR . $PROJECT_NAME . '/login';


$MIN_USERNAME_SIZE = 1;
$MIN_PASSWORD_SIZE = 1;

$MAX_ATTEMPTS = 5;

$FORGOTTEN_PASSWORD_APP = 'user_manager/run.ForgottenPassword.php';

//$APP_MENU = '/';


require_once 'class.PHPApplication.php';
require_once 'class.login.php';

define('ERROR_FILE', 'errors.login.php');
define('MESSAGE_FILE', 'messages.login.php');
define('LABEL_FILE', 'labels.login.php');



?>
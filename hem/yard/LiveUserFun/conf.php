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

$AUTH_DB_URL = 'mysql://test:test@localhost/test';

$ON = TRUE;
$OFF = FALSE;

$db_user='test';
$db_pass='test';
$db_host='localhost';
$db_name='testlu';


$dsn = "mysql://$db_user:$db_pass@$db_host/$db_name";


require_once 'class.PHPApplication.php';
require_once 'LiveUser/LiveUser.php';
require_once 'HTML/Template/IT.php';
require_once 'DB.php';

/*$db = DB::connect($dsn);

if (DB::isError($db)) {
    echo $db->getMessage() . ' ' . $db->getUserInfo();
}

$db->setFetchMode(DB_FETCHMODE_ASSOC);*/

$conf =
    array(
        'autoInit' => false,
        'session'  => array(
            'name'     => 'PHPSESSION',
            'varname'  => 'ludata'
        ),
        'login' => array(
            'method'   => 'post',
            'username' => 'handle',
            'password' => 'passwd',
            'force'    => false,
            'function' => '',
            'remember' => 'rememberMe'
        ),
        'logout' => array(
            'trigger'  => 'logout',
            'redirect' => 'index.php',
            'destroy'  => true,
            'method' => 'get',
            'function' => ''
        ),
        'authContainers' => array(
            array(
                'type'          => 'DB',
                'name'          => 'DB_Local',
                'loginTimeout'  => 0,
                'expireTime'    => 3600,
                'idleTime'      => 1800,
                'dsn'           => $dsn,
                'allowDuplicateHandles' => 0,
                'authTable'     => 'liveuser_users',
                'authTableCols' => array(
                    'user_id'    => 'auth_user_id',
                    'handle'     => 'handle',
                    'passwd'     => 'passwd',
                    'lastlogin'  => 'lastlogin',
                    'is_active'  => 'is_active'
                )
            )
        ),
        'permContainer' => array(
            'dsn'        => $dsn,
            'type'       => 'DB_Medium',
            'prefix'     => 'liveuser_'
        )
    );

function logOut()
{
}

function logIn()
{
}
$usr = LiveUser::singleton($conf);

$usr->setLoginFunction('logIn');
$usr->setLogOutFunction('logOut');



$e = $usr->init();
//print_r($e);

?>
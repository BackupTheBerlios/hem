<?php
// BC hack
if (!defined('PATH_SEPARATOR')) {
    if (defined('DIRECTORY_SEPARATOR') && DIRECTORY_SEPARATOR == "\\") {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}

ini_set("include_path", '/Users/martin/htdocs/hem/pear' . PATH_SEPARATOR . ini_get("include_path"));
ini_set("include_path", '/usr/lib/php' . PATH_SEPARATOR . ini_get("include_path"));

echo ini_get("include_path");

require_once 'DB.php';
require_once 'LiveUser/LiveUser.php';
// Plase configure the following file according to your environment

$db_user = 'test';
$db_pass = 'test';
$db_host = 'localhost';
$db_name = 'testlu';

$dsn = "mysql://$db_user:$db_pass@$db_host/$db_name";

$db = DB::connect($dsn);

if (DB::isError($db)) {
    echo $db->getMessage() . ' ' . $db->getUserInfo();
}

$db->setFetchMode(DB_FETCHMODE_ASSOC);


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
            'redirect' => 'home.php',
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

PEAR::setErrorHandling(PEAR_ERROR_RETURN);

$usr = LiveUser::singleton($conf);
$usr->setLoginFunction('logIn');
$usr->setLogOutFunction('logOut');

$e = $usr->init();

if (PEAR::isError($e)) {
//var_dump($usr);
    die($e->getMessage() . ' ' . $e->getUserinfo());
}

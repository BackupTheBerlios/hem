<?php
require_once 'DB.php';
require_once 'LiveUser.php';
// Plase configure the following file according to your environment

$db_user = 'root';
$db_pass = '';
$db_host = 'localhost';
$db_name = 'pear_test';

$dsn = "mysql://$db_user:$db_pass@$db_host/$db_name";

$db = DB::connect($dsn);

if (DB::isError($db)) {
    echo $db->getMessage() . ' ' . $db->getUserInfo();
}

$db->setFetchMode(DB_FETCHMODE_ASSOC);


$conf =
    array(
        'autoInit' => true,
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
                     'required' => array(
                        'auth_user_id' => array('type' => 'text', 'name' => 'auth_user_id'),
                        'handle'       => array('type' => 'text', 'name' => 'handle'),
                        'passwd'       => array('type' => 'text', 'name' => 'passwd')
                     ),
                     'optional' => array(
                        'lastlogin'      => array('type' => 'timestamp', 'name' => 'lastlogin'),
                        'is_active'      => array('type' => 'boolean',   'name' => 'is_active'),
                        'owner_user_id'  => array('type' => 'integer',   'name' => 'owner_user_id'),
                        'owner_group_id' => array('type' => 'integer',   'name' => 'owner_group_id')
                        )
                     )
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
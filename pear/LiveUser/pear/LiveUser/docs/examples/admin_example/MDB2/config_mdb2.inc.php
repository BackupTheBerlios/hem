<?php
// $Id: config_mdb2.inc.php,v 1.1 2004/07/16 13:59:08 mloitzl Exp $
require_once 'MDB2.php';
require_once 'LiveUser.php';
// Plase configure the following file according to your environment

$db_user = 'root';
$db_pass = '';
$db_host = 'localhost';
$db_name = 'pear_test';

$dsn = "mysql://$db_user:$db_pass@$db_host/$db_name";

//$db = MDB2::connect($dsn, array('sequence_col_name' => 'id'));
$db = MDB2::connect($dsn);

if (MDB2::isError($db)) {
    echo $db->getMessage() . ' ' . $db->getUserInfo();
}

$db->setFetchMode(MDB2_FETCHMODE_ASSOC);


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
                'type'          => 'MDB2',
                'name'          => 'MDB2_Local',
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
                     ),
                    'custom' => array (
                        'name'  => array('type' => 'text', 'name' => 'name'),
                        'email' => array('type' => 'text', 'name' => 'email'),
                     )
                )
            )
        ),
        'permContainer' => array(
            'dsn'        => $dsn,
            'type'       => 'MDB2_Medium',
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
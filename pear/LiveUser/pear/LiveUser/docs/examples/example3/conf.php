<?php
// BC hack
if (!defined('PATH_SEPARATOR')) {
    if (defined('DIRECTORY_SEPARATOR') && DIRECTORY_SEPARATOR == "\\") {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}

// set this to the path in which the directory for liveuser resides
// more remove the following two lines to test LiveUser in the standard
// PEAR directory
//$path_to_liveuser_dir = './pear/'.PATH_SEPARATOR;
//ini_set('include_path', $path_to_liveuser_dir.ini_get('include_path'));

// Data Source Name (DSN)
//$dsn = '{dbtype}://{user}:{passwd}@{dbhost}/{dbname}';
$dsn = 'mysql://root:@localhost/pear_test';

$liveuserConfig = array(
    'session'           => array('name' => 'PHPSESSID','varname' => 'loginInfo'),
    'login'             => array('username' => 'handle', 'password' => 'passwd', 'remember' => 'rememberMe'),
    'logout'            => array('trigger' => 'logout', 'destroy'  => true, 'method' => 'get'),
    'cookie'            => array('name' => 'loginInfo', 'path' => '/', 'domain' => '', 'lifetime' => 30, 'secret' => 'mysecretkey'),
    'autoInit'          => true,
    'authContainers'    => array(0 => array(
        'type' => 'DB',
                  'dsn' => $dsn,
                  'loginTimeout' => 0,
                  'expireTime'   => 0,
                  'idleTime'     => 0,
                  'allowDuplicateHandles'  => 1,
                  'passwordEncryptionMode' => 'PLAIN',
                  'authTableCols' => array(
                      'required' => array(
                          'auth_user_id' => array('name' => 'auth_user_id', 'type' => ''),
                          'handle'       => array('name' => 'handle',       'type' => ''),
                          'passwd'       => array('name' => 'passwd',       'type' => ''),
                      ),
                      'optional' => array(
                          'lastlogin'    => array('name' => 'lastlogin',    'type' => ''),
                          'is_active'    => array('name' => 'is_active',    'type' => '')
                      )
                    )
    )
                                ),
    'permContainer' => array(
        'type'   => 'DB_Complex',
        'dsn' => $dsn,
        'prefix' => 'liveuser_'
                )
);

// Get LiveUser class definition
require_once 'LiveUser.php';

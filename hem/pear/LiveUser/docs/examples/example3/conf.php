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
ini_set("include_path", '/Users/martin/htdocs/hem/pear' . PATH_SEPARATOR . ini_get("include_path"));

//$path_to_liveuser_dir = '/Users/martin/htdocs/hem/pear'.PATH_SEPARATOR;
//ini_set('include_path', $path_to_liveuser_dir.ini_get('include_path'));

$dsn = array('username' => 'test',
                    'password'  => 'test',
                    'hostspec'  => 'localhost',
                    'phptype'   => 'mysql',
                    'database'  => 'pear_test');

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
                  'passwordEncryptionMode' => 'PLAIN'
    )
                                ),
    'permContainer' => array(
        'type'   => 'DB_Complex',
        'dsn' => $dsn,
        'prefix' => 'liveuser_'
                )
);

// Get LiveUser class definition
require_once 'LiveUser/LiveUser.php';
?>

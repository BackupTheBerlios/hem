<?php

if (!defined('PATH_SEPARATOR')) {
    if (defined('DIRECTORY_SEPARATOR') && DIRECTORY_SEPARATOR == "\\") {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
 }
define('EMAIL_WEBMASTER', 'krausbn@php.net');

// PEAR path
//$path_to_liveuser_dir = 'path/to/pear/'.PATH_SEPARATOR;
//ini_set('include_path', $path_to_liveuser_dir.ini_get('include_path'));
ini_set("include_path", '/Users/martin/htdocs/hem/pear' . PATH_SEPARATOR . ini_get("include_path"));
ini_set("include_path", '/usr/lib/php' . PATH_SEPARATOR . ini_get("include_path"));


    error_reporting(E_ALL ^ E_NOTICE);

    function php_error_handler($errno, $errstr, $errfile, $errline)
    {
        include_once 'HTML/Template/IT.php';
        $tpl = new HTML_Template_IT();
        $tpl->loadTemplatefile('error-page.tpl.php');

        $tpl->setVariable('error_msg', "<b>$errfile ($errline)</b><br />$errstr");

        $tpl->show();
         exit();
    }
    set_error_handler('php_error_handler');

    require_once 'PEAR.php';

    function pear_error_handler($err_obj)
    {
        $error_string = $err_obj->getMessage() . '<br />' . $err_obj->getUserinfo();
        trigger_error($error_string, E_USER_ERROR);
    }
    PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'pear_error_handler');


    function showLoginForm($liveUserObj = false)
    {
        include_once 'HTML/Template/IT.php';
        $tpl = new HTML_Template_IT();
        $tpl->loadTemplatefile('loginform.tpl.php');

        $tpl->setVariable('form_action', $_SERVER['PHP_SELF']);

        if (is_object($liveUserObj)) {
            if ($liveUserObj->status) {
                switch ($liveUserObj->status) {
                    case LIVEUSER_STATUS_ISINACTIVE:
                        $tpl->touchBlock('inactive');
                        break;
                    case LIVEUSER_STATUS_IDLED:
                        $tpl->touchBlock('idled');
                        break;
                    case LIVEUSER_STATUS_EXPIRED:
                        $tpl->touchBlock('expired');
                        break;
                    default:
                        $tpl->touchBlock('failure');
                        break;
                }
            }
        }

        $tpl->show();
        exit();
    }


    require_once 'DB.php';

    // Data Source Name (DSN)
    $myDB = array('host' => 'localhost',
                'type' => 'mysql',
                'name' => 'pear_test',
                'user' => 'test',
                'pwd'  => 'test'
            );



    $dsn = $myDB['type'] . '://' .
           $myDB['user'] . ':'   .
           $myDB['pwd']  . '@'   .
           $myDB['host'] . '/'   .
           $myDB['name'];

    $db =& DB::connect($dsn, true);
    $db->setFetchMode(DB_FETCHMODE_ASSOC);


    require_once 'HTML/Template/IT.php';
    $tpl = new HTML_Template_IT();


    require_once 'LiveUser/LiveUser.php';
    $LUOptions = array('autoInit'       => true,
                       'login'          => array('function' => 'showLoginForm',
                                               'force'    => true),
                        'logout' => array(
                            'trigger'  => 'logout',
                            'redirect' => '',
                            'destroy'  => true,
                            'method'   => 'get',
                            'function' => ''
                             ),
                        'authContainers' => array(array('type'          => 'DB',
                                                        'connection'    => $db,
                                                        'loginTimeout'  => 0,
                                                        'expireTime'    => 3600,
                                                        'idleTime'      => 1800,
                                                        'allowDuplicateHandles' => 0,
                                                        'authTable'     => 'liveuser_users',
                                                        'authTableCols' => array('user_id'   => 'auth_user_id',
                                                                                'handle'     => 'handle',
                                                                                'passwd'     => 'passwd',
                                                                                'lastlogin'  => 'lastlogin',
                                                                                'is_active'  => 'is_active'
                                                                            )
                                                    )
                                                ),
                        'permContainer'  => array('type'     => 'DB_Complex',
                                                'connection' => $db,
                                                'prefix'     => 'liveuser_')
                    );
    $LU = LiveUser::factory($LUOptions);

    define('AREA_NEWS',          1);
    define('RIGHT_NEWS_NEW',     1);
    define('RIGHT_NEWS_CHANGE',  2);
    define('RIGHT_NEWS_DELETE',  3);
?>

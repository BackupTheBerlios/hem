<?php

$dsn = 'mysql://test:test@localhost/lutest';

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
										     ),
								 'custom' => array ()
								 )
					)
				  ),
        'permContainer' => array(
				 'dsn'        => $dsn,
				 'type'       => 'DB_Medium',
				 'prefix'     => 'liveuser_'
				 )
	);


require_once 'LiveUser.php';

$LU = &LiveUser::factory($conf);

?>
<?php

require_once "conf.ChangeUser.php";

$count = 0;
$thisApp = new ChangeUser(
			array(
			      'app_name' => $APPLICATION_NAME,
			      'app_version'=>'1.0.0',
			      'app_type'=>'WEB',
			      'app_auth_dsn' => $AUTH_DB_URL,
			      'app_db_url'=>$APP_DB_URL,
			      'app_authentication' => TRUE,
			      'app_auto_authenticate' => TRUE,
			      'app_admin_auth' => FALSE,
			      'app_exit_point' => $_SERVER['SCRIPT_NAME'],
			      'app_session_name' => 'PHPSESSION',
			      'app_auto_connect'=>TRUE,
			      'app_debugger' =>$ON,
			      'app_themes' => TRUE
			      )
			);

$thisApp->bufferDebugging();
$thisApp->debug("This is $thisApp->app_name_ application.");
$thisApp->debug(ini_get('include_path'));
$thisApp->run();
$thisApp->debugArray($_SESSION);
$thisApp->dumpDebugInfo();


?>
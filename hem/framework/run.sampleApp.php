<?php
error_reporting(E_ALL);

require_once('conf.sampleApp.php');

$thisApp = new sampleApp(
			 array(
			       'app_name' => 'Sample Application',
			       'app_version' => '1.0.0',
			       'app_type' => 'WEB',
			       'app_auth_dsn' => $APP_AUTH_DSN,
			       'app_db_url' => $APP_AUTH_DSN,
			       'app_authentication' => TRUE,
			       'app_auto_authenticate' => TRUE,
			       'app_auto_connect' => FALSE,
			       'app_exit_point' => $_SERVER['SCRIPT_NAME'],
			       'app_session_name' => 'PHPSESSION',
			       'app_type' => 'WEB',
			       'app_debugger' => $ON,
			       'app_themes' => FALSE
			       )
			 );

$thisApp->bufferDebugging();
$thisApp->debug("This is ".$thisApp->getAppName()." Application");
$thisApp->run();
$thisApp->debug($thisApp->auth_handler_->getUserName());
$thisApp->debugArray($_SESSION);
//$thisApp->debugArray($_SERVER);
//$thisApp->debug($_SERVER['HTTP_ACCEPT_LANGUAGE']);
//$thisApp->debug("Destroying Session");
//session_unset();
//session_destroy();
$thisApp->dumpDebugInfo();



?>
<?php
error_reporting(E_ALL);

require_once('conf.sampleApp.php');


$thisApp = new sampleApp(
			 array(
			       'app_name' => 'Sample Application',
			       'app_version' => '1.0.0',
			       'app_type' => 'WEB',
			       'app_db_url' => $GLOBALS['SAMPLE_DB_URI'],
			       'app_auto_authenticate' => FALSE,
			       'app_auto_check_session' => TRUE,
			       'app_auto_connect' => FALSE,
			       'app_type' => 'WEB',
			       'app_debugger' => $ON
			       )
			 );

$thisApp->bufferDebugging();
$thisApp->debug("This is ".$thisApp->getAppName()." Application");
$thisApp->run();
$thisApp->dumpDebugInfo();



?>
<?php

require_once "conf.appName.php";

$count = 0;
$thisApp = new appName(
			array(
			      'app_name'=>$APPLICATION_NAME,
			      'app_version'=>'1.0.0',
			      'app_type'=>'WEB',
			      'app_db_url'=>$AUTH_DB_URL,
			      'app_auto_authenticate'=>FALSE,
			      'app_auto_check_session'=>FALSE,
			      'app_auto_connect'=>TRUE,
			      'app_debugger'=>$ON
			      )
			);

$thisApp->bufferDebugging();
$thisApp->debug(ini_get('include_path'));
$thisApp->run();
$thisApp->dumpDebugInfo();

?>
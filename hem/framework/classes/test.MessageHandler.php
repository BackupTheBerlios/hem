<?
/* $Id: test.MessageHandler.php,v 1.1 2004/06/08 11:42:51 mloitzl Exp $
 */
error_reporting(E_ALL);

$DOC_ROOT = $_SERVER['DOCUMENT_ROOT'];

$USER_DIR = '/martin';
$PROJECT_NAME = '/hem';

$APP_ROOT = $DOC_ROOT . $USER_DIR . $PROJECT_NAME;

$PEAR_DIR = $APP_ROOT . '/pear';
$APP_FRAMEWORK_DIR = $APP_ROOT . '/framework';
$APP_FRAMEWORK_CLASSES_DIR = $APP_FRAMEWORK_DIR . '/classes';


$PATH = $PEAR_DIR.
  $APP_FRAMEWORK_DIR.
  $APP_FRAMEWORK_CLASSES_DIR;

ini_set( ' include_path' , ':' . 
	 $PATH . ':' .
	 ini_get( 'include_path' ));


$DEFAULT_LANGUAGE='US';


require_once('class.Handler.php');
require_once('class.MessageHandler.php');
require_once('test.MessageHandler.messages');

$params = array ( 'caller' => 'test.MessageHandler');

$msg = new MessageHandler($params);

echo "MessageHandler Version " . $msg->apiVersion() . " loaded <br/>";
echo "Loaded MessageHandler with languagecode " . $DEFAULT_LANGUAGE ." <br/>";

$msg->write('SAMPLE_MSG_CODE');


$DEFAULT_LANGUAGE='DE';

$msg1 = new MessageHandler($params);
echo "Loaded ErrorHandler with languagecode " . $DEFAULT_LANGUAGE ." <br/>";
$msg1->write('SAMPLE_MSG_CODE');


?>
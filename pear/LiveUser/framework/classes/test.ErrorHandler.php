<?

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
require_once('class.ErrorHandler.php');
require_once('test.ErrorHandler.errors');

$params = array ( 'caller' => 'test.ErrorHandler');

$err = new ErrorHandler($params);

echo "ErrorHandler Version " . $err->apiVersion() . " loaded <br/>";
echo "Loaded ErrorHandler with languagecode " . $DEFAULT_LANGUAGE ." <br/>";

$err->alert('SAMPLE_ERR_CODE', '1');


$DEFAULT_LANGUAGE='DE';

$err1 = new ErrorHandler($params);
echo "Loaded ErrorHandler with languagecode " . $DEFAULT_LANGUAGE ." <br/>";
$err1->alert('SAMPLE_ERR_CODE', '1');

// $err->alert();
// $err1->alert();


?>
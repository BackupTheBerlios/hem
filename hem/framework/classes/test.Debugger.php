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

require_once('class.Debugger.php');

$params = array ( 'prefix' => 'test.Debugger',
		  'color' => 'blue',
		  'buffer' => 'FALSE');

$deb = new Debugger($params);

echo "Debugger Version " . $deb->apiVersion() . " loaded <br/>";

$deb->setBuffer();
$deb->write('Sample Message');
$deb->write('$params Array:');
$deb->debugArray($params);

// $deb->resetBuffer();

$deb->write('Sample Message2');

$deb->flushBuffer();

?>
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
require_once('class.LabelHandler.php');
require_once('test.LabelHandler.labels');

$params = array ( 'caller' => 'test.LabelHandler');

$lbl = new LabelHandler($params);

echo "LabelHandler Version " . $lbl->apiVersion() . " loaded <br/>";
echo "Loaded LabelHandler with languagecode " . $DEFAULT_LANGUAGE ." <br/>";

$label=$lbl->write('SAMPLE_LBL_CODE');
echo "\n<input type='submit' value='".$lbl->write('SAMPLE_LBL_CODE')."' name='test' >\n";


$DEFAULT_LANGUAGE='DE';

$lbl1 = new LabelHandler($params);
echo "Loaded LabelHandler with languagecode " . $DEFAULT_LANGUAGE ." <br/>";
echo "\n<input type='submit' value='".$lbl1->write('SAMPLE_LBL_CODE')."' name='test' >\n";

?>
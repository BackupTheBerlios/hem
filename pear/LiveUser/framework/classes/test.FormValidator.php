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

require_once('class.FormValidator.php');

$params = array ( 'prefix' => 'test.FormValidator');

$val = new FormValidator($params);

echo "FormValidator Version " . $val->apiVersion() . " loaded <br/>";


// Fieldname --> Fieldtype
$field_type = array (
         "name" => "text",
	 "date" => "date",
	 "amount" => "number");

// Fieldname --> Fielddata
$field_data = array (
         "name" => "Martin",
	 "date" => "01.03.2004",
	 "amount" => "s200");

// Fieldname --> Importance
$field_required = array (
          "name" => "TRUE",
	  "date" => "FALSE",
	  "amount" => "TRUE");

$ret=$val->checkFields($field_type, $field_data, $field_required);

print_r($ret);

?>
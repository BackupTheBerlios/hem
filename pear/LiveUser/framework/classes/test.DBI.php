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

require_once('DB.php');

require_once('class.DBI.php');

$DB_URL = 'mysql://test:test@localhost/test';

$dbi = new DBI($DB_URL);

echo "DBI Version " . $dbi->apiVersion() . " loaded <br/>";

if( ! $dbi->isConnected())
  {
    echo "Connection failed for $DB_URL: ". $dbi->getError() . "<br/>";
    exit;
  }

$statement = "SELECT ID, text FROM test";

$result = $dbi->query($statement);

if( $result == NULL )
  {
    echo "Database error: " . $dbi->getError() . "<br/>"; 
  }
 else if (! $result->numRows())
   {
     echo "Database error: No rows found<br/>";
   }
 else 
   {
     echo "<pre>ID\text<br/>";
     while($row = $result->fetchRow())
       {
	 echo $row->ID. "\t" . $row->text . "<br/>";
       }
     echo "</pre>";
   }
?>
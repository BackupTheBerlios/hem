<?php

error_reporting(E_ALL);

  // AuthHandler Testclass

$DOC_ROOT = $_SERVER['DOCUMENT_ROOT'];

$USER_DIR = '/martin';
$PROJECT_NAME = '/hem';

$APP_ROOT = $DOC_ROOT . $USER_DIR . $PROJECT_NAME;

$PEAR_DIR = $APP_ROOT . '/pear';
$APP_FRAMEWORK_DIR = $APP_ROOT . '/framework';


$PATH = $PEAR_DIR.":".
  $APP_FRAMEWORK_DIR;


ini_set( 'include_path' , ':' . 
	 $PATH . ':' .
	 ini_get( 'include_path' ));

$DB_URL = 'mysql://test:test@localhost/testlu';

require_once('class.AuthHandler.php');

$auth_handler = new AuthHandler(
				array(
				      'auth_dsn' => $DB_URL,
				      'auth_session_name' => 'PHPSESSION',
				      'auth_exit_page' => $_SERVER['SCRIPT_NAME']
				      )
				);

if($auth_handler->isAuthenticated())
  {
    echo "You're in: ".$auth_handler->getUserName()."<br/>";
    $_SESSION['SESSION_USERNAME']=$auth_handler->getUserName();
    echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?logout=1\">Logout</a>";
  }
else
  {
    echo "<form name='loginForm' action='".$_SERVER['SCRIPT_NAME']."' method=\"post\">";
    echo "Username:<input type=\"text\" name=\"handle\" value=\"\" /><br/>";
    echo "Password:<input type=\"password\" name=\"passwd\" /><br/>";
    echo "Remember me <input type=\"checkbox\" name=\"rememberMe\" /><br/>";
    echo "<input type=\"submit\" value=\"Log-in\" />";

    echo "</form>";
    
  }



?>
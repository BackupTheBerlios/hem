<?php

class loginApp extends PHPApplication
{
  
  function run()
  {
    global $MIN_USERNAME_SIZE, $MIN_PASSWORD_SIZE, $MAX_ATTEMPTS;
    global $APP_MENU;

    $url = $this->getRequestField('url');    

    $this->debug("Login attempts : " . $this->getSessionField('SESSION_ATTEMPTS'));

    if($this->isAuthenticated())
      {
	$this->debug("User already authenticated");
	$this->debug("Redirecting to $url");
	$url = (isset($url)) ? $url : $this->getServer();
	$this->setUID($this->auth_handler_->getUID());
	// TODO: Log Users Acitivity
	header("Location: $url");
      }
    else
      {
	// TODO: Log Users Acitivity
	$this->debug("Authentication failed");
	$this->setSessionField('SESSION_ATTEMPTS', $this->getSessionField('SESSION_ATTEMPTS', '0') +1 );
	$this->displayLogin();
      }
  }
  
  function warn()
  {
    global $WARNING_URL;
    $this->debug("Came to warn the user $WARNING_URL");
    header("Location: $WARNING_URL");
  }
  
  function displayLogin()
  {
   global $LOGIN_TEMPLATE;

   $this->showScreen($LOGIN_TEMPLATE, 'displayLoginScreen', $this->getAppName());

  }

  function displayLoginScreen(& $tpl)
  {
    //    global $TEMPLATE_DIR;
    //   global $LOGIN_TEMPLATE;
    global $MAX_ATTEMPTS;
    // TODO: Check->    global $REL_TEMPLATE_DIR;
    global $email, $url;
    global $PHP_SELF, $FORGOTTEN_PASSWORD_APP;
    
    $this->debug("Now in Display function");


    $url = $this->getRequestField('url');

    if(0)
      //$this->getSessionField("SESSION_ATTEMPTS") > $MAX_ATTEMPTS)
      {
	$this->warn();
      }

    $this->debug("Display login dialog box");
    // TODO: Abstract this!!! --> DONE! Clean up!
    //    require_once('HTML/Template/IT.php');
    //$tpl =& new HTML_Template_IT($TEMPLATE_DIR);
    //    $tpl = new TemplateHandler($TEMPLATE_DIR);
    //    $tpl->loadTemplatefile($LOGIN_TEMPLATE, true, false);

    $tpl->setVar(array(
			    'SELF_PATH' => $PHP_SELF,
			    'PAGE_TITLE' => $this->getAppName(),
			    'ATTEMPTS' => $this->getSessionField("SESSION_ATTEMPTS"),
			    'USERNAME' => $this->getRequestField('handle', ''),
			    'LABEL_USERNAME' => $this->getLabelText('LABEL_USERNAME'),
			    'LABEL_PASSWORD' => $this->getLabelText('LABEL_PASSWORD'),
			    'REDIRECT_URL' => $url,
			    'FORGOTTEN_PASSWORD_APP' => $FORGOTTEN_PASSWORD_APP,
			    'LOGIN_BUTTON' => $this->getLabelText('LOGIN_BUTTON'),
			    'CANCEL_BUTTON' => $this->getLabelText('CANCEL_BUTTON'),
			    'LABEL_FORGOTTEN_PASSWORD' => $this->getLabelText('FORGOTTEN_PASSWORD_APP'),
			    'BASE_URL' => sprintf("%s", $this->getBaseUrl()),
			    'REDIRECT_URL' => sprintf("%s", $url)
			    ));

    //    $tpl->show();
    // TODO: Recheck Return Values
    return 1;
    }
  
  /*  
  function isAuthenticated()
  {
    //    return FALSE;
    return (!empty($_SESSION['SESSION_USERNAME'])) ? TRUE : FALSE;
  }
  */
  /*  function authenticate($user = null, $password = null)
  {
    $authObj = new Authentication($user, $password, $this->app_db_url_);
    
    if($authObj->authenticate())
      {
	$uid = $authObj->getUID();
	$this->debug("SETTING user id to $uid");
        $this->setUID($uid);
	$this->setSessionField('SESSION_ATTEMPTS', '0');
	return TRUE;
      }

    return FALSE;
    } */ 
}




?>
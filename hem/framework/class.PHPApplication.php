<?

/**
 * Include the classes used by the framework
 */
require_once('def.PHPApplication.php');


/**
 * PHP Application Framework Class
 *
 * TODO: Describe me!
 * TODO: Check the config stuff created by kabir
 *       Should be done with a unique method (via globals, or via $params array)
 *       We want to differentiate betwwen Proj wide Conf and App conf
 *       App conf should be hidden from user
 *
 * Errormessages:
 * APP_FAILED -> Very bad, no database connection
 * NO_CSS_FOUND -> Themes support Activated, but no CSS file found
 *
 * @author M.J.Kabir
 * @author Martin Loitzl <martin@loitzl.com>
 *
 */


/* Errormessages used:
 *
 * APP_FAILED
 *
 */
class PHPApplication
{
  function PHPApplication($param = null)
  {
    global $ON, $OFF, $TEMPLATE_DIR, $DEFAULT_LANGUAGE;
    //    global $MESSAGES
    global $DEFAULT_MESSAGE, $REL_APP_PATH, 
      $REL_TEMPLATE_DIR;  //<-- TODO: check paths

    $this->app_name_ = $this->setDefault($param['app_name'], null);
    $this->app_version_ = $this->setDefault($param['app_version'], null);
    $this->app_type_ = $this->setDefault($param['app_type'], null);
    $this->app_db_url_ = $this->setDefault($param['app_db_url'], null);
    $this->app_debug_mode_ = $this->setDefault($param['app_debugger'], $OFF);
    $this->auto_connect_ = $this->setDefault($param['app_auto_connect'], TRUE);

    // NOTE: Deprecated since we use LiveUser    
    //    $this->auto_chk_session_ = $this->setDefault($param['app_auto_check_session'], TRUE);
    //    $this->auto_authenticate_ = $this->setDefault($param['app_auto_authenticate'], TRUE);
    // TODO: check setting
    //    $this->session_ok_ = $this->setDefault($param['app_auto_authenticate'], TRUE);
    $this->error_ = array();
    $this->authorized_ = FALSE;
    $this->language_ = $DEFAULT_LANGUAGE;
    $this->base_url_ = sprintf("%s%s", $this->getServer(), $REL_TEMPLATE_DIR);
    $this->app_path_ = $REL_APP_PATH;
    $this->template_dir_ = $TEMPLATE_DIR;
    //    $this->messages_ = $MESSAGES;
    $this->user_auth_ = $this->setDefault($param['app_authentication'], FALSE);
    $this->user_auto_auth_ = $this->setDefault($param['app_auto_authenticate'], FALSE);
    $this->user_auth_dsn_ = $this->setDefault($param['app_auth_dsn'], FALSE);
    $this->user_auth_logout_page_ = $this->setDefault($param['app_exit_point'], 'index.php');
    $this->user_auth_session_name_ = $this->setDefault($param['app_session_name'], 'PHPSESSION');
    
    $this->app_themes_ = $this->setDefault($param['app_themes'], FALSE);

    if (defined("DEBUGGER_LOADED") && $this->app_debug_mode_ == $ON)
      {
	if(empty($param['debug_color'])) 
	  {
	    $param['debug_color'] = 'red';
	  }
	$this->debugger_ = new Debugger ( array(
					       'color' => $param['debug_color'],
					       'prefix' => $this->app_name_,
					       'buffer' => $OFF) );
      }

    if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
      {
	$language_code = trim(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
	if($language_code == 'de') $this->language_ = "DE";
	switch ($language_code)
	  {
	  case 'en':
	    $this->language_ = "US";
	    break;
	  case 'de':
	    $this->language_ = "DE";
	    break;
	  default:
	    break;
	  }
      }

    $this->has_error_ = null;

    $this->setErrorHandler();
    $this->setMessageHandler();
    $this->setLabelHandler();


    if(! empty($this->app_db_url_) && $this->auto_connect_ && !$this->connect())
      {
	$this->showPopup('APP_FAILED');
      }	  
    

    if(strstr($this->getAppType(), 'WEB'))
      {

	// TODO: Include LiveUser here!!! --> Done: clean up!

	if(defined("AUTH_HANDLER_LOADED") && $this->user_auth_)
	  {
	    $this->setAuthHandler();
	  }

	if($this->user_auto_auth_ && !$this->isAuthenticated()) $this->reauthenticate();

	$this->user_id_ = $this->getSessionField('SESSION_USER_ID', null);

	// TODO: Setup User Object here Done: --> programm User class!
	if($this->dbi_->isConnected() && $this->user_auth_ )
	  {
	    $this->user_ = new User($this->user_id_, $this->dbi_);
	  }
	
	if(defined("THEME_HANDLER_LOADED") && $this->app_themes_ && $this->dbi_->isConnected() )
	  {
	    $this->theme_ = new Theme($this->dbi_);
	  }

      }
  }


  // TODO: Clean up. replaced by 
  /*  function getUserEmail()
  {
    return $this->user_email_;
    }

  function getUserName()
  {
    list($name, $host) = explode('@', $this->getUserEmail());
    return ucwords($name);
    }*/

  function getUID()
  {
    return $this->user_id_;
  }

  function setUID($uid = null)
  {
    $this->setSessionField('SESSION_USER_ID', $uid);
    $this->user_id_ = $uid;
  }


  function setAuthHandler()
  {
    // TODO: create conf here;
    $conf = array(
		  'auth_dsn' => $this->user_auth_dsn_,
		  'auth_exit_page' =>  $this->user_auth_logout_page_,
		  'auth_session_name' => $this->user_auth_session_name_ 
		  );

    
    $this->auth_handler_ = new AuthHandler($conf);
  }

  function isAuthenticated()
  {
    return $this->auth_handler_->isAuthenticated();
  }


  function reauthenticate()
  {
    global $AUTHENTICATION_URL;
    header("Location: $AUTHENTICATION_URL?url=$this->self_url_");
  }

  function getBaseUrl()
  {
    return $this->base_url_;
  }

  function getServer()
  {
    $this->setUrl();
    return $this->server_;
  }

  function getAppPath()
  {
    return $this->app_path_;
  }

  function getFQAP()
  {
    // get fully qualified App Path
    return sprintf("%s%s", $this->server_, $this->app_path_);
  }

  function getFQAN($thisApp = null)
  {
    return sprintf("%s/%s", $this->getFQAP(), $thisApp);
  }

  function getTemplateDir()
  {
    return $this->template_dir_;
  }

  function setUrl()
  {
    $row_protocol = $this->getEnvironment('SERVER_PROTOCOL');
    $port = $this->getEnvironment('SERVER_PORT');
    
    if($port == 80)
      {
	$port = null;
      }
    else
      {
	$port = ':' . $port;
      }
    
    $protocol = strtolower(substr($row_protocol, 0, strpos($row_protocol, '/')));
    $this->server_ = sprintf("%s://%s%s", 
			     $protocol,
			     $this->getEnvironment('SERVER_NAME'),
			     $port);

    $this->self_url_ = sprintf("%s://%s%s%s", 
			       $protocol,
			       $this->getEnvironment('SERVER_NAME'),
			       $port,
			       $this->getEnvironment('REQUEST_URI'));

  }

  function terminate()
  {
    if($isset($this->dbi_))
      {
	if($this->dbi_->connected_)
	  {
	    $this->dbi_->disconnect();
	  }
      }
    // TODO: Integrate LiveUser here!!
    session_destroy();
    exit;
  }

  /*  function authenticate($username = null)
  {

    // implement me!

    }*/


  function setErrorHandler()
  {
    if(defined("ERROR_HANDLER_LOADED"))
      {
	$this->err_handler_ = new ErrorHandler( 
					       array(
						     'name' => $this->app_name_,
						     'language' => $this->language_
						     )
					       );
      }
  }


  function getErrorText($code)
  {
    return $this->err_handler_->getMessage($code);
  }


  function showPopup($code, $flag = 0)
  {
    return (defined('ERROR_HANDLER_LOADED')) ? 
      $this->err_handler_->alert($code, $flag) : FALSE;
  }

  
  function setMessageHandler()
  {
    if(defined("MESSAGE_HANDLER_LOADED"))
      {
	$this->msg_handler_ = new MessageHandler( 
					       array(
						     'name' => $this->app_name_,
						     'language' => $this->language_
						     )
					       );
      }
  }

  function getMessageText($code)
  {
    return $this->msg_handler_->getMessage($code);
  }

 function setLabelHandler()
  {
    if(defined("MESSAGE_HANDLER_LOADED"))
      {
	$this->lbl_handler_ = new LabelHandler( 
					       array(
						     'name' => $this->app_name_,
						     'language' => $this->language_
						     )
					       );
      }
  }

  function getLabelText($code)
  {
    return $this->lbl_handler_->getMessage($code);
  }



  // FIXME: check if really needed. Banner is printed automatically by debugger class
  function showDebuggerBanner()
  {
    global $ON;
    
    if ( defined("DEBUGGER_LOADED") && $this->app_debug_mode_ == $ON)
      {
	$this->debugger_->printBanner();
      }
  }


  function bufferDebugging()
  {
    global $ON;
    
    if( defined("DEBUGGER_LOADED") && $this->app_debug_mode_ == $ON )
    {
      $this->debugger_->setBuffer();
    }
    
  }


  function dumpDebugInfo()
  {
    global $ON;

    if ( defined("DEBUGGER_LOADED") && $this->app_debug_mode_ == $ON)
      {
	$this->debugger_->flushBuffer();
      }
  }

  function debug($msg)
  {
    global $ON;
    
    if( defined("DEBUGGER_LOADED") &&  $this->app_debug_mode_ == $ON)
      {
	$this->debugger_->write($msg);
      }
  }


  function debugArray($hash = null)
  {
    global $ON;
    
    if( defined("DEBUGGER_LOADED") &&  $this->app_debug_mode_ == $ON)
      {
	$this->debugger_->debugArray($hash);
      }
  }

  function run()
  {
    // implement me!!!
    $this->writeln("You need to overwrite this function (PHPApplication::run())!");
  }


  function connect($db_url = null)
  {
    if(empty($db_url))
      {
	$db_url = $this->app_db_url_;
      }
    
    if(defined("DBI_LOADED") && !empty($this->app_db_url_))
      {
	$this->dbi_ = new DBI($db_url);
	return $this->dbi_->isConnected();
      }
  }

  function disconnect()
  {
    $this->dbi_->disconnect();
    return $this->dbi_->isConnected();
  }


  function getAppVersion()
  {
    return $this->app_version_;
  }

  function getAppName()
  {
    return $this->app_name_;
  }

  function getAppType()
  {
    return $this->app_type_;
  }

  // TODO: Convert this to a Session Messaging System!
  function setError($err = null)
  {
    if(isset($err))
      {
	array_push($this->error_, $err);
	$this->has_error_ = TRUE;
	return TRUE;
      }
    else 
      {
	return FALSE;
      }
  }

  function hasError()
  {
    return $this->has_error_;
  }

  function resetError()
  {
    $this->error_ = null;
    $this->has_error_ = FALSE;
  }

  function getError()
  {
    if( $err=array_pop($this->error_) == null)
      {
	$this->has_error_ = FALSE;
	$this->error_ = null;
	return FALSE;
      }
    else
      {
	return $err;
      }
  }

  function getErrorArray()
  {
    return $this->error_;
  }

  // FIXME: check id really needed, output of HTML is not wanted here!
  function dumpArray($a)
  {
    if(strstr($this->getAppType(), 'WEB'))
      {
	echo "<pre>";
	print_r($a);
	echo "</pre>";
      }
    else
      {
	print_r($a);
      }
  }
  

  function dump()
  {
    if(strstr($this->getAppType(), 'WEB'))
      {
	echo "<pre>";
	print_r($this);
	echo "</pre>";
      }
    else
      {
	print_r($this);
      }
  }


  function writeln($msg = null)
  {
    global $WWW_NEWLINE;
    global $NEWINE;

    echo $msg.(strstr($this->app_type_, 'WEB')) ? $WWW_NEWLINE : $NEWLINE;

  }


  // TODO: check if we really need this, should be done by the planned Session Messaging tool
  function showStatus($msg = null, $returnURL = null)
  {
    global $STATUS_TEMPLATE;
    $tpl =& new HTML_Template_IT($this->template_dir_);
  
    $tpl->loadTemplatefile($STATUS_TEMPLATE, true, true);

    $tpl->setVariable('STATUS_MESSAGE', $msg );

    if(!preg_match('/^http:/', $returnURL) && (!preg_match('/^\//', $returnURL)))
      {
	$appPath = sprintf("%s/%s", $this->app_path_, $retrunURL);
      }
    else
      {
	$appPath = $returnURL;
      }

    $tpl->setVariable('RETURN_URL', $app_path);
    $tpl->setVariable('BASE_URL', $this->getBaseUrl());

    $tpl->get();
  }

  function getLocDate($timestamp)
  {
    switch ($this->language_)
      {
      case 'US':
	return date('Y-m-d');
	break;
      case 'DE':
	return date('d.m.Y');
	break;
      default:
	return date('Y-m-d');
	break;
      }
  }

  function getLocTime($timestamp)
  {
    switch ($this->language_)
      {
      case 'US':
	return date('g:i:s a');
	break;
      case 'DE':
	return date('H:i:s');
	break;
      default:
	return date('Y-m-d');
	break;
      }
  }

  function getEnvironment($key)
  {
    return !empty($_SERVER[$key]) ? $_SERVER[$key] : null;
  }


  // for security reasons. only requestfield is not good for sensitive data
  // can be tricked with url hacking
  function getPostRequestField($field, $default)
  {
    return (! empty($_POST[$field] )) ? $_POST[$field] : $default;
  }

  function getGetRequestField($field, $default)
  {
    return (! empty($_GET[$field] )) ? $_GET[$field] : $default;
  }

  // just use for insensitive Data
  // can be hacked with url hacking
  function getRequestField($field, $default = null)
  {
    return (! empty($_REQUEST[$field] )) ? $_REQUEST[$field] : $default;
  }

  function getSessionField($field, $default = null)
  {
    return (! empty($_SESSION[$field] )) ? $_SESSION[$field] : $default;
  }

  function setSessionField($field, $value = null)
  {
    $_SESSION[$field] = $value;
  }

  function setDefault($value, $default)
  {
    return (isset($value)) ? $value : $default ;
  }

  function getFileExtension($filename)
  {
    return substr(basename($filename), strpos(basename($filename), ".") + 1);
  }

  function showScreen($template_file, $func = null, $app_name)
  {
    
    $template =new TemplateHandler($this->template_dir_);
    $this->setupScreen($template, $template_file);
    
    $this->debug("Template File: ".$template_file);

    if ($func != null)
      {
	$this->debug("Calling Display function");
	$status = $this->$func($template);
      }
    if($status == TRUE)
      {
	$this->debug("Showing Page");
	$this->showPage($template->get());
      }
  }

  function setupScreen(&$t, $template_file)
  {
    $t->loadTemplatefile($template_file, true, true);
  }


  // TODO: implement th whole Theme Story! This one just does nothing!
  function showPage($content = null)
  {
    global $MASTER_TEMPLATE_DIR, $MASTER_TEMPLATE, $LOGO_URL;

    if($this->app_themes_ == TRUE)
      {
	if(isset($MASTER_TEMPLATE_DIR) && isset($MASTER_TEMPLATE))
	  {
	    $this->debug("Showing template");
	    $this->mTempl = new TemplateHandler($MASTER_TEMPLATE_DIR);
	    $this->setupScreen($this->mTempl, $MASTER_TEMPLATE);
	    
	    
	    $this->mTempl->setVar(array(
					'BASE_URL' => $this->getBaseURL(),
					'LOGO_URL' => $LOGO_URL,
					'CSS_FILE' => $this->getCSS(),
					'PAGE_TITLE' => $this->getAppName(),
					'CONTENT' => $content
		
					
					));
	    $this->mTempl->show();
	  }
      }
    else
      {
	echo $content;
      }
  }

  function getCSS()
  {
    global $DEFAULT_CSS;
    // Get users preferenced css

    if( ($this->user_id_ != null) && isset($this->theme_) )
      {
	$user_theme_id = $this->user_->getThemeID();
	if( $this->user_->getThemeID() )
	  {
	    $this->debug("This Users CSS: ".$this->theme_->getThemeCSS($user_theme_id));
	    return $this->theme_->getThemeCSS($user_theme_id);
	    //return 1; $this->theme_->getCSSFile();
	  }
	else
	  {
	    return $DEFAULT_CSS;
	  }
      }
    else 
      {
	if(isset($DEFAULT_CSS))
	  {
	    return $DEFAULT_CSS;
	  }
	else
	  {
	    $this->setError($NO_CSS_FOUND);
	    return FALSE;
	  }
      }


  }
  
}
?>
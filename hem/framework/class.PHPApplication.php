<?


/*
 * Abstract PHP Application Class
 * by M.J.Kabir
 * adopted by Martin Loitzl
 *
 */


/* Errormessages used:
 *
 * APP_FAILED
 * UNAUTHORIZED_ACCESS
 *
 */

if(!defined("DEBUGGER_LOADED") && !empty($DEBUGGER_CLASS))
  {
    include_once $DEBUGGER_CLASS;
  }


class PHPApplication
{
  function PHPApplication($param = null)
  {
    global $ON, $OFF, $TEMPLATE_DIR;
    global $MESSAGES, $DEFAULT_MESSAGE, $REL_APP_PATH, 
      $REL_TEMLPLATE_DIR;  //<-- TODO: check paths

    $this->app_name_ = $this->setDefault($param['app_name'], null);
    $this->app_version_ = $this->setDefault($param['app_version'], null);
    $this->app_type_ = $this->setDefault($param['app_type'], null);
    $this->app_db_url_ = $this->setDefault($param['app_db_url'], null);
    $this->app_debug_mode_ = $this->setDefault($param['app_debugger'], null);
    $this->auto_connect_ = $this->setDefault($param['app_auto_connect'], TRUE);
    $this->auto_chk_session_ = $this->setDefault($param['app_auto_check_session'], TRUE);
    $this->auto_authorize_ = $this->setDefault($param['app_auto_authorize'], TRUE);
    // TODO: check setting
    $this->session_ok_ = $this->setDefault($param['app_auto_authorize'], TRUE);
    $this->error_ = array();
    $this->authorized_ = FALSE;
    $this->language_ = $DEFAULT_LANGUAGE;
    $this->base_url_ = sprintf("%s%s", $this->getServer, $REL_TEMPLATE_DIR);
    $this->app_path_ = $REL_APP_PATH;
    $this->template_dir_ = $TEMPLATE_DIR;
    $this->messages_ = $MESSAGES;

    if (defined("DEBUGGER_LOADED") && $this->debug_mode_ == $ON)
      {
	if(empty($param['debug_color'])) 
	  {
	    $param['debug_color'] = 'red';
	  }
	$this->debugger_ = new Debugger ( array(
					       'color' => $param['debug_color'],
					       'prefix' => $this->app_name_,
					       'buffer' => $OFF));
      }

    $this->has_error_ = null;

    $this->setErrorHanlder();

    if(strstr($this->getType(), 'WEB'))
      {

	// TODO: Include LiveUser here!!!
	session_start();
	$this->user_id_ = (! empty($_SESSION['SESSION_USER_ID'])) ? $_SESSION['SESSION_USER_ID'] : null;
	$this->user_name_ = (! empty($_SESSION['SESSION_USERNAME'])) ? $_SESSION['SESSION_USERNAME'] : null;
	$this->user_email_ = (! empty($_SESSION['SESSION_USERNAME'])) ? $_SESSION['SESSION_USERNAME'] : null;
	$this->setUrl();

	if($this->auto_chk_session_) $this->checkSession();
	
	if(! empty($this->app_db_url_) && $this->auto_connect_ && !$this->connect())
	  {
	    $this->alert('APP_FAILED');
	  }	  
	
	if($this->auto_authorize_ && !$this->authorize())
	  {
	    $this->alert('UNAUTHORIZED_ACCESS');
	  }
      }
    
  }


  function getUserEmail()
  {
    return $this->user_email_;
  }

  function getUserName()
  {
    list($name, $host) = explode('', $this->getUserEmail());
    return ucwords($name);
  }

  function checkSession()
  {
    if($this->session_ok_ == TRUE)
      {
	return TRUE;
      }
    
    if(!empty($this->user_name_))
      {
	$this->session_ok_ = TRUE;
      }
    else
      {
	$this->session_ok_ = FALSE;
	$this->reauthenticate();
      }
    
    return $this->session_ok_;
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
			     $this->getEnvironment('HTTP_POST'),
			     $port,
			     $this->getEnvironment('REQUEST_URI'));

    $this->self_url_ = sprintf("%s://%s%s%s", 
			       $protocol,
			       $this->getEnvironment('HTTP_POST'),
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

  function authenticate($username = null)
  {

    // implement me!

  }


  function setErrorHandler()
  {
    if(defined("ERROR_HANDLER_LOADED"))
      {
	$this->err_handler_ = new ErrorHandler( array(
						      'name' => $this->app_name_));
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
	$this->msg_handler_ = new MessageHandler( array(
						      'name' => $this->app_name_));
      }
  }

  function getMessageText($code)
  {
    return $this->msg_handler_->getMessage($code);
  }


  // FIXME: check if really needed. Banner is printed automatically by debugger class
  function showDebuggerBanner()
  {
    global $ON;
    
    if ( defined("DEBUGGER_LOADED") && $this->debug_mode_ == $ON)
      {
	$this->debugger_->printBanner();
      }
  }


  function bufferDebugging()
  {
    global $ON;
    
    if( defined("DEBUGGER_LOADED") && $this->debug_mode_ == $ON )
    {
      $this->debugger_->setBuffer();
    }
    
  }


  function dumpDebugInfo()
  {
    global $ON;

    if ( defined("DEBUGGER_LOADED") && $this->debug_mode_ == $ON)
      {
	$this->debugger_->flushBuffer();
      }
  }

  function debug($msg)
  {
    global $ON;
    
    if( defined("DEBUGGER_LOADED") &&  $this->debug_mode_ == $ON)
      {
	$this->debugger_->write($msg);
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
    if(strstr($this->getType(), 'WEB'))
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
    if(strstr($this->getType(), 'WEB'))
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



}
?>
<?


/*
 * Abstract PHP Application Class
 * by M.J.Kabir
 * adopted by Martin Loitzl
 *
 */


if(defined("DEBUGGER_LOADED") && !empty($DEBUGGER_CLASS))
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
	$this->debugger = new Debugger ( array(
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


      }
    

  }


}

?>
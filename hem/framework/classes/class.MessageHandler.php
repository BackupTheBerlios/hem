<?

/* 
 * Multilanguage Message Handler class
 * from Martin Loitzl
 */


define ('MESSAGE_HANDLER_LOADED', TRUE);

class MessageHandler extends Handler
{

  var $version_ = '1.0.1';
  // V.1.0.1  07.06.04 : New Handler Superclass

  function MessageHandler($params=null)
  {
    Handler::Handler($params);

    return $this->loadMessageCode();
  }


  function write($code = null, $flag = null)
  {
    $msg = $this->getMessage($code);
    if(!strlen($msg))
      {
	$msg = $code;
      }
    // TODO: Add some severity Information, e.g. colored style!
    if ($flag == null) 
      {
	echo "$msg<br/>";
      }
  }


  function loadMessageCode()
  {
    if(!defined("MESSAGE_FILE"))
      {
	return FALSE;
      }
    else
      {
	require_once MESSAGE_FILE;

	while (list($key, $value) = each ($MESSAGES[$this->language_]))
	  {
	    $this->messages_[$key] = $value;
	  }
	return TRUE;
      }
  }

  function apiVersion()
  {
    return $this->version_;
  }

}



?>
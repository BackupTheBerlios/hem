<?

/* 
 * Multilanguage Message Handler class
 * from Martin Loitzl
 */


define ('LABEL_HANDLER_LOADED', TRUE);

class LabelHandler extends Handler
{

  var $version_ = '1.0.1';
  // V.1.0.1  07.06.04 : New Handler Superclass

  function labelHandler($params=null)
  {
    Handler::Handler($params);

    $this->loadLabelCode();
  }


  function write($code = null, $flag = null)
  {
    $msg = $this->getMessage($code);
    if(!strlen($msg))
      {
	$msg = $code;
      }
    if ($flag == null) 
      {
	return "$msg";
      }
  }


  function loadLabelCode()
  {
    global $LABELS;

    if(empty($LABELS[$this->language_]))
      {
	return FALSE;
      }
    else
      {
	while (list($key, $value) = each ($LABELS[$this->language_]))
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
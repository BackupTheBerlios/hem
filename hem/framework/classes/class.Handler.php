<?

/* 
 * Multilanguage Error Handler class
 * from Martin Loitzl
 * V 1.0.0
 */

define('HANDLER_LOADED',TRUE);

class Handler {

  var $version_ = '1.0.0';
  
  function Handler($params = null) 
  {

    global $DEFAULT_LANGUAGE;

    $DEFAULT_LANGUAGE = (!empty($DEFAULT_LANGUAGE)) ? $DEFAULT_LANGUAGE : 'US';

    $this->language_ = (!empty($params['language'])) ? $params['language'] : $DEFAULT_LANGUAGE;
    $this->caller_class_ = (!empty($params['caller'])) ? $params['caller'] : null ;
    $this->messages_ = array();
    }


  function loadMessageCode() 
  {    // implement me according to your needs
  }


  function getMessage($code)
  {
    if(isset($code))
      {
	// works only for associative Arrays!
	if(is_array($code))
	  {
	    $out = array();
	    foreach ($code as $entry)
	      {
		array_push($out, $this->messages_[$entry]);
	      }
	    return $out;
	  }
	else 
	  {
	    return (!empty($this->messages_[$code])) ? $this->messages_[$code] : null;
	  }
      }
    else 
      {
	return (!empty($this->messages_['MISSING'])) ? $this->messages_['MISSING'] : null;
      }
  }

  function apiVersion()
  {
    return $version_;
  }


}
?>
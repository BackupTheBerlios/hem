<?
/* 
 * Fullfeartured debugger  class
 * from M.J.Kabir
 * adapted by Martin Loitzl
 */

define ('DEBUGGER_LOADED', TRUE);

class Debugger {
  
  var $debugger_color_ = 'red';

  var $version_= '1.0.0';

  function Debugger($params = null)
  {

    $this->color_ = $params['color'];
    $this->prefix_ = $params['prefix'];
    $this->line_ = 0;
    $this->buffer_str_ = null;
    $this->buffer_ = ($params['buffer'] == 'TRUE') ? TRUE : FALSE;
    $this->banner_printed_ = FALSE;

  }

  function printBanner()
  {
    if ($this->banner_printed_ == TRUE)
      {
	return FALSE;
      }

    $out = "<br/><br/><font color = '$this->debugger_color_'>".
      "<strong>Debugger started for $this->prefix_</strong>".
      "</font><br/><hr/>";

    if($this->buffer_ == TRUE) 
      {
	$this->buffer_str_ .= $out;
	$this->banner_printed_ = TRUE;
      }
    else 
      {
	echo $out;
	$this->banner_printed_ = TRUE;
      }
    
    return TRUE;
            
  }

  function write($msg)
  {
    $out = sprintf("<div style='align:left;'><font color='%s'>%03d &nbsp;</font>".
		   "<font color='%s'>%s</font><br/></div>\n",
		   $this->debugger_color_,
		   $this->line_++,
		   $this->color_,
		   strip_tags(htmlentities(trim($msg))));
    
    $this->printBanner();
    if($this->buffer_ == TRUE) 
      {
	$this->buffer_str_ .= $out;
      }
    else 
      {
	echo $out;
      }
    
    return TRUE;
    
  }

  function debugArray($hash = null)
  {
    while(list($k, $v) = each($hash))
      {
	$this->write("$k = $v");
      }
  }
  

  function setBuffer()
  {
    $this->buffer_ = TRUE;
  }

  function resetBuffer()
  {
    $this->buffer_ = FALSE;
    $this->buffer_str_ = null;
  }

  function flushBuffer()
  {
    $this->buffer_ = FALSE;
    $this->printBanner();
    echo $this->buffer_str_;
  }

  function apiVersion()  
  {
    return $this->version_;
  }

}



?>
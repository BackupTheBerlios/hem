<?

/* 
 * Database abstraction class
 * Uses PEAR::DB
 * from M.J.Kabir
 * adapted by Martin Loitzl
 * 2004-06-08
 */


define('DBI_LOADED', TRUE);

class DBI 
{



  /*
   * DODO: Doc!!
   *
   */
  var $version_ = "1.0.0";


  /*
   * DODO: Doc!!
   *
   */
  function DBI($DB_URL) 
  {

    $this->db_url_ = $DB_URL;

    $this->connect();

    if($this->connected_) 
      {

      $this->dbh_->setFetchMode(DB_FETCHMODE_OBJECT);

      }
  }


  /*
   * DODO: Doc!!
   *
   */
  function connect() 
  {

    $status = $this->dbh_ = DB::connect($this->db_url_);

    if (DB::isError($status))
      {    
	$this->connected_ = FALSE;
	$this->error_ = $status->getMessage();
      }
    else
      {
	$this->connected_ = TRUE;
      }

    return $this->connected_ ;
  }

  /*
   * DODO: Doc!!
   *
   */
  function isConnected() 
  {
    return $this->connected_;
  }
  
  /*
   * DODO: Doc!!
   *
   */
  function disconnect()
  {
    if (isset($this->dbh_))
      {
	$this->dbh_->disconnect();
	return TRUE;
      }
    else
      {
	return FALSE;
      }
  }
  
  /*
   * DODO: Doc!!
   *
   */
  function query($statement)
  {
    $result = $this->dbh_->query($statement);
    
    if(DB::isError($result)) 
      {
	$this->setError($result->getMessage());
	return FALSE;
      }
    else 
      {
	return $result;
      }
    
  }
  
  
   /*
   * DODO: Doc!!
   *
   */
  function setError($msg = null) 
  {
    global $TABLE_DOES_NOT_EXIST, $TABLE_UNKNOWN_ERROR;
    $this->error_ = $msg;
    
    if(strpos($msg, 'no such table'))
      {
	$this->error_type_ = $TABLE_DOES_NOT_EXIST;
      }
    else 
      {
	$this->error_type_ = $TABLE_UNKNOWN_ERROR;
      }
  }
  

  /*
   * DODO: Doc!!
   *
   */
  function isError()
  {
    return (!empty($this->error_)) ? TRUE : FALSE ;
  }
  
  /*
   * DODO: Doc!!
   *
   */
  function isErrorType($type = null) 
  {
    return ($this->error_type_ == $type) ? TRUE : FALSE ;
  }
  
  /*
   * DODO: Doc!!
   *
   */
  function getError()
  {
    return $this->error_;
  }
  

  /*
   * DODO: Doc!!
   *
   */
  function quote($str)
  {
    return "'" . $str . "'";
  }
  
  /*
   * DODO: Doc!!
   *
   */
  function apiVersion()
  {
    return $this->version_;
  }
}
<?php
define('USER_CLASS_LOADED', TRUE);

class User
{
  function User($uid, & $dbh)
  {
    global $USER_PREF_TBL, $TEMPLATE_PREF_ID;

    $this->user_pref_table_ = $USER_PREF_TBL;
    $this->template_pref_id_ = $TEMPLATE_PREF_ID;

    $this->user_id_ = $uid;
    $this->dbh_ = $dbh;

    if($this->user_id_ != null ) $this->init();

    // setup User
    // TODO: setUserName, email,...

    // TODO: set Users Theme
  }


  function init()
  {
    $this->getThemeID();
  }

  function getThemeID()
  {
     // check if already set, so no db query needed
    if(!empty($this->theme_id_)) 
      {
	return $this->theme_id_;
      }
    else 
      {
	$query = "SELECT value FROM $this->user_pref_table_ WHERE ".
	  "pref_id = $this->template_pref_id_ AND ".
	  "auth_user_id = ".$this->dbh_->quote($this->user_id_)."";
	
	$result = $this->dbh_->query($query);
	if($result != null && $result->numRows() > 0) 
	  {
	    $row = $result->fetchRow();
	    $this->theme_id_ = $row->value;
	    return $this->theme_id_;
	  }
	return FALSE;
      }
  }

  function setThemeID($theme_id)
  {
    if($this->getThemeID() == FALSE && !empty($this->user_id_ )) 
      {
	$query = "INSERT INTO $this->user_pref_table_ ".
	  "(auth_user_id, pref_id, value) ".
	  "VALUES ".
	  "(".
	  $this->dbh_->quote($this->user_id_).", ".
	  $this->template_pref_id_.", ".
	  $this->dbh_->quote($theme_id).
	  ")";
	echo $query;
      }
    else if($this->getThemeID() != $theme_id)
      {
	$query = "UPDATE $this->user_pref_table_ SET value = ".
	  $this->dbh_->quote($theme_id) ." WHERE ".
	  "auth_user_id = ".$this->dbh_->quote($this->user_id_)." AND ".
	  "pref_id = ".$this->template_pref_id_;
	echo $query;
      }
    else
      {
	$query = "";
      }
 
    if (!empty($query))
      {
	$result = $this->dbh_->query($query);
      }
    else
      {
	$result = FALSE;
      }
    
    if($result == TRUE) 
      {
	return TRUE;
      }
    else
      {
	return FALSE;
      }
  }

  function getEmail()
  {


  }

  function setEmail()
  {

  }


  function getUserName()
  {

  }


  function getRealName()
  {

  }

  
}


?>
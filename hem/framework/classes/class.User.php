<?php
define('USER_CLASS_LOADED', TRUE);

class User
{
  function User($uid, $dbh)
  {

    $this->user_id_ = $uid;
    $this->dbh_ = $dbh;

    // setup User
    // TODO: setUserName, email,...

    // TODO: set Users Theme



  }


  function getTheme()
  {
    global $USER_PREF_TBL, $TEMPLATE_PREF_ID;
    // check if already set, so no db query needed
    if(!empty($this->theme_id_)) 
      {
	return $this->theme_id_;
      }
    else 
      {
	$query = "SELECT value FROM $USER_PREF_TBL WHERE ".
	  "pref_id = $TEMPLATE_PREF_ID AND ".
	  "auth_user_id = $this->user_id_";
	echo $query;
	$this->dhb_->query($query);
      }
  }

  function setTheme()
  {

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
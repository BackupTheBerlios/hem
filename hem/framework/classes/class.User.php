<?php
define('USER_CLASS_LOADED', TRUE);

class User
{
  function User($uid = null, & $dbh)
  {
    global $USER_ATTR_TBL;
    global $USER_PREF_TBL, $TEMPLATE_PREF_ID;


    $this->user_attr_table_ = $USER_ATTR_TBL;
    $this->user_pref_table_ = $USER_PREF_TBL;
    $this->template_pref_id_ = $TEMPLATE_PREF_ID;

    $this->user_id_ = $uid;
    $this->dbh_ = $dbh;

    $this->user_tbl_fields_ = array(
				    'auth_user_id' => 'text',
				    'first_name' => 'text',
				    'last_name' => 'text',
				    'email' => 'text',
				    'street' => 'text',
				    'no' => 'text',
				    'city' => 'text',
				    'zip' => 'text',
				    'country' => 'text',
				    'phone' => 'text',
				    'comment' => 'text'
				    );

    if($this->user_id_ != null ) $this->init();


    // setup User
    // TODO: setUserName, email,...

    // TODO: set Users Theme
  }


  function init()
  {
    $this->getThemeID();
    $this->getUserdata();
  }

  function getUserFieldList()
  {
    return array_keys($this->user_tbl_fields_);
  }


  function makeUpdateKeyValuePairs($fields = null, $data = null)
  {
    $set_values = array();

    if(($fields != null) && ($data != null))
    {
      while(list($k, $v) = each($fields))
	{
	  if(!strcmp($v, 'text'))
	    {
	      $v = $this->dbh_->quote(addslashes($data[$k]));
	      $set_values[] = "$k = $v";
	    }
	  else
	    {
	      $set_values[] = "$k = $data[$k]";
	    }
	}
      return implode(', ', $set_values);
    }
    else return FALSE;
  }


  function getUserData()
  {
    $fields = $this->getUserFieldList();
    $fields_string = implode(',', $fields);

    if($this->user_id_ != null)
      {
	$query = "SELECT $fields_string FROM $this->user_attr_table_ WHERE auth_user_id = '$this->user_id_'";

	$result = $this->dbh_->query($query);

	if( ($result != null) && ($result->numRows() > 0) )
	  {
	    $row = $result->fetchRow();

	    foreach($fields as $f)
	      {
		$this->$f = $row->$f;
		$this->user_data_array_[$f] = $row->$f;
	      }
	    return $this->user_data_array_;
	  }
	return FALSE;
      }
    return FALSE;
  }

  function addUserData($data = null)
  {
    $fields = $this->user_tbl_fields_;
    $fields_string = implode(',', $this->getUserFieldList());

    $value_list = array();
    
    if($data != null)
      {
	while (list ($k, $v) = each($fields))
	  {
	    if(!strcmp($v, 'text'))
	      {
		$value_list[] = $this->dbh_->quote($data[$k]);
	      }
	    else
	      {
		$value_list[] = $data[$k];
	      }
	  }
	$value_string = implode(',', $value_list);

	$query = "INSERT INTO $this->user_attr_table_ ($fields_string) VALUES ($value_string)";

	$result = $this->dbh_->query($query);

	if($result == TRUE)
	  return TRUE;
	else return FALSE;

	echo $query;
      }
    else return FALSE;
  }

  function updateUserData($data = null)
  {

    if( ($data != null) && ($this->user_id_ == $data['auth_user_id']) )
      {
	$fields = $this->user_tbl_fields_;

	$key_value_pairs = $this->makeUpdateKeyValuePairs($fields, $data);

	$query = "UPDATE $this->user_attr_table_ SET $key_value_pairs WHERE auth_user_id = '$this->user_id_'";

	$result = $this->dbh_->query($query);

	return $result;

      }
    // TODO: Admin changes here!
    else
      return FALSE;
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
    else if($this->getThemeID() != $theme_id && !empty($this->user_id_ ))
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
	if($result == TRUE) return TRUE;
	else return FALSE;
      }
    else
      {
	return FALSE;
      }
  }

  function getEmail()
  {
    return (isset($this->email)) ? $this->email : '';
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
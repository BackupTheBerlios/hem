<?php

  // FIXME: Not a real impl.
  // DUMMY CLASS!!!

class Authentication {

  function Authentication($user, $pass, $db_url)
  {

    $this->user = $user;
    $this->password = $pass;

  }


  function authenticate()
  {
    if($this->user == "martin" || $this->password == "test" )
      {
	$_SESSION['SESSION_USERNAME'] = $this->user;
	return TRUE;
      }

    return FALSE;

  }

  function getUID()
  {
    return 3;
  }
  
}


?>
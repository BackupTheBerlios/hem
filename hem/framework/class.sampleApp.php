<?php


class sampleApp extends PHPApplication
{

  function sampleApp($params)
  {
    PHPApplication::PHPApplication($params);
  }

  function run()
  {
    $this->doSomething();
  }

  /*
  function authenticate($name = null)
  {
    return TRUE;
    }*/


  function doSomething()
  {
    echo $this->getMessageText('SOME_MSG');
    echo "<br/>";
    if($this->auth_handler_->isAuthenticated())
      {
	echo $this->getMessageText('AUTH_AS').": ".$this->auth_handler_->getUserName()."<br/>";
	echo $this->getMessageText('MSG_UID').": ".$this->getUID()."<br/>";
	echo $this->getMessageText('LAST_LOGIN').": ".$this->getLocDate($this->auth_handler_->getLastLogin())." ".$this->getLocTime($this->auth_handler_->getLastLogin())."<br/>";
	echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?logout=1\">".$this->getLabelText('LOGOUT_BUTTON')."</a>";
      }
    

  }



}



?>
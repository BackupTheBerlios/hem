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
	echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?logout=1\">".$this->getLabelText('LOGOUT_BUTTON')."</a>";
      }
    

  }



}



?>
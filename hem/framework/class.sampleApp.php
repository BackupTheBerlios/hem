<?php


class sampleApp extends PHPApplication
{

  function sampleApp($params)
  {
    PHPApplication::PHPApplication($params);
  }

  function run()
  {
    $this->debug($this->getAppName()." running");
    $this->debug("Calling doSomething");
    $this->doSomething();
  }


  function authenticate($name = null)
  {
    return TRUE;
  }


  function doSomething()
  {
    echo "Hi! I'm doing something<br/>";
  }


}



?>
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

  function showDoSomething(& $tpl)
  {

    $this->debug("showDoSomething called");

    //    $theme_id = '02ec099f2d602cc49c099f2d6nwa8a5f';
    //    $this->debug("Setting Usertheme to ".$theme_id);
    //    $this->user_->setThemeID($theme_id);
    $this->debug("User Theme is: ". $this->user_->getThemeID());

    $user_data = array(
				    'auth_user_id' => '29214857b12575501c5c731353c7217e',
				    'first_name' => 'Alois',
				    'last_name' => 'Dengg',
				    'email' => 'ali@sbox.tugraz.at',
				    'street' => 'Infeldgasse',
				    'no' => '16c',
				    'city' => 'Graz',
				    'zip' => '8010',
				    'country' => 'Austria',
				    'phone' => '0676 212312321',
				    'comment' => 'Nixi'
				    );

    $this->user_->updateUserData($user_data);


    $this->debugArray($this->user_->getUserData());

    $some_id = $this->getUniqueId();

    $this->debug("SomeID: ". $some_id . " with length: " . strlen($some_id));

    $content =$this->getMessageText('SOME_MSG');
    $content .= "<br/>";
    if($this->auth_handler_->isAuthenticated())
      {
	$tpl->setCurrentBlock('side_box');
	
	$tpl->setVar(array(
			   'BOX_TITLE' => $this->getLabelText('AUTH_AS_TITLE'),
			   'BOX_CONTENT' => $this->auth_handler_->getUserName()."<br/><a href=\"".$_SERVER['SCRIPT_NAME']."?logout=1\">".$this->getLabelText('LOGOUT_BUTTON')."</a>"
			   
			   ));

	$tpl->parseCurrentBlock('side_box');
	
	$content.= $this->getMessageText('AUTH_AS').": ".$this->auth_handler_->getUserName()." ( ".$this->user_->getEmail()." )<br/>";
	$content.= $this->getMessageText('MSG_UID').": ".$this->auth_handler_->getUID()."<br/>";
	$content.= $this->getMessageText('LAST_LOGIN').": ".$this->getLocDate($this->auth_handler_->getLastLogin())." ".$this->getLocTime($this->auth_handler_->getLastLogin())."<br/>";
	$content.= "<a href=\"".$_SERVER['SCRIPT_NAME']."?logout=1\">".$this->getLabelText('LOGOUT_BUTTON')."</a>";
      }

    $tpl->setCurrentBlock('main_block');

    $tpl->setVar('TITLE', $this->getAppName());

    $tpl->setVar('CONTENT', $content);

    $tpl->parseCurrentBlock('side_box');

    $tpl->setCurrentBlock('main_block');

    $tpl->setVar('TITLE', 'Ganz was andres');

    $tpl->setVar('CONTENT', 'Blog wirds hoffentlich keine ;o)');

    $tpl->parseCurrentBlock('side_box');

    $tpl->setCurrentBlock('side_box');

    $tpl->setVar('BOX_TITLE', $this->getLabelText('SAMPLE_BOX_TITLE'));
    $tpl->setVar('BOX_CONTENT', 'Some text without internationalisation, just to see how far we can get');

    
    $tpl->parseCurrentBlock('side_box');

    return TRUE;
  }

  function doSomething()
  {
    global $TEMPLATE;
    
    $this->debug("doSomething called");
    $this->showScreen($TEMPLATE, 'showDoSomething', $this->getAppName());
  }



}



?>
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

    global $CHANGE_USER_DATA_URL;

    $this->debug("showDoSomething called");

    //    $theme_id = '02ec099f2d602cc49c099f2d6nwa8a5f';
    //    $this->debug("Setting Usertheme to ".$theme_id);
    //    $this->user_->setThemeID($theme_id);
    //    $this->debug("User Theme is: ". $this->user_->getThemeID());


    //    $this->debug("-- Testing Admin Auth Handler --");
    $options = array(
		     'where_is_active' => TRUE 
		     );
    //    $this->debugArray($this->admin_auth_handler_->getGroups($options));

    //    $this->admin_auth_handler_->addGroup('testgroup', 'Group for testing AdminAuthHandler', TRUE);

    //    $this->debugArray($this->admin_auth_handler_->getGroups($options));

    //    $this->debug("## Testing Admin Auth Handler ##");



    $content =$this->getMessageText('SOME_MSG')."<br/>";

    $message_text = '';

    if($this->hasSessionMessages() == TRUE)
      {
	$messages = $this->getAllSessionMessages();

	while($msg = array_pop($messages))
	  {
	    $message_text.=$msg;
	  }
      }


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

    //$this->user_->updateUserData($user_data);


    //    $this->debugArray($this->user_->getUserData());

    //    $some_id = $this->getUniqueId();

    //    $this->debug("SomeID: ". $some_id . " with length: " . strlen($some_id));

    if($this->auth_handler_->isAuthenticated())
      {
	$tpl->setCurrentBlock('side_box');
	
	$tpl->setVar(array(
			   'BOX_TITLE' => $this->getLabelText('AUTH_AS_TITLE'),
			   'BOX_CONTENT' => $this->auth_handler_->getUserName()."<br/><a href=\"".$_SERVER['SCRIPT_NAME']."?logout=1\">".$this->getLabelText('LOGOUT_BUTTON')."</a>"
			   
			   ));

	$tpl->parseCurrentBlock('side_box');
	
	$this->debug($CHANGE_USER_DATA_URL);
	
	$content.= $this->getMessageText('AUTH_AS').": ".$this->auth_handler_->getUserName()." ( ".$this->user_->getEmail()." ) ";
	$content.= "<a href='".$CHANGE_USER_DATA_URL."?url=".$_SERVER['SCRIPT_NAME']."'>".$this->getMessageText('CHANGE_USER_DATA')."</a>  <br/>";
	$content.= $this->getMessageText('MSG_UID').": ".$this->auth_handler_->getUID()."<br/>";
	$content.= $this->getMessageText('LAST_LOGIN').": ".$this->getLocDate($this->auth_handler_->getLastLogin())." ".$this->getLocTime($this->auth_handler_->getLastLogin())."<br/>";
	$content.= "<a href=\"".$_SERVER['SCRIPT_NAME']."?logout=1\">".$this->getLabelText('LOGOUT_BUTTON')."</a>";
      }

    $tpl->setCurrentBlock('main_block');

    $tpl->setVar('TITLE', $this->getAppName());

    $tpl->setVar('MESSAGES', $message_text);

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
<?php



class ChangeUser extends PHPApplication

{

  function ChangeUser($params)
  {
    PHPApplication::PHPApplication($params);
  }


  function run()
  {
    $this->changeUserDriver();

  }

  function changeUserDriver()
  {

    if($this->getPostRequestField('step', null) == '1')
      {
	$submitted_data = $this->getPostRequestField('data', null);
	if( $submitted_data['auth_user_id'] == $this->getUID())
	  {
	    $this->changeUserData();
	  }
	else if(0) // check if user is Admin
	  {
	    $this->changeUserData();
	  }
	else
	  {
	    $this->addSessionMessage("DO_NOT_CHANGE_OTHER_USER");
	  }
      }
    else 
      {
	$this->changeForm();
      }
    

  }


  function changeForm()
  {
    global $CHANGE_USER_TEMPLATE;

    $this->showScreen($CHANGE_USER_TEMPLATE, 'displayChangeForm', $this->getAppName());
  }

  function changeUserData()
  {

    if($this->user_->updateUserData($this->getPostRequestField('data', null)))
      {
	// TODO: Drop a message, that everything was fine
	if($this->getPostRequestField('url', null))
	  {
	    $this->debug($this->getPostRequestField('url', null));
	    $this->addSessionMessage("USER_CHANGED");
	    header("Location: ".$this->getPostRequestField('url', null)."");
	  }
	    else
	  echo "now what?!?!";
      }

    else
      {
	// TODO: Drop a message, that something went wrong
	//	print_r($this->getPostRequestField('data', null));
      }
    

  }

  function displayChangeForm(& $tpl)
  {
    global $PHP_SELF;

    $this->debug("Display Change Form");

    $messages = $this->getAllSessionMessages();
    $message_text = '';
    if(is_array($messages))
      {
	while($msg = array_pop($messages))
	  {
	    $message_text.=$this->getMessageText($msg);
	  }
      }
    
    $tpl->setCurrentBlock('main_block');



    $tpl->setVar(array(
		       'CHANGE_USER_DATA_TITLE' => $this->getLabelText('CHANGE_USER_TITLE'),
		       'MESSAGES' => $message_text
		       )
		 );

    $tpl->setVar(array(
		       'SELF_PATH' => $PHP_SELF,
		       'REDIRECT_URL' => $PHP_SELF
		       )
		 );
    $tpl->setVar(array(
		       'LABEL_FIRSTNAME' => $this->getLabelText('FIRST_NAME'),
		       'LABEL_LASTNAME' => $this->getLabelText('LAST_NAME'),
		       'LABEL_EMAIL' => $this->getLabelText('EMAIL'),
		       'LABEL_STREET' => $this->getLabelText('STREET'),
		       'LABEL_NO' => $this->getLabelText('NO'),
		       'LABEL_CITY' => $this->getLabelText('CITY'),
		       'LABEL_ZIP' => $this->getLabelText('ZIP'),
		       'LABEL_COUNTRY' => $this->getLabelText('COUNTRY'),
		       'LABEL_PHONE' => $this->getLabelText('PHONE'),
		       'LABEL_COMMENT' => $this->getLabelText('COMMENT')
		       )
		 );
    $tpl->setVar(array(
		       'SUBMIT_BUTTON' => $this->getLabelText('SUBMIT_BUTTON'),
		       'CANCEL_BUTTON' => $this->getLabelText('CANCEL_BUTTON')
		       )
		 );

    $tpl->setVar(array(
		       'FIRST_NAME' => $this->user_->first_name,
		       'LAST_NAME' => $this->user_->last_name,
		       'EMAIL' => $this->user_->email,
		       'STREET' => $this->user_->street,
		       'NO' => $this->user_->no,
		       'CITY' => $this->user_->city,
		       'ZIP' => $this->user_->zip,
		       'COUNTRY' => $this->user_->country,
		       'PHONE' => $this->user_->phone,
		       'COMMENT' => $this->user_->comment,
		       'USER_ID' => $this->user_->user_id_
		       )
		 );





    $tpl->parseCurrentBlock();

    return 1;
  }


}



?>
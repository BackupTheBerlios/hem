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

    $user_id = $this->getGetRequestField('uid', null);

    // We change another User
    if(!is_null($user_id))
      {
	$this->user_to_change_ = new User($user_id, $this->dbi_);
      }
    // We change our selves data
    else
      {
	$this->user_to_change_ = $this->user_;
      }


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
	    $this->changeForm();
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

    $this->debug($this->auth_handler_->getUserName());
    $this->debug($this->auth_handler_->checkRight('1'));
    if($this->auth_handler_->checkRight(1))
      $this->debug("User has Right 1");
    else
      $this->debug("User has not Right 1");


    $message_text = '';

    if($this->hasSessionMessages() == TRUE)
      {
	$messages = $this->getAllSessionMessages();

	while($msg = array_pop($messages))
	  {
	    $message_text.=$msg;
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
		       'REDIRECT_URL' => (!is_null($this->getGetRequestField('url', null))) ? $this->getGetRequestField('url', null) : $PHP_SELF
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
		       'FIRST_NAME' => $this->user_to_change_->first_name,
		       'LAST_NAME' => $this->user_to_change_->last_name,
		       'EMAIL' => $this->user_to_change_->email,
		       'STREET' => $this->user_to_change_->street,
		       'NO' => $this->user_to_change_->no,
		       'CITY' => $this->user_to_change_->city,
		       'ZIP' => $this->user_to_change_->zip,
		       'COUNTRY' => $this->user_to_change_->country,
		       'PHONE' => $this->user_to_change_->phone,
		       'COMMENT' => $this->user_to_change_->comment,
		       'USER_ID' => $this->user_to_change_->user_id_
		       )
		 );





    $tpl->parseCurrentBlock();

    return 1;
  }


}



?>
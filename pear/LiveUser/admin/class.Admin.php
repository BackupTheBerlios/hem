<?php
class Admin extends PHPApplication

{

  function Admin($params)
  {
    PHPApplication::PHPApplication($params);
  }


  function run()
  {
    //   echo $this->getMessageText("DEFAULT");

    $this->cmd = $this->getGetRequestField('cmd', null);


    switch ($this->cmd)
      {
      case 'show_users':
	$this->showUsers();
	break;
      case 'show_groups':
	$this->showGroups();
	break;
      case 'show_rights':
	$this->showRights();
	break;
      case 'setup_db':
	$this->setUpLiveUser();
	break;
      case 'change_right':
	$this->changeRightDriver();
	break;
      default:
	$this->showMenu();
	break;

      }

  }

  function showMenu()
  {
    global $APP_TEMPLATE;
    
    $this->showScreen($APP_TEMPLATE, 'displayMenu', $this->getAppName());    

  }

  function changeRightDriver()
  {

    if( !is_null( $rid = $this->getGetRequestField('rid', null) ) )
      {
	$this->right_to_change_ = $this->admin_auth_handler_->perm_->getRights(
									       array(
										     'where_right_id' => $rid
										     )
									       );
	$this->debug("Right to Change: $rid");
	$this->debugArray($this->right_to_change_);
      }

    if($this->getPostRequestField('step', null) == '1')    
      {

	$this->debugArray($this->getPostRequestField('data', null));

      }
    else
      {
	$this->changeRightForm();
      }
  }


  function changeRightForm()
  {
    global $CHANGE_RIGHT_TEMPLATE;

    $this->showScreen($CHANGE_RIGHT_TEMPLATE, 'displayChangeRightForm', $this->getAppName());  
    
  }


  function displayChangeRightForm(& $tpl)
  {
    global $PHP_SELF;
    
    $tpl->setCurrentBlock('main_block');


    $message_text = '';

    if (isset($this->right_to_change_))
      {
	$tpl->setVar('CHANGE_RIGHT_TITLE', $this->getLabelText('CHANGE_RIGHT_TITLE'));
      }
    else
      {
	$tpl->setVar('CHANGE_RIGHT_TITLE', $this->getLabelText('ADD_RIGHT_TITLE'));
      }


    $tpl->setVar(array(
		       'SELF_PATH' => $PHP_SELF."?cmd=".$this->cmd,
		       'REDIRECT_URL' => (!is_null($this->getGetRequestField('url', null))) ? $this->getGetRequestField('url', null) : $PHP_SELF
		       )
		 );
    $tpl->setVar('MESSAGES', $message_text);
    
    $tpl->setVar(array(
		       'RIGHT_DEFINE_NAME' => $this->getLabelText('RIGHT_DEFINE_NAME'),
		       'RIGHT_TITLE' => $this->getLabelText('RIGHT_TITLE'),
		       'RIGHT_DESCRIPTION' => $this->getLabelText('RIGHT_DESCRIPTION')
		       )
		 );

    if (isset($this->right_to_change_))
      {
	$tpl->setVar(array(
			   'VAL_RIGHT_DEFINE_NAME' => $this->getLabelText('RIGHT_DEFINE_NAME'),
			   'VAL_RIGHT_TITLE' => $this->getLabelText('RIGHT_TITLE'),
			   'VAL_RIGHT_DESCRIPTION' => $this->getLabelText('RIGHT_DESCRIPTION')
			   )
		     );
	
      }

    $tpl->setVar(array(
		       'EVALUATOR' => $this->getLabelText('EVALUATOR'),
		       'MANAGER' => $this->getLabelText('MANAGER'),
		       'ADMIN' => $this->getLabelText('ADMIN')
		       )
		 );
    


    $tpl->parseCurrentBlock();

    return 1;

  }

  function setUpLiveUser()
  {
    global $APP_TEMPLATE;

    $this->showScreen($APP_TEMPLATE, 'displaySetup', $this->getAppName());    


  }

  // Sets Up Admin stuff
  // - An Applikation
  // - An Standard Area
  // - Groups: Evaluators, Managers, Admins
  function displaySetup(& $tpl)
  {

    $return_string = '';


    $this->admin_auth_handler_->perm_->addLanguage('en', 'english', 'English language');
    $this->admin_auth_handler_->perm_->setCurrentLanguage('en');
    $app_id = $this->admin_auth_handler_->perm_->addApplication('HEM', 'Heuristic Evaluation Manager');
    if(PEAR::isError($app_id))
      {
	$return_string.= "<pre>Adding App:";
	$return_string.= var_export($app_id);
	$return_string.= "</pre>";
      }
    else
      $return_string.= "Added Application $app_id<br/>";
 
    $area_id = $this->admin_auth_handler_->perm_->addArea($app_id, 'AREA', 'The Only Area');
    
    if(PEAR::isError($area_id))
      {
	$return_string.= "<pre>Adding Area:";
	$return_string.= var_export($area_id);
	$return_string.= "</pre>";
      }
    else
      $return_string.= "Added Area $area_id<br/>"; 
    

    $group_id_1 = $this->admin_auth_handler_->perm_->addGroup('evaluator', 'The Evaluators', TRUE, 'EVALUATOR');
    if(PEAR::isError($group_id_1))
      {
	$return_string.= "<pre>Adding Area:";
	$return_string.= var_export($group_id_1);
	$return_string.= "</pre>";
      }
    else
      $return_string.= "Added Group $group_id_1<br/>"; 

    $group_id_2 = $this->admin_auth_handler_->perm_->addGroup('manager', 'The Managers', TRUE, 'MANAGER');
    if(PEAR::isError($group_id_2))
      {
	$return_string.= "<pre>Adding Area:";
	$return_string.= var_export($group_id_2);
	$return_string.= "</pre>";
      }
    else
      $return_string.= "Added Group $group_id_2<br/>"; 

    $group_id_3 = $this->admin_auth_handler_->perm_->addGroup('admin', 'The Administrators', TRUE, 'ADMIN');
    if(PEAR::isError($group_id_3))
      {
	$return_string.= "<pre>Adding Area:";
	$return_string.= var_export($group_id_3);
	$return_string.= "</pre>";
      }
    else
      $return_string.= "Added Group $group_id_3<br/>"; 



    $tpl->setCurrentBlock('main_block');
    
    $tpl->setVar('CONTENT', $return_string);
    
    $tpl->parseCurrentBlock();

    return 1;

  }


  function showUsers()
  {
    global $APP_TEMPLATE;

    $this->showScreen($APP_TEMPLATE, 'displayShowUsers', $this->getAppName());
  }

  function showGroups()
  {
    global $APP_TEMPLATE;

    $this->showScreen($APP_TEMPLATE, 'displayShowGroups', $this->getAppName());
  }

  function showRights()
  {
    global $APP_TEMPLATE;

    $this->showScreen($APP_TEMPLATE, 'displayShowRights', $this->getAppName());
  }


  function displayMenu(& $tpl)
  {
    global $PHP_SELF;
    
    $tpl->setCurrentBlock('main_block');
    
    $content = '';
    $content.= "<a href=\"".$PHP_SELF."?cmd=change_right\">Add Right</a>";
    $content.= "<br/>";
    $content.= "<a href=\"".$PHP_SELF."?cmd=setup_db\">Setup the Database</a>";
    $content.= "<br/>";
    $content.= "<a href=\"".$PHP_SELF."?cmd=show_users\">Show Users</a>";
    $content.= "<br/>";
    $content.= "<a href=\"".$PHP_SELF."?cmd=show_groups\">Show Groups</a>";
    $content.= "<br/>";
    $content.= "<a href=\"".$PHP_SELF."?cmd=show_rights\">Show Rights</a>";

    $tpl->setVar('CONTENT', $content);
    
    $tpl->parseCurrentBlock();
    
    return 1;

  }

  function displayShowUsers(& $tpl)
  {
    $tpl->setCurrentBlock('main_block');
    
    $tpl->setVar('CONTENT', $this->admin_auth_handler_->listUsers());
    
    $tpl->parseCurrentBlock();

    return 1;

  }


  function displayShowGroups(& $tpl)
  {
    $tpl->setCurrentBlock('main_block');
    
    $tpl->setVar('CONTENT', $this->admin_auth_handler_->listGroups());
    
    $tpl->parseCurrentBlock();

    return 1;

  }
  
 function displayShowRights(& $tpl)
  {
    $tpl->setCurrentBlock('main_block');
    
    $tpl->setVar('CONTENT', $this->admin_auth_handler_->listRights());
    
    $tpl->parseCurrentBlock();

    return 1;

  }


}








?>
<?php
define('ADMIN_AUTH_HANDLER_LOADED', TRUE);


// Include the API we're abstracting here
require_once 'LiveUser/Admin/Perm/Container/DB_Medium.php';
require_once 'LiveUser/Admin/Auth/Container/DB.php';
require_once 'DB.php';

class AdminAuthHandler 
{

  var $version_ = "1.0.0";


  function AdminAuthHandler($conf)
  {
   
    //    $this->conf_ = $conf;
    //    print_r($this->conf_);

    $this->init($conf);
    
  }

  function init($conf)
  {
    global $APP_AUTH_DSN;

    $complex_conf = 
      array(
	    'autoInit' => false,
	    'session'  => array(
				'name'     => $conf['auth_session_name'],
				'varname'  => 'ludata'
				),
	    'login' => array(
			     'method'   => 'post',
			     'username' => 'handle',
			     'password' => 'passwd',
			     'force'    => false,
			     'function' => '',
			     'remember' => 'rememberMe'
			     ),
	    'logout' => array(
			      'trigger'  => 'logout',
			      'redirect' => $conf['auth_exit_page'],
			      'destroy'  => true,
			      'method' => 'get',
			      'function' => ''
			      ),
	    'authContainers' => array(
				      array(
					    'type'          => 'DB',
					    'name'          => 'DB_Local',
					    'loginTimeout'  => 0,
					    'expireTime'    => 3600,
					    'idleTime'      => 1800,
					    'dsn'           => $conf['auth_dsn'],
					    'allowDuplicateHandles' => 0,
					    'authTable'     => 'liveuser_users',
					    'authTableCols' => array(
								     'user_id'    => 'auth_user_id',
								     'handle'     => 'handle',
								     'passwd'     => 'passwd',
								     'lastlogin'  => 'lastlogin',
								     'is_active'  => 'is_active'
								     )
					    )
				      ),
	    'permContainer' => array(
				     'dsn'        => $conf['auth_dsn'],
				     'type'       => 'DB_Medium',
				     'prefix'     => 'liveuser_'
				     )
	    );
    


    $connect_options = array(
			     'dsn' => $conf['auth_dsn']
			     );


    $this->perm_ = & new LiveUser_Admin_Perm_Container_DB_Medium($connect_options);
    $this->auth_ = & new LiveUser_Admin_Auth_Container_DB($connect_options, $complex_conf['authContainers'][0]);

    $this->perm_->setCurrentLanguage('en');
  }


  function addUser($uid = '', $handle = '', $password = '', $active = false, $perm_container = null)
  {
    if(is_null($perm_container)) $perm_container = $complex_conf['permContainer']['type'];

    if(!empty($uid) && !empty($handle) && is_object($this->auth_) && is_object($this->perm_))
      {
	$user_auth_id = $this->auth_->addUser($handle, $password, $active, null, null, $uid, null);
	
	if(PEAR::isError($user_auth_id))
	  {
	    return FALSE;
	    echo "<pre>";
	    print_r($user_auth_id);
	    echo "</pre>";
	  }
	else 
	  {
	    $user_perm_id = $this->perm_->addUser($user_auth_id, $perm_container,  LIVEUSER_USER_TYPE_ID);
	    
	    if(PEAR::isError($user_perm_id))
	      {
		return FALSE;
		echo "<pre>";
		print_r($user_perm_id);
		echo "</pre>";
	      }
	    else return $user_perm_id;
	  }
      }
    else return FALSE; 
  }

  function removeUser($permId)
  {
    if (is_object($this->auth_) && is_object($this->perm_)) {
      $auth_data = $this->perm_->getAuthUserId($permId);
      
      if (PEAR::isError($auth_data)) {
	    return FALSE;
	    echo "<pre>";
	    print_r($auth_data);
	    echo "</pre>";
      }
      
      $result = $this->auth_->removeUser($auth_data['auth_user_id']);
      
      if (PEAR::isError($result)) {
	return FALSE;
	echo "<pre>";
	print_r($result);
	echo "</pre>";
      }
      
      return $this->perm_->removeUser($permId);
    }
    return FALSE;
  }


  function getUsers()
  {
    // TODO: fixme
    return $this->admin_->getUsers();
  }

  function getGroups($options = null)
  {
    return (!empty($options)) ? $this->perm_admin_->getGroups($options) : FALSE;
  }

  function addGroup($group_name, $group_description, $active = FALSE)
  {
    if (DB::isError($this->perm_admin_))
      {    
	echo $status->getMessage();
	
      }
    return $this->perm_admin_->addGroup($group_name, $group_description, $active);
  }


  // Helper functions -> thumb output
  function listGroups()
  {
    $groups = $this->perm_->getGroups(array(
				     'where_is_active' => "'Y'"
				     )
			       );

    $text = '';
    $text.= "<pre>Groups";
    $text.= var_export($groups, TRUE);
    $text.= "</pre>";
    
    return $text;

  }

  function listRights()
  {
    $rights = $this->perm_->getRights();

    $text = '';
    $text.= "<pre>Rights";
    $text.= var_export($rights, TRUE);
    $text.= "</pre>";
    
    return $text;

  }
  
  
  function listUsers()
  {
    // Get Auth Users
    $auth_users = $this->auth_->getUsers();
    
    $text = '';
    $text.= "<pre>AuthUsers:";
    $text.= var_export($auth_users, TRUE);
    $text.= "</pre>";
    
    // Get Perm Users
    $perm_users = $this->perm_->getUsers();
    $text.= "<pre>PermUsers:";
    $text.= var_export($perm_users, TRUE);
    $text.= "</pre>";

    return $text;
  }  
  

  function apiVersion()
  {
    return $this->version_;
  }



}

<?php
define('AUTH_HANDLER_LOADED', TRUE);


// Include the API we're abstracting here
require_once 'LiveUser/LiveUser.php';


class AuthHandler 
{

  var $version_ = "1.0.0";


  function AuthHandler($conf)
  {
   
    //    $this->conf_ = $conf;
    //    print_r($this->conf_);

    $this->init($conf);
    
  }


  function init($conf)
  {
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
    


    $this->auth_handler_ = LiveUser::singleton($complex_conf);
    $error = $this->auth_handler_->init();
    return $error;
  }

  function isAuthenticated()
  {
    return $this->auth_handler_->isLoggedIn();
  }

  function getUserName()
  {
    return $this->auth_handler_->getProperty('handle');
  }

  function getUID()
  {
    return $this->auth_handler_->getProperty('authUserId');
  }

  function getLastLogin()
  {
    return $this->auth_handler_->getProperty('lastLogin');
  }

  function isActive()
  {
    return $this->auth_handler_->getProperty('isActive');
  }

  function getCurrentLogin()
  {
    return $this->auth_handler_->getProperty('currentLogin');
  }

  function apiVersion()
  {
    return $this->version_;
  }

}
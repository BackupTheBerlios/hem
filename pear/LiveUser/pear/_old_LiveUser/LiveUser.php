<?php

// LiveUser: A framework for authentication and authorization in PHP applications
// Copyright (C) 2002-2003 Markus Wolff
//
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public
// License along with this library; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

/**
 * LiveUser is an authentication/permission framework designed
 * to be flexible and easily extendable.
 *
 * Since it is impossible to have a
 * "one size fits all" it takes a container
 * approach which should enable it to
 * be versatile enough to meet most needs. 
 *
 * @package  LiveUser
 * @category authentication
 */

/**
 * Include PEAR base class
 */
require_once 'PEAR.php';

/**#@+
 * Error related constants definition
 *
 * @var integer
 */
define('LIVEUSER_ERROR',                        -1);
define('LIVEUSER_ERROR_NOT_SUPPORTED',          -2);
define('LIVEUSER_ERROR_CONFIG',                 -3);
define('LIVEUSER_ERROR_MISSING_DEPS',           -4);
define('LIVEUSER_ERROR_MISSING_LOGINFUNCTION',  -5);
define('LIVEUSER_ERROR_MISSING_LOGOUTFUNCTION', -6);
define('LIVEUSER_ERROR_COOKIE',                 -7);
/**#@-*/

/**#@+
 * Statuses of the current object.
 *
 * @var integer
 */
define('LIVEUSER_STATUS_IDLED',          -1);
define('LIVEUSER_STATUS_EXPIRED',        -2);
define('LIVEUSER_STATUS_ISINACTIVE',     -3);
define('LIVEUSER_STATUS_PERMINITERROR',  -4);
define('LIVEUSER_STATUS_AUTHINITERROR',  -5);
define('LIVEUSER_STATUS_UNKNOWN',        -6);
define('LIVEUSER_STATUS_AUTHNOTFOUND',   -7);
define('LIVEUSER_STATUS_LOGGEDOUT',      -8);
/**#@-*/

/**
 * Class LiveUser - Login handling class
 *
 * Description:
 * This is a manager class for a user login system using the LiveUser
 * class. It creates a LiveUser object, takes care of the whole login
 * process and stores the LiveUser object in a session.
 *
 * You can also configure this class to try to connect to more than
 * one server that can store user information - each server requiring
 * a different backend class. This way you can for example create a login
 * system for a live website that first queries the local database and
 * if the requested user is not found, it tries to find im in your
 * company's LDAP server. That way you don't have to create lots of
 * user accounts for your employees so that they can access closed
 * sections of your website - everyone can use his existing account.
 *
 * NOTE: No browser output may be made before using this class, because
 * it will try to send HTTP headers such as cookies and redirects.
 *
 * Requirements:
 * - Should run on PHP version 4.1.0 or higher, tested only from 4.2.1 onwards
 *
 * Thanks to:
 * Bjoern Schotte, Kristian Koehntopp, Antonio Guerra
 *
 * @author   Markus Wolff       <wolff@21st.de>
 * @author   Bjoern Kraus       <krausbn@php.net>
 * @author   Lukas Smith        <smith@backendmedia.com>
 * @author   Pierre-Alain Joye  <pajoye@php.net>
 * @author   Arnaud Limbourg    <arnaud@php.net>
 * @version  $Id: LiveUser.php,v 1.1 2004/07/16 13:58:06 mloitzl Exp $
 * @package  LiveUser
 */
class LiveUser extends PEAR
{
    /**
     * LiveUser options set in the configuration file.
     *
     * @access  private
     * @var     array
     */
    var $_options = array(
        'session_save_handler' => false,
        'autoInit'=> false,
        'session' => array(
            'name'     => 'PHPSESSID',
            'varname'  => 'ludata'
        ),
        'login'   => array(
            'method'   => 'request',
            'username' => 'username',
            'password' => 'password',
            'force'    => false,
            'function' => '',
            'remember' => false
        ),
        'logout'  => array(
            'method'   => 'request',
            'trigger'  => 'logout',
            'redirect' => '',
            'destroy'  => true,
            'function' => ''
        )
    );

    /**
     * The auth container object.
     *
     * @access private
     * @var    object
     */
    var $_auth = null;

    /**
     * The permission container object.
     *
     * @access private
     * @var    object
     */
    var $_perm = null;

    /**
     * Nested array with the auth containers that shall be queried for user information.
     * Format:
     * <code>
     * array('name' => array("option1" => "value", ....))
     * </code>
     * Typical options are:
     * <ul>
     * - server: The adress of the server being queried (ie. "localhost").
     * - handle: The user name used to login for the server.
     * - password: The password used to login for the server.
     * - database: Name of the database containing user information (this is
     *   usually used only by RDBMS).
     * - baseDN: Obviously, this is what you need when using an LDAP server.
     * - connection: Present only if an existing connection shall be used. This
     *   contains a reference to an already existing connection resource or object.
     * - type: The container type. This option must always be present, otherwise
     *   the LoginManager can't include the correct container class definition.
     * - name: The name of the auth container. You can freely define this name,
     *   it can be used from within the permission container to see from which
     *   auth container a specific user was coming from.
     *</ul>
     *
     * @access private
     * @var    array
     */
    var $authContainers = array();

    /**
     * Array of settings for the permission container to use for retrieving
     * user rights.
     * If set to false, no permission container will be used.
     * If that is the case, all calls to checkRight() will return false.
     * The array element 'type' must be present for the LoginManager to be able
     * to include the correct class definition (example: "DB_Complex").
     *
     * @access private
     * @var    mixed
     */
    var $permContainer = false;

    /**
     * Current status of the LiveUser object.
     *
     * @access private
     * @var    string
     * @see    LIVEUSER_STATUS constants
     */
    var $status = null;

    /**
     * Whether an error was raised
     *
     * @access private
     * @var    boolean
     */
    var $_error = false;

    // }}}
    // {{{ constructor

    /**
     * Constructor
     */
    function LiveUser()
    {
        $this->PEAR();
    }

    // }}}
    // {{{ raiseError()

    /**
     * This method is used to communicate an error and invoke error
     * callbacks etc.  Basically a wrapper for PEAR::raiseError
     * without the message string.
     *
     * @param mixed    integer error code, or a PEAR error object (all
     *                 other parameters are ignored if this parameter is
     *                 an object
     *
     * @param int      error mode, see PEAR_Error docs
     *
     * @param mixed    If error mode is PEAR_ERROR_TRIGGER, this is the
     *                 error level (E_USER_NOTICE etc).  If error mode is
     *                 PEAR_ERROR_CALLBACK, this is the callback function,
     *                 either as a function name, or as an array of an
     *                 object and method name.  For other error modes this
     *                 parameter is ignored.
     *
     * @param string   Extra debug information.  Defaults to the last
     *                 query and native error code.
     *
     * @return object  a PEAR error object
     *
     * @see PEAR_Error
     */
    function &raiseError($code = null, $mode = null, $options = null,
                         $userinfo = null)
    {
        // The error is yet a LiveUser error object
        if (is_object($code)) {
            return PEAR::raiseError($code, null, null, null, null, null, true);
        }

        if (empty($code)) {
            $code = LIVEUSER_ERROR;
        }
        $msg = LiveUser::errorMessage($code);
        return PEAR::raiseError("LiveUser Error: $msg", $code, $mode, $options, $userinfo);
    }

    /**
     * Checks the given file and returns an object of the LoginManager.
     *
     * This array contains private options defined by
     * the following associative keys:
     *
     * <code>
     *
     * array(
     *  'autoInit' => false/true,
     *  'session'  => array(
     *      'name'    => 'liveuser session name',
     *      'varname' => 'liveuser session var name'
     *  ),
     *  'login' => array(
     *      'method'   => 'request, get or post',
     *      'username' => 'Form input containing user handle',
     *      'password' => 'Form input containing password',
     *      'remember' => '(optional) Form checkbox containing <Remember Me> info',
     *      'function' => '(optional) Function to be called when accessing a page without logging in first',
     *      'force'    => 'Should the user be forced to login'
     *  ),
     *  'logout' => array(
     *      'method'   => 'request, get or post',
     *      'trigger'  => 'REQUEST, GET or POST var that triggers the logout process',
     *      'redirect' => 'Page path to be redirected to after logout',
     *      'function' => '(optional) Function to be called when accessing a page without logging in first',
     *      'destroy'  => 'Whether to destroy the session on logout' false, true or regenid
     *  ),
     * // The cookie options are optional. If they are specified, the Remember Me
     * // feature is activated.
     *  'cookie' => array(
     *      'name'     => 'Name of Remember Me cookie',
     *      'lifetime' => 'Cookie lifetime in days',
     *      'path'     => 'Cookie path',
     *      'domain'   => 'Cookie domain',
     *      'secret'   => 'Secret key used for cookie value encryption'
     *  ),
     *  'authContainers' => array(
     *      'name' => array(
     *            'type' => 'DB',
     *            'connection'    => 'db connection object, use this or dsn',
     *            'dsn'           => 'database dsn, use this or connection',
     *            'loginTimeout'  => 0,
     *            'expireTime'    => 3600,
     *            'idleTime'      => 1800,
     *            'allowDuplicateHandles' => 0,
     *            'authTable'     => 'liveuser_users',
     *            'authTableCols' => array('user_id'            => 'auth_user_id',
     *                                     'handle'             => 'handle',
     *                                     'passwd'             => 'passwd',
     *                                     'lastlogin'          => 'lastlogin'
     *                                     'owner_user_id'      => 'owner_user_id'
     *                                     'owner_group_id'     => 'owner_group_id'
     *            )
     *      )
     *  ),
     *  'permContainer' => array(
     *      'type'       => 'DB_Complex',
     *      'connection' => 'db connection object, use this or dsn',
     *      'dsn'        => 'database dsn, use this or connection',
     *      'prefix'     => 'liveuser_'
     *  )
     *
     * </code>
     *
     * Other options in the configuration file relative to
     * the Auth and Perm containers depend on what the
     * containers expect. Refer to the Containers documentation.
     * The examples for containers provided are just general
     * do not reflect all the options for all containers.
     *
     * @access public
     * @param  mixed    The config file or the config array to configure.
     * @param  string   Handle of the user trying to authenticate
     * @param  string   Password of the user trying to authenticate
     * @param  boolean  set to true if user wants to logout
     * @param  boolean  set if remember me is set
     * @param  mixed    Name of array containing the configuration.
     * @return object   Returns an object of either LiveUser or PEAR_Error type
     */
    function &factory($conf, $handle = '', $passwd = '', $logout = false, $remember = false, $confName = 'liveuserConfig')
    {
        $obj = &new LiveUser();

        if (!empty($conf)) {
            $init = $obj->_readConfig($conf, $confName);
            if (LiveUser::isError($init)) {
                return $init;
            }
        }

        if (!PEAR::isError($obj) &&
            isset($obj->_options['autoInit']) &&
            $obj->_options['autoInit']
        ) {
            $init = $obj->init($handle, $passwd, $logout);
            if (LiveUser::isError($init)) {
                return $init;
            }
        }

        return $obj;
    }

    /**
    * Makes your instance global.
    *
    * <b>You MUST call this method with the $var = &LiveUser::singleton() syntax.
    * Without the ampersand (&) in front of the method name, you will not get
    * a reference, you will get a copy.</b>
    *
    * @access public
    * @param  mixed    The config file or the config array to configure.
    * @param  string    Handle of the user trying to authenticate
    * @param  string    Password of the user trying to authenticate
    * @param  boolean  set to true if user wants to logout
    * @param  boolean  set if remember me is set
    * @param  mixed    Name of array containing the configuration.
    * @return object      Returns an object of either LiveUser or PEAR_Error type
    * @see    LiveUser::factory
    */
    function &singleton($conf, $handle = '', $passwd = '', $logout = false, $remember = false, $confName = 'liveuserConfig')
    {
        static $instances;
        if (!isset($instances)) $instances = array();

        $signature = serialize(array($handle, $passwd, $confName));
        if (!isset($instances[$signature])) {
            $instances[$signature] = &LiveUser::factory($conf, $handle, $passwd, $logout, $confName);
        }

        return $instances[$signature];
    }

    /**
     * creates an instance of an auth object
     *
     * @access public
     * @param  mixed    Name of array containing the configuration.
     * @return object   Returns an object of an auth container
     */
    function &authFactory($conf)
    {
        $classname = 'LiveUser_Auth_Container_' . $conf['type'];
        $filename = 'LiveUser/Auth/Container/' . $conf['type'] . '.php';


        @include_once($filename);

        if (!class_exists($classname)) {
            $this->_error = true;
            $error = LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
                'Missing file: ' . $filename, 'LiveUser');
            return $error;
        }
        $auth = &new $classname($conf);
        return $auth;
    }

    /**
     * Creates an instance of an perm object
     *
     * @access public
     * @param  mixed    Name of array containing the configuration.
     * @return object   Returns an object of an perm container
     */
    function &permFactory($conf)
    {
        $classname = 'LiveUser_Perm_Container_' . $conf['type'];
        $filename = 'LiveUser/Perm/Container/' . $conf['type'] . '.php';
        @include_once($filename);
        if (!class_exists($classname)) {
            $this->_error = true;
            $error = LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
                'Missing file: ' . $filename);
            return $error;
        }
        $perm = &new $classname($conf);
        return $perm;
    }

    /**
     * Clobbers two arrays together
     * taken from the user notes of array_merge_recursive
     * used in LiveUser::_readConfig()
     * may be called statically
     *
     * @access public
     * @param  array    array that should be clobbered
     * @param  array    array that should be clobbered
     *
     * @return mixed array on success and false on error
     * @author kc@hireability.com
     */
    function arrayMergeClobber($a1, $a2)
    {
        if (!is_array($a1) || !is_array($a2)) {
            return false;
        }
        foreach($a2 as $key => $val) {
            if (is_array($val) &&
                isset($a1[$key]) &&
                is_array($a1[$key])
            ) {
                $a1[$key] = LiveUser::arrayMergeClobber($a1[$key], $val);
            } else {
                $a1[$key] = $val;
            }
        }
        return $a1;
    }

    /**
     * Reads the configuration
     *
     * @access private
     * @param  mixed    Conf array or file path to configuration
     * @param  string   Name of array containing the configuration
     * @return mixed    true on success, PEAR_Error on failure
     */
    function _readConfig($conf, $confName)
    {
        if (is_array($conf)) {
            if (isset($conf['authContainers'])) {
                $this->authContainers = $conf['authContainers'];
                unset($conf['authContainers']);
            }
            if (isset($conf['permContainer'])) {
                $this->permContainer = $conf['permContainer'];
                unset($conf['permContainer']);
            }

            $this->_options = $this->arrayMergeClobber($this->_options, $conf);
            if (isset($this->_options['cookie'])) {
                $cookie_default = array(
                    'name'     => 'ludata',
                    'lifetime' => '365',
                    'path'     => '/',
                    'domain'   => '',
                    'secret'   => 'secret',
                );
                $this->_options['cookie'] = $this->arrayMergeClobber($cookie_default, $this->_options['cookie']);
            }

            return true;
        }

        if (!@include_once($conf)) {
            $this->_error = true;
            return LiveUser::raiseError(LIVEUSER_ERROR_CONFIG, null, null,
                "Could not read the configuration file in LiveUser::readConfig()");
        }
        if (isset(${$confName}) && is_array(${$confName})) {
            return $this->_readConfig(${$confName}, $confName);
        }
        $this->_error = true;
        return LiveUser::raiseError(LIVEUSER_ERROR_CONFIG, null, null,
            "Configuration array not found in LiveUser::readConfig()");
    }

    /**
     * Factory method for auth container admin classes, will look up the
     * given name in the configuration array and return a new auth container
     * admin object if a match was found, or false if not.
     *
     * @access private
     * @param  string  auth container name
     * @return mixed   auth container object on success, false on failure
     */
    function &getAuthAdminObjectByName($name)
    {
        $con = false;
        // Loop through container array
        foreach ($this->authContainers as $container) {
            if (isset($container['name']) && $container['name'] == $name) {
                $con = $container['name'];
                break; // exit foreach loop
            }
        }

        if ($con !== false) {
            // get proper admin class definition
            $classname = 'LiveUser_Admin_Auth_Container_' . $container['name'];
            $filename = 'LiveUser/Admin/Auth/Container/' . $container['name'] . '.php';
            @include_once($filename);
            if (!class_exists($classname)) {
                $this->_error = true;
                $error = LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
                    'Missing file: '.$filename);
                return $error;
            }
            $con = &new $classname($container, array('type' => $container['type']));
        }
        return $con;
    }

    /**
     * Crypts data using mcrypt or userland if not available
     *
     * @access private
     * @param  boolean true to crypt, false to decrypt
     * @param  string  data to crypt
     * @return string  crypted data
     */
    function _cookieCryptMode($crypt, $data)
    {
        if (function_exists('mcrypt_module_open')) {
            $td = mcrypt_module_open('tripledes', '', 'ecb', '');
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);
            mcrypt_generic_init($td, $this->_options['cookie']['secret'], $iv);
            if ($crypt) {
                $data = mcrypt_generic($td, $data);
            } else {
                $data = mdecrypt_generic($td, $data);
            }
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
        } else {
            @include_once 'Crypt/Rc4.php';
            if (!class_exists('Crypt_RC4')) {
                $this->_error = true;
                return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
                    'Please install Crypt_RC4 to use this feature');
            }
            $rc4 =& new Crypt_RC4($this->_options['cookie']['secret']);
            if ($crypt) {
                $rc4->crypt($data);
            } else {
                $rc4->decrypt($data);
            }
        }

        return $data;
    }

    /**
     * Sets an option.
     *
     * @access public
     * @param  string option name
     * @param  mixed  value for the option
     * @return mixed  true or PEAR Error
     * @see    LiveUser::_options
     */
    function setOption($option, $value)
    {
        if (isset($this->options[$option])) {
            $this->options[$option] = $value;
            return true;
        }
        return LiveUser::raiseError(LIVEUSER_ERROR_CONFIG, null, null,
            "unknown option $option");
    }

    /**
     * Returns the value of an option
     *
     * @access public
     * @param  string option name
     * @return mixed the option value
     */
    function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
        return LiveUser::raiseError(LIVEUSER_ERROR_CONFIG, null, null,
            "unknown option $option");
    }

    /**
     * Get the Auth Container class instance of it exists
     *
     * @access public
     * @return mixed object or PEAR Error
     */
    function &getAuthContainer()
    {
        if (is_object($this->_auth)) {
            return $this->_auth;
        }
        $return = LiveUser::raiseError(LIVEUSER_ERROR_CONFIG, null, null,
            'authentication container has not yet been instantianted');
        return $return;
    }

    /**
     * Get the Perm Container class instance if it exists
     *
     * @access public
     * @return mixed object or PEAR Error
     */
    function &getPermContainer()
    {
        if (is_object($this->_perm)) {
            return $this->_perm;
        }
        $return = LiveUser::raiseError(LIVEUSER_ERROR_CONFIG, null, null,
            "permission container has not yet been instantianted");
        return $return;
    }

    /**
     * Tries to retrieve auth object from session.
     * If this fails, the class attempts a login based on cookie or form
     * information (depends on class settings).
     * Returns true if a auth object was successfully retrieved or created.
     * Otherwise, false is returned.
     *
     * @access public
     * @param  string   handle of the user trying to authenticate
     * @param  string   password of the user trying to authenticate
     * @param  boolean  set to true if user wants to logout
     * @param  boolean  set if remember me is set
     * @return mixed    true if init process well, false if something
     *                  went wrong or an error object.
     */
    function init($handle = '', $passwd = '', $logout = false, $remember = false)
    {

        if ($this->_error) {
            return false;
        }

        // set session save handler if needed
        if ($this->_options['session_save_handler'] == true) {
            session_set_save_handler(
                $this->_options['session_save_handler']['open'],
                $this->_options['session_save_handler']['close'],
                $this->_options['session_save_handler']['read'],
                $this->_options['session_save_handler']['write'],
                $this->_options['session_save_handler']['destroy'],
                $this->_options['session_save_handler']['gc']
            );
        }
        // Set the name of the current session
        session_name($this->_options['session']['name']);
        // If there's no session yet, start it now
        @session_start();


        // Determine if user wanted to log out
        $this->processLogout($logout, $handle, $passwd);


        // Try to fetch auth object from session
        if (!$this->unfreeze()) {
            if (is_null($this->_auth)) {
                $lulogin = $this->tryLogin($handle, $passwd);
                if (PEAR::isError($lulogin)) {
                    $this->status = LIVEUSER_STATUS_PERMINITERROR;
                    return $lulogin;
                }
            }
        } else {
            $lulogin = true;
        }


        // Check if authentication session is expired.
        if ($lulogin &&
            $this->_auth->loggedIn &&
            $this->_auth->expireTime > 0 &&
            $this->_auth->currentLogin > 0 &&
            ($this->_auth->currentLogin + $this->_auth->expireTime) < time()
        ) {
            $this->logout();
            $this->status = LIVEUSER_STATUS_EXPIRED;
        }

        // Check if maximum idle time is reached.
        if ($lulogin &&
            $this->_auth->loggedIn &&
            $this->_auth->idleTime > 0 &&
            isset($_SESSION[$this->_options['session']['varname']]['idle']) &&
            ($_SESSION[$this->_options['session']['varname']]['idle'] + $this->_auth->idleTime) < time()
        ) {
            $this->logout();
            $this->status = LIVEUSER_STATUS_IDLED;
        }



        $_SESSION[$this->_options['session']['varname']]['idle'] = time();

        // Force user login.
        if (!$this->isLoggedIn() && $this->_options['login']['force']) {
            if (!empty($this->_options['login']['function']) &&
                is_callable($this->_options['login']['function'])
            ) {
                call_user_func($this->_options['login']['function'], $this);
            }
        }

        // Return boolean that indicates whether a auth object has been created
        // or retrieved from session
        if ($lulogin) {
            return true;
        }

        $this->_error = true;
        if (is_null($this->status)) {
            $this->status = LIVEUSER_STATUS_UNKNOWN;
        }

        return false;
    }

    /**
     * Tries to log the user in by trying all the Auth containers defined
     * in the configuration file until there is a success or failure.
     *
     * @access private
     * @param  string   handle of the user trying to authenticate
     * @param  string   password of the user trying to authenticate
     * @param  boolean  set if remember me is set
     * @return void
     */
    function tryLogin($handle = '', $passwd = '', $remember = false)
    {
        if ($this->_error) {
            return false;
        }


        // handle and password not directly passed
        if (empty($handle)) {

            if (isset($this->_options['cookie']) &&
                isset($_COOKIE[$this->_options['cookie']['name']]))
            {
                $cookieData = unserialize(stripslashes($_COOKIE[$this->_options['cookie']['name']]));
                if (count($cookieData) != 3) {
                    $this->_error = true;
                    // Delete cookie if it's not valid, keeping it messes up the
                    // authentication process
                    setcookie($this->_options['cookie']['name'], '',
                        time() - 3600,
                        $this->_options['cookie']['path'],
                        $this->_options['cookie']['domain']
                    );
                    return LiveUser::raiseError(LIVEUSER_ERROR_COOKIE, null, null,
                        "Wrong data in cookie store in LiveUser::tryLogin()");
                }

                $serverData = $this->_readStoreCookiePasswdId($cookieData[0]);

                if ($serverData[0] != $cookieData[2]) {
                    $this->_error = true;
                    // Delete cookie if it's not valid, keeping it messes up the
                    // authentication process
                    setcookie($this->_options['cookie']['name'], '',
                        time() - 3600,
                        $this->_options['cookie']['path'],
                        $this->_options['cookie']['domain']);
                    return LiveUser::raiseError(LIVEUSER_ERROR_COOKIE, null, null,
                        "Passwords hashes do not match in cookie in LiveUser::tryLogin()");
                }

                $handle = $cookieData[1];
                $passwd = $serverData[1];
                // $remember
            } elseif ($this->_options['login']['method']) {

                switch (strtolower($this->_options['login']['method'])) {
                    case 'request':
                        $httpvar = $_REQUEST;
                        break;
                    case 'post':
                        $httpvar = $_POST;
                        break;
                    case 'get':
                    default:
                        $httpvar = $_GET;
                        break;
                }

                if (empty($httpvar[$this->_options['login']['username']]) ||
                    empty($httpvar[$this->_options['login']['password']]))
                {
                    if (!$this->_options['login']['force'] &&
                        !empty($this->_options['login']['function']) &&
                        is_callable($this->_options['login']['function']))
                    {
                        call_user_func($this->_options['login']['function'], $this);
                    }
                } else {
                    $handle = $httpvar[$this->_options['login']['username']];
                    $passwd = $httpvar[$this->_options['login']['password']];
                    if ($this->_options['login']['remember'] &&
                        isset($httpvar[$this->_options['login']['remember']])
                    ) {
                        $remember = $httpvar[$this->_options['login']['remember']];
                    }
                    if (get_magic_quotes_gpc()) {
                        $handle = stripslashes($handle);
                        $passwd = stripslashes($passwd);
                    }
                }
            }
        }


        if (empty($handle)) {
            return false;
        }

        $counter     = 0;
        $userFound   = false;
        $backends    = array_keys($this->authContainers);
        $backend_cnt = count($backends);


        //loop into auth containers

        while ($userFound == false && $backend_cnt > $counter) {

   
         $auth = &$this->authFactory($this->authContainers[$backends[$counter]]);

            if (LiveUser::isError($auth)) {
                $this->status = LIVEUSER_STATUS_AUTHINITERROR;
                return $auth;
            }


            $auth->login($handle, $passwd, true);
            if ($auth->loggedIn) {
                $this->status = null;
                $userFound    = true;
                $this->_auth  = $auth;
                $this->_auth->backendArrayIndex = $backends[$counter];

                // Create permission object
                if (is_array($this->permContainer)) {
                    $this->_perm =& $this->permFactory($this->permContainer);
                    $res = $this->_perm->init($this->_auth->authUserId);

                    if (PEAR::isError($res)) {
                        $this->status = LIVEUSER_STATUS_PERMINITERROR;
                        return $res;
                    }
                }

                $this->freeze();
                $this->setRememberCookie($handle, $passwd, $remember);
            } else {
                if (!$auth->isActive) {
                    $this->status = LIVEUSER_STATUS_ISINACTIVE;
                    $userFound = true;
                }
            }
            $counter++;
        }

        return true;
    }

    /**
     * Gets auth and perm container objects back from session and tries
     * to give them an active database/whatever connection again
     *
     * @access private
     * @return mixed   false or void
     */
    function unfreeze()
    {
        if ($this->_error) {
            return false;
        }

        $success = false;

        if (isset($_SESSION[$this->_options['session']['varname']]['auth'])
            && is_array($_SESSION[$this->_options['session']['varname']]['auth'])
            && isset($_SESSION[$this->_options['session']['varname']]['auth_name'])
            && strlen($_SESSION[$this->_options['session']['varname']]['auth_name']) > 0
        ) {
            $success = true;
            $name = $_SESSION[$this->_options['session']['varname']]['auth_name'];
            $this->_auth = &$this->authFactory($this->authContainers[$name]);
            $this->_auth->unfreeze($_SESSION[$this->_options['session']['varname']]['auth']);

            if (isset($_SESSION[$this->_options['session']['varname']]['perm'])
                && is_array($_SESSION[$this->_options['session']['varname']]['perm'])
            ) {
                $this->_perm = &$this->permFactory($this->permContainer);
                $this->_perm->unfreeze($_SESSION[$this->_options['session']['varname']]['perm']);
            }
        }

        return $success;
    }

    /**
     * store all properties in an array
     *
     * @access  public
     * @return  void
     */
    function freeze()
    {
        if ($this->_error) {
            return false;
        }

        if (is_object($this->_auth) && $this->_auth->loggedIn) {
            // Bind objects to session
            $_SESSION[$this->_options['session']['varname']] = array();
            $_SESSION[$this->_options['session']['varname']]['auth'] = $this->_auth->freeze();
            $_SESSION[$this->_options['session']['varname']]['auth_name'] = $this->_auth->backendArrayIndex;
            if (is_object($this->_perm)) {
                $_SESSION[$this->_options['session']['varname']]['perm'] = $this->_perm->freeze();
            }
        }
    }

    /**
     * properly disconnect resources in the active container
     *
     * @access  public
     * @return  void
     */
    function disconnect()
    {
        if ($this->_error) {
            return false;
        }

        if (is_object($this->_auth) && $this->_auth->loggedIn) {
            $this->_auth->disconnect();
            $this->_auth = null;
            if (is_object($this->_perm)) {
                $this->_perm->disconnect();
                $this->_perm = null;
            }
        }
    }

    /**
     * If cookies are allowed, this method checks if the user wanted
     * a cookie to be set so he doesn't have to enter handle and password
     * for his next login. If true, it will set the cookie.
     *
     * @access private
     * @param  string   handle of the user trying to authenticate
     * @param  string   password of the user trying to authenticate
     * @param  boolean  set if remember me is set
     * @return boolean  true if the cookie can be set, false otherwise
     */
    function setRememberCookie($handle, $passwd, $remember)
    {
        if ($this->_error) {
            return false;
        }

        switch (strtolower($this->_options['login']['method'])) {
            case 'request':
                $httpvar = $_REQUEST;
                break;
            case 'post':
                $httpvar = $_POST;
                break;
            case 'get':
            default:
                $httpvar = $_GET;
                break;
        }

        if ($remember && isset($this->_options['cookie'])) {
            // Calculate cookie timeout in days
            $cookieTimeout = time() + (86400 * $this->_options['cookie']['lifetime']);

            $store_id = md5($handle . $passwd);

            if (!$passwd_id = $this->_storeCookiePasswdId($passwd, $store_id)) {
                $this->_error = true;
                return LiveUser::raiseError(LIVEUSER_ERROR_COOKIE, null, null,
                    "Cannot store cookie data in LiveUser::setRememberCookie()");
            }

            $setcookie = setcookie(
                          $this->_options['cookie']['name'],
                          serialize(array($store_id, $handle, $passwd_id)),
                          $cookieTimeout,
                          $this->_options['cookie']['path'],
                          $this->_options['cookie']['domain']);

            return $setcookie;
        }
    }

    /**
     * If the logout parameter has been set via GET/POST or the user is
     * idled/expired, this destroys the session object.
     *
     * @access private
     * @param  boolean  set to true if user wants to logout
     * @return void
     * @see    LiveUser::init()
     */
    function processLogout($logout, $handle = '', $passwd = '')
    {
        if ($this->_error) {
            return false;
        }

        // logout user if the user is logged in
        // and new creditionals have been passed to LiveUser
        if (!$logout && $this->_options['logout']['method']) {
            switch (strtolower($this->_options['logout']['method'])) {
                case 'request':
                    $httpvar = $_REQUEST;
                    break;
                case 'post':
                    $httpvar = $_POST;
                    break;
                case 'get':
                default:
                    $httpvar = $_GET;
                    break;
            }
            if (isset($httpvar[$this->_options['logout']['trigger']]) &&
                $httpvar[$this->_options['logout']['trigger']]
            ) {
                $logout = true;
            }
        }

        // logout user if the user is logged in
        // and new creditionals have been passed to LiveUser
        if (!$logout &&
            isset($_SESSION[$this->_options['session']['varname']]['auth_name'])
        ) {
            // handle and password not directly passed
            if (empty($handle) && $this->_options['login']['method']) {
                switch (strtolower($this->_options['login']['method'])) {
                    case 'request':
                        $httpvar = $_REQUEST;
                        break;
                    case 'post':
                        $httpvar = $_POST;
                        break;
                    case 'get':
                    default:
                        $httpvar = $_GET;
                        break;
                }
                if (isset($httpvar[$this->_options['login']['username']]) &&
                    $httpvar[$this->_options['login']['username']] &&
                    isset($httpvar[$this->_options['login']['password']]) &&
                    $httpvar[$this->_options['login']['password']]
                ) {
                    $logout = true;
                }
            } else if ($handle && $passwd) {
                $logout = true;
            }
        }

        // Does the the user have to be logged out?
        if ($logout) {
            $this->logout();
        }
    }

    /**
     * This destroys the session object.
     *
     * @access public
     * @return void
     */
    function logout()
    {
        if ($this->_error) {
            return false;
        }

        if ($this->status != LIVEUSER_STATUS_IDLED ||
            $this->status != LIVEUSER_STATUS_EXPIRED
        ) {
            $this->status = LIVEUSER_STATUS_LOGGEDOUT;
        }

        // If a callback function is set, call it
        if (!empty($this->_options['logout']['function']) &&
            is_callable($this->_options['logout']['function'])
        ) {
            call_user_func($this->_options['logout']['function'], $this);
        }

       // If there's a cookie and the session hasn't idled or expired, kill that one too...
       if (isset($this->_options['cookie']) &&
            isset($_COOKIE[$this->_options['cookie']['name']]) &&
            ($this->status != LIVEUSER_STATUS_IDLED ||
                $this->status != LIVEUSER_STATUS_EXPIRED)
        ) {
            // is this what we want?
            $cookieKillTime = time() - 86400;
            setcookie($this->_options['cookie']['name'], '', $cookieKillTime,
                      $this->_options['cookie']['path'], $this->_options['cookie']['domain']);
            unset($_COOKIE[$this->_options['cookie']['name']]);
        }

        // If the session should be destroyed, do so now...
        if ($this->_options['logout']['destroy'] == true) {
            session_unset();
            session_destroy();
            // set session save handler if needed
            if ($this->_options['session_save_handler'] == true) {
                session_set_save_handler(
                    $this->_options['session_save_handler']['open'],
                    $this->_options['session_save_handler']['close'],
                    $this->_options['session_save_handler']['read'],
                    $this->_options['session_save_handler']['write'],
                    $this->_options['session_save_handler']['destroy'],
                    $this->_options['session_save_handler']['gc']
                );
            }

            // Set the name of the current session
            session_name($this->_options['session']['name']);
            // If there's no session yet, start it now
            @session_start();
            if ($this->_options['logout']['destroy'] == 'regenid') {
                session_regenerate_id();
            }
        } else {
            unset($_SESSION[$this->_options['session']['varname']]);
        }

        // Finally, set the LoginManager's internal auth object back to
        $this->_auth = null;
        $this->_perm = null;

        // If there's a URL to redirect to on logout, do so
        if (!empty($this->_options['logout']['redirect'])) {
            header('Location: ' . $this->_options['logout']['redirect']);
            exit();
        }
    }

    /**
     * Wrapper method for the permission object's own checkRight method.
     *
     * @access public
     * @param  mixed    A right id or an array of rights.
     * @return mixed  level if the user has the right/rights false if not
     */
    function checkRight($rights)
    {
        if ($this->_error) {
            return false;
        }
        if (is_object($this->_perm)) {
            $hasright = false;

            if (is_array($rights)) {
                foreach($rights as $currentright) {
                    if ($level = $this->_perm->checkRight($currentright)) {
                        $hasright = max($hasright, $level);
                    } else {
                        $hasright = false;
                        break;
                    }
                }
            } else {
                // Remember: $rights is a single value at this point!
                $hasright = $this->_perm->checkRight($rights);
            }

            return $hasright;
        }

        return false;
    }

    /**
     * Wrapper method for the permission object's own checkRightLevel method.
     *
     * @access public
     * @param  mixed    A right id or an array of rights.
     * @param  mixed  $owner_user_id  Id or array of Ids of the owner of the
                                        ressource for which the right is requested.
     * @param  mixed  $owner_group_id Id or array of Ids of the group of the
     *                                  ressource for which the right is requested.
     * @return boolean
     */
    function checkRightLevel($rights, $owner_user_id, $owner_group_id)
    {
        if ($this->_error) {
            return false;
        }
        if (is_object($this->_perm)) {
            $hasright = false;

            if (is_array($rights)) {
                foreach($rights as $currentright) {
                    $level = $this->_perm->checkRightLevel($currentright, $owner_user_id, $owner_group_id);
                    if ($level) {
                        $hasright = max($hasright, $level);
                    } else {
                        $hasright = false;
                        break;
                    }
                }
            } else {
                // Remember: $rights is a single value at this point!
                $hasright = $this->_perm->checkRightLevel($rights, $owner_user_id, $owner_group_id);
            }

            return $hasright;
        }

        return false;
    }

    /**
     * Checks if a user is logged in.
     *
     * @access public
     * @return boolean
     */
    function isLoggedIn()
    {
        if ($this->_error) {
            return false;
        }
        if (!is_object($this->_auth)) {
            return false;
        }

        return $this->_auth->loggedIn;
    }

    /**
     * Function that determines if the user exists but hasn't yet been declared
     * "active" by an administrator.
     *
     * Use this to check if this was the reason
     * why a user was not able to login.
     * true == Not active
     * false == active
     *
     * @access public
     * @return boolean
     */
    function isInactive()
    {
        if ($this->_error) {
            return false;
        }

        return $this->status == LIVEUSER_STATUS_ISINACTIVE;
    }

    /**
     * Sets a callback login function.
     *
     * The user can set a function that will be called if the user
     * tries to access a page wihout logging in first. It will receive
     * the liveuser object. If an empty string or a non-existent function is passed
     * it deactivates the call.
     *
     * @access  public
     * @param   string  The name of the function to be called.
     * @return  mixed   void or PEAR_Error
     */
    function setLoginFunction($functionName)
    {
        if ($this->_error) {
            return false;
        }

        if (!empty($functionName) && is_callable($functionName)) {
            $this->_options['login']['function'] = $functionName;
            return;
        }

        return LiveUser::raiseError(LIVEUSER_ERROR_MISSING_LOGINFUNCTION, null, null,
            "Login function not found in LiveUser::setLoginFunction()");
    }

    /**
     * Sets a callback logout function.
     *
     * The user can set a function that will be called if the user
     * wants to logout (by providing the appropriate GET-parameter).
     * If an empty string or a non-existent function is passed
     * it deactivates the call.
     * <b>Attention: Don't use a die() or exit() statement in your logout function.
     *            Otherwise the user can't be logged out properly.</b>
     *
     * @access  public
     * @param   string  The name of the function to be called.
     * @return  mixed   void or a PEAR_Error
     * @see     LiveUser::_options
     */
    function setLogoutFunction($functionName)
    {
        if ($this->_error) {
            return false;
        }

        if (!empty($functionName) && is_callable($functionName)) {
            $this->_options['logout']['function'] = $functionName;
            return;
        }

        return LiveUser::raiseError(LIVEUSER_ERROR_MISSING_LOGOUTFUNCTION, null, null,
            "Logout function not found in LiveUser::setLogoutFunction()");
    }

    /**
     * Wrapper method to access properties from the auth and
     * permission containers.
     *
     * @access public
     * @param  string   Name of the property to be returned.
     * @param  string   'auth' or 'perm'
     * @return mixed    , a value or an array.
     */
    function getProperty($what, $container = 'auth')
    {
        if ($this->_error) {
            return false;
        }
        $that = null;
        if ($container == 'auth' && $this->_auth && $this->_auth->getProperty($what) !== null) {
            $that = $this->_auth ? $this->_auth->getProperty($what) : null;
        } elseif ($this->_perm && $this->_perm->getProperty($what) !== null) {
            $that = $this->_perm ? $this->_perm->getProperty($what) : null;
        }
        return $that;
    }

    /**
     * Returns a one-dimensional array with all rights assigned to this user.
     *
     * Array format depends on the optional parameter:
     * true: <code>array(intRight_ID => intRightLevel, ...)</code>
     * false <code>array(intRight_ID, ...) [Default]</code>
     * If no rights are available, false is returned.
     *
     * @access public
     * @param  boolean  Return array with right_id's as key and level as value
     * @return mixed    an array of rights or false
     */
    function getRights($withLevels = false)
    {
      if (is_object($this->_perm)) {
          return($this->_perm->getRights($withLevels));
      }
      return false;
    }

    /**
     * Get the current status.
     *
     * @access public
     * @return integer
     */
    function getStatus()
    {
        return $this->status;
    }

    /**
     * A "store" on the server contains the password and the
     * cookie id in an encrypted form.
     *
     * This method generates a md5 from given
     * password and writes it into the "store" along
     * with crypted password.
     *
     * Cookies are not secure but keeping
     * the password in plain text in the cookie
     * is not the best way to go. Since some
     * containers like LDAP need the clear text
     * password, we store an encrypted version
     * of the password using Crypt_Rc4 which provides
     * a simple two-way mechanism.
     *
     * To do this LiveUser needs access
     * to a writeable directory. If you do
     * no have access to the ini_get()
     * function please set a constant
     * named LIVEUSER_TMPDIR with an absolute
     * path to a writeable directory.
     *
     * @access private
     * @param  string   the password to store
     * @param  string   file name used as storage
     * @return boolean  true if success, false otherwise
     */
    function _storeCookiePasswdId($passwd, $store)
    {
        if (!defined('LIVEUSER_TMPDIR')) {
            define('LIVEUSER_TMPDIR', ini_get('session.save_path'));
        }

        if (!$fh = fopen(LIVEUSER_TMPDIR . "/$store.lu", 'wb')) {
            $this->_error = true;
            return LiveUser::raiseError(LIVEUSER_ERROR_COOKIE, null, null,
                "Cannot open file for writting in LiveUser::_storeCookiePasswdId()");
        }

        $data = serialize(array(md5($passwd), $passwd));

        $crypted_data = $this->_cookieCryptMode(true, $data);

        if (!fwrite($fh, $crypted_data)) {
            fclose($fh);
            $this->_error = true;
            return LiveUser::raiseError(LIVEUSER_COOKIE_ERROR, null, null,
                "Cannot save cookie data in LiveUser::_storeCookiePasswdId()");
        }

        fclose($fh);

        return true;
    }

    /**
     * A "store" on the server contains the password and the
     * cookie id in an encrypted form.
     *
     * This method reads the data contained in it.
     *
     * @access private
     * @param  string the filename of the store
     * @return mixed  an array of the data, false otherwise
     */
    function _readStoreCookiePasswdId($store)
    {
        if (!defined('LIVEUSER_TMPDIR')) {
            define('LIVEUSER_TMPDIR', ini_get('session.save_path'));
        }

        if (!$fh = fopen(LIVEUSER_TMPDIR . "/$store.lu", 'rb')) {
            $this->_error = true;
            return LiveUser::raiseError(LIVEUSER_ERROR_COOKIE, null, null,
                "Cannot open file for reading in LiveUser::_readStoreCookiePasswdId()");
        }

        if (!$fields = fread($fh, 4096)) {
            fclose($fh);
            $this->_error = true;
            return LiveUser::raiseError(LIVEUSER_COOKIE_ERROR, null, null,
                "Cannot read file in LiveUser::_readStoreCookiePasswdId()");
        }

        fclose($fh);

        $params = unserialize($this->_cookieCryptMode(false, $fields));

        return (count($params) == 2) ? $params : false;
    }

    /**
     * Tell whether a result from a LiveUser method is an error.
     *
     * @access  public
     * @param   mixed   result code
     * @return  bool    whether value is an error
     */
    function isError($value)
    {
        return (is_object($value) &&
            (is_a($value, 'pear_error') || is_subclass_of($value, 'pear_error')));
    } // end func isError

    /**
     * Return a textual error message for a LiveUser error code.
     *
     * @access  public
     * @param   int     error code
     * @return  string  error message
     */
    function errorMessage($value)
    {
        // make the variable static so that it only has to do the defining on the first call
        static $errorMessages;

        // define the varies error messages
        if (!isset($errorMessages)) {
            $errorMessages = array(
                LIVEUSER_ERROR                          => 'Unknown error',
                LIVEUSER_ERROR_NOT_SUPPORTED            => 'Feature not supported',
                LIVEUSER_ERROR_CONFIG                   => 'Config file error',
                LIVEUSER_ERROR_MISSING_DEPS             => 'Missing package depedencies',
                LIVEUSER_ERROR_MISSING_LOGINFUNCTION    => 'Login function not found',
                LIVEUSER_ERROR_MISSING_LOGOUTFUNCTION   => 'Logout function not found',
                LIVEUSER_ERROR_COOKIE                   => 'Remember Me cookie error',
                LIVEUSER_STATUS_EXPIRED                 => 'User session has expired',
                LIVEUSER_STATUS_ISINACTIVE              => 'User is set to inactive',
                LIVEUSER_STATUS_PERMINITERROR           => 'Cannot instantiate permission container',
                LIVEUSER_STATUS_AUTHINITERROR           => 'Cannot instantiate authentication configuration',
                LIVEUSER_STATUS_AUTHNOTFOUND            => 'Cannot retrieve Auth object from session',
                LIVEUSER_STATUS_UNKNOWN                 => 'Something went wrong in whatever you were trying to do',
                LIVEUSER_STATUS_LOGGEDOUT               => 'User was logged out correctly',
            );
        }

        // If this is an error object, then grab the corresponding error code
        if (LiveUser::isError($value)) {
            $value = $value->getCode();
        }

        // return the textual error message corresponding to the code
        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[LIVEUSER_ERROR];
    } // end func errorMessage
} // end class LiveUser
?>
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
 * Attempt at a unified admin class
 *
 * Simple usage:
 *
 * <code>
 * $conf = array(
 *  'autoInit' => false/true,
 *  'session'  => array(
 *      'name'    => 'liveuser session name',
 *      'varname' => 'liveuser session var name'
 *  ),
 *  'login' => array(
 *      'method'   => 'get or post',
 *      'username' => 'Form input containing user handle',
 *      'password' => 'Form input containing password',
 *      'remember' => '(optional) Form checkbox containing <Remember Me> info',
 *      'function' => '(optional) Function to be called when accessing a page without logging in first',
 *      'force'    => 'Should the user be forced to login'
 *  ),
 *  'logout' => array(
 *      'trigger'  => 'GET or POST var that triggers the logout process',
 *      'redirect' => 'Page path to be redirected to after logout',
 *      'function' => '(optional) Function to be called when accessing a page without logging in first',
 *      'destroy'  => 'Whether to destroy the session on logout'
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
 *            'authTableCols' => array('user_id'   => 'auth_user_id',
 *                                     'handle'    => 'handle',
 *                                     'passwd'    => 'passwd',
 *                                     'lastlogin' => 'lastlogin'
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
 * $admin = new LiveUser_Admin($conf, 'FR');
 * $found = $admin->getUser(3);
 *
 * if ($found) {
 *  var_dump($admin->perm->getRights());
 * }
 * </code>
 *
 * @author  Lukas Smith
 * @author  Arnaud Limbourg
 * @author Helgi Þormar Þorbjörnsson
 * @version $Id: Admin.php,v 1.1 2004/07/16 13:58:07 mloitzl Exp $
 * @package LiveUser
 */
class LiveUser_Admin
{

     /**
      * Name of the current selected auth container
      *
      * @access public
      * @var    string
      */
     var $authContainerName;

    /**
     * Array containing the auth objects.
     *
     * @access private
     * @var    array
     */
    var $_authContainers = array();

    /**
     * Admin perm object
     *
     * @access public
     * @var    object
     */
    var $perm = null;

    /**
     * Auth admin object
     *
     * @access public
     * @var    object
     */
    var $auth = null;

    /**
     * Configuration array
     *
     * @access private
     * @var    array
     */
     var $_conf = array();

     /**
      * Language to be used
      *
      * @access public
      * @var    string
      */
     var $lang = '';

    /**
     * Constructor
     *
     * @access protected
     * @param  array  liveuser conf array
     * @param  string two letters language code
     * @return void
     */
    function LiveUser_Admin($conf, $lang)
    {
        if (is_array($conf)) {
            $this->_conf = $conf;
        }
        $this->lang = $lang;

        if (isset($this->_conf['autoInit']) && $this->_conf['autoInit'] === true) {
            $this->setAdminContainers();
        }
    }

    /**
     * Merges the current configuration array with configuration array pases
     * along with the method call.
     *
     * @param  array   configuration array
     * @return boolean true upon success, false otherwise
     */
    function setConfArray($conf)
    {
        if (!is_array($conf)) {
            return false;
        }

        $this->_conf = LiveUser::arrayMergeClobber($this->_conf, $conf);
        return true;
    }

    /**
     * creates an instance of an auth object
     *
     * @access private
     * @param  mixed    Array containing the configuration.
     * @param  string   Name of the auth container.
     * @return object   Returns an object of an auth container
     */
    function &_authFactory($conf, $name = null)
    {
        if (!is_array($conf)) {
            return false;
        }
        $classname = 'LiveUser_Admin_Auth_Container_' . $conf['type'];
        $filename  = 'LiveUser/Admin/Auth/Container/' . $conf['type'] . '.php';
        @include_once($filename);
        if (!class_exists($classname)) {
            $this->_error = true;
            $error = LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
                'Missing file: '.$filename);
            return $error;
        }
        $auth = &new $classname($conf, $name);
        return $auth;
    }

    /**
     * creates an instance of an perm object
     *
     * @access private
     * @param  mixed    Name of array containing the configuration.
     * @return object   Returns an object of a perm container
     */
    function &_permFactory($conf)
    {
        if (!is_array($conf)) {
            return false;
        }
        $classname = 'LiveUser_Admin_Perm_Container_' . $conf['type'];
        $filename = 'LiveUser/Admin/Perm/Container/' . $conf['type'] . '.php';
        @include_once($filename);
        if (!class_exists($classname)) {
            $this->_error = true;
            $error = LiveUser::raiseError(LIVEUSER_NOT_SUPPORTED, null, null,
                'Missing file: '.$filename);
            return $error;
        }
        $perm = &new $classname($conf);
        return $perm;
    }

    /**
     * Sets the current auth container to the one with the given auth container name
     *
     * Upon success it will return true. You can then
     * access the auth backend container by using the
     * auth property of this class.
     *
     * e.g.: $admin->auth->addUser();
     *
     * @access public
     * @param  string   auth container name
     * @return boolean true upon success, false otherwise
     */
    function setAdminAuthContainer($authName)
    {
        if (!isset($this->_authContainers[$authName])
            || !is_object($this->_authContainers[$authName])
        ) {
            if (!isset($this->_conf['authContainers'][$authName])) {
                return false;
            }
            $this->_authContainers[$authName] =
                &$this->_authFactory($this->_conf['authContainers'][$authName], $authName);
        }
        $this->authContainerName = $authName;
        $this->auth = &$this->_authContainers[$authName];
        return true;
    }

    /**
     * Sets the perm container
     *
     * Upon success it will return true. You can then
     * access the perm backend container by using the
     * perm properties of this class.
     *
     * e.g.: $admin->perm->addUser();
     *
     * @access public
     * @return boolean true upon success, false otherwise
     */
    function setAdminPermContainer()
    {
        if (!is_array($this->_conf)) {
            return false;
        }

        $this->perm = &$this->_permFactory($this->_conf['permContainer']);
        $this->perm->authName = $this->_conf['permContainer']['type'];
        $this->perm->setCurrentLanguage($this->lang);
        return true;
    }

    /**
     * Tries to find a user in any of the auth container.
     *
     * Upon success it will return true. You can then
     * access the backend container by using the auth
     * and perm properties of this class.
     *
     * e.g.: $admin->perm->updateAuthUserId();
     *
     * @access public
     * @param  mixed   user auth id
     * @return boolean true upon success, false otherwise
     */
    function setAdminContainers($authId = null)
    {
        if (!is_array($this->_conf)) {
            return false;
        }

        if (is_null($authId)) {
            reset($this->_conf['authContainers']);
            $authName = key($this->_conf['authContainers']);
        } else {
            foreach ($this->_conf['authContainers'] as $k => $v) {
                if (!isset($this->_authContainers[$k]) ||
                    !is_object($this->_authContainers[$k])
                ) {
                    $this->_authContainers[$k] = &$this->_authFactory($v, $k);
                }

                if (!is_null($authId)) {
                    $match = $this->_authContainers[$k]->getUsers(array('auth_user_id' => $authId));
                    if (is_array($match) && sizeof($match) > 0) {
                        $authName = $k;
                        break;
                    }
                }
            }
        }

        if (isset($authName)) {
            if (!isset($this->perm) || !is_object($this->perm)) {
                $this->setAdminPermContainer();
            }
            $this->setAdminAuthContainer($authName);
            return true;
        }

        return false;
    }

    /**
     * Tries to add a user to both containers.
     *
     * If the optional $id parameter is passed it will be used
     * for both containers.
     *
     * In any case the auth and perm id will be equal when using this method.
     *
     * If this behaviour doesn't suit your needs please consider
     * using directly the concerned method. This method is just
     * implement to simplify things a bit and should satisfy most
     * user needs.
     *
     *  Note type is optional for DB, thus it's needed for MDB and MDB2,
     *  we recommend that you use type even though you use DB, so if you change to MDB[2],
     *  it will be no problem for you.
     *  usage example for addUser:
     * <code>
     *        $custom = array(
     *          array('name' => 'name', 'value' => 'asdf', 'type' => 'text'),
     *           array('name' => 'email', 'value' => 'fleh@example.com', 'type' => 'text')
     *      );
     *       $user_id = $admin->addUser('johndoe', 'dummypass', true, null, null, null, $custom);
     *  </code>
     *
     * Untested: it most likely doesn't work.
     *
     * @access public
     * @param  string  user handle (username)
     * @param  string  user password
     * @param  boolean is account active ?
     * @param  int          ID
     * @param  integer ID of the owning user.
     * @param  integer ID of the owning group.
     * @param  array  Array of custom fields to be added
     * @return mixed   userid or false
     */
    function addUser($handle, $password, $active = true, $id = null, $owner_user_id = null,
                    $owner_group_id = null, $customFields = array())
    {
        if (is_object($this->auth) && is_object($this->perm)) {
            $authId = $this->auth->addUser($handle, $password, $active, $owner_user_id,
                                            $owner_group_id, $id, $customFields);

            if (LiveUser::isError($authId)) {
                return $authId;
            }

            $permId = $this->perm->addUser($authId, $this->authContainerName, LIVEUSER_USER_TYPE_ID, $authId);

            if (LiveUser::isError($permId)) {
                return $permId;
            }

            return $authId;
        }
        return LiveUser::raiseError(LIVEUSER_ERROR, null, null,
                    'Perm or Auth container couldn\t be started.');
    }

    /**
    * Tried to changes user data for both containers.
    *
    *  Note type is optional for DB, thus it's needed for MDB and MDB2,
    *  we recommend that you use type even though you use DB, so if you change to MDB[2],
    *  it will be no problem for you.
    *  usage example for updateUser:
    * <code>
    *       $custom = array(
    *           array('name' => 'name', 'value' => 'asdfUpdated', 'type' => 'text'),
    *           array('name' => 'email', 'value' => 'fleh@example.comUpdated', 'type' => 'text')
    *       );
    *       $admin->updateUser($user_id, 'johndoe', 'dummypass', true, null, null, $custom);
    * </code>
    *
    * Untested: it most likely doesn't work.
    *
    * @access public
    * @param  string  user handle (username)
    * @param  string  user password
    * @param  boolean is account active ?
    * @param  int          ID
    * @param  integer  ID of the owning user.
    * @param  integer  ID of the owning group.
    * @param  array  Array of custom fields to be updated
    * @return mixed   error object or true
    */
    function updateUser($authId, $handle, $password, $active = true, $owner_user_id = null,
                    $owner_group_id = null, $customFields = array())
    {
        if (is_object($this->auth) && is_object($this->perm)) {
            $auth = $this->auth->updateUser($authId, $handle, $password, $active,
                                        $owner_user_id, $owner_group_id, $customFields);

            if (LiveUser::isError($auth)) {
                return $auth;
            }

            $permId = $this->perm->getPermUserId($authId, $this->authContainerName);
            $perm = $this->perm->updateAuthUserId($permId, $authId, $this->authContainerName);

            if (LiveUser::isError($perm)) {
                return $perm;
            }

            return true;
        }
        return LiveUser::raiseError(LIVEUSER_ERROR, null, null,
                    'Perm or Auth container couldn\t be started.');
    }

    /**
    * Removes user from both containers
    *
    * Untested: it most likely doesn't work.
    *
    * @access public
    * @param  mixed Auth ID
    * @return  mixed error object or true
    */
    function removeUser($authId)
    {
        if (is_object($this->auth) && is_object($this->perm)) {
            $this->auth->removeUser($authId);

            if (LiveUser::isError($authId)) {
                return $authId;
            }

            $permId = $this->perm->getPermUserId($authId, $this->perm->authName);
            $this->perm->removeUser($permId);

            if (LiveUser::isError($permId)) {
                return $permId;
            }

            return true;
        }
        return LiveUser::raiseError(LIVEUSER_ERROR, null, null,
                    'Perm or Auth container couldn\t be started.');
    }

    /**
    * Searches users with given filters and returns
    * all users found with their handle, passwd, auth_user_id
    * lastlogin, is_active and the customFields if they are specified
    *
    * Untested: it most likely doesn't work.
    *
    * @access public
    * @param   array   filters to apply to fetched data
    * @param   array  custom fields you want to be returned. If not specified
    *                 the basic set of fields is returned. The keys are the
    *                 names and the values
    * @param   string  if not null 'ORDER BY $order' will be appended to the query
    * @param   boolean will return an associative array with the auth_user_id
    *                  as the key by using DB::getAssoc() instead of DB::getAll()
    * @return mixed error object or array
    */
    function searchUsers($filters = array(), $customFields = array(), $order = null,
                        $rekey = false)
    {
        if (is_object($this->auth) && is_object($this->perm)) {
            $search = $this->auth->getUsers($filters, $customFields, $order, $rekey);

            if (LiveUser::isError($search)) {
                return $search;
            }

            return $search;
        }
        return LiveUser::raiseError(LIVEUSER_ERROR, null, null,
                    'Perm or Auth container couldn\t be started.');
    }

    /**
    * Finds and gets userinfo by his userID, customFields can
    *  also be gotten
    *
    * Untested: it most likely doesn't work.
    *
    * @access public
    * @param mixed User ID
    * @param   array  custom fields you want to be returned. If not specified
    *                 the basic set of fields is returned. The keys are the
    *                 names and the values
    * @return mixed Array with userinfo if found else error object
    */
    function getUser($userId, $customFields = array())
    {
        if (is_object($this->auth) && is_object($this->perm)) {
            if (is_array($this->auth->authTableCols['user_id'])) {
                $user_auth_id = $this->auth->authTableCols['user_id']['name'];
                $type = $this->auth->authTableCols['user_id']['type'];
            } else {
                $user_auth_id = $this->auth->authTableCols['user_id'];
                $type = '';
            }

            $filters = array($user_auth_id =>
                    array('op' => '=', 'value' => $userId, 'cond' => '', 'type' => $type)
            );

            $search = $this->auth->getUsers($filters, $customFields);

            if (LiveUser::isError($search)) {
                return $search;
            }

            return $search;
        }
        return LiveUser::raiseError(LIVEUSER_ERROR, null, null,
                    'Perm or Auth container couldn\t be started.');
    }
}
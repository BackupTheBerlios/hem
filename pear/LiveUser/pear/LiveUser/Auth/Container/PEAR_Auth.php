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
 * PEAR_Auth container for Authentication
 *
 * @package  LiveUser
 * @category authentication
 */

/**
 * Require parent class definition and PEAR::Auth class.
 */
require_once 'LiveUser/Auth/Common.php';
require_once 'Auth/Auth.php';

/**
 * Class LiveUser_Auth_Container_PEAR_Auth
 *
 * ==================== !!! WARNING !!! ========================================
 *
 *      THIS CONTAINER IS UNDER HEAVY DEVELOPMENT. IT'S STILL IN EXPERIMENTAL
 *      STAGE. USE IT AT YOUR OWN RISK.
 *
 * =============================================================================
 *
 * Description:
 * This is a PEAR::Auth backend driver for the LiveUser class.
 * The general options to setup the PEAR::Auth class can be passed to the constructor.
 * To choose the right auth container, you have to add the 'pearAuthContainer' var to
 * the options array.
 *
 * Requirements:
 * - File "LoginManager.php" (contains the parent class "LiveUser")
 * - PEAR::Auth must be installed in your PEAR directory
 * - Array of setup options must be passed to the constructor.
 *   Example:
 *   $conf = array(
 *       'authContainers' => array(
 *            'DB' => array(
 *               'type'           => 'DB',
 *               'name'           => 'DB_Local',
 *               'loginTimeout'   => 0,
 *               'expireTime'     => 3600,
 *               'idleTime'       => 1800,
 *               'dsn'            => $dsn_lu,
 *               'allowDuplicateHandles' => 0,
 *               'authTable'      => 'liveuser_users',
 *               'authTableCols'  => array(
 *                   'user_id'    => 'auth_user_id',
 *                   'handle'     => 'handle',
 *                   'passwd'     => 'passwd',
 *                   'lastlogin'  => 'lastlogin',
 *                   'is_active'  => 'is_active'
 *               )
 *           ),
 *           'LDAP' => array(
 *               'type'              => 'PEAR_Auth',
 *               'pearAuthContainer' => 'LDAP',
 *               'pearAuthOptions'   => array(
                    'host'              => 'mein.ldap.server.de',
 *                  'port'              => '389',
 *                  'basedn'            => 'dc=company,dc=com',
 *                  'userattr'          => 'uid',
 *                  'useroc'            => 'person',
 *                )
 *           )
 *       )
 *   );
 * @author  Bjoern Kraus <krausbn@php.net>
 * @version $Id: PEAR_Auth.php,v 1.1 2004/07/16 13:59:04 mloitzl Exp $
 * @package LiveUser
 * @category authentication
 */
class LiveUser_Auth_Container_PEAR_Auth extends LiveUser_Auth_Common
{
    /**
     * Contains the PEAR::Auth object.
     *
     * @var object
     */
    var $pearAuth = null;

    /**
     * LiveUser_Auth_Container_PEAR_Auth::LiveUser_Auth_Container_Auth()
     *
     * Class constructor.
     *
     * @param  mixed
     */
    function &LiveUser_Auth_Container_PEAR_Auth(&$connectOptions)
    {
        require_once 'Auth/Auth.php';
        if (!is_object($this->pearAuth)) {
            $this->pearAuth = new Auth($connectOptions['pearAuthContainer'], $connectOptions['pearAuthOptions'], '', false);
        }
    }

    /**
     * LiveUser_Auth_Container_PEAR_Auth::unfreeze()
     *
     * @param mixed $connectOptions
     * @access public
     */
    function unfreeze($propertyValues)
    {
        parent::unfreeze($propertyValues);
    }

    /**
     * LiveUser_Auth_Container_PEAR_Auth::freeze()
     *
     * @access public
     */
    function freeze()
    {
        return parent::freeze();
    }

    /**
     * LiveUser_Auth_Container_PEAR_Auth::readUserData()
     *
     * @return boolean
     */
    function readUserData()
    {
        $this->pearAuth->start();

        $success = false;

        // If a user was found, read data into class variables and set
        // return value to true
        if ($this->pearAuth->getAuth()) {
            $this->handle       = $this->pearAuth->getUsername();
            $this->passwd       = $this->encryptPW($this->pearAuth->password);
            $this->isActive     = true;
            $this->authUserId   = $this->pearAuth->getUsername();;
            $this->lastLogin    = '';

            $success = true;
        }
        return $success;
    }

    /**
     * LiveUser_Auth_Container_PEAR_Auth::_updateUserData()
     *
     * @return boolean
     */
    function _updateUserData()
    {
        return true;
    }

}
?>
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
 * MDB2_Simple container for permission handling
 *
 * @package  LiveUser
 * @category authentication
 */

/**
 * Require parent class definition and PEAR::MDB2 class.
 */
require_once 'LiveUser/Perm/Common.php';
require_once 'MDB2.php';

/**
 * Class LiveUser_Perm_Container_MDB2_Simple
 *
 * Simple MDB2-based complexity driver for LiveUser.
 *
 * Description:
 * The MDB2_Simple provides the following functionalities
 * - users
 * - userrights
 *
 * @author  Björn Kraus <krausbn@php.net>
 * @version $Id: MDB2_Simple.php,v 1.1 2004/06/08 11:42:57 mloitzl Exp $
 * @package LiveUser
 * @category authentication
 */
class LiveUser_Perm_Container_MDB2_Simple extends LiveUser_Perm_Common
{
    /**
     * dsn that was connected to
     * @var object
     * @access private
     */
    var $dsn = null;

    /**
     * disconnect
     * @var object
     * @access private
     */
    var $disconnect = null;

    /**
     * PEAR::MDB2 connection object.
     *
     * @var    object
     * @access private
     */
    var $dbc = null;

    /**
     * Table prefix
     * Prefix for all db tables the container has.
     *
     * @var    string
     * @access public
     */
    var $prefix = 'liveuser_';

    /**
     * Indicates if backend module initialized correctly. If yes,
     * true, if not false. Backend module won't initialize if the
     * init value (usually an object or resource handle that
     * identifies the backend to be used) is not of the required
     * type.
     *
     * @var    boolean
     * @access public
     */
    var $init_ok = false;

    /**
     * Constructor
     *
     * @param  mixed    $connectOptions  Array or PEAR::MDB2 object.
     * @return void
     */
    function &LiveUser_Perm_Container_MDB2_Simple(&$connectOptions)
    {
        if (is_array($connectOptions)) {
            foreach ($connectOptions as $key => $value) {
                if (isset($this->$key)) {
                    $this->$key = $value;
                }
            }
            if (isset($connectOptions['connection'])  &&
                    MDB2::isConnection($connectOptions['connection'])
            ) {
                $this->dbc     = &$connectOptions['connection'];
                $this->init_ok = true;
            } elseif (isset($connectOptions['dsn'])) {
                $this->dsn = $connectOptions['dsn'];
                $options = null;
                if (isset($connectOptions['options'])) {
                    $options = $connectOptions['options'];
                }
                $options['portability'] = MDB2_PORTABILITY_ALL;
                if ($function == 'singleton') {
                    $this->dbc =& MDB2::singleton($connectOptions['dsn'], $options);
                } else {
                    $this->dbc =& MDB2::connect($connectOptions['dsn'], $options);
                }
                if (!MDB2::isError($this->dbc)) {
                    $this->init_ok = true;
                }
            }
        }
    }

    /**
     * Tries to find the user with the given user ID in the permissions
     * container. Will read all permission data and return true on success.
     *
     * @access  public
     * @param   string  $uid  user identifier
     * @return  mixed   true on success or a PEAR_Error object
     */
    function init($uid)
    {
        $success = true;

        $query = '
            SELECT
                LU.perm_user_id AS userid,
                LU.perm_type    AS usertype
            FROM
                '.$this->prefix.'perm_users LU
            WHERE
                auth_user_id='.$this->dbc->quote($uid, 'text');

        $result = $this->dbc->queryRow($query, array('integer', 'integer'), MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($result)) {
            return $result;
        }

        $this->permUserId = $result['userid'];
        $this->userType   = $result['usertype'];

        $this->readRights();

        return $success;
    } // end func init

    /**
     * properly disconnect from resources
     *
     * @access  public
     */
    function disconnect()
    {
        if ($this->disconnect) {
            $this->dbc->disconnect();
            $this->dbc = null;
        }
    }

    /**
     * LiveUser_Perm_Container_MDB2_Complex::userExists()
     *
     * Checks if a user with the given perm_user_id exists in the
     * permission container and returns true on success.
     *
     * @access public
     * @param  integer  $user_id  The users id in the permission table.
     * @return boolean  true if the id was found, else false.
     */
    function userExists($user_id)
    {
        if ($this->init_ok) {
            $query = '
                SELECT
                    1
                FROM
                    '.$this->prefix.'perm_users
                WHERE
                    perm_user_id='.$this->dbc->quote($user_id, 'integer');

            $res = $this->dbc->queryOne($query, 'integer');

            if (MDB2::isError($res) || is_null($res)) {
                return false;
            }

            return true;
        }
        return false;
    }

    /**
     * Reads all rights of current user into an
     * associative array.
     *
     * Right => 1
     *
     * @access  public
     * @return  void
     */
    function readRights()
    {
        $query = '
            SELECT
                R.right_id AS rightid, '.LIVEUSER_MAX_LEVEL.'
            FROM
                '.$this->prefix.'userrights UR
            INNER JOIN
                '.$this->prefix.'rights R
            ON
                UR.right_id=R.right_id
            WHERE
                UR.perm_user_id='.$this->dbc->quote($this->permUserId, 'integer');
        $types = array('integer', 'integer');
        $result = $this->dbc->queryAll($query, $types, MDB2_FETCHMODE_ORDERED, true);

        if (MDB2::isError($result)) {
            return $result;
        } // if

        $this->rights = $result;
    } // end func readRights
} // end class LiveUser_Perm_Container_MDB2_Simple
?>

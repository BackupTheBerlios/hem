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
 * MDB_Simple container for permission handling
 *
 * @package  LiveUser
 * @category authentication
 */

/**
 * Require parent class definition and PEAR::MDB class.
 */
require_once 'LiveUser/Perm/Common.php';
require_once 'MDB.php';

/**
 * Class LiveUser_Perm_Container_MDB_Simple
 *
 * Simple MDB-based complexity driver for LiveUser.
 *
 * Description:
 * The MDB_Simple provides the following functionalities
 * - users
 * - userrights
 *
 * @author  Björn Kraus <krausbn@php.net>
 * @version $Id: MDB_Simple.php,v 1.1 2004/07/16 13:58:49 mloitzl Exp $
 * @package LiveUser
 * @category authentication
 */
class LiveUser_Perm_Container_MDB_Simple extends LiveUser_Perm_Common
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
     * PEAR::MDB connection object.
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
     * @param  mixed    $connectOptions  Array or PEAR::MDB object.
     * @return void
     */
    function &LiveUser_Perm_Container_MDB_Simple(&$connectOptions)
    {
        if (is_array($connectOptions)) {
            foreach ($connectOptions as $key => $value) {
                if (isset($this->$key)) {
                    $this->$key = $value;
                }
            }
            if (isset($connectOptions['connection'])  &&
                    MDB::isConnection($connectOptions['connection'])
            ) {
                $this->dbc     = &$connectOptions['connection'];
                $this->init_ok = true;
            } elseif (isset($connectOptions['dsn'])) {
                $this->dsn = $connectOptions['dsn'];
                $options = null;
                if (isset($connectOptions['options'])) {
                    $options = $connectOptions['options'];
                }
                $options['optimize'] = 'portability';
                if ($function == 'singleton') {
                    $this->dbc =& MDB::singleton($connectOptions['dsn'], $options);
                } else {
                    $this->dbc =& MDB::connect($connectOptions['dsn'], $options);
                }
                if (!MDB::isError($this->dbc)) {
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
                auth_user_id='.$this->dbc->getValue('text', $uid);

        $result = $this->dbc->queryRow($query, array('integer', 'integer'), MDB_FETCHMODE_ASSOC);

        if (MDB::isError($result)) {
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
     * LiveUser_Perm_Container_MDB_Complex::userExists()
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
                    perm_user_id='.$this->dbc->getValue('integer', $user_id);

            $res = $this->dbc->queryOne($query, 'integer');

            if (MDB::isError($res) || is_null($res)) {
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
                UR.perm_user_id='.$this->dbc->getValue('integer', $this->permUserId);
        $types = array('integer', 'integer');
        $result = $this->dbc->queryAll($query, $types, MDB_FETCHMODE_ORDERED, true);

        if (MDB::isError($result)) {
            return $result;
        } // if

        $this->rights = $result;
    } // end func readRights
} // end class LiveUser_Perm_Container_MDB_Simple
?>

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
 * DB_Medium container for permission handling
 *
 * @package  LiveUser
 * @category authentication
 */

/**
 * Require parent class definition.
 */
require_once 'LiveUser/Perm/Container/DB_Simple.php';

/**
 * Class LiveUser_Perm_Container_DB_Medium
 *
 * Medium DB-based complexity driver for LiveUser.
 *
 * Description:
 * The DB_Medium provides the following functionalities
 * - users
 * - groups
 * - grouprights
 * - userrights
 * - authareas
 *
 * @author  Arnaud Limbourg
 * @version $Id: DB_Medium.php,v 1.1 2004/07/16 13:58:49 mloitzl Exp $
 * @package LiveUser
 * @category authentication
 */
class LiveUser_Perm_Container_DB_Medium extends LiveUser_Perm_Container_DB_Simple
{
    /**
     * Constructor
     *
     * @param  mixed    $connectOptions  Array or PEAR::DB object.
     */
    function &LiveUser_Perm_Container_DB_Medium(&$connectOptions)
    {
        $this->LiveUser_Perm_Container_DB_Simple($connectOptions);
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
                ' . $this->prefix . 'perm_users LU
            WHERE
                auth_user_id=' . $this->dbc->quoteSmart($uid);

        $result = $this->dbc->getRow($query, null, DB_FETCHMODE_ASSOC);

        if (DB::isError($result)) {
               return $result;
        }

        $this->permUserId = $result['userid'];
        $this->userType   = $result['usertype'];

        $this->readRights();

        return $success;
    } // end func init

    /**
     * Reads all rights of current user into an
     * associative array.
     * Group rights and invididual rights are being merged
     * in the process.
     *
     * @access private
     * @return void
     */
    function readRights()
    {
        // reset rights
        $this->rights = array();

        $this->readUserRights();
        $this->readGroups();
        $this->readGroupRights();

        $tmpRights = $this->groupRights;

        // Check if user has individual rights...
        if (is_array($this->userRights)) {
            // Overwrite values from temporary array with values from userrights
            foreach ($this->userRights as $right => $level) {
                if (isset($tmpRights[$right])) {
                    if ($level < 0) {
                        // Revoking rights: A negative value indicates that the
                        // right level is lowered or the right is even revoked
                        // despite the group memberships of this user
                        $tmpRights[$right] = $tmpRights[$right] + $level;
                    } else {
                        $tmpRights[$right] = max($tmpRights[$right], $level);
                    }
                } else {
                    $tmpRights[$right] = $level;
                }
            }
        }

        // Strip values from array if level is not greater than zero
        $cRights = array();
        if (is_array($tmpRights)) {
            foreach ($tmpRights as $right => $level) {
               if ($level > 0) {
                   $cRights[$right] = $level;
               }
            }
        }

        $this->rights = $cRights;
    } // end func readRights

    /**
     * Reads the user rights and put them in an array
     *
     * right => level
     *
     * This level is either 1 or -1
     * i.e. you grant the user the right
     * or you do not allow him to have
     * the right.
     *
     * @access  public
     * @return  mixed   DB_Error on failure or nothing
     */
    function readUserRights()
    {
        $this->userRights = array();

        $query = '
            SELECT
                R.right_id,
                U.right_level
            FROM
                ' . $this->prefix . 'rights R
            INNER JOIN
                ' . $this->prefix . 'userrights U
            ON
                R.right_id=U.right_id
            WHERE
                U.perm_user_id=' . $this->permUserId;

        $result = $this->dbc->getAssoc($query);

        if (DB::isError($result)) {
            return $result;
        }

        if (is_array($result)) {
            $this->userRights = $result;
        }

        if ($this->userType == LIVEUSER_AREAADMIN_TYPE_ID) {
            // get all areas in which the user is area admin
            $query = '
                SELECT
                    R.right_id,
                    '.LIVEUSER_MAX_LEVEL.' AS right_level
                FROM
                    '.$this->prefix.'area_admin_areas AAA,
                    '.$this->prefix.'rights R
                WHERE
                    AAA.area_id=R.area_id
                AND
                    AAA.perm_user_id='.$this->permUserId;

            $result = $this->dbc->getAssoc($query);

            if (DB::isError($result)) {
               return $result;
            }

            if (is_array($result)) {
                if (is_array($this->userRights)) {
                    $this->userRights = $result + $this->userRights;
                } else {
                    $this->userRights = $result;
                }
            }
        }
    } // end func readUserRights

    /**
     * Reads all the group ids in that the user is also a member of
     * (all groups that are subgroups of these are also added recursively)
     *
     * @access private
     * @see    readRights()
     * @return void
     */
    function readGroups()
    {
        $query = '
            SELECT
                GU.group_id
            FROM
                '.$this->prefix.'groupusers GU
            INNER JOIN
                '.$this->prefix.'groups G
            ON
                GU.group_id=G.group_id
            WHERE
                G.is_active=\'Y\'
            AND
                perm_user_id='.$this->permUserId;

        $result = $this->dbc->getCol($query);

        if (!DB::isError($result)) {
            $this->groupIds = $result;
        } else {
            $this->groupIds = array();
        }
    } // end func readGroups

    /**
     * Reads the group rights
     * and put them in the array
     *
     * right => 1
     *
     * @access  public
     * @return  mixed   DB_Error on failure or nothing
     */
    function readGroupRights()
    {
        $this->groupRights = array();

        if (count($this->groupIds)) {
            $query = '
                SELECT
                    GR.right_id,
                    MAX(GR.right_level)
                FROM
                    '.$this->prefix.'grouprights GR
                WHERE
                    GR.group_id IN('.implode(',', $this->groupIds).')
                GROUP BY GR.right_id';

            $result = $this->dbc->getAssoc($query);

            if (DB::isError($result)) {
                return $result;
            }

            if (is_array($result)) {
                $this->groupRights = $result;
            }
        }
    } // end func readGroupRights
} // end class LiveUser_Perm_Container_DB_Medium
?>

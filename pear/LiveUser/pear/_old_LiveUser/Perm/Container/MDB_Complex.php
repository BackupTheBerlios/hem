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
 * MDB_Complex container for permission handling
 *
 * @package  LiveUser
 * @category authentication
 */

/**
 * Require parent class definition.
 */
require_once 'LiveUser/Perm/Container/MDB_Medium.php';

/**
 * Class LiveUser_Perm_Container_MDB_Complex
 *
 * Description:
 * This is a PEAR::MDB backend driver for the LiveUser class.
 * A PEAR::MDB connection object can be passed to the constructor to reuse an
 * existing connection. Alternatively, a DSN can be passed to open a new one.
 *
 * Requirements:
 * - File "Liveuser.php" (contains the parent class "LiveUser")
 * - Array of connection options or a PEAR::MDB connection object must be
 *   passed to the constructor.
 *   Example: array('dsn' => 'mysql://user:pass@host/db_name')
 *              OR
 *            &$conn (PEAR::MDB connection object)
 *
 * @author  Lukas Smith <smith@backendmedia.com>
 * @author  Bjoern Kraus <krausbn@php.net>
 * @version $Id: MDB_Complex.php,v 1.1 2004/07/16 13:58:49 mloitzl Exp $
 * @package LiveUser
 * @category authentication
 */
class LiveUser_Perm_Container_MDB_Complex extends LiveUser_Perm_Container_MDB_Medium
{
    /**
     * Constructor
     *
     * @param  mixed    $connectOptions  Array or PEAR::MDB object.
     * @return void
     */
    function &LiveUser_Perm_Container_MDB_Complex(&$connectOptions)
    {
        $this->LiveUser_Perm_Container_MDB_Medium($connectOptions);
    }

    /**
     * LiveUser_Perm_Container_MDB_Complex::_readImpliedRights()
     *
     * Reads all individual implied rights of current user into
     * an array of this format:
     * RightName -> Value
     *
     * @access private
     * @return array with rightIds as key and level as value
     */
    function _readImpliedRights($rightIds, $table)
    {
        if (count($rightIds) > 0) {
            $queue = array();
            $query = '
                SELECT
                DISTINCT
                    TR.right_level,
                    TR.right_id
                FROM
                    '.$this->prefix.'rights R,
                    '.$this->prefix.$table.'rights TR
                WHERE
                    TR.right_id=R.right_id
                AND
                    R.right_id IN ('.implode(',', array_keys($rightIds)).')
                AND
                    R.has_implied='.$this->dbc->getValue('boolean', true);

                if ($table == 'user') {
                    $query .= ' AND TR.perm_user_id = '.$this->dbc->getValue('integer', $this->permUserId);
                } else {
                    $query .= ' AND TR.group_id IN ('.implode(',', $this->groupIds).')';
                }

            $types = array('integer', 'integer');
            $result = $this->dbc->queryAll($query, $types, MDB_FETCHMODE_ORDERED, true, false, true);

            if (MDB::isError($result)) {
                return $result;
            }

            if (is_array($result)) {
                $queue = $result;
            }

            reset($queue);
            while (count($queue) > 0) {
                $currentLevel = key($queue);
                $currentRights = array_shift($queue);

                $query = '
                    SELECT
                        RI.implied_right_id AS right_id,
                        '.$currentLevel.' AS right_level,
                        R.has_implied
                    FROM
                        '.$this->prefix.'rights R,
                        '.$this->prefix.'right_implied RI
                    WHERE
                        RI.implied_right_id=R.right_id
                    AND
                        RI.right_id IN ('.implode(',', $currentRights).')';

                $types = array('integer', 'integer', 'boolean');
                $result = $this->dbc->queryAll($query, $types, MDB_FETCHMODE_ASSOC);

                if (MDB::isError($result)) {
                    return $result;
                } elseif (is_array($result)) {
                    foreach ($result as $val) {
                        // only store the implied right if the right wasn't stored before
                        // or if the level is higher
                        if (!isset($rightIds[$val['right_id']])
                            || $rightIds[$val['right_id']] < $val['right_level']
                        ) {
                            $rightIds[$val['right_id']] = $val['right_level'];
                            if ($val['has_implied']) {
                                $queue[$val['right_level']][] = $val['right_id'];
                            }
                        }
                    }
                }
            }
        }
        return $rightIds;
    } // end func _readImpliedRights

    /**
     * LiveUser_Perm_Container_MDB_Complex::readUserRights()
     *
     * Reads all individual rights of current user into
     * an array of this format:
     * RightName -> Value
     *
     * @access private
     * @see    readRights()
     * @return void
     */
    function readUserRights()
    {
        // lets use MDB_Medium here
        // its obviously not as efficient in terms of run time behavior
        // since we now have to fetch the implied rights separately
        // but it saves a lot of code
        // if we care about performance this is not done at run time anyways
        parent::readUserRights();

        $this->userRights = $this->_readImpliedRights($this->userRights, 'user');
    } // end func readUserRights

    /**
     * LiveUser_Perm_Container_MDB_Complex::readGroups()
     *
     * Reads all the group ids in that the user is also a member of
     * (all groups that are subgroups of these are also added recursively)
     *
     * @access private
     * @see    readRights()
     * @return void
     */
    function readGroups()
    {
        parent::readGroups();

        $result = $this->groupIds;
        // get all subgroups recursively
        while (count($result) > 0) {
            $query = '
                SELECT
                    DISTINCT SG.subgroup_id
                FROM
                    '.$this->prefix.'groups G,
                    '.$this->prefix.'group_subgroups SG
                WHERE
                    SG.subgroup_id = G.group_id
                AND
                    SG.group_id IN ('.implode(', ', $result).')
                AND
                    SG.subgroup_id NOT IN ('.implode(', ', $this->groupIds).')
                AND
                    G.is_active='.$this->dbc->getValue('boolean', true);

            $result = $this->dbc->queryCol($query);

            if (MDB::isError($result)) {
                break;
            } else {
                $this->groupIds = array_merge($result, $this->groupIds);
            }
        }
    } // end func readGroups

    /**
     * LiveUser_Perm_Container_MDB_Complex::readGroupRights()
     *
     * Reads all individual rights of current user into
     * a two-dimensional array of this format:
     * "GroupName" => "RightName" -> "Level"
     *
     * @access private
     * @see    readRights()
     * @return void
     */
    function readGroupRights()
    {
        // lets use MDB_Medium here
        // its obviously not as efficient in terms of run time behavior
        // since we now have to fetch the implied rights separately
        // but it saves a lot of code
        // if we care about performance this is not done at run time anyways
        parent::readGroupRights();

        $this->groupRights = $this->_readImpliedRights($this->groupRights, 'group');
    } // end func readGroupRights

    /**
     * LiveUser_Perm_Container_MDB_Complex::checkLevel()
     *
     * Checks if the current user has a certain right in a
     * given area at the necessary level.
     *
     * Level 1: requires that owner_user_id matches $this->permUserId
     * Level 2: requires that the $owner_group_id matches the id one of
     *          the (sub)groups that $this->permUserId is a memember of
     *          or requires that the $owner_user_id matches a perm_user_id of
     *          a memeber of one of $this->permUserId's (sub)groups
     * Level 3: no requirements
     *
     * Important note:
     *          Every ressource MAY be owned by a user and/or by a group.
     *          Therefore, $owner_user_id and/or $owner_group_id can
     *          either be an integer or null.
     *
     * @access private
     * @see    checkRightLevel()
     * @param  integer  $level          Level value as returned by checkRight().
     * @param  mixed  $owner_user_id  Id or array of Ids of the owner of the
                                        ressource for which the right is requested.
     * @param  mixed  $owner_group_id Id or array of Ids of the group of the
     *                                  ressource for which the right is requested.
     * @return boolean  level if the level is sufficient to grant access else false.
     */
    function checkLevel($level, $owner_user_id, $owner_group_id)
    {
        // level above 0
        if ($level > 0) {
            // highest level (that is level 3)
            if ($level == LIVEUSER_MAX_LEVEL) {
                return $level;
            } elseif ($level >= 1) {
                // level 1 or higher
                if ((!is_array($owner_user_id) && $this->permUserId == $owner_user_id) ||
                    is_array($owner_user_id) && in_array($this->permUserId, $owner_user_id)
                ) {
                    return $level;
                // level 2 or higher
                } elseif ($level >= 2) {
                    // check if the ressource is owned by a (sub)group
                    // that the user is part of
                    if (is_array($owner_group_id)) {
                        if (count(array_intersect($owner_group_id, $this->groupIds))) {
                            return $level;
                        }
                    } elseif (in_array($owner_group_id, $this->groupIds)) {
                        return $level;
                    }
                }
            }
        }
        return false;
    } // end func checkLevel

    /**
     * LiveUser_Perm_Container_MDB_Complex::checkRightLevel()
     *
     * Checks if the current user has a certain right with the sufficient
     * level to gain access to a specific ressource in that area.
     *
     * @access public
     * @see    LiveUser_Perm_Common:checkRight(), checkLevel()
     * @param  integer  Id of the right to check for.
     * @param  mixed  $owner_user_id  Id or array of Ids of the owner of the
                                        ressource for which the right is requested.
     * @param  mixed  $owner_group_id Id or array of Ids of the group of the
     *                                  ressource for which the right is requested.
     * @return boolean  true if the level is sufficient to grant access else false.
     */
    function checkRightLevel($right_id, $owner_user_id, $owner_group_id)
    {
        $level = $this->checkRight($right_id);
        $hasright = $this->checkLevel($level, $owner_user_id, $owner_group_id);

        return $hasright;
    }
}
?>
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
 * Base class for permission handling
 *
 * @package  LiveUser
 * @category authentication
 */

/**#@+
 * Usertypes
 *
 * @var integer
 */
/**
 * lowest user type id
 */
define('LIVEUSER_ANONYMOUS_TYPE_ID',    0);
/**
 * lowest user type id
 */
// higest user type id
define('LIVEUSER_USER_TYPE_ID',         1);
/**
 * lowest admin type id
 */
define('LIVEUSER_ADMIN_TYPE_ID',        2);
define('LIVEUSER_AREAADMIN_TYPE_ID',    3);
define('LIVEUSER_SUPERADMIN_TYPE_ID',   4);
/**
 * higest admin type id
 */
define('LIVEUSER_MASTERADMIN_TYPE_ID',  5);

/**
 * This class provides a set of functions for implementing a user
 * permission management system on live websites. All authorisation
 * backends/containers must be extensions of this base class.
 *
 * @author  Markus Wolff <wolff@21st.de>
 * @author  Bjoern Kraus <krausbn@php.net>
 * @version $Id: Common.php,v 1.1 2004/07/16 13:59:10 mloitzl Exp $
 * @package LiveUser
 * @category authentication
 */
class LiveUser_Perm_Common
{
    /**
     * Unique user ID, used to identify users from the auth container.
     *
     * @var string
     */
    var $permUserId = '';

    /**
     * One-dimensional array containing current user's rights.
     * This already includes grouprights and possible overrides by
     * individual right settings.
     *
     * Format: "RightId" => "Level"
     *
     * @var mixed
     */
    var $rights = false;

    /**
     * One-dimensional array containing only the individual
     * rights for the actual user.
     *
     * Format: "RightId" => "Level"
     *
     * @var array
     */
    var $userRights = array();

    /**
     * Two-dimensional array containing all groups that
     * the user belongs to and the grouprights.
     *
     * Format: "GroupId" => "RightId" => "Level"
     *
     * @var array
     * @see $userRights
     * @see $rights
     */
    var $groupRights = array();

    /**
     * Defines the user type.
     *
     * @var integer
     */
    var $userType = LIVEUSER_ANONYMOUS_TYPE_ID;

    /**
     * Defines the (sub)groups in which the user is a member
     *
     * @var mixed
     */
    var $groupIds = false;

    /**
     * Defines if the user rights should be retrieved ondemand.
     *
     * @var boolean
     */
    var $ondemand = false;

    /**
     * Class constructor. Feel free to override in backend subclasses.
     */
    function LiveUser_Perm_Common()
    {
        // I do nothing here, override me plenty ;-)
    }

    /**
     * Tries to find the user with the given user ID in the permissions
     * container. Will read all permission data and return true on success.
     *
     * @access public
     * @param  string   $uid  User id in the auth container.
     * @return boolean  true if init process was successfull else false.
     */
    function init($uid)
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'init(): Method not supported by this container');
    }

    /**
     * store all properties in an array
     *
     * @access  public
     * @return  array containing the property values
     */
    function freeze()
    {
        $propertyValues = array(
            'permUserId'  => $this->permUserId,
            'rights'      => $this->rights,
            'userRights'  => $this->userRights,
            'groupRights' => $this->groupRights,
            'userType'    => $this->userType,
            'groupIds'    => $this->groupIds,
        );
        return $propertyValues;
    } // end func freeze

    /**
     * properly disconnect from resources
     *
     * @access  public
     */
    function disconnect()
    {
    }

    /**
     * Reinitializes properties
     *
     * @access  public
     * @param   array  $propertyValues
     */
    function unfreeze($propertyValues = false)
    {
        if ($propertyValues) {
            foreach ($propertyValues as $key => $value) {
                $this->{$key} = $value;
            }
            return true;
        }
        return $this->init();
    } // end func unfreeze

    /**
     * Reads all rights of current user into a
     * two-dimensional associative array, having the
     * area names as the key of the 1st dimension.
     * Group rights and invididual rights are being merged
     * in the process.
     */
    function readRights()
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'readRights(): Method not supported by this container');
    }

    /**
     * Reads all individual rights of current user into
     * a two-dimensional array of this format:
     * AreaName => RightName -> Value
     *
     * Again, this does nothing in the base class. The
     * described functionality must be implemented in a
     * subclass overriding this method.
     */
    function readUserRights()
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'readUserRights(): Method not supported by this container');
    }

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
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'readGroups(): Method not supported by this container');
    }

    /**
     * Reads all individual rights of current user into
     * a two-dimensional array of this format:
     * "GroupName" => "AreaName" -> "RightName"
     *
     * Again, this does nothing in the base class. The
     * described functionality must be implemented in a
     * subclass overriding this method.
     */
    function readGroupRights()
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'readGroupRights(): Method not supported by this container');
    }

    /**
     * Returns a one-dimensional array with all rights assigned
     * to this user. Array format depends on the optional parameter:
     * true: array(intRight_ID => intRightLevel, ...)
     * false array(intRight_ID, ...) [Default]
     * If no rights are available, false is returned.
     *
     * @param  boolean   $withLevels   Return array with right_id´s as
                                       key and level as value
     * @access public
     * @return mixed
     */
    function getRights($withLevels = false)
    {
        if (is_array($this->rights)) {
            if ($withLevels) {
                return $this->rights;
            } else {
                return array_keys($this->rights);
            }
        }

        // If there are no rights...
        return false;
    }

    /**
     * Checks if the current user has a certain right in a
     * given area.
     * If $this->ondemand is true, the rights will be loaded on the fly.
     *
     * @access  public
     * @param   integer $right_id  Id of the right to check for.
     * @return  integer Level of the right.
     */
    function checkRight($right_id)
    {
        if (is_array($this->rights)) {
            // check if the user is above areaadmin
            if (!$right_id || $this->userType > LIVEUSER_AREAADMIN_TYPE_ID) {
                return LIVEUSER_MAX_LEVEL;
            } else {
                // If he does, look for the right in question.
                if (in_array($right_id, array_keys($this->rights))) {
                    // We know the user has the right so the right level will be returned.
                    return $this->rights[$right_id];
                }
            }
        } elseif ($this->ondemand) {
            $this->readRights();
            return $this->checkRight($right_id);
        }
        return false;
    } // end func checkRight

    /**
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
     * @param  integer  $level          Level value as returned by checkRight().
     * @param  mixed  $owner_user_id  Id or array of Ids of the owner of the
                                        ressource for which the right is requested.
     * @param  mixed  $owner_group_id Id or array of Ids of the group of the
     *                                  ressource for which the right is requested.
     * @return boolean  true if the level is sufficient to grant access else false.
     */
    function checkLevel($level, $owner_user_id, $owner_group_id)
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'checkLevel(): Method not supported by this container');
    }

    /**
     * Checks if a user with the given perm_user_id exists in the
     * permission container and returns true on success.
     *
     * @access public
     * @param  integer  The users id in the permission table.
     * @return boolean  true if the id was found, else false.
     */
    function userExists($user_id)
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'userExists(): Method not supported by this container');
    }

    /**
     * Function returns the inquired value if it exists in the class.
     *
     * @param  string   Name of the property to be returned.
     * @return mixed    null, a value or an array.
     */
    function getProperty($what)
    {
        $that = null;
        if (isset($this->$what)) {
            $that = $this->$what;
        }
        return $that;
    }
}
?>
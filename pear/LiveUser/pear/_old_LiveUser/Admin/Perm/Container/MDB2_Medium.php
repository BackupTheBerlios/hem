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
 * MDB2_Medium permission administration class
 *
 * @package  LiveUser
 * @category authentication
 */

/**
 * Require the parent class definition
 */

require_once 'LiveUser/Admin/Perm/Container/MDB2_Simple.php';

/**
 * This is a PEAR::MDB2 admin class for the LiveUser package.
 *
 * It takes care of managing the permission part of the package.
 *
 * A PEAR::MDB2 connection object can be passed to the constructor to reuse an
 * existing connection. Alternatively, a DSN can be passed to open a new one.
 *
 * Requirements:
 * - Files "common.php", "Container/MDB2_Medium.php" in directory "Perm"
 * - Array of connection options must be passed to the constructor.
 *   Example: array("server" => "localhost", "user" => "root",
 *   "password" => "pwd", "database" => "AllMyPreciousData")
 *
 * @author  Christian Dickmann <dickmann@php.net>
 * @author  Markus Wolff <wolff@21st.de>
 * @author  Matt Scifo <mscifo@php.net>
 * @author  Arnaud Limbourg <arnaud@php.net>
 * @version $Id: MDB2_Medium.php,v 1.1 2004/07/16 13:58:13 mloitzl Exp $
 * @package LiveUser
 */
class LiveUser_Admin_Perm_Container_MDB2_Medium extends LiveUser_Admin_Perm_Container_MDB2_Simple
{
    /**
     * Constructor
     *
     * @access protected
     * @param  array  full liveuser conf array
     * @return void
     */
    function LiveUser_Admin_Perm_Container_MDB2_Medium(&$connectOptions)
    {
        $this->LiveUser_Admin_Perm_Container_MDB2_Simple($connectOptions);
    }

    /**
     * Add a group to the database
     *
     * @access public
     * @param  string  name of group
     * @param  boolean description of group
     * @param  boolean activate group?
     * @return mixed   integer (group_id) or MDB2 Error object
     */
    function addGroup($group_name, $group_description = null, 
                    $active = false, $customFields = array())
    {
        // Get next group ID
        $group_id = $this->dbc->nextId($this->prefix . 'groups', true);

        if (MDB2::isError($group_id)) {
            return $group_id;
        };

        if (sizeof($customFields) > 0) {
            foreach ($customFields as $k => $v) {
                $col[] = $v['name'];
                $val[] = $this->dbc->quoteSmart($v['value']);
            }
        }

        if (is_array($col) && count($col) > 0) {
            $col = ',' . implode(',', $col);
            $val = ',' . implode(',', $val);
        }

        // Insert Group into Groupstable
        $query = 'INSERT INTO
                  ' . $this->prefix . 'groups
                  (group_id, is_active ' . $col . ')
                VALUES
                  (
                    ' . $this->dbc->quote($group_id, 'integer') . ',
                    ' . $this->dbc->quote($active, 'boolean') . '
                    ' . $val . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Insert Group translation into Translations table
        $result = $this->addTranslation(
            $group_id,
            LIVEUSER_SECTION_GROUP,
            $this->getCurrentLanguage(),
            $group_name,
            $group_description
        );

        if (MDB2::isError($result)) {
            return $result;
        };

        return $group_id;
    }

    /**
     * Deletes a group from the database
     *
     *
     * @access public
     * @param  integer id of deleted group
     * @return mixed   boolean or MDB2 Error object
     */
    function removeGroup($group_id)
    {
        // Delete user assignments
        $query = 'DELETE FROM
                  ' . $this->prefix . 'groupusers
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete group rights
        $query = 'DELETE FROM
                  ' . $this->prefix . 'grouprights
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete group itself
        $query = 'DELETE FROM
                  ' . $this->prefix . 'groups
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete group translations
        $result = $this->removeTranslation($group_id, LIVEUSER_SECTION_GROUP, $this->getCurrentLanguage(), true);

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Update group
     *
     * @access public
     * @param  integer id of group
     * @param  string  name of group
     * @param  boolean description of group
     * @param  boolean activate group?
     * @return mixed   boolean or MDB2 Error object
     */
    function updateGroup($group_id, $group_name, $group_description = null, 
                        $active = null, $customFields = array())
    {
        if ($active !== null) {
            $updateValues = array();
            // Create query.
            $query = 'UPDATE
                      ' . $this->prefix . 'groups
                    SET';
            
            $updateValues[] =
                      'is_active = ' . $this->dbc->quote($active, 'boolean');

            if (sizeof($customFields) > 0) {
                foreach ($customFields as $k => $v) {
                    $updateValues[] =
                        $v['name'] . ' = ' . $this->dbc->quoteSmart($v['value']);
                }
            }
            
            if (count($updateValues) >= 1) {
                $query .= implode(', ', $updateValues);
            } else {
                return false;
            }

            $query .= 'WHERE
                        group_id = ' . $this->dbc->quote($group_id, 'integer');

            $result = $this->dbc->query($query);

            if (MDB2::isError($result)) {
                return $result;
            };
        }

        // Update Group translation into Translations table
        $result = $this->updateTranslation(
            $group_id,
            LIVEUSER_SECTION_GROUP,
            $this->getCurrentLanguage(),
            $group_name,
            $group_description
        );

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Activate group
     *
     * @access public
     * @param integer id of group
     * @return mixed  boolean or MDB2 Error object or false
     */
    function activateGroup($group_id)
    {
        if (!is_numeric($group_id)) {
            return false;
        }

        $query = 'UPDATE
                  ' . $this->prefix . 'groups
                SET
                  is_active = '.$this->dbc->quote(true, 'boolean').'
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Deactivate group
     *
     * @access public
     * @param  integer id of group
     * @return mixed   boolean or MDB2 Error object
     */
    function deactivateGroup($group_id)
    {
        $query = 'UPDATE
                  ' . $this->prefix . 'groups
                SET
                  is_active = ' . $this->dbc->quote(false, 'boolean') . '
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Grant right to group
     *
     * @access public
     * @param  integer id of group
     * @param  integer id of right
     * @return mixed   boolean or MDB2 Error object
     */
    function grantGroupRight($group_id, $right_id)
    {
        //return if this group already has right
        $query = 'SELECT
                  count(*)
                FROM
                  ' . $this->prefix . 'grouprights
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer') . ' AND
                  right_id = ' . $this->dbc->quote($right_id, 'integer');

        $count = $this->dbc->queryOne($query);

        if (MDB2::isError($count) || $count != 0) {
            return false;
        };

        $query = 'INSERT INTO
                  ' . $this->prefix . 'grouprights
                  (group_id, right_id, right_level)
                VALUES
                  (
                    ' . $this->dbc->quote($group_id, 'integer') . ',
                    ' . $this->dbc->quote($right_id, 'integer') . ', '.LIVEUSER_MAX_LEVEL.'
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return true;
    }

    /**
     * Revoke right from group
     *
     * @access public
     * @param  integer id of group
     * @param  integer id of right
     * @return mixed   boolean or MDB2 Error object
     */
    function revokeGroupRight($group_id, $right_id = null)
    {
        $query = 'DELETE FROM
                  ' . $this->prefix . 'grouprights
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer');
        if (!is_null($right_id)) {
            $query .= ' AND
              right_id = ' . $this->dbc->quote($right_id, 'integer');
        }

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return true;
    }

    /**
     * Update right level of groupRight
     *
     * @access public
     * @param  integer id of group
     * @param  integer id of right
     * @param  integer right level
     * @return mixed   boolean or MDB2 Error object
     */
    function updateGroupRight($group_id, $right_id, $right_level)
    {
        $query = 'UPDATE
                  ' . $this->prefix . 'grouprights
                SET
                  right_level = ' . $this->dbc->quote($right_level, 'integer') . '
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer') . ' AND
                  right_id = ' . $this->dbc->quote($right_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return true;
    }

    /**
     * Add User to Group
     *
     * @access public
     * @param  string  id of user
     * @param  integer id of group
     * @return mixed   boolean or MDB2 Error object
     */
    function addUserToGroup($permId, $group_id)
    {
        $query = 'SELECT COUNT(*)
                  FROM ' . $this->prefix . 'groupusers
                WHERE
                    perm_user_id=' . $this->dbc->quote($permId, 'integer') . '
                AND
                    group_id=' . $this->dbc->quote($group_id, 'integer');

        $res = $this->dbc->queryOne($query);

        if ($res > 0) {
            return false;
        }

        $query = 'INSERT INTO
                  ' . $this->prefix . 'groupusers
                  (group_id, perm_user_id)
                VALUES
                  (
                    ' . $this->dbc->quote($group_id, 'integer') . ',
                    ' . $this->dbc->quote($permId, 'integer') . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return true;
    }

    /**
     * Remove User from Group
     *
     * @access public
     * @param  string  id of user
     * @param  integer id of group
     * @return mixed   boolean or MDB2 Error object
     */
    function removeUserFromGroup($permId, $group_id = null)
    {
        $query = 'DELETE FROM
                  ' . $this->prefix . 'groupusers
                WHERE
                  perm_user_id  = ' . $this->dbc->quote($permId, 'integer');

        if (!is_null($group_id)) {
            $query =' AND group_id = ' . $this->dbc->quote($group_id, 'integer');
        }
        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return true;
    }

    /**
     * Get list of all groups
     *
     * This method accepts the following options...
     *  'where_user_id' = [PERM_USER_ID],
     *  'where_group_id' = [GROUP_ID],
     *  'where_is_active' = [BOOLEAN],
     *  'with_rights' = [BOOLEAN]
     *
     * @access public
     * @param  array  an array determining which fields and conditions to use
     * @return mixed array or MDB2 Error object
     */
    function getGroups($options = null)
    {
        $query = 'SELECT
                  groups.group_id           AS group_id,
                  groups.owner_user_id      AS owner_user_id,
                  groups.owner_group_id     AS owner_group_id,
                  translations.name         AS name,
                  translations.description  AS description,
                  groups.is_active          AS is_active
                FROM';

        if (isset($options['where_user_id'])) {
            $query .= ' ' . $this->prefix . 'groupusers groupusers,';
        }

        $query .= ' ' . $this->prefix . 'groups groups,
                  ' . $this->prefix . 'translations translations
                WHERE';

        if (isset($options['where_user_id'])) {
            $query .= ' groupusers.perm_user_id = '
                      . $this->dbc->quote($options['where_user_id'], 'integer') . ' AND
                      groupusers.group_id = groups.group_id AND';
        }

        if (isset($options['where_group_id'])
                 && is_numeric($options['where_group_id'])) {
            $query .= ' groups.group_id = ' . $this->dbc->quote($options['where_group_id'], 'integer') . ' AND';
        }

        if (isset($options['where_owner_user_id'])
                && is_numeric($options['where_owner_user_id'])) {
            $query .= ' groups.owner_user_id = ' . $this->dbc->quote($options['where_owner_user_id'], 'integer') . ' AND';
        }

        if (isset($options['where_owner_group_id'])
                && is_numeric($options['where_owner_group_id'])) {
            $query .= ' groups.owner_group_id = ' . $this->dbc->quote($options['where_owner_group_id'], 'integer') . ' AND';
        }

        if (isset($options['where_is_active'])
                && is_string($options['where_is_active'])) {
            $query .= ' groups.is_active = ' . $options['where_is_active'] . ' AND';
        }

        $query .= ' translations.section_id = groups.group_id AND
                  translations.section_type = ' . LIVEUSER_SECTION_GROUP . ' AND
                  translations.language_id = ' . $this->dbc->quote($this->_langs[$this->getCurrentLanguage()], 'integer');

        $groups = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($groups)) {
            return $groups;
        };

        $_groups = array();
        if (is_array($groups)) {
            foreach($groups as $key => $value) {
                if (isset($options['with_rights'])) {
                    $_options = $options;
                    $_options['where_group_id'] = $value['group_id'];
                    $value['rights'] = $this->getRights($_options);
                };
                $_groups[$value['group_id']] = $value;
            };
        };

        return $_groups;
    }
}
?>
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
 * MDB2_Complex permission administration class
 *
 * @package  LiveUser
 * @category authentication
 */

/**
 * Require the parent class definition
 */
require_once 'LiveUser/Admin/Perm/Container/MDB2_Medium.php';

/**
 * This is a PEAR::MDB2 admin class for the LiveUser package.
 *
 * It takes care of managing the permission part of the package.
 *
 * A PEAR::MDB2 connection object can be passed to the constructor to reuse an
 * existing connection. Alternatively, a DSN can be passed to open a new one.
 *
 * Requirements:
 * - Files "common.php", "Container/MDB2_Complex.php" in directory "Perm"
 * - Array of connection options must be passed to the constructor.
 *   Example: array("server" => "localhost", "user" => "root",
 *   "password" => "pwd", "database" => "AllMyPreciousData")
 *
 * @author  Christian Dickmann <dickmann@php.net>
 * @author  Markus Wolff <wolff@21st.de>
 * @author  Matt Scifo <mscifo@php.net>
 * @version $Id: MDB2_Complex.php,v 1.1 2004/07/16 13:58:13 mloitzl Exp $
 * @package LiveUser
 */
class LiveUser_Admin_Perm_Container_MDB2_Complex extends LiveUser_Admin_Perm_Container_MDB2_Medium
{
    /**
     * Constructor
     *
     * @access protected
     * @param  mixed  Array or PEAR::MDB2 object.
     * @param  array  configuration array not used atm
     * @return void
     */
    function LiveUser_Admin_Perm_Container_MDB2_Complex(&$connectOptions)
    {
        $this->LiveUser_Admin_Perm_Container_MDB2_Medium($connectOptions);
    }

    /**
     * Add a group to the database
     *
     * @access public
     * @param  string  name of group
     * @param  boolean description of group
     * @param  boolean activate group?
     * @param  integer owner_user_id of group
     * @param  integer owner_group_id of group
     * @return mixed integer (group_id) or MDB2 Error object
     */
    function addGroup($group_name, $group_description = null,
        $active = false, $owner_user = null, $owner_group = null, $customFields = array())
    {
        $customFields[]  = array('name' => 'owner_user_id',  'value' => $owner_user,  'type' => 'integer');
        $customFields[]  = array('name' => 'owner_group_id', 'value' => $owner_group, 'type' => 'integer');
        
        $group_id = parent::addGroup($group_name, $group_description,
                                    $active, $customFields);
        return $group_id;
    }

    /**
     * Assign subgroup to parent group.
     *
     * First checks that the child group does not have a parent group
     * already assigned to it. If so it returns an error object
     *
     * @access public
     * @param  integer id of parent group
     * @param  integer id of child group
     * @return mixed boolean, MDB2 Error object or LiveUser Error Object
     */
    function assignSubgroup($group_id, $subgroup_id)
    {
        $query = 'SELECT subgroup_id FROM
                  ' . $this->prefix . 'group_subgroups
                  WHERE subgroup_id=' . $this->dbc->quote($subgroup_id, 'integer');

        if (!is_null($this->dbc->queryOne($query))) {
            return LiveUser::raiseError(LIVEUSER_ERROR, null, null,
                'Child group already has a parent group');
        }

        $query = 'INSERT INTO
                  ' . $this->prefix . 'group_subgroups
                  (group_id, subgroup_id)
                VALUES
                  (
                    ' . $this->dbc->quote($group_id, 'integer') . ',
                    ' . $this->dbc->quote($subgroup_id, 'integer') . '
                  )';

        $result = $this->dbc->query($query);

        return $result;
    }

     /**
     * Unassign subgroup from parent group.
     *
     * Remove parent group from child group.
     *
     * @access public
     * @param  integer id of child group
     * @return mixed boolean, MDB2 Error object or LiveUser Error Object
     */
    function unassignSubgroup($subgroup_id)
    {
        $query = 'DELETE FROM
                  ' . $this->prefix . 'group_subgroups
                  WHERE subgroup_id=' . $this->dbc->quote($subgroup_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Deletes a group from the database
     *
     *
     * @access public
     * @param  integer id of deleted group
     * @param  boolean recursive delete of all subgroups
     * @return mixed   boolean or MDB2 Error object
     */
    function removeGroup($group_id, $recursive = false)
    {
        // Recursive delete groups
        if ($recursive) {
            // Get all subgroups
            $query = 'SELECT
                      subgroup_id
                    FROM
                      ' . $this->prefix . 'group_subgroups
                    WHERE
                      group_id = ' . $this->dbc->quote($group_id, 'integer');

            $result = $this->dbc->queryCol($query);

            if (MDB2::isError($result)) {
                return $result;
            };

            // Recursive removeGroup() call for every subgroup
            foreach ($result as $subgroup_id) {
                $res = $this->removeGroup($subgroup_id, true);
                if (MDB2::isError($res)) {
                    return $res;
                };
            };
        };

        parent::removegroup($group_id);

        // Delete Subgroup assignments
        $query = 'DELETE FROM
                  ' . $this->prefix . 'group_subgroups
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer') . ' OR
                  subgroup_id = ' . $this->dbc->quote($group_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // sets owner_group_id to null
        $query = 'UPDATE
                  ' . $this->prefix . 'groups
                SET
                  owner_group_id = NULL
                WHERE
                  owner_group_id = ' . $group_id;

        $result = $this->dbc->query($query);

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
     * @param  integer owner_user_id of group
     * @param  integer owner_group_id of group
     * @return mixed   boolean or MDB2 Error object
     */
    function updateGroup($group_id, $group_name, $group_description = null,
        $active = null, $owner_user = null, $owner_group = null, $customFields)
    {
        $customFields[]  = array('name' => 'owner_user_id',  'value' => $owner_user,  'type' => 'integer');
        $customFields[]  = array('name' => 'owner_group_id', 'value' => $owner_group, 'type' => 'integer');
        
        $result = parent::updateGroup($group_id, $group_name, $group_description,
                            $active, $customFields);

        if (MDB2::isError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * get parent group of group
     *
     * @access public
     * @param  integer id of subgroup
     * @return mixed integer or MDB2 Error object
     */
    function getParentGroup($subgroup_id)
    {
        $query = 'SELECT
                            group_id
                        FROM
                            ' . $this->prefix . 'group_subgroups
                       WHERE
                            subgroup_id = ' . (int)$subgroup_id;

        $group_id = $this->dbc->queryOne($query);

        return $group_id;
    }

    /**
     * Update implied status of right
     *
     * @access private
     * @param  integer id of right
     * @return mixed   boolean or MDB2 Error object
     */
    function _updateImpliedStatus($right_id)
    {
        // Are there any implied rights?
        $query = 'SELECT
                  Count(*)
                FROM
                  ' . $this->prefix . 'right_implied
                WHERE
                  right_id = ' . $this->dbc->quote($right_id, 'integer');

        $count = $this->dbc->queryOne($query);

        if (MDB2::isError($count)) {
            return $count;
        };

        $query = 'UPDATE
                  ' . $this->prefix . 'rights
                SET
                  has_implied = ' . $this->dbc->quote(($count > 0), 'boolean') . '
                WHERE
                  right_id = ' . $this->dbc->quote($right_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return true;
    }

    /**
     * Imply right
     *
     * @access public
     * @param  integer id of right
     * @param  string  id of implied right
     * @return mixed   boolean or MDB2 Error object
     */
    function implyRight($right_id, $implied_right_id)
    {
        // Imply Right
        $query = 'INSERT INTO
                  ' . $this->prefix . 'right_implied
                  (right_id, implied_right_id)
                VALUES
                  (
                    ' . $this->dbc->quote($right_id, 'integer') . ',
                    ' . $this->dbc->quote($implied_right_id, 'integer') . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Update implied status
        return $this->_updateImpliedStatus($right_id);
    }

    /**
     * Unimply right
     *
     * @access public
     * @param  integer id of right
     * @param  string  id of implied right
     * @return mixed   boolean or MDB2 Error object
     */
    function unimplyRight($right_id, $implied_right_id)
    {
        // Unimply right
        $query = 'DELETE FROM
                  ' . $this->prefix . 'right_implied
                WHERE
                  right_id         = ' . $this->dbc->quote($right_id, 'integer') . ' AND
                  implied_right_id = ' . $this->dbc->quote($implied_right_id, 'integer');
        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Update implied status
        return $this->_updateImpliedStatus($right_id);
    }

    /**
     * Overriden method to delete implied rights mapping
     * as well as the right.
     *
     * @access public
     * @param  int   right identifier
     * @return mixed true on success or MDB2 error
     */
    function removeRight($right_id)
    {
        parent::removeRight($right_id);

        // delete from implied_rights unimply rights
        $query = 'DELETE FROM
                  ' . $this->prefix . 'right_implied
                WHERE
                  right_id         = ' . $this->dbc->quote($right_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Update implied status
        return $this->_updateImpliedStatus($right_id);
    }

    /**
     * Get list of all groups
     *
     * This method accepts the following options...
     * <code>
     *  'where_user_id' = [PERM_USER_ID],
     *  'where_group_id' = [GROUP_ID],
     *  'where_owner_user_id' = [OWNER_USER_ID],
     *  'where_owner_group_id' = [OWNER_GROUP_ID],
     *  'where_is_active' = [BOOLEAN],
     *  'with_rights' = [BOOLEAN]
     * </code>
     *
     * @access public
     * @param  array  an array determining which fields and conditions to use
     * @param  boolean  determine whether or not to build a hierarchal result set
     * @return mixed array or MDB2 Error object
     */
    function getGroups($options = null, $hierarchy = false)
    {
        static $groups;
        static $subgroups;
        $result = array();

        $query = 'SELECT
                  groups.group_id           AS group_id,
                  groups.owner_user_id      AS owner_user_id,
                  groups.owner_group_id     AS owner_group_id,
                  translations.name         AS name,
                  translations.description     AS description,
                  groups.is_active          AS is_active
                FROM';

        if (isset($options['where_user_id'])
                && is_numeric($options['where_user_id'])) {
            $query .= ' ' . $this->prefix . 'groupusers groupusers,';
        }

        $query .= ' ' . $this->prefix . 'groups groups,
                  ' . $this->prefix . 'translations translations
                WHERE';

        if (isset($options['where_user_id'])
                && is_numeric($options['where_user_id'])) {
            $query .= ' groupusers.perm_user_id = ' . $this->dbc->quote($options['where_user_id'], 'integer') . ' AND
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
        }

        $_groups = array();
        if (is_array($groups)) {
            foreach($groups as $key => $value) {
                if (isset($options['with_rights'])) {
                    $_options = $options;
                    $_options['where_group_id'] = $value['group_id'];
                    $value['rights'] = $this->getRights($_options);
                }
                $_groups[$value['group_id']] = $value;
            }
        }

        $query = 'SELECT
                      subgroups.group_id as group_id,
                      subgroups.subgroup_id as subgroup_id
                 FROM
                 ' . $this->prefix . 'group_subgroups subgroups';

        $subgroups = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($subgroups)) {
            return $subgroups;
        }

        foreach($subgroups as $subgroup) {
            if ($_groups[$subgroup['group_id']]) {
                $result = $this->getGroups(array('where_group_id' => $subgroup['subgroup_id']), $hierarchy);
                $_groups[$subgroup['group_id']]['subgroups'][$subgroup['subgroup_id']] = $result[$subgroup['subgroup_id']];

                if ($hierarchy) {
                    unset($_groups[$subgroup['subgroup_id']]);
                }
            }
        }

        return $_groups;
    }

    /**
     * Get list of all rights
     *
     * This method accepts the following options...
     *  'where_user_id' = [PERM_USER_ID],
     *  'where_group_id' = [GROUP_ID],
     *  'where_right_id' = [RIGHT_ID],
     *  'where_area_id' = [AREA_ID],
     *  'where_application_id' = [APPLICATION_ID],
     *  'with_areas' = [BOOLEAN],
     *  'with_applications' = [BOOLEAN]
     *  'with_inherited_rights' = [BOOLEAN]
     *  'with_implied_rights' = [BOOLEAN]
     *
     * @access public
     * @param  array    an array determining which fields and conditions to use
     * @param  boolean whether or not to build a hierarchal result set
     * @return mixed     array or MDB2 Error object
     */
    function getRights($options = null, $hierarchy = false)
    {
        $query = 'SELECT
                  rights.right_id      AS right_id,
                  rights.area_id       AS area_id,
                  areas.application_id AS application_id,';

        if (isset($options['where_user_id'])) {
            $query .= ' userrights.perm_user_id AS user_id,';
            $query .= ' userrights.right_level AS right_level,';
        } else if (isset($options['where_group_id'])
                && is_numeric($options['where_group_id'])) {
            $query .= ' grouprights.group_id AS group_id,';
            $query .= ' grouprights.right_level AS right_level,';
        }

        $query .= '
                  rights.right_define_name AS define_name,
                  rights.has_implied       AS has_implied,
                  rights.has_level         AS has_level,
                  rights.has_scope         AS has_scope,
                  translations.name        AS name,
                  translations.description AS description
                FROM
                  ' . $this->prefix . 'rights rights,
                  ' . $this->prefix . 'areas areas,
                  ' . $this->prefix . 'applications applications,';

        if (isset($options['where_user_id'])) {
            $query .= ' ' . $this->prefix . 'userrights userrights,';
        } else if (isset($options['where_group_id'])
                && is_numeric($options['where_group_id'])) {
            $query .= ' ' . $this->prefix . 'grouprights grouprights,';
        }

        $query .= ' ' . $this->prefix . 'translations translations
                WHERE';

        if (isset($options['where_right_id'])
                && is_numeric($options['where_right_id'])) {
            $query .= ' rights.right_id = '
                . $this->dbc->quote($options['where_right_id'], 'integer') . ' AND';
        };

        if (isset($options['where_area_id'])
                && is_numeric($options['where_area_id'])) {
            $query .= ' rights.area_id = '
                . $this->dbc->quote($options['where_area_id'], 'integer') . ' AND';
        };

        if (isset($options['where_application_id'])
                && is_numeric($options['where_application_id'])) {
            $query .= ' areas.application_id = '
                . $this->dbc->quote($options['where_application_id'], 'integer') . ' AND';
        };

        if (isset($options['where_user_id'])) {
            $query .= ' userrights.perm_user_id = '
                . $this->dbc->quote($options['where_user_id'], 'integer') . ' AND
                      userrights.right_id = rights.right_id AND';
        } else if (isset($options['where_group_id'])
                && is_numeric($options['where_group_id'])) {
            $query .= ' grouprights.group_id = '
                . $this->dbc->quote($options['where_group_id'], 'integer') . ' AND
                      grouprights.right_id = rights.right_id AND';
        };

        $query .= ' rights.area_id = areas.area_id AND
                  rights.right_id = translations.section_id AND
                  translations.section_type = ' . LIVEUSER_SECTION_RIGHT . ' AND
                  translations.language_id = '
                    . $this->dbc->quote(($this->_langs[$this->getCurrentLanguage()]), 'integer') . '
                GROUP BY
                  rights.right_id, rights.area_id, areas.application_id';

        if (isset($options['where_user_id'])) {
            $query .= ',userrights.perm_user_id';
            $query .= ',userrights.right_level';
        } else if (isset($options['where_group_id'])
                && is_numeric($options['where_group_id'])) {
            $query .= ',grouprights.group_id';
            $query .= ',grouprights.right_level';
        }

        $query .= '
                  ,rights.right_define_name, rights.has_implied,
                  rights.has_level, rights.has_scope,
                  translations.name, translations.description
                ORDER BY
                  rights.area_id ASC';

        $rights = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($rights)) {
            return $rights;
        };

        $_rights = array();
        if (is_array($rights)) {
            foreach ($rights as $key => $value) {
                $id = $value['right_id'];
                $_rights[$id] = $value;

                if (isset($options['with_areas'])) {
                    // Add area
                    $filter = array('where_area_id' => $value['area_id']);
                    $_rights[$id]['area'] =
                        array_shift($this->getAreas($filter));

                    if (MDB2::isError($_rights[$id]['area'])) {
                        return $_rights[$id]['area'];
                    };

                    if (isset($options['with_applications'])) {
                        // Add application
                        $filter = array('where_application_id' => $value['application_id']);
                        $_rights[$id]['application'] =
                            array_shift($this->getApplications($filter));

                        if (MDB2::isError($_rights[$id]['application'])) {
                            return $_rights[$id]['application'];
                        };
                    };
                };

                if (isset($options['with_implied_rights'])) {
                    $implied_rights = $this->getImpliedRights(
                        array(
                            'where_right_id' => $id,
                            'with_areas' => true,
                            'with_applications' => true
                        )
                    );

                    if (MDB2::isError($implied_rights)) {
                        return $implied_rights;
                    }

                    foreach($implied_rights as $right) {
                        if ($_rights[$right['right_id']]) {
                            continue;
                        }

                        $right['type'] = 'implied';

                        if ($hierarchy) {
                            $_rights[$id]['implied_rights'][$right['right_id']] = $right;
                            unset($_rights[$right['right_id']]);
                        } else {
                            $_rights[$right['right_id']] = $right;
                        }
                    }
                }

                if (!$_rights[$id]['type']) {
                    $_rights[$id]['type'] = 'granted';
                }
            }
        }

        if ($options['with_inherited_rights'] &&
            (isset($options['where_user_id']) ||
            isset($options['where_group_id']))
        ) {
            $inherited_rights = $this->getInheritedRights($options);

            if (DB::isError($inherited_rights)) {
                return $inherited_rights;
            }

            foreach ($inherited_rights as $right) {
                if ($_rights[$right['right_id']]) {
                    continue;
                }

                $right['type'] = 'inherited';
                $_rights[$right['right_id']] = $right;
            }
        }

        return $_rights;
    }

    /**
     * Get implied rights
     *
     * @access public
     * @param  array    an array determining which fields and conditions to use
     * @return mixed    array or MDB2 Error object
     */
    function getImpliedRights($options = array())
    {
        $query = 'SELECT
                      implied.right_id as right_id,
                      implied.implied_right_id as implied_right_id
                     FROM
                     ' . $this->prefix . 'right_implied implied';
        if (isset($options['where_right_id'])
                && is_numeric($options['where_right_id'])) {
            $query .= ' WHERE implied.right_id = ' . $options['where_right_id'];
        }

        $implied_rights = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($implied_rights)) {
            return $implied_rights;
        }

        $_rights = array();
        foreach($implied_rights as $implied) {
            $options['where_right_id'] = $implied['implied_right_id'];
            $implied_rights = $this->getRights($options);

            if (MDB2::isError($implied_rights)) {
                return $implied_rights;
            }

            $_rights = array_merge($_rights, $implied_rights);
        }

        return $_rights;
    }

    /**
     * Get inherited rights
     *
     * @access public
     * @param  array    an array determining which fields and conditions to use
     * @return mixed    array or MDB2 Error object
     */
    function getInheritedRights($options = array())
    {
        if (isset($options['where_user_id'])) {
            $query = 'SELECT
                      groupusers.group_id as group_id
                     FROM
                     ' . $this->prefix . 'groupusers groupusers
                     WHERE groupusers.perm_user_id = ' . $this->dbc->quote($options['where_user_id'], 'integer');
        } else {
            $query = 'SELECT
                      subgroups.group_id as group_id
                     FROM
                     ' . $this->prefix . 'group_subgroups subgroups
                     WHERE subgroups.subgroup_id = ' . $options['where_group_id'];
        }

        $groups = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($groups)) {
            return $groups;
        }

        $_rights = array();
        foreach($groups as $group) {
            $options['where_user_id'] = null;
            $options['where_group_id'] = $group['group_id'];
            $inherited_rights = $this->getRights($options);

            if (MDB2::isError($inherited_rights)) {
                return $inherited_rights;
            }

            $_rights = array_merge($_rights, $inherited_rights);
        }

        return $_rights;
    }

     /*
     * Delete a user.
     *
     * @access public
     * @param  string  perm_user_id
     * @return mixed   boolean or MDB2 Error object
     */
    function removeUser($permId)
    {
        parent::removeUser($permId);

        // sets owner_user_id to null
        $query = 'UPDATE
                  ' . $this->prefix . 'groups
                SET
                  owner_user_id = NULL
                WHERE
                  owner_user_id=' . $permId;

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Update level status of right
     *
     * @access private
     * @param  integer id of right
     * @return mixed   boolean or MDB2 Error object
     */
    function _updateLevelStatus($right_id)
    {
        // Are there any userrights with levels?
        $query = 'SELECT
                  count(*)
                FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  right_id = ' . $this->dbc->quote($right_id, 'integer') . ' AND
                  right_level < '.LIVEUSER_MAX_LEVEL;

        $usercount = $this->dbc->queryOne($query);

        if (MDB2::isError($usercount)) {
            return $usercount;
        };

        // Are there any grouprights with levels?
        $query = 'SELECT
                  count(*)
                FROM
                  ' . $this->prefix . 'grouprights
                WHERE
                  right_id = ' . $this->dbc->quote($right_id, 'integer') . ' AND
                  right_level < '.LIVEUSER_MAX_LEVEL;

        $groupcount = $this->dbc->queryOne($query);

        if (MDB2::isError($groupcount)) {
            return $groupcount;
        };

        $count = $usercount + $groupcount;

        $query = 'UPDATE
                  ' . $this->prefix . 'rights
                SET
                  has_level = ' . $this->dbc->quote(($count > 0), 'boolean') . '
                WHERE
                  right_id = ' . $this->dbc->quote($right_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return true;
    }

    /**
     * Grant right to user
     *
     * @access public
     * @param  string  id of user
     * @param  integer id of right
     * @param  integer level of right (can be negative to revoke/lower a right level)
     * @return mixed   boolean or MDB2 Error object
     */
    function grantUserRight($permId, $right_id, $right_level = LIVEUSER_MAX_LEVEL)
    {
        //return if this user already has right
        $query = 'SELECT
                  count(*)
                FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  perm_user_id  = ' . $permId . ' AND
                  right_id      = ' . $this->dbc->quote($right_id, 'integer');

        $count = $this->dbc->queryOne($query);

        if (MDB2::isError($count) || $count != 0) {
            return false;
        };

        $query = 'INSERT INTO
                  ' . $this->prefix . 'userrights
                  (perm_user_id, right_id, right_level)
                VALUES
                  (
                    ' . $permId . ',
                    ' . $this->dbc->quote($right_id, 'integer') . ',
                    ' . $this->dbc->quote($right_level, 'integer') . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        $this->_updateLevelStatus($right_id);

        // Job done ...
        return true;
    }

    /**
     * Grand right to group
     *
     * @access public
     * @param  integer id of group
     * @param  integer id of right
     * @param  integer right level
     * @return mixed   boolean or MDB2 Error object
     */
    function grantGroupRight($group_id, $right_id, $right_level = LIVEUSER_MAX_LEVEL)
    {
        //return if this group already has right
        $query = 'SELECT
                  count(*)
                FROM
                  ' . $this->prefix . 'grouprights
                WHERE
                  group_id = ' . $this->dbc->quote($group_id, 'integer') . ' AND
                  right_id = ' . $this->dbc->quote($right_id, 'integer');

        $count = $this->dbc->queryOne($query);quote($right_level, 'integer');

        if (MDB2::isError($count) || $count != 0) {
            return false;
        };

        $query = 'INSERT INTO
                  ' . $this->prefix . 'grouprights
                  (group_id, right_id, right_level)
                VALUES
                  (
                    ' . $this->dbc->quote($group_id, 'integer') . ',
                    ' . $this->dbc->quote($right_id, 'integer') . ',
                    ' . $this->dbc->quote($right_level, 'integer') . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        $this->_updateLevelStatus($right_id);

        // Job done ...
        return true;
    }
}
?>
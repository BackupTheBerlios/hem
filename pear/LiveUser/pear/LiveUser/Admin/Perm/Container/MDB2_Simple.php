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
 * Include parent class
 */
require_once 'LiveUser/Admin/Perm/Common.php';
require_once 'MDB2.php';

/**
 * Container for simple rights managements.
 *
 * With it you can only assign rights to users. For advanced uses like
 * groups assignements and more see DB_Medium and DB_Complex.
 *
 * @category authentication
 * @version $Id: MDB2_Simple.php,v 1.1 2004/07/16 13:59:02 mloitzl Exp $
 * @package LiveUser
 */
class LiveUser_Admin_Perm_Container_MDB2_Simple extends LiveUser_Admin_Perm_Common
{
    /**
     * Constructor
     *
     * @access protected
     * @param  array  full liveuser configuration array
     * @return void
     * @see    LiveUser::factory()
     */
    function LiveUser_Admin_Perm_Container_MDB2_Simple(&$connectOptions)
    {
        $this->LiveUser_Admin_Perm_Common($connectOptions);
        if (is_array($connectOptions)) {
            $function = 'connect';
            if (isset($connectOptions['function'])) {
                $function = $connectOptions['function'];
                unset($connectOptions['function']);
            }
            foreach ($connectOptions as $key => $value) {
                if (isset($this->$key)) {
                    $this->$key = $value;
                }
            }
            if (isset($connectOptions['connection']) &&
                    MDB2::isConnection($connectOptions['connection'])
            ) {
                $this->dbc     = &$connectOptions['connection'];
                $this->init_ok = true;
            } elseif (isset($connectOptions['dsn'])) {
                $this->dsn = $connectOptions['dsn'];
                $function = null;
                if (isset($connectOptions['function'])) {
                    $function = $connectOptions['function'];
                }
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
     * Gets the perm ID of a user.
     *
     * @access  public
     * @param   string  Auth user ID.
     * @param   string  Auth container name.
     * @return  mixed   Permission ID or MDB2 error.
     */
    function getPermUserId($authId, $authName)
    {
        $query = '
            SELECT
                perm_user_id
            FROM
                ' . $this->prefix . 'perm_users
            WHERE
                auth_user_id = '.$this->dbc->quote($authId, 'text').'
            AND
                auth_container_name = '.$this->dbc->quote($authName, 'text');
        $permId = $this->dbc->queryOne($query);

        return $permId;
    } // end func _getPermUserId

    function getAuthUserId($permId)
    {
        $query = '
            SELECT
                auth_user_id, auth_container_name
            FROM
                ' . $this->prefix . 'perm_users
            WHERE
                perm_user_id = '.$this->dbc->quote($permId, 'text');
                $authId = $this->dbc->queryRow($query, array('text', 'text'), MDB2_FETCHMODE_ASSOC);
            return $authId;
    } // end func _getAuthUserId

    /**
     * Reads all languages from databases and stores them in private variable
     *
     * $this->_langs is filled with this array structure:
     *     two_letter_code => language_id
     *
     * @access private
     * @return mixed boolean or MDB2 Error object
     */
    function _getLanguages()
    {
        if (sizeof($this->_langs) < 1) {
            $query = 'SELECT two_letter_name, language_id FROM ' . $this->prefix . 'languages';
            $langs = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC, true);
            if (MDB2::isError($langs)) {
                return $langs;
            };
            $this->_langs = $langs;
        };
        return true;
    }

    /**
     * Set current language
     *
     * Returns false if the language is not known
     *
     * @access public
     * @param  string language short name
     * @return mixed   boolean or MDB2 Error object or false
     */
    function setCurrentLanguage($language)
    {
        // Get all language ids
        if (MDB2::isError($result = $this->_getLanguages())) {
            return $result;
        };

        // Check if language is a known one
        if (!isset($this->_langs[$language])) {
            return false;
        };

        $this->_language = $language;

        return true;
    }

    /**
     * Get current language
     *
     * @access public
     * @return string name of the current language
     */
    function getCurrentLanguage()
    {
        return $this->_language;
    }

    /**
     * Set current application
     *
     * @access public
     * @param  integer  id of application
     * @return boolean always true
     */
    function setCurrentApplication($application_id)
    {
        $this->_application = $application_id;

        return true;
    }

    /**
     * Get current application
     *
     * @access public
     * @return string name of the current application
     */
    function getCurrentApplication()
    {
        return $this->_application;
    }

    /**
     * Assigns name (and description) in specified language to a section
     *
     * @access public
     * @param integer id of [section]
     * @param integer type of section
     * @param string  language (two letter code) of name/description
     * @param string  name of [section]
     * @param string  description of [section]
     * @return mixed boolean or MDB2 Error object
     */
    function addTranslation($section_id, $section_type, $language, $name, $description = null)
    {
        // Get all language ids
        if (MDB2::isError($result = $this->_getLanguages())) {
            return $result;
        };

        // Check if language is a known one
        if (!isset($this->_langs[$language])) {
            return false;
        };

        // Register translation
        $query = 'INSERT INTO
                  ' . $this->prefix . 'translations
                  (section_id, section_type, language_id, name, description)
                VALUES
                  (
                    ' . $this->dbc->quote($section_id, 'integer') . ',
                    ' . $this->dbc->quote($section_type, 'integer') . ',
                    ' . $this->dbc->quote($this->_langs[$language], 'integer') . ',
                    ' . $this->dbc->quote($name, 'text') . ',
                    ' . $this->dbc->quote($description, 'text') . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // name (and description) added ...
        return true;
    }

    /**
     * Updates name (and description) of [section] in specified language
     *
     * @access public
     * @param  integer id of [section]
     * @param  integer type of section
     * @param  string  language (two letter code) of name/description
     * @param  string  name of [section]
     * @param  string  description of [section]
     * @return mixed boolean or MDB2 Error object
     */
    function updateTranslation($section_id, $section_type, $language, $name, $description = null)
    {
        // Get all language ids
        if (MDB2::isError($result = $this->_getLanguages())) {
            return $result;
        };

        // Check if language is a known one
        if (!isset($this->_langs[$language])) {
            return false;
        };

        // Update translation
        $query = 'UPDATE
                  ' . $this->prefix . 'translations
                SET
                  name        = ' . $this->dbc->quote($name, 'text') . ',
                  description = ' . $this->dbc->quote($description, 'text') . '
                WHERE
                  section_id    = ' . $this->dbc->quote($section_id, 'integer') . ' AND
                  section_type  = ' . $this->dbc->quote($section_type, 'integer') . ' AND
                  language_id   = ' . $this->dbc->quote($this->_langs[$language], 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Translation name (and description) updated ...
        return true;
    }

    /**
     * Remove name (and description) of the [section] in specified language
     *
     * @access public
     * @param  integer id of [section]
     * @param  integer type of section
     * @param  string  language (two letter code) of name/description
     * @param  boolean recursive delete of all translations
     * @return mixed boolean or MDB2 Error object
     */
    function removeTranslation($section_id, $section_type, $language, $recursive = false)
    {
        // Get all language ids
        if (MDB2::isError($result = $this->_getLanguages())) {
            return $result;
        };

        // Check if language is a known one
        if (!isset($this->_langs[$language])) {
            return false;
        };

        // Remove translation
        $query = 'DELETE FROM
                  ' . $this->prefix . 'translations
                WHERE
                  section_id    = ' . $this->dbc->quote($section_id, 'integer') . ' AND
                  section_type  = ' . $this->dbc->quote($section_type, 'integer');
        if (!$recursive) {
            $query .= ' AND language_id = ' . $this->dbc->quote($this->_langs[$language], 'integer');
        };

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Translation name (and description) removed ...
        return true;
    }

    /**
     * Get name (and description) of the [section] in specified language
     *
     * @access public
     * @param  integer id of [section]
     * @param  integer type of section
     * @return mixed array or MDB2 Error object
     */
    function getTranslation($section_id, $section_type)
    {
        // get translation
        $query = 'SELECT
                  translations.name        AS name,
                  translations.description AS description
                FROM
                  ' . $this->prefix . 'translations translations
                WHERE
                  section_id    = ' . $this->dbc->quote($section_id, 'integer') . ' AND
                  section_type  = ' . $this->dbc->quote($section_type, 'integer');
        $translation = $this->dbc->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
        if (MDB2::isError($translation)) {
            return $translation;
        };

        if (!is_array($translation)) {
            return array();
        }
        // Translation name (and description) removed ...
        return $translation;
    }

    /**
     * Add a new language
     *
     * @access public
     * @param  string two letter code of language
     * @param  string name of language
     * @param  string description of language
     * @return mixed integer (language_id) or MDB2 Error object
     */
    function addLanguage($two_letter_code, $language_name, $language_description = null)
    {
        // Get next language id
        $language_id = $this->dbc->nextId($this->prefix . 'languages', true);

        if (MDB2::isError($language_id)) {
            return $language_id;
        };

        // Add language
        $query = 'INSERT INTO
                  ' . $this->prefix . 'languages
                  (language_id, two_letter_name)
                VALUES
                  (
                    ' . $language_id . ',
                    ' . $this->dbc->quote($two_letter_code, 'text') . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // force language reload in case it is the first language we create
        $this->_getLanguages();

        if (sizeof($this->_langs) == 1) {
            $lang = $two_letter_code;
        } else {
            $lang = $this->getCurrentLanguage();
        }

        // Insert Language translation into Translations table
        $result = $this->addTranslation(
            $language_id,
            LIVEUSER_SECTION_LANGUAGE,
            $lang,
            $language_name,
            $language_description
        );

        if (MDB2::isError($result)) {
            return $result;
        };

        // Clear language cache
        unset($this->_langs);

        // Job done ...
        return $language_id;
    }

    /**
     * Remove a language
     *
     * @access public
     * @param  integer language (two letter code)
     * @return mixed   boolean or MDB2 Error object
     */
    function removeLanguage($language)
    {
        // Get all language ids
        if (MDB2::isError($result = $this->_getLanguages())) {
            return $result;
        };

        // Check if language is a known one
        if (!isset($this->_langs[$language])) {
            return false;
        };

        // Delete language
        $query = 'DELETE FROM
                  ' . $this->prefix . 'languages
                WHERE
                  language_id = ' . $this->dbc->quote($this->_langs[$language], 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete language translations
        $result = $this->removeTranslation($this->_langs[$language], LIVEUSER_SECTION_LANGUAGE, $this->getCurrentLanguage(), true);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Clear language cache
        unset($this->_langs);

        // Job done ...
        return true;
    }

    /**
     * Update language
     *
     * @access public
     * @param  integer language (two letter code)
     * @param  string  name of language
     * @param  string  description of language
     * @return mixed   boolean or MDB2 Error object
     */
    function updateLanguage($language, $language_name, $language_description = null)
    {
        // Get all language ids
        if (MDB2::isError($result = $this->_getLanguages())) {
            return $result;
        };

        // Check if language is a known one
        if (!isset($this->_langs[$language])) {
            return false;
        };

        // Update Language translation into Translations table
        $result = $this->updateTranslation(
            $this->_langs[$language],
            LIVEUSER_SECTION_LANGUAGE,
            $this->getCurrentLanguage(),
            $language_name,
            $langauge_description
        );

        if (MDB2::isError($result)) {
            return $result;
        };

        // Clear language cache
        unset($this->_langs);

        // Job done ...
        return true;
    }

    /**
     * Add an application
     *
     * @access public
     * @param  string name of application constant
     * @param  string name of application
     * @param  string description of application
     * @return mixed  integer (application_id) or MDB2 Error object
     */
    function addApplication($define_name, $application_name, $application_description = null)
    {
        // Get next application id
        $application_id = $this->dbc->nextId($this->prefix . 'applications', true);
        if (MDB2::isError($application_id)) {
            return $application_id;
        };

        // Register new application
        $query = 'INSERT INTO
                  ' . $this->prefix . 'applications
                  (application_id, application_define_name)
                VALUES
                  (
                    ' . $this->dbc->quote($application_id, 'integer') . ',
                    ' . $this->dbc->quote($define_name, 'text') . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Insert Application translation into Translations table
        $result = $this->addTranslation(
            $application_id,
            LIVEUSER_SECTION_APPLICATION,
            $this->getCurrentLanguage(),
            $application_name,
            $application_description
        );

        if (MDB2::isError($result)) {
            return $result;
        };

        return $application_id;
    }

    /**
     * Add an area
     *
     * @access public
     * @param  string id of application
     * @param  string name of area constant
     * @param  string name of area
     * @param  string description of area
     * @return mixed  integer (area_id) or MDB2 Error object
     */
    function addArea($application_id, $define_name, $area_name, $area_description = null)
    {
        // Get next area id
        $area_id = $this->dbc->nextId($this->prefix . 'areas', true);

        if (MDB2::isError($area_id)) {
            return $area_id;
        };

        // Register new area
        $query = 'INSERT INTO
                  ' . $this->prefix . 'areas
                  (area_id, area_define_name, application_id)
                VALUES
                  (
                    ' . $this->dbc->quote($area_id, 'integer') . ',
                    ' . $this->dbc->quote($define_name, 'text') . ',
                    ' . $this->dbc->quote($application_id, 'integer') . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Insert Area translation into Translations table
        $result = $this->addTranslation(
            $area_id,
            LIVEUSER_SECTION_AREA,
            $this->getCurrentLanguage(),
            $area_name,
            $area_description
        );

        if (MDB2::isError($result)) {
            return $result;
        };

        return $area_id;
    }

    /**
     * Delete an application
     *
     * @access public
     * @param  integer id of application
     * @return mixed   boolean or MDB2 Error object or false
     */
    function removeApplication($application_id)
    {
        if (!is_numeric($application_id)) {
            return false;
        }

        // Get all areas within the application, no matter what language
        $query = '
            SELECT
                area_id
            FROM
            ' . $this->prefix . 'areas
            WHERE
                application_id=' . $this->dbc->quote($application_id, 'integer');

        $areas = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($areas)) {
            return $areas;
        }

        // Delete all areas within the application
        if ($areas) {
            foreach($areas as $area) {
                $res = $this->removeArea($area['area_id']);
                if (MDB2::isError($res)) {
                    return $res;
                }
            }
        }

        // Delete application translations
        $result = $this->removeTranslation($application_id, LIVEUSER_SECTION_APPLICATION, $this->getCurrentLanguage(), true);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete application itself
        $query = 'DELETE FROM
                  ' . $this->prefix . 'applications
                WHERE
                  application_id = ' . $application_id;

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Delete an area
     *
     * @access public
     * @param  integer id of area
     * @return mixed   boolean or MDB2 Error object or false
     */
    function removeArea($area_id)
    {
        if (!is_numeric($area_id)) {
            return false;
        }

        // Delete all rights in this area
        $query = 'SELECT
                  right_id
                FROM
                  ' . $this->prefix . 'rights
                WHERE
                  area_id = ' . $area_id;

        $result = $this->dbc->queryCol($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        if (is_array($result)) {
            foreach($result as $right_id) {
                $this->removeRight($right_id);
            };
        };

        // Delete area admins
        $query = '
            DELETE FROM
                ' . $this->prefix . 'area_admin_areas
            WHERE
                area_id=' . $area_id . '
        ';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete area itself
        $query = 'DELETE FROM
                  ' . $this->prefix . 'areas
                WHERE
                  area_id = ' . $this->dbc->quote($area_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete area translations
        $result = $this->removeTranslation($area_id, LIVEUSER_SECTION_AREA, $this->getCurrentLanguage(), true);

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Update an application
     *
     * @access public
     * @param  integer id of application
     * @param  string  name of application constant
     * @param  string  name of application
     * @param  string  description of application
     * @return mixed   boolean or MDB2 Error object
     */
    function updateApplication($application_id, $define_name, $application_name, $application_description = null)
    {
        $query = 'UPDATE
                  ' . $this->prefix . 'applications
                SET
                  application_define_name = ' . $this->dbc->quote($define_name, 'text') . '
                WHERE
                  application_id = ' . $this->dbc->quote($application_id, 'integer');

        $result = $this->dbc->query($query);
        if (MDB2::isError($result)) {
            return $result;
        };

        // Update Application translation into Translations table
        $result = $this->updateTranslation(
            $application_id,
            LIVEUSER_SECTION_APPLICATION,
            $this->getCurrentLanguage(),
            $application_name,
            $application_description
        );

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Update an area
     *
     * @access public
     * @param  integer id of area
     * @param  int     id of application
     * @param  string  name of area constant
     * @param  string  name of area
     * @param  string  description of area
     * @return mixed   boolean or MDB2 Error object or false
     */
    function updateArea($area_id, $application_id, $define_name, $area_name, $area_description = null)
    {
        if (!is_numeric($area_id)) {
            return false;
        }

        $query = 'UPDATE
                  ' . $this->prefix . 'areas
                SET
                  application_id   = ' . $this->dbc->quote($application_id, 'integer') . ',
                  area_define_name = ' . $this->dbc->quote($define_name, 'text') . '
                WHERE
                  area_id = ' . $this->dbc->quote($area_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Update Area translation into Translations table
        $result = $this->updateTranslation(
            $area_id,
            LIVEUSER_SECTION_AREA,
            $this->getCurrentLanguage(),
            $area_name,
            $area_description
        );
        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Add a right in special area
     *
     * @access public
     * @param  integer id of area
     * @param  string  name of right constant
     * @param  string  name of right
     * @param  string  description of right
     * @return mixed   integer (right_id) or MDB2 Error object
     */
    function addRight($area_id, $define_name, $right_name, $right_description = null)
    {
        // Get next right id
        $right_id = $this->dbc->nextId($this->prefix . 'rights', true);

        if (MDB2::isError($right_id)) {
            return $right_id;
        };

        // Register right
        $query = 'INSERT INTO
                  ' . $this->prefix . 'rights
                  (right_id, area_id, right_define_name)
                VALUES
                  (
                    ' . $this->dbc->quote($right_id, 'integer') . ',
                    ' . $this->dbc->quote($area_id, 'integer') . ',
                    ' . $this->dbc->quote($define_name, 'text') . '
                  )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Insert Right translation into Translations table
        $result = $this->addTranslation(
            $right_id,
            LIVEUSER_SECTION_RIGHT,
            $this->getCurrentLanguage(),
            $right_name,
            $right_description
        );

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return $right_id;
    }

    /**
     * Delete a right
     *
     * @access public
     * @param  integer id of right
     * @return mixed   boolean or MDB2 Error object
     */
    function removeRight($right_id)
    {
        // Delete userright
        $query = 'DELETE FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  right_id = ' . $this->dbc->quote($right_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete group right
        $query = 'DELETE FROM
                  ' . $this->prefix . 'grouprights
                WHERE
                  right_id = ' . $this->dbc->quote($right_id, 'integer');
        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete right translations
        $result = $this->removeTranslation($right_id, LIVEUSER_SECTION_RIGHT, $this->getCurrentLanguage(), true);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete right itself
        $query = 'DELETE FROM
                  ' . $this->prefix . 'rights
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
     * Update a right
     *
     * @access public
     * @param  integer id of right
     * @param  integer id of area
     * @param  string  name of right constant
     * @param  string  name of right
     * @param  string  description of right
     * @return mixed   boolean or MDB2 Error object
     */
    function updateRight($right_id, $area_id, $define_name, $right_name, $right_description = null)
    {
        $query = 'UPDATE
                  ' . $this->prefix . 'rights
                SET
                  area_id           = ' . $this->dbc->quote($area_id, 'integer') . ',
                  right_define_name = ' . $this->dbc->quote($define_name, 'text') . '
                WHERE
                  right_id = ' . $this->dbc->quote($right_id, 'integer');

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Update Right translation into Translations table
        $result = $this->updateTranslation(
            $right_id,
            LIVEUSER_SECTION_RIGHT,
            $this->getCurrentLanguage(),
            $right_name,
            $right_description
        );

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return true;
    }

    /**
     * Add a user
     *
     * @access  public
     * @param   string   $authId    Auth user ID of the user that should be added.
     * @param   string   $authname  Auth container name.
     * @param   int      $type      User type (constants defined in Perm/Common.php) (optional).
     * @param   mixed    $permId    If specificed no new ID will be automatically generated instead
     * @return mixed   string (perm_user_id) or MDB2 Error object
     */
    function addUser($authId, $authName = null, $type = LIVEUSER_USER_TYPE_ID, $permId = null)
    {
        if (!$this->init_ok) {
            return false;
        }

        if (is_null($authName)) {
            return LiveUser::raiseError(LIVEUSER_ERROR, null, null,
                    'Auth name has to be passed with the function');
        }

        if (is_null($permId)) {
            $permId = $this->dbc->nextId($this->prefix . 'perm_users', true);
        }

        $query = '
            INSERT INTO
                ' . $this->prefix . 'perm_users
                (perm_user_id, auth_user_id, perm_type, auth_container_name)
            VALUES
                (
                ' . $this->dbc->quote($permId, 'integer') . ',
                ' . $this->dbc->quote($authId, 'text') . ',
                ' . $this->dbc->quote($type, 'integer') . ',
                ' . $this->dbc->quote($authName, 'text') . '
                )';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        }

        return $permId;
    }

    /**
     * Updates auth_user_id in the mapping table.
     *
     * @access  public
     * @param   int     perm_user_id of the user
     * @param   mixed   new Auth user ID
     * @param   mixed   new Auth Container name
     * @param   mixed   new perm type
     * @return  mixed   true or MDB2 Error object or false if there was an error
     *                  to begin with
     */
    function updateUser($permId, $authId = false, $authName = false, $type = false)
    {
        if (!$this->init_ok) {
            return false;
        }

        $update = array();
        if ($authId !== false) {
            $update[] = ' auth_user_id=' . $this->dbc->quote($authId, 'text');
        }
        if ($authName !== false) {
            $update[] = ' auth_container_name=' . $this->dbc->quote($authName, 'text');
        }
        if ($type !== false) {
            $update[] = ' perm_type=' . $this->dbc->quote($type, 'text');
        }

        if (!empty($update)) {
            $update = implode(',', $update);
            $query = '
                UPDATE
                    ' . $this->prefix . 'perm_users
                SET ' . $update . '
                WHERE
                    perm_user_id=' . $this->dbc->quote($permId, 'integer');

            $result = $this->dbc->query($query);

            if (MDB2::isError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Delete user
     *
     * @access public
     * @param  string  id of user
     * @return mixed   boolean or MDB2 Error object
     */
    function removeUser($permId)
    {
        if (!$this->init_ok) {
            return false;
        }

        // Delete user from perm table (Perm/MDB2)
        $query = '
            DELETE FROM
                ' . $this->prefix . 'perm_users
            WHERE
                perm_user_id = ' . $permId;

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        }

        // Delete group assignments
        $query = 'DELETE FROM
                  ' . $this->prefix . 'groupusers
                WHERE
                  perm_user_id = ' . $permId;

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Delete right assignments
        $query = 'DELETE FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  perm_user_id = ' . $permId;

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // remove user area admin relation
        $result = $this->removeUserAreaAdmin($permId);

        if (MDB2::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Grant right to user
     *
     * @access public
     * @param  string  id of user
     * @param  integer id of right
     * @return mixed   boolean or MDB2 Error object
     */
    function grantUserRight($permId, $right_id)
    {
        //return if this user already has right
        $query = 'SELECT
                  count(*)
                FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  perm_user_id = ' . $this->dbc->quote($right_id, 'integer') . '
                AND
                  right_id     = ' . $this->dbc->quote($right_id, 'integer');

        $count = $this->dbc->queryOne($query);

        if (MDB2::isError($count) || $count != 0) {
            return false;
        };

        $query = 'INSERT INTO
                  ' . $this->prefix . 'userrights
                  (perm_user_id, right_id, right_level)
                VALUES
                  (
                    ' . $this->dbc->quote($permId, 'integer') . ',
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
     * Update right level of userRight
     *
     * @access public
     * @param  string  id of user
     * @param  integer id of right
     * @param  integer right level
     * @return mixed   boolean or MDB2 Error object
     */
    function updateUserRight($permId, $right_id, $right_level)
    {
        $query = 'UPDATE
                  ' . $this->prefix . 'userrights
                SET
                  right_level = ' . $this->dbc->quote($right_level, 'integer') . '
                WHERE
                  perm_user_id = ' . $this->dbc->quote($permId, 'integer') . '
                AND
                  right_id = ' . $this->dbc->quote($right_id, 'integer');
        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        };

        // Job done ...
        return true;
    }

    /**
     * Revoke right from user
     *
     * @access public
     * @param  string  id of user
     * @param  integer id of right
     * @return mixed   boolean or MDB2 Error object
     */
    function revokeUserRight($permId, $right_id = null)
    {
        $query = 'DELETE FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  perm_user_id = ' . $this->dbc->quote($permId, 'integer');
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
     * Get list of all applications
     *
     * This method accepts the following options...
     *  'where_application_id' = [APPLICATION_ID]
     *
     * @access public
     * @param  array an array determining which fields and conditions to use
     * @return mixed array or MDB2 Error object
     */
    function getApplications($options = null)
    {
        $query = 'SELECT
                  applications.application_id          AS application_id,
                  applications.application_define_name AS define_name,
                  translations.name                    AS name,
                  translations.description             AS description
                FROM
                  ' . $this->prefix . 'applications applications,
                  ' . $this->prefix . 'translations translations
                WHERE';

        if (isset($options['where_application_id'])
                && is_numeric($options['where_application_id'])) {
            $query .= ' applications.application_id = '
                . $this->dbc->quote($options['where_application_id'], 'integer') . ' AND ';
        }

        $query .= ' applications.application_id = translations.section_id AND
                  translations.section_type = '
                    . LIVEUSER_SECTION_APPLICATION . ' AND
                  translations.language_id = '
                    . $this->dbc->quote($this->_langs[$this->getCurrentLanguage()], 'integer') . '
                ORDER BY
                  applications.application_id ASC';

        $applications = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($applications)) {
            return $applications;
        }

        return $applications;
    }

    /**
     * Get list of all areas within a given application
     *
     * This method accepts the following options...
     *  'where_area_id' = [AREA_ID],
     *  'where_application_id' = [APPLICATION_ID],
     *  'with_applications' = [BOOLEAN]
     *
     * @access public
     * @param  array an array determining which fields and conditions to use
     * @return mixed array or MDB2 Error object
     */
    function getAreas($options = null)
    {
        $query = 'SELECT
                  areas.area_id            AS area_id,
                  areas.application_id     AS application_id,
                  translations.name        AS name,
                  translations.description AS description,
                  areas.area_define_name   AS define_name
                FROM
                  ' . $this->prefix . 'areas areas,
                  ' . $this->prefix . 'translations translations
                WHERE';

        if (isset($options['where_area_id'])
                && is_numeric($options['where_area_id'])) {
                  $query .= ' areas.area_id=' . $this->dbc->quote($options['where_area_id'], 'integer') . ' AND';
        };

        if (isset($options['where_application_id'])
                && is_numeric($options['where_application_id'])) {
                  $query .= ' areas.application_id=' . $this->dbc->quote($options['where_application_id'], 'integer') . ' AND';
        };

        $query .= ' areas.area_id = translations.section_id AND
                  translations.section_type = '.LIVEUSER_SECTION_AREA . ' AND
                  translations.language_id = ' . $this->dbc->quote($this->_langs[$this->getCurrentLanguage()], 'integer') . '
                ORDER BY
                  areas.area_id ASC';

        $areas = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($areas)) {
            return $areas;
        }

        $_areas = array();
        if (is_array($areas)) {
            foreach($areas as $key => $value) {
                $id = $value['area_id'];
                $_areas[$id] = $value;

                if (isset($options['with_applications'])) {
                    $_areas[$id]['application'] = $this->getTranslation($value['application_id'], LIVEUSER_SECTION_APPLICATION);
                    if (MDB2::isError($_areas[$id]['application'])) {
                        return $_areas[$id]['application'];
                    }
                };
            };
        };

        return $_areas;
    }

    /**
     * Get list of all languages
     *
     * This method accepts the following options...
     *  'where_language_id' = [LANGUAGE_ID],
     *  'with_translations' = [BOOLEAN]
     *
     * @access public
     * @param  array an array determining which fields and conditions to use
     * @return mixed array or MDB2 Error object
     */
    function getLanguages($options = null)
    {
        $query = 'SELECT
                  languages.language_id     AS language_id,
                  languages.two_letter_name AS two_letter_code
                FROM
                  ' . $this->prefix . 'languages languages';

        if (isset($options['where_language_id'])
                && is_numeric($options['where_language_id'])) {
            $query .= ' WHERE languages.language_id = ' . $this->dbc->quote($options['where_language_id'], 'integer');
        };

        $langs = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        if (MDB2::isError($langs)) {
            return $langs;
        };

        if (!is_array($langs)) {
            return array();
        }

        if (isset($options['with_translations'])
                && $options['with_translations']
        ) {
            $query = '
                    SELECT
                        translations.section_id     AS section_id,
                        translations.language_id    AS language_id,
                        languages.two_letter_name   AS two_letter_code,
                        translations.name           AS name
                    FROM
                        ' . $this->prefix . 'languages languages,
                        ' . $this->prefix . 'translations translations
                    WHERE
                        languages.language_id = translations.language_id
                        AND translations.section_type = ' . LIVEUSER_SECTION_LANGUAGE;

            $trans = $this->dbc->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

            if (MDB2::isError($trans)) {
                return $trans;
            };
        };

        foreach($langs as $key => $value) {
            unset($langs[$key]);
            $code = $value['two_letter_code'];
            unset($value['two_letter_code']);
            $langs[$code] = $value;

            if ($options['with_translations'] == true && is_array($trans)) {
                foreach($trans as $translation) {
                    if ($translation['section_id'] == $value['language_id']) {
                        $langs[$code]['name'] = $translation['name'];
                    };
                };
            };
        };
        return $langs;
    }

    /**
     * Get list of all rights
     *
     * This method accepts the following options...
     *  'where_user_id' = [AUTH_USER_ID],
     *  'where_group_id' = [GROUP_ID],
     *  'where_right_id' = [RIGHT_ID],
     *  'where_area_id' = [AREA_ID],
     *  'where_application_id' = [APPLICATION_ID],
     *  'with_areas' = [BOOLEAN],
     *  'with_applications' = [BOOLEAN]
     *
     * @access public
     * @param  array an array determining which fields and conditions to use
     * @return mixed array or MDB2 Error object
     */
    function getRights($options = null)
    {
        $query = 'SELECT
                  rights.right_id      AS right_id,
                  rights.area_id       AS area_id,
                  areas.application_id AS application_id,';

        if (isset($options['where_user_id'])) {
            $query .= ' userrights.perm_user_id AS user_id,';
        }

        if (isset($options['where_group_id'])
                && is_numeric($options['where_group_id'])) {
            $query .= ' grouprights.group_id AS group_id,';
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
        }

        if (isset($options['where_group_id'])
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
        };

        if (isset($options['where_group_id'])
                && is_numeric($options['where_group_id'])) {
            $query .= ' grouprights.group_id = '
                . $this->dbc->quote($options['where_group_id'], 'integer') . ' AND
                      grouprights.right_id = rights.right_id AND';
        };

        $query .= ' rights.area_id = areas.area_id AND
                  rights.right_id = translations.section_id AND
                  translations.section_type = ' . LIVEUSER_SECTION_RIGHT . ' AND
                  translations.language_id = '
                    . $this->dbc->quote($this->_langs[$this->getCurrentLanguage()], 'integer') . '
                GROUP BY
                  rights.right_id, rights.area_id, areas.application_id';

        if (isset($options['where_user_id'])) {
            $query .= ',userrights.perm_user_id';
        }

        if (isset($options['where_group_id'])
                && is_numeric($options['where_group_id'])) {
            $query .= ',grouprights.group_id';
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
            foreach($rights as $key => $value)
            {
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
            }
        }

        return $_rights;
    }

    /**
     * Make a user an admin of a given area.
     *
     * @access public
     * @param  mixed  user identifier
     * @param  int    area identifier
     * @return mixed  true on success, MDB2 Error object or false
     */
    function addUserAreaAdmin($permId, $area_id)
    {
        $query = '
            INSERT INTO
                ' . $this->prefix . 'area_admin_areas
                (perm_user_id, area_id)
            VALUES (
                ' . $permId . ', ' . $this->dbc->quote($area_id, 'integer') . '
            )
        ';

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Remove the privilege of being an admin.
     *
     * If no area_id is provided the user will be removed asan admin
     * from all areas he was an admin for.
     *
     * @access public
     * @param  mixed  user identifier
     * @param  int    area identifier
     * @return mixed  true on success, MDB2 Error object or false
     */
    function removeUserAreaAdmin($permId, $area_id = null)
    {
        $query = '
            DELETE FROM
                ' . $this->prefix . 'area_admin_areas
            WHERE
                perm_user_id=' . $permId;

        if (!is_null($area_id) && is_numeric($area_id)) {
            $query .= '
            AND
                area_id= ' . $this->dbc->quote($area_id, 'integer');
        }

        $result = $this->dbc->query($query);

        if (MDB2::isError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Fetch users from the database.
     *
     * The only supported filter is perm_user_id => 'value'
     *
     * The array will look like this:
     * <code>
     * $userData[0]['perm_user_id'] = 1;
     *             ['type']         = 1;
     *             ['container']    = '';
     *             ['rights']       = array(); // the array returned by getRights()
     * </code>
     *
     * @access  public
     * @param   array   filters to apply to fetched data
     * @param   boolean  If true the rights for each user will be retrieved.
     * @param   boolean will return an associative array with the auth_user_id
     *                  as the key by using the $rekey param in MDB2::fetchAll()
     * @return  mixed    Array with user data or error object.
     * @see     LiveUser_Admin_Perm_DB_Common::getRights()
     */
    function getUsers($filters = array(), $options = array(), $rekey = false)
    {
        $query = 'SELECT
                      users.perm_user_id        AS perm_user_id,
                      users.auth_user_id        AS auth_user_id,
                      users.perm_type                AS type,
                      users.auth_container_name AS container
                  FROM
                  ' . $this->prefix . 'perm_users users';

        if (isset($filters['group_id'])) {
            $query .= ', ' . $this->prefix . 'groupusers groupusers';
        }

        if (isset($filters['group_id'])) {
            $filter_array[] = 'groupusers.perm_user_id=users.perm_user_id';
            $filter_array[] = 'groupusers.group_id IN (' . $this->dbc->datatype->implodeArray($filters['group_id'], 'integer') . ')';
        }

        if (isset($filters['perm_user_id'])) {
            $filter_array[] = 'users.perm_user_id=' . $filters['perm_user_id'];
        }

        if (isset($filter_array) && count($filter_array)) {
            $query .= ' WHERE '.implode(' AND ', $filter_array);
        }

        $types = array('integer', 'text', 'integer', 'text');
        $res = $this->dbc->queryAll($query, $types, MDB2_FETCHMODE_ASSOC, $rekey);

        if (is_array($res)) {
            foreach ($res as $k => $v) {
                if (isset($options['with_rights'])) {
                    $res[$k]['rights'] = $this->getRights(array('where_user_id' => $v['perm_user_id']));
                }
                if (isset($options['with_groups'])) {
                    $res[$k]['groups'] = $this->getGroups(array('where_user_id' => $v['perm_user_id']));
                }
            }
        } else if (!MDB2::isError($res)) {
            $res = array();
        }
        return $res;
    }
}
?>

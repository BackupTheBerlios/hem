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
require_once 'DB.php';

/**
 * Container for simple rights managements.
 *
 * With it you can only assign rights to users. For advanced uses like
 * groups assignements and more see DB_Medium and DB_Complex.
 *
 * @category authentication
 * @author  Christian Dickmann <dickmann@php.net>
 * @author  Markus Wolff <wolff@21st.de>
 * @author  Matt Scifo <mscifo@php.net>
 * @version $Id: DB_Simple.php,v 1.1 2004/07/16 13:58:10 mloitzl Exp $
 * @package LiveUser
 */
class LiveUser_Admin_Perm_Container_DB_Simple extends LiveUser_Admin_Perm_Common
{
    /**
     * Constructor
     *
     * @access protected
     * @param  array  full liveuser configuration array
     * @return void
     * @see    LiveUser::factory()
     */
    function LiveUser_Admin_Perm_Container_DB_Simple(&$connectOptions)
    {
        if (is_array($connectOptions)) {
            foreach ($connectOptions as $key => $value) {
                if (isset($this->$key)) {
                    $this->$key = $value;
                }
            }
            if (isset($connectOptions['connection'])  &&
                    DB::isConnection($connectOptions['connection'])
            ) {
                $this->dbc     = &$connectOptions['connection'];
                $this->init_ok = true;
            } elseif (isset($connectOptions['dsn'])) {
                $this->dsn = $connectOptions['dsn'];
                $options = null;
                if (isset($connectOptions['options'])) {
                    $options = $connectOptions['options'];
                }
                $options['portability'] = DB_PORTABILITY_ALL;
                $this->dbc =& DB::connect($connectOptions['dsn'], $options);
                if (!DB::isError($this->dbc)) {
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
     * @return  mixed   Permission ID or DB error.
     */
    function getPermUserId($authId, $authName)
    {
        $query = '
            SELECT
                perm_user_id
            FROM
                ' . $this->prefix . 'perm_users
            WHERE
                auth_user_id = ' . $this->dbc->quoteSmart($authId).'
            AND
                auth_container_name = '.$this->dbc->quoteSmart($authName);
        $permId = $this->dbc->getOne($query);

        return $permId;
    } // end func _getPermUserId

    /**
    * Gets the auth ID of a user.
    *
    * @access  public
    * @param   string  Perm user ID.
    * @return  mixed   Permission ID or MDB2 error.
    */
    function getAuthUserId($permId)
    {
        $query = '
            SELECT
                auth_user_id, auth_container_name
            FROM
                ' . $this->prefix . 'perm_users
            WHERE
                perm_user_id = '.$this->dbc->quoteSmart($permId);
                $authId = $this->dbc->getRow($query, DB_FETCHMODE_ASSOC);
            return $authId;
    } // end func _getAuthUserId

    /**
     * Reads all languages from databases and stores them in private variable
     *
     * $this->_langs is filled with this array structure:
     *     two_letter_code => language_id
     *
     * @access private
     * @return mixed boolean or DB Error object
     */
    function _getLanguages()
    {
        if (sizeof($this->_langs) < 1) {
            $query = 'SELECT two_letter_name, language_id FROM ' . $this->prefix . 'languages';
            $langs = $this->dbc->getAssoc($query);
            if (DB::isError($langs)) {
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
     * @return mixed   boolean or DB Error object or false
     */
    function setCurrentLanguage($language)
    {
        // Get all language ids
        if (DB::isError($result = $this->_getLanguages())) {
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
     * @return boolean  always true
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
     * @return mixed boolean or DB Error object
     */
    function addTranslation($section_id, $section_type, $language, $name, $description = null)
    {
        // Get all language ids
        if (DB::isError($result = $this->_getLanguages())) {
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
                    ' . (int)$section_id . ',
                    ' . (int)$section_type . ',
                    ' . (int)$this->_langs[$language] . ',
                    ' . $this->dbc->quoteSmart($name) . ',
                    ' . (($description === null) ? 'null' : $this->dbc->quoteSmart($description)) . '
                  )';

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
     * @return mixed boolean or DB Error object
     */
    function updateTranslation($section_id, $section_type, $language, $name, $description = null)
    {
        // Get all language ids
        if (DB::isError($result = $this->_getLanguages())) {
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
                  name        = ' . $this->dbc->quoteSmart($name) . ',
                  description = ' . (($description === null) ? 'null' : $this->dbc->quoteSmart($description)) . '
                WHERE
                  section_id    = ' . (int)$section_id . ' AND
                  section_type  = ' . (int)$section_type . ' AND
                  language_id   = ' . (int)$this->_langs[$language];

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
     * @return mixed boolean or DB Error object
     */
    function removeTranslation($section_id, $section_type, $language, $recursive = false)
    {
        // Get all language ids
        if (DB::isError($result = $this->_getLanguages())) {
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
                  section_id    = ' . (int)$section_id . ' AND
                  section_type  = ' . (int)$section_type;
        if (!$recursive) {
            $query .= ' AND language_id = ' . (int)$this->_langs[$language];
        };

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
     * @return mixed array or DB Error object
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
                  section_id    = ' . (int)$section_id . ' AND
                  section_type  = ' . (int)$section_type;
        $translation = $this->dbc->getRow($query, DB_FETCHMODE_ASSOC);
        if (DB::isError($translation)) {
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
     * @param  string native name of language
     * @param  string name of language
     * @param  string description of language
     * @return mixed integer (language_id) or DB Error object
     */
    function addLanguage($two_letter_code, $native_name, $language_name, $language_description = null)
    {
        // Get next language id
        $language_id = $this->dbc->nextId($this->prefix . 'languages', true);

        if (DB::isError($language_id)) {
            return $language_id;
        };

        // Add language
        $query = 'INSERT INTO
                  ' . $this->prefix . 'languages
                  (language_id, two_letter_name, native_name)
                VALUES
                  (
                    ' . $language_id . ',
                    ' . $this->dbc->quoteSmart($two_letter_code) . ',
                    ' . $this->dbc->quoteSmart($native_name) . '
                  )';

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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

        if (DB::isError($result)) {
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
     * @return mixed   boolean or DB Error object
     */
    function removeLanguage($language)
    {
        // Get all language ids
        if (DB::isError($result = $this->_getLanguages())) {
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
                  language_id = ' . (int)$this->_langs[$language];

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        };

        // Delete language translations
        $result = $this->removeTranslation($this->_langs[$language], LIVEUSER_SECTION_LANGUAGE, $this->getCurrentLanguage(), true);

        if (DB::isError($result)) {
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
     * @param  string  native name of language
     * @param  string  name of language
     * @param  string  description of language
     * @return mixed   boolean or DB Error object
     */
    function updateLanguage($language, $native_name, $language_name, $language_description = null)
    {
        // Get all language ids
        if (DB::isError($result = $this->_getLanguages())) {
            return $result;
        };

        // Check if language is a known one
        if (!isset($this->_langs[$language])) {
            return false;
        };

        $query = 'UPDATE
                  ' . $this->prefix . 'languages
                SET
                  native_name     = ' . $this->dbc->quoteSmart($native_name) . '
                WHERE
                  language_id     = ' . (int)$this->_langs[$language];

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        };

        // Update Language translation into Translations table
        $result = $this->updateTranslation(
            $this->_langs[$language],
            LIVEUSER_SECTION_LANGUAGE,
            $this->getCurrentLanguage(),
            $language_name,
            $langauge_description
        );

        if (DB::isError($result)) {
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
     * @return mixed  integer (application_id) or DB Error object
     */
    function addApplication($define_name, $application_name, $application_description = null)
    {
        // Get next application id
        $application_id = $this->dbc->nextId($this->prefix . 'applications', true);
        if (DB::isError($application_id)) {
            return $application_id;
        };

        // Register new application
        $query = 'INSERT INTO
                  ' . $this->prefix . 'applications
                  (application_id, application_define_name)
                VALUES
                  (
                    ' . (int)$application_id . ',
                    ' . $this->dbc->quoteSmart($define_name) . '
                  )';

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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

        if (DB::isError($result)) {
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
     * @return mixed  integer (area_id) or DB Error object
     */
    function addArea($application_id, $define_name, $area_name, $area_description = null)
    {
        // Get next area id
        $area_id = $this->dbc->nextId($this->prefix . 'areas', true);

        if (DB::isError($area_id)) {
            return $area_id;
        };

        // Register new area
        $query = 'INSERT INTO
                  ' . $this->prefix . 'areas
                  (area_id, area_define_name, application_id)
                VALUES
                  (
                    ' . (int)$area_id . ',
                    ' . $this->dbc->quoteSmart($define_name) . ',
                    ' . (int)$application_id . '
                  )';

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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

        if (DB::isError($result)) {
            return $result;
        };

        return $area_id;
    }

    /**
     * Delete an application
     *
     * @access public
     * @param  integer id of application
     * @return mixed   boolean or DB Error object or false
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
                application_id=' . (int)$application_id;

        $areas = $this->dbc->getAll($query, null, DB_FETCHMODE_ASSOC);

        if (DB::isError($areas)) {
            return $areas;
        }

        // Delete all areas within the application
        if (is_array($areas)) {
            foreach ($areas as $area) {
                $res = $this->removeArea($area['area_id']);
                if (DB::isError($res)) {
                    return $res;
                }
            }
        }

        // Delete application translations
        $result = $this->removeTranslation($application_id, LIVEUSER_SECTION_APPLICATION, $this->getCurrentLanguage(), true);

        if (DB::isError($result)) {
            return $result;
        };

        // Delete application itself
        $query = 'DELETE FROM
                  ' . $this->prefix . 'applications
                WHERE
                  application_id = ' . $application_id;

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        };

        return true;
    }

    /**
     * Delete an area
     *
     * @access public
     * @param  integer id of area
     * @return mixed   boolean or DB Error object or false
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

        $result = $this->dbc->getCol($query);

        if (DB::isError($result)) {
            return $result;
        };

        if (is_array($result)) {
            foreach ($result as $right_id) {
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

        if (DB::isError($result)) {
            return $result;
        };

        // Delete area itself
        $query = 'DELETE FROM
                  ' . $this->prefix . 'areas
                WHERE
                  area_id = ' . (int)$area_id;

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        };

        // Delete area translations
        $result = $this->removeTranslation($area_id, LIVEUSER_SECTION_AREA, $this->getCurrentLanguage(), true);

        if (DB::isError($result)) {
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
     * @return mixed   boolean or DB Error object
     */
    function updateApplication($application_id, $define_name, $application_name, $application_description = null)
    {
        $query = 'UPDATE
                  ' . $this->prefix . 'applications
                SET
                  application_define_name = ' . $this->dbc->quoteSmart($define_name) . '
                WHERE
                  application_id = ' . (int)$application_id;

        $result = $this->dbc->query($query);
        if (DB::isError($result)) {
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

        if (DB::isError($result)) {
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
     * @return mixed   boolean or DB Error object or false
     */
    function updateArea($area_id, $application_id, $define_name, $area_name, $area_description = null)
    {
        if (!is_numeric($area_id)) {
            return false;
        }

        $query = 'UPDATE
                  ' . $this->prefix . 'areas
                SET
                  application_id   = ' . $application_id . ',
                  area_define_name = ' . $this->dbc->quoteSmart($define_name) . '
                WHERE
                  area_id = ' . (int)$area_id;

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
        if (DB::isError($result)) {
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
     * @return mixed   integer (right_id) or DB Error object
     */
    function addRight($area_id, $define_name, $right_name, $right_description = null)
    {
        // Get next right id
        $right_id = $this->dbc->nextId($this->prefix . 'rights', true);

        if (DB::isError($right_id)) {
            return $right_id;
        };

        // Register right
        $query = 'INSERT INTO
                  ' . $this->prefix . 'rights
                  (right_id, area_id, right_define_name)
                VALUES
                  (
                    ' . (int)$right_id . ',
                    ' . (int)$area_id . ',
                    ' . $this->dbc->quoteSmart($define_name) . '
                  )';

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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

        if (DB::isError($result)) {
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
     * @return mixed   boolean or DB Error object
     */
    function removeRight($right_id)
    {
        // Delete userright
        $query = 'DELETE FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  right_id = ' . (int)$right_id;

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        };

        // Delete group right
        $query = 'DELETE FROM
                  ' . $this->prefix . 'grouprights
                WHERE
                  right_id = ' . (int)$right_id;
        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        };

        // Delete right translations
        $result = $this->removeTranslation($right_id, LIVEUSER_SECTION_RIGHT, $this->getCurrentLanguage(), true);

        if (DB::isError($result)) {
            return $result;
        };

        // Delete right itself
        $query = 'DELETE FROM
                  ' . $this->prefix . 'rights
                WHERE
                  right_id = ' . (int)$right_id;
        $result = $this->dbc->query($query);
        if (DB::isError($result)) {
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
     * @return mixed   boolean or DB Error object
     */
    function updateRight($right_id, $area_id, $define_name, $right_name, $right_description = null)
    {
        $query = 'UPDATE
                  ' . $this->prefix . 'rights
                SET
                  area_id           = ' . (int)$area_id . ',
                  right_define_name = ' . $this->dbc->quoteSmart($define_name) . '
                WHERE
                  right_id = ' . (int)$right_id;

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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

        if (DB::isError($result)) {
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
     * @param   int         $type      User type (constants defined in Perm/Common.php) (optional).
     * @param   mixed  $permId    If specificed no new ID will be automatically generated instead
     * @return mixed    string (perm_user_id) or DB Error object
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
                ' . (int)$permId . ',
                ' . $this->dbc->quoteSmart($authId) . ',
                ' . (int)$type . ',
                ' . $this->dbc->quoteSmart($authName) . '
                )';

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
     * @return  mixed   true or DB Error object or false if there was an error
     *                  to begin with
     */
    function updateAuthUserId($permId, $authId, $authName = false)
    {
        if (!$this->init_ok) {
            return false;
        }

        $query = '
            UPDATE
                ' . $this->prefix . 'perm_users
            SET';
        if ($authName !== false) {
            $query .= ' auth_container_name=' . $this->dbc->quoteSmart($authName) . ',';
        }
        $query .= '
                auth_user_id=' . $this->dbc->quoteSmart($authId) . '
            WHERE
                perm_user_id=' . (int)$permId;

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Delete user
     *
     * @access public
     * @param  string  id of user
     * @return mixed   boolean or DB Error object
     */
    function removeUser($permId)
    {
        if (!$this->init_ok) {
            return false;
        }

        // Delete user from perm table (Perm/DB)
        $query = '
            DELETE FROM
                ' . $this->prefix . 'perm_users
            WHERE
                perm_user_id = ' . $permId;

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        }

        // Delete group assignments
        $query = 'DELETE FROM
                  ' . $this->prefix . 'groupusers
                WHERE
                  perm_user_id = ' . $permId;

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        };

        // Delete right assignments
        $query = 'DELETE FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  perm_user_id = ' . $permId;

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
            return $result;
        };

        // remove user area admin relation
        $result = $this->removeUserAreaAdmin($permId);

        if (DB::isError($result)) {
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
     * @return mixed   boolean or DB Error object
     */
    function grantUserRight($permId, $right_id)
    {
        //return if this user already has right
        $query = 'SELECT
                  count(*)
                FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  perm_user_id         = ' . (int)$permId . '
                AND
                  right_id     = ' . (int)$right_id;

        $count = $this->dbc->getOne($query);

        if (DB::isError($count) || $count != 0) {
            return false;
        };

        $query = 'INSERT INTO
                  ' . $this->prefix . 'userrights
                  (perm_user_id, right_id, right_level)
                VALUES
                  (
                    ' . (int)$permId . ',
                    ' . (int) $right_id . ', '.LIVEUSER_MAX_LEVEL.'
                  )';

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
     * @return mixed   boolean or DB Error object
     */
    function updateUserRight($permId, $right_id, $right_level)
    {
        $query = 'UPDATE
                  ' . $this->prefix . 'userrights
                SET
                  right_level = ' . (int)$right_level . '
                WHERE
                  perm_user_id = ' . (int)$permId . '
                AND
                  right_id = ' . (int)$right_id;
        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
     * @return mixed   boolean or DB Error object
     */
    function revokeUserRight($permId, $right_id = null)
    {
        $query = 'DELETE FROM
                  ' . $this->prefix . 'userrights
                WHERE
                  perm_user_id = ' . (int)$permId;
        if (!is_null($right_id)) {
            $query .= ' AND
              right_id = ' . (int) $right_id;
        }

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
     * @return mixed array or DB Error object
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
                . (int)$options['where_application_id'] . ' AND ';
        }

        $query .= ' applications.application_id = translations.section_id AND
                  translations.section_type = '
                    . LIVEUSER_SECTION_APPLICATION . ' AND
                  translations.language_id = '
                    . (int)$this->_langs[$this->getCurrentLanguage()] . '
                ORDER BY
                  applications.application_id ASC';

        $applications = $this->dbc->getAll($query, null, DB_FETCHMODE_ASSOC);

        if (DB::isError($applications)) {
            return $applications;
        };

        if (!is_array($applications)) {
            return array();
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
     * @return mixed array or DB Error object
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
                  $query .= ' areas.area_id=' . (int)$options['where_area_id'] . ' AND';
        };

        if (isset($options['where_application_id'])
                && is_numeric($options['where_application_id'])) {
                  $query .= ' areas.application_id=' . (int)$options['where_application_id'] . ' AND';
        };

        $query .= ' areas.area_id = translations.section_id AND
                  translations.section_type = '.LIVEUSER_SECTION_AREA . ' AND
                  translations.language_id = ' . (int)$this->_langs[$this->getCurrentLanguage()] . '
                ORDER BY
                  areas.area_id ASC';

        $areas = $this->dbc->getAll($query, null, DB_FETCHMODE_ASSOC);

        if (DB::isError($areas)) {
            return $areas;
        };

        if (!is_array($areas)) {
            return array();
        }

        $_areas = array();
        foreach ($areas as $key => $value) {
            $id = $value['area_id'];
            $_areas[$id] = $value;

            if (isset($options['with_applications'])) {
                $_areas[$id]['application'] = $this->getTranslation($value['application_id'], LIVEUSER_SECTION_APPLICATION);
                if (DB::isError($_areas[$id]['application'])) {
                    return $_areas[$id]['application'];
                }
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
     * @return mixed array or DB Error object
     */
    function getLanguages($options = null)
    {
        $query = 'SELECT
                  languages.language_id     AS language_id,
                  languages.two_letter_name AS two_letter_code,
                  languages.native_name     AS native_name
                FROM
                  ' . $this->prefix . 'languages languages';

        if (isset($options['where_language_id'])
                && is_numeric($options['where_language_id'])) {
            $query .= ' WHERE languages.language_id = ' . (int)$options['where_language_id'];
        };

        $langs = $this->dbc->getAll($query, null, DB_FETCHMODE_ASSOC);

        if (DB::isError($langs)) {
            return $langs;
        };

        if (!is_array($langs)) {
            return array();
        }

        if (isset($options['with_translations'])
                && $options['with_translations']) {
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
                        AND translations.section_type = ' . LIVEUSER_SECTION_LANGUAGE . '
                        AND translations.language_id = ' . (int)$this->_langs[$this->getCurrentLanguage()];

            $trans = $this->dbc->getAll($query, null, DB_FETCHMODE_ASSOC);

            if (DB::isError($trans)) {
                return $trans;
            };
        };

        foreach ($langs as $key => $value) {
            unset($langs[$key]);
            $code = $value['two_letter_code'];
            unset($value['two_letter_code']);
            $value['name'] = $value['native_name'];
            $langs[$code] = $value;

            if ($options['with_translations'] == true && is_array($trans)) {
                foreach ($trans as $translation) {
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
     * @return mixed array or DB Error object
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
                . (int)$options['where_right_id'] . ' AND';
        };

        if (isset($options['where_area_id'])
                && is_numeric($options['where_area_id'])) {
            $query .= ' rights.area_id = '
                . (int)$options['where_area_id'] . ' AND';
        };

        if (isset($options['where_application_id'])
                && is_numeric($options['where_application_id'])) {
            $query .= ' areas.application_id = '
                . (int)$options['where_application_id'] . ' AND';
        };

        if (isset($options['where_user_id'])) {
            $query .= ' userrights.perm_user_id = '
                . (int)$options['where_user_id'] . ' AND
                      userrights.right_id = rights.right_id AND';
        };

        if (isset($options['where_group_id'])
                && is_numeric($options['where_group_id'])) {
            $query .= ' grouprights.group_id = '
                . (int)$options['where_group_id'] . ' AND
                      grouprights.right_id = rights.right_id AND';
        };

        $query .= ' rights.area_id = areas.area_id AND
                  rights.right_id = translations.section_id AND
                  translations.section_type = ' . LIVEUSER_SECTION_RIGHT . ' AND
                  translations.language_id = '
                    . (int)($this->_langs[$this->getCurrentLanguage()]) . '
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

        $rights = $this->dbc->getAll($query, null, DB_FETCHMODE_ASSOC);

        if (DB::isError($rights)) {
            return $rights;
        };

        $_rights = array();
        if (is_array($rights)) {
            foreach ($rights as $key => $value)
            {
                $id = $value['right_id'];
                $_rights[$id] = $value;

                if (isset($options['with_areas'])) {
                    // Add area
                    $filter = array('where_area_id' => $value['area_id']);
                    $_rights[$id]['area'] =
                        array_shift($this->getAreas($filter));

                    if (DB::isError($_rights[$id]['area'])) {
                        return $_rights[$id]['area'];
                    };

                    if (isset($options['with_applications'])) {
                        // Add application
                        $filter = array('where_application_id' => $value['application_id']);
                        $_rights[$id]['application'] =
                            array_shift($this->getApplications($filter));

                        if (DB::isError($_rights[$id]['application'])) {
                            return $_rights[$id]['application'];
                        };
                    };
                };
            };
        }

        return $_rights;
    }

   /**
     * Make a user an admin of a given area.
     *
     * @access public
     * @param  mixed  user identifier
     * @param  int    area identifier
     * @return mixed  true on success, DB Error object or false
     */
    function addUserAreaAdmin($permId, $area_id)
    {
        $query = '
            INSERT INTO
                ' . $this->prefix . 'area_admin_areas
                (perm_user_id, area_id)
            VALUES (
                ' . $permId . ', ' . (int)$area_id . '
            )
        ';

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
     * @return mixed  true on success, DB Error object or false
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
                area_id= ' . (int)$area_id;
        }

        $result = $this->dbc->query($query);

        if (DB::isError($result)) {
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
     *                  as the key by using DB::getAssoc() instead of DB::getAll()
     * @return  mixed    Array with user data or error object.
     * @see     LiveUser_Admin_Perm_DB_Common::getRights()
     */
    function getUsers($filters = array(), $options = array(), $rekey = false)
    {
        $query = 'SELECT
                      users.perm_user_id        AS perm_user_id,
                      users.auth_user_id        AS auth_user_id,
                      users.perm_type           AS type,
                      users.auth_container_name AS container
                  FROM
                  ' . $this->prefix . 'perm_users users';

        if (isset($filters['group_id'])) {
            $query .= ', ' . $this->prefix . 'groupusers groupusers';
        }

        if (isset($filters['group_id'])) {
            $filter_array[] = 'groupusers.perm_user_id=users.perm_user_id';
            $filter_array[] = 'groupusers.group_id IN (' . implode(',', $filters['group_id']) . ')';
        }

        if (isset($filters['perm_user_id'])) {
            $filter_array[] = 'users.perm_user_id=' . $filters['perm_user_id'];
        }

        if (isset($filter_array) && count($filter_array)) {
          $query .= ' WHERE '.implode(' AND ', $filter_array);
        }

        if ($rekey) {
            $res = $this->dbc->getAssoc($query, false, array(), DB_FETCHMODE_ASSOC);
        } else {
            $res = $this->dbc->getAll($query, array(), DB_FETCHMODE_ASSOC);
        }

        if (is_array($res)) {
            foreach ($res as $k => $v) {
                if (isset($options['with_rights'])) {
                    $res[$k]['rights'] = $this->getRights(array('where_user_id' => $v['perm_user_id']));
                }
                if (isset($options['with_groups'])) {
                    $res[$k]['groups'] = $this->getGroups(array('where_user_id' => $v['perm_user_id']));
                }
            }
        } elseif (!DB::isError($res)) {
            $res = array();
        }
        return $res;
    }
}
?>

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
 * Base class for administration of permissions
 *
 * @package  LiveUser
 */

/**
 * Require permission handling common class
 *
 * used to get the constants defined there. This class is small, being
 * mostly abstract so the performance impact is very light.
 */
require_once 'LiveUser/Perm/Common.php';

/**#@+
 * Section types
 *
 * @var integer
 */
define('LIVEUSER_SECTION_APPLICATION',  1);
define('LIVEUSER_SECTION_AREA',         2);
define('LIVEUSER_SECTION_GROUP',        3);
define('LIVEUSER_SECTION_LANGUAGE',     4);
define('LIVEUSER_SECTION_RIGHT',        5);
/**#@-*/

/**
 * This class provides a set of functions for implementing a user
 * permission management system on live websites. All authorisation
 * backends/containers must be extensions of this base class.
 *
 * @author  Markus Wolff <wolff@21st.de>
 * @author  Bjoern Kraus <krausbn@php.net>
 * @version $Id: Common.php,v 1.1 2004/07/16 13:59:00 mloitzl Exp $
 * @package LiveUser
 */
class LiveUser_Admin_Perm_Common
{
    /**
     * Indicates if backend module initialized correctly. If yes,
     * true, if not false. Backend module won't initialize if the
     * init value (usually an object or resource handle that
     * identifies the backend to be used) is not of the required
     * type.
     *
     * @var    boolean
     * @access private
     */
    var $init_ok = false;

    /**
     * Table prefix
     *
     * @access public
     * @var    string
     */
    var $prefix = 'liveuser_';

    /**
     * Associative array of all available languages
     * (short name (i.e. "DE", "EN"...) as key, database id AS value)
     *
     * @access private
     * @var    array
     */
    var $_langs = array();

    /**
     * Current language (short name (i.e. "DE", "EN"...) being used
     *
     * @access private
     * @var    string
     */
    var $_language = '';

    /**
     * Columns definitions
     *
     * @access protected
     * @var    array
     */
    var $groupTableCols = array();

    /**
     * Class constructor. Feel free to override in backend subclasses.
     *
     * @access protected
     */
    function LiveUser_Admin_Perm_Common($connectOptions)
    {
        if (is_array($connectOptions)) {
            foreach ($connectOptions as $key => $value) {
                if (isset($this->$key)) {
                    $this->$key = $value;
                }
            }
        }
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

    /**
     * Adds a new user to Perm.
     *
     * @access  public
     * @param   string   $authId    Auth user ID of the user that should be added.
     * @param   int      $type      User type (constants defined in Perm/Common.php) (optional).
     * @param   mixed    $permId    If specificed no new ID will be automatically generated instead
     * @return mixed   string (perm_user_id) or PEAR Error object
     */
    function addUser($authId, $type = LIVEUSER_USER_TYPE_ID, $permId = null)
    {
        return LiveUser::raiseError(LIVEUSER_NOT_SUPPORTED, null, null,
            'Method not supported by this container');
    }

    /**
     * Removes an existing user from Perm.
     *
     * @access  public
     * @param   string   Auth user ID of the user that should be removed.
     * @return  mixed    True on success, error object if not.
     */
    function removeUser($authId)
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'removeUser(): Method not supported by this container');
    }

    /**
     * Adds rights for a single user.
     *
     * @access  public
     * @param   string  Auth user ID.
     * @param   array   Array of right IDs to add.
     * @return  mixed   True on success, error object if not.
     */
    function addRights($authId, $rights)
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'addRights(): Method not supported by this container');
    }

    /**
     * Removes rights for a single user.
     *
     * @access  public
     * @param   string  Auth user ID.
     * @param   array   Array of right IDs to remove or empty to
     *                           remove all.
     * @return  mixed   True on success, error object if not.
     */
    function removeRights($authId, $rights = null)
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'removeRights(): Method not supported by this container');
    }

    /**
     * Shortcut to delete all existing rights of a user and add the given.
     *
     * @access  public
     * @param   string  Auth user ID.
     * @param   array   Array of right IDs to add.
     * @return  mixed   True or error object if not.
     */
    function updateRights($authId, $rights)
    {
        $this->removeRights($authId);
        $this->addRights($authId, $rights);
        return true;
    }

    /**
     * Gets all perm_user_id, type, container and rights
     *
     * The array will look like this:
     * <code>
     * $userData[0]['perm_user_id'] = 1;
     *             ['type']         = 1;
     *             ['container']    = '';
     *             ['rights'][0]    = 1;
     *             ['rights'][1]    = 4;
     *             ['rights'][2]    = 5;
     * </code>
     *
     * @access  public
     * @param   boolean  If true the rights for each user will be retrieved.
     * @return  mixed    Array with user data or error object.
     */
    function getUsers($withRights = false)
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'getUsers(): Method not supported by this container');
    }

    /**
     * Updates user type.
     *
     * @access  public
     * @param   string  Auth user ID.
     * @param   int     User type (constants defined in Perm/Common.php).
     * @return  mixed   True on success, error object if not.
     */
    function setUserType($authId, $type)
    {
        return LiveUser::raiseError(LIVEUSER_ERROR_NOT_SUPPORTED, null, null,
            'setUserType(): Method not supported by this container');
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
        return LiveUser::raiseError(LIVEUSER_NOT_SUPPORTED, null, null,
            'Method not supported by this container');
    }

    /**
    * Gets the auth ID of a user.
    *
    * @access  public
    * @param   string  Perm user ID.
    * @return  mixed   Permission ID or MDB2 error.
    */
    function getAuthUserId($permId)
    {
        return LiveUser::raiseError(LIVEUSER_NOT_SUPPORTED, null, null,
            'Method not supported by this container');
    }

    /**
     * Generate the constants to a file or define them directly.
     *
     * $mode can be either 'file' or 'php'. File will write the constant
     * in the given file, replacing/adding constants as needed. Php will
     * call define() function to actually define the constants.
     *
     * $options can contain
     * 'prefix'      => 'prefix_goes_here',
     * 'area'        => 'specific area id to grab rights from',
     * 'application' => 'specific application id to grab rights from'
     * 'naming'      => 1 for PREFIX_RIGHTNAME  <- DEFAULT
     *                  2 for PREFIX_AREANAME_RIGHTNAME
     *                  3 for PREFIX_APPLICATIONNAME_AREANAME_RIGHTNAME
     * 'filename'    => if $mode is file you must give the full path for the
     *                  output file
     *
     * If not prefix is given it will not be used to generate the constants
     *
     * @access public
     * @param  array   options for constants generation
     * @param  string  output mode desired (file or direct)
     * @param  string  type of output (constant or array)
     * @return mixed   boolean, array or DB Error object
     */
    function outputRightsConstants($options = array(), $mode = 'file', $type = 'constant')
    {
        $opt = array();

        $naming = 1;
        if (isset($options['naming'])) {
            $naming = $options['naming'];
            switch ($naming) {
                case 2:
                    $opt['with_areas']    = true;
                    break;
                case 3:
                    $opt['with_applications']    = true;
                    $opt['with_areas']           = true;
                    break;
            }
        }

        if (isset($options['area'])) {
            $opt['where_area_id'] = $options['area'];
            $opt['with_areas']    = true;
        }

        if (isset($options['application'])) {
            $opt['where_application_id'] = $options['application'];
            $opt['with_applications']    = true;
            $opt['with_areas']           = true;
        }

        $rights = $this->getRights($opt);

        if (PEAR::isError($rights)) {
            return $rights;
        }

        $generate = array();

        $prefix = '';
        if (isset($options['prefix'])) {
            $prefix = $options['prefix'] . '_';
        }

        switch ($naming) {
            case 2:
                foreach ($rights as $r) {
                    $key = $prefix . $r['area']['define_name'] . '_' . $r['define_name'];
                    $generate[$key] = $r['right_id'];
                };
                break;
            case 3:
                foreach ($rights as $r) {
                    $key = $prefix . $r['application']['define_name'] . '_'
                        . $r['area']['define_name'] . '_' . $r['define_name'];
                    $generate[$key] = $r['right_id'];
                };
                break;
            case 1:
            default:
                foreach ($rights as $r) {
                    $generate[$prefix . $r['define_name']] = $r['right_id'];
                };
        }

        $strDef = "<?php\n";
        if ($type == 'array') {
            if ($mode == 'file') {
                if (!isset($options['varname'])) {
                    return false;
                }
                $strDef .= sprintf("\$%s = %s;\n", $options['varname'], var_export($generate, true));
            } else {
                return $generate;
            }
        } else {
            foreach ($generate as $v => $k) {
                if ($mode == 'file' && $type == 'constant') {
                    $strDef .= sprintf("define('%s', %s);\n", strtoupper($v), $k);
                } else {
                    define(strtoupper($v), $k);
                }
            }
        }
        $strDef .= "?>";

        if ($mode == 'file') {
            if (!isset($options['filename'])) {
                return false;
            }

            $fp = @fopen($options['filename'], 'wb');

            if (!$fp) {
                return false;
            }

            fputs($fp, $strDef);
            fclose($fp);
        }

        return true;
    }
}
?>
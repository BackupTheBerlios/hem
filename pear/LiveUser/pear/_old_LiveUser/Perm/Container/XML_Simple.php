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
 * XML_Simple permission handling container
 *
 * @package  LiveUser
 * @category authentication
 */

/**
 * Require parent class definition and XML::Tree class.
 */
require_once 'LiveUser/Perm/Common.php';
require_once 'XML/Tree.php';

/**
 * Class LiveUser_Perm_Container_XML_Simple
 *
 * Simple XML-based complexity driver for LiveUser.
 *
 * Description:
 * The XML_Simple provides the following functionalities
 * - users
 * - userrights
 *
 * @author  Björn Kraus <krausbn@php.net>
 * @version $Id: XML_Simple.php,v 1.1 2004/07/16 13:58:49 mloitzl Exp $
 * @package LiveUser
 * @category authentication
 */
class LiveUser_Perm_Container_XML_Simple extends LiveUser_Perm_Common
{
    /**
     * XML file in which the auth data is stored.
     * @var string
     * @access private
     */
    var $file = '';

    /**
     * XML::Tree object.
     *
     * @var    object
     * @access private
     */
    var $tree = null;

    /**
     * XML::Tree object of the user logged in.
     *
     * @var    object
     * @access private
     * @see    readUserData()
     */
    var $userObj = null;

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
     * @param  mixed $connectoptions  connection options
     * @return void
     */
    function &LiveUser_Perm_Container_XML_Simple(&$connectOptions)
    {
      if (is_array($connectOptions)) {
            foreach ($connectOptions as $key => $value) {
                if (isset($this->$key)) {
                    $this->$key = $value;
                }
            }
            if (!is_file($this->file)) {
                if (is_file(getenv('DOCUMENT_ROOT') . $this->file)) {
                    $this->file = getenv('DOCUMENT_ROOT') . $this->file;
                } else {
                    return LiveUser::raiseError(LIVEUSER_ERROR_MISSING_DEPS, null, null,
                        "Perm initialisation failed. Can't find xml file.");
                }
            }
            if ($this->file) {
                if (class_exists('XML_Tree')) {
                    $tree =& new XML_Tree($this->file);
                    $err =& $tree->getTreeFromFile();
                    if (PEAR::isError($err)) {
                        return $err;
                    } else {
                        $this->tree = $tree;
                        $this->init_ok = true;
                    }
                } else {
                    $this->_error = true;
                    return LiveUser::raiseError(LIVEUSER_ERROR_MISSING_DEPS, null, null,
                        "Perm initialisation failed. Can't find XML_Tree class.");
                }
            } else {
                return LiveUser::raiseError(LIVEUSER_ERROR_MISSING_DEPS, null, null,
                    "Perm initialisation failed. Can't find xml file.");
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
        if (!$this->init_ok) {
            return false;
        }

        $success = false;
        $nodeIndex = 0;
        $userIndex = 0;

        foreach ($this->tree->root->children as $node) {
            if ($node->name == 'users') {
                foreach ($node->children as $user) {
                    if ($uid == $user->attributes['authUserId']) {
                        $this->permUserId = $user->attributes['userId'];
                        $this->userType   = $user->attributes['type'];
                        $this->userObj    =& $this->tree->root->getElement(array($nodeIndex, $userIndex));
                        $success = true;
                        break;
                    }
                    $userIndex++;
                }
            }
            $nodeIndex++;
        }

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
        $this->tree = null;
        $this->userObj = null;
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
            foreach ($this->tree->root->children as $node) {
                if ($node->name == 'users') {
                    foreach ($node->children as $user) {
                        if ($user_id == $user->attributes['authUserId']) {
                            return true;
                        }
                        $userIndex++;
                    }
                }
                $nodeIndex++;
            }
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
     * @return void
     */
    function readRights()
    {
        if (!$this->init_ok || !$this->userObj) {
            return false;
        }
        foreach ($this->userObj->children as $node) {
            if ($node->name == 'rights') {
                $tmp = explode(',', $node->content);
                foreach ($tmp as $value) {
                    $this->rights[$value] = 1;
                }
            }
        }
    } // end func readRights
} // end class LiveUser_Perm_Container_XML_Simple
?>

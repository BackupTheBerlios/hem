<?php
require_once 'config.inc.php';
//require_once 'LiveUser/LiveUser.php';
require_once 'LiveUser/Admin/Perm/Container/DB_Medium.php';
require_once 'LiveUser/Admin/Auth/Container/DB.php';


$lu_dsn = array('dsn' => $dsn);


$auth = & new LiveUser_Admin_Auth_Container_DB($lu_dsn, $conf['authContainers'][0]);

$perm = & new LiveUser_Admin_Perm_Container_DB_Medium($lu_dsn, $conf);
//             LiveUser_Admin_Perm_Container_DB_Medium($lu_dsn, $conf);
if (!$perm->init_ok) {
  echo "<pre>perm init?";
  print_r($perm);
  echo "</pre>";
  die('impossible to initialize' . $perm->getMessage());
}

function addUser($uid = '', $handle = '', $password = '', $active = false, $perm_container)
{
  global $auth, $perm;

  if(!empty($uid) && !empty($handle) && is_object($auth) && is_object($perm))
    {
      $user_auth_id = $auth->addUser($handle, $password, $active, null, null, $uid, null);
 
      if(PEAR::isError($user_auth_id))
	{
	  echo "<pre>";
	  print_r($user_auth_id);
	  echo "</pre>";
	  return FALSE;
	}
      else 
	{
	  $user_perm_id = $perm->addUser($user_auth_id, $perm_container,  LIVEUSER_USER_TYPE_ID);

	  if(PEAR::isError($user_perm_id))
	    {
	      echo "<pre>";
	      print_r($user_perm_id);
	      echo "</pre>";
	      return FALSE;
	    }
	  else return $user_perm_id;
	}
    }
  else return FALSE;
}


function removeUser($permId)
{
  global $auth, $perm;
  if (is_object($auth) && is_object($perm)) {
    $authData = $perm->getAuthUserId($permId);
    
    if (LiveUser::isError($authData)) {
      return $authData;
    }
    
    $result = $auth->removeUser($authData['auth_user_id']);
    
    if (LiveUser::isError($result)) {
      return $result;
    }
    
    return $perm->removeUser($permId);
  }
  return FALSE;
}


function listRights()
{
  
  global $perm;
  $rights = $perm->getRights();

  echo "<pre>Rights";
  print_r($rights);
  echo "</pre>";
}



function listGroups()
{
  global $perm;
  $groups = $perm->getGroups(array(
				   'where_is_active' => "'Y'"
				   )
			     );
  
  echo "<pre>Groups";
  print_r($groups);
  echo "</pre>";
}


function listUsers()
{
  global $auth, $perm;
  // Get Auth Users
  $auth_users = $auth->getUsers();
  
  echo "<pre>AuthUsers:";
  print_r($auth_users);
  echo "</pre>";
  
  // Get Perm Users
  $perm_users = $perm->getUsers();
  echo "<pre>PermUsers:";
  print_r($perm_users);
  echo "</pre>";
}  



$perm->addLanguage('en', 'english', 'English language');
// Set Language
$perm->setCurrentLanguage('en');


// Add Applikation
$app_id = $perm->addApplication('HEM', 'Heuristic Evaluation Manager');
if(PEAR::isError($app_id))
  {
    echo "<pre>";
    print_r($app_id);
    echo "</pre>";
  }
else
    echo "Added Application $app_id<br/>";

// Add Area
$area_id = $perm->addArea($app_id, 'SAMPLE_AREA', 'A Sample Area');
if(PEAR::isError($area_id))
  {
    echo "<pre>";
    print_r($area_id);
    echo "</pre>";
  }
else
    echo "Added Area $area_id<br/>";


$auth_id = getUniqueId();

// Add User
$user_id = addUser($auth_id, 'franz_josef', 'dummypass', true, $conf['permContainer']['type']);

if($user_id != FALSE)
  echo "Added User $user_id<br/>";
else
  echo "Failed to add user<br/>";

listUsers();


// Add Group
$group_id = $perm->addGroup('manager', 'The Managers', TRUE, 'MANAGER');

if(PEAR::isError($group_id))
  {
    echo "<pre>";
    print_r($group_id);
    echo "</pre>";
  }
else
  {
    echo "Added group $group_id<br/>";
    }

$res = $perm->addUserToGroup($user_id, $group_id);
if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
    echo "Added User $user_id to Group $group_id<br/>";

$right_id = $perm->addRight($area_id, 'SAMPLE_RIGHT', 'A sample Right', 'Sample Rights do not matter');
if(PEAR::isError($right_id))
  {
    echo "<pre>";
    print_r($right_id);
    echo "</pre>";
  }
else
    echo "Added Right $right_id to Area $area_id<br/>";




listGroups();

$res = $perm->grantGroupRight($group_id, $right_id);
if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
    echo "Granted Right $right_id to Group $group_id<br/>";


$user_rights = $perm->getRights(array(
				      'where_user_id' => $perm->getAuthUserId($user_id)
				      )
				);

echo "<pre>UserRights:";
print_r($user_rights);
echo "</pre>";

listRights();

if($res=$LU->tryLogin('franz_josef', 'dummypass'))
  echo $LU->getProperty('handle')." logged in<br/>";
else
  {
    echo "<pre>LU Result";
    print_r($res);
    echo "</pre>";
  }

    /* $options can contain
     * 'prefix'      => 'prefix_goes_here',
     * 'area'        => 'specific area id to grab rights from',
     * 'application' => 'specific application id to grab rights from'
     * 'naming'      => 1 for PREFIX_RIGHTNAME  <- DEFAULT
     *                  2 for PREFIX_AREANAME_RIGHTNAME
     *                  3 for PREFIX_APPLICATIONNAME_AREANAME_RIGHTNAME
     * 'filename'    => if $mode is file you must give the full path for the
     *                  output file
     */

$options = array (
		  'filename' => '/Library/WebServer/Documents/martin/hem/yard/LiveUserAdminFun/right.constants.php'
		   );



$res = $perm->outputRightsConstants($options, 'file', 'constant');
if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
    echo "Wrote ".$options['filename']."<br/>";


require_once 'right.constants.php';



$groups = $perm->getGroups(array('where_group_id' => '%'));

echo "<pre>Groups: ";
print_r($groups);
echo "</pre>";


if($LU->checkRight(SAMPLE_RIGHT))
  echo $LU->getProperty('handle')." has Right SAMPLE_RIGHT<br/>";
else
  echo "Sorry Kid you do not have Right SAMPLE_RIGHT<br/>";



$res = $perm->revokeGroupRight($group_id, $right_id);
if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
    echo "Revoked Right $right_id from Group $group_id<br/>";


$res = $perm->removeUserFromGroup($user_id, $group_id);
if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
    echo "Removed User $user_id from Group $group_id<br/>";


// Remove Group
$res = $perm->removeGroup($group_id);

if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
    echo "Removed group $group_id";

listGroups();

// Remove User
$user_res = removeUser($user_id);

if($user_res != FALSE)
  echo "Removed User $user_id<br/>";
else
  echo "Failed to remove user<br/>";


// Remove Area
$res = $perm->removeArea($area_id);
if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
    echo "Removed Area $area_id<br/>";


// Remove Right
$res = $perm->removeRight($right_id);
if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
    echo "Removed Right $right_id<br/>";



// Remove Applikation
$res = $perm->removeApplication($app_id);
if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
    echo "Removed Application $app_id<br/>";



//$res = $objRightsAdminPerm->addLanguage('en', 'english', 'English language');
//$res = $objRightsAdminPerm->setCurrentLanguage('en');






// Get Groups
/*$groups = $objRightsAdminPerm->getGroups(array(
					       'where_is_active' => "'Y'"
					       )
					 );
*/
/*echo "<pre>";
print_r($groups);
echo "</pre>";

$perm_users = $objRightsAdminPerm->getUsers();



echo "<pre>";
print_r($perm_users);
echo "</pre>";


*/




function getUniqueId($length=32, $pool="")
{ 
  // set pool of possible char 
  if($pool == ""){ 
    //      $pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
    $pool = "abcdefghijklmnopqrstuvwxyz"; 
    $pool .= "0123456789"; 
  }// end if 
  mt_srand ((double) microtime() * 1000000); 
  $unique_id = ""; 
  for ($index = 0; $index < $length; $index++) { 
    $unique_id .= substr($pool, (mt_rand()%(strlen($pool))), 1); 
  }// end for 
  return($unique_id); 
}// end get_unique_id 








//if(PEAR::isError($res))  echo $res->getMessage().": ".$res->userinfo."<br/";
//echo 'Perm_user_id created ' . print_r($res) . "\n";


// create application and areas
//$app_id = $objRightsAdminPerm->addApplication('LIVEUSER', 'website');
//$area_id = $objRightsAdminPerm->addArea($app_id, 'ONLY_AREA', 'the one and only area');


// Then he adds three rights
//$right_1 = $objRightsAdminPerm->addright($area_id, 'MODIFYNEWS',   'read something');
//$right_2 = $objRightsAdminPerm->addright($area_id, 'EDITNEWS',  'write something');

//echo 'Created two rights with id ' . $right_1 . ' and ' . $right_2 . "\n";

// Grant the user rights
//$objRightsAdminPerm->grantUserRight($user_auth_id, $right_1);
//$objRightsAdminPerm->grantUserRight($user_auth_id, $right_2);

?>

<?php
require_once 'config.inc.php';
require_once 'LiveUser/Admin/Admin.php';
//require_once 'LiveUser/LiveUser.php';
//require_once 'LiveUser/Admin/Perm/Container/DB_Medium.php';
//require_once 'LiveUser/Admin/Auth/Container/DB.php';


$admin = new LiveUser_Admin($conf, 'en');
$res = $admin->perm->setCurrentLanguage('en');
//$res = $admin->perm->addLanguage('en', 'english', 'English language');

$ts = time();

// ADD User
//function addUser($handle, $password, $type = null, $active = true, $id = null, $owner_user_id = null,
//                    $owner_group_id = null, $customFields = array())

$user_id = $admin->addUser('martin', 'dummypass', null, true, getUniqueId(), null, null, null);
if(PEAR::isError($user_id))
  {
    echo "<pre>";
    print_r($user_id);
    echo "</pre>";
  }
else 
  echo 'Created User Id ' . $user_id . '<br />';
/*
// GET User
$user = $admin->getUser($user_id);
if(PEAR::isError($user))
  {
    echo "<pre>";
    print_r($user);
    echo "</pre>";
  }
 else
   {
     echo "<pre>";
     print_r($user);
     echo "</pre>";
   }

// ADD Group
$group_id = $admin->perm->addGroup('manager', 'The Managers', TRUE, 'MANAGER');
if(PEAR::isError($group_id))
  {
    echo "<pre>";
    print_r($group_id);
    echo "</pre>";
  }
 else
   echo 'Added Group with ID :' . $group_id  . '<br />';


// GET Group
$groups = $admin->perm->getGroups();

//array('where_is_active' => "'Y'")

echo "<pre>";
print_r($groups);
echo "</pre>";

// Add User to Group
$res =$admin->perm->addUserToGroup($user_id, $group_id);

if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
 else
   echo 'Added User ' . $user_id  . 'to Group '. $group_id.'<br />';

// Add Right
$right_id = $admin->perm->addRight(null, 'SAMPLE_RIGHT', 'A sample Right', null);

if(PEAR::isError($right_id))
  {
    echo "<pre>";
    print_r($right_id);
    echo "</pre>";
  }
 else
   echo 'Added Right ' . $right_id  . '<br />';


// Grant Right to Group
$res = $admin->perm->grantGroupRight($group_id, $right_id);

if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
 else
   echo 'Granted Right ' . $right_id  . ' to Group ' . $group_id. '<br />';





// Revoke Right from Group
$res = $admin->perm->revokeGroupRight($group_id, $right_id);

if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
 else
   echo 'Revoked Right ' . $right_id  . ' from Group ' . $group_id. '<br />';


// DEL User
$res = $admin->removeUser( $user_id );
if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
 else
   echo 'Removed User Id ' . $user_id  . '<br />';



//$group_id ='13';
// DEL Group
$res = $admin->perm->removeGroup($group_id);

if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
  {
    echo "Removed group $res<br/>";
    }



//Delete Right
$res = $admin->perm->removeRight($right_id);

if(PEAR::isError($res))
  {
    echo "<pre>";
    print_r($res);
    echo "</pre>";
  }
else
  {
    echo "Removed right $res<br/>";
    }


echo "Methods:<pre>";
print_r(get_class_methods($admin->perm));
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

?>
<?php
// $Id: admin_example_mdb2.php,v 1.1 2004/07/16 13:59:08 mloitzl Exp $
require_once 'config_mdb2.inc.php';
require_once 'LiveUser/Admin.php';
$increment = time();

$admin = new LiveUser_Admin($conf, 'FR');

$custom = array(
    'name'  => 'asdfMDB22',
    'email' => 'fleh@example.comMDB23'
);
$user_id = $admin->addUser('johndoe' . $increment, 'dummypass', null, true, null, null, null, $custom);
echo 'Created Perm User Id ' . $user_id . '<br />';

if (MDB2::isError($user_id)) {
    var_dump($user_id);
}

if (!MDB2::isError($user_id) && $user_id > 2) {
    $echo_user_id = $user_id - 2;
    $admin->removeUser($echo_user_id);
    echo 'Removed Perm User Id ' . $echo_user_id . '<br />';
}

if (!MDB2::isError($user_id) && $user_id > 1) {
    $custom = array(
        'name'  => 'asdfMDBUpdated22',
        'email' => 'fleh@example.comMDBUpdated22'
    );
    $update_id = $user_id - 1;
    $admin->updateUser($update_id, 'johndoe' . $increment, 'dummypass', null, true, null, null, $custom);
    echo 'Updated Perm User Id ' . $update_id . '<br />';
}

$foo = $admin->getUser($user_id);
if (empty($foo)) {
    echo 'No user with that Perm ID was found ' . $user_id;
} else {
    print_r($foo);
}
echo '<br />';

$filters = array(
    'email' => array('name' => 'email', 'op' => '=', 'value' => 'fleh@example.comMDBUpdated22', 'cond' => 'AND', 'type' => 'text'),
    'name'  => array('name' => 'name',  'op' => '=', 'value' => 'asdfMDBUpdated22', 'cond' => '', 'type' => 'text')
);
$foo1 = $admin->searchUsers($filters);
echo 'These Users were found: <br />';
print_r($foo1);
?>
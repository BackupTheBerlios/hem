<?php
require_once 'config_mdb.inc.php';
require_once 'LiveUser/Admin/Admin.php';

$admin = new LiveUser_Admin($conf, 'FR');
$custom = array(
    array('name' => 'name',  'value' => 'asdfMDB',             'type' => 'text'),
    array('name' => 'email', 'value' => 'fleh@example.comMDB', 'type' => 'text')
);
$user_id = $admin->addUser('johndoe', 'dummypass', true, null, null, null, $custom);
echo 'Created User Id ' . $user_id . '<br />';

if ($user_id > 2) {
    $admin->removeUser(($user_id - 2));
    echo 'Removed User Id ' . ($user_id - 2) . '<br />';
}

if ($user_id > 1) {
    $custom = array(
        array('name' => 'name',  'value' => 'asdfMDBUpdated',             'type' => 'text'),
        array('name' => 'email', 'value' => 'fleh@example.comMDBUpdated', 'type' => 'text')
    );
    $admin->updateUser($user_id, 'johndoe', 'dummypass', true, null, null, $custom);
    echo 'Updated User Id ' . ($user_id - 1) . '<br />';
}

$custom = array(
    array('name' => 'name',  'type' => 'text'),
    array('name' => 'email', 'type' => 'text')
);
$foo = $admin->getUser($user_id, $custom);
if (empty($foo)) {
    echo 'No user with that ID was found';
} else {
    print_r($foo);
}
echo '<br />';

$cols = array(
    array('name' => 'name',  'type' => 'text'), 
    array('name' => 'email', 'type' => 'text')
);
    
$filters = array(
    'email' => array('op' => '=', 'value' => 'fleh@example.comMDBUpdated', 'cond' => 'AND', 'type' => 'text'),
    'name'  => array('op' => '=', 'value' => 'asdfMDBUpdated', 'cond' => '', 'type' => 'text')
);
$foo1 = $admin->searchUsers($filters, $cols);
echo 'These Users were found: <br />';
print_r($foo1);
?>
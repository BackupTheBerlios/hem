<?php
require_once 'config.inc.php';
require_once 'LiveUser/Admin/Admin.php';

$admin = new LiveUser_Admin($conf, 'FR');
$custom = array(
    array('name' => 'name',  'value' => 'asdf',             'type' => 'text'),
    array('name' => 'email', 'value' => 'fleh@example.com', 'type' => 'text')
);

$user_id = $admin->addUser('johndoe', 'dummypass', true, null, null, null, $custom);
echo 'Created User Id ' . $user_id . '<br />';

if ($user_id > 2) {
    $admin->removeUser(($user_id - 2));
    echo 'Removed User Id ' . ($user_id - 2) . '<br />';
}

if ($user_id > 1) {
    $custom = array(
        array('name' => 'name',  'value' => 'asdfUpdated',             'type' => 'text'),
        array('name' => 'email', 'value' => 'fleh@example.comUpdated', 'type' => 'text')
    );
    $admin->updateUser($user_id, 'johndoe', 'dummypass', true, null, null, $custom);
    echo 'Updated User Id ' . ($user_id - 1) . '<br />';
}

$custom = array(
    'name',
    'email'
);

$foo = $admin->getUser($user_id, $custom);
if (empty($foo)) {
    echo 'No user with that ID was found';
} else {
    print_r($foo);
}
echo '<br />';

$cols = array(
    'name', 
    'email'
);
    
$filters = array(
        'email' => array('op' => '=', 'value' => 'fleh@example.comUpdated', 'cond' => 'AND'),
        'name'  => array('op' => '=', 'value' => 'asdfUpdated', 'cond' => '')
);
$foo1 = $admin->searchUsers($filters, $cols);
echo 'These Users were found: <br />';
print_r($foo1);
?>
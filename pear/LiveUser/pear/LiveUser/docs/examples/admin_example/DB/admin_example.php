<?php
require_once 'config.inc.php';
require_once 'LiveUser/Admin.php';

$increment = time();

$admin = new LiveUser_Admin($conf, 'FR');
$custom = array(
    'name'  => 'asdfDB',
    'email' => 'fleh@example.comDB'
);

$user_id = $admin->addUser('johndoe' . $increment, 'dummypass', null, true, null, null, null, $custom);
echo 'Created User Id ' . $user_id . '<br />';

if (DB::isError($user_id)) {
    var_dump($user_id);
}

if (!DB::isError($user_id) && $user_id > 2) {
    $echo_user_id = $user_id - 2;
    $admin->removeUser($echo_user_id);
    echo 'Removed Perm User Id ' . $echo_user_id . '<br />';
}

if (!DB::isError($user_id) && $user_id > 1) {
    $custom = array(
        'name'  => 'asdfDBUpdated',
        'email' => 'fleh@example.comDBUpdated'
    );
    $updated_id = $user_id - 1;
    $admin->updateUser($updated_id, 'johndoe' . $increment, 'dummypass', null, true, null, null, $custom);
    echo 'Updated User Id ' . $updated_id . '<br />';
}

$foo = $admin->getUser($user_id);
if (empty($foo)) {
    echo 'No user with that ID was found';
} else {
    print_r($foo);
}
echo '<br />';

$filters = array(
        'email' => array('name' => 'email', 'op' => '=', 'value' => 'fleh@example.comDBUpdated', 'cond' => 'AND'),
        'name'  => array('name' => 'name',  'op' => '=', 'value' => 'asdfDBUpdated', 'cond' => '')
);
$foo1 = $admin->searchUsers($filters);
echo 'These Users were found: <br />';
print_r($foo1);
?>
<?php
require_once 'conf.php';

echo "<h1>Welcome</h1>";

if($usr->isLoggedIn()) echo "Your're logged in as: " . $usr->getProperty('passwd');
else echo "<a href=\"admin.php\">Login</a>";

?>
<?php

require_once('../php/Connection.php');
require_once('../php/GroupSystem.php');

$db = new Connection();
$groupSys = new GroupSystem($db);

echo $groupSys->is_member(1,60);

echo '<hr>'. $groupSys->error;


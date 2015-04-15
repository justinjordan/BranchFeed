<?php

require_once('../php/Connection.php');
require_once('../php/GroupSystem.php');

$db = new Connection();
$groupSys = new GroupSystem($db);

$openGroups = $groupSys->FindGroup(51);

foreach( $openGroups as $group )
{
    echo $group . '<br>';
}